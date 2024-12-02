<?php
require_once '../config/Database.php';
require_once '../controller/UserController.php';

class ResourceController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Existing methods...

    public function borrowResource($userId, $resourceType, $resourceId) {
        try {
            // Start a database transaction
            $this->conn->beginTransaction();

            // Check user's current borrowed items and max book limit
            $userController = new UserController();
            $user = $userController->getUserById($userId);
            
            if (!$user) {
                throw new Exception("User not found");
            }

            // Validate resource type
            $validResourceTypes = ['books', 'periodicals', 'media_resources'];
            if (!in_array($resourceType, $validResourceTypes)) {
                throw new Exception("Invalid resource type");
            }

            // Check if the resource exists and is available
            $checkQuery = "SELECT * FROM $resourceType WHERE id = :resource_id AND status = 'available'";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bindParam(":resource_id", $resourceId);
            $checkStmt->execute();

            if ($checkStmt->rowCount() == 0) {
                throw new Exception("Resource not available for borrowing");
            }

            // Count current borrowed items
            $countQuery = "SELECT COUNT(*) as borrowed_count FROM borrowings 
                           WHERE user_id = :user_id AND return_date IS NULL";
            $countStmt = $this->conn->prepare($countQuery);
            $countStmt->bindParam(":user_id", $userId);
            $countStmt->execute();
            $borrowedCount = $countStmt->fetch(PDO::FETCH_ASSOC)['borrowed_count'];

            // Check if user has reached max borrowing limit
            if ($borrowedCount >= $user['max_books']) {
                throw new Exception("Borrowing limit reached");
            }

            // Insert borrowing record
            $borrowQuery = "INSERT INTO borrowings 
                            (user_id, resource_type, resource_id, borrow_date, due_date) 
                            VALUES 
                            (:user_id, :resource_type, :resource_id, CURRENT_DATE, 
                             DATE_ADD(CURRENT_DATE, INTERVAL 14 DAY))";
            $borrowStmt = $this->conn->prepare($borrowQuery);
            $borrowStmt->bindParam(":user_id", $userId);
            $borrowStmt->bindParam(":resource_type", $resourceType);
            $borrowStmt->bindParam(":resource_id", $resourceId);
            $borrowStmt->execute();

            // Update resource status to 'borrowed'
            $updateQuery = "UPDATE $resourceType SET status = 'borrowed' WHERE id = :resource_id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(":resource_id", $resourceId);
            $updateStmt->execute();

            // Commit the transaction
            $this->conn->commit();

            return true;
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $this->conn->rollBack();
            error_log("Borrow resource error: " . $e->getMessage());
            return false;
        }
    }

    public function returnResource($borrowingId) {
        try {
            // Start a database transaction
            $this->conn->beginTransaction();

            // Retrieve borrowing details
            $borrowQuery = "SELECT * FROM borrowings WHERE id = :borrowing_id";
            $borrowStmt = $this->conn->prepare($borrowQuery);
            $borrowStmt->bindParam(":borrowing_id", $borrowingId);
            $borrowStmt->execute();
            $borrowing = $borrowStmt->fetch(PDO::FETCH_ASSOC);

            if (!$borrowing) {
                throw new Exception("Borrowing record not found");
            }

            // Update borrowing record with return date
            $returnQuery = "UPDATE borrowings 
                            SET return_date = CURRENT_DATE, 
                                status = 'returned' 
                            WHERE id = :borrowing_id";
            $returnStmt = $this->conn->prepare($returnQuery);
            $returnStmt->bindParam(":borrowing_id", $borrowingId);
            $returnStmt->execute();

            // Update resource status back to 'available'
            $resourceType = $borrowing['resource_type'];
            $resourceId = $borrowing['resource_id'];
            $updateQuery = "UPDATE $resourceType SET status = 'available' WHERE id = :resource_id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(":resource_id", $resourceId);
            $updateStmt->execute();

            // Check for late return and calculate potential fine
            $dueDate = new DateTime($borrowing['due_date']);
            $returnDate = new DateTime();
            
            if ($returnDate > $dueDate) {
                $interval = $dueDate->diff($returnDate);
                $daysLate = $interval->days;
                
                // Assume a fine of $0.50 per day late
                $fine = $daysLate * 0.50;
                
                // Insert fine record
                $fineQuery = "INSERT INTO fines 
                              (borrowing_id, user_id, amount, reason) 
                              VALUES 
                              (:borrowing_id, :user_id, :amount, 'Late Return')";
                $fineStmt = $this->conn->prepare($fineQuery);
                $fineStmt->bindParam(":borrowing_id", $borrowingId);
                $fineStmt->bindParam(":user_id", $borrowing['user_id']);
                $fineStmt->bindParam(":amount", $fine);
                $fineStmt->execute();
            }

            // Commit the transaction
            $this->conn->commit();

            return true;
        } catch (Exception $e) {
            // Rollback the transaction in case of error
            $this->conn->rollBack();
            error_log("Return resource error: " . $e->getMessage());
            return false;
        }
    }

    public function getUserBorrowings($userId) {
        try {
            $query = "SELECT b.id, b.resource_type, b.resource_id, b.borrow_date, b.due_date, 
                             r.title, r.status AS resource_status
                      FROM borrowings b
                      JOIN (
                          SELECT id, title, 'books' AS type FROM books
                          UNION ALL
                          SELECT id, title, 'periodicals' AS type FROM periodicals
                          UNION ALL
                          SELECT id, title, 'media_resources' AS type FROM media_resources
                      ) r ON b.resource_id = r.id AND b.resource_type = r.type
                      WHERE b.user_id = :user_id AND b.return_date IS NULL
                      ORDER BY b.due_date";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get user borrowings error: " . $e->getMessage());
            return [];
        }
    }

    public function getBookStatistics() {
        try {
            // Total books
            $totalQuery = "SELECT 
                (SELECT COUNT(*) FROM books) +
                (SELECT COUNT(*) FROM periodicals) +
                (SELECT COUNT(*) FROM media_resources) AS total_books,
                
                (SELECT COUNT(*) FROM books WHERE status = 'available') +
                (SELECT COUNT(*) FROM periodicals WHERE status = 'available') +
                (SELECT COUNT(*) FROM media_resources WHERE status = 'available') AS available_books,
                
                (SELECT COUNT(*) FROM books WHERE status = 'borrowed') +
                (SELECT COUNT(*) FROM periodicals WHERE status = 'borrowed') +
                (SELECT COUNT(*) FROM media_resources WHERE status = 'borrowed') AS borrowed_books,
                
                (SELECT COUNT(*) FROM borrowings 
                 WHERE due_date < CURRENT_DATE AND return_date IS NULL) AS overdue_books";
            
            $stmt = $this->conn->prepare($totalQuery);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get book statistics error: " . $e->getMessage());
            return [
                'total_books' => 0,
                'available_books' => 0,
                'borrowed_books' => 0,
                'overdue_books' => 0
            ];
        }
    }

    public function getBookCategoriesDistribution() {
        try {
            // Combine categories from different resource types
            $query = "
                (SELECT category, COUNT(*) as count FROM books GROUP BY category)
                UNION ALL
                (SELECT category, COUNT(*) as count FROM periodicals GROUP BY category)
                UNION ALL
                (SELECT category, COUNT(*) as count FROM media_resources GROUP BY category)
            ";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get book categories distribution error: " . $e->getMessage());
            return [];
        }
    }
}