-- Add exam mode, status, and config columns to quiz table
ALTER TABLE `quiz`
    ADD COLUMN `examMode` TINYINT(1) NOT NULL DEFAULT 0 AFTER `totalQuestions`,
    ADD COLUMN `status` ENUM('pending','completed') NOT NULL DEFAULT 'pending' AFTER `examMode`,
    ADD COLUMN `questionConfig` LONGTEXT NULL AFTER `title`;

-- Optional: ensure markAt defaults remain for completed timestamp
ALTER TABLE `quiz`
    MODIFY `markAt` DATETIME NULL DEFAULT NULL;

-- Create quiz_attempt table to store user attempts
CREATE TABLE IF NOT EXISTS `quiz_attempt` (
    `attemptID` INT(11) NOT NULL AUTO_INCREMENT,
    `quizID` INT(11) NOT NULL,
    `userID` INT(11) NOT NULL,
    `answers` LONGTEXT NOT NULL,
    `feedback` LONGTEXT NULL,
    `suggestions` LONGTEXT NULL,
    `score` DECIMAL(5,2) NULL,
    `examMode` TINYINT(1) NOT NULL DEFAULT 0,
    `createdAt` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`attemptID`),
    KEY `quiz_attempt_quiz_idx` (`quizID`),
    KEY `quiz_attempt_user_idx` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
