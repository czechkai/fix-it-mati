-- Migration: Create notifications system tables
-- Date: 2025-12-05
-- Sprint 2: Notification System

-- Notifications Table
CREATE TABLE IF NOT EXISTS notifications (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    type VARCHAR(50) NOT NULL CHECK (type IN ('request_status', 'assignment', 'payment', 'announcement', 'system')),
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSONB, -- Additional data specific to notification type
    channel VARCHAR(50) NOT NULL CHECK (channel IN ('in_app', 'email', 'sms')),
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'sent', 'failed')),
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP,
    sent_at TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Notification Preferences Table
CREATE TABLE IF NOT EXISTS notification_preferences (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    notification_type VARCHAR(50) NOT NULL,
    in_app_enabled BOOLEAN DEFAULT TRUE,
    email_enabled BOOLEAN DEFAULT TRUE,
    sms_enabled BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, notification_type)
);

-- Notification Templates Table
CREATE TABLE IF NOT EXISTS notification_templates (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(100) UNIQUE NOT NULL,
    type VARCHAR(50) NOT NULL,
    channel VARCHAR(50) NOT NULL,
    subject VARCHAR(255),
    template TEXT NOT NULL,
    variables JSONB, -- List of available template variables
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for performance
CREATE INDEX IF NOT EXISTS idx_notifications_user_id ON notifications(user_id);
CREATE INDEX IF NOT EXISTS idx_notifications_type ON notifications(type);
CREATE INDEX IF NOT EXISTS idx_notifications_status ON notifications(status);
CREATE INDEX IF NOT EXISTS idx_notifications_is_read ON notifications(is_read);
CREATE INDEX IF NOT EXISTS idx_notifications_created_at ON notifications(created_at);
CREATE INDEX IF NOT EXISTS idx_notification_preferences_user_id ON notification_preferences(user_id);

-- Function to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_notification_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger for notifications table
DROP TRIGGER IF EXISTS trigger_update_notification_timestamp ON notifications;
CREATE TRIGGER trigger_update_notification_timestamp
    BEFORE UPDATE ON notifications
    FOR EACH ROW
    EXECUTE FUNCTION update_notification_timestamp();

-- Trigger for notification_preferences table
DROP TRIGGER IF EXISTS trigger_update_notification_preferences_timestamp ON notification_preferences;
CREATE TRIGGER trigger_update_notification_preferences_timestamp
    BEFORE UPDATE ON notification_preferences
    FOR EACH ROW
    EXECUTE FUNCTION update_notification_timestamp();

-- Trigger for notification_templates table
DROP TRIGGER IF EXISTS trigger_update_notification_templates_timestamp ON notification_templates;
CREATE TRIGGER trigger_update_notification_templates_timestamp
    BEFORE UPDATE ON notification_templates
    FOR EACH ROW
    EXECUTE FUNCTION update_notification_timestamp();

-- Insert default notification templates
INSERT INTO notification_templates (name, type, channel, subject, template, variables) VALUES
(
    'request_submitted_customer',
    'request_status',
    'in_app',
    'Service Request Submitted',
    'Your service request "{{title}}" has been submitted successfully. Tracking number: {{tracking_number}}',
    '["title", "tracking_number", "category"]'::jsonb
),
(
    'request_submitted_admin',
    'request_status',
    'in_app',
    'New Service Request',
    'New {{category}} request from {{customer_name}}: {{title}}',
    '["title", "category", "customer_name", "tracking_number"]'::jsonb
),
(
    'request_reviewed',
    'request_status',
    'in_app',
    'Request Under Review',
    'Your service request "{{title}}" is now under review.',
    '["title", "tracking_number"]'::jsonb
),
(
    'technician_assigned',
    'assignment',
    'in_app',
    'Technician Assigned',
    'Technician {{technician_name}} has been assigned to your request "{{title}}".',
    '["title", "tracking_number", "technician_name", "technician_phone"]'::jsonb
),
(
    'request_in_progress',
    'request_status',
    'in_app',
    'Work In Progress',
    'Work has started on your request "{{title}}".',
    '["title", "tracking_number", "technician_name"]'::jsonb
),
(
    'request_completed',
    'request_status',
    'in_app',
    'Request Completed',
    'Your service request "{{title}}" has been completed.',
    '["title", "tracking_number", "completion_notes"]'::jsonb
),
(
    'payment_due',
    'payment',
    'in_app',
    'Payment Due',
    'Your {{service_type}} bill of ₱{{amount}} is due on {{due_date}}.',
    '["service_type", "amount", "due_date", "account_number"]'::jsonb
),
(
    'payment_received',
    'payment',
    'in_app',
    'Payment Received',
    'Payment of ₱{{amount}} received for {{service_type}}. Thank you!',
    '["amount", "service_type", "reference_number"]'::jsonb
),
(
    'announcement_new',
    'announcement',
    'in_app',
    'New Announcement',
    '{{title}}: {{message}}',
    '["title", "message", "category", "priority"]'::jsonb
)
ON CONFLICT (name) DO NOTHING;

-- Insert default notification preferences for existing users
INSERT INTO notification_preferences (user_id, notification_type, in_app_enabled, email_enabled, sms_enabled)
SELECT 
    id,
    'request_status',
    TRUE,
    TRUE,
    FALSE
FROM users
ON CONFLICT (user_id, notification_type) DO NOTHING;

COMMENT ON TABLE notifications IS 'Stores all notifications sent to users';
COMMENT ON TABLE notification_preferences IS 'User preferences for notification channels';
COMMENT ON TABLE notification_templates IS 'Templates for generating notification content';
