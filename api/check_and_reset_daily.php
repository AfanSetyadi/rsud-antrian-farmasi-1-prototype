<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config.php';

try {
    // Get current date and time
    $now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
    $today = $now->format('Y-m-d');
    $currentTime = $now->format('H:i:s');
    
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
    
    // Count total queues for today
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) as count
        FROM antrian 
        WHERE id_loket = ? AND DATE(tanggal) = ?
    ");
    $countStmt->execute([$id_loket, $today]);
    $countResult = $countStmt->fetch();
    
    $response = [
        'success' => true,
        'current_date' => $today,
        'current_time' => $currentTime,
        'timezone' => 'Asia/Jakarta'
    ];
    
    // Check if we have any queue data and if it's from a different date
    if ($lastQueue) {
        $lastQueueDate = $lastQueue['queue_date'];
        $isNewDay = ($lastQueueDate !== $today);
        
        $response['last_queue_date'] = $lastQueueDate;
        $response['is_new_day'] = $isNewDay;
        
        if ($isNewDay) {
            // Different date - reset to start from F001 (but keep old data)
            $response['queue_exists'] = false;
            $response['current_number'] = 'F000';
            $response['last_queue_time'] = $lastQueue['tanggal'];
            $response['total_queues_today'] = 0;
            $response['message'] = 'New day detected. Queue will reset to F001 (old data preserved)';
            $response['next_number'] = 'F001';
            $response['reset_status'] = 'daily_reset_triggered';
        } else {
            // Same date - continue from where we left off
            $response['queue_exists'] = true;
            $response['current_number'] = $lastQueue['no_antrian'];
            $response['last_queue_time'] = $lastQueue['tanggal'];
            $response['total_queues_today'] = $countResult['count'];
            $response['message'] = 'Queue system is active for today';
            $response['next_number'] = 'F' . str_pad((intval(substr($lastQueue['no_antrian'], 1)) + 1), 3, '0', STR_PAD_LEFT);
            $response['reset_status'] = 'continuing_same_day';
        }
    } else {
        // No queue data at all - start fresh
        $response['queue_exists'] = false;
        $response['current_number'] = 'F000';
        $response['last_queue_time'] = null;
        $response['total_queues_today'] = 0;
        $response['message'] = 'No queue data found. System ready to start from F001';
        $response['next_number'] = 'F001';
        $response['is_new_day'] = true;
        $response['reset_status'] = 'no_data_found';
    }
    
    // Check if we're past midnight (new day detection)
    $response['reset_status_info'] = 'Date comparison based reset system active';
    
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'current_date' => date('Y-m-d'),
        'current_time' => date('H:i:s')
    ]);
}
?>