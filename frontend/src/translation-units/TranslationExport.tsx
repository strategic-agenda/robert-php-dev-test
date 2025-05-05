import { useState } from "react";
import { Button, Modal, Form, Alert, Spinner } from "react-bootstrap";
import translationService, {
  type TranslationUnit,
} from "../services/translationService";

interface TranslationExportProps {
  documentId: number;
  targetLanguageId: number;
}

const TranslationExport: React.FC<TranslationExportProps> = ({
  documentId,
  targetLanguageId,
}) => {
  const [showModal, setShowModal] = useState<boolean>(false);
  const [format, setFormat] = useState<string>("json");
  const [includeUntranslated, setIncludeUntranslated] =
    useState<boolean>(false);
  const [includeMetadata, setIncludeMetadata] = useState<boolean>(true);
  const [loading, setLoading] = useState<boolean>(false);
  const [error, setError] = useState<string | null>(null);
  const [exportedData, setExportedData] = useState<string | null>(null);

  const handleExport = async () => {
    try {
      setLoading(true);
      setError(null);
      setExportedData(null);

      // Fetch all translation units for the document
      const response = await translationService.getTranslationsByDocument(
        documentId,
        1,
        1000 // Large limit to get all units
      );

      const units = response.units;

      // Filter units based on options
      const filteredUnits = includeUntranslated
        ? units
        : units.filter((unit) => !!unit.translations[targetLanguageId]);

      // Format the data based on selected format
      let formattedData: string;

      switch (format) {
        case "json":
          formattedData = formatAsJson(filteredUnits, includeMetadata);
          break;
        case "csv":
          formattedData = formatAsCsv(filteredUnits, includeMetadata);
          break;
        case "xliff":
          formattedData = formatAsXliff(filteredUnits, includeMetadata);
          break;
        default:
          formattedData = formatAsJson(filteredUnits, includeMetadata);
      }

      setExportedData(formattedData);
    } catch (err) {
      setError("Error exporting translations. Please try again.");
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  // Format as JSON
  const formatAsJson = (
    units: TranslationUnit[],
    includeMetadata: boolean
  ): string => {
    // Create a simplified version of the units for export
    const exportData = units.map((unit) => {
      const translation = unit.translations[targetLanguageId];

      const baseData = {
        sequence: unit.sequence_number,
        source: unit.source_content,
        target: translation ? translation.content : "",
      };

      if (includeMetadata && translation) {
        return {
          ...baseData,
          status: translation.status,
          translated_by: translation.translated_by,
          reviewed_by: translation.reviewed_by,
          created_at: translation.created_at,
          updated_at: translation.updated_at,
        };
      }

      return baseData;
    });

    return JSON.stringify(exportData, null, 2);
  };

  // Format as CSV
  const formatAsCsv = (
    units: TranslationUnit[],
    includeMetadata: boolean
  ): string => {
    // Create header row
    let headers = ["Sequence", "Source", "Target"];

    if (includeMetadata) {
      headers = [
        ...headers,
        "Status",
        "Translated By",
        "Reviewed By",
        "Created",
        "Updated",
      ];
    }

    const headerRow = headers.join(",");

    // Create data rows
    const dataRows = units.map((unit) => {
      const translation = unit.translations[targetLanguageId];

      // Escape CSV values
      const escapeCsv = (value: string) => {
        if (!value) return "";
        // Escape quotes and wrap in quotes if contains comma or quote
        if (
          value.includes('"') ||
          value.includes(",") ||
          value.includes("\n")
        ) {
          return `"${value.replace(/"/g, '""')}"`;
        }
        return value;
      };

      let row = [
        unit.sequence_number,
        escapeCsv(unit.source_content),
        translation ? escapeCsv(translation.content) : "",
      ];

      if (includeMetadata && translation) {
        row = [
          ...row,
          translation.status,
          translation.translated_by.toString(),
          translation.reviewed_by?.toString() || "",
          translation.created_at || "",
          translation.updated_at || "",
        ];
      }

      return row.join(",");
    });

    return [headerRow, ...dataRows].join("\n");
  };

  // Format as XLIFF (simplified version)
  const formatAsXliff = (
    units: TranslationUnit[],
    includeMetadata: boolean
  ): string => {
    const xmlHeader = '<?xml version="1.0" encoding="UTF-8"?>';
    const xliffHeader =
      '<xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">';
    const xliffFooter = "</xliff>";

    const fileHeader = `<file source-language="en" target-language="target" datatype="plaintext">
  <header/>
  <body>`;
    const fileFooter = `  </body>
</file>`;

    // Escape XML characters
    const escapeXml = (str: string) => {
      return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&apos;");
    };

    // Create trans-units
    const transUnits = units
      .map((unit) => {
        const translation = unit.translations[targetLanguageId];

        let metadata = "";
        if (includeMetadata && translation) {
          metadata = `
      <note>Status: ${translation.status}</note>
      <note>Translator: ${translation.translated_by}</note>
      ${
        translation.reviewed_by
          ? `<note>Reviewer: ${translation.reviewed_by}</note>`
          : ""
      }`;
        }

        return `    <trans-unit id="unit-${unit.sequence_number}">
      <source>${escapeXml(unit.source_content)}</source>
      <target>${
        translation ? escapeXml(translation.content) : ""
      }</target>${metadata}
    </trans-unit>`;
      })
      .join("\n");

    return `${xmlHeader}
${xliffHeader}
${fileHeader}
${transUnits}
${fileFooter}
${xliffFooter}`;
  };

  // Download the exported data as a file
  const handleDownload = () => {
    if (!exportedData) return;

    const blob = new Blob([exportedData], {
      type:
        format === "json"
          ? "application/json"
          : format === "csv"
          ? "text/csv"
          : "application/xml",
    });

    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = `translations_${documentId}_${targetLanguageId}.${format}`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
  };

  return (
    <div className="translation-export">
      <Button
        variant="outline-secondary"
        size="sm"
        onClick={() => setShowModal(true)}
      >
        Export Translations
      </Button>

      <Modal show={showModal} onHide={() => setShowModal(false)} size="lg">
        <Modal.Header closeButton>
          <Modal.Title>Export Translations</Modal.Title>
        </Modal.Header>
        <Modal.Body>
          {error && <Alert variant="danger">{error}</Alert>}

          <Form>
            <Form.Group className="mb-3">
              <Form.Label>Export Format</Form.Label>
              <Form.Select
                value={format}
                onChange={(e) => setFormat(e.target.value)}
              >
                <option value="json">JSON</option>
                <option value="csv">CSV</option>
                <option value="xliff">XLIFF (XML)</option>
              </Form.Select>
            </Form.Group>

            <Form.Group className="mb-3">
              <Form.Check
                type="checkbox"
                id="include-untranslated"
                label="Include untranslated units"
                checked={includeUntranslated}
                onChange={(e) => setIncludeUntranslated(e.target.checked)}
              />
            </Form.Group>

            <Form.Group className="mb-3">
              <Form.Check
                type="checkbox"
                id="include-metadata"
                label="Include metadata (status, user info, timestamps)"
                checked={includeMetadata}
                onChange={(e) => setIncludeMetadata(e.target.checked)}
              />
            </Form.Group>
          </Form>

          <div className="d-grid gap-2 mb-3">
            <Button variant="primary" onClick={handleExport} disabled={loading}>
              {loading ? (
                <>
                  <Spinner
                    as="span"
                    animation="border"
                    size="sm"
                    className="me-2"
                  />
                  Generating Export...
                </>
              ) : (
                "Generate Export"
              )}
            </Button>
          </div>

          {exportedData && (
            <div className="export-preview">
              <div className="d-flex justify-content-between mb-2">
                <h5>Export Preview</h5>
                <Button variant="success" size="sm" onClick={handleDownload}>
                  Download
                </Button>
              </div>
              <div
                className="border rounded p-3 bg-light"
                style={{ maxHeight: "300px", overflow: "auto" }}
              >
                <pre style={{ whiteSpace: "pre-wrap" }}>{exportedData}</pre>
              </div>
            </div>
          )}
        </Modal.Body>
        <Modal.Footer>
          <Button variant="secondary" onClick={() => setShowModal(false)}>
            Close
          </Button>
        </Modal.Footer>
      </Modal>
    </div>
  );
};

export default TranslationExport;
