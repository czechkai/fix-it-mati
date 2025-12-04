-- Migration: Add password_hash and role columns to users table
-- Run this in Supabase SQL Editor

-- Add password_hash column
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255);

-- Add role column with default value
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS role VARCHAR(50) DEFAULT 'customer';

-- Create index on role for faster queries
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);

-- Add check constraint for valid roles
ALTER TABLE users 
ADD CONSTRAINT check_user_role 
CHECK (role IN ('customer', 'admin', 'technician'));

-- Update existing users to have customer role if NULL
UPDATE users SET role = 'customer' WHERE role IS NULL;
