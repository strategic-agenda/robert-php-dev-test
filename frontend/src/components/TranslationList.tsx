// Component to display a list of translation units and their translations.
import { useState, useEffect, useCallback } from "react";
import { Button, Table, Pagination, Spinner, Badge } from "react-bootstrap";
import translationService, {
  type TranslationUnit,
  type PaginationInfo,
} from "@/services/translationService";
import TranslationForm from "./TranslationForm";
import TranslationActions from "./TranslationActions";

interface TranslationListProps {
  documentId: number;
  targetLanguageId: number;
  currentUserId: number;
}

const TranslationList: React.FC<TranslationListProps> = ({
  documentId,
  targetLanguageId,
  currentUserId,
}) => {
  const [units, setUnits] = useState<TranslationUnit[]>([]);
  const [pagination, setPagination] = useState<PaginationInfo>({
    page: 1,
    limit: 10,
    total: 0,
    pages: 0,
  });
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);
  const [selectedUnit, setSelectedUnit] = useState<TranslationUnit | null>(
    null
  );
  const [editMode, setEditMode] = useState<boolean>(false);

  const fetchTranslationUnits = useCallback(() => {
    const performFetch = async () => {
      try {
        setLoading(true);
        const response = await translationService.getTranslationsByDocument(
          documentId,
          pagination.page,
          pagination.limit
        );
        setUnits(response.units);
        setPagination(response.pagination);
        setError(null);
      } catch (err) {
        setError("Error loading translation units. Please try again.");
        console.error(err);
      } finally {
        setLoading(false);
      }
    };
    performFetch();
  }, [documentId, pagination.page, pagination.limit]);

  useEffect(() => {
    fetchTranslationUnits();
  }, [documentId, pagination.page, fetchTranslationUnits]);

  const handlePageChange = (page: number) => {
    setPagination((prev) => ({ ...prev, page }));
  };

  const handleEditClick = (unit: TranslationUnit) => {
    setSelectedUnit(unit);
    setEditMode(true);
  };

  const handleFormSubmit = async (updatedUnit: TranslationUnit) => {
    try {
      await translationService.updateTranslationUnit(updatedUnit);
      setEditMode(false);
      setSelectedUnit(null);
      fetchTranslationUnits();
    } catch (err) {
      setError("Error updating translation. Please try again.");
      console.error(err);
    }
  };

  const handleFormCancel = () => {
    setEditMode(false);
    setSelectedUnit(null);
  };

  const handleAddTranslation = (unit: TranslationUnit) => {
    setSelectedUnit(unit);
    setEditMode(true);
  };

  const handleDeleteClick = async (unitId: number) => {
    try {
      if (
        window.confirm("Are you sure you want to delete this translation unit?")
      ) {
        await translationService.deleteTranslationUnit(unitId);
        fetchTranslationUnits();
      }
    } catch (err) {
      setError("Error deleting translation unit. Please try again.");
      console.error(err);
    }
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case "draft":
        return <Badge bg="secondary">Draft</Badge>;
      case "reviewed":
        return <Badge bg="info">Reviewed</Badge>;
      case "approved":
        return <Badge bg="success">Approved</Badge>;
      case "rejected":
        return <Badge bg="danger">Rejected</Badge>;
      default:
        return <Badge bg="light">Unknown</Badge>;
    }
  };

  if (loading && units.length === 0) {
    return <Spinner animation="border" role="status" />;
  }

  if (error && units.length === 0) {
    return <div className="alert alert-danger">{error}</div>;
  }

  if (editMode && selectedUnit) {
    return (
      <TranslationForm
        unit={selectedUnit}
        targetLanguageId={targetLanguageId}
        currentUserId={currentUserId}
        onSubmit={handleFormSubmit}
        onCancel={handleFormCancel}
      />
    );
  }

  return (
    <div className="translation-list">
      <h2>Translation Units</h2>
      {error && <div className="alert alert-danger">{error}</div>}

      <div className="d-flex justify-content-between mb-3">
        <div>
          {/* Add the TranslationActions component */}
          <TranslationActions
            documentId={documentId}
            onUnitAdded={fetchTranslationUnits}
            onUnitDeleted={fetchTranslationUnits}
          />
        </div>
      </div>

      <Table striped bordered hover>
        <thead>
          <tr>
            <th>#</th>
            <th>Source Content</th>
            <th>Translation</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          {units.map((unit) => {
            const translation = unit.translations[targetLanguageId];
            return (
              <tr key={unit.id}>
                <td>{unit.sequence_number}</td>
                <td>{unit.source_content}</td>
                <td>
                  {translation ? (
                    translation.content
                  ) : (
                    <em>Not translated yet</em>
                  )}
                </td>
                <td>
                  {translation ? (
                    getStatusBadge(translation.status)
                  ) : (
                    <Badge bg="warning">Missing</Badge>
                  )}
                </td>
                <td>
                  <div className="d-flex gap-2">
                    {translation ? (
                      <Button
                        variant="primary"
                        size="sm"
                        onClick={() => handleEditClick(unit)}
                      >
                        Edit
                      </Button>
                    ) : (
                      <Button
                        variant="success"
                        size="sm"
                        onClick={() => handleAddTranslation(unit)}
                      >
                        Translate
                      </Button>
                    )}
                    <Button
                      variant="danger"
                      size="sm"
                      onClick={() => handleDeleteClick(unit.id!)}
                    >
                      Delete
                    </Button>
                  </div>
                </td>
              </tr>
            );
          })}
        </tbody>
      </Table>

      <Pagination>
        <Pagination.First
          disabled={pagination.page === 1}
          onClick={() => handlePageChange(1)}
        />
        <Pagination.Prev
          disabled={pagination.page === 1}
          onClick={() => handlePageChange(pagination.page - 1)}
        />

        {Array.from({ length: pagination.pages }, (_, i) => (
          <Pagination.Item
            key={i + 1}
            active={i + 1 === pagination.page}
            onClick={() => handlePageChange(i + 1)}
          >
            {i + 1}
          </Pagination.Item>
        ))}

        <Pagination.Next
          disabled={pagination.page === pagination.pages}
          onClick={() => handlePageChange(pagination.page + 1)}
        />
        <Pagination.Last
          disabled={pagination.page === pagination.pages}
          onClick={() => handlePageChange(pagination.pages)}
        />
      </Pagination>
    </div>
  );
};

export default TranslationList;
