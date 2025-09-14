<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    // Get form data
    $title = trim($_POST['title'] ?? '');
    $category = $_POST['category'] ?? '';
    $date = $_POST['date'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $type = $_POST['type'] ?? '';
    $uploaded_by = $_POST['uploaded_by'] ?? 'anonymous';

    // Validate required fields
    if (empty($title) || empty($date) || empty($type)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    // Validate type
    if (!in_array($type, ['my', 'partner'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid type']);
        exit;
    }

    // Handle file upload
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
        exit;
    }

    $file = $_FILES['photo'];

    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.']);
        exit;
    }

    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File size too large. Maximum 5MB allowed.']);
        exit;
    }

    // Create uploads directory if it doesn't exist
    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('photo_', true) . '.' . $file_extension;
    $filepath = $upload_dir . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to save file']);
        exit;
    }

    // Save to database
    $stmt = $pdo->prepare("INSERT INTO photos (title, category, date, description, image_path, type, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $category, $date, $description, $filepath, $type, $uploaded_by]);

    echo json_encode([
        'success' => true,
        'message' => 'Photo uploaded successfully',
        'photo_id' => $pdo->lastInsertId()
    ]);

} catch (Exception $e) {
    error_log('Upload error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>
