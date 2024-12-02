<?php
require_once '../controller/Session.php';
require_once '../controller/BorrowingController.php';
require_once '../controller/ResourceController.php';

Session::start();

// Check if user is logged in and has appropriate role
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'student' && $_SESSION['role'] !== 'faculty')) {
    header("Location: ../login.php");
    exit();
}

$borrowingController = new BorrowingController();
$resourceController = new ResourceController();

// Handle borrowing request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['resource_id'])) {
    $result = $borrowingController->borrowResource($_SESSION['user_id'], $_POST['resource_id']);
    $message = $result ? "Book borrowed successfully!" : "Unable to borrow book. Check your borrowing limit or resource availability.";
}

// Debug: Get all available resources without category filter
$availablePeriodicals = $borrowingController->getAvailablePeriodicals();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Books</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.7.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../css/custom.css" rel="stylesheet"> <!-- Assuming you have a custom CSS file -->
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
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h2 class="mb-0">Available Periodicals</h2>
                            </div>
                            <div class="card-body">
                                <?php if (isset($message)): ?>
                                    <div class="alert <?php echo $result ? 'alert-success' : 'alert-danger'; ?>">
                                        <?php echo $message; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Debug: Resource Categories -->
                                <!-- <div class="alert alert-info">
                                    Available Resource Categories:
                                    <?php 
                                    $categories = array_unique(array_column($availablePeriodicals, 'category'));
                                    echo implode(', ', $categories);
                                    ?>
                                </div> -->

                                <!-- Resources Table -->
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Accession Number</th>
                                        <th>ISSN</th>
                                        <th>Publication Date</th>
                                        <th>Volume</th>
                                        <th>Issue</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($availablePeriodicals as $periodicals): ?>
                                    <tr>          
                                        <td><?php echo htmlspecialchars($periodicals['title']); ?></td>
                                        <td><?php echo htmlspecialchars($periodicals['category']); ?></td>
                                        <td><?php echo htmlspecialchars($periodicals['accession_number']); ?></td>
                                        <td><?php echo htmlspecialchars($periodicals['publisher']); ?></td>
                                        <td><?php echo htmlspecialchars($periodicals['publication_date']); ?></td>
                                        <td><?php echo htmlspecialchars($periodicals['volume']); ?></td>
                                        <td><?php echo htmlspecialchars($periodicals['issue']); ?></td>
                                        <td>
                                            <form method="POST">
                                                <input type="hidden" name="resource_id" value="<?php echo $periodicals['resource_id']; ?>">
                                                <button type="submit" class="btn btn-primary btn-sm">Borrow</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>