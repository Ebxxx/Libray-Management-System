<?php
require_once '../config/Database.php';
require_once '../app/handler/ResourceController.php';
require_once '../app/handler/BookController.php';
require_once '../app/handler/MediaResourceController.php';
require_once '../app/handler/PeriodicalController.php';
require_once '../app/handler/BorrowingController.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize controllers
$resourceController = new ResourceController();
$bookController = new BookController();
$mediaController = new MediaResourceController();
$periodicalController = new PeriodicalController();

// Fetch statistics
$bookStats = $resourceController->getBookStatistics();
$monthlyBorrowings = $resourceController->getMonthlyBorrowings(date('Y'));
$popularResources = $resourceController->getMostBorrowedResources();

// Get counts for each resource type
$totalBooks = $bookController->getTotalBooks();
$totalMedia = $mediaController->getTotalMediaResources();
$totalPeriodicals = $periodicalController->getTotalPeriodicals();
$totalResources = $totalBooks + $totalMedia + $totalPeriodicals;
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

            <!-- Resource Type Statistics -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-body position-relative p-4" style="background-color: #2B3377;">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-book-fill text-white fs-3 me-3"></i>
                                <h5 class="card-title text-white mb-0">Books</h5>
                            </div>
                            <h2 class="text-white mb-0 fw-bold"><?php echo $totalBooks; ?></h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-body position-relative p-4" style="background-color: #FF00FF;">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-journal text-white fs-3 me-3"></i>
                                <h5 class="card-title text-white mb-0">Periodicals</h5>
                            </div>
                            <h2 class="text-white mb-0 fw-bold"><?php echo $totalPeriodicals; ?></h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-body position-relative p-4" style="background-color: #0047FF;">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-camera-video text-white fs-3 me-3"></i>
                                <h5 class="card-title text-white mb-0">Media Resources</h5>
                            </div>
                            <h2 class="text-white mb-0 fw-bold"><?php echo $totalMedia; ?></h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-body position-relative p-4" style="background-color: #FF6600;">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-collection text-white fs-3 me-3"></i>
                                <h5 class="card-title text-white mb-0">Total Resources</h5>
                            </div>
                            <h2 class="text-white mb-0 fw-bold"><?php echo $totalResources; ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Statistics -->
            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-body position-relative p-4" style="background-color: #28a745;">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-check-circle-fill text-white fs-3 me-3"></i>
                                <h5 class="card-title text-white mb-0">Available Resources</h5>
                            </div>
                            <h2 class="text-white mb-0 fw-bold"><?php echo $bookStats['available_books']; ?></h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-body position-relative p-4" style="background-color: #ffc107;">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-arrow-up-circle-fill text-white fs-3 me-3"></i>
                                <h5 class="card-title text-white mb-0">Borrowed Resources</h5>
                            </div>
                            <h2 class="text-white mb-0 fw-bold"><?php echo $bookStats['borrowed_books']; ?></h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-body position-relative p-4" style="background-color: #dc3545;">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-exclamation-triangle-fill text-white fs-3 me-3"></i>
                                <h5 class="card-title text-white mb-0">Overdue Resources</h5>
                            </div>
                            <h2 class="text-white mb-0 fw-bold"><?php echo $bookStats['overdue_books']; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-body position-relative p-4" style="background-color: #28a745;">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-check-circle-fill text-white fs-3 me-3"></i>
                                <h5 class="card-title text-white mb-0">Available Resources</h5>
                            </div>
                            <h2 class="text-white mb-0 fw-bold"><?php echo $bookStats['available_books']; ?></h2>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                        <div class="card-body position-relative p-4" style="background-color: #ffc107;">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-arrow-up-circle-fill text-white fs-3 me-3"></i>
                                <h5 class="card-title text-white mb-0">Borrowed Resources</h5>
                            </div>
                            <h2 class="text-white mb-0 fw-bold"><?php echo $bookStats['borrowed_books']; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Replace both Top Choices and Most Borrowed Resources sections with this include -->
            <?php include 'includes/most_borrowed_resources.php'; ?>

            <!-- Chart Section (Previously in chart.php) -->
            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-title mb-0 fw-bold">Borrowing Trends</h5>
                                <select id="yearSelector" class="form-select form-select-sm" style="width: auto;">
                                    <?php
                                    $currentYear = date('Y');
                                    for($year = $currentYear; $year >= $currentYear - 4; $year--) {
                                        echo "<option value='$year'>$year</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="chart-container" style="position: relative; height: 300px;">
                                <canvas id="monthlyBorrowingsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 rounded-4">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4 fw-bold">Resource Distribution</h5>
                            <div class="chart-container" style="position: relative; height: 300px;">
                                <canvas id="resourceDistributionChart"></canvas>
                            </div>l
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
        // Initialize chart data that PHP provides
        const initialResourceData = {
            books: <?php echo $totalBooks; ?>,
            mediaResources: <?php echo $totalMedia; ?>,
            periodicals: <?php echo $totalPeriodicals; ?>
        };
        const initialMonthlyData = [<?php echo implode(',', $monthlyBorrowings); ?>];
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Resource Distribution Chart
            var ctx = document.getElementById('resourceDistributionChart').getContext('2d');
            var resourceData = {
                labels: ['Books', 'Periodicals', 'Media Resources'],
                datasets: [{
                    data: [
                        <?php echo $totalBooks; ?>,
                        <?php echo $totalPeriodicals; ?>,
                        <?php echo $totalMedia; ?>
                    ],
                    backgroundColor: [
                        '#2B3377',  // Dark blue for Books
                        '#FF00FF',  // Magenta for Periodicals
                        '#0047FF'   // Bright blue for Media Resources
                    ],
                    borderWidth: 0,
                    spacing: 2,
                    hoverOffset: 15
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
                            position: 'right',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                                font: {
                                    size: 12,
                                    weight: 'bold',
                                    family: "'Arial', sans-serif"
                                },
                                generateLabels: function(chart) {
                                    const data = chart.data;
                                    return data.labels.map((label, i) => ({
                                        text: `${label} (${data.datasets[0].data[i]})`,
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        index: i
                                    }));
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ` ${context.raw} items`;
                                }
                            },
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            titleColor: '#000',
                            bodyColor: '#000',
                            borderColor: '#ddd',
                            borderWidth: 1,
                            padding: 10,
                            displayColors: true,
                            bodyFont: {
                                size: 12
                            }
                        }
                    },
                    layout: {
                        padding: {
                            top: 20,
                            bottom: 20,
                            left: 20,
                            right: 20
                        }
                    }
                }
            });

            // Monthly Borrowings Chart
            let monthlyBorrowingsChart;

            function initializeMonthlyChart(data) {
                var monthlyCtx = document.getElementById('monthlyBorrowingsChart').getContext('2d');
                var monthlyData = {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                    datasets: [{
                        label: 'Borrowings',
                        data: data,
                        backgroundColor: 'rgba(43, 51, 119, 0.8)',
                        borderColor: '#2B3377',
                        borderWidth: 1,
                        borderRadius: 8,
                        maxBarThickness: 40,
                        hoverBackgroundColor: '#2B3377'
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
                        aspectRatio: 2,
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    display: true,
                                    color: 'rgba(0, 0, 0, 0.05)',
                                    drawBorder: false
                                },
                                ticks: {
                                    stepSize: 1,
                                    font: {
                                        size: 12,
                                        family: "'Arial', sans-serif"
                                    },
                                    padding: 10
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 12,
                                        family: "'Arial', sans-serif"
                                    },
                                    padding: 5
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'Monthly Borrowings ' + document.getElementById('yearSelector').value,
                                font: {
                                    size: 16,
                                    weight: 'bold',
                                    family: "'Arial', sans-serif"
                                },
                                padding: {
                                    top: 10,
                                    bottom: 30
                                },
                                color: '#2B3377'
                            },
                            tooltip: {
                                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                                titleColor: '#000',
                                bodyColor: '#000',
                                borderColor: '#ddd',
                                borderWidth: 1,
                                padding: 10,
                                displayColors: false,
                                callbacks: {
                                    label: function(context) {
                                        return `${context.parsed.y} borrowings`;
                                    }
                                }
                            }
                        },
                        hover: {
                            mode: 'index',
                            intersect: false
                        }
                    }
                });
            }

            initializeMonthlyChart(initialMonthlyData);

            document.getElementById('yearSelector').addEventListener('change', function() {
                fetch(`../controller/get_monthly_borrowings.php?year=${this.value}`)
                    .then(response => response.json())
                    .then(data => {
                        initializeMonthlyChart(data);
                    })
                    .catch(error => console.error('Error:', error));
            });
        });
    </script>

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

    <!-- Resource Details Modal (ensure this is present only once) -->
    <div class="modal fade" id="resourceDetailsModal" tabindex="-1" aria-labelledby="resourceDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resourceDetailsModalLabel">Resource Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="resourceDetailsContent">
                    <div class="text-center">Loading...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modify the image sections to be clickable -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const resourceImages = document.querySelectorAll('.resource-image');
        resourceImages.forEach(img => {
            img.style.cursor = 'pointer';
            img.addEventListener('click', function() {
                const resourceId = this.dataset.resourceId;
                const resourceType = this.dataset.resourceType;
                fetchResourceDetails(resourceId, resourceType);
            });
        });

        function fetchResourceDetails(resourceId, resourceType) {
            // Clear modal content before fetching
            document.getElementById('resourceDetailsContent').innerHTML = '<div class="text-center">Loading...</div>';
            // Only show modal after data is loaded!
            fetch(`../api/get_resource_details.php?resource_id=${resourceId}&type=${resourceType}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayResourceDetails(data.resource);
                    } else {
                        document.getElementById('resourceDetailsContent').innerHTML = '<div class="text-danger">Error loading resource details.</div>';
                    }
                })
                .catch(error => {
                    document.getElementById('resourceDetailsContent').innerHTML = '<div class="text-danger">Error loading resource details.</div>';
                });
        }

        function displayResourceDetails(resource) {
            const statusColor = resource.status && resource.status.toLowerCase() === 'available' 
                ? 'text-success' 
                : resource.status && resource.status.toLowerCase() === 'borrowed' 
                    ? 'text-warning' 
                    : 'text-muted';

            let detailsHtml = `
                <div class="text-center mb-4">
                    <img src="../${resource.cover_image || 'assets/images/default1.png'}" 
                         class="img-fluid rounded shadow-sm" 
                         style="max-height: 200px;" 
                         alt="Resource Cover">
                </div>
                <div class="resource-details">
                    <h6 class="fw-bold">Title:</h6>
                    <p>${resource.title || 'N/A'}</p>
                    <h6 class="fw-bold">Category:</h6>
                    <p>${resource.category || 'N/A'}</p>
                    <h6 class="fw-bold">Status:</h6>
                    <p class="fw-bold ${statusColor}">${resource.status ? resource.status.toUpperCase() : 'N/A'}</p>`;

            // Book details
            if (resource.author) {
                detailsHtml += `
                    <h6 class="fw-bold">Author:</h6>
                    <p>${resource.author}</p>
                    <h6 class="fw-bold">ISBN:</h6>
                    <p>${resource.isbn}</p>
                    <h6 class="fw-bold">Publisher:</h6>
                    <p>${resource.publisher}</p>
                    <h6 class="fw-bold">Edition:</h6>
                    <p>${resource.edition}</p>
                    <h6 class="fw-bold">Publication Date:</h6>
                    <p>${resource.publication_date}</p>`;
            }

            // Periodical details
            if (resource.volume || resource.issue) {
                detailsHtml += `
                    <h6 class="fw-bold">Volume:</h6>
                    <p>${resource.volume || 'N/A'}</p>
                    <h6 class="fw-bold">Issue:</h6>
                    <p>${resource.issue || 'N/A'}</p>
                    <h6 class="fw-bold">Publication Date:</h6>
                    <p>${resource.publication_date || 'N/A'}</p>`;
            }

            // Media details
            if (resource.media_type || resource.runtime || resource.format) {
                detailsHtml += `
                    <h6 class="fw-bold">Media Type:</h6>
                    <p>${resource.media_type || 'N/A'}</p>
                    <h6 class="fw-bold">Runtime:</h6>
                    <p>${resource.runtime || 'N/A'}</p>
                    <h6 class="fw-bold">Format:</h6>
                    <p>${resource.format || 'N/A'}</p>`;
            }

            document.getElementById('resourceDetailsContent').innerHTML = detailsHtml;
            // Only show the modal here!
            const modal = new bootstrap.Modal(document.getElementById('resourceDetailsModal'), {
                backdrop: true,
                keyboard: true,
                focus: true
            });
            modal.show();
        }
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var resourceModal = document.getElementById('resourceDetailsModal');
        resourceModal.addEventListener('hidden.bs.modal', function () {
            // Remove any lingering modal-backdrop
            document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
                backdrop.parentNode.removeChild(backdrop);
            });
            // Remove blur from body if present
            document.body.classList.remove('modal-open');
            document.body.style = '';
        });
    });
    </script>
</body>
</html>