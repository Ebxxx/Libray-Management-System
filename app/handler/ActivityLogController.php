<?php
require_once __DIR__ . '/../../config/Database.php';

class ActivityLogController {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function logActivity($userId, $actionType, $actionDescription) {
        try {
            $query = "INSERT INTO activity_logs (user_id, action_type, action_description, ip_address) 
                      VALUES (:user_id, :action_type, :action_description, :ip_address)";
            
            $stmt = $this->conn->prepare($query);
            
            $ipAddress = $this->getRealIpAddress();
            
            $stmt->bindParam(":user_id", $userId);
            $stmt->bindParam(":action_type", $actionType);
            $stmt->bindParam(":action_description", $actionDescription);
            $stmt->bindParam(":ip_address", $ipAddress);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Log activity error: " . $e->getMessage());
            return false;
        }
    }

    private function getRealIpAddress() {
        // Check for various IP address sources (useful behind proxies/load balancers)
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];

        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) && !empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                
                // Validate IP address
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
                // Allow private ranges for development
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        // Fallback
        return '0.0.0.0';
    }

    public function logUserUpdate($adminId, $targetUserId, $changes) {
        try {
            $stmt = $this->conn->prepare("SELECT username FROM users WHERE user_id = :user_id");
            $stmt->bindParam(":user_id", $targetUserId);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $description = "Account details updated for user: " . $user['username'];
            
            return $this->logActivity($adminId, 'update', $description);
        } catch (PDOException $e) {
            error_log("Log user update error: " . $e->getMessage());
            return false;
        }
    }

    public function getLogs($limit = 100) {
        try {
            $query = "SELECT al.*, u.username 
                     FROM activity_logs al 
                     LEFT JOIN users u ON al.user_id = u.user_id 
                     ORDER BY al.timestamp DESC 
                     LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get logs error: " . $e->getMessage());
            return [];
        }
    }
} 