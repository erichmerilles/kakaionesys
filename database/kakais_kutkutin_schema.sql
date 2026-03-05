-- database/kakais_kutkutin_schema.sql

CREATE DATABASE IF NOT EXISTS kakaikrk_system;
USE kakaikrk_system;

-- 1. User & Role Management (Granular RBAC)
CREATE TABLE roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_name VARCHAR(50) NOT NULL UNIQUE, -- e.g., Admin, Cashier, Stockman, Staff
    description TEXT
) ENGINE=InnoDB;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role_id INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(role_id)
) ENGINE=InnoDB;

CREATE TABLE user_permissions (
    user_id INT,
    permission_key VARCHAR(50) NOT NULL, -- e.g., 'can_view_pos', 'can_explode_bulk'
    is_granted BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (user_id, permission_key),
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 2. Catalog & 3-Tier Inventory
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE -- e.g., Nuts, Gummies, Chocolates
) ENGINE=InnoDB;

CREATE TABLE inventory (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(50) NOT NULL UNIQUE,
    product_name VARCHAR(150) NOT NULL,
    category_id INT,
    
    -- Pricing
    wholesale_price DECIMAL(10,2) NOT NULL,
    retail_price DECIMAL(10,2) NOT NULL,
    
    -- The 3-Tier System
    wholesale_boxes INT DEFAULT 0,
    retail_warehouse_pcs INT DEFAULT 0,
    store_shelf_pcs INT DEFAULT 0,
    
    -- Conversion Logic (How many pcs in 1 box?)
    pcs_per_box INT NOT NULL DEFAULT 1,
    
    -- Alerts
    critical_level_pcs INT DEFAULT 20,
    
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
) ENGINE=InnoDB;

-- 3. Sales & Daily Operations (POS)
CREATE TABLE sales_transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    receipt_no VARCHAR(50) NOT NULL UNIQUE,
    user_id INT, -- Cashier who processed it
    total_amount DECIMAL(12,2) NOT NULL,
    amount_tendered DECIMAL(12,2) NOT NULL,
    change_amount DECIMAL(12,2) NOT NULL,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;

CREATE TABLE sales_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price_at_sale DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    FOREIGN KEY (transaction_id) REFERENCES sales_transactions(transaction_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES inventory(product_id)
) ENGINE=InnoDB;

-- 4. Audit Trail / Activity Logger
CREATE TABLE activity_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action_type VARCHAR(50) NOT NULL, -- e.g., 'EXPLODE_BULK', 'POS_CHECKOUT', 'LOGIN'
    description TEXT NOT NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;

-- Insert Default Admin Role and User (Password is 'admin123')
INSERT INTO roles (role_name, description) VALUES ('Admin', 'Full system access');
INSERT INTO users (username, password_hash, full_name, role_id) 
VALUES ('admin', '$2y$10$eO.qO.k3t9R1p1y1o/V/PeX/e.v/n9Q1c/G/V/V.h/C.C.c/o/O', 'System Administrator', 1);