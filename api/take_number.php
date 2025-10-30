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
    
    // Save to database
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

    // Function to validate print response
    function validatePrintResponse($response, $httpCode) {
        if (empty($response)) {
            return ['success' => false, 'error' => 'Empty response from print server'];
        }
        
        // Check HTTP status code first
        if ($httpCode !== 200) {
            if ($httpCode === 404) {
                return ['success' => false, 'error' => 'Print endpoint not found (404). Check if direct-print service is running on 10.10.12.166'];
            } else if ($httpCode === 0) {
                return ['success' => false, 'error' => 'Cannot connect to print server (10.10.12.166). Check network connection and firewall.'];
            } else {
                return ['success' => false, 'error' => "HTTP error: $httpCode"];
            }
        }
        
        // Check if response is HTML (error page)
        if (strpos($response, '<!DOCTYPE') !== false || strpos($response, '<html>') !== false) {
            return ['success' => false, 'error' => 'Print server returned HTML error page instead of JSON'];
        }
        
        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'error' => 'Invalid JSON response from print server'];
        }
        
        $isValid = isset($decoded['status']) && 
                   $decoded['status'] === 'success' && 
                   isset($decoded['message']) && 
                   $decoded['message'] === 'Printing completed.';
        
        if (!$isValid) {
            return ['success' => false, 'error' => 'Print server response format incorrect'];
        }
        
        return ['success' => true, 'error' => null];
    }

    // Hit print endpoint twice
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $print_endpoint);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($printData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // Add connection timeout for network requests
    
    // First print
    $printResult1 = curl_exec($ch);
    $printError1 = curl_error($ch);
    $httpCode1 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $printValidation1 = validatePrintResponse($printResult1, $httpCode1);
    $printSuccess1 = empty($printError1) && $printValidation1['success'];
    
    // Wait before second print
    sleep(1);
    
    // Second print
    $printResult2 = curl_exec($ch);
    $printError2 = curl_error($ch);
    $httpCode2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $printValidation2 = validatePrintResponse($printResult2, $httpCode2);
    $printSuccess2 = empty($printError2) && $printValidation2['success'];
    
    curl_close($ch);
    
    // Return response with detailed print status
    echo json_encode([
        'success' => true,
        'queueNumber' => $queueNumber,
        'printSuccess' => $printSuccess1 && $printSuccess2,
        'printDetails' => [
            'first_print' => [
                'success' => $printSuccess1,
                'error' => $printError1 ?: $printValidation1['error'],
                'response' => $printResult1,
                'http_code' => $httpCode1
            ],
            'second_print' => [
                'success' => $printSuccess2,
                'error' => $printError2 ?: $printValidation2['error'],
                'response' => $printResult2,
                'http_code' => $httpCode2
            ]
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