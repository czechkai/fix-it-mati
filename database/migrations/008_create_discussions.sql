-- Migration: Create discussions table
-- Date: 2025-12-13
-- Community discussions feature

-- Discussions Table
CREATE TABLE IF NOT EXISTS discussions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    category VARCHAR(50) NOT NULL CHECK (category IN ('Water Supply', 'Electricity', 'Billing', 'General')),
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    upvotes INTEGER DEFAULT 0,
    is_answered BOOLEAN DEFAULT FALSE,
    answered_by VARCHAR(255),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Discussion Comments Table
CREATE TABLE IF NOT EXISTS discussion_comments (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    discussion_id UUID NOT NULL REFERENCES discussions(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    is_solution BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Discussion Upvotes Table (to track who upvoted)
CREATE TABLE IF NOT EXISTS discussion_upvotes (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    discussion_id UUID NOT NULL REFERENCES discussions(id) ON DELETE CASCADE,
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    UNIQUE(discussion_id, user_id)
);

-- Indexes for performance
CREATE INDEX IF NOT EXISTS idx_discussions_user_id ON discussions(user_id);
CREATE INDEX IF NOT EXISTS idx_discussions_category ON discussions(category);
CREATE INDEX IF NOT EXISTS idx_discussions_created_at ON discussions(created_at);
CREATE INDEX IF NOT EXISTS idx_discussions_upvotes ON discussions(upvotes);
CREATE INDEX IF NOT EXISTS idx_discussion_comments_discussion_id ON discussion_comments(discussion_id);
CREATE INDEX IF NOT EXISTS idx_discussion_upvotes_discussion_id ON discussion_upvotes(discussion_id);

-- Function to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_discussion_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Trigger to automatically update updated_at
DROP TRIGGER IF EXISTS discussions_updated_at ON discussions;
CREATE TRIGGER discussions_updated_at
    BEFORE UPDATE ON discussions
    FOR EACH ROW
    EXECUTE FUNCTION update_discussion_timestamp();

DROP TRIGGER IF EXISTS discussion_comments_updated_at ON discussion_comments;
CREATE TRIGGER discussion_comments_updated_at
    BEFORE UPDATE ON discussion_comments
    FOR EACH ROW
    EXECUTE FUNCTION update_discussion_timestamp();

-- Comments for documentation
COMMENT ON TABLE discussions IS 'Community discussion threads';
COMMENT ON TABLE discussion_comments IS 'Comments/replies on discussion threads';
COMMENT ON TABLE discussion_upvotes IS 'Tracks which users upvoted which discussions';
COMMENT ON COLUMN discussions.is_answered IS 'Whether the discussion has an accepted solution';
COMMENT ON COLUMN discussions.answered_by IS 'Name/role of person who provided the solution';
COMMENT ON COLUMN discussion_comments.is_solution IS 'Whether this comment is marked as the solution';
