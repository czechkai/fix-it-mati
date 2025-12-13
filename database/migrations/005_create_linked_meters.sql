-- Migration: Create linked_meters table
-- Date: 2025-12-13

-- Linked Meters Table
CREATE TABLE IF NOT EXISTS linked_meters (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    provider VARCHAR(100) NOT NULL,
    meter_type VARCHAR(50) NOT NULL CHECK (meter_type IN ('water', 'electricity')),
    account_number VARCHAR(100) NOT NULL,
    account_holder_name VARCHAR(255) NOT NULL,
    alias VARCHAR(100),
    address TEXT,
    status VARCHAR(50) DEFAULT 'active' CHECK (status IN ('active', 'inactive', 'pending')),
    last_reading DECIMAL(10, 2),
    last_bill_amount DECIMAL(10, 2),
    last_bill_date DATE,
    metadata JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(user_id, account_number)
);

-- Indexes for performance
CREATE INDEX IF NOT EXISTS idx_linked_meters_user_id ON linked_meters(user_id);
CREATE INDEX IF NOT EXISTS idx_linked_meters_meter_type ON linked_meters(meter_type);
CREATE INDEX IF NOT EXISTS idx_linked_meters_status ON linked_meters(status);
CREATE INDEX IF NOT EXISTS idx_linked_meters_account_number ON linked_meters(account_number);

-- Function to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_linked_meter_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger to automatically update updated_at
CREATE TRIGGER linked_meters_updated_at
    BEFORE UPDATE ON linked_meters
    FOR EACH ROW
    EXECUTE FUNCTION update_linked_meter_timestamp();

-- Comments for documentation
COMMENT ON TABLE linked_meters IS 'Stores linked utility meters (water/electricity) for users';
COMMENT ON COLUMN linked_meters.provider IS 'Utility provider name (e.g., Mati Water District, Davao Light)';
COMMENT ON COLUMN linked_meters.meter_type IS 'Type of meter: water or electricity';
COMMENT ON COLUMN linked_meters.account_number IS 'Utility account or meter number';
COMMENT ON COLUMN linked_meters.alias IS 'User-friendly name for the meter (e.g., Home, Office)';
COMMENT ON COLUMN linked_meters.status IS 'Meter status: active, inactive, or pending verification';
COMMENT ON COLUMN linked_meters.metadata IS 'Additional meter information (JSON format)';
