import { useState } from "react";
import { Button, Modal, Form, Alert, Spinner } from "react-bootstrap";
import translationService, {
  type TranslationUnit,
} from "../services/translationService";

interface BulkOperationsProps {
  documentId: number;
  targetLanguageId: number;
  currentUserId: number;
  onOperationComplete: () => void;
}

const BulkOperations: React.FC<BulkOperationsProps> = ({
  documentId,
  targetLanguageId,
  currentUserId,
  onOperationComplete,
}) => {
  // Modal states
  const [showBulkTranslateModal, setShowBulkTranslateModal] =
    useState<boolean>(false);
  const [showBulkStatusModal, setShowBulkStatusModal] =
    useState<boolean>(false);

  // Form data
  const [selectedUnitIds, setSelectedUnitIds] = useState<number[]>([]);
  const [bulkTranslateContent, setBulkTranslateContent] = useState<string>("");
  const [bulkStatus, setBulkStatus] = useState<string>("draft");

  // Operation state
  const [loading, setLoading] = useState<boolean>(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState<string | null>(null);
  const [units, setUnits] = useState<TranslationUnit[]>([]);

  // Fetch all units for the document
  const fetchUnits = async () => {
    try {
      setLoading(true);
      setError(null);

      // Fetch all units (we'll use a large limit to get all)
      const response = await translationService.getTranslationsByDocument(
        documentId,
        1,
        1000
      );

      setUnits(response.units);
    } catch (err) {
      setError("Error fetching translation units");
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  // Open bulk translate modal and fetch units
  const handleOpenBulkTranslateModal = async () => {
    await fetchUnits();
    setShowBulkTranslateModal(true);
  };

  // Open bulk status modal and fetch units
  const handleOpenBulkStatusModal = async () => {
    await fetchUnits();
    setShowBulkStatusModal(true);
  };

  // Handle bulk translation
  const handleBulkTranslate = async () => {
    try {
      setLoading(true);
      setError(null);

      if (selectedUnitIds.length === 0) {
        setError("Please select at least one unit");
        setLoading(false);
        return;
      }

      if (!bulkTranslateContent) {
        setError("Please enter translation content");
        setLoading(false);
        return;
      }

      // Process all selected units
      for (const unitId of selectedUnitIds) {
        await translationService.addTranslation(
          unitId,
          targetLanguageId,
          bulkTranslateContent,
          currentUserId
        );
      }

      // Success
      setSuccess(`Successfully translated ${selectedUnitIds.length} units`);

      // Clear form data
      setSelectedUnitIds([]);
      setBulkTranslateContent("");

      // Close modal
      setShowBulkTranslateModal(false);

      // Notify parent
      onOperationComplete();

      // Clear success message after 3 seconds
      setTimeout(() => setSuccess(null), 3000);
    } catch (err) {
      setError("Error in bulk translation. Please try again.");
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  // Handle bulk status update
  const handleBulkStatusUpdate = async () => {
    try {
      setLoading(true);
      setError(null);

      if (selectedUnitIds.length === 0) {
        setError("Please select at least one unit");
        setLoading(false);
        return;
      }

      // Process all selected units
      for (const unitId of selectedUnitIds) {
        await translationService.updateTranslationStatus(
          unitId,
          targetLanguageId,
          bulkStatus,
          currentUserId
        );
      }

      // Success
      setSuccess(
        `Successfully updated status for ${selectedUnitIds.length} units`
      );

      // Clear form data
      setSelectedUnitIds([]);
      setBulkStatus("draft");

      // Close modal
      setShowBulkStatusModal(false);

      // Notify parent
      onOperationComplete();

      // Clear success message after 3 seconds
      setTimeout(() => setSuccess(null), 3000);
    } catch (err) {
      setError("Error updating statuses. Please try again.");
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  // Toggle unit selection
  const toggleUnitSelection = (unitId: number) => {
    if (selectedUnitIds.includes(unitId)) {
      setSelectedUnitIds(selectedUnitIds.filter((id) => id !== unitId));
    } else {
      setSelectedUnitIds([...selectedUnitIds, unitId]);
    }
  };

  // Select all units
  const selectAllUnits = () => {
    const allUnitIds = units.map((unit) => unit.id!);
    setSelectedUnitIds(allUnitIds);
  };

  // Clear selection
  const clearSelection = () => {
    setSelectedUnitIds([]);
  };

  return (
    <div className="bulk-operations mb-4">
      {error && <Alert variant="danger">{error}</Alert>}
      {success && <Alert variant="success">{success}</Alert>}

      <div className="d-flex gap-2">
        <Button
          variant="outline-primary"
          onClick={handleOpenBulkTranslateModal}
        >
          Bulk Translate
        </Button>
        <Button variant="outline-primary" onClick={handleOpenBulkStatusModal}>
          Bulk Update Status
        </Button>
      </div>

      {/* Bulk Translate Modal */}
      <Modal
        show={showBulkTranslateModal}
        onHide={() => setShowBulkTranslateModal(false)}
        size="lg"
      >
        <Modal.Header closeButton>
          <Modal.Title>Bulk Translate</Modal.Title>
        </Modal.Header>
        <Modal.Body>
          {loading && units.length === 0 ? (
            <div className="text-center my-4">
              <Spinner animation="border" />
              <p>Loading translation units...</p>
            </div>
          ) : (
            <>
              <div className="mb-3">
                <Button
                  variant="outline-primary"
                  size="sm"
                  onClick={selectAllUnits}
                  className="me-2"
                >
                  Select All
                </Button>
                <Button
                  variant="outline-secondary"
                  size="sm"
                  onClick={clearSelection}
                >
                  Clear Selection
                </Button>
              </div>

              <div
                className="unit-selection mb-4"
                style={{ maxHeight: "300px", overflowY: "auto" }}
              >
                <Form>
                  {units.map((unit) => (
                    <Form.Check
                      key={unit.id}
                      type="checkbox"
                      id={`unit-${unit.id}`}
                      label={`#${
                        unit.sequence_number
                      }: ${unit.source_content.substring(0, 50)}${
                        unit.source_content.length > 50 ? "..." : ""
                      }`}
                      checked={selectedUnitIds.includes(unit.id!)}
                      onChange={() => toggleUnitSelection(unit.id!)}
                      className="mb-2"
                    />
                  ))}
                </Form>
              </div>

              <Form.Group className="mb-3">
                <Form.Label>Translation Content</Form.Label>
                <Form.Control
                  as="textarea"
                  rows={5}
                  value={bulkTranslateContent}
                  onChange={(e) => setBulkTranslateContent(e.target.value)}
                  placeholder="Enter translation content to be applied to all selected units"
                  required
                />
                <Form.Text className="text-muted">
                  This content will be used for all selected units.
                </Form.Text>
              </Form.Group>
            </>
          )}
        </Modal.Body>
        <Modal.Footer>
          <Button
            variant="secondary"
            onClick={() => setShowBulkTranslateModal(false)}
          >
            Cancel
          </Button>
          <Button
            variant="primary"
            onClick={handleBulkTranslate}
            disabled={
              loading || selectedUnitIds.length === 0 || !bulkTranslateContent
            }
          >
            {loading ? (
              <>
                <Spinner
                  as="span"
                  animation="border"
                  size="sm"
                  className="me-2"
                />
                Processing...
              </>
            ) : (
              `Translate ${selectedUnitIds.length} Units`
            )}
          </Button>
        </Modal.Footer>
      </Modal>

      {/* Bulk Status Update Modal */}
      <Modal
        show={showBulkStatusModal}
        onHide={() => setShowBulkStatusModal(false)}
        size="lg"
      >
        <Modal.Header closeButton>
          <Modal.Title>Bulk Update Status</Modal.Title>
        </Modal.Header>
        <Modal.Body>
          {loading && units.length === 0 ? (
            <div className="text-center my-4">
              <Spinner animation="border" />
              <p>Loading translation units...</p>
            </div>
          ) : (
            <>
              <div className="mb-3">
                <Button
                  variant="outline-primary"
                  size="sm"
                  onClick={selectAllUnits}
                  className="me-2"
                >
                  Select All
                </Button>
                <Button
                  variant="outline-secondary"
                  size="sm"
                  onClick={clearSelection}
                >
                  Clear Selection
                </Button>
              </div>

              <div
                className="unit-selection mb-4"
                style={{ maxHeight: "300px", overflowY: "auto" }}
              >
                <Form>
                  {units
                    .filter((unit) => unit.translations[targetLanguageId]) // Only units that have translations
                    .map((unit) => (
                      <Form.Check
                        key={unit.id}
                        type="checkbox"
                        id={`status-unit-${unit.id}`}
                        label={`#${
                          unit.sequence_number
                        }: ${unit.source_content.substring(0, 50)}${
                          unit.source_content.length > 50 ? "..." : ""
                        }`}
                        checked={selectedUnitIds.includes(unit.id!)}
                        onChange={() => toggleUnitSelection(unit.id!)}
                        className="mb-2"
                      />
                    ))}
                </Form>
              </div>

              <Form.Group className="mb-3">
                <Form.Label>New Status</Form.Label>
                <Form.Select
                  value={bulkStatus}
                  onChange={(e) => setBulkStatus(e.target.value)}
                >
                  <option value="draft">Draft</option>
                  <option value="reviewed">Reviewed</option>
                  <option value="approved">Approved</option>
                  <option value="rejected">Rejected</option>
                </Form.Select>
              </Form.Group>
            </>
          )}
        </Modal.Body>
        <Modal.Footer>
          <Button
            variant="secondary"
            onClick={() => setShowBulkStatusModal(false)}
          >
            Cancel
          </Button>
          <Button
            variant="primary"
            onClick={handleBulkStatusUpdate}
            disabled={loading || selectedUnitIds.length === 0}
          >
            {loading ? (
              <>
                <Spinner
                  as="span"
                  animation="border"
                  size="sm"
                  className="me-2"
                />
                Processing...
              </>
            ) : (
              `Update ${selectedUnitIds.length} Units`
            )}
          </Button>
        </Modal.Footer>
      </Modal>
    </div>
  );
};

export default BulkOperations;
