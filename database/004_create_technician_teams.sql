-- Create technician_teams table for team management
-- Run this migration to create the table

-- Departments: Water = Water District, Electric = Davao Oriental Electric Cooperative (DORECO)
CREATE TABLE IF NOT EXISTS technician_teams (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    department VARCHAR(50) NOT NULL CHECK (department IN ('Water', 'Electric')),
    lead VARCHAR(255) NOT NULL,
    members INTEGER NOT NULL DEFAULT 1,
    contact_number VARCHAR(20),
    status VARCHAR(20) NOT NULL DEFAULT 'Available' CHECK (status IN ('Available', 'Busy', 'On Route', 'Off Duty')),
    location VARCHAR(255) DEFAULT 'HQ (Standby)',
    current_task TEXT,
    current_ticket VARCHAR(50),
    rating DECIMAL(2,1) DEFAULT 4.5 CHECK (rating >= 0 AND rating <= 5),
    tickets_resolved INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index for faster queries
CREATE INDEX IF NOT EXISTS idx_teams_department ON technician_teams(department);
CREATE INDEX IF NOT EXISTS idx_teams_status ON technician_teams(status);
CREATE INDEX IF NOT EXISTS idx_teams_created ON technician_teams(created_at);

-- Insert sample data (optional - for testing)
INSERT INTO technician_teams (name, department, lead, members, contact_number, status, location, current_task, current_ticket, rating, tickets_resolved) VALUES
('Team Alpha', 'Water', 'Engr. R. Santos', 4, '0917-123-4567', 'Busy', 'Brgy. Central', 'Main Pipe Leak - Brgy. Central', 'SR-8821', 4.8, 145),
('Team Bravo', 'Electric', 'Engr. J. Reyes', 3, '0917-234-5678', 'Available', 'HQ (Standby)', NULL, NULL, 4.5, 98),
('Team Charlie', 'Water', 'Foreman L. Lapid', 5, '0917-345-6789', 'On Route', 'National Highway', 'Travel to Brgy. Matiao', 'SR-8822', 4.9, 210),
('Team Delta', 'Water', 'Tech M. Clara', 2, '0917-456-7890', 'Available', 'HQ (Standby)', NULL, NULL, 4.2, 65),
('Team Echo', 'Electric', 'Engr. A. Mabini', 3, '0917-567-8901', 'Busy', 'Purok 2, Dahican', 'Transformer Repair - Purok 2', 'SR-8823', 4.7, 112)
ON CONFLICT DO NOTHING;

-- Add comment to table
COMMENT ON TABLE technician_teams IS 'Stores technician team information for field operations';
COMMENT ON COLUMN technician_teams.department IS 'Department: Water or Electric only';
COMMENT ON COLUMN technician_teams.status IS 'Team availability status';
COMMENT ON COLUMN technician_teams.rating IS 'Team performance rating (0-5)';
