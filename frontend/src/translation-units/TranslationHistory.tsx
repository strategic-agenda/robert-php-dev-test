import { useState, useEffect } from "react";
import { Card, Button, Spinner, Alert } from "react-bootstrap";
import translationService from "../services/translationService";

interface TranslationHistoryProps {
  unitId: number;
  targetLanguageId: number;
  onClose: () => void;
}

interface HistoryEntry {
  field: string;
  old_value: string;
  new_value: string;
  changed_at: string;
  metadata?: {
    translated_by: number;
    reviewed_by?: number;
    status?: string;
  };
}

const TranslationHistory: React.FC<TranslationHistoryProps> = ({
  unitId,
  targetLanguageId,
  onClose,
}) => {
  const [history, setHistory] = useState<HistoryEntry[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchHistory = async () => {
      try {
        setLoading(true);
        const historyData = await translationService.getTranslationHistory(
          unitId
        );

        // Filter history entries related to the target language
        const filteredHistory = historyData.filter((entry: HistoryEntry) =>
          entry.field.includes(`translation_${targetLanguageId}`)
        );

        setHistory(filteredHistory);
        setError(null);
      } catch (err) {
        setError("Error fetching translation history. Please try again.");
        console.error("Error fetching translation history:", err);
      } finally {
        setLoading(false);
      }
    };

    fetchHistory();
  }, [unitId, targetLanguageId]);

  // Format date for display
  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString();
  };

  if (loading) {
    return (
      <div className="text-center p-4">
        <Spinner animation="border" />
        <p>Loading translation history...</p>
      </div>
    );
  }

  if (error) {
    return (
      <div className="p-4">
        <Alert variant="danger">{error}</Alert>
        <Button variant="secondary" onClick={onClose}>
          Close
        </Button>
      </div>
    );
  }

  return (
    <div className="translation-history p-3">
      <div className="d-flex justify-content-between align-items-center mb-4">
        <h4>Translation History</h4>
        <Button variant="secondary" onClick={onClose}>
          Close
        </Button>
      </div>

      {history.length === 0 ? (
        <Alert variant="info">No history found for this translation.</Alert>
      ) : (
        <div className="history-entries">
          {history.map((entry, index) => (
            <Card key={index} className="mb-3">
              <Card.Header className="d-flex justify-content-between">
                <span>Version {history.length - index}</span>
                <span>{formatDate(entry.changed_at)}</span>
              </Card.Header>
              <Card.Body>
                <div className="metadata mb-2">
                  {entry.metadata && (
                    <>
                      <div>
                        Translated by: User ID {entry.metadata.translated_by}
                      </div>
                      {entry.metadata.reviewed_by && (
                        <div>
                          Reviewed by: User ID {entry.metadata.reviewed_by}
                        </div>
                      )}
                      {entry.metadata.status && (
                        <div>Status: {entry.metadata.status}</div>
                      )}
                    </>
                  )}
                </div>

                <div className="content-comparison">
                  <div className="old-content mb-2">
                    <h6>Previous Content:</h6>
                    <div className="p-2 bg-light rounded">
                      {entry.old_value}
                    </div>
                  </div>

                  <div className="new-content">
                    <h6>New Content:</h6>
                    <div className="p-2 bg-light rounded">
                      {entry.new_value}
                    </div>
                  </div>
                </div>
              </Card.Body>
            </Card>
          ))}
        </div>
      )}
    </div>
  );
};

export default TranslationHistory;
