-- Add email verification columns to users table
ALTER TABLE users
ADD COLUMN verification_token VARCHAR(64) DEFAULT NULL,
ADD COLUMN token_expiry DATETIME DEFAULT NULL,
ADD COLUMN status ENUM('pending', 'active', 'inactive') DEFAULT 'pending' AFTER role;

-- Update existing users to active status
UPDATE users SET status = 'active' WHERE status IS NULL; 