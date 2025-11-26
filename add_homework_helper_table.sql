-- ============================================================================
-- Homework Helper Table Creation Script
-- ============================================================================
-- This script creates the homework_helper table for storing homework
-- questions and AI-generated answers.
-- ============================================================================

-- Drop table if exists (use with caution - this will delete all data)
-- DROP TABLE IF EXISTS `homework_helper`;

-- Create homework_helper table
CREATE TABLE IF NOT EXISTS `homework_helper` (
  `homeworkID` INT(11) NOT NULL AUTO_INCREMENT,
  `userID` INT(11) NOT NULL,
  `fileName` VARCHAR(255) NOT NULL,
  `fileType` VARCHAR(50) NOT NULL,
  `filePath` TEXT NOT NULL,
  `extractedText` TEXT DEFAULT NULL,
  `question` TEXT DEFAULT NULL,
  `answer` TEXT DEFAULT NULL,
  `status` ENUM('pending', 'processing', 'completed', 'no_question') DEFAULT 'pending',
  `createdAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`homeworkID`),
  KEY `idx_userID` (`userID`),
  KEY `idx_createdAt` (`createdAt`),
  CONSTRAINT `homework_helper_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- Table Description:
-- ============================================================================
-- homeworkID: Primary key, auto-incrementing ID
-- userID: Foreign key to user table, identifies the owner
-- fileName: Name of the uploaded homework file
-- fileType: Type of file (pdf, jpg, png, etc.)
-- filePath: Path to the file in Google Cloud Storage
-- extractedText: Text extracted from the uploaded file using OCR
-- question: The homework question identified from the extracted text
-- answer: AI-generated answer to the homework question
-- status: Current processing status (pending, processing, completed, no_question)
-- createdAt: Timestamp when the record was created
-- updatedAt: Timestamp when the record was last updated
-- ============================================================================


