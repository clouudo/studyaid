-- Add isStarred column to flashcard table
ALTER TABLE `flashcard` 
ADD COLUMN `isStarred` TINYINT(1) NOT NULL DEFAULT 0 
AFTER `createdAt`;

-- Add index for better query performance when filtering starred items
CREATE INDEX `idx_isStarred` ON `flashcard` (`isStarred`);
