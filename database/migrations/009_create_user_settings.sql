-- Migration: Create user_settings table
-- Stores user preferences for notifications, payments, security

CREATE TABLE IF NOT EXISTS user_settings (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    
    -- Notification Settings
    bill_reminders BOOLEAN DEFAULT TRUE,
    bill_reminder_days INTEGER DEFAULT 3,
    high_consumption_water BOOLEAN DEFAULT TRUE,
    high_consumption_power BOOLEAN DEFAULT FALSE,
    water_interrupt_alerts BOOLEAN DEFAULT TRUE,
    power_interrupt_alerts BOOLEAN DEFAULT TRUE,
    
    -- Payment Settings
    auto_pay BOOLEAN DEFAULT FALSE,
    
    -- Preferences
    paperless BOOLEAN DEFAULT TRUE,
    calendar_sync BOOLEAN DEFAULT FALSE,
    language VARCHAR(50) DEFAULT 'English',
    font_size VARCHAR(20) DEFAULT 'Normal',
    dark_mode BOOLEAN DEFAULT FALSE,
    
    -- Security
    two_factor BOOLEAN DEFAULT FALSE,
    support_pin VARCHAR(4) DEFAULT NULL,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Constraints
    UNIQUE(user_id)
);

-- Create payment_methods table
CREATE TABLE IF NOT EXISTS payment_methods (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    type VARCHAR(50) NOT NULL, -- 'GCash', 'Visa', 'Maya', 'Bank', etc.
    display_name VARCHAR(100) NOT NULL,
    details VARCHAR(255), -- Masked info like '•••• 4567'
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create household_members table
CREATE TABLE IF NOT EXISTS household_members (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    relationship VARCHAR(100), -- 'Spouse', 'Tenant', 'Child', etc.
    role VARCHAR(50) DEFAULT 'View Only', -- 'Admin', 'View Only'
    invited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes
CREATE INDEX IF NOT EXISTS idx_user_settings_user_id ON user_settings(user_id);
CREATE INDEX IF NOT EXISTS idx_payment_methods_user_id ON payment_methods(user_id);
CREATE INDEX IF NOT EXISTS idx_household_members_user_id ON household_members(user_id);

-- Trigger to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_user_settings_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER user_settings_update_timestamp
    BEFORE UPDATE ON user_settings
    FOR EACH ROW
    EXECUTE FUNCTION update_user_settings_timestamp();

CREATE TRIGGER payment_methods_update_timestamp
    BEFORE UPDATE ON payment_methods
    FOR EACH ROW
    EXECUTE FUNCTION update_user_settings_timestamp();
