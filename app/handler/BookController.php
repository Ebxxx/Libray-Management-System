<?php
require_once 'Session.php';
require_once '../config/Database.php';

class BookController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function createBook($bookData) {
        try {
            $this->conn->beginTransaction();

            // Log incoming data
            error_log("Creating book with data: " . print_r($bookData, true));

            // Handle image upload
            $coverImage = null;
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = '../uploads/covers/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileExtension = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
                $fileName = uniqid('cover_') . '.' . $fileExtension;
                $uploadPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadPath)) {
                    $coverImage = 'uploads/covers/' . $fileName;
                    error_log("Cover image uploaded successfully: " . $coverImage);
                } else {
                    error_log("Failed to move uploaded file to: " . $uploadPath);
                }
            }

            // First, insert into library_resources - automatically set type to 'book'
            $resourceQuery = "INSERT INTO library_resources 
                              (title, accession_number, category, status, cover_image) 
                              VALUES (:title, :accession_number, 'book', 'available', :cover_image)
                              RETURNING resource_id";
            
            error_log("Executing resource query: " . $resourceQuery);
            error_log("Resource parameters: " . print_r([
                'title' => $bookData['title'],
                'accession_number' => $bookData['accession_number'],
                'type' => 'book',
                'cover_image' => $coverImage
            ], true));

            $resourceStmt = $this->conn->prepare($resourceQuery);
            $resourceStmt->bindParam(":title", $bookData['title']);
            $resourceStmt->bindParam(":accession_number", $bookData['accession_number']);
            $resourceStmt->bindParam(":cover_image", $coverImage);
            $resourceStmt->execute();

            // Get the last inserted resource_id
            $result = $resourceStmt->fetch(PDO::FETCH_ASSOC);
            if (!$result) {
                throw new PDOException("Failed to get resource_id after insert");
            }
            $resourceId = $result['resource_id'];
            error_log("Resource created with ID: " . $resourceId);

            // Then, insert into books
            $bookQuery = "INSERT INTO books 
                          (resource_id, author, isbn, publisher, edition, publication_date, type) 
                          VALUES (:resource_id, :author, :isbn, :publisher, :edition, :publication_date, :type)";
            
            error_log("Executing book query: " . $bookQuery);
            error_log("Book parameters: " . print_r([
                'resource_id' => $resourceId,
                'author' => $bookData['author'],
                'isbn' => $bookData['isbn'],
                'publisher' => $bookData['publisher'],
                'edition' => $bookData['edition'],
                'publication_date' => $bookData['publication_date'],
                'type' => $bookData['category']
            ], true));

            $bookStmt = $this->conn->prepare($bookQuery);
            $bookStmt->bindParam(":resource_id", $resourceId);
            $bookStmt->bindParam(":author", $bookData['author']);
            $bookStmt->bindParam(":isbn", $bookData['isbn']);
            $bookStmt->bindParam(":publisher", $bookData['publisher']);
            $bookStmt->bindParam(":edition", $bookData['edition']);
            $bookStmt->bindParam(":publication_date", $bookData['publication_date']);
            $bookStmt->bindParam(":type", $bookData['category']);
            $bookStmt->execute();

            // Commit transaction
            $this->conn->commit();
            error_log("Book created successfully");

            return true;
        } catch (PDOException $e) {
            // Rollback transaction
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Create book error: " . $e->getMessage());
            error_log("Error trace: " . $e->getTraceAsString());
            throw $e; // Re-throw the exception to handle it in the calling code
        }
    }

    public function getBooks() {
        try {
            $query = "SELECT lr.resource_id, lr.title, lr.accession_number, lr.category as resource_type, lr.status, lr.cover_image,
                             b.author, b.isbn, b.publisher, b.edition, b.publication_date, b.type
                      FROM library_resources lr
                      JOIN books b ON lr.resource_id = b.resource_id
                      ORDER BY lr.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get books error: " . $e->getMessage());
            return [];
        }
    }

    public function getBookById($resourceId) {
        try {
            $query = "SELECT lr.resource_id, lr.title, lr.accession_number, lr.category as resource_type, lr.status, lr.cover_image,
                             b.author, b.isbn, b.publisher, b.edition, b.publication_date, b.type
                      FROM library_resources lr
                      JOIN books b ON lr.resource_id = b.resource_id
                      WHERE lr.resource_id = :resource_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":resource_id", $resourceId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get book by ID error: " . $e->getMessage());
            return null;
        }
    }

    public function updateBook($resourceId, $bookData) {
        try {
            $this->conn->beginTransaction();

            // Handle image upload
            $coverImage = null;
            if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                // Delete old image if exists
                $oldImage = $this->getBookById($resourceId)['cover_image'];
                if ($oldImage && file_exists('../' . $oldImage)) {
                    unlink('../' . $oldImage);
                }

                $uploadDir = '../uploads/covers/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileExtension = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
                $fileName = uniqid('cover_') . '.' . $fileExtension;
                $uploadPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadPath)) {
                    $coverImage = 'uploads/covers/' . $fileName;
                }
            }

            // Update library_resources - keep type as 'book'
            $resourceQuery = "UPDATE library_resources 
                              SET title = :title, 
                                  accession_number = :accession_number" .
                                  ($coverImage ? ", cover_image = :cover_image" : "") . 
                              " WHERE resource_id = :resource_id";
            $resourceStmt = $this->conn->prepare($resourceQuery);
            $resourceStmt->bindParam(":title", $bookData['title']);
            $resourceStmt->bindParam(":accession_number", $bookData['accession_number']);
            $resourceStmt->bindParam(":resource_id", $resourceId);
            if ($coverImage) {
                $resourceStmt->bindParam(":cover_image", $coverImage);
            }
            $resourceStmt->execute();

            // Update books
            $bookQuery = "UPDATE books 
                          SET author = :author, 
                              isbn = :isbn, 
                              publisher = :publisher, 
                              edition = :edition, 
                              publication_date = :publication_date,
                              type = :type
                          WHERE resource_id = :resource_id";
            $bookStmt = $this->conn->prepare($bookQuery);
            $bookStmt->bindParam(":author", $bookData['author']);
            $bookStmt->bindParam(":isbn", $bookData['isbn']);
            $bookStmt->bindParam(":publisher", $bookData['publisher']);
            $bookStmt->bindParam(":edition", $bookData['edition']);
            $bookStmt->bindParam(":publication_date", $bookData['publication_date']);
            $bookStmt->bindParam(":type", $bookData['category']);
            $bookStmt->bindParam(":resource_id", $resourceId);
            $bookStmt->execute();

            // Commit transaction
            $this->conn->commit();

            return true;
        } catch (PDOException $e) {
            // Rollback transaction
            $this->conn->rollBack();
            error_log("Update book error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteBook($resourceId) {
        try {
            // Check if book is currently borrowed
            $borrowQuery = "SELECT COUNT(*) as borrow_count 
                           FROM borrowings 
                           WHERE resource_id = :resource_id 
                           AND status = 'active'";
            $borrowStmt = $this->conn->prepare($borrowQuery);
            $borrowStmt->bindParam(":resource_id", $resourceId);
            $borrowStmt->execute();
            $borrowResult = $borrowStmt->fetch(PDO::FETCH_ASSOC);

            if ($borrowResult['borrow_count'] > 0) {
                throw new Exception("Cannot delete: Book is currently borrowed");
            }

            // Check current status
            $statusQuery = "SELECT status FROM library_resources WHERE resource_id = :resource_id";
            $statusStmt = $this->conn->prepare($statusQuery);
            $statusStmt->bindParam(":resource_id", $resourceId);
            $statusStmt->execute();
            $statusResult = $statusStmt->fetch(PDO::FETCH_ASSOC);

            $currentStatus = $statusResult['status'];
            if (!is_null($currentStatus) && $currentStatus !== 'available') {
                throw new Exception("Cannot delete: Book status must be 'available' or NULL");
            }

            // Begin transaction
            $this->conn->beginTransaction();

            // Delete from books first
            $bookQuery = "DELETE FROM books WHERE resource_id = :resource_id";
            $bookStmt = $this->conn->prepare($bookQuery);
            $bookStmt->bindParam(":resource_id", $resourceId);
            $bookStmt->execute();

            // Update library_resources status to 'maintenance'
            $resourceQuery = "UPDATE library_resources 
                             SET status = 'maintenance' 
                             WHERE resource_id = :resource_id";
            $resourceStmt = $this->conn->prepare($resourceQuery);
            $resourceStmt->bindParam(":resource_id", $resourceId);
            $resourceStmt->execute();

            // Commit transaction
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            // Rollback transaction if started
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Delete book error: " . $e->getMessage());
            throw $e;
        }
    }

    // Generate unique Accession Number
    public function generateAccessionNumber($resourceType = 'B') {
        try {
            $currentYear = date('Y');
            $prefix = $resourceType . '-' . $currentYear . '-';
            
            $query = "SELECT MAX(accession_number) as last_number 
                    FROM library_resources 
                    WHERE accession_number LIKE :prefix";
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(":prefix", $prefix . '%');
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['last_number']) {
                // Extract the last sequential number and increment
                $lastNumber = intval(substr($result['last_number'], -3));
                $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            } else {
                $newNumber = '001';
            }
            
            return $prefix . $newNumber;
        } catch (PDOException $e) {
            error_log("Generate Accession Number error: " . $e->getMessage());
            return null;
        }
    }

    // Get total books
    public function getTotalBooks() {
        try {
            $query = "SELECT COUNT(*) as total FROM books";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'];
        } catch (PDOException $e) {
            error_log("Get total books error: " . $e->getMessage());
            return 0;
        }
    }
}