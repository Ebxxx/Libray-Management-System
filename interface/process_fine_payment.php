<?php
require_once '../controller/Session.php';
require_once '../config/Database.php';
require_once '../controller/BorrowingController.php';

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

try {
    // Validate input
    if (!isset($_POST['borrowing_id']) || !isset($_POST['amount_paid']) || !isset($_POST['cash_received'])) {
        throw new Exception('Missing required payment information');
    }

    $borrowing_id = filter_var($_POST['borrowing_id'], FILTER_VALIDATE_INT);
    $amount_paid = filter_var($_POST['amount_paid'], FILTER_VALIDATE_FLOAT);
    $cash_received = filter_var($_POST['cash_received'], FILTER_VALIDATE_FLOAT);
    $payment_notes = isset($_POST['payment_notes']) ? trim($_POST['payment_notes']) : '';

    if (!$borrowing_id || !$amount_paid || !$cash_received) {
        throw new Exception('Invalid payment information');
    }

    if ($cash_received < $amount_paid) {
        throw new Exception('Cash received is less than the amount to be paid');
    }

    // Get database connection
    $database = new Database();
    $conn = $database->getConnection();

    // Begin transaction
    $conn->beginTransaction();

    // Insert payment record
    $change_amount = $cash_received - $amount_paid;

    $payment_query = "INSERT INTO fine_payments (
        borrowing_id,
        amount_paid,
        cash_received,
        change_amount,
        payment_date,
        payment_status,
        payment_notes,
        processed_by
    ) VALUES (
        :borrowing_id,
        :amount_paid,
        :cash_received,
        :change_amount,
        CURRENT_TIMESTAMP,
        'paid'::payment_status,
        :payment_notes,
        :processed_by
    )";

    $stmt = $conn->prepare($payment_query);
    $stmt->bindParam(':borrowing_id', $borrowing_id);
    $stmt->bindParam(':amount_paid', $amount_paid);
    $stmt->bindParam(':cash_received', $cash_received);
    $stmt->bindParam(':change_amount', $change_amount);
    $stmt->bindParam(':payment_notes', $payment_notes);
    $stmt->bindParam(':processed_by', $_SESSION['user_id']);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to record payment');
    }

    // Update borrowing record with new fine amount
    $update_query = "UPDATE borrowings 
                    SET fine_amount = COALESCE(fine_amount, 0) + :amount_paid
                    WHERE borrowing_id = :borrowing_id";
    
    $stmt = $conn->prepare($update_query);
    $stmt->bindParam(':amount_paid', $amount_paid);
    $stmt->bindParam(':borrowing_id', $borrowing_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update borrowing record');
    }

    // Log the activity
    $activityLogger = new ActivityLogController();
    $description = sprintf(
        "Fine payment processed - Amount: $%.2f, Borrowing ID: %d",
        $amount_paid,
        $borrowing_id
    );
    $activityLogger->logActivity($_SESSION['user_id'], 'fine_payment', $description);

    // Commit transaction
    $conn->commit();

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Payment processed successfully',
        'amount_paid' => $amount_paid,
        'change' => $change_amount
    ]);

} catch (Exception $e) {
    // Rollback transaction if active
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }

    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
