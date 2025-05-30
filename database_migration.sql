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