-- SecureCart database setup
-- Run with:  mysql -u root -p < setup.sql

CREATE DATABASE IF NOT EXISTS securecart;
USE securecart;

DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255)
);

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    username VARCHAR(50) NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, password, email, is_admin) VALUES
('admin', 'admin123', 'admin@securecart.com', 1),
('alice', 'alice123', 'alice@example.com', 0),
('bob', 'bobpass', 'bob@example.com', 0);

INSERT INTO products (name, description, price, image) VALUES
('Wireless Mouse', 'Ergonomic wireless mouse with USB receiver.', 19.99, 'mouse.jpg'),
('Mechanical Keyboard', 'RGB backlit mechanical keyboard.', 49.99, 'keyboard.jpg'),
('USB-C Hub', '7-in-1 USB-C hub with HDMI output.', 29.99, 'hub.jpg'),
('Laptop Stand', 'Adjustable aluminum laptop stand.', 24.99, 'stand.jpg');
