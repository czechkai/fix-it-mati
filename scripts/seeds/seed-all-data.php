<?php

/**
 * Comprehensive Seed Data Script
 * Creates realistic sample data for all tables
 */

require_once __DIR__ . '/Core/Database.php';
require_once __DIR__ . '/Models/User.php';
require_once __DIR__ . '/Models/ServiceRequest.php';
require_once __DIR__ . '/Models/Payment.php';
require_once __DIR__ . '/Models/Announcement.php';
require_once __DIR__ . '/Models/Technician.php';

use FixItMati\Core\Database;
use FixItMati\Models\User;
use FixItMati\Models\ServiceRequest;
use FixItMati\Models\Payment;
use FixItMati\Models\Announcement;
use FixItMati\Models\Technician;

// Load env
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

try {
    $db = Database::getInstance()->getConnection();

    echo "\n";
    echo "═══════════════════════════════════════════════════════\n";
    echo "         SEEDING DATABASE WITH SAMPLE DATA\n";
    echo "═══════════════════════════════════════════════════════\n\n";

    // Check if data already exists
    $userCountStmt = $db->query("SELECT COUNT(*) FROM users");
    $userCount = $userCountStmt->fetchColumn();

    if ($userCount > 4) {
        echo "⚠ Database already has {$userCount} users.\n";
        echo "Do you want to continue and add more data? (yes/no): ";
        $response = trim(fgets(STDIN));
        if (strtolower($response) !== 'yes' && strtolower($response) !== 'y') {
            echo "Seed cancelled.\n";
            exit(0);
        }
    }

    $paymentModel = new Payment();
    $announcementModel = new Announcement();
    $technicianModel = new Technician();
    $requestModel = new ServiceRequest();

    // Get existing users for seeding
    $usersStmt = $db->query("SELECT id, role, full_name FROM users ORDER BY created_at LIMIT 10");
    $existingUsers = $usersStmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($existingUsers)) {
        echo "✗ No users found. Please create users first.\n";
        exit(1);
    }

    $regularUsers = array_filter($existingUsers, fn($u) => in_array($u['role'], ['user', 'customer']));
    $adminUsers = array_filter($existingUsers, fn($u) => $u['role'] === 'admin');

    if (empty($regularUsers)) {
        echo "✗ No regular users found.\n";
        exit(1);
    }

    echo "Found " . count($regularUsers) . " regular users and " . count($adminUsers) . " admin users\n\n";

    // ==================================================
    // 1. SEED TECHNICIANS
    // ==================================================
    echo "┌─ SEEDING TECHNICIANS\n";

    $technicianCount = $db->query("SELECT COUNT(*) FROM technicians")->fetchColumn();

    if ($technicianCount == 0) {
        $specializations = [
            ['Plumber', 'water'],
            ['Electrician', 'electricity'],
            ['Plumber', 'water'],
            ['Electrician', 'electricity']
        ];

        $techIds = [];
        foreach ($specializations as $i => $spec) {
            // Create technician user if needed
            $techUser = $existingUsers[$i % count($existingUsers)];

            $tech = $technicianModel->create([
                'user_id' => $techUser['id'],
                'specialization' => $spec[0],
                'status' => 'active',
                'phone' => '09' . rand(100000000, 999999999),
                'assigned_area' => ['Poblacion', 'Central', 'Dahican', 'Sainz'][rand(0, 3)]
            ]);

            if ($tech) {
                $techIds[] = $tech['id'];
                echo "│  ✓ Created {$spec[0]}: {$techUser['full_name']}\n";
            }
        }
        echo "│  Total: " . count($techIds) . " technicians\n";
    } else {
        echo "│  ⚠ {$technicianCount} technicians already exist, skipping\n";
    }
    echo "└─\n\n";

    // ==================================================
    // 2. SEED PAYMENTS
    // ==================================================
    echo "┌─ SEEDING PAYMENTS\n";

    $paymentCount = $db->query("SELECT COUNT(*) FROM payments")->fetchColumn();

    if ($paymentCount == 0) {
        $months = ['October 2024', 'November 2024', 'December 2024'];
        $paymentIds = [];

        foreach ($regularUsers as $user) {
            foreach ($months as $i => $month) {
                $waterAmount = rand(300, 600);
                $electricityAmount = rand(600, 1200);
                $totalAmount = $waterAmount + $electricityAmount;

                $dueDate = date('Y-m-d', strtotime("+{$i} month", strtotime('2024-10-25')));
                $status = $i == 0 ? 'paid' : ($i == 1 ? 'overdue' : 'unpaid');

                $payment = $paymentModel->createPayment([
                    'user_id' => $user['id'],
                    'bill_month' => $month,
                    'amount' => $totalAmount,
                    'status' => $status,
                    'due_date' => $dueDate
                ]);

                if ($payment) {
                    $paymentIds[] = $payment['id'];

                    // Add payment items
                    $paymentModel->addPaymentItems($payment['id'], [
                        [
                            'description' => "Mati Water District - " . rand(20, 35) . " m³",
                            'amount' => $waterAmount,
                            'category' => 'water'
                        ],
                        [
                            'description' => "Davao Light - " . rand(100, 200) . " kWh",
                            'amount' => $electricityAmount,
                            'category' => 'electricity'
                        ]
                    ]);

                    // Create transaction for paid bills
                    if ($status === 'paid') {
                        $db->prepare("INSERT INTO transactions (user_id, payment_id, amount, type, status, reference_number, created_at) 
                                     VALUES (?, ?, ?, 'payment', 'completed', ?, NOW() - INTERVAL '15 days')")
                            ->execute([
                                $user['id'],
                                $payment['id'],
                                $totalAmount,
                                'TRX-' . strtoupper(substr(uniqid(), -8))
                            ]);
                    }

                    echo "│  ✓ {$user['full_name']}: {$month} - ₱{$totalAmount} ({$status})\n";
                }
            }
        }
        echo "│  Total: " . count($paymentIds) . " payments\n";
    } else {
        echo "│  ⚠ {$paymentCount} payments already exist, skipping\n";
    }
    echo "└─\n\n";

    // ==================================================
    // 3. SEED ANNOUNCEMENTS
    // ==================================================
    echo "┌─ SEEDING ANNOUNCEMENTS\n";

    $announcementCount = $db->query("SELECT COUNT(*) FROM announcements")->fetchColumn();

    if ($announcementCount == 0) {
        $adminUser = !empty($adminUsers) ? $adminUsers[0]['id'] : $regularUsers[0]['id'];

        $announcements = [
            [
                'title' => 'Scheduled Water Interruption - December 10-11',
                'content' => 'There will be a scheduled water interruption on December 10-11, 2024, from 8:00 AM to 5:00 PM. This is for the installation of new water pumps. Please store enough water for your needs.',
                'category' => 'water',
                'type' => 'maintenance',
                'status' => 'published',
                'affected_areas' => ['Poblacion', 'Central', 'Dahican'],
                'start_date' => '2024-12-10 08:00:00',
                'end_date' => '2024-12-11 17:00:00'
            ],
            [
                'title' => 'New Online Payment System Available',
                'content' => 'We are pleased to announce that our new online payment system is now available! You can now pay your water and electricity bills through GCash, PayPal, or Credit/Debit Card. Visit our website to get started.',
                'category' => 'general',
                'type' => 'news',
                'status' => 'published',
                'affected_areas' => [],
                'start_date' => '2024-12-01 00:00:00',
                'end_date' => null
            ],
            [
                'title' => 'Holiday Schedule - December 25-26',
                'content' => 'Our offices will be closed on December 25-26 for the Christmas holiday. Emergency services will still be available. Happy holidays!',
                'category' => 'general',
                'type' => 'news',
                'status' => 'published',
                'affected_areas' => [],
                'start_date' => '2024-12-20 00:00:00',
                'end_date' => '2024-12-27 00:00:00'
            ],
            [
                'title' => 'Power Outage Advisory - Sainz Area',
                'content' => 'URGENT: There is an ongoing power outage in the Sainz area due to a damaged transformer. Our technicians are working to restore power as soon as possible. Estimated restoration time: 3-4 hours.',
                'category' => 'electricity',
                'type' => 'urgent',
                'status' => 'published',
                'affected_areas' => ['Sainz'],
                'start_date' => '2024-12-07 14:00:00',
                'end_date' => '2024-12-07 18:00:00'
            ],
            [
                'title' => 'Water Quality Test Results - All Clear',
                'content' => 'The latest water quality tests have been completed and all results are within safe limits. Our water supply continues to meet national standards for drinking water.',
                'category' => 'water',
                'type' => 'news',
                'status' => 'published',
                'affected_areas' => [],
                'start_date' => '2024-12-05 00:00:00',
                'end_date' => null
            ]
        ];

        foreach ($announcements as $ann) {
            $ann['created_by'] = $adminUser;
            $result = $announcementModel->create($ann);
            if ($result) {
                echo "│  ✓ {$ann['title']}\n";
            }
        }
        echo "│  Total: " . count($announcements) . " announcements\n";
    } else {
        echo "│  ⚠ {$announcementCount} announcements already exist, skipping\n";
    }
    echo "└─\n\n";

    // ==================================================
    // 4. SEED SERVICE REQUESTS
    // ==================================================
    echo "┌─ SEEDING SERVICE REQUESTS\n";

    $requestCount = $db->query("SELECT COUNT(*) FROM service_requests")->fetchColumn();

    if ($requestCount < 5) {
        $requests = [
            [
                'title' => 'Low water pressure in kitchen',
                'description' => 'The water pressure in my kitchen has been very low for the past week. Other faucets seem fine.',
                'category' => 'water',
                'priority' => 'normal',
                'location' => 'Poblacion, Mati City',
                'status' => 'pending'
            ],
            [
                'title' => 'Flickering lights in living room',
                'description' => 'The lights in my living room have been flickering on and off. This started after the storm last night.',
                'category' => 'electricity',
                'priority' => 'high',
                'location' => 'Central, Mati City',
                'status' => 'in_progress'
            ],
            [
                'title' => 'Water leak under sink',
                'description' => 'There is a small water leak under my kitchen sink. It has been getting worse over the past few days.',
                'category' => 'water',
                'priority' => 'high',
                'location' => 'Dahican, Mati City',
                'status' => 'pending'
            ],
            [
                'title' => 'Power outlet not working',
                'description' => 'One of the power outlets in my bedroom has stopped working completely. I have tried resetting the breaker with no success.',
                'category' => 'electricity',
                'priority' => 'normal',
                'location' => 'Sainz, Mati City',
                'status' => 'completed'
            ],
            [
                'title' => 'No water supply since morning',
                'description' => 'We have had no water supply since 6 AM this morning. Is there a problem in our area?',
                'category' => 'water',
                'priority' => 'urgent',
                'location' => 'Poblacion, Mati City',
                'status' => 'in_progress'
            ]
        ];

        foreach ($requests as $req) {
            $user = $regularUsers[array_rand($regularUsers)];
            $req['user_id'] = $user['id'];

            $result = $requestModel->create($req);
            if ($result) {
                echo "│  ✓ {$req['title']} ({$req['status']})\n";

                // Add update for completed requests
                if ($req['status'] === 'completed') {
                    $db->prepare("UPDATE service_requests SET completed_at = NOW() - INTERVAL '2 days' WHERE id = ?")
                        ->execute([$result['id']]);
                }
            }
        }
        echo "│  Total: New requests added\n";
    } else {
        echo "│  ⚠ {$requestCount} requests already exist, skipping\n";
    }
    echo "└─\n\n";

    // ==================================================
    // SUMMARY
    // ==================================================
    echo "═══════════════════════════════════════════════════════\n";
    echo "                 SEED COMPLETE!\n";
    echo "═══════════════════════════════════════════════════════\n\n";

    // Final counts
    $counts = [];
    foreach (['users', 'service_requests', 'payments', 'payment_items', 'transactions', 'announcements', 'technicians'] as $table) {
        $stmt = $db->query("SELECT COUNT(*) FROM {$table}");
        $counts[$table] = $stmt->fetchColumn();
    }

    echo "Database now contains:\n";
    foreach ($counts as $table => $count) {
        echo "  • " . str_pad($table, 25) . ": {$count} rows\n";
    }

    echo "\n✓ You can now test the application with realistic data!\n\n";
} catch (Exception $e) {
    echo "\n✗ ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
