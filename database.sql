-- Create database
CREATE DATABASE IF NOT EXISTS conway_game;
USE conway_game;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Game sessions table
CREATE TABLE IF NOT EXISTS game_sessions (
    session_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP NULL,
    generations INT DEFAULT 0,
    score INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Patterns table
CREATE TABLE IF NOT EXISTS patterns (
    pattern_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    pattern_data TEXT NOT NULL,
    created_by INT,
    is_public BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Drop the admin_users table since we're using is_admin column instead 