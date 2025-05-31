-- Migration script to add type columns to individual resource tables
-- Run this script to update your database with the new type columns

-- Add type column to books table
ALTER TABLE books
ADD COLUMN type TEXT;

-- Add type column to periodicals table
ALTER TABLE periodicals
ADD COLUMN type TEXT;

-- Add type column to media_resources table
ALTER TABLE media_resources
ADD COLUMN type TEXT;

-- Add indexes for faster user search queries
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);
CREATE INDEX IF NOT EXISTS idx_users_membership_id ON users(membership_id);
CREATE INDEX IF NOT EXISTS idx_users_name ON users(first_name, last_name);
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);

-- Add indexes for faster borrowing queries
CREATE INDEX IF NOT EXISTS idx_borrowings_user_id ON borrowings(user_id);
CREATE INDEX IF NOT EXISTS idx_borrowings_status ON borrowings(status);
CREATE INDEX IF NOT EXISTS idx_borrowings_user_status ON borrowings(user_id, status);
CREATE INDEX IF NOT EXISTS idx_borrowings_due_date ON borrowings(due_date);

-- Add indexes for resource queries
CREATE INDEX IF NOT EXISTS idx_library_resources_category ON library_resources(category);
CREATE INDEX IF NOT EXISTS idx_library_resources_status ON library_resources(status); 