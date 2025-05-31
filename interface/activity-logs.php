<?php
require_once '../app/handler/UserController.php';
require_once '../app/handler/ActivityLogController.php';
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$activityLogController = new ActivityLogController();
$logs = $activityLogController->getLogs();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - Library Management System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.7.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <?php include 'includes/sidebarModal.php'; ?>

        <div class="main-content flex-grow-1">
            <!-- Header Section -->
            <div class="d-flex justify-content-between align-items-center py-3 px-4">
                <h2 class="mb-0">Activity Logs</h2>
                <!-- User Profile Section -->
                <div>
                    <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                    <span class="badge bg-primary"><?php echo ucfirst($_SESSION['role']); ?></span>
                </div>
            </div>
            <hr>

            <div class="container-fluid px-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-list-ul"></i> Recent Activity Logs
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>User</th>
                                        <th>Action Type</th>
                                        <th>Description</th>
                                        <th>IP Address</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($logs)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No activity logs found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($logs as $log): ?>
                                            <tr>
                                                <td><?php echo date('Y-m-d H:i:s', strtotime($log['timestamp'])); ?></td>
                                                <td><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></td>
                                                <td>
                                                    <span class="badge <?php echo getActionTypeBadgeClass($log['action_type']); ?>">
                                                        <?php echo htmlspecialchars($log['action_type']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($log['action_description']); ?></td>
                                                <td><?php echo formatIpAddress($log['ip_address']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
function getActionTypeBadgeClass($actionType) {
    $classes = [
        'login' => 'text-primary',
        'logout' => 'text-secondary',
        'create' => 'text-success',
        'update' => 'text-warning'
    ];
    
    return $classes[$actionType] ?? 'text-secondary';
}

function formatIpAddress($ip) {
    if (empty($ip) || $ip === '0.0.0.0') {
        return '<span class="badge bg-secondary">Unknown</span>';
    }
    
    // Check for IPv6 localhost
    if ($ip === '::1') {
        return '
            <div>
                <code class="text-primary">' . htmlspecialchars($ip) . '</code>
                <br>
                <small class="text-muted">
                    <i class="bi bi-house-door"></i> IPv6 Localhost
                </small>
            </div>';
    }
    
    // Check for IPv4 localhost
    if ($ip === '127.0.0.1') {
        return '
            <div>
                <code class="text-primary">' . htmlspecialchars($ip) . '</code>
                <br>
                <small class="text-muted">
                    <i class="bi bi-house-door"></i> IPv4 Localhost
                </small>
            </div>';
    }
    
    // Check for private IPv4 ranges
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $longIp = ip2long($ip);
        
        // Private IP ranges
        if (($longIp >= ip2long('192.168.0.0') && $longIp <= ip2long('192.168.255.255')) ||
            ($longIp >= ip2long('172.16.0.0') && $longIp <= ip2long('172.31.255.255')) ||
            ($longIp >= ip2long('10.0.0.0') && $longIp <= ip2long('10.255.255.255'))) {
            
            return '
                <div>
                    <code class="text-warning">' . htmlspecialchars($ip) . '</code>
                    <br>
                    <small class="text-muted">
                        <i class="bi bi-shield-lock"></i> Private Network
                    </small>
                </div>';
        }
        
        // Public IPv4
        return '
            <div>
                <code class="text-success">' . htmlspecialchars($ip) . '</code>
                <br>
                <small class="text-muted">
                    <i class="bi bi-globe"></i> Public IPv4
                </small>
            </div>';
    }
    
    // Check for IPv6
    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        // Check for IPv6 private/link-local addresses
        if (strpos($ip, 'fe80:') === 0 || strpos($ip, 'fc00:') === 0 || strpos($ip, 'fd00:') === 0) {
            return '
                <div>
                    <code class="text-warning">' . htmlspecialchars($ip) . '</code>
                    <br>
                    <small class="text-muted">
                        <i class="bi bi-shield-lock"></i> IPv6 Private
                    </small>
                </div>';
        }
        
        // Public IPv6
        return '
            <div>
                <code class="text-info">' . htmlspecialchars($ip) . '</code>
                <br>
                <small class="text-muted">
                    <i class="bi bi-globe2"></i> Public IPv6
                </small>
            </div>';
    }
    
    // Fallback for unknown format
    return '
        <div>
            <code class="text-secondary">' . htmlspecialchars($ip) . '</code>
            <br>
            <small class="text-muted">
                <i class="bi bi-question-circle"></i> Unknown Format
            </small>
        </div>';
}
?>
