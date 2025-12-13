-- Migration: Add service history columns
-- Date: 2025-01-XX
-- Adds rating, feedback, and resolution tracking columns to service_requests table

-- Add new columns to service_requests table
ALTER TABLE service_requests 
ADD COLUMN IF NOT EXISTS rating INTEGER CHECK (rating >= 1 AND rating <= 5),
ADD COLUMN IF NOT EXISTS feedback TEXT,
ADD COLUMN IF NOT EXISTS rated_at TIMESTAMP WITH TIME ZONE,
ADD COLUMN IF NOT EXISTS resolution TEXT,
ADD COLUMN IF NOT EXISTS technician_notes TEXT,
ADD COLUMN IF NOT EXISTS resolved_at TIMESTAMP WITH TIME ZONE,
ADD COLUMN IF NOT EXISTS resolved_by VARCHAR(255),
ADD COLUMN IF NOT EXISTS before_images TEXT[],
ADD COLUMN IF NOT EXISTS after_images TEXT[],
ADD COLUMN IF NOT EXISTS original_request_id UUID REFERENCES service_requests(id) ON DELETE SET NULL;

-- Add index for rating queries
CREATE INDEX IF NOT EXISTS idx_service_requests_rating ON service_requests(rating);

-- Add index for resolved requests
CREATE INDEX IF NOT EXISTS idx_service_requests_resolved_at ON service_requests(resolved_at);

-- Add index for recurring issues
CREATE INDEX IF NOT EXISTS idx_service_requests_original_request ON service_requests(original_request_id);

-- Comments for documentation
COMMENT ON COLUMN service_requests.rating IS 'Customer rating of the service (1-5 stars)';
COMMENT ON COLUMN service_requests.feedback IS 'Customer feedback/comments after service completion';
COMMENT ON COLUMN service_requests.rated_at IS 'Timestamp when the customer submitted the rating';
COMMENT ON COLUMN service_requests.resolution IS 'Description of how the issue was resolved';
COMMENT ON COLUMN service_requests.technician_notes IS 'Internal notes from the technician about the work done';
COMMENT ON COLUMN service_requests.resolved_at IS 'Timestamp when the request was marked as completed';
COMMENT ON COLUMN service_requests.resolved_by IS 'Name of the technician who resolved the issue';
COMMENT ON COLUMN service_requests.before_images IS 'Array of image URLs showing the issue before resolution';
COMMENT ON COLUMN service_requests.after_images IS 'Array of image URLs showing the result after resolution';
COMMENT ON COLUMN service_requests.original_request_id IS 'Reference to the original request if this is a recurring issue';

-- Update existing completed requests with resolved_at timestamp
UPDATE service_requests 
SET resolved_at = updated_at 
WHERE status = 'completed' AND resolved_at IS NULL;

-- Create a trigger to automatically set resolved_at when status changes to completed
CREATE OR REPLACE FUNCTION set_resolved_at()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.status = 'completed' AND OLD.status != 'completed' THEN
        NEW.resolved_at = CURRENT_TIMESTAMP;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS service_requests_set_resolved_at ON service_requests;
CREATE TRIGGER service_requests_set_resolved_at
    BEFORE UPDATE ON service_requests
    FOR EACH ROW
    EXECUTE FUNCTION set_resolved_at();
