-- Add ticket_number column if not exists
ALTER TABLE service_requests 
ADD COLUMN IF NOT EXISTS ticket_number VARCHAR(50) UNIQUE;

-- Generate ticket numbers for existing records that don't have one
DO $$
DECLARE
    rec RECORD;
    counter INTEGER := 0;
    year VARCHAR(4) := EXTRACT(YEAR FROM CURRENT_TIMESTAMP)::VARCHAR;
BEGIN
    FOR rec IN SELECT id FROM service_requests WHERE ticket_number IS NULL ORDER BY created_at
    LOOP
        counter := counter + 1;
        UPDATE service_requests 
        SET ticket_number = 'REQ-' || year || '-' || LPAD(counter::TEXT, 6, '0')
        WHERE id = rec.id;
    END LOOP;
END $$;

-- Create index for ticket_number
CREATE INDEX IF NOT EXISTS idx_service_requests_ticket_number ON service_requests(ticket_number);

-- Create function to generate ticket number
CREATE OR REPLACE FUNCTION generate_ticket_number()
RETURNS TRIGGER AS $$
DECLARE
    new_number VARCHAR(50);
    counter INTEGER;
BEGIN
    IF NEW.ticket_number IS NULL THEN
        -- Count existing records for this year
        SELECT COUNT(*) + 1 INTO counter 
        FROM service_requests 
        WHERE EXTRACT(YEAR FROM created_at) = EXTRACT(YEAR FROM NEW.created_at);
        
        -- Generate ticket number
        NEW.ticket_number := 'REQ-' || EXTRACT(YEAR FROM NEW.created_at) || '-' || LPAD(counter::TEXT, 6, '0');
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create trigger to auto-generate ticket number
DROP TRIGGER IF EXISTS service_requests_generate_ticket_number ON service_requests;
CREATE TRIGGER service_requests_generate_ticket_number
    BEFORE INSERT ON service_requests
    FOR EACH ROW
    EXECUTE FUNCTION generate_ticket_number();

COMMENT ON COLUMN service_requests.ticket_number IS 'Unique human-readable ticket identifier (e.g., REQ-2025-000001)';
