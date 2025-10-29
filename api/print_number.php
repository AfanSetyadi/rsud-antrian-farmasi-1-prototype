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
    // Get JSON input
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);
    
    // Log incoming request payload
    error_log("=== PRINT REQUEST DEBUG ===");
    error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
    error_log("Request Headers: " . json_encode(getallheaders(), JSON_PRETTY_PRINT));
    error_log("Raw Input: " . $rawInput);
    error_log("Parsed Input: " . json_encode($input, JSON_PRETTY_PRINT));
    error_log("Timestamp: " . date('Y-m-d H:i:s'));
    error_log("===========================");
    
    if (!isset($input['queueNumber'])) {
        throw new Exception('Queue number is required');
    }
    
    $queueNumber = $input['queueNumber'];
    
    // Verify queue number exists in database
    $stmt = $pdo->prepare("
        SELECT id, no_antrian, id_jenis_antrian 
        FROM antrian 
        WHERE no_antrian = ? AND id_loket = ? AND DATE(tanggal) = ?
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stmt->execute([$queueNumber, $id_loket, date('Y-m-d')]);
    $queue = $stmt->fetch();
    
    if (!$queue) {
        throw new Exception('Queue number not found or invalid');
    }
    
    // Prepare print data
    $printData = [
        'id_jenis_antrian' => $queue['id_jenis_antrian'],
        'no_antrian' => $queueNumber
    ];

    // Log print data preparation to console for debugging
    error_log("=== PRINT DATA PREPARATION ===");
    error_log("Queue from DB: " . json_encode($queue, JSON_PRETTY_PRINT));
    error_log("Print Data Array: " . json_encode($printData, JSON_PRETTY_PRINT));
    error_log("id_jenis_antrian: " . ($queue['id_jenis_antrian'] ?? 'NULL'));
    error_log("no_antrian: " . $queueNumber);
    error_log("Timestamp: " . date('Y-m-d H:i:s'));
    error_log("==============================");

    // Log payload to console for debugging
    error_log("=== PRINT PAYLOAD DEBUG ===");
    error_log("Print Endpoint: " . $print_endpoint);
    error_log("Payload: " . json_encode($printData, JSON_PRETTY_PRINT));
    error_log("Queue Data: " . json_encode($queue, JSON_PRETTY_PRINT));
    error_log("Timestamp: " . date('Y-m-d H:i:s'));
    error_log("===========================");

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
    $httpCode1 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Log first print attempt
    error_log("=== FIRST PRINT ATTEMPT ===");
    error_log("HTTP Code: " . $httpCode1);
    error_log("Result: " . $printResult1);
    error_log("Error: " . ($printError1 ?: 'None'));
    error_log("===========================");
    
    // Wait a moment before second print
    sleep(1);
    
    // Second print
    $printResult2 = curl_exec($ch);
    $printError2 = curl_error($ch);
    $httpCode2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Log second print attempt
    error_log("=== SECOND PRINT ATTEMPT ===");
    error_log("HTTP Code: " . $httpCode2);
    error_log("Result: " . $printResult2);
    error_log("Error: " . ($printError2 ?: 'None'));
    error_log("============================");
    
    curl_close($ch);
    
    // Check if prints were successful
    $printSuccess = empty($printError1) && empty($printError2);
    
    // Update queue status in database to indicate it has been printed
    if ($printSuccess) {
        $stmt = $pdo->prepare("
            UPDATE antrian 
            SET keterangan = 'Antrian diambil dari sistem - sudah dicetak' 
            WHERE id = ?
        ");
        $stmt->execute([$queue['id']]);
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'queueNumber' => $queueNumber,
        'timestamp' => date('c'),
        'printSuccess' => $printSuccess,
        'message' => $printSuccess ? 
            'Nomor antrian telah dicetak 2x. Silahkan ambil tiket Anda.' : 
            'Nomor antrian valid, namun ada masalah dengan printer.',
        'printErrors' => [
            'first' => $printError1,
            'second' => $printError2
        ],
        'printResults' => [
            'first' => ['httpCode' => $httpCode1, 'result' => $printResult1],
            'second' => ['httpCode' => $httpCode2, 'result' => $printResult2]
        ]
    ];
    
    // Log final response
    error_log("=== FINAL RESPONSE ===");
    error_log("Response: " . json_encode($response, JSON_PRETTY_PRINT));
    error_log("======================");
    
    // Return response
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>