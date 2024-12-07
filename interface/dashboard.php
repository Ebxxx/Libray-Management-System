<?php
require_once '../config/Database.php';
require_once '../controller/ResourceController.php';
require_once '../controller/BookController.php';
require_once '../controller/MediaResourceController.php';
require_once '../controller/PeriodicalController.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch book statistics
$resourceController = new ResourceController();
$bookStats = $resourceController->getBookStatistics();
$categoryDistribution = $resourceController->getBookCategoriesDistribution();
$monthlyBorrowings = $resourceController->getMonthlyBorrowings(date('Y'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Library Management System</title>
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
            <h2 class="fw-bold">Welcome, <small class="fw-normal" style="font-size: 0.8em;"><?php echo htmlspecialchars($_SESSION['username']); ?></small></h2>
                <!-- User Profile Section -->
                <div class="dropdown">
                    <div class="d-flex align-items-center gap-2 dropdown-toggle" 
                         role="button" data-bs-toggle="dropdown" aria-expanded="false">
                       
                        <div>
                            <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                            <span class="badge text-primary"><?php echo ucfirst($_SESSION['role']); ?></span>
                        </div>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end" style="background-color: rgb(184, 207, 202);">
                        <li class="fas fa-people">
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#accountSettingsModal">Account Settings</a>
                        </li>
                    </ul>
                </div>
            </div>
            <hr>

            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Books</h5>
                            <h2 class="mb-0"><?php echo $bookStats['total_books']; ?></h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Available Books</h5>
                            <h2 class="mb-0"><?php echo $bookStats['available_books']; ?></h2>
                        </div>
                    </div>
                </div>
                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                <div class="col-md-3">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <h5 class="card-title">Borrowed Books</h5>
                            <h2 class="mb-0"><?php echo $bookStats['borrowed_books']; ?></h2>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                <div class="col-md-3">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h5 class="card-title">Overdue Books</h5>
                            <h2 class="mb-0"><?php echo $bookStats['overdue_books']; ?></h2>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php include 'includes/chart.php'; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Initialize chart data that PHP provides
        const initialResourceData = {
            books: <?php echo $resourceController->getTotalBooks(); ?>,
            mediaResources: <?php echo $resourceController->getTotalMediaResources(); ?>,
            periodicals: <?php echo $resourceController->getTotalPeriodicals(); ?>
        };
        const initialMonthlyData = [<?php echo implode(',', $monthlyBorrowings); ?>];
    </script>
    <script src="assets/js/dashboard-charts.js"></script>

    <!-- Account Settings Modal -->
    <div class="modal fade" id="accountSettingsModal" tabindex="-1" aria-labelledby="accountSettingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="accountSettingsModalLabel">Account Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="accountSettingsForm">
                        <div class="mb-3">
                            <label for="newUsername" class="form-label">New Username</label>
                            <input type="text" class="form-control" id="newUsername" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="newPassword" name="password">
                            <small class="text-muted">Leave blank if you don't want to change the password</small>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('accountSettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            username: document.getElementById('newUsername').value,
            password: document.getElementById('newPassword').value,
            // Preserve existing user data
            first_name: '<?php echo $_SESSION['first_name'] ?? ''; ?>',
            last_name: '<?php echo $_SESSION['last_name'] ?? ''; ?>',
            email: '<?php echo $_SESSION['email'] ?? ''; ?>',
            role: '<?php echo $_SESSION['role'] ?? ''; ?>',
            max_books: '<?php echo $_SESSION['max_books'] ?? 0; ?>'
        };

        fetch('../api/update_account.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Account updated successfully!');
                location.reload();
            } else {
                alert('Error updating account: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the account');
        });
    });
    </script>
</body>
</html>