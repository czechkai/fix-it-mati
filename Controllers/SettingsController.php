<?php

namespace FixItMati\Controllers;

use FixItMati\Core\Request;
use FixItMati\Core\Response;
use FixItMati\Models\User;

/**
 * SettingsController
 * 
 * Handles HTTP requests for user settings management.
 */
class SettingsController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Get user settings
     * GET /api/settings
     */
    public function index(Request $request): Response
    {
        try {
            $userId = $request->user()['id'] ?? null;
            
            if (!$userId) {
                return Response::error('User not authenticated', 401);
            }
            
            $settings = $this->userModel->getSettings($userId);
            $paymentMethods = $this->userModel->getPaymentMethods($userId);
            $householdMembers = $this->userModel->getHouseholdMembers($userId);
            
            return Response::success([
                'settings' => $settings,
                'payment_methods' => $paymentMethods,
                'household_members' => $householdMembers
            ]);
        } catch (\Exception $e) {
            error_log("Error fetching settings: " . $e->getMessage());
            return Response::error('Failed to fetch settings', 500);
        }
    }

    /**
     * Update user settings
     * PUT /api/settings
     */
    public function update(Request $request): Response
    {
        try {
            $userId = $request->user()['id'] ?? null;
            
            if (!$userId) {
                return Response::error('User not authenticated', 401);
            }
            
            $data = $request->all();
            
            // Filter allowed fields
            $allowedFields = [
                'bill_reminders', 'bill_reminder_days', 'high_consumption_water', 
                'high_consumption_power', 'water_interrupt_alerts', 'power_interrupt_alerts',
                'auto_pay', 'paperless', 'calendar_sync', 'language', 
                'font_size', 'dark_mode', 'two_factor'
            ];
            
            $settings = [];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $settings[$field] = $data[$field];
                }
            }
            
            if (empty($settings)) {
                return Response::error('No valid settings provided', 400);
            }
            
            $success = $this->userModel->updateSettings($userId, $settings);
            
            if ($success) {
                // Return updated settings
                $updatedSettings = $this->userModel->getSettings($userId);
                return Response::success([
                    'settings' => $updatedSettings,
                    'message' => 'Settings updated successfully'
                ]);
            } else {
                return Response::error('Failed to update settings', 500);
            }
        } catch (\Exception $e) {
            error_log("Error updating settings: " . $e->getMessage());
            return Response::error('Failed to update settings', 500);
        }
    }

    /**
     * Reset settings to default
     * POST /api/settings/reset
     */
    public function reset(Request $request): Response
    {
        try {
            $userId = $request->user()['id'] ?? null;
            
            if (!$userId) {
                return Response::error('User not authenticated', 401);
            }
            
            // Delete existing settings (will fallback to defaults)
            $conn = $this->userModel->getConnection();
            $sql = "DELETE FROM user_settings WHERE user_id = :user_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            
            $defaultSettings = $this->userModel->getSettings($userId);
            
            return Response::success([
                'settings' => $defaultSettings,
                'message' => 'Settings reset to default'
            ]);
        } catch (\Exception $e) {
            error_log("Error resetting settings: " . $e->getMessage());
            return Response::error('Failed to reset settings', 500);
        }
    }
}
