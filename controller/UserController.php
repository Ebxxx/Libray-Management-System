<?php
require_once __DIR__ . '/../config/Database.php';
require_once 'Session.php';
require_once 'ActivityLogController.php';

class UserController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    private function generateMembershipId($role) {
        // Generate a prefix based on role
        $prefix = substr(strtoupper($role), 0, 1); // A for admin, S for student, etc.
        
        // Get current year
        $year = date('Y');
        
        // Generate a random 4-digit number
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Combine: [Role][Year][Random] e.g., A20240001
        $membership_id = $prefix . $year . $random;
        
        // Check if this ID already exists
        $stmt = $this->conn->prepare("SELECT membership_id FROM users WHERE membership_id = :membership_id");
        $stmt->bindParam(":membership_id", $membership_id);
        $stmt->execute();
        
        // If ID exists, recursively try again
        if ($stmt->rowCount() > 0) {
            return $this->generateMembershipId($role);
        }
        
        return $membership_id;
    }

    public function login($username, $password) {
        try {
            // Debug connection
            if (!$this->conn) {
                error_log("Database connection failed");
                return false;
            }

            // Query the users table using PDO
            $query = "SELECT user_id, username, password, role, first_name, last_name 
                     FROM users 
                     WHERE username = :username 
                     LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":username", $username);
            $stmt->execute();
            
            // Debug query results
            error_log("Login attempt for username: " . $username);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("Query result: " . print_r($user, true));
            
            // Check if user exists and verify password
            if ($user && password_verify($password, $user['password'])) {
                Session::start();
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];
                
                // Log the login activity
                $activityLogger = new ActivityLogController();
                $activityLogger->logActivity(
                    $user['user_id'],
                    'login',
                    'User logged in successfully'
                );
                
                error_log("Login successful for user: " . $username);
                return true;
            }
            
            // Log failed login attempt
            error_log("Login failed for username: " . $username);
            if (!$user) {
                error_log("User not found in database");
            } else {
                error_log("Password verification failed");
            }
            return false;
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }    

    public function createUser($data) {
        try {
            // Check if username already exists
            $stmt = $this->conn->prepare("SELECT username FROM users WHERE username = :username");
            $stmt->bindParam(":username", $data['username']);
            $stmt->execute();
    
            if ($stmt->rowCount() > 0) {
                error_log("Username already exists: " . $data['username']);
                return false;
            }
    
            // Check if email already exists
            $stmt = $this->conn->prepare("SELECT email FROM users WHERE email = :email");
            $stmt->bindParam(":email", $data['email']);
            $stmt->execute();
    
            if ($stmt->rowCount() > 0) {
                error_log("Email already exists: " . $data['email']);
                return false;
            }
    
            // Generate membership ID
            $data['membership_id'] = $this->generateMembershipId($data['role']);
    
            // Insert the new user
            $query = "INSERT INTO users (membership_id, username, password, first_name, last_name, email, role, max_books, borrowing_days_limit) 
                      VALUES (:membership_id, :username, :password, :first_name, :last_name, :email, :role, :max_books, :borrowing_days_limit)";
            $stmt = $this->conn->prepare($query);
    
            // Hash the password before storing
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
    
            // Bind parameters
            $stmt->bindParam(":membership_id", $data['membership_id']);
            $stmt->bindParam(":username", $data['username']);
            $stmt->bindParam(":password", $hashedPassword); // Store hashed password
            $stmt->bindParam(":first_name", $data['first_name']);
            $stmt->bindParam(":last_name", $data['last_name']);
            $stmt->bindParam(":email", $data['email']);
            $stmt->bindParam(":role", $data['role']);
            $stmt->bindParam(":max_books", $data['max_books']);
            $stmt->bindParam(":borrowing_days_limit", $data['borrowing_days_limit']);
    
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Create user error: " . $e->getMessage());
            return false;
        }
    }    

    public function getUsers() {
        try {
            $query = "SELECT user_id, membership_id, username, first_name, last_name, 
                             email, role, max_books, borrowing_days_limit 
                      FROM users 
                      ORDER BY user_id DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get users error: " . $e->getMessage());
            return [];
        }
    }

    public function getUserById($userId) {
        try {
            $query = "SELECT user_id, membership_id, username, first_name, last_name, email, role, max_books 
                     FROM users WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Get user by ID error: " . $e->getMessage());
            return null;
        }
    }

    public function updateUser($userId, $data) {
        try {
            // Start building the update query
            $query = "UPDATE users SET 
                     username = :username,
                     first_name = :first_name,
                     last_name = :last_name,
                     email = :email,
                     role = :role,
                     max_books = :max_books,
                     borrowing_days_limit = :borrowing_days_limit";

            // Add password to update if it's provided
            if (!empty($data['password'])) {
                $query .= ", password = :password";
            }

            $query .= ", updated_at = CURRENT_TIMESTAMP
                     WHERE user_id = :user_id";

            $stmt = $this->conn->prepare($query);
            
            // Bind basic parameters
            $stmt->bindParam(":username", $data['username']);
            $stmt->bindParam(":first_name", $data['first_name']);
            $stmt->bindParam(":last_name", $data['last_name']);
            $stmt->bindParam(":email", $data['email']);
            $stmt->bindParam(":role", $data['role']);
            $stmt->bindParam(":max_books", $data['max_books']);
            $stmt->bindParam(":borrowing_days_limit", $data['borrowing_days_limit']);
            $stmt->bindParam(":user_id", $userId);

            // Bind password if provided
            if (!empty($data['password'])) {
                $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmt->bindParam(":password", $hashedPassword);
            }

            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch(PDOException $e) {
            error_log("Update user error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteUser($userId) {
        try {
            // Start transaction
            $this->conn->beginTransaction();

            // Store username before deletion for logging
            $stmt = $this->conn->prepare("SELECT username, role FROM users WHERE user_id = :user_id");
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                error_log("User not found for deletion: " . $userId);
                $this->conn->rollBack();
                return false;
            }

            // Only check for last admin if the user being deleted is an admin
            if ($user['role'] === 'admin') {
                $stmt = $this->conn->prepare("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'");
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result['admin_count'] <= 1) {
                    error_log("Cannot delete last admin user");
                    $this->conn->rollBack();
                    return false;
                }
            }

            // Update activity logs to set user_id to NULL
            $stmt = $this->conn->prepare("UPDATE activity_logs SET user_id = NULL WHERE user_id = :user_id");
            $stmt->bindParam(":user_id", $userId);
            $stmt->execute();

            // Delete the user
            $stmt = $this->conn->prepare("DELETE FROM users WHERE user_id = :user_id");
            $stmt->bindParam(":user_id", $userId);
            
            if ($stmt->execute()) {
                $this->conn->commit();
                return true;
            }

            $this->conn->rollBack();
            return false;
            
        } catch(PDOException $e) {
            error_log("Delete user error: " . $e->getMessage());
            $this->conn->rollBack();
            return false;
        }
    }

    public function getUserStatistics() {
        try {
            $query = "SELECT 
                        COUNT(*) as total_users,
                        SUM(CASE WHEN role = 'student' THEN 1 ELSE 0 END) as student_count,
                        SUM(CASE WHEN role = 'faculty' THEN 1 ELSE 0 END) as faculty_count,
                        SUM(CASE WHEN role = 'staff' THEN 1 ELSE 0 END) as staff_count,
                        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_count
                      FROM users";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get user statistics error: " . $e->getMessage());
            return [
                'total_users' => 0,
                'student_count' => 0,
                'faculty_count' => 0,
                'staff_count' => 0,
                'admin_count' => 0
            ];
        }
    }

    public function updateCredentials($userId, $data) {
        try {
            // Check if username already exists for other users
            if (isset($data['username'])) {
                $stmt = $this->conn->prepare("SELECT username FROM users WHERE username = :username AND user_id != :user_id");
                $stmt->bindParam(":username", $data['username']);
                $stmt->bindParam(":user_id", $userId);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    error_log("Username already exists: " . $data['username']);
                    return false;
                }
            }

            // Start building the update query
            $query = "UPDATE users SET ";
            $updateParts = [];
            
            if (isset($data['username'])) {
                $updateParts[] = "username = :username";
            }
            if (!empty($data['password'])) {
                $updateParts[] = "password = :password";
            }
            
            if (empty($updateParts)) {
                return false; // Nothing to update
            }
            
            $query .= implode(", ", $updateParts);
            $query .= " WHERE user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind parameters
            if (isset($data['username'])) {
                $stmt->bindParam(":username", $data['username']);
            }
            if (!empty($data['password'])) {
                $stmt->bindParam(":password", $data['password']); // Password should already be hashed
            }
            $stmt->bindParam(":user_id", $userId);
            
            if ($stmt->execute()) {
                // Log the update activity
                $activityLogger = new ActivityLogController();
                $activityLogger->logUserUpdate(
                    $_SESSION['user_id'],
                    $userId,
                    'credentials updated'
                );
                
                return true;
            }
            return false;
        } catch(PDOException $e) {
            error_log("Update credentials error: " . $e->getMessage());
            return false;
        }
    }
}