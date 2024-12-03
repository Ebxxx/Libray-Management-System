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
    $message = $result ? "Resource borrowed successfully!" : "Unable to borrow resource. Check your borrowing limit or resource availability.";
}

// Get resource type from GET parameter, default to books
$resourceType = isset($_GET['type']) ? $_GET['type'] : 'books';

// Get available resources based on type
switch ($resourceType) {
    case 'media':
        $availableResources = $borrowingController->getAvailableMedia();
        $columns = ['title', 'category', 'accession_number', 'media_type', 'runtime', 'format'];
        break;
    case 'periodicals':
        $availableResources = $borrowingController->getAvailablePeriodicals();
        $columns = ['title', 'category', 'accession_number', 'publisher', 'publication_date', 'volume', 'issue'];
        break;
    default: // books
        $availableResources = $borrowingController->getAvailableBooks();
        $columns = ['title', 'category', 'author', 'isbn', 'publisher', 'accession_number'];
}

// Search functionality
$searchTerm = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';
if (!empty($searchTerm)) {
    $availableResources = array_filter($availableResources, function($resource) use ($searchTerm, $columns) {
        foreach ($columns as $column) {
            if (isset($resource[$column]) && 
                strpos(strtolower($resource[$column]), $searchTerm) !== false) {
                return true;
            }
        }
        return false;
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Resources</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.7.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="../css/custom.css" rel="stylesheet">
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
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h2 class="mb-0">Available Resources</h2>
                                
                                <!-- Resource Type and Search Dropdown -->
                                <div class="d-flex align-items-center">
                                    <form method="GET" class="d-flex" id="resourceForm">
                                        <select name="type" class="form-select me-2" style="width: 150px;" onchange="this.form.submit()">
                                            <option value="books" <?php echo $resourceType == 'books' ? 'selected' : ''; ?>>Books</option>
                                            <option value="media" <?php echo $resourceType == 'media' ? 'selected' : ''; ?>>Media</option>
                                            <option value="periodicals" <?php echo $resourceType == 'periodicals' ? 'selected' : ''; ?>>Periodicals</option>
                                        </select>
                                        
                                        <input type="text" name="search" class="form-control me-2" placeholder="Search..." 
                                               value="<?php echo htmlspecialchars($searchTerm); ?>"
                                               style="width: 200px;">
                                        
                                        <button type="submit" class="btn btn-light">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <?php if (isset($message)): ?>
                                    <div class="alert <?php echo $result ? 'alert-success' : 'alert-danger'; ?>">
                                        <?php echo $message; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Resources Table -->
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <?php 
                                                $headerMap = [
                                                    'books' => ['Title', 'Category', 'Author', 'ISBN', 'Publisher', 'Accession Number'],
                                                    'media' => ['Title', 'Category', 'Accession Number', 'Media Type', 'Runtime', 'Format'],
                                                    'periodicals' => ['Title', 'Category', 'Accession Number', 'ISSN', 'Publication Date', 'Volume', 'Issue']
                                                ];
                                                foreach ($headerMap[$resourceType] as $header): ?>
                                                    <th><?php echo $header; ?></th>
                                                <?php endforeach; ?>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($availableResources as $resource): ?>
                                            <tr>
                                                <?php 
                                                $displayMap = [
                                                    'books' => [
                                                        'title', 'category', 'author', 'isbn', 'publisher', 'accession_number'
                                                    ],
                                                    'media' => [
                                                        'title', 'category', 'accession_number', 'media_type', 'runtime', 'format'
                                                    ],
                                                    'periodicals' => [
                                                        'title', 'category', 'accession_number', 'publisher', 
                                                        'publication_date', 'volume', 'issue'
                                                    ]
                                                ];
                                                
                                                foreach ($displayMap[$resourceType] as $field): 
                                                    $displayValue = $field === 'publisher' && $resourceType === 'periodicals' 
                                                        ? $resource['publisher'] 
                                                        : ($resource[$field] ?? 'N/A');
                                                ?>
                                                    <td><?php echo htmlspecialchars($displayValue); ?></td>
                                                <?php endforeach; ?>
                                                <td>
                                                    <form method="POST">
                                                        <input type="hidden" name="resource_id" value="<?php echo $resource['resource_id']; ?>">
                                                        <button type="submit" class="btn btn-primary btn-sm">Borrow</button>
                                                    </form>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>

                                    <?php if (empty($availableResources)): ?>
                                        <div class="alert alert-info text-center">
                                            No resources available in this category.
                                        </div>
                                    <?php endif; ?>
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