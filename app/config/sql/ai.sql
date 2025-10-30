-- Run these statements in your MySQL 'studyaid' database

CREATE TABLE IF NOT EXISTS ai_summary (
  id INT AUTO_INCREMENT PRIMARY KEY,
  userID INT NOT NULL,
  fileID INT NOT NULL,
  content MEDIUMTEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_user_file (userID, fileID)
);

CREATE TABLE IF NOT EXISTS ai_note (
  id INT AUTO_INCREMENT PRIMARY KEY,
  userID INT NOT NULL,
  fileID INT NOT NULL,
  content MEDIUMTEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_user_file (userID, fileID)
);

CREATE TABLE IF NOT EXISTS ai_mindmap (
  id INT AUTO_INCREMENT PRIMARY KEY,
  userID INT NOT NULL,
  fileID INT NOT NULL,
  json JSON NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_user_file (userID, fileID)
);

CREATE TABLE IF NOT EXISTS ai_usage (
  id INT AUTO_INCREMENT PRIMARY KEY,
  userID INT NULL,
  task VARCHAR(32) NOT NULL,
  model VARCHAR(64) NOT NULL,
  input_tokens INT NULL,
  output_tokens INT NULL,
  cost_usd DECIMAL(10,6) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


