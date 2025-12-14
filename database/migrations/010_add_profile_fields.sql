-- Migration: Add profile fields to users table
-- Adds: first_name, last_name, profile_image, phone, address

ALTER TABLE users 
ADD COLUMN IF NOT EXISTS first_name VARCHAR(255);

ALTER TABLE users 
ADD COLUMN IF NOT EXISTS last_name VARCHAR(255);

ALTER TABLE users 
ADD COLUMN IF NOT EXISTS profile_image VARCHAR(255);

ALTER TABLE users 
ADD COLUMN IF NOT EXISTS phone VARCHAR(20);

ALTER TABLE users 
ADD COLUMN IF NOT EXISTS address TEXT;

-- Update updated_at column if not exists
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP;

-- Create index for faster profile lookups
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_created_at ON users(created_at);
