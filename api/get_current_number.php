<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Get the latest queue number (regardless of date)
    date_default_timezone_set('Asia/Jakarta');
    $today = date('Y-m-d');
    
    // Get the latest queue regardless of date
    $stmt = $pdo->prepare("
        SELECT no_antrian, tanggal, DATE(tanggal) as queue_date
        FROM antrian 
        WHERE id_loket = ?
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stmt->execute([$id_loket]);
    $lastQueue = $stmt->fetch();
    
    if ($lastQueue) {
        $lastQueueDate = $lastQueue['queue_date'];
        $isNewDay = ($lastQueueDate !== $today);
        
        if ($isNewDay) {
            // Different date - show F000 to indicate reset needed
            echo json_encode([
                'success' => true,
                'currentNumber' => 'F000',
                'timestamp' => null,
                'is_new_day' => true,
                'last_queue_date' => $lastQueueDate,
                'current_date' => $today,
                'message' => 'New day detected - queue will reset to F001'
            ]);
        } else {
            // Same date - show current number
            echo json_encode([
                'success' => true,
                'currentNumber' => $lastQueue['no_antrian'],
                'timestamp' => $lastQueue['tanggal'],
                'is_new_day' => false,
                'last_queue_date' => $lastQueueDate,
                'current_date' => $today,
                'message' => 'Continuing same day queue'
            ]);
        }
    } else {
        // No queue data at all
        echo json_encode([
            'success' => true,
            'currentNumber' => 'F000',
            'timestamp' => null,
            'is_new_day' => true,
            'last_queue_date' => null,
            'current_date' => $today,
            'message' => 'No queue data found - will start from F001'
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