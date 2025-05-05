import { useState, useEffect } from "react";
import { Container, Row, Col, Form } from "react-bootstrap";
import TranslationList from "./components/TranslationList";
import "bootstrap/dist/css/bootstrap.min.css";

interface Language {
  id: number;
  code: string;
  name: string;
  direction: string;
}

interface Document {
  id: number;
  title: string;
  source_language_id: number;
}

const App: React.FC = () => {
  const [languages, setLanguages] = useState<Language[]>([]);
  const [documents, setDocuments] = useState<Document[]>([]);
  const [selectedDocument, setSelectedDocument] = useState<number | null>(null);
  const [targetLanguage, setTargetLanguage] = useState<number | null>(null);

  // Mock user ID for demo purposes
  const currentUserId = 1;

  useEffect(() => {
    // In a real application, we would fetch these from the API
    // For this demo, we'll use mock data

    const mockLanguages: Language[] = [
      { id: 1, code: "en-US", name: "English (US)", direction: "ltr" },
      { id: 2, code: "es-ES", name: "Spanish (Spain)", direction: "ltr" },
      { id: 5, code: "fr-FR", name: "French (France)", direction: "ltr" },
      { id: 4, code: "de-DE", name: "German (Germany)", direction: "ltr" },
      { id: 3, code: "ar-SA", name: "Arabic (Saudi Arabia)", direction: "rtl" },
    ];

    const mockDocuments: Document[] = [
      { id: 1, title: "Product Manual", source_language_id: 1 },
      { id: 2, title: "Marketing Brochure", source_language_id: 1 },
      { id: 3, title: "Website Content", source_language_id: 1 },
    ];

    setLanguages(mockLanguages);
    setDocuments(mockDocuments);

    // Set defaults
    if (mockDocuments.length > 0) {
      setSelectedDocument(mockDocuments[0].id);
    }

    if (mockLanguages.length > 1) {
      // Default to the second language (assuming first is the source)
      setTargetLanguage(mockLanguages[1].id);
    }
  }, []);

  const handleDocumentChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    setSelectedDocument(parseInt(e.target.value, 10));
  };

  const handleLanguageChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    setTargetLanguage(parseInt(e.target.value, 10));
  };

  return (
    <Container className="py-4">
      <h1 className="mb-4">Robert CAT Tool</h1>

      <Row className="mb-4">
        <Col md={6}>
          <Form.Group>
            <Form.Label>Document</Form.Label>
            <Form.Select
              value={selectedDocument || ""}
              onChange={handleDocumentChange}
            >
              <option value="">Select a document</option>
              {documents.map((doc) => (
                <option key={doc.id} value={doc.id}>
                  {doc.title}
                </option>
              ))}
            </Form.Select>
          </Form.Group>
        </Col>

        <Col md={6}>
          <Form.Group>
            <Form.Label>Target Language</Form.Label>
            <Form.Select
              value={targetLanguage || ""}
              onChange={handleLanguageChange}
            >
              <option value="">Select a language</option>
              {languages.map((lang) => (
                <option key={lang.id} value={lang.id}>
                  {lang.name} ({lang.code})
                </option>
              ))}
            </Form.Select>
          </Form.Group>
        </Col>
      </Row>

      {selectedDocument && targetLanguage && (
        <TranslationList
          documentId={selectedDocument}
          targetLanguageId={targetLanguage}
          currentUserId={currentUserId}
        />
      )}

      {(!selectedDocument || !targetLanguage) && (
        <div className="alert alert-info">
          Please select a document and target language to begin translation.
        </div>
      )}
    </Container>
  );
};

export default App;
