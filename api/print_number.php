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
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['queueNumber'])) {
        throw new Exception('Queue number is required');
    }
    
    $queueNumber = $input['queueNumber'];
    
    // Verify queue number exists in database
    $stmt = $pdo->prepare("
        SELECT id, no_antrian 
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
    
    // Update queue status in database to indicate it has been printed
    if ($printSuccess) {
        $stmt = $pdo->prepare("
            UPDATE antrian 
            SET keterangan = 'Antrian diambil dari sistem - sudah dicetak' 
            WHERE id = ?
        ");
        $stmt->execute([$queue['id']]);
    }
    
    // Return response
    echo json_encode([
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