// Enhanced component to allow users to add/edit translations to a translation unit.
import { useState, useEffect } from "react";
import { Form, Button, Card, Alert, Row, Col } from "react-bootstrap";
import translationService, {
  type Translation,
  type TranslationUnit,
} from "@/services/translationService";

interface TranslationFormProps {
  unit: TranslationUnit;
  targetLanguageId: number;
  currentUserId: number;
  onSubmit: (unit: TranslationUnit) => void;
  onCancel: () => void;
}

const TranslationForm: React.FC<TranslationFormProps> = ({
  unit,
  targetLanguageId,
  currentUserId,
  onSubmit,
  onCancel,
}) => {
  const [translationContent, setTranslationContent] = useState<string>("");
  const [translationStatus, setTranslationStatus] = useState<string>("draft");
  const [isReviewer, setIsReviewer] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    // Initialize form with existing translation if available
    const existingTranslation = unit.translations[targetLanguageId];
    if (existingTranslation) {
      setTranslationContent(existingTranslation.content);
      setTranslationStatus(existingTranslation.status);
    }

    // In a real app, check user roles from a user service
    setIsReviewer(true);
  }, [unit, targetLanguageId]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();

    try {
      if (!translationContent.trim()) {
        setError("Translation content cannot be empty");
        return;
      }

      const updatedUnit = { ...unit };
      const existingTranslation = unit.translations[targetLanguageId];

      if (existingTranslation) {
        // Update existing translation
        const newTranslation: Translation = {
          ...existingTranslation,
          content: translationContent,
          status: translationStatus as Translation["status"],
          reviewed_by:
            translationStatus !== "draft" ? currentUserId : undefined,
        };

        updatedUnit.translations = {
          ...updatedUnit.translations,
          [targetLanguageId]: newTranslation,
        };

        // If we're submitting with a status change to anything but draft,
        // update the translation status through the specific API
        if (
          translationStatus !== "draft" &&
          existingTranslation.status !== translationStatus
        ) {
          await translationService.updateTranslationStatus(
            unit.id!,
            targetLanguageId,
            translationStatus,
            currentUserId
          );
        }
      } else {
        // Add new translation
        await translationService.addTranslation(
          unit.id!,
          targetLanguageId,
          translationContent,
          currentUserId
        );

        // Create the structure for the updated unit
        const newTranslation: Translation = {
          content: translationContent,
          status: translationStatus as Translation["status"],
          translated_by: currentUserId,
          reviewed_by:
            translationStatus !== "draft" ? currentUserId : undefined,
        };

        updatedUnit.translations = {
          ...updatedUnit.translations,
          [targetLanguageId]: newTranslation,
        };
      }

      onSubmit(updatedUnit);
    } catch (err) {
      setError("Error saving translation. Please try again.");
      console.error(err);
    }
  };

  return (
    <Row>
      <Col md={12}>
        <Card className="mb-4">
          <Card.Header>
            {unit.translations[targetLanguageId]
              ? "Edit Translation"
              : "Add Translation"}
          </Card.Header>
          <Card.Body>
            {error && <Alert variant="danger">{error}</Alert>}

            <div className="mb-4">
              <h5>Source Content:</h5>
              <div className="source-content p-3 bg-light">
                {unit.source_content}
              </div>
              {unit.context && (
                <div className="context mt-2 text-muted">
                  <small>Context: {unit.context}</small>
                </div>
              )}
            </div>

            <Form onSubmit={handleSubmit}>
              <Form.Group className="mb-3">
                <Form.Label>Translation:</Form.Label>
                <Form.Control
                  as="textarea"
                  rows={5}
                  value={translationContent}
                  onChange={(e) => setTranslationContent(e.target.value)}
                  required
                />
              </Form.Group>

              {isReviewer && (
                <Form.Group className="mb-3">
                  <Form.Label>Status:</Form.Label>
                  <Form.Select
                    value={translationStatus}
                    onChange={(e) => setTranslationStatus(e.target.value)}
                  >
                    <option value="draft">Draft</option>
                    <option value="reviewed">Reviewed</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                  </Form.Select>
                </Form.Group>
              )}

              <div className="d-flex justify-content-between">
                <div>
                  <Button variant="primary" type="submit" className="me-2">
                    Save
                  </Button>
                  <Button variant="secondary" onClick={onCancel}>
                    Cancel
                  </Button>
                </div>
              </div>
            </Form>
          </Card.Body>
        </Card>
      </Col>
    </Row>
  );
};

export default TranslationForm;
