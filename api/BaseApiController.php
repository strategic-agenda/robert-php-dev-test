<?php

/**
 * BaseApiController - Abstract class for handling common API operations
 *
 * This class provides a foundation for all API controllers with shared
 * functionality for CRUD operations, request handling, and responses.
 */
abstract class BaseApiController
{
    protected ?PDO $db;
    protected ?int $resourceId;
    protected ?array $requestBody;
    protected int $userId;

    /**
     * Constructor - initializes the controller and handles the request
     */
    public function __construct()
    {
        setupApiHeaders();
        $this->db = getDbConnection();
        $this->resourceId = $this->getResourceIdFromUri();
        $this->requestBody = parseJsonBody();
        $this->userId = getCurrentUserId();

        try {
            $this->handleRequest();
        } catch (Exception $e) {
            handleApiError($e);
        } finally {
            closeDbConnection($this->db);
        }
    }

    /**
     * Handles the HTTP request based on method
     */
    protected function handleRequest(): void
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                $this->handleGet();
                break;
            case 'POST':
                $this->handlePost();
                break;
            case 'PUT':
                if (!$this->resourceId) {
                    sendJsonResponse(['error' => 'Resource ID is required for updates'], 400, $this->db);
                    exit;
                }
                $this->handlePut();
                break;
            case 'DELETE':
                if (!$this->resourceId) {
                    sendJsonResponse(['error' => 'Resource ID is required for deletion'], 400, $this->db);
                    exit;
                }
                $this->handleDelete();
                break;
            default:
                sendJsonResponse(['error' => 'Method not allowed'], 405, $this->db);
                break;
        }
    }

    /**
     * Abstract methods to be implemented by child controllers
     */
    abstract protected function handleGet(): void;

    abstract protected function handlePost(): void;

    abstract protected function handlePut(): void;

    abstract protected function handleDelete(): void;

    /**
     * Extract resource ID from URI using the pattern from child class
     *
     * @return int|null The extracted ID or null if not found
     */
    protected function getResourceIdFromUri(): ?int
    {
        $pattern = $this->getResourcePattern();
        $requestUri = $_SERVER['REQUEST_URI'];
        $id = null;

        if (preg_match($pattern, $requestUri, $matches)) {
            $id = (int)$matches[1];
        }

        return $id;
    }

    /**
     * Get the regex pattern for extracting resource ID from URI
     *
     * @return string Regex pattern
     */
    abstract protected function getResourcePattern(): string;

    /**
     * Check if a resource exists in a given table
     *
     * @param int $id Resource ID
     * @param string $table Table name
     * @return bool True if resource exists
     */
    protected function resourceExists(int $id, string $table): bool
    {
        $stmt = $this->db->prepare("SELECT id FROM {$table} WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return (bool)$stmt->fetch();
    }

    /**
     * Get paginated results for a list endpoint
     *
     * @param string $baseQuery The base SQL query without ORDER BY and LIMIT
     * @param string $countQuery The SQL query to count total records
     * @param array $params Query parameters for prepared statement
     * @param string $orderBy The ORDER BY clause
     * @param int $defaultPerPage Default number of items per page
     * @return array Paginated results with pagination info
     */
    protected function getPaginatedResults(
        string $baseQuery,
        string $countQuery,
        array $params = [],
        string $orderBy = 'id DESC',
        int $defaultPerPage = 10
    ): array {
        // Get pagination parameters
        $pagination = getPaginationParams($_GET, $defaultPerPage);

        // Get total count
        $countStmt = $this->db->prepare($countQuery);
        $this->bindParams($countStmt, $params);
        $countStmt->execute();
        $totalCount = (int)$countStmt->fetchColumn();

        // Get paginated results
        $query = $baseQuery . " ORDER BY {$orderBy} LIMIT :offset, :limit";
        $stmt = $this->db->prepare($query);
        $this->bindParams($stmt, $params);
        $stmt->bindParam(':offset', $pagination['offset'], PDO::PARAM_INT);
        $stmt->bindParam(':limit', $pagination['perPage'], PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'data' => $results,
            'pagination' => formatPaginationInfo($pagination['page'], $pagination['perPage'], $totalCount)
        ];
    }

    /**
     * Bind multiple parameters to a prepared statement
     *
     * @param PDOStatement $stmt Prepared statement
     * @param array $params Parameters to bind
     */
    protected function bindParams(\PDOStatement $stmt, array $params): void
    {
        foreach ($params as $key => $value) {
            if (is_bool($value)) {
                $stmt->bindValue($key, $value, PDO::PARAM_BOOL);
            } else {
                $stmt->bindValue($key, $value);
            }
        }
    }

    /**
     * Build a WHERE clause from search parameters
     *
     * @param array $searchFields Array of field => value pairs to search
     * @param array $params Reference to params array to be populated
     * @param string $operator SQL operator to use (AND or OR)
     * @return string WHERE clause
     */
    protected function buildSearchWhereClause(array $searchFields, array &$params, string $operator = 'OR'): string
    {
        if (empty($searchFields)) {
            return '';
        }

        $conditions = [];
        foreach ($searchFields as $field => $value) {
            $paramName = ':' . str_replace('.', '_', $field);
            $conditions[] = "$field LIKE $paramName";
            $params[$paramName] = "%$value%";
        }

        return 'WHERE ' . implode(" $operator ", $conditions);
    }
}
