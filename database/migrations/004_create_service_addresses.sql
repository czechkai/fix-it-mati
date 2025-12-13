-- Migration: Create service_addresses table
-- Date: 2025-12-13

-- Service Addresses Table
CREATE TABLE IF NOT EXISTS service_addresses (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    label VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL CHECK (type IN ('Residential', 'Commercial')),
    barangay VARCHAR(100) NOT NULL,
    street TEXT NOT NULL,
    details TEXT,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for performance
CREATE INDEX IF NOT EXISTS idx_service_addresses_user_id ON service_addresses(user_id);
CREATE INDEX IF NOT EXISTS idx_service_addresses_is_default ON service_addresses(is_default);
CREATE INDEX IF NOT EXISTS idx_service_addresses_barangay ON service_addresses(barangay);

-- Function to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_service_address_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger to automatically update updated_at
CREATE TRIGGER service_addresses_updated_at
    BEFORE UPDATE ON service_addresses
    FOR EACH ROW
    EXECUTE FUNCTION update_service_address_timestamp();

-- Function to ensure only one default address per user
CREATE OR REPLACE FUNCTION ensure_single_default_address()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.is_default = TRUE THEN
        -- Set all other addresses for this user to non-default
        UPDATE service_addresses 
        SET is_default = FALSE 
        WHERE user_id = NEW.user_id AND id != NEW.id;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger to enforce single default address
CREATE TRIGGER enforce_single_default_address
    BEFORE INSERT OR UPDATE ON service_addresses
    FOR EACH ROW
    WHEN (NEW.is_default = TRUE)
    EXECUTE FUNCTION ensure_single_default_address();

-- Comments for documentation
COMMENT ON TABLE service_addresses IS 'Stores user service addresses for water and electricity services';
COMMENT ON COLUMN service_addresses.label IS 'User-friendly label (e.g., Home, Office)';
COMMENT ON COLUMN service_addresses.type IS 'Address type: Residential or Commercial';
COMMENT ON COLUMN service_addresses.is_default IS 'Only one address can be default per user';
COMMENT ON COLUMN service_addresses.details IS 'Landmarks or additional notes for finding the location';
