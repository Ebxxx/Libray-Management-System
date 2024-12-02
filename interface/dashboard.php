<?php
require_once '../config/Database.php';
require_once '../controller/ResourceController.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch book statistics
$resourceController = new ResourceController();
$bookStats = $resourceController->getBookStatistics();
$categoryDistribution = $resourceController->getBookCategoriesDistribution();
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
                    <?php endif; ?>
                </div>
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
            <!-- chart section here -->
            <?php include 'includes/chart.php'; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($_SESSION['role'] === 'admin'): ?>
        var ctx = document.getElementById('bookCategoryChart').getContext('2d');
        var categoryData = <?php echo json_encode($categoryDistribution); ?>;
        
        var labels = categoryData.map(item => item.category);
        var counts = categoryData.map(item => item.count);
        
        var chart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: counts,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Book Categories Distribution'
                    }
                }
            }
        });
        <?php endif; ?>
    });
    </script>
</body>
</html>