-- Modify technicians table to support both individuals and teams
-- This replaces the need for a separate technician_teams table

-- First, drop the technician_teams table if it exists
DROP TABLE IF EXISTS technician_teams;

-- Alter the technicians table to support team management
ALTER TABLE technicians 
ADD COLUMN IF NOT EXISTS type VARCHAR(20) DEFAULT 'individual' CHECK (type IN ('individual', 'team')),
ADD COLUMN IF NOT EXISTS name VARCHAR(255),
ADD COLUMN IF NOT EXISTS department VARCHAR(50),
ADD COLUMN IF NOT EXISTS lead VARCHAR(255),
ADD COLUMN IF NOT EXISTS members INTEGER DEFAULT 1,
ADD COLUMN IF NOT EXISTS contact_number VARCHAR(20),
ADD COLUMN IF NOT EXISTS location VARCHAR(255) DEFAULT 'HQ (Standby)',
ADD COLUMN IF NOT EXISTS current_task TEXT,
ADD COLUMN IF NOT EXISTS current_ticket VARCHAR(50),
ADD COLUMN IF NOT EXISTS rating DECIMAL(2,1) DEFAULT 4.5 CHECK (rating >= 0 AND rating <= 5),
ADD COLUMN IF NOT EXISTS tickets_resolved INTEGER DEFAULT 0;

-- Update existing columns
ALTER TABLE technicians ALTER COLUMN user_id DROP NOT NULL;
ALTER TABLE technicians ALTER COLUMN specialization DROP NOT NULL;

-- Add indexes
CREATE INDEX IF NOT EXISTS idx_technicians_type ON technicians(type);
CREATE INDEX IF NOT EXISTS idx_technicians_department ON technicians(department);
CREATE INDEX IF NOT EXISTS idx_technicians_status ON technicians(status);

-- Insert sample teams
INSERT INTO technicians (type, name, department, lead, members, contact_number, status, location, current_task, current_ticket, rating, tickets_resolved) VALUES
('team', 'Team Alpha', 'Water', 'Engr. R. Santos', 4, '0917-123-4567', 'busy', 'Brgy. Central', 'Main Pipe Leak - Brgy. Central', 'SR-8821', 4.8, 145),
('team', 'Team Bravo', 'Electric', 'Engr. J. Reyes', 3, '0917-234-5678', 'available', 'HQ (Standby)', NULL, NULL, 4.5, 98),
('team', 'Team Charlie', 'Water', 'Foreman L. Lapid', 5, '0917-345-6789', 'on_route', 'National Highway', 'Travel to Brgy. Matiao', 'SR-8822', 4.9, 210),
('team', 'Team Delta', 'Water', 'Tech M. Clara', 2, '0917-456-7890', 'available', 'HQ (Standby)', NULL, NULL, 4.2, 65),
('team', 'Team Echo', 'Electric', 'Engr. A. Mabini', 3, '0917-567-8901', 'busy', 'Purok 2, Dahican', 'Transformer Repair - Purok 2', 'SR-8823', 4.7, 112)
ON CONFLICT DO NOTHING;

-- Add comments
COMMENT ON COLUMN technicians.type IS 'Type: individual (linked to user) or team (group)';
COMMENT ON COLUMN technicians.department IS 'For teams: Water or Electric';
COMMENT ON COLUMN technicians.status IS 'Status: available, busy, on_route, off_duty, active, inactive';
