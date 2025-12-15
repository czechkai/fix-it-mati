<?php
/**
 * Database Check - Verify service_requests data
 */

require_once __DIR__ . '/../../autoload.php';

use FixItMati\Core\Database;

header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();
    
    // Count total records
    $stmt = $db->query("SELECT COUNT(*) as total FROM service_requests");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get sample records
    $stmt = $db->query("SELECT * FROM service_requests LIMIT 5");
    $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get table structure
    $stmt = $db->query("SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'service_requests' ORDER BY ordinal_position");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'total_records' => $count['total'],
        'sample_records' => $samples,
        'table_columns' => $columns
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
