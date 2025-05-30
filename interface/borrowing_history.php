<?php
require_once '../app/handler/Session.php';
require_once '../app/handler/BorrowingController.php';

// Start the session and check login status
Session::start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Create an instance of BorrowingController
$borrowingController = new BorrowingController();

// Get borrowing history
$borrowingHistory = $borrowingController->displayBorrowingHistory($user_id);
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
                            
                            <?php if ($borrowingHistory === false): ?>
                                <div class="alert alert-danger">An error occurred while retrieving the borrowing history.</div>
                            <?php elseif (empty($borrowingHistory)): ?>
                                <div class="alert alert-info">No borrowing history found.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Title</th>
                                                <th>Category</th>
                                                <th>Type</th>
                                                <th>Borrow Date</th>
                                                <th>Due Date</th>
                                                <th>Return Date</th>
                                                <th>Status</th>
                                                <th>Days Overdue</th>
                                                <th>Fine</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($borrowingHistory as $record): ?>
                                                <?php 
                                                    $isOverdue = $record['current_status'] === 'overdue';
                                                    $rowClass = $isOverdue ? 'table-danger' : '';
                                                ?>
                                                <tr class="<?php echo $rowClass; ?>">
                                                    <td><?php echo htmlspecialchars($record['title']); ?></td>
                                                    <td>
                                                        <span class="badge bg-secondary">
                                                            <?php echo ucfirst(htmlspecialchars($record['category'])); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info">
                                                            <?php echo ucfirst(htmlspecialchars($record['resource_type'])); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('M d, Y', strtotime($record['borrow_date'])); ?></td>
                                                    <td>
                                                        <?php 
                                                            $dueDate = strtotime($record['due_date']);
                                                            $dueDateClass = $isOverdue ? 'text-danger fw-bold' : '';
                                                            echo "<span class='$dueDateClass'>" . date('M d, Y', $dueDate) . "</span>";
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php 
                                                            echo $record['return_date'] 
                                                                ? date('M d, Y', strtotime($record['return_date'])) 
                                                                : '<span class="text-muted">Not returned</span>';
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $statusBadge = match($record['current_status']) {
                                                            'active' => 'bg-success',
                                                            'returned' => 'bg-info',
                                                            'overdue' => 'bg-danger',
                                                            default => 'bg-secondary'
                                                        };
                                                        $statusText = ucfirst($record['current_status']);
                                                        ?>
                                                        <span class="badge <?php echo $statusBadge; ?>">
                                                            <?php echo $statusText; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($record['days_overdue'] > 0): ?>
                                                            <span class="text-danger fw-bold">
                                                                <?php echo $record['days_overdue']; ?> days
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-success">On time</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($record['calculated_fine'] > 0): ?>
                                                            <div class="text-danger">
                                                                <strong>
                                                                    $<?php echo number_format($record['calculated_fine'], 2); ?>
                                                                </strong>
                                                                <?php if ($isOverdue): ?>
                                                                    <div class="small text-muted">
                                                                        Rate: $<?php echo number_format($record['daily_fine_rate'], 2); ?>/day
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-success">No fine</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
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