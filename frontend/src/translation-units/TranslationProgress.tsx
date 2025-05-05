import { useState, useEffect } from "react";
import { Card, ProgressBar, Badge } from "react-bootstrap";
import translationService, {
  type TranslationUnit,
} from "../services/translationService";
import translationUtils from "../utils/translationUtils";

interface TranslationProgressProps {
  documentId: number;
  targetLanguageId: number;
}

const TranslationProgress: React.FC<TranslationProgressProps> = ({
  documentId,
  targetLanguageId,
}) => {
  const [units, setUnits] = useState<TranslationUnit[]>([]);
  const [loading, setLoading] = useState<boolean>(true);
  const [error, setError] = useState<string | null>(null);
  const [progress, setProgress] = useState<{
    total: number;
    translated: number;
    approved: number;
    reviewed: number;
    rejected: number;
    draft: number;
    percentage: number;
  }>({
    total: 0,
    translated: 0,
    approved: 0,
    reviewed: 0,
    rejected: 0,
    draft: 0,
    percentage: 0,
  });

  useEffect(() => {
    const fetchData = async () => {
      try {
        setLoading(true);

        // Fetch all units with a large limit
        const response = await translationService.getTranslationsByDocument(
          documentId,
          1,
          1000
        );

        setUnits(response.units);

        // Calculate progress
        const calculatedProgress = translationUtils.getTranslationProgress(
          response.units,
          targetLanguageId
        );

        setProgress(calculatedProgress);
        setError(null);
      } catch (err) {
        setError("Error loading translation progress");
        console.error(err);
      } finally {
        setLoading(false);
      }
    };

    fetchData();
  }, [documentId, targetLanguageId]);

  if (loading) {
    return (
      <Card className="mb-4">
        <Card.Body>Loading progress...</Card.Body>
      </Card>
    );
  }

  if (error) {
    return (
      <Card className="mb-4 bg-danger text-white">
        <Card.Body>{error}</Card.Body>
      </Card>
    );
  }

  return (
    <Card className="mb-4">
      <Card.Header>Translation Progress</Card.Header>
      <Card.Body>
        <div className="mb-3">
          <ProgressBar>
            <ProgressBar
              variant="success"
              now={(progress.approved / progress.total) * 100}
              key={1}
            />
            <ProgressBar
              variant="info"
              now={(progress.reviewed / progress.total) * 100}
              key={2}
            />
            <ProgressBar
              variant="secondary"
              now={(progress.draft / progress.total) * 100}
              key={3}
            />
            <ProgressBar
              variant="danger"
              now={(progress.rejected / progress.total) * 100}
              key={4}
            />
          </ProgressBar>
          <div className="text-center mt-1">
            <strong>{progress.percentage}%</strong> complete (
            {progress.translated} of {progress.total} units)
          </div>
        </div>

        <div className="d-flex flex-wrap justify-content-between">
          <div className="me-3 mb-2">
            <Badge bg="success" className="me-1">
              {progress.approved}
            </Badge>
            Approved
          </div>
          <div className="me-3 mb-2">
            <Badge bg="info" className="me-1">
              {progress.reviewed}
            </Badge>
            Reviewed
          </div>
          <div className="me-3 mb-2">
            <Badge bg="secondary" className="me-1">
              {progress.draft}
            </Badge>
            Draft
          </div>
          <div className="me-3 mb-2">
            <Badge bg="danger" className="me-1">
              {progress.rejected}
            </Badge>
            Rejected
          </div>
          <div className="mb-2">
            <Badge bg="warning" className="me-1">
              {progress.total - progress.translated}
            </Badge>
            Not translated
          </div>
        </div>
      </Card.Body>
    </Card>
  );
};

export default TranslationProgress;
