-- FixItMati Database Schema
-- This file contains sample table structures for Supabase PostgreSQL
-- Run these in Supabase SQL Editor to create the database tables

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    email VARCHAR(255) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    account_number VARCHAR(50) UNIQUE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Service Requests Table
CREATE TABLE IF NOT EXISTS service_requests (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(50) NOT NULL, -- 'Water Supply', 'Electricity'
    status VARCHAR(50) DEFAULT 'pending', -- 'pending', 'in-progress', 'completed', 'cancelled'
    priority VARCHAR(20) DEFAULT 'normal', -- 'low', 'normal', 'high', 'urgent'
    location TEXT,
    assigned_technician_id UUID REFERENCES users(id),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    completed_at TIMESTAMP WITH TIME ZONE
);

-- Request Timeline/Updates Table
CREATE TABLE IF NOT EXISTS request_updates (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    request_id UUID NOT NULL REFERENCES service_requests(id) ON DELETE CASCADE,
    status VARCHAR(50) NOT NULL,
    message TEXT,
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Announcements Table
CREATE TABLE IF NOT EXISTS announcements (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    category VARCHAR(50) NOT NULL, -- 'Water Supply', 'Electricity', 'General', 'Maintenance'
    type VARCHAR(50) DEFAULT 'news', -- 'urgent', 'warning', 'news', 'maintenance'
    status VARCHAR(20) DEFAULT 'draft', -- 'draft', 'published', 'archived'
    affected_areas TEXT[], -- Array of affected areas
    start_date TIMESTAMP WITH TIME ZONE,
    end_date TIMESTAMP WITH TIME ZONE,
    created_by UUID REFERENCES users(id),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Announcement Comments/Discussions Table
CREATE TABLE IF NOT EXISTS announcement_comments (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    announcement_id UUID NOT NULL REFERENCES announcements(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES users(id),
    comment TEXT NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Payments/Bills Table
CREATE TABLE IF NOT EXISTS payments (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    bill_month VARCHAR(20) NOT NULL, -- 'October 2023', 'November 2023', etc.
    amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'unpaid', -- 'unpaid', 'paid', 'overdue', 'partial'
    due_date DATE NOT NULL,
    paid_date TIMESTAMP WITH TIME ZONE,
    payment_method VARCHAR(50), -- 'cash', 'bank', 'gcash', 'paymaya', etc.
    reference_number VARCHAR(100),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Payment Items/Breakdown Table
CREATE TABLE IF NOT EXISTS payment_items (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    payment_id UUID NOT NULL REFERENCES payments(id) ON DELETE CASCADE,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    category VARCHAR(50) -- 'water', 'electricity'
);

-- Transactions Table
CREATE TABLE IF NOT EXISTS transactions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id),
    payment_id UUID REFERENCES payments(id),
    amount DECIMAL(10, 2) NOT NULL,
    type VARCHAR(50) NOT NULL, -- 'payment', 'refund', 'adjustment'
    status VARCHAR(20) DEFAULT 'completed', -- 'pending', 'completed', 'failed'
    reference_number VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Technicians/Staff Table
CREATE TABLE IF NOT EXISTS technicians (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID UNIQUE REFERENCES users(id),
    specialization VARCHAR(100), -- 'Plumber', 'Electrician', 'Road Maintenance', etc.
    status VARCHAR(20) DEFAULT 'active', -- 'active', 'inactive', 'on-leave'
    phone VARCHAR(20),
    assigned_area TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Create Indexes for Performance
CREATE INDEX idx_requests_user_id ON service_requests(user_id);
CREATE INDEX idx_requests_status ON service_requests(status);
CREATE INDEX idx_requests_created_at ON service_requests(created_at);
CREATE INDEX idx_payments_user_id ON payments(user_id);
CREATE INDEX idx_payments_status ON payments(status);
CREATE INDEX idx_announcements_category ON announcements(category);
CREATE INDEX idx_announcements_status ON announcements(status);

-- Enable Row Level Security (RLS) - Important for Supabase
ALTER TABLE users ENABLE ROW LEVEL SECURITY;
ALTER TABLE service_requests ENABLE ROW LEVEL SECURITY;
ALTER TABLE request_updates ENABLE ROW LEVEL SECURITY;
ALTER TABLE announcements ENABLE ROW LEVEL SECURITY;
ALTER TABLE announcement_comments ENABLE ROW LEVEL SECURITY;
ALTER TABLE payments ENABLE ROW LEVEL SECURITY;
ALTER TABLE payment_items ENABLE ROW LEVEL SECURITY;
ALTER TABLE transactions ENABLE ROW LEVEL SECURITY;
ALTER TABLE technicians ENABLE ROW LEVEL SECURITY;

-- Create RLS Policies (Examples - adjust based on your needs)

-- Users can read their own data
CREATE POLICY "Users can read own data" ON users
    FOR SELECT USING (auth.uid() = id);

-- Users can read their own requests
CREATE POLICY "Users can read own requests" ON service_requests
    FOR SELECT USING (auth.uid() = user_id);

-- Users can create their own requests
CREATE POLICY "Users can create requests" ON service_requests
    FOR INSERT WITH CHECK (auth.uid() = user_id);

-- Everyone can read published announcements
CREATE POLICY "Anyone can read published announcements" ON announcements
    FOR SELECT USING (status = 'published');

-- Users can read their own payments
CREATE POLICY "Users can read own payments" ON payments
    FOR SELECT USING (auth.uid() = user_id);

-- Sample Data for Testing (Optional)
-- INSERT INTO users (email, full_name, phone, account_number) VALUES
-- ('john.doe@example.com', 'John Doe', '09123456789', 'ACC-001'),
-- ('jane.smith@example.com', 'Jane Smith', '09187654321', 'ACC-002');

-- Comments
COMMENT ON TABLE users IS 'Stores user account information';
COMMENT ON TABLE service_requests IS 'Stores service requests submitted by users';
COMMENT ON TABLE announcements IS 'Stores municipal announcements and updates';
COMMENT ON TABLE payments IS 'Stores billing and payment information';
COMMENT ON TABLE transactions IS 'Stores payment transaction history';
