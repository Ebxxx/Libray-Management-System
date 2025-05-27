<?php
require_once '../config/Database.php';
require_once '../controller/Session.php';
require_once '../controller/ActivityLogController.php';

class BorrowingController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function borrowResource($userId, $resourceId) {
        try {
            $this->conn->beginTransaction();
            
            // First check if the resource exists and is available
            $stmt = $this->conn->prepare("
                SELECT status 
                FROM library_resources 
                WHERE resource_id = :resource_id
            ");
            $stmt->bindParam(":resource_id", $resourceId);
            $stmt->execute();
            $resource = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$resource) {
                throw new Exception('Resource not found.');
            }

            if ($resource['status'] !== 'available') {
                throw new Exception('This resource is not available for borrowing.');
            }

            // Check if user already has a pending request for this resource
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as pending_count 
                FROM borrowings 
                WHERE user_id = :user_id 
                AND resource_id = :resource_id 
                AND status = 'pending'::borrowing_status
            ");
            $stmt->bindParam(":user_id", $userId);
            $stmt->bindParam(":resource_id", $resourceId);
            $stmt->execute();
            $pendingCheck = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($pendingCheck['pending_count'] > 0) {
                throw new Exception('You already have a pending request for this resource.');
            }

            // Check user's current active borrowings against their limit
            $stmt = $this->conn->prepare("
                SELECT 
                    COUNT(b.borrowing_id) as active_borrowings,
                    u.max_books
                FROM users u
                LEFT JOIN borrowings b ON u.user_id = b.user_id 
                AND b.status IN ('active'::borrowing_status, 'overdue'::borrowing_status, 'pending'::borrowing_status)
                WHERE u.user_id = :user_id
                GROUP BY u.user_id, u.max_books
            ");
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();
            $borrowingInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($borrowingInfo && $borrowingInfo['active_borrowings'] >= $borrowingInfo['max_books']) {
                throw new Exception('You have reached your maximum borrowing limit of ' . $borrowingInfo['max_books'] . ' items.');
            }
            
            // Create pending borrowing request
            $stmt = $this->conn->prepare("
                INSERT INTO borrowings (user_id, resource_id, status, borrow_date) 
                VALUES (:user_id, :resource_id, 'pending'::borrowing_status, CURRENT_TIMESTAMP)
            ");
            $stmt->bindParam(":user_id", $userId);
            $stmt->bindParam(":resource_id", $resourceId);
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to create borrowing request.');
            }

            // Update resource status to borrowed since it's now in the borrowing process
            $stmt = $this->conn->prepare("
                UPDATE library_resources 
                SET status = 'borrowed'::resource_status 
                WHERE resource_id = :resource_id
            ");
            $stmt->bindParam(":resource_id", $resourceId);
            if (!$stmt->execute()) {
                throw new Exception('Failed to update resource status.');
            }
            
            $this->conn->commit();
            return [
                'success' => true,
                'message' => 'Resource borrowing request submitted successfully.'
            ];

        } catch(Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Borrow resource error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function approveBorrowing($borrowingId) {
        try {
            $this->conn->beginTransaction();

            // Get borrowing and user details
            $stmt = $this->conn->prepare("
                SELECT b.*, u.borrowing_days_limit 
                FROM borrowings b
                JOIN users u ON b.user_id = u.user_id
                WHERE b.borrowing_id = :borrowing_id
            ");
            $stmt->bindParam(':borrowing_id', $borrowingId);
            $stmt->execute();
            $borrowing = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$borrowing) {
                throw new Exception("Borrowing record not found");
            }

            // Use user's borrowing_days_limit or default to 7 days
            $borrowingDaysLimit = $borrowing['borrowing_days_limit'] ?? 7;
            
            // Calculate due date based on user's borrowing days limit
            $dueDate = date('Y-m-d H:i:s', strtotime("+{$borrowingDaysLimit} days"));

            // Update borrowing record
            $updateStmt = $this->conn->prepare("
                UPDATE borrowings 
                SET status = 'active',
                    due_date = :due_date,
                    approved_by = :approved_by,
                    approved_at = CURRENT_TIMESTAMP
                WHERE borrowing_id = :borrowing_id
            ");

            $updateStmt->bindParam(':due_date', $dueDate);
            $updateStmt->bindParam(':approved_by', $_SESSION['user_id']);
            $updateStmt->bindParam(':borrowing_id', $borrowingId);
            $updateStmt->execute();

            // Update resource status
            $updateResourceStmt = $this->conn->prepare("
                UPDATE library_resources 
                SET status = 'borrowed'::resource_status 
                WHERE resource_id = :resource_id
            ");
            $updateResourceStmt->bindParam(':resource_id', $borrowing['resource_id']);
            $updateResourceStmt->execute();

            // Log the activity
            $activityLogger = new ActivityLogController();
            
            // Get resource and user details for logging
            $stmt = $this->conn->prepare("
                SELECT lr.title, u.first_name, u.last_name, u.membership_id 
                FROM borrowings b
                JOIN library_resources lr ON b.resource_id = lr.resource_id
                JOIN users u ON b.user_id = u.user_id
                WHERE b.borrowing_id = :borrowing_id
            ");
            $stmt->bindParam(':borrowing_id', $borrowingId);
            $stmt->execute();
            $details = $stmt->fetch(PDO::FETCH_ASSOC);

            $description = sprintf(
                "Approved borrowing request - Resource: %s, Borrower: %s %s (ID: %s), Due Date: %s",
                $details['title'],
                $details['first_name'],
                $details['last_name'],
                $details['membership_id'],
                $dueDate
            );

            $activityLogger->logActivity($_SESSION['user_id'], 'approve_borrowing', $description);

            $this->conn->commit();
            return [
                'success' => true,
                'due_date' => $dueDate
            ];
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error in approving borrowing: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function returnResource($borrowing_id) {
        try {
            $this->conn->beginTransaction();

            // Get borrowing details with resource type
            $borrow_query = "SELECT 
                b.*, 
                lr.category,
                CASE 
                    WHEN b2.book_id IS NOT NULL THEN 'book'
                    WHEN m.media_id IS NOT NULL THEN 'media'
                    WHEN p.periodical_id IS NOT NULL THEN 'periodical'
                    ELSE lr.category
                END as resource_type
                FROM borrowings b
                JOIN library_resources lr ON b.resource_id = lr.resource_id
                LEFT JOIN books b2 ON lr.resource_id = b2.resource_id
                LEFT JOIN media_resources m ON lr.resource_id = m.resource_id
                LEFT JOIN periodicals p ON lr.resource_id = p.resource_id
                WHERE b.borrowing_id = :borrowing_id 
                AND b.status IN ('active'::borrowing_status, 'overdue'::borrowing_status)";
            
            $stmt = $this->conn->prepare($borrow_query);
            $stmt->bindParam(":borrowing_id", $borrowing_id);
            $stmt->execute();
            $borrowing = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$borrowing) {
                throw new Exception("Borrowing record not found or not in active/overdue status");
            }

            // Get fine configuration based on resource type
            $fine_query = "SELECT fine_amount 
                          FROM fine_configurations 
                          WHERE resource_type = :resource_type";
            $stmt = $this->conn->prepare($fine_query);
            $stmt->bindParam(":resource_type", $borrowing['resource_type']);
            $stmt->execute();
            $fine_config = $stmt->fetch(PDO::FETCH_ASSOC);

            // Debug log
            error_log("Resource Type: " . $borrowing['resource_type']);
            error_log("Category: " . $borrowing['category']);
            error_log("Fine Config: " . print_r($fine_config, true));

            $fine_rate = $fine_config ? $fine_config['fine_amount'] : 1.00;
            
            // Calculate fine if overdue
            $current_date = date('Y-m-d H:i:s');
            $fine_amount = 0;

            if (strtotime($current_date) > strtotime($borrowing['due_date'])) {
                $overdue_days = ceil((strtotime($current_date) - strtotime($borrowing['due_date'])) / (60 * 60 * 24));
                $fine_amount = $overdue_days * $fine_rate;
                error_log("Overdue days: $overdue_days, Fine rate: $fine_rate, Total fine: $fine_amount");
            }

            // Update borrowing record
            $return_query = "UPDATE borrowings 
                             SET return_date = CURRENT_TIMESTAMP, 
                                 status = 'returned'::borrowing_status, 
                                 fine_amount = :fine_amount,
                                 returned_by = :staff_id
                             WHERE borrowing_id = :borrowing_id";
            $stmt = $this->conn->prepare($return_query);
            $stmt->bindParam(":fine_amount", $fine_amount);
            $stmt->bindParam(":borrowing_id", $borrowing_id);
            $stmt->bindParam(":staff_id", $_SESSION['user_id']);
            $stmt->execute();
    
            // Update resource status back to available
            $update_query = "UPDATE library_resources 
                             SET status = 'available'::resource_status 
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
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Return resource error: " . $e->getMessage());
            throw new Exception("Failed to return resource: " . $e->getMessage());
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
                        b.approved_at,
                        u.user_id,
                        u.first_name, 
                        u.last_name, 
                        u.email, 
                        u.role,
                        lr.title AS resource_title,
                        lr.category AS resource_type,
                        CONCAT(u_staff.first_name, ' ', u_staff.last_name) as approved_by,
                        u_staff.role as approver_role
                    FROM borrowings b
                    JOIN users u ON b.user_id = u.user_id
                    JOIN library_resources lr ON b.resource_id = lr.resource_id
                    LEFT JOIN users u_staff ON b.approved_by = u_staff.user_id
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
            $query = "SELECT lr.*, b.* 
                      FROM library_resources lr
                      JOIN books b ON lr.resource_id = b.resource_id
                      WHERE lr.status = 'available'
                      ORDER BY lr.created_at DESC";
            
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
            $query = "SELECT lr.*, mr.* 
                      FROM library_resources lr
                      JOIN media_resources mr ON lr.resource_id = mr.resource_id
                      WHERE lr.status = 'available'
                      ORDER BY lr.created_at DESC";
            
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
            $query = "SELECT lr.*, p.* 
                      FROM library_resources lr
                      JOIN periodicals p ON lr.resource_id = p.resource_id
                      WHERE lr.status = 'available'
                      ORDER BY lr.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $results;
        } catch (PDOException $e) {
            error_log("Get available periodicals error: " . $e->getMessage());
            return [];
        }
    }

    public function getMonthlyBorrowings($year) {
        try {
            $query = "SELECT MONTH(borrow_date) as month, COUNT(*) as borrow_count
                      FROM borrowings
                      WHERE YEAR(borrow_date) = :year
                      GROUP BY MONTH(borrow_date)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":year", $year, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get monthly borrowings error: " . $e->getMessage());
            return [];
        }
    }

    private function updateOverdueStatus() {
        try {
            // Update status to overdue for items past due date
            $updateQuery = "
                UPDATE borrowings 
                SET status = 'overdue'::borrowing_status
                WHERE status = 'active'::borrowing_status 
                AND due_date < CURRENT_TIMESTAMP
                AND return_date IS NULL
                RETURNING borrowing_id, due_date";
            
            $stmt = $this->conn->prepare($updateQuery);
            $stmt->execute();
            $updatedItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Updated " . count($updatedItems) . " items to overdue status");
            foreach ($updatedItems as $item) {
                error_log("Updated borrowing_id: " . $item['borrowing_id'] . " to overdue. Due date was: " . $item['due_date']);
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Error updating overdue status: " . $e->getMessage());
            return false;
        }
    }


    // Add new method to manage fine configurations
    public function updateFineConfiguration($resource_type, $fine_amount) {
        try {
            // Convert resource type to lowercase for consistency
            $resource_type = strtolower($resource_type);
            
            // First check if the configuration exists
            $check_query = "SELECT * FROM fine_configurations WHERE resource_type = :resource_type";
            $stmt = $this->conn->prepare($check_query);
            $stmt->bindParam(":resource_type", $resource_type);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Update existing configuration
                $query = "UPDATE fine_configurations 
                         SET fine_amount = :fine_amount 
                         WHERE resource_type = :resource_type";
            } else {
                // Insert new configuration
                $query = "INSERT INTO fine_configurations (resource_type, fine_amount) 
                         VALUES (:resource_type, :fine_amount)";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":resource_type", $resource_type);
            $stmt->bindParam(":fine_amount", $fine_amount);
            
            $success = $stmt->execute();
            
            // Debug log
            error_log("Updating fine configuration - Type: $resource_type, Amount: $fine_amount, Success: " . ($success ? 'true' : 'false'));
            
            return $success;
        } catch (Exception $e) {
            error_log("Update fine configuration error: " . $e->getMessage());
            return false;
        }
    }

    public function getFineConfigurations() {
        try {
            $query = "SELECT * FROM fine_configurations ORDER BY resource_type";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get fine configurations error: " . $e->getMessage());
            return [];
        }
    }

    public function getPendingBorrowings() {
        try {
            $query = "SELECT 
                        b.borrowing_id,
                        b.borrow_date,
                        u.first_name,
                        u.last_name,
                        u.membership_id,
                        u.role as user_role,
                        lr.title as resource_title,
                        lr.accession_number,
                        lr.category as resource_type
                    FROM borrowings b
                    JOIN users u ON b.user_id = u.user_id
                    JOIN library_resources lr ON b.resource_id = lr.resource_id
                    WHERE b.status = 'pending'::borrowing_status
                    ORDER BY b.borrow_date ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get pending borrowings error: " . $e->getMessage());
            return [];
        }
    }

    public function getRecentApprovals() {
        try {
            $query = "SELECT 
                        b.borrowing_id,
                        b.approved_at,
                        b.due_date,
                        u_borrower.membership_id,
                        CONCAT(u_borrower.first_name, ' ', u_borrower.last_name) as borrower_name,
                        lr.title as resource_title,
                        lr.accession_number,
                        CONCAT(u_staff.first_name, ' ', u_staff.last_name) as staff_name,
                        u_staff.role as staff_role
                    FROM borrowings b
                    JOIN users u_borrower ON b.user_id = u_borrower.user_id
                    JOIN users u_staff ON b.approved_by = u_staff.user_id
                    JOIN library_resources lr ON b.resource_id = lr.resource_id
                    WHERE b.status = 'active'::borrowing_status
                    AND b.approved_at IS NOT NULL
                    ORDER BY b.approved_at DESC
                    LIMIT 10";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get recent approvals error: " . $e->getMessage());
            return [];
        }
    }

    // Add this new method to get all approved borrowings for audit
    public function getApprovedBorrowings() {
        try {
            $query = "SELECT 
                        b.borrowing_id, 
                        b.borrow_date, 
                        b.due_date,
                        b.return_date,
                        b.status,
                        b.approved_at,
                        u.first_name, 
                        u.last_name, 
                        u.email, 
                        u.role,
                        lr.title AS resource_title,
                        lr.category AS resource_type,
                        CONCAT(u_staff.first_name, ' ', u_staff.last_name) as approved_by,
                        u_staff.role as approver_role,
                        CONCAT(u_returner.first_name, ' ', u_returner.last_name) as returned_by,
                        u_returner.role as returner_role
                    FROM borrowings b
                    JOIN users u ON b.user_id = u.user_id
                    JOIN library_resources lr ON b.resource_id = lr.resource_id
                    LEFT JOIN users u_staff ON b.approved_by = u_staff.user_id
                    LEFT JOIN users u_returner ON b.returned_by = u_returner.user_id
                    WHERE b.approved_by IS NOT NULL
                    ORDER BY b.approved_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get approved borrowings error: " . $e->getMessage());
            return [];
        }
    }

    public function getAvailableAndPendingBooks($userId) {
        try {
            $query = "SELECT DISTINCT lr.*, bb.*,
                      CASE WHEN b.status = 'pending'::borrowing_status AND b.user_id = :user_id THEN 1 ELSE 0 END as pending
                      FROM library_resources lr
                      LEFT JOIN books bb ON lr.resource_id = bb.resource_id
                      LEFT JOIN (
                          SELECT resource_id, status, user_id 
                          FROM borrowings 
                          WHERE user_id = :user_id AND status = 'pending'::borrowing_status
                      ) b ON lr.resource_id = b.resource_id
                      WHERE lr.category = 'book' 
                      AND (lr.status = 'available'::resource_status 
                          OR (b.status = 'pending'::borrowing_status AND b.user_id = :user_id))
                      GROUP BY lr.resource_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get available and pending books error: " . $e->getMessage());
            return [];
        }
    }

    public function getAvailableAndPendingMedia($userId) {
        try {
            $query = "SELECT DISTINCT lr.*, mr.*,
                      CASE WHEN b.status = 'pending'::borrowing_status AND b.user_id = :user_id THEN 1 ELSE 0 END as pending
                      FROM library_resources lr
                      LEFT JOIN media_resources mr ON lr.resource_id = mr.resource_id
                      LEFT JOIN (
                          SELECT resource_id, status, user_id 
                          FROM borrowings 
                          WHERE user_id = :user_id AND status = 'pending'::borrowing_status
                      ) b ON lr.resource_id = b.resource_id
                      WHERE lr.category = 'media' 
                      AND (lr.status = 'available'::resource_status 
                          OR (b.status = 'pending'::borrowing_status AND b.user_id = :user_id))
                      GROUP BY lr.resource_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get available and pending media error: " . $e->getMessage());
            return [];
        }
    }

    public function getAvailableAndPendingPeriodicals($userId) {
        try {
            $query = "SELECT DISTINCT lr.*, p.*,
                      CASE WHEN b.status = 'pending'::borrowing_status AND b.user_id = :user_id THEN 1 ELSE 0 END as pending
                      FROM library_resources lr
                      LEFT JOIN periodicals p ON lr.resource_id = p.resource_id
                      LEFT JOIN (
                          SELECT resource_id, status, user_id 
                          FROM borrowings 
                          WHERE user_id = :user_id AND status = 'pending'::borrowing_status
                      ) b ON lr.resource_id = b.resource_id
                      WHERE lr.category = 'periodical' 
                      AND (lr.status = 'available'::resource_status 
                          OR (b.status = 'pending'::borrowing_status AND b.user_id = :user_id))
                      GROUP BY lr.resource_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get available and pending periodicals error: " . $e->getMessage());
            return [];
        }
    }

    public function displayBorrowingHistory($user_id) {
        try {
            $query = "WITH resource_types AS (
                SELECT 
                    lr.resource_id,
                    CASE 
                        WHEN b.book_id IS NOT NULL THEN 'book'
                        WHEN m.media_id IS NOT NULL THEN 'media'
                        WHEN p.periodical_id IS NOT NULL THEN 'periodical'
                        ELSE 'unknown'
                    END as resource_type
                FROM library_resources lr
                LEFT JOIN books b ON lr.resource_id = b.resource_id
                LEFT JOIN media_resources m ON lr.resource_id = m.resource_id
                LEFT JOIN periodicals p ON lr.resource_id = p.resource_id
            )
            SELECT 
                b.borrowing_id,
                lr.title,
                lr.category,
                rt.resource_type,
                b.borrow_date,
                b.due_date,
                b.return_date,
                b.status::text as status,
                b.fine_amount,
                CASE 
                    WHEN b.return_date IS NOT NULL THEN 'returned'
                    WHEN b.status = 'overdue'::borrowing_status THEN 'overdue'
                    WHEN CURRENT_TIMESTAMP > b.due_date THEN 'overdue'
                    ELSE 'active'
                END as current_status,
                CASE 
                    WHEN b.return_date IS NOT NULL THEN 
                        GREATEST(0, EXTRACT(DAY FROM (b.return_date - b.due_date))::integer)
                    WHEN CURRENT_TIMESTAMP > b.due_date THEN 
                        EXTRACT(DAY FROM (CURRENT_TIMESTAMP - b.due_date))::integer
                    ELSE 0
                END as days_overdue,
                fc.fine_amount as daily_fine_rate,
                CASE 
                    WHEN b.return_date IS NOT NULL THEN 
                        COALESCE(b.fine_amount, 0)
                    WHEN CURRENT_TIMESTAMP > b.due_date THEN 
                        GREATEST(
                            COALESCE(b.fine_amount, 0),
                            EXTRACT(DAY FROM (CURRENT_TIMESTAMP - b.due_date))::integer * COALESCE(fc.fine_amount, 0)
                        )
                    ELSE COALESCE(b.fine_amount, 0)
                END as calculated_fine
            FROM borrowings b
            JOIN library_resources lr ON b.resource_id = lr.resource_id
            JOIN resource_types rt ON rt.resource_id = lr.resource_id
            LEFT JOIN fine_configurations fc ON rt.resource_type = fc.resource_type
            WHERE b.user_id = :user_id
            ORDER BY 
                CASE 
                    WHEN b.status = 'overdue'::borrowing_status THEN 1
                    WHEN b.status = 'active'::borrowing_status AND CURRENT_TIMESTAMP > b.due_date THEN 2
                    WHEN b.status = 'active'::borrowing_status THEN 3
                    ELSE 4
                END,
                b.due_date ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Post-process the results to ensure proper formatting
            foreach ($results as &$result) {
                // Ensure dates are in proper format
                $result['borrow_date'] = date('Y-m-d H:i:s', strtotime($result['borrow_date']));
                $result['due_date'] = date('Y-m-d H:i:s', strtotime($result['due_date']));
                if ($result['return_date']) {
                    $result['return_date'] = date('Y-m-d H:i:s', strtotime($result['return_date']));
                }
                
                // Ensure numeric values are properly formatted
                $result['days_overdue'] = (int)$result['days_overdue'];
                $result['calculated_fine'] = (float)$result['calculated_fine'];
                $result['daily_fine_rate'] = (float)$result['daily_fine_rate'];
            }
            
            error_log("Retrieved borrowing history for user " . $user_id . ": " . count($results) . " records");
            return $results;
            
        } catch (PDOException $e) {
            error_log("Error in borrowing history: " . $e->getMessage());
            return false;
        }
    }

    public function getOverdueBorrowings() {
        try {
            // First, update any active borrowings that are now overdue
            $updateQuery = "
                UPDATE borrowings 
                SET status = 'overdue'::borrowing_status
                WHERE status = 'active'::borrowing_status 
                AND due_date < CURRENT_TIMESTAMP 
                AND return_date IS NULL
                RETURNING borrowing_id, due_date";
            
            $stmt = $this->conn->prepare($updateQuery);
            $stmt->execute();
            $updatedItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("Updated " . count($updatedItems) . " items to overdue status");
            foreach ($updatedItems as $item) {
                error_log("Updated borrowing_id: " . $item['borrowing_id'] . " to overdue. Due date was: " . $item['due_date']);
            }

            // Now fetch all overdue items with PostgreSQL syntax
            $query = "
                WITH overdue_items AS (
                    SELECT 
                        b.borrowing_id,
                        b.borrow_date,
                        b.due_date,
                        b.status,
                        COALESCE(b.fine_amount, 0) as current_fine,
                        u.user_id,
                        u.first_name,
                        u.last_name,
                        u.email,
                        u.role,
                        u.membership_id,
                        lr.resource_id,
                        lr.title as resource_title,
                        lr.category as resource_type,
                        lr.accession_number,
                        COALESCE(fc.fine_amount, 1.00) as daily_fine_rate,
                        EXTRACT(DAY FROM (CURRENT_TIMESTAMP - b.due_date))::integer as days_overdue,
                        GREATEST(
                            COALESCE(b.fine_amount, 0),
                            EXTRACT(DAY FROM (CURRENT_TIMESTAMP - b.due_date))::integer * COALESCE(fc.fine_amount, 1.00)
                        ) as calculated_fine,
                        COALESCE(
                            (SELECT SUM(fp.amount_paid) 
                             FROM fine_payments fp 
                             WHERE fp.borrowing_id = b.borrowing_id 
                             AND fp.payment_status = 'paid'::payment_status), 
                            0
                        ) as total_paid
                    FROM borrowings b
                    JOIN users u ON b.user_id = u.user_id
                    JOIN library_resources lr ON b.resource_id = lr.resource_id
                    LEFT JOIN fine_configurations fc ON lr.category = fc.resource_type
                    WHERE b.status IN ('overdue'::borrowing_status, 'active'::borrowing_status)
                    AND b.return_date IS NULL
                    AND CURRENT_TIMESTAMP > b.due_date
                )
                SELECT * FROM overdue_items
                ORDER BY due_date ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Log the results for debugging
            error_log("Found " . count($results) . " overdue items");
            if (!empty($results)) {
                foreach ($results as $item) {
                    error_log(sprintf(
                        "Overdue item: ID=%d, Title=%s, Due=%s, Status=%s, Days=%d, Fine=$%.2f",
                        $item['borrowing_id'],
                        $item['resource_title'],
                        $item['due_date'],
                        $item['status'],
                        $item['days_overdue'],
                        $item['calculated_fine']
                    ));
                }
            } else {
                error_log("No overdue items found. Checking if there are any active borrowings...");
                // Debug query to check for active borrowings
                $debugQuery = "
                    SELECT COUNT(*) as count 
                    FROM borrowings 
                    WHERE status = 'active'::borrowing_status 
                    AND return_date IS NULL";
                $debugStmt = $this->conn->prepare($debugQuery);
                $debugStmt->execute();
                $debugResult = $debugStmt->fetch(PDO::FETCH_ASSOC);
                error_log("Active borrowings count: " . $debugResult['count']);
                
                // Check for borrowings with past due dates
                $debugQuery2 = "
                    SELECT COUNT(*) as count 
                    FROM borrowings 
                    WHERE due_date < CURRENT_TIMESTAMP 
                    AND return_date IS NULL";
                $debugStmt2 = $this->conn->prepare($debugQuery2);
                $debugStmt2->execute();
                $debugResult2 = $debugStmt2->fetch(PDO::FETCH_ASSOC);
                error_log("Past due borrowings count: " . $debugResult2['count']);
            }

            return $results;
        } catch (PDOException $e) {
            error_log("Error in getOverdueBorrowings: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [];
        }
    }
}
