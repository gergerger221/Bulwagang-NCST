-- =============================================
-- Database Creation Script for Audition Website
-- =============================================

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `bulwagan_db` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Use the created database
USE `bulwagan_db`;

-- =============================================
-- Table: pending_audition
-- Description: Stores audition form submissions
-- =============================================

CREATE TABLE IF NOT EXISTS `pending_audition` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `category` ENUM('singer', 'dancer', 'solo-musician', 'band') NOT NULL,
    `details` TEXT NULL,
    `submission_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    INDEX `idx_email` (`email`),
    INDEX `idx_category` (`category`),
    INDEX `idx_status` (`status`),
    INDEX `idx_submission_date` (`submission_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Optional: Create additional tables for better data management
-- =============================================

-- Table for audition categories (for future extensibility)
CREATE TABLE IF NOT EXISTS `audition_categories` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `category_name` VARCHAR(50) NOT NULL,
    `category_description` TEXT NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_category` (`category_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default categories
INSERT INTO `audition_categories` (`category_name`, `category_description`) VALUES
('singer', 'Vocal performance auditions'),
('dancer', 'Dance performance auditions'),
('solo-musician', 'Individual instrumental performance auditions'),
('band', 'Group/band performance auditions');

-- =============================================
-- Sample Data (Optional - for testing purposes)
-- =============================================

-- Uncomment the following lines to insert sample data for testing
/*
INSERT INTO `pending_audition` 
(`first_name`, `last_name`, `email`, `phone`, `category`, `details`) 
VALUES
('John', 'Doe', 'john.doe@example.com', '09123456789', 'singer', 'I have been singing for 5 years and specialize in pop music.'),
('Jane', 'Smith', 'jane.smith@example.com', '09987654321', 'dancer', 'Contemporary dance background with 3 years of experience.'),
('Mike', 'Johnson', 'mike.johnson@example.com', '09111222333', 'solo-musician', 'Guitar player with rock and blues experience.');
*/

-- =============================================
-- Useful Queries for Managing Auditions
-- =============================================

-- View all pending auditions
-- SELECT * FROM pending_audition WHERE status = 'pending' ORDER BY submission_date DESC;

-- Count auditions by category
-- SELECT category, COUNT(*) as total FROM pending_audition GROUP BY category;

-- View recent submissions (last 7 days)
-- SELECT * FROM pending_audition WHERE submission_date >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY submission_date DESC;

-- Update audition status
-- UPDATE pending_audition SET status = 'reviewed' WHERE id = 1;

-- Search by name or email
-- SELECT * FROM pending_audition WHERE first_name LIKE '%search%' OR last_name LIKE '%search%' OR email LIKE '%search%';
