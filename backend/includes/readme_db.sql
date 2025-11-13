-- Run this SQL to create the required database & tables for Play2EarnX PHP backend
CREATE DATABASE IF NOT EXISTS play2earnx;
USE play2earnx;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) UNIQUE NOT NULL,
  role ENUM('player','admin','sponsor') DEFAULT 'player',
  balance DECIMAL(32,8) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token VARCHAR(100) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE otp_requests (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(150) NOT NULL,
  otp_hash VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  attempts INT DEFAULT 0,
  ip_address VARCHAR(50),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE user_claims (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  wallet_address VARCHAR(100) NOT NULL,
  score INT NOT NULL,
  amount DECIMAL(32,8) NOT NULL,
  claim_hash VARCHAR(255) NOT NULL,
  nonce VARCHAR(64) NOT NULL,
  claimed TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE tx_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  type ENUM('claim','buy','sell','sponsor') NOT NULL,
  amount DECIMAL(32,8) NOT NULL,
  fee DECIMAL(32,8) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
