import { useState } from "react";
import { Button, Modal, Form, Alert } from "react-bootstrap";
import translationService, {
  type TranslationUnit,
} from "../services/translationService";

interface TranslationActionsProps {
  documentId: number;
  onUnitAdded: () => void;
  onUnitDeleted: () => void;
}

const TranslationActions: React.FC<TranslationActionsProps> = ({
  documentId,
  onUnitAdded,
  onUnitDeleted,
}) => {
  // Modal state
  const [showAddModal, setShowAddModal] = useState<boolean>(false);
  const [showDeleteModal, setShowDeleteModal] = useState<boolean>(false);

  // Form data
  const [sourceContent, setSourceContent] = useState<string>("");
  const [sequenceNumber, setSequenceNumber] = useState<number>(0);
  const [context, setContext] = useState<string>("");
  const [unitIdToDelete, setUnitIdToDelete] = useState<number | null>(null);

  // Status messages
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);

  const handleAddUnit = async () => {
    try {
      setError(null);

      // Validate form
      if (!sourceContent || sequenceNumber <= 0) {
        setError("Please fill in all required fields");
        return;
      }

      // Create new translation unit
      const newUnit: TranslationUnit = {
        document_id: documentId,
        sequence_number: sequenceNumber,
        source_content: sourceContent,
        context: context || undefined,
        translations: {},
      };

      await translationService.createTranslationUnit(newUnit);

      // Clear form and show success message
      setSourceContent("");
      setSequenceNumber(0);
      setContext("");
      setSuccess("Translation unit added successfully");

      // Close modal and notify parent
      setShowAddModal(false);
      onUnitAdded();

      // Clear success message after 3 seconds
      setTimeout(() => setSuccess(null), 3000);
    } catch (err) {
      setError("Error adding translation unit. Please try again.");
      console.error(err);
    }
  };

  const handleDeleteUnit = async () => {
    try {
      setError(null);

      if (!unitIdToDelete) {
        setError("No unit selected for deletion");
        return;
      }

      await translationService.deleteTranslationUnit(unitIdToDelete);

      // Show success message
      setSuccess("Translation unit deleted successfully");

      // Close modal and notify parent
      setShowDeleteModal(false);
      setUnitIdToDelete(null);
      onUnitDeleted();

      // Clear success message after 3 seconds
      setTimeout(() => setSuccess(null), 3000);
    } catch (err) {
      setError("Error deleting translation unit. Please try again.");
      console.error(err);
    }
  };

  const openDeleteModal = (unitId: number) => {
    setUnitIdToDelete(unitId);
    setShowDeleteModal(true);
  };

  return (
    <div className="translation-actions mb-4">
      {error && <Alert variant="danger">{error}</Alert>}
      {success && <Alert variant="success">{success}</Alert>}

      <div className="d-flex gap-2 mb-3">
        <Button variant="primary" onClick={() => setShowAddModal(true)}>
          Add New Translation Unit
        </Button>
      </div>

      {/* Add Translation Unit Modal */}
      <Modal show={showAddModal} onHide={() => setShowAddModal(false)}>
        <Modal.Header closeButton>
          <Modal.Title>Add Translation Unit</Modal.Title>
        </Modal.Header>
        <Modal.Body>
          <Form>
            <Form.Group className="mb-3">
              <Form.Label>Sequence Number*</Form.Label>
              <Form.Control
                type="number"
                value={sequenceNumber || ""}
                onChange={(e) =>
                  setSequenceNumber(parseInt(e.target.value, 10) || 0)
                }
                required
              />
              <Form.Text className="text-muted">
                The order of this unit in the document
              </Form.Text>
            </Form.Group>

            <Form.Group className="mb-3">
              <Form.Label>Source Content*</Form.Label>
              <Form.Control
                as="textarea"
                rows={3}
                value={sourceContent}
                onChange={(e) => setSourceContent(e.target.value)}
                required
              />
            </Form.Group>

            <Form.Group className="mb-3">
              <Form.Label>Context (Optional)</Form.Label>
              <Form.Control
                as="textarea"
                rows={2}
                value={context}
                onChange={(e) => setContext(e.target.value)}
              />
              <Form.Text className="text-muted">
                Additional context to help translators
              </Form.Text>
            </Form.Group>
          </Form>
        </Modal.Body>
        <Modal.Footer>
          <Button variant="secondary" onClick={() => setShowAddModal(false)}>
            Cancel
          </Button>
          <Button variant="primary" onClick={handleAddUnit}>
            Add Unit
          </Button>
        </Modal.Footer>
      </Modal>

      {/* Delete Translation Unit Modal */}
      <Modal show={showDeleteModal} onHide={() => setShowDeleteModal(false)}>
        <Modal.Header closeButton>
          <Modal.Title>Confirm Deletion</Modal.Title>
        </Modal.Header>
        <Modal.Body>
          Are you sure you want to delete this translation unit? This action
          cannot be undone.
        </Modal.Body>
        <Modal.Footer>
          <Button variant="secondary" onClick={() => setShowDeleteModal(false)}>
            Cancel
          </Button>
          <Button variant="danger" onClick={handleDeleteUnit}>
            Delete
          </Button>
        </Modal.Footer>
      </Modal>
    </div>
  );
};

export default TranslationActions;
