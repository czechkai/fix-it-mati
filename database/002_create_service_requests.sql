-- Migration: Create service_requests and related tables
-- Date: 2025-12-05

-- Service Requests Table
CREATE TABLE IF NOT EXISTS service_requests (
    id SERIAL PRIMARY KEY,
    tracking_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    category VARCHAR(50) NOT NULL CHECK (category IN ('water', 'electricity')),
    issue_type VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    location TEXT NOT NULL,
    contact_phone VARCHAR(20),
    preferred_contact VARCHAR(20) DEFAULT 'sms' CHECK (preferred_contact IN ('sms', 'call', 'email')),
    priority VARCHAR(20) DEFAULT 'normal' CHECK (priority IN ('low', 'normal', 'high', 'urgent')),
    status VARCHAR(50) DEFAULT 'pending' CHECK (status IN ('pending', 'reviewed', 'assigned', 'in_progress', 'completed', 'cancelled')),
    assigned_to INTEGER REFERENCES users(id) ON DELETE SET NULL,
    photos TEXT[], -- Array of photo URLs/paths
    admin_notes TEXT,
    estimated_completion TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Request Updates/Timeline Table
CREATE TABLE IF NOT EXISTS request_updates (
    id SERIAL PRIMARY KEY,
    request_id INTEGER NOT NULL REFERENCES service_requests(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    old_status VARCHAR(50),
    new_status VARCHAR(50) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Indexes for performance
CREATE INDEX IF NOT EXISTS idx_service_requests_user_id ON service_requests(user_id);
CREATE INDEX IF NOT EXISTS idx_service_requests_status ON service_requests(status);
CREATE INDEX IF NOT EXISTS idx_service_requests_category ON service_requests(category);
CREATE INDEX IF NOT EXISTS idx_service_requests_assigned_to ON service_requests(assigned_to);
CREATE INDEX IF NOT EXISTS idx_service_requests_tracking_number ON service_requests(tracking_number);
CREATE INDEX IF NOT EXISTS idx_request_updates_request_id ON request_updates(request_id);

-- Function to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_service_request_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger to automatically update updated_at
CREATE TRIGGER service_requests_updated_at
    BEFORE UPDATE ON service_requests
    FOR EACH ROW
    EXECUTE FUNCTION update_service_request_timestamp();

-- Function to generate tracking number
CREATE OR REPLACE FUNCTION generate_tracking_number()
RETURNS TEXT AS $$
DECLARE
    new_number TEXT;
    counter INTEGER;
BEGIN
    -- Format: REQ-YYYY-NNNNNN (e.g., REQ-2025-000001)
    SELECT COUNT(*) + 1 INTO counter FROM service_requests 
    WHERE EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM CURRENT_TIMESTAMP);
    
    new_number := 'REQ-' || EXTRACT(YEAR FROM CURRENT_TIMESTAMP) || '-' || LPAD(counter::TEXT, 6, '0');
    
    RETURN new_number;
END;
$$ LANGUAGE plpgsql;

-- Comments for documentation
COMMENT ON TABLE service_requests IS 'Stores all service requests from customers';
COMMENT ON TABLE request_updates IS 'Timeline/history of status changes for each request';
COMMENT ON COLUMN service_requests.tracking_number IS 'Unique tracking ID shown to customers (e.g., REQ-2025-000001)';
COMMENT ON COLUMN service_requests.status IS 'Current state: pending -> reviewed -> assigned -> in_progress -> completed/cancelled';
COMMENT ON COLUMN service_requests.priority IS 'Priority level set by admin';
COMMENT ON COLUMN service_requests.assigned_to IS 'Technician user_id assigned to handle this request';
