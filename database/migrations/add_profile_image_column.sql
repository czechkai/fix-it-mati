-- Migration: Add profile_image column to users table
-- Date: 2025-12-15
-- Description: Adds profile_image column to store user profile picture filenames

-- Add profile_image column if it doesn't exist
DO $$ 
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name = 'users' 
        AND column_name = 'profile_image'
    ) THEN
        ALTER TABLE users ADD COLUMN profile_image VARCHAR(255);
        RAISE NOTICE 'Column profile_image added to users table';
    ELSE
        RAISE NOTICE 'Column profile_image already exists in users table';
    END IF;
END $$;

-- Add first_name and last_name columns if they don't exist
DO $$ 
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name = 'users' 
        AND column_name = 'first_name'
    ) THEN
        ALTER TABLE users ADD COLUMN first_name VARCHAR(100);
        RAISE NOTICE 'Column first_name added to users table';
    ELSE
        RAISE NOTICE 'Column first_name already exists in users table';
    END IF;
END $$;

DO $$ 
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name = 'users' 
        AND column_name = 'last_name'
    ) THEN
        ALTER TABLE users ADD COLUMN last_name VARCHAR(100);
        RAISE NOTICE 'Column last_name added to users table';
    ELSE
        RAISE NOTICE 'Column last_name already exists in users table';
    END IF;
END $$;

-- Add role column if it doesn't exist
DO $$ 
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name = 'users' 
        AND column_name = 'role'
    ) THEN
        ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'customer';
        RAISE NOTICE 'Column role added to users table';
    ELSE
        RAISE NOTICE 'Column role already exists in users table';
    END IF;
END $$;

-- Add password_hash column if it doesn't exist
DO $$ 
BEGIN
    IF NOT EXISTS (
        SELECT 1 
        FROM information_schema.columns 
        WHERE table_name = 'users' 
        AND column_name = 'password_hash'
    ) THEN
        ALTER TABLE users ADD COLUMN password_hash TEXT;
        RAISE NOTICE 'Column password_hash added to users table';
    ELSE
        RAISE NOTICE 'Column password_hash already exists in users table';
    END IF;
END $$;

-- Create index on profile_image for faster lookups (optional but recommended)
CREATE INDEX IF NOT EXISTS idx_users_profile_image ON users(profile_image);

COMMENT ON COLUMN users.profile_image IS 'Filename of the user profile picture stored in uploads/profiles/';
