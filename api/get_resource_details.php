<?php
require_once '../config/Database.php';
require_once '../app/handler/BookController.php';
require_once '../app/handler/MediaResourceController.php';
require_once '../app/handler/PeriodicalController.php';

header('Content-Type: application/json');

if (!isset($_GET['resource_id']) || !isset($_GET['type'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$resourceId = $_GET['resource_id'];
$resourceType = $_GET['type'];
$resource = null;

try {
    switch ($resourceType) {
        case 'book':
            $bookController = new BookController();
            $resource = $bookController->getBookById($resourceId);
            break;
        case 'periodical':
            $periodicalController = new PeriodicalController();
            $periodicals = $periodicalController->getPeriodicals();
            foreach ($periodicals as $periodical) {
                if ($periodical['resource_id'] == $resourceId) {
                    $resource = $periodical;
                    break;
                }
            }
            break;
        case 'media':
            $mediaController = new MediaResourceController();
            $mediaResources = $mediaController->getMediaResources();
            foreach ($mediaResources as $media) {
                if ($media['resource_id'] == $resourceId) {
                    $resource = $media;
                    break;
                }
            }
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid resource type']);
            exit;
    }

    if ($resource) {
        echo json_encode([
            'success' => true,
            'resource' => $resource
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Resource not found'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving resource: ' . $e->getMessage()
    ]);
} 