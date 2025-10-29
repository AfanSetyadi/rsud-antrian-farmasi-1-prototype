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
    
    // Check if there are any queues for today
    $stmt = $pdo->prepare("
        SELECT no_antrian, tanggal, COUNT(*) as count
        FROM antrian 
        WHERE id_loket = ? AND DATE(tanggal) = ?
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stmt->execute([$id_loket, $today]);
    $result = $stmt->fetch();
    
    $response = [
        'success' => true,
        'current_date' => $today,
        'current_time' => $currentTime,
        'timezone' => 'Asia/Jakarta'
    ];
    
    if ($result && $result['count'] > 0) {
        // There are queues for today
        $response['queue_exists'] = true;
        $response['current_number'] = $result['no_antrian'];
        $response['last_queue_time'] = $result['tanggal'];
        $response['total_queues_today'] = $result['count'];
        $response['message'] = 'Queue system is active for today';
        $response['next_number'] = 'F' . str_pad((intval(substr($result['no_antrian'], 1)) + 1), 3, '0', STR_PAD_LEFT);
    } else {
        // No queues for today - system will start fresh
        $response['queue_exists'] = false;
        $response['current_number'] = 'F000';
        $response['last_queue_time'] = null;
        $response['total_queues_today'] = 0;
        $response['message'] = 'No queues for today. System ready to start from F001';
        $response['next_number'] = 'F001';
    }
    
    // Check if we're past midnight (new day detection)
    $response['is_new_day'] = true; // Always true since we're checking for today's date
    $response['reset_status'] = 'automatic_daily_reset_active';
    
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