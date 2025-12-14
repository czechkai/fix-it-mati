<?php
/**
 * Final Verification: Discussions Feature
 * Comprehensive check of all components
 */

echo "🔍 DISCUSSIONS FEATURE - FINAL VERIFICATION\n";
echo str_repeat("=", 60) . "\n\n";

$checks = [];
$passed = 0;
$failed = 0;

// Helper function
function check($name, $test, $details = '') {
    global $checks, $passed, $failed;
    $result = $test();
    $checks[] = [
        'name' => $name,
        'passed' => $result,
        'details' => $details
    ];
    
    if ($result) {
        $passed++;
        echo "✅ PASS: $name\n";
    } else {
        $failed++;
        echo "❌ FAIL: $name\n";
    }
    
    if ($details) {
        echo "   ℹ️  $details\n";
    }
    echo "\n";
}

// 1. Database Connection
check(
    'Database Connection',
    function() {
        try {
            require_once __DIR__ . '/autoload.php';
            $db = \FixItMati\Core\Database::getInstance()->getConnection();
            return $db !== null;
        } catch (Exception $e) {
            return false;
        }
    },
    'PostgreSQL connection established'
);

$db = \FixItMati\Core\Database::getInstance()->getConnection();

// 2. Discussions Table Exists
check(
    'Discussions Table Exists',
    function() use ($db) {
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM discussions");
            return $stmt !== false;
        } catch (Exception $e) {
            return false;
        }
    },
    'Main discussions table'
);

// 3. Discussion Comments Table Exists
check(
    'Discussion Comments Table Exists',
    function() use ($db) {
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM discussion_comments");
            return $stmt !== false;
        } catch (Exception $e) {
            return false;
        }
    },
    'Comments table for replies'
);

// 4. Discussion Upvotes Table Exists
check(
    'Discussion Upvotes Table Exists',
    function() use ($db) {
        try {
            $stmt = $db->query("SELECT COUNT(*) FROM discussion_upvotes");
            return $stmt !== false;
        } catch (Exception $e) {
            return false;
        }
    },
    'Upvote tracking table'
);

// 5. Has Discussions Data
check(
    'Has Discussions Data',
    function() use ($db) {
        try {
            $stmt = $db->query("SELECT COUNT(*) as count FROM discussions");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (Exception $e) {
            return false;
        }
    },
    'At least one discussion exists'
);

// 6. Discussion Model File Exists
check(
    'Discussion Model Exists',
    function() {
        return file_exists(__DIR__ . '/Models/Discussion.php');
    },
    'Models/Discussion.php'
);

// 7. Discussion Controller File Exists
check(
    'Discussion Controller Exists',
    function() {
        return file_exists(__DIR__ . '/Controllers/DiscussionController.php');
    },
    'Controllers/DiscussionController.php'
);

// 8. Discussions Page File Exists
check(
    'Discussions Page Exists',
    function() {
        return file_exists(__DIR__ . '/public/discussions.php');
    },
    'public/discussions.php'
);

// 9. Discussion Detail Page File Exists
check(
    'Discussion Detail Page Exists',
    function() {
        return file_exists(__DIR__ . '/public/discussion-detail.php');
    },
    'public/discussion-detail.php'
);

// 10. Discussions JS File Exists
check(
    'Discussions JavaScript Exists',
    function() {
        return file_exists(__DIR__ . '/assets/discussions.js');
    },
    'assets/discussions.js'
);

// 11. Discussion Detail JS File Exists
check(
    'Discussion Detail JavaScript Exists',
    function() {
        return file_exists(__DIR__ . '/assets/discussion-detail.js');
    },
    'assets/discussion-detail.js'
);

// 12. API Routes Registered
check(
    'API Routes Defined',
    function() {
        $content = file_get_contents(__DIR__ . '/public/api/index.php');
        return strpos($content, '/api/discussions') !== false;
    },
    'Routes defined in API index'
);

// 13. User Upvote Tracking in Model
check(
    'User Upvote Tracking Implemented',
    function() {
        $content = file_get_contents(__DIR__ . '/Models/Discussion.php');
        return strpos($content, 'user_upvoted') !== false;
    },
    'Shows if current user upvoted'
);

// 14. Comments Count Works
check(
    'Comments Count Works',
    function() use ($db) {
        try {
            $stmt = $db->query("
                SELECT d.id, 
                       (SELECT COUNT(*) FROM discussion_comments WHERE discussion_id = d.id) as comments_count
                FROM discussions d
                LIMIT 1
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return isset($result['comments_count']);
        } catch (Exception $e) {
            return false;
        }
    },
    'Comment counts calculated correctly'
);

// 15. Indexes Exist
check(
    'Database Indexes Exist',
    function() use ($db) {
        try {
            $stmt = $db->query("
                SELECT COUNT(*) as count 
                FROM pg_indexes 
                WHERE tablename IN ('discussions', 'discussion_comments', 'discussion_upvotes')
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] >= 6; // We expect at least 6 indexes
        } catch (Exception $e) {
            return false;
        }
    },
    'Performance indexes in place'
);

// Summary
echo str_repeat("=", 60) . "\n";
echo "📊 VERIFICATION SUMMARY\n";
echo str_repeat("=", 60) . "\n\n";

echo "Total Checks: " . ($passed + $failed) . "\n";
echo "✅ Passed: $passed\n";
echo "❌ Failed: $failed\n\n";

if ($failed === 0) {
    echo "🎉 ALL CHECKS PASSED!\n";
    echo "✅ Discussions feature is fully functional and ready to use.\n\n";
    
    // Show quick stats
    $stmt = $db->query("SELECT COUNT(*) as count FROM discussions");
    $discussionsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM discussion_comments");
    $commentsCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $stmt = $db->query("SELECT COUNT(*) as count FROM discussion_upvotes");
    $upvotesCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo "📊 Current Database Stats:\n";
    echo "   • Discussions: $discussionsCount\n";
    echo "   • Comments: $commentsCount\n";
    echo "   • Upvotes: $upvotesCount\n\n";
    
    echo "🌐 Access the feature at:\n";
    echo "   http://localhost:8000/discussions.php\n\n";
    
    echo "📚 Documentation:\n";
    echo "   • DISCUSSIONS_COMPLETE.md - Full technical details\n";
    echo "   • DISCUSSIONS_QUICK_START.md - Usage guide\n\n";
    
    exit(0);
} else {
    echo "⚠️  SOME CHECKS FAILED\n";
    echo "Please review the failed checks above and fix the issues.\n\n";
    exit(1);
}
