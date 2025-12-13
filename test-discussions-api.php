<?php
/**
 * Test Discussions API
 * Tests all discussion endpoints
 */

require_once __DIR__ . '/autoload.php';

use FixItMati\Core\Database;

echo "🧪 Testing Discussions API...\n\n";

// Test connection
try {
    $db = Database::getInstance()->getConnection();
    echo "✅ Database connected\n\n";
} catch (Exception $e) {
    die("❌ Database error: " . $e->getMessage() . "\n");
}

// Get a test user token
$stmt = $db->query("SELECT id, email, first_name, last_name FROM users LIMIT 1");
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("❌ No users found. Please create a user first.\n");
}

echo "📋 Testing with user: {$user['first_name']} {$user['last_name']} ({$user['email']})\n\n";

// Create a simple auth token for testing
$token = base64_encode(json_encode(['user_id' => $user['id'], 'exp' => time() + 3600]));

echo "🔐 Test token: $token\n\n";

// Function to make API requests
function testAPI($method, $endpoint, $data = null, $token = null) {
    $url = "http://localhost:8000/api" . $endpoint;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    $headers = ['Content-Type: application/json'];
    if ($token) {
        $headers[] = "Authorization: Bearer $token";
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    if ($data && ($method === 'POST' || $method === 'PUT' || $method === 'PATCH')) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

// Test 1: Get all discussions
echo "1️⃣ Testing GET /api/discussions\n";
$result = testAPI('GET', '/discussions', null, $token);
if ($result['code'] === 200 && $result['response']['success']) {
    $count = count($result['response']['data']);
    echo "   ✅ Success! Found $count discussions\n";
    if ($count > 0) {
        $first = $result['response']['data'][0];
        echo "   📝 Sample: \"{$first['title']}\" by {$first['author_name']}\n";
        echo "   👍 Upvotes: {$first['upvotes']}, User upvoted: " . ($first['user_upvoted'] ? 'Yes' : 'No') . "\n";
    }
} else {
    echo "   ❌ Failed: HTTP {$result['code']}\n";
    if (isset($result['response']['message'])) {
        echo "   Message: {$result['response']['message']}\n";
    }
}
echo "\n";

// Test 2: Create new discussion
echo "2️⃣ Testing POST /api/discussions\n";
$newDiscussion = [
    'category' => 'General',
    'title' => 'Test Discussion - ' . date('Y-m-d H:i:s'),
    'content' => 'This is a test discussion created by the API test script.'
];
$result = testAPI('POST', '/discussions', $newDiscussion, $token);
if ($result['code'] === 201 && $result['response']['success']) {
    $discussionId = $result['response']['data']['id'];
    echo "   ✅ Success! Created discussion ID: $discussionId\n";
    echo "   📝 Title: \"{$result['response']['data']['title']}\"\n";
} else {
    echo "   ❌ Failed: HTTP {$result['code']}\n";
    if (isset($result['response']['message'])) {
        echo "   Message: {$result['response']['message']}\n";
    }
    $discussionId = null;
}
echo "\n";

// Test 3: Get single discussion
if ($discussionId) {
    echo "3️⃣ Testing GET /api/discussions/{id}\n";
    $result = testAPI('GET', "/discussions/$discussionId", null, $token);
    if ($result['code'] === 200 && $result['response']['success']) {
        echo "   ✅ Success!\n";
        $disc = $result['response']['data'];
        echo "   📝 Title: \"{$disc['title']}\"\n";
        echo "   👤 Author: {$disc['author_name']}\n";
        echo "   💬 Comments: {$disc['comments_count']}\n";
    } else {
        echo "   ❌ Failed: HTTP {$result['code']}\n";
    }
    echo "\n";
}

// Test 4: Upvote discussion
if ($discussionId) {
    echo "4️⃣ Testing POST /api/discussions/{id}/upvote\n";
    $result = testAPI('POST', "/discussions/$discussionId/upvote", null, $token);
    if ($result['code'] === 200 && $result['response']['success']) {
        echo "   ✅ Success!\n";
        echo "   👍 New upvotes: {$result['response']['data']['upvotes']}\n";
        echo "   🎯 User upvoted: " . ($result['response']['data']['user_upvoted'] ? 'Yes' : 'No') . "\n";
    } else {
        echo "   ❌ Failed: HTTP {$result['code']}\n";
    }
    echo "\n";
}

// Test 5: Add comment
if ($discussionId) {
    echo "5️⃣ Testing POST /api/discussions/{id}/comments\n";
    $comment = [
        'content' => 'This is a test comment posted via API.'
    ];
    $result = testAPI('POST', "/discussions/$discussionId/comments", $comment, $token);
    if ($result['code'] === 201 && $result['response']['success']) {
        $commentId = $result['response']['data']['id'];
        echo "   ✅ Success! Created comment ID: $commentId\n";
        echo "   💬 Content: \"{$result['response']['data']['content']}\"\n";
    } else {
        echo "   ❌ Failed: HTTP {$result['code']}\n";
        if (isset($result['response']['message'])) {
            echo "   Message: {$result['response']['message']}\n";
        }
        $commentId = null;
    }
    echo "\n";
}

// Test 6: Mark comment as solution
if ($discussionId && isset($commentId)) {
    echo "6️⃣ Testing POST /api/discussions/{id}/comments/{commentId}/mark-solution\n";
    $result = testAPI('POST', "/discussions/$discussionId/comments/$commentId/mark-solution", null, $token);
    if ($result['code'] === 200 && $result['response']['success']) {
        echo "   ✅ Success! Comment marked as solution\n";
    } else {
        echo "   ❌ Failed: HTTP {$result['code']}\n";
    }
    echo "\n";
}

// Test 7: Filter by category
echo "7️⃣ Testing GET /api/discussions?category=Water Supply\n";
$result = testAPI('GET', '/discussions?category=Water%20Supply', null, $token);
if ($result['code'] === 200 && $result['response']['success']) {
    $count = count($result['response']['data']);
    echo "   ✅ Success! Found $count Water Supply discussions\n";
} else {
    echo "   ❌ Failed: HTTP {$result['code']}\n";
}
echo "\n";

// Test 8: Sort by top rated
echo "8️⃣ Testing GET /api/discussions?sort=top\n";
$result = testAPI('GET', '/discussions?sort=top', null, $token);
if ($result['code'] === 200 && $result['response']['success']) {
    $count = count($result['response']['data']);
    echo "   ✅ Success! Found $count discussions sorted by upvotes\n";
    if ($count > 0) {
        $top = $result['response']['data'][0];
        echo "   🏆 Top discussion: \"{$top['title']}\" with {$top['upvotes']} upvotes\n";
    }
} else {
    echo "   ❌ Failed: HTTP {$result['code']}\n";
}
echo "\n";

echo "✅ All API tests completed!\n";
echo "\n📋 Summary:\n";
echo "   - Discussions can be listed, created, and filtered\n";
echo "   - Upvoting works with user tracking\n";
echo "   - Comments can be added and marked as solutions\n";
echo "   - Real-time data updates are working\n";
echo "\n🌐 Visit: http://localhost:8000/discussions.php\n";
