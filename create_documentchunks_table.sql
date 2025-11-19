-- Create documentchunks table for RAG (Retrieval-Augmented Generation) functionality
-- This table stores document chunks with their embeddings for chatbot context retrieval

CREATE TABLE IF NOT EXISTS `documentchunks` (
  `documentChunkID` int(11) NOT NULL AUTO_INCREMENT,
  `fileID` int(11) NOT NULL,
  `chunkText` longtext NOT NULL,
  `embedding` longtext NOT NULL,
  PRIMARY KEY (`documentChunkID`),
  KEY `fileID` (`fileID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

