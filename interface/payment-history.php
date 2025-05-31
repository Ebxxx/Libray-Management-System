<?php
require_once '../app/handler/Session.php';
require_once '../app/handler/BorrowingController.php';

// Start the session and check login status
Session::start();

// Restrict access to admin and staff
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    header("Location: ../login.php");
    exit();
}

// Create an instance of BorrowingController
$borrowingController = new BorrowingController();

// Fetch all borrowings with fines and overdue status
try {
    $conn = (new Database())->getConnection();
    $query = "SELECT 
                fp.payment_id,
                fp.borrowing_id,
                fp.amount_paid,
                fp.payment_date,
                fp.payment_status::text as payment_status,
                fp.payment_notes,
                fp.cash_received,
                fp.change_amount,
                b.borrow_date,
                b.due_date,
                b.return_date,
                b.status as borrowing_status,
                u.first_name,
                u.last_name,
                u.email,
                u.role,
                lr.title as resource_title,
                lr.category as resource_type,
                CASE 
                    WHEN lr.category = 'book' THEN bk.author
                    WHEN lr.category = 'periodical' THEN p.volume
                    WHEN lr.category = 'media' THEN m.media_type
                END AS resource_detail,
                proc.first_name as processor_first_name,
                proc.last_name as processor_last_name,
                proc.role as processor_role
              FROM fine_payments fp
              JOIN borrowings b ON fp.borrowing_id = b.borrowing_id
              JOIN users u ON b.user_id = u.user_id
              JOIN library_resources lr ON b.resource_id = lr.resource_id
              LEFT JOIN books bk ON lr.resource_id = bk.resource_id AND lr.category = 'book'
              LEFT JOIN periodicals p ON lr.resource_id = p.resource_id AND lr.category = 'periodical'
              LEFT JOIN media_resources m ON lr.resource_id = m.resource_id AND lr.category = 'media'
              LEFT JOIN users proc ON fp.processed_by = proc.user_id
              ORDER BY fp.payment_date DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $fineResources = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total payments
    $totalPayments = array_reduce($fineResources, function($carry, $payment) {
        return $carry + floatval($payment['amount_paid']);
    }, 0);

    // Debug information
    error_log("Number of payment records found: " . count($fineResources));
    if (empty($fineResources)) {
        error_log("No payment records found in the query results");
    } else {
        error_log("First payment record: " . print_r($fineResources[0], true));
    }
} catch (PDOException $e) {
    error_log("Payment history error: " . $e->getMessage());
    $fineResources = [];
    $totalPayments = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History | Library Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet"> 
    <style>
        body {
            background-color: #f4f6f9;
        }
        .payment-history-container {
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
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <?php include 'includes/sidebarModal.php'; ?>
        
        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="payment-history-container">
                <div class="page-header d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">
                        <i class="bi bi-cash-stack me-2"></i>Payment History
                    </h2>
                    <div class="box p-3 border rounded">
                        <span>Total Payments Collected: $<?php echo number_format($totalPayments, 2); ?></span>
                    </div>
                </div>
                
                <?php if (empty($fineResources)): ?>
                    <div class="alert alert-info">No payments to display.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Borrower</th>
                                    <th>Resource</th>
                                    <th>Borrow Date</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Amount</th>
                                    <th>Payment Date</th>
                                    <th>Payment Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fineResources as $payment): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($payment['role']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($payment['resource_title']); ?>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($payment['resource_type']); 
                                                if (!empty($payment['resource_detail'])) {
                                                    echo ' - ' . htmlspecialchars($payment['resource_detail']);
                                                }
                                                ?>
                                            </small>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($payment['borrow_date'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($payment['due_date'])); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($payment['borrowing_status']); ?>
                                        </td>
                                        <td>
                                            <strong class="text-danger">$<?php echo number_format($payment['amount_paid'], 2); ?></strong>
                                        </td>
                                        <td><?php echo date('M d, Y H:i:s', strtotime($payment['payment_date'])); ?></td>
                                        <td>
                                            <?php if ($payment['payment_status'] === 'paid'): ?>
                                                <span class="badge text-success">
                                                    <i class="bi bi-check-circle"></i> Paid
                                                </span>
                                            <?php else: ?>
                                                <span class="badge text-warning">
                                                    <i class="bi bi-exclamation-circle"></i> Unpaid
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" 
                                                    data-bs-target="#paymentModal<?php echo $payment['payment_id']; ?>">
                                                <i class="bi bi-eye"></i> View Details
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- Payment Details Modal -->
                                    <div class="modal fade" id="paymentModal<?php echo $payment['payment_id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Payment Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <h6>Resource</h6>
                                                            <p class="mb-1">
                                                                <?php echo htmlspecialchars($payment['resource_title']); ?>
                                                                <br>
                                                                <small class="text-muted">
                                                                    <?php echo htmlspecialchars($payment['resource_type']); 
                                                                    if (!empty($payment['resource_detail'])) {
                                                                        echo ' - ' . htmlspecialchars($payment['resource_detail']);
                                                                    }
                                                                    ?>
                                                                </small>
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>Borrower</h6>
                                                            <p class="mb-1">
                                                                <?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?>
                                                                <br>
                                                                <small class="text-muted"><?php echo htmlspecialchars($payment['role']); ?></small>
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <h6>Due Date</h6>
                                                            <p class="mb-1"><?php echo date('M d, Y', strtotime($payment['due_date'])); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>Payment Date</h6>
                                                            <p class="mb-1"><?php echo date('M d, Y H:i:s', strtotime($payment['payment_date'])); ?></p>
                                                        </div>
                                                    </div>

                                                    <div class="card mb-3">
                                                        <div class="card-body">
                                                            <h6 class="card-title">Payment Information</h6>
                                                            <p class="card-text mb-1">
                                                                <strong>Amount:</strong> 
                                                                <span class="text-danger">$<?php echo number_format($payment['amount_paid'], 2); ?></span>
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <?php if ($payment['payment_status'] === 'paid'): ?>
                                                        <div class="alert alert-success">
                                                            <h6 class="alert-heading">Payment Completed</h6>
                                                            <p class="mb-1">
                                                                <strong>Amount Paid:</strong> $<?php echo number_format($payment['amount_paid'], 2); ?><br>
                                                                <strong>Cash Received:</strong> $<?php echo number_format($payment['cash_received'], 2); ?><br>
                                                                <strong>Change:</strong> $<?php echo number_format($payment['change_amount'], 2); ?><br>
                                                                <strong>Processed By:</strong> <?php echo htmlspecialchars($payment['processor_first_name'] . ' ' . $payment['processor_last_name']); ?> 
                                                                <small>(<?php echo htmlspecialchars($payment['processor_role']); ?>)</small>
                                                            </p>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="alert alert-warning">
                                                            <h6 class="alert-heading">Payment Pending</h6>
                                                            <p class="mb-0">
                                                                <?php echo $payment['return_date'] ? 
                                                                    'Item returned but payment is unpaid.' : 
                                                                    'Payment continues to accumulate until the resource is returned.'; ?>
                                                            </p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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