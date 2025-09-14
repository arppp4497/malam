<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Photo ID is required']);
        exit;
    }

    // Get photo details from database
    $stmt = $pdo->prepare("SELECT image_path FROM photos WHERE id = ?");
    $stmt->execute([$id]);
    $photo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$photo) {
        echo json_encode(['success' => false, 'message' => 'Photo not found']);
        exit;
    }

    // Delete file from server
    if (file_exists($photo['image_path'])) {
        unlink($photo['image_path']);
    }

    // Delete from database
    $stmt = $pdo->prepare("DELETE FROM photos WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['success' => true, 'message' => 'Photo deleted successfully']);

} catch (Exception $e) {
    error_log('Delete error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>
