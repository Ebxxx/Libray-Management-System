<?php
require_once '../controller/Session.php';
require_once '../controller/BorrowingController.php';

// Start the session and check login status
Session::start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrowing History | Library Management System</title> 
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .borrowing-history-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 10px;
        }
        .page-header {
            background-color: #003161;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'includes/sidebarModal.php'; ?>
        
        <!-- Main Content Area -->
        <main class="flex-grow-1 bg-light">
            <div class="container-fluid p-4">
                <div class="row">
                    <div class="col-12">
                        <div class="borrowing-history-container">
                            <div class="page-header">
                                <h2 class="mb-0">
                                    <i class="bi bi-arrow-left-right me-2"></i>Borrowing History
                                </h2>
                            </div>
                            
                            <?php
                            // Function to display borrowing history
                            function displayBorrowingHistory($user_id) {
                                // Create an instance of BorrowingController
                                $borrowingController = new BorrowingController();
                                
                                // Fetch borrowing history for the user
                                $borrowingHistory = $borrowingController->getUserBorrowingHistory($user_id);
                                
                                // Check if there are any borrowing records
                                if (empty($borrowingHistory)) {
                                    echo '<div class="alert alert-info">No borrowing history found.</div>';
                                    return;
                                }
                                
                                // Start table to display borrowing history
                                echo '<div class="table-responsive">';
                                echo '<table class="table table-striped table-hover">';
                                echo '<thead class="table-dark">
                                        <tr>
                                            <th>Title</th>
                                            <th>Borrow Date</th>
                                            <th>Due Date</th>
                                            <th>Return Date</th>
                                            <th>Status</th>
                                            <th>Fine Amount</th>
                                        </tr>
                                      </thead>';
                                echo '<tbody>';
                                
                                // Loop through borrowing history and display each record
                                foreach ($borrowingHistory as $record) {
                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($record['title']) . '</td>';
                                    echo '<td>' . date('M d, Y', strtotime($record['borrow_date'])) . '</td>';
                                    echo '<td>' . date('M d, Y', strtotime($record['due_date'])) . '</td>';
                                    
                                    // Handle return date display
                                    $returnDate = $record['return_date'] 
                                        ? date('M d, Y', strtotime($record['return_date'])) 
                                        : 'Not returned';
                                    echo '<td>' . $returnDate . '</td>';
                                    
                                    // Status formatting
                                    $statusClass = '';
                                    switch ($record['status']) {
                                        case 'active':
                                            $statusClass = 'text-warning';
                                            break;
                                        case 'returned':
                                            $statusClass = 'text-success';
                                            break;
                                        case 'overdue':
                                            $statusClass = 'text-danger';
                                            break;
                                    }
                                    echo '<td><span class="' . $statusClass . '">' . 
                                         htmlspecialchars(ucfirst($record['status'])) . '</span></td>';
                                    
                                    // Fine amount display
                                    $fineAmount = $record['fine_amount'] > 0 
                                        ? '$' . number_format($record['fine_amount'], 2) 
                                        : 'No fine';
                                    echo '<td>' . $fineAmount . '</td>';
                                    
                                    echo '</tr>';
                                }
                                
                                echo '</tbody>';
                                echo '</table>';
                                echo '</div>';
                            }

                            // Call the function to display borrowing history
                            displayBorrowingHistory($user_id);
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>