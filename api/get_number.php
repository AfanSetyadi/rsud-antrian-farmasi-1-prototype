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
    // Get the latest queue number (regardless of date)
    date_default_timezone_set('Asia/Jakarta');
    $today = date('Y-m-d');
    
    // Get the latest queue regardless of date
    $stmt = $pdo->prepare("
        SELECT no_antrian, DATE(tanggal) as queue_date
        FROM antrian 
        WHERE id_loket = ?
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stmt->execute([$id_loket]);
    $lastQueue = $stmt->fetch();
    
    // Generate new queue number
    if ($lastQueue) {
        $lastQueueDate = $lastQueue['queue_date'];
        $isNewDay = ($lastQueueDate !== $today);
        
        if ($isNewDay) {
            // Different date - start from 1 (reset)
            $newNumber = 1;
        } else {
            // Same date - continue from last number
            $lastNumber = intval(substr($lastQueue['no_antrian'], 1));
            $newNumber = $lastNumber + 1;
        }
    } else {
        // No queue data at all - start from 1
        $newNumber = 1;
    }
    
    $queueNumber = 'F' . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    
    // Insert new queue to database
    $stmt = $pdo->prepare("
        INSERT INTO antrian (id_loket, id_jenis_antrian, tanggal, no_antrian, status, keterangan) 
        VALUES (?, ?, NOW(), ?, 0, 'Antrian diambil dari sistem - belum dicetak')
    ");
    $stmt->execute([$id_loket, $id_jenis_antrian, $queueNumber]);
    
    // Get the inserted ID for tracking
    $queueId = $pdo->lastInsertId();
    
    // Return response without printing
    echo json_encode([
        'success' => true,
        'queueNumber' => $queueNumber,
        'queueId' => $queueId,
        'timestamp' => date('c'),
        'message' => 'Nomor antrian berhasil diambil. Silahkan klik tombol cetak untuk mencetak tiket.'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>