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
        VALUES (?, ?, NOW(), ?, 0, 'Antrian diambil dari sistem')
    ");
    $stmt->execute([$id_loket, $id_jenis_antrian, $queueNumber]);
    
    // Prepare print data
    $printData = [
        'id_jenis_antrian' => $id_jenis_antrian,
        'no_antrian' => $queueNumber
    ];

    // Log print data preparation to console for debugging (sama seperti di print_number.php)
    error_log("=== PRINT DATA PREPARATION ===");
    error_log("Generated Queue Data: " . json_encode([
        'id' => 'NEW_QUEUE',
        'no_antrian' => $queueNumber,
        'id_jenis_antrian' => $id_jenis_antrian
    ], JSON_PRETTY_PRINT));
    error_log("Print Data Array: " . json_encode($printData, JSON_PRETTY_PRINT));
    error_log("id_jenis_antrian: " . $id_jenis_antrian);
    error_log("no_antrian: " . $queueNumber);
    error_log("Timestamp: " . date('Y-m-d H:i:s'));
    error_log("==============================");

    // Log payload to console for debugging
    error_log("=== TAKE NUMBER - PRINT PAYLOAD DEBUG ===");
    error_log("Print Endpoint: " . $print_endpoint);
    error_log("Payload: " . json_encode($printData, JSON_PRETTY_PRINT));
    error_log("Queue Number Generated: " . $queueNumber);
    error_log("Timestamp: " . date('Y-m-d H:i:s'));
    error_log("=========================================");

    // Send to print endpoint (first print)
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $print_endpoint);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($printData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $printResult1 = curl_exec($ch);
    $printError1 = curl_error($ch);
    
    // Wait a moment before second print
    sleep(1);
    
    // Second print
    $printResult2 = curl_exec($ch);
    $printError2 = curl_error($ch);
    
    curl_close($ch);
    
    // Check if prints were successful
    $printSuccess = empty($printError1) && empty($printError2);
    
    // Return response
    echo json_encode([
        'success' => true,
        'queueNumber' => $queueNumber,
        'timestamp' => date('c'),
        'printSuccess' => $printSuccess,
        'printErrors' => [
            'first' => $printError1,
            'second' => $printError2
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>