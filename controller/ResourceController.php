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

            // Insert into library_resources
            $query = "INSERT INTO library_resources (title, accession_number, category, status) 
                     VALUES (:title, :accession_number, :category, :status)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":title", $data['title']);
            $stmt->bindParam(":accession_number", $data['accession_number']);
            $stmt->bindParam(":category", $data['category']);
            $status = 'available';
            $stmt->bindParam(":status", $status);
            $stmt->execute();
            
            $resource_id = $this->conn->lastInsertId();

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
            return false;
        }
    }

    public function getResources($type = 'book') {
        try {
            $query = "SELECT lr.*, b.* 
                     FROM library_resources lr 
                     LEFT JOIN books b ON lr.resource_id = b.resource_id 
                     ORDER BY lr.resource_id DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
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
            $this->conn->rollBack();
            return false;
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
            $query_total = "SELECT COUNT(*) as total_books FROM library_resources";
            $stmt_total = $this->conn->prepare($query_total);
            $stmt_total->execute();
            $total_books = $stmt_total->fetch(PDO::FETCH_ASSOC)['total_books'];
    
            // Available Books
            $query_available = "SELECT COUNT(*) as available_books 
                                FROM library_resources 
                                WHERE status = 'available'";
            $stmt_available = $this->conn->prepare($query_available);
            $stmt_available->execute();
            $available_books = $stmt_available->fetch(PDO::FETCH_ASSOC)['available_books'];
    
            // Borrowed Books (based on status)
            $query_borrowed = "SELECT COUNT(*) as borrowed_books 
                               FROM library_resources 
                               WHERE status = 'borrowed'";
            $stmt_borrowed = $this->conn->prepare($query_borrowed);
            $stmt_borrowed->execute();
            $borrowed_books = $stmt_borrowed->fetch(PDO::FETCH_ASSOC)['borrowed_books'];
    
            // Overdue Books (if you have a separate tracking mechanism)
            $query_overdue = "SELECT COUNT(*) as overdue_books 
                              FROM library_resources 
                              WHERE status = 'overdue'";
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
        $query = "SELECT MONTH(borrow_date) as month, COUNT(*) as count 
                  FROM borrowings 
                  WHERE YEAR(borrow_date) = :year 
                  GROUP BY MONTH(borrow_date)";
        
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
    }
}