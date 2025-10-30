<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Get current date
    date_default_timezone_set('Asia/Jakarta');
    $today = date('Y-m-d');
    
    // Check if there are any queues for today
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM antrian 
        WHERE id_loket = ? AND DATE(tanggal) = ?
    ");
    $stmt->execute([$id_loket, $today]);
    $result = $stmt->fetch();
    
    if ($result['count'] > 0) {
        // There are already queues for today, no need to reset
        echo json_encode([
            'success' => true,
            'message' => 'Queue already exists for today, no reset needed',
            'date' => $today,
            'queue_count' => $result['count']
        ]);
    } else {
        // No queues for today, this is expected behavior
        // The system will automatically start from F001 when first queue is taken
        echo json_encode([
            'success' => true,
            'message' => 'No queues found for today, system will start from F001 on first queue',
            'date' => $today,
            'queue_count' => 0
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>