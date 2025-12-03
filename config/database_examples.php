<?php
/**
 * Example: Using Database in FixItMati Pages
 * This file demonstrates how to integrate database queries into your PHP pages
 */

// Include database configuration
require_once __DIR__ . '/../config/database.php';

// Example 1: Fetching service requests
function getActiveRequests($userId) {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("
            SELECT id, title, category, status, created_at, priority
            FROM service_requests
            WHERE user_id = :user_id AND status != 'completed'
            ORDER BY created_at DESC
        ");
        
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
        
    } catch(Exception $e) {
        error_log("Error fetching requests: " . $e->getMessage());
        return [];
    }
}

// Example 2: Creating a new request
function createServiceRequest($userId, $title, $category, $description) {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("
            INSERT INTO service_requests (user_id, title, category, description, status, created_at)
            VALUES (:user_id, :title, :category, :description, 'pending', NOW())
            RETURNING id
        ");
        
        $stmt->execute([
            'user_id' => $userId,
            'title' => $title,
            'category' => $category,
            'description' => $description
        ]);
        
        $result = $stmt->fetch();
        return $result['id'];
        
    } catch(Exception $e) {
        error_log("Error creating request: " . $e->getMessage());
        return false;
    }
}

// Example 3: Updating request status
function updateRequestStatus($requestId, $newStatus) {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE service_requests
            SET status = :status, updated_at = NOW()
            WHERE id = :id
        ");
        
        return $stmt->execute([
            'status' => $newStatus,
            'id' => $requestId
        ]);
        
    } catch(Exception $e) {
        error_log("Error updating status: " . $e->getMessage());
        return false;
    }
}

// Example 4: Getting user payment information
function getUserPayments($userId) {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("
            SELECT 
                id,
                bill_month,
                amount,
                status,
                due_date,
                paid_date
            FROM payments
            WHERE user_id = :user_id
            ORDER BY due_date DESC
            LIMIT 10
        ");
        
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
        
    } catch(Exception $e) {
        error_log("Error fetching payments: " . $e->getMessage());
        return [];
    }
}

// Example 5: Getting announcements
function getAnnouncements($category = null, $limit = 10) {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        $sql = "
            SELECT 
                id,
                title,
                content,
                category,
                type,
                created_at,
                affected_areas
            FROM announcements
            WHERE status = 'published'
        ";
        
        $params = [];
        
        if ($category && $category !== 'All') {
            $sql .= " AND category = :category";
            $params['category'] = $category;
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT :limit";
        $params['limit'] = $limit;
        
        $stmt = $conn->prepare($sql);
        
        // Bind limit as integer
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        
        // Bind category if exists
        if (isset($params['category'])) {
            $stmt->bindValue(':category', $params['category'], PDO::PARAM_STR);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
        
    } catch(Exception $e) {
        error_log("Error fetching announcements: " . $e->getMessage());
        return [];
    }
}

// Example 6: Transaction example (multiple operations)
function processPayment($userId, $paymentId, $amount) {
    try {
        $db = Database::getInstance();
        $conn = $db->getConnection();
        
        // Start transaction
        $conn->beginTransaction();
        
        // Update payment status
        $stmt = $conn->prepare("
            UPDATE payments
            SET status = 'paid', paid_date = NOW()
            WHERE id = :id AND user_id = :user_id
        ");
        $stmt->execute([
            'id' => $paymentId,
            'user_id' => $userId
        ]);
        
        // Record transaction
        $stmt = $conn->prepare("
            INSERT INTO transactions (user_id, payment_id, amount, type, created_at)
            VALUES (:user_id, :payment_id, :amount, 'payment', NOW())
        ");
        $stmt->execute([
            'user_id' => $userId,
            'payment_id' => $paymentId,
            'amount' => $amount
        ]);
        
        // Commit transaction
        $conn->commit();
        return true;
        
    } catch(Exception $e) {
        // Rollback on error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Error processing payment: " . $e->getMessage());
        return false;
    }
}

// Example 7: Using Supabase REST API (alternative to direct SQL)
function fetchDataViaSupabaseAPI($table, $filter = []) {
    $config = Database::getSupabaseConfig();
    $url = $config['url'] . '/rest/v1/' . $table;
    
    // Build query string
    $queryParams = [];
    foreach ($filter as $key => $value) {
        $queryParams[] = $key . '=eq.' . urlencode($value);
    }
    
    if (!empty($queryParams)) {
        $url .= '?' . implode('&', $queryParams);
    }
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'apikey: ' . $config['anon_key'],
        'Authorization: Bearer ' . $config['anon_key']
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    
    return [];
}

// Example usage in a page:
/*
<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/database_examples.php';

// Get user ID from session (you'll implement authentication)
$userId = $_SESSION['user_id'] ?? 1;

// Fetch data
$requests = getActiveRequests($userId);
$payments = getUserPayments($userId);
$announcements = getAnnouncements('Water Supply', 5);

// Display in HTML
foreach ($requests as $request) {
    echo '<div class="request-card">';
    echo '<h3>' . htmlspecialchars($request['title']) . '</h3>';
    echo '<span>' . htmlspecialchars($request['status']) . '</span>';
    echo '</div>';
}
?>
*/

?>
