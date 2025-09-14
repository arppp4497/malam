<?php
require_once 'config.php';

header('Content-Type: application/json');

try {
    $type = $_GET['type'] ?? null;
    $search = trim($_GET['search'] ?? '');

    $query = "SELECT * FROM photos WHERE 1=1";
    $params = [];

    if ($type && in_array($type, ['my', 'partner'])) {
        $query .= " AND type = ?";
        $params[] = $type;
    }

    if ($search) {
        $query .= " AND (title LIKE ? OR description LIKE ? OR category LIKE ?)";
        $search_param = '%' . $search . '%';
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }

    $query .= " ORDER BY uploaded_at DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Convert image_path to full URL for client
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
    foreach ($photos as &$photo) {
        $photo['image_url'] = $base_url . '/' . $photo['image_path'];
    }

    echo json_encode([
        'success' => true,
        'photos' => $photos
    ]);

} catch (Exception $e) {
    error_log('Get photos error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred',
        'photos' => []
    ]);
}
?>
