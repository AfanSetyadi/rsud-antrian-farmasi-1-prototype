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
    // Get the latest queue number for today
    $today = date('Y-m-d');
    $stmt = $pdo->prepare("
        SELECT no_antrian 
        FROM antrian 
        WHERE id_loket = ? AND DATE(tanggal) = ? 
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stmt->execute([$id_loket, $today]);
    $lastQueue = $stmt->fetch();
    
    // Generate new queue number
    if ($lastQueue) {
        // Extract number from last queue (e.g., F048 -> 48)
        $lastNumber = intval(substr($lastQueue['no_antrian'], 1));
        $newNumber = $lastNumber + 1;
    } else {
        // Start from 1 if no queue today
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