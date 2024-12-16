<?php
require_once '../controller/Session.php';
require_once '../controller/BorrowingController.php';

// Start the session and check login status
Session::start();

// Restrict access to admin and staff
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    header("Location: ../login.php");
    exit();
}

// Create an instance of BorrowingController
$borrowingController = new BorrowingController();

// Fetch all borrowings
$borrowings = $borrowingController->getAllBorrowings();

try {
    $conn = (new Database())->getConnection();
    $query = "SELECT 
                b.borrowing_id, 
                u.first_name, 
                u.last_name, 
                u.email, 
                u.role,
                lr.title AS resource_title,
                lr.category AS resource_type,
                b.borrow_date, 
                b.due_date, 
                b.status,
                DATEDIFF(CURRENT_DATE, b.due_date) as days_overdue,
                fc.fine_amount as daily_fine_rate,
                CASE 
                    WHEN DATEDIFF(CURRENT_DATE, b.due_date) > 0 
                    THEN DATEDIFF(CURRENT_DATE, b.due_date) * fc.fine_amount 
                    ELSE 0 
                END as calculated_fine
              FROM borrowings b
              JOIN users u ON b.user_id = u.user_id
              JOIN library_resources lr ON b.resource_id = lr.resource_id
              JOIN fine_configurations fc ON lr.category = fc.resource_type
              WHERE (b.status = 'overdue' OR 
                    (b.status = 'active' AND b.due_date < CURRENT_DATE))
              ORDER BY b.due_date ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $overdueResources = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total fines
    $totalOverdueFines = array_reduce($overdueResources, function($carry, $borrowing) {
        return $carry + $borrowing['calculated_fine'];
    }, 0);
} catch (PDOException $e) {
    error_log("Overdue management error: " . $e->getMessage());
    $overdueResources = [];
    $totalOverdueFines = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overdue Management | Library Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet"> 
    <style>
        body {
            background-color: #f4f6f9;
        }
        .overdue-management-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 30px;
        }
        .page-header {
            background-color: #003161;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .overdue-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'includes/sidebarModal.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="overdue-management-container">
                <div class="page-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">
                        <i class="bi bi-calendar2-x me-2"></i>Overdue Management
                    </h2>
                    <div class="box p-3 border rounded">
                            <span class="me-3">Total Overdue: <?php echo count($overdueResources); ?></span>
                            <span>Total Overdue Fines: $<?php echo number_format($totalOverdueFines, 2); ?></span>
                    </div>
                </div>
                
                <?php if (empty($overdueResources)): ?>
                    <div class="alert alert-info">No overdue resources at the moment.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Borrower</th>
                                    <th>Resource</th>
                                    <th>Borrow Date</th>
                                    <th>Due Date</th>
                                    <th>Days Overdue</th>
                                    <th>Fine Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($overdueResources as $borrowing): 
                                    // Calculate days overdue and fine
                                    $overduedays = ceil((strtotime('now') - strtotime($borrowing['due_date'])) / (60 * 60 * 24));
                                    $fineAmount = $overduedays * 1; // $1 per day overdue
                                ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($borrowing['first_name'] . ' ' . $borrowing['last_name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($borrowing['role']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($borrowing['resource_title']); ?>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($borrowing['resource_type']); ?></small>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($borrowing['borrow_date'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($borrowing['due_date'])); ?></td>
                                        <td>
                                        <span class="text-danger fw-bold">
                                                <?php echo $borrowing['days_overdue']; ?> days
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-success">$<?php echo number_format($borrowing['calculated_fine'], 2); ?></strong>
                                            <br>
                                            <small class="text-muted">Rate: $<?php echo number_format($borrowing['daily_fine_rate'], 2); ?>/day</small>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" 
                                                    data-bs-target="#overdueModal<?php echo $borrowing['borrowing_id']; ?>">
                                                <i class="bi bi-exclamation-triangle"></i> Details
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Overdue Details Modal -->
                                    <div class="modal fade" id="overdueModal<?php echo $borrowing['borrowing_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Overdue Resource Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <h6>Borrower Information</h6>
                                                    <p>
                                                        <strong>Name:</strong> <?php echo htmlspecialchars($borrowing['first_name'] . ' ' . $borrowing['last_name']); ?><br>
                                                        <strong>Email:</strong> <?php echo htmlspecialchars($borrowing['email']); ?><br>
                                                        <strong>Role:</strong> <?php echo htmlspecialchars($borrowing['role']); ?>
                                                    </p>

                                                    <h6>Resource Details</h6>
                                                    <p>
                                                        <strong>Title:</strong> <?php echo htmlspecialchars($borrowing['resource_title']); ?><br>
                                                        <strong>Type:</strong> <?php echo htmlspecialchars($borrowing['resource_type']); ?>
                                                    </p>

                                                    <h6>Overdue Information</h6>
                                                    <p>
                                                        <strong>Borrow Date:</strong> <?php echo date('M d, Y', strtotime($borrowing['borrow_date'])); ?><br>
                                                        <strong>Due Date:</strong> <?php echo date('M d, Y', strtotime($borrowing['due_date'])); ?><br>
                                                        <strong>Days Overdue:</strong> <?php echo $borrowing['days_overdue']; ?> days<br>
                                                        <strong>Daily Fine Rate:</strong> $<?php echo number_format($borrowing['daily_fine_rate'], 2); ?><br>
                                                        <strong>Total Fine Amount:</strong> $<?php echo number_format($borrowing['calculated_fine'], 2); ?>
                                                    </p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <button type="button" class="btn btn-primary">Contact Borrower</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>