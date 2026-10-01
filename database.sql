-- Database Schema for Car Showroom Management System
CREATE DATABASE IF NOT EXISTS `car_showroom` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `car_showroom`;

-- 1. Settings Table
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(50) UNIQUE NOT NULL,
  `setting_value` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('showroom_name', 'Apex Motors Dealership'),
('showroom_email', 'contact@apexmotors.com'),
('showroom_phone', '+977-1-4433221'),
('showroom_address', 'Kathmandu, Nepal'),
('currency_symbol', 'NPR '),
('tax_rate', '13.00');

-- 2. Admins Table
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('Super Admin', 'Admin', 'Staff') DEFAULT 'Admin',
  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
  `last_login` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Password for superadmin: Admin@12345
INSERT INTO `admins` (`name`, `email`, `password_hash`, `role`, `status`) VALUES
('Super Administrator', 'admin@apexmotors.com', '$2y$10$w8.mP90B8.GkQ2tY2hIeue95kG2I/9I1TlyKj.1.v1X43lG9rC0.e', 'Super Admin', 'Active');

-- 3. Customers Table
CREATE TABLE IF NOT EXISTS `customers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) UNIQUE NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address` TEXT NOT NULL,
  `city` VARCHAR(50) NOT NULL,
  `country` VARCHAR(50) DEFAULT 'Nepal',
  `password_hash` VARCHAR(255) NOT NULL,
  `status` ENUM('Active', 'Inactive') DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Cars Table
CREATE TABLE IF NOT EXISTS `cars` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `brand` VARCHAR(50) NOT NULL,
  `model` VARCHAR(50) NOT NULL,
  `variant` VARCHAR(50) NULL,
  `year` INT NOT NULL,
  `price` DECIMAL(12,2) NOT NULL,
  `discount_price` DECIMAL(12,2) DEFAULT 0.00,
  `fuel_type` ENUM('Petrol', 'Diesel', 'Electric', 'Hybrid') NOT NULL,
  `transmission` ENUM('Manual', 'Automatic') NOT NULL,
  `engine` VARCHAR(50) NULL,
  `mileage` VARCHAR(30) NULL,
  `color` VARCHAR(30) NULL,
  `body_type` ENUM('Sedan', 'SUV', 'Hatchback', 'Coupe', 'Truck', 'Van') NOT NULL,
  `seating_capacity` INT NOT NULL,
  `registration_info` VARCHAR(100) NULL,
  `description` TEXT NULL,
  `features` TEXT NULL, -- Stored as JSON array or CSV
  `main_image` VARCHAR(255) DEFAULT 'default-car.jpg',
  `availability_status` ENUM('Available', 'Reserved', 'Sold') DEFAULT 'Available',
  `condition_type` ENUM('New', 'Used') DEFAULT 'New',
  `is_featured` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_brand_model` (`brand`, `model`),
  INDEX `idx_status` (`availability_status`),
  INDEX `idx_price` (`price`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Car Images Table
CREATE TABLE IF NOT EXISTS `car_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `car_id` INT NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`car_id`) REFERENCES `cars`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Favorites Table
CREATE TABLE IF NOT EXISTS `favorites` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT NOT NULL,
  `car_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_fav` (`customer_id`, `car_id`),
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`car_id`) REFERENCES `cars`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Orders Table
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_number` VARCHAR(30) UNIQUE NOT NULL,
  `customer_id` INT NOT NULL,
  `car_id` INT NOT NULL,
  `car_snapshot` JSON NOT NULL, -- Full car state at purchase time
  `price` DECIMAL(12,2) NOT NULL,
  `discount` DECIMAL(12,2) DEFAULT 0.00,
  `tax_amount` DECIMAL(12,2) DEFAULT 0.00,
  `final_amount` DECIMAL(12,2) NOT NULL,
  `payment_status` ENUM('Pending', 'Success', 'Failed', 'Cancelled', 'Refunded') DEFAULT 'Pending',
  `order_status` ENUM('Pending Payment', 'Payment Processing', 'Paid', 'Confirmed', 'Cancelled', 'Completed', 'Refunded') DEFAULT 'Pending Payment',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`),
  FOREIGN KEY (`car_id`) REFERENCES `cars`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Payments Table
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `customer_id` INT NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `payment_method` VARCHAR(30) DEFAULT 'eSewa',
  `esewa_transaction_uuid` VARCHAR(100) UNIQUE NOT NULL,
  `esewa_ref_id` VARCHAR(100) NULL,
  `payment_status` ENUM('Pending', 'Success', 'Failed', 'Cancelled') DEFAULT 'Pending',
  `gateway_response` JSON NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Invoices Table
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `invoice_number` VARCHAR(30) UNIQUE NOT NULL,
  `order_id` INT UNIQUE NOT NULL,
  `customer_id` INT NOT NULL,
  `invoice_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `pdf_path` VARCHAR(255) NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Test Drive Bookings Table
CREATE TABLE IF NOT EXISTS `test_drive_bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `customer_id` INT NOT NULL,
  `car_id` INT NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `preferred_date` DATE NOT NULL,
  `preferred_time` TIME NOT NULL,
  `message` TEXT NULL,
  `status` ENUM('Pending', 'Approved', 'Rejected', 'Rescheduled', 'Completed', 'Cancelled') DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`),
  FOREIGN KEY (`car_id`) REFERENCES `cars`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. Inquiries Table
CREATE TABLE IF NOT EXISTS `inquiries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `phone` VARCHAR(20) NULL,
  `subject` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('Unread', 'Read', 'Replied') DEFAULT 'Unread',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. Audit Logs Table
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT NULL,
  `action` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample Cars
INSERT INTO `cars` (`brand`, `model`, `variant`, `year`, `price`, `discount_price`, `fuel_type`, `transmission`, `engine`, `mileage`, `color`, `body_type`, `seating_capacity`, `registration_info`, `description`, `features`, `availability_status`, `condition_type`, `is_featured`) VALUES
('Tesla', 'Model 3', 'Long Range', 2024, 7500000.00, 7200000.00, 'Electric', 'Automatic', 'Dual Motor AWD', '500 km/charge', 'Pearl White', 'Sedan', 5, 'BA 2 PA 8821', 'All-electric sedan with industry-leading range, performance, and safety specs.', '["Autopilot", "Leather Seats", "360 Camera", "Panaromic Sunroof", "Touchscreen Nav"]', 'Available', 'New', 1),
('Hyundai', 'Tucson', 'Signature 2.0', 2023, 6200000.00, 0.00, 'Diesel', 'Automatic', '1999 cc', '15.4 kmpl', 'Titan Gray', 'SUV', 5, 'BAGMATI PRO 01-023', 'Premium SUV with high ground clearance and robust highway dynamics.', '["ABS", "Airbags", "Ventilated Seats", "Wireless Charging", "Lane Assist"]', 'Available', 'New', 1);