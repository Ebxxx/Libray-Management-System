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
            <div class="d-flex justify-content-between align-items-center py-3 px-4 ">
                <h2 class="mb-0">Dashboard</h2>
                <!-- User Profile Section -->
                <div>
                    <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
                    <span class="badge bg-primary"><?php echo ucfirst($_SESSION['role']); ?></span>
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
</body>
</html>