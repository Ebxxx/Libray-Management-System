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
            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
            <!-- Add chart section -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-start mb-1">
                                <select id="yearSelector" class="form-select" style="width: auto;">
                                    <?php
                                    $currentYear = date('Y');
                                    for($year = $currentYear; $year >= $currentYear - 4; $year--) {
                                        echo "<option value='$year'>$year</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <canvas id="monthlyBorrowingsChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <canvas id="resourceDistributionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var ctx = document.getElementById('resourceDistributionChart').getContext('2d');
        var resourceData = {
            labels: ['Books', 'Media Resources', 'Periodicals'],
            datasets: [{
                data: [
                    <?php echo $resourceController->getTotalBooks(); ?>,
                    <?php echo $resourceController->getTotalMediaResources(); ?>,
                    <?php echo $resourceController->getTotalPeriodicals(); ?>
                ],
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 206, 86, 0.8)'
                ]
            }]
        };
        
        new Chart(ctx, {
            type: 'pie',
            data: resourceData,
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 1.5,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Resource Type Distribution'
                    }
                }
            }
        });
    });

    // Monthly Borrowings Chart
    let monthlyBorrowingsChart; // Declare chart variable globally

    function initializeMonthlyChart(data) {
        var monthlyCtx = document.getElementById('monthlyBorrowingsChart').getContext('2d');
        var monthlyData = {
            labels: ['January', 'February', 'March', 'April', 'May', 'June', 
                    'July', 'August', 'September', 'October', 'November', 'December'],
            datasets: [{
                label: 'Number of Borrowings',
                data: data,
                backgroundColor: 'rgba(75, 192, 192, 0.8)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        };
        
        if (monthlyBorrowingsChart) {
            monthlyBorrowingsChart.destroy();
        }
        
        monthlyBorrowingsChart = new Chart(monthlyCtx, {
            type: 'bar',
            data: monthlyData,
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 1.5,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },  
                    title: {
                        display: true,
                        text: 'Monthly Borrowings for ' + document.getElementById('yearSelector').value
                    }
                }
            }
        });
    }

    // Initialize chart with current year's data
    initializeMonthlyChart([<?php echo implode(',', $monthlyBorrowings); ?>]);

    // Add event listener for year selection
    document.getElementById('yearSelector').addEventListener('change', function() {
        fetch(`../controller/get_monthly_borrowings.php?year=${this.value}`)
            .then(response => response.json())
            .then(data => {
                initializeMonthlyChart(data);
            })
            .catch(error => console.error('Error:', error));
    });
    </script>
</body>
</html>