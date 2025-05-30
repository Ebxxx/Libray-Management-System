<?php
require_once '../app/handler/Session.php';
require_once '../app/handler/BorrowingController.php';
require_once '../config/Database.php';

// Start the session and check login status
Session::start();

// Restrict access to admin and staff
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    header("Location: ../login.php");
    exit();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create an instance of BorrowingController
$borrowingController = new BorrowingController();

// Debug: Check database connection
try {
    $db = new Database();
    $conn = $db->getConnection();
    if ($conn) {
        error_log("Database connection successful");
        
        // Debug: Direct database query to check for overdue items
        $directQuery = "
            SELECT 
                b.borrowing_id,
                b.due_date,
                b.status,
                b.return_date,
                lr.title,
                DATEDIFF(NOW(), b.due_date) as days_past_due,
                NOW() as current_time
            FROM borrowings b
            JOIN library_resources lr ON b.resource_id = lr.resource_id
            WHERE b.return_date IS NULL
            ORDER BY b.due_date ASC";
        
        $stmt = $conn->prepare($directQuery);
        $stmt->execute();
        $allActiveLoans = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("=== ALL ACTIVE LOANS DEBUG ===");
        error_log("Total active loans: " . count($allActiveLoans));
        foreach ($allActiveLoans as $loan) {
            error_log(sprintf(
                "Loan ID: %d, Title: %s, Due: %s, Status: %s, Days Past Due: %d, Current Time: %s",
                $loan['borrowing_id'],
                $loan['title'],
                $loan['due_date'],
                $loan['status'],
                $loan['days_past_due'],
                $loan['current_time']
            ));
        }
        error_log("=== END DEBUG ===");
        
    }
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
}

// Fetch overdue borrowings
try {
    $overdueResources = $borrowingController->getOverdueBorrowings();
    error_log("Retrieved overdue resources count: " . count($overdueResources));
    
    if (empty($overdueResources)) {
        error_log("No overdue resources found from controller method.");
        
        // Try a direct simple query as backup
        $backupQuery = "
            SELECT 
                b.borrowing_id,
                b.borrow_date,
                b.due_date,
                b.status,
                b.fine_amount as current_fine,
                u.first_name,
                u.last_name,
                u.email,
                u.role,
                u.membership_id,
                lr.title as resource_title,
                lr.category as resource_type,
                lr.accession_number,
                1.50 as daily_fine_rate,
                EXTRACT(DAY FROM (CURRENT_TIMESTAMP - b.due_date))::integer as days_overdue,
                EXTRACT(DAY FROM (CURRENT_TIMESTAMP - b.due_date))::integer * 1.50 as calculated_fine,
                0 as total_paid
            FROM borrowings b
            JOIN users u ON b.user_id = u.user_id
            JOIN library_resources lr ON b.resource_id = lr.resource_id
            WHERE b.due_date < CURRENT_TIMESTAMP
            AND b.return_date IS NULL
            AND EXTRACT(DAY FROM (CURRENT_TIMESTAMP - b.due_date))::integer > 0
            ORDER BY b.due_date ASC";
        
        $stmt = $conn->prepare($backupQuery);
        $stmt->execute();
        $overdueResources = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("Backup query found " . count($overdueResources) . " overdue items");
    } else {
        error_log("First overdue resource: " . json_encode($overdueResources[0]));
    }

    // Calculate total fines
    $totalOverdueFines = array_reduce($overdueResources, function($carry, $borrowing) {
        return $carry + ($borrowing['calculated_fine'] ?? 0);
    }, 0);

} catch (Exception $e) {
    error_log("Error in overdue management: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    $overdueResources = [];
    $totalOverdueFines = 0;
}

// Debug: Output current time and timezone
error_log("Current server time: " . date('Y-m-d H:i:s'));
error_log("Current timezone: " . date_default_timezone_get());
error_log("Final overdue count: " . count($overdueResources));
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
                            <span class="me-3">Total Overdue: <span id="total-overdue-count"><?php echo count($overdueResources); ?></span></span>
                            <span>Total Overdue Fines: $<span id="total-overdue-fines"><?php echo number_format($totalOverdueFines, 2); ?></span></span>
                    </div>
                </div>
                
                <div class="alert alert-info" id="no-overdue-message" style="display: <?php echo empty($overdueResources) ? 'block' : 'none'; ?>;">
                    No overdue resources at the moment.
                </div>
                
                <?php if (!empty($overdueResources)): ?>
                    <div class="table-responsive" id="overdue-table-container">
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
                            <tbody id="overdue-table-body">
                                <?php foreach ($overdueResources as $borrowing): ?>
                                    <tr id="overdue-row-<?php echo $borrowing['borrowing_id']; ?>">
                                        <td>
                                            <strong><?php echo htmlspecialchars($borrowing['first_name'] . ' ' . $borrowing['last_name']); ?></strong>
                                            <br>
                                            <small class="text-muted">ID: <?php echo htmlspecialchars($borrowing['membership_id']); ?></small>
                                            <br>
                                            <small class="text-muted"><?php echo htmlspecialchars($borrowing['role']); ?></small>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($borrowing['resource_title']); ?>
                                            <br>
                                            <small class="text-muted">Type: <?php echo htmlspecialchars($borrowing['resource_type']); ?></small>
                                            <br>
                                            <small class="text-muted">Accession: <?php echo htmlspecialchars($borrowing['accession_number']); ?></small>
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
                                            <?php if ($borrowing['current_fine'] > 0): ?>
                                            <br>
                                            <small class="text-warning">Current: $<?php echo number_format($borrowing['current_fine'], 2); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning me-2" data-bs-toggle="modal" 
                                                    data-bs-target="#overdueModal<?php echo $borrowing['borrowing_id']; ?>">
                                                <i class="bi bi-exclamation-triangle"></i> Details
                                            </button>
                                            <button class="btn btn-sm btn-success" onclick="openPayFineModal(<?php echo $borrowing['borrowing_id']; ?>, <?php echo $borrowing['calculated_fine']; ?>)">
                                                <i class="bi bi-cash"></i> Pay Fine
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
                                                        <strong>Total Fine Amount:</strong> $<?php echo number_format($borrowing['calculated_fine'], 2); ?><br>
                                                        <strong>Amount Paid:</strong> $<?php echo number_format($borrowing['total_paid'], 2); ?><br>
                                                        <strong>Remaining Balance:</strong> $<?php echo number_format($borrowing['calculated_fine'] - $borrowing['total_paid'], 2); ?>
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

    <!-- Pay Fine Modal -->
    <div class="modal fade" id="payFineModal" tabindex="-1" aria-labelledby="payFineModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="payFineModalLabel">Pay Fine</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="payFineForm" action="process_fine_payment.php" method="POST">
                     <input type="hidden" name="borrowing_id" id="modal_borrowing_id">
                    <input type="hidden" name="calculated_fine" id="modal_calculated_fine">
                    
                    <div class="mb-3">
                        <label for="amount_paid" class="form-label">Fine Amount</label>
                        <input type="number" step="0.01" class="form-control" id="amount_paid" name="amount_paid" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="cash_received" class="form-label">Cash Received</label>
                        <input type="number" step="0.01" class="form-control" id="cash_received" name="cash_received" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="change_amount" class="form-label">Change</label>
                        <input type="number" step="0.01" class="form-control" id="change_amount" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="payment_notes" class="form-label">Payment Notes</label>
                        <textarea class="form-control" id="payment_notes" name="payment_notes" rows="3"></textarea>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="submitPayment" disabled>Submit Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cashReceivedInput = document.getElementById('cash_received');
    const fineAmountInput = document.getElementById('amount_paid');
    const changeAmountInput = document.getElementById('change_amount');
    const submitPaymentButton = document.getElementById('submitPayment');
    const payFineForm = document.getElementById('payFineForm');

    // Input validation and change calculation
    cashReceivedInput.addEventListener('input', function() {
        const fineAmount = parseFloat(fineAmountInput.value) || 0;
        const cashReceived = parseFloat(this.value) || 0;
        
        // Calculate change
        const change = cashReceived - fineAmount;
        changeAmountInput.value = change.toFixed(2);
        
        // Enable/disable submit button based on payment amount
        submitPaymentButton.disabled = cashReceived < fineAmount;
    });

    // Modal opening function
    window.openPayFineModal = function(borrowingId, calculatedFine) {
        document.getElementById('modal_borrowing_id').value = borrowingId;
        document.getElementById('modal_calculated_fine').value = calculatedFine;
        fineAmountInput.value = calculatedFine;
        cashReceivedInput.value = '';
        changeAmountInput.value = '';
        document.getElementById('payment_notes').value = '';
        submitPaymentButton.disabled = true;
        
        var modal = new bootstrap.Modal(document.getElementById('payFineModal'));
        modal.show();
    };

    // Form submission handler
    payFineForm.addEventListener('submit', function(event) {
        event.preventDefault();

        const fineAmount = parseFloat(fineAmountInput.value);
        const cashReceived = parseFloat(cashReceivedInput.value);

        // Additional validation
        if (cashReceived < fineAmount) {
            alert('Cash received must be greater than or equal to the fine amount.');
            return;
        }

        // Disable submit button to prevent double submission
        submitPaymentButton.disabled = true;
        submitPaymentButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';

        // Submit payment via AJAX
        fetch('process_fine_payment.php', {
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Print receipt
                printReceipt(data.amount_paid, data.change);
                // Remove the paid row from the table
                const paidRow = document.getElementById('overdue-row-' + document.getElementById('modal_borrowing_id').value);
                if (paidRow) paidRow.remove();
                // Update total overdue and fines
                const totalOverdueCountElem = document.getElementById('total-overdue-count');
                const totalOverdueFinesElem = document.getElementById('total-overdue-fines');
                if (totalOverdueCountElem && totalOverdueFinesElem) {
                    // Decrement the count
                    let currentCount = parseInt(totalOverdueCountElem.textContent, 10) || 1;
                    totalOverdueCountElem.textContent = Math.max(currentCount - 1, 0);

                    // Subtract the paid fine from the total
                    let currentFines = parseFloat(totalOverdueFinesElem.textContent.replace(/[^0-9.-]+/g, "")) || 0;
                    let paidAmount = parseFloat(data.amount_paid) || 0;
                    totalOverdueFinesElem.textContent = (currentFines - paidAmount).toFixed(2);
                }
                // If no more rows, hide the table and show the no-overdue message
                const overdueTableBody = document.getElementById('overdue-table-body');
                const overdueTableContainer = document.getElementById('overdue-table-container');
                if (overdueTableBody && overdueTableBody.children.length === 0) {
                    if (overdueTableContainer) overdueTableContainer.style.display = 'none';
                    const noOverdueMsg = document.getElementById('no-overdue-message');
                    if (noOverdueMsg) noOverdueMsg.style.display = 'block';
                }
                // Show success message
                alert('Payment processed successfully!');
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('payFineModal')).hide();
            } else {
                throw new Error(data.message || 'Payment processing failed');
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
            submitPaymentButton.disabled = false;
            submitPaymentButton.innerHTML = 'Submit Payment';
        });
    });

    function printReceipt(amountPaid, change) {
        const fineAmount = document.getElementById('amount_paid').value;
        const cashReceived = document.getElementById('cash_received').value;
        const paymentNotes = document.getElementById('payment_notes').value;

        // Ensure change is a number
        const changeNumber = parseFloat(change) || 0;

        const receiptHTML = `
            <style>
                .receipt {
                    font-family: 'Courier New', monospace;
                    width: 300px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .receipt-header {
                    text-align: center;
                    margin-bottom: 20px;
                }
                .receipt-body {
                    margin-bottom: 20px;
                }
                .receipt-footer {
                    text-align: center;
                    margin-top: 20px;
                    border-top: 1px dashed #000;
                    padding-top: 10px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                td {
                    padding: 5px;
                }
                .text-right {
                    text-align: right;
                }
            </style>

            <div class="receipt">
                <div class="receipt-header">
                    <h2>Payment Receipt</h2>
                    <p>Date: ${new Date().toLocaleDateString()}</p>
                    <p>Time: ${new Date().toLocaleTimeString()}</p>
                </div>

                <div class="receipt-body">
                    <table>
                        <tr>
                            <td>Fine Amount:</td>
                            <td class="text-right">$${fineAmount}</td>
                        </tr>
                        <tr>
                            <td>Cash Received:</td>
                            <td class="text-right">$${cashReceived}</td>
                        </tr>
                        <tr>
                            <td>Change:</td>
                            <td class="text-right">$${changeNumber.toFixed(2)}</td>
                        </tr>
                    </table>
                    ${paymentNotes ? `<p>Notes: ${paymentNotes}</p>` : ''}
                </div>

                <div class="receipt-footer">
                    <p>Thank you for your payment!</p>
                </div>
            </div>
        `;

        const printWindow = window.open('', 'print');
        printWindow.document.write(receiptHTML);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    }
});
</script>
</body>
</html>






