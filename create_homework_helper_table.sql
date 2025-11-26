-- Create homework_helper table
CREATE TABLE IF NOT EXISTS `homework_helper` (
  `homeworkID` INT(11) NOT NULL AUTO_INCREMENT,
  `userID` INT(11) NOT NULL,
  `fileName` VARCHAR(255) NOT NULL,
  `fileType` VARCHAR(50) NOT NULL,
  `filePath` TEXT NOT NULL,
  `extractedText` TEXT,
  `question` TEXT,
  `answer` TEXT,
  `status` ENUM('pending', 'processing', 'completed', 'no_question') DEFAULT 'pending',
  `createdAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`homeworkID`),
  KEY `idx_userID` (`userID`),
  KEY `idx_createdAt` (`createdAt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


