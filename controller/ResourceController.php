<?php
require_once '../config/Database.php';

class ResourceController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createResource($data, $type = 'book') {
        try {
            $this->conn->beginTransaction();

            // Validate category (as text, not enum)
            $validCategories = ['fiction', 'non-fiction', 'reference', 'academic'];
            $category = strtolower($data['category']);
            if (!in_array($category, $validCategories)) {
                throw new Exception("Invalid category. Must be one of: " . implode(', ', $validCategories));
            }

            // Validate status
            $validStatuses = ['available', 'unavailable', 'borrowed'];
            $status = strtolower($data['status'] ?? 'available');
            if (!in_array($status, $validStatuses)) {
                throw new Exception("Invalid status. Must be one of: " . implode(', ', $validStatuses));
            }

            // Handle file upload
            $cover_image = null;
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = dirname(__DIR__) . '/uploads/covers/';
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Generate unique filename
                $file_extension = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $file_extension;
                
                // Move uploaded file
                if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $upload_dir . $filename)) {
                    $cover_image = $filename;
                }
            }

            // Insert into library_resources with cover_image
            $query = "INSERT INTO library_resources (title, accession_number, category, status, cover_image) 
                     VALUES (:title, :accession_number, :category, 'available'::resource_status, :cover_image) 
                     RETURNING resource_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":title", $data['title']);
            $stmt->bindParam(":accession_number", $data['accession_number']);
            $stmt->bindParam(":category", $category);
            $stmt->bindParam(":cover_image", $cover_image);
            $stmt->execute();
            
            $resource_id = $stmt->fetch(PDO::FETCH_ASSOC)['resource_id'];

            // Insert into specific resource type table
            if ($type === 'book') {
                $query = "INSERT INTO books (resource_id, author, isbn, publisher, edition, publication_date) 
                         VALUES (:resource_id, :author, :isbn, :publisher, :edition, :publication_date)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":resource_id", $resource_id);
                $stmt->bindParam(":author", $data['author']);
                $stmt->bindParam(":isbn", $data['isbn']);
                $stmt->bindParam(":publisher", $data['publisher']);
                $stmt->bindParam(":edition", $data['edition']);
                $stmt->bindParam(":publication_date", $data['publication_date']);
                $stmt->execute();
            }

            $this->conn->commit();
            return true;
        } catch(PDOException $e) {
            $this->conn->rollBack();
            error_log("Error in createResource: " . $e->getMessage());
            throw new Exception("Failed to create resource: " . $e->getMessage());
        }
    }

    public function getResources($type = 'book') {
        try {
            $query = "SELECT 
                        lr.*,
                        CASE 
                            WHEN b.book_id IS NOT NULL THEN json_build_object(
                                'author', b.author,
                                'isbn', b.isbn,
                                'publisher', b.publisher,
                                'edition', b.edition,
                                'publication_date', b.publication_date
                            )
                            WHEN m.media_id IS NOT NULL THEN json_build_object(
                                'media_type', m.media_type,
                                'runtime', m.runtime,
                                'format', m.format
                            )
                            WHEN p.periodical_id IS NOT NULL THEN json_build_object(
                                'volume', p.volume,
                                'issue', p.issue,
                                'publication_date', p.publication_date
                            )
                        END as resource_details
                    FROM library_resources lr 
                    LEFT JOIN books b ON lr.resource_id = b.resource_id 
                    LEFT JOIN media_resources m ON lr.resource_id = m.resource_id
                    LEFT JOIN periodicals p ON lr.resource_id = p.resource_id
                    WHERE lr.status = 'available'::resource_status
                    AND NOT EXISTS (
                        SELECT 1 FROM borrowings br 
                        WHERE br.resource_id = lr.resource_id 
                        AND br.status IN ('pending'::borrowing_status, 'active'::borrowing_status, 'overdue'::borrowing_status)
                    )
                    AND CASE 
                        WHEN :type = 'book' THEN b.book_id IS NOT NULL
                        WHEN :type = 'media' THEN m.media_id IS NOT NULL
                        WHEN :type = 'periodical' THEN p.periodical_id IS NOT NULL
                        ELSE true
                    END
                    ORDER BY lr.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':type', $type);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Process the results to flatten the JSON resource_details
            foreach ($results as &$result) {
                if (isset($result['resource_details'])) {
                    $details = json_decode($result['resource_details'], true);
                    if ($details) {
                        $result = array_merge($result, $details);
                    }
                    unset($result['resource_details']);
                }
            }
            
            error_log("Retrieved " . count($results) . " resources of type: " . $type);
            return $results;
        } catch(PDOException $e) {
            error_log("Error in getResources: " . $e->getMessage());
            return [];
        }
    }

    public function generateAccessionNumber($type = 'B') {
        $year = date('Y');
        $query = "SELECT MAX(accession_number) as max_number 
                 FROM library_resources 
                 WHERE accession_number LIKE :prefix";
        $prefix = $type . "-" . $year . "-";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':prefix', $prefix . '%');
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['max_number'] === null) {
                return $prefix . "001";
            }
            
            $current_number = intval(substr($result['max_number'], -3));
            $next_number = str_pad($current_number + 1, 3, '0', STR_PAD_LEFT);
            
            return $prefix . $next_number;
        } catch(PDOException $e) {
            error_log("Error in generateAccessionNumber: " . $e->getMessage());
            return false;
        }
    }

    public function updateResource($resource_id, $data) {
        try {
            $this->conn->beginTransaction();

            // Update library_resources table
            $query = "UPDATE library_resources 
                      SET title = :title, category = :category 
                      WHERE resource_id = :resource_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":title", $data['title']);
            $stmt->bindParam(":category", $data['category']);
            $stmt->bindParam(":resource_id", $resource_id);
            $stmt->execute();

            // Update books table
            $query = "UPDATE books 
                      SET author = :author, 
                          isbn = :isbn, 
                          publisher = :publisher, 
                          edition = :edition, 
                          publication_date = :publication_date 
                      WHERE resource_id = :resource_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":author", $data['author']);
            $stmt->bindParam(":isbn", $data['isbn']);
            $stmt->bindParam(":publisher", $data['publisher']);
            $stmt->bindParam(":edition", $data['edition']);
            $stmt->bindParam(":publication_date", $data['publication_date']);
            $stmt->bindParam(":resource_id", $resource_id);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch(PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function deleteResource($resource_id) {
        try {
            // Check if resource is currently borrowed
            $borrowQuery = "SELECT COUNT(*) as borrow_count 
                           FROM borrowings 
                           WHERE resource_id = :resource_id 
                           AND status = 'active'";
            $borrowStmt = $this->conn->prepare($borrowQuery);
            $borrowStmt->bindParam(":resource_id", $resource_id);
            $borrowStmt->execute();
            $borrowResult = $borrowStmt->fetch(PDO::FETCH_ASSOC);

            if ($borrowResult['borrow_count'] > 0) {
                throw new Exception("Cannot delete: Resource is currently borrowed");
            }

            // Check current status
            $statusQuery = "SELECT status FROM library_resources WHERE resource_id = :resource_id";
            $statusStmt = $this->conn->prepare($statusQuery);
            $statusStmt->bindParam(":resource_id", $resource_id);
            $statusStmt->execute();
            $statusResult = $statusStmt->fetch(PDO::FETCH_ASSOC);

            $currentStatus = $statusResult['status'];
            if (!is_null($currentStatus) && $currentStatus !== 'available') {
                throw new Exception("Cannot delete: Resource status must be 'available' or NULL");
            }

            $this->conn->beginTransaction();

            // Delete from books table first
            $query = "DELETE FROM books WHERE resource_id = :resource_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":resource_id", $resource_id);
            $stmt->execute();

            // Delete from library_resources table
            $query = "DELETE FROM library_resources WHERE resource_id = :resource_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":resource_id", $resource_id);
            $stmt->execute();

            $this->conn->commit();
            return true;
        } catch(PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Delete resource error: " . $e->getMessage());
            throw $e;
        }
    }

    public function getResourceById($resource_id) {
        try {
            $query = "SELECT lr.*, b.* 
                     FROM library_resources lr 
                     LEFT JOIN books b ON lr.resource_id = b.resource_id 
                     WHERE lr.resource_id = :resource_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":resource_id", $resource_id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return false;
        }
    }

    public function getBookStatistics() {
        try {
            // Total Books
            $query_total = "SELECT COUNT(*) as total_books 
                           FROM library_resources 
                           WHERE category = 'book'";
            $stmt_total = $this->conn->prepare($query_total);
            $stmt_total->execute();
            $total_books = $stmt_total->fetch(PDO::FETCH_ASSOC)['total_books'];
    
            // Available Books
            $query_available = "SELECT COUNT(*) as available_books 
                              FROM library_resources 
                              WHERE category = 'book' 
                              AND status = 'available'::resource_status";
            $stmt_available = $this->conn->prepare($query_available);
            $stmt_available->execute();
            $available_books = $stmt_available->fetch(PDO::FETCH_ASSOC)['available_books'];
    
            // Borrowed Books
            $query_borrowed = "SELECT COUNT(*) as borrowed_books 
                             FROM library_resources 
                             WHERE category = 'book' 
                             AND status = 'borrowed'::resource_status";
            $stmt_borrowed = $this->conn->prepare($query_borrowed);
            $stmt_borrowed->execute();
            $borrowed_books = $stmt_borrowed->fetch(PDO::FETCH_ASSOC)['borrowed_books'];
    
            // Overdue Books
            $query_overdue = "SELECT COUNT(DISTINCT lr.resource_id) as overdue_books 
                            FROM library_resources lr
                            JOIN borrowings b ON lr.resource_id = b.resource_id
                            WHERE lr.category = 'book' 
                            AND b.status = 'overdue'::borrowing_status";
            $stmt_overdue = $this->conn->prepare($query_overdue);
            $stmt_overdue->execute();
            $overdue_books = $stmt_overdue->fetch(PDO::FETCH_ASSOC)['overdue_books'];
    
            return [
                'total_books' => $total_books,
                'available_books' => $available_books,
                'borrowed_books' => $borrowed_books,
                'overdue_books' => $overdue_books
            ];
        } catch(PDOException $e) {
            error_log("Error in getBookStatistics: " . $e->getMessage());
            return [
                'total_books' => 0,
                'available_books' => 0,
                'borrowed_books' => 0,
                'overdue_books' => 0
            ];
        }
    }
    
    // Method to get book categories distribution
    public function getBookCategoriesDistribution() {
        try {
            $query = "SELECT category, COUNT(*) as count 
                      FROM library_resources 
                      GROUP BY category";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            return [];
        }
    }
    
    public function getTotalBooks() {
        $bookController = new BookController();
        return $bookController->getTotalBooks();
    }

    public function getTotalMediaResources() {
        $mediaController = new MediaResourceController();
        return $mediaController->getTotalMediaResources();
    }

    public function getTotalPeriodicals() {
        $periodicalController = new PeriodicalController();
        return $periodicalController->getTotalPeriodicals();
    }

    

    public function getMonthlyBorrowings($year) {
        try {
            $query = "SELECT EXTRACT(MONTH FROM borrow_date) as month, COUNT(*) as count 
                     FROM borrowings 
                     WHERE EXTRACT(YEAR FROM borrow_date) = :year 
                     GROUP BY EXTRACT(MONTH FROM borrow_date)
                     ORDER BY month";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':year', $year);
            $stmt->execute();
            
            // Initialize array with zeros for all months
            $monthlyData = array_fill(0, 12, 0);
            
            // Fill in actual data
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $monthlyData[$row['month'] - 1] = (int)$row['count'];
            }
            
            return $monthlyData;
        } catch(PDOException $e) {
            error_log("Error in getMonthlyBorrowings: " . $e->getMessage());
            return array_fill(0, 12, 0);
        }
    }

    public function getMostBorrowedResources() {
        try {
            $sql = "WITH resource_counts AS (
                    SELECT 
                        lr.resource_id,
                        lr.title,
                        lr.cover_image,
                        CASE 
                            WHEN b2.book_id IS NOT NULL THEN 'book'
                            WHEN p.periodical_id IS NOT NULL THEN 'periodical'
                            WHEN m.media_id IS NOT NULL THEN 'media'
                        END as resource_type,
                        COUNT(br.borrowing_id) as borrow_count
                    FROM library_resources lr
                    LEFT JOIN borrowings br ON lr.resource_id = br.resource_id
                    LEFT JOIN books b2 ON lr.resource_id = b2.resource_id
                    LEFT JOIN periodicals p ON lr.resource_id = p.resource_id
                    LEFT JOIN media_resources m ON lr.resource_id = m.resource_id
                    GROUP BY 
                        lr.resource_id, 
                        lr.title,
                        lr.cover_image,
                        resource_type
                )
                SELECT * FROM resource_counts 
                WHERE borrow_count > 0
                ORDER BY borrow_count DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            
            $results = [
                'books' => ['title' => 'N/A', 'count' => 0, 'cover_image' => null],
                'periodicals' => ['title' => 'N/A', 'count' => 0, 'cover_image' => null],
                'media' => ['title' => 'N/A', 'count' => 0, 'cover_image' => null]
            ];
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                switch ($row['resource_type']) {
                    case 'book':
                        if ($row['borrow_count'] > ($results['books']['count'] ?? 0)) {
                            $results['books'] = [
                                'title' => $row['title'], 
                                'count' => $row['borrow_count'],
                                'cover_image' => $row['cover_image'],
                                'resource_id' => $row['resource_id']
                            ];
                        }
                        break;
                    case 'periodical':
                        if ($row['borrow_count'] > ($results['periodicals']['count'] ?? 0)) {
                            $results['periodicals'] = [
                                'title' => $row['title'], 
                                'count' => $row['borrow_count'],
                                'cover_image' => $row['cover_image'],
                                'resource_id' => $row['resource_id']
                            ];
                        }
                        break;
                    case 'media':
                        if ($row['borrow_count'] > ($results['media']['count'] ?? 0)) {
                            $results['media'] = [
                                'title' => $row['title'], 
                                'count' => $row['borrow_count'],
                                'cover_image' => $row['cover_image'],
                                'resource_id' => $row['resource_id']
                            ];
                        }
                        break;
                }
            }
            
            return $results;
        } catch(PDOException $e) {
            error_log("Error in getMostBorrowedResources: " . $e->getMessage());
            return [
                'books' => ['title' => 'N/A', 'count' => 0, 'cover_image' => null],
                'periodicals' => ['title' => 'N/A', 'count' => 0, 'cover_image' => null],
                'media' => ['title' => 'N/A', 'count' => 0, 'cover_image' => null]
            ];
        }
    }
}