<?php
require_once '../config/Database.php';
require_once '../controller/Session.php';

class BorrowingController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function borrowResource($user_id, $resource_id) {
        try {
            // Start a transaction
            $this->conn->beginTransaction();
    
            // Check if the user has reached their borrowing limit
            $user_query = "SELECT role, max_books FROM users WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($user_query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$user) {
                throw new Exception("User not found");
            }
    
            // Check current borrowed resources
            $current_borrowings_query = "SELECT COUNT(*) as current_borrows 
                                          FROM borrowings 
                                          WHERE user_id = :user_id AND status = 'active'";
            $stmt = $this->conn->prepare($current_borrowings_query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            $current_borrows = $stmt->fetch(PDO::FETCH_ASSOC)['current_borrows'];
    
            if ($current_borrows >= $user['max_books']) {
                throw new Exception("Borrowing limit reached");
            }
    
            // Check resource availability
            $resource_query = "SELECT status FROM library_resources 
                               WHERE resource_id = :resource_id AND status = 'available'";
            $stmt = $this->conn->prepare($resource_query);
            $stmt->bindParam(":resource_id", $resource_id);
            $stmt->execute();
    
            if ($stmt->rowCount() == 0) {
                throw new Exception("Resource not available");
            }
    
            // Calculate due date based on user role
            if ($user['role'] === 'student') {
                $due_date = date('Y-m-d H:i:s', strtotime('+3 days'));
            } elseif ($user['role'] === 'faculty') {
                $due_date = date('Y-m-d H:i:s', strtotime('+30 days'));
            } else {
                throw new Exception("Invalid user role");
            }
    
            // Insert borrowing record
            $borrow_query = "INSERT INTO borrowings (user_id, resource_id, due_date) 
                             VALUES (:user_id, :resource_id, :due_date)";
            $stmt = $this->conn->prepare($borrow_query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":resource_id", $resource_id);
            $stmt->bindParam(":due_date", $due_date);
            $stmt->execute();
    
            // Update resource status to borrowed
            $update_query = "UPDATE library_resources 
                             SET status = 'borrowed' 
                             WHERE resource_id = :resource_id";
            $stmt = $this->conn->prepare($update_query);
            $stmt->bindParam(":resource_id", $resource_id);
            $stmt->execute();
    
            // Commit transaction
            $this->conn->commit();
    
            return true;
        } catch (Exception $e) {
            // Rollback transaction
            $this->conn->rollBack();
            error_log("Borrowing error: " . $e->getMessage());
            return false;
        }
    }

    public function returnResource($borrowing_id) {
        try {
            // Start a transaction
            $this->conn->beginTransaction();
    
            // Retrieve borrowing details
            $borrow_query = "SELECT resource_id, user_id, due_date, status FROM borrowings 
                             WHERE borrowing_id = :borrowing_id AND status IN ('active', 'overdue')";
            $stmt = $this->conn->prepare($borrow_query);
            $stmt->bindParam(":borrowing_id", $borrowing_id);
            $stmt->execute();
            $borrowing = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$borrowing) {
                throw new Exception("Borrowing record not found");
            }
    
            // Calculate fine if overdue
            $current_date = date('Y-m-d H:i:s');
            $fine_amount = 0;
            $fine_rate = 1; // $1 per day overdue
    
            // Only calculate fine if the resource is past due date
            if (strtotime($current_date) > strtotime($borrowing['due_date'])) {
                $overdue_days = ceil((strtotime($current_date) - strtotime($borrowing['due_date'])) / (60 * 60 * 24));
                $fine_amount = $overdue_days * $fine_rate;
            }
    
            // Update borrowing record
            $return_query = "UPDATE borrowings 
                             SET return_date = :return_date, 
                                 status = 'returned', 
                                 fine_amount = :fine_amount 
                             WHERE borrowing_id = :borrowing_id";
            $stmt = $this->conn->prepare($return_query);
            $stmt->bindParam(":return_date", $current_date);
            $stmt->bindParam(":fine_amount", $fine_amount);
            $stmt->bindParam(":borrowing_id", $borrowing_id);
            $stmt->execute();
    
            // Update resource status back to available
            $update_query = "UPDATE library_resources 
                             SET status = 'available' 
                             WHERE resource_id = :resource_id";
            $stmt = $this->conn->prepare($update_query);
            $stmt->bindParam(":resource_id", $borrowing['resource_id']);
            $stmt->execute();
    
            // Commit transaction
            $this->conn->commit();
    
            return [
                'success' => true,
                'fine_amount' => $fine_amount,
                'return_date' => $current_date
            ];
        } catch (Exception $e) {
            // Rollback transaction
            $this->conn->rollBack();
            error_log("Return resource error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getAllBorrowings() {
        try {
            $conn = (new Database())->getConnection();
            $query = "SELECT 
                        b.borrowing_id, 
                        b.borrow_date, 
                        b.due_date, 
                        b.status,
                        b.fine_amount,
                        u.user_id,
                        u.first_name, 
                        u.last_name, 
                        u.email, 
                        u.role,
                        lr.title AS resource_title,
                        lr.category AS resource_type,
                        CASE 
                            WHEN lr.category = 'book' THEN bk.author
                            WHEN lr.category = 'periodical' THEN p.volume
                            WHEN lr.category = 'media' THEN m.media_type
                        END AS resource_detail,
                        CASE 
                            WHEN lr.category = 'book' THEN bk.isbn
                            WHEN lr.category = 'periodical' THEN p.issn
                            WHEN lr.category = 'media' THEN m.format
                        END AS resource_identifier
                    FROM borrowings b
                    JOIN users u ON b.user_id = u.user_id
                    JOIN library_resources lr ON b.resource_id = lr.resource_id
                    LEFT JOIN books bk ON lr.resource_id = bk.resource_id AND lr.category = 'book'
                    LEFT JOIN periodicals p ON lr.resource_id = p.resource_id AND lr.category = 'periodical'
                    LEFT JOIN media_resources m ON lr.resource_id = m.resource_id AND lr.category = 'media'
                    WHERE b.status IN ('active', 'overdue')
                    ORDER BY b.due_date ASC";
            
            $stmt = $conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Borrowing monitoring error: " . $e->getMessage());
            return [];
        }
    }

    // // Borrowing of students and faculty
    // public function getAllBorrowings() {
    //     try {
    //         $conn = (new Database())->getConnection();
    //         $query = "SELECT 
    //                     b.borrowing_id, 
    //                     b.borrow_date, 
    //                     b.due_date, 
    //                     b.status,
    //                     b.fine_amount,
    //                     u.user_id,
    //                     u.first_name, 
    //                     u.last_name, 
    //                     u.email, 
    //                     u.role,
    //                     lr.title AS resource_title,
    //                     lr.category AS resource_type
    //                 FROM borrowings b
    //                 JOIN users u ON b.user_id = u.user_id
    //                 JOIN library_resources lr ON b.resource_id = lr.resource_id
    //                 WHERE b.status IN ('active', 'overdue')
    //                 ORDER BY b.due_date ASC";
            
    //         $stmt = $conn->prepare($query);
    //         $stmt->execute();
    //         return $stmt->fetchAll(PDO::FETCH_ASSOC);
    //     } catch (PDOException $e) {
    //         error_log("Borrowing monitoring error: " . $e->getMessage());
    //         return [];
    //     }
    // }

    public function calculateOverdueStatus($dueDate) {
        $now = new DateTime();
        $due = new DateTime($dueDate);
        
        if ($now > $due) {
            $interval = $now->diff($due);
            return [
                'status' => 'Overdue',
                'days_overdue' => $interval->days,
                'class' => 'text-danger'
            ];
        }
        
        $interval = $due->diff($now);
        if ($interval->days <= 3) {
            return [
                'status' => 'Due Soon',
                'days_remaining' => $interval->days,
                'class' => 'text-warning'
            ];
        }
        
        return [
            'status' => 'Active',
            'days_remaining' => $interval->days,
            'class' => 'text-success'
        ];
    }

    // public function calculateOverdueStatus($dueDate) {
    //     $now = new DateTime();
    //     $due = new DateTime($dueDate);
        
    //     if ($now > $due) {
    //         $interval = $now->diff($due);
    //         return [
    //             'status' => 'Overdue',
    //             'days_overdue' => $interval->days,
    //             'class' => 'text-danger'
    //         ];
    //     }
        
    //     $interval = $due->diff($now);
    //     if ($interval->days <= 3) {
    //         return [
    //             'status' => 'Due Soon',
    //             'days_remaining' => $interval->days,
    //             'class' => 'text-warning'
    //         ];
    //     }
        
    //     return [
    //         'status' => 'Active',
    //         'class' => 'text-success'
    //     ];
    // }
    

    public function getUserBorrowingHistory($user_id) {
        try {
            $query = "SELECT 
                        b.borrowing_id, 
                        lr.title, 
                        b.borrow_date, 
                        b.due_date, 
                        b.return_date, 
                        b.status, 
                        b.fine_amount
                      FROM borrowings b
                      JOIN library_resources lr ON b.resource_id = lr.resource_id
                      WHERE b.user_id = :user_id
                      ORDER BY b.borrow_date DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get borrowing history error: " . $e->getMessage());
            return [];
        }
    }

    public function getAvailableBooks($type = 'book') {
        try {
            $query = "SELECT lr.resource_id, lr.title, lr.category, lr.accession_number, 
                             b.author, b.isbn, b.publisher 
                      FROM library_resources lr
                      INNER JOIN books b ON lr.resource_id = b.resource_id
                      WHERE lr.status = 'available'";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $results;
        } catch (PDOException $e) {
            error_log("Get available resources error: " . $e->getMessage());
            return [];
        }
    }

    public function getAvailableMedia($type = 'media') {
        try {
            $query = "SELECT lr.resource_id, lr.title, lr.category, lr.accession_number, 
                             m.media_type, m.runtime, m.format
                      FROM library_resources lr
                      INNER JOIN media_resources m ON lr.resource_id = m.resource_id
                      WHERE lr.status = 'available'";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $results;
        } catch (PDOException $e) {
            error_log("Get available media error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getAvailablePeriodicals($type = 'periodical') {
        try {
            $query = "SELECT lr.resource_id, lr.title, lr.category, lr.accession_number, 
                             p.issn as publisher, p.publication_date, p.volume, p.issue
                      FROM library_resources lr
                      INNER JOIN periodicals p ON lr.resource_id = p.resource_id
                      WHERE lr.status = 'available'";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $results;
        } catch (PDOException $e) {
            error_log("Get available periodicals error: " . $e->getMessage());
            return [];
        }
    }
}