-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 08, 2025 at 02:09 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `studyaid`
--

-- --------------------------------------------------------

--
-- Table structure for table `file`
--

CREATE TABLE `file` (
  `fileID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `folderID` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `fileType` varchar(255) NOT NULL,
  `extracted_text` mediumtext DEFAULT NULL,
  `filePath` varchar(255) NOT NULL,
  `uploadDate` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `file`
--

INSERT INTO `file` (`fileID`, `userID`, `folderID`, `name`, `fileType`, `extracted_text`, `filePath`, `uploadDate`) VALUES
(10, 1, NULL, 'Sentiment_Analysis_of_Summarized_Texts_pdf.txt', 'txt', '﻿Sentiment Analysis of Summarized Texts.pdf\n==========================================\n# Analyzing Sentiment Shifts in Texts Summarized at Varying Compression Ratios\n**Date:** 18/10/2025\n**Members:** Yong Jia Ying, To Jia Xuan, and Yeoh Man Tik\n---\n## Introduction\n*   **Problem:** Despite advancements in text summarization, there\'s limited research on how summary length (compression ratio) affects sentiment preservation.\n*   **Current Focus:** Most prior work prioritizes fluency and informativeness, often neglecting how aggressive compression or paraphrasing can distort sentiment-bearing words.\n*   **Consequences:** This can lead to sentiment polarity flips or reduced accuracy in downstream sentiment analysis tasks.\n*   **Gap:** Existing studies rarely compare extractive and abstractive summarization methods under controlled length variations. The relationship between summarization degree and sentiment integrity remains unclear, especially across different types of methods.\n*   **Importance:** Addressing this gap is crucial for developing sentiment-aware summarization practices that maintain emotional tone and reliability for sentiment analysis.\n---\n## Research Gap\n*   Limited systematic examination of how summary length/compression ratio impacts sentiment polarity preservation.\n*   Overlooking of how aggressive summarization can distort sentiment-bearing words.\n*   Lack of comparative studies between extractive and abstractive summarization techniques under controlled length variations.\n*   Unclear relationship between summarization degree and sentiment integrity across different summarization methods.\n---\n## Research Trends\n*   **Metric Development & Diagnostic Studies:**\n    *   **Definition:** Creation of metrics to evaluate sentiment/affect preservation and studies demonstrating sentiment loss in summaries.\n    *   **Example:** Development of metrics like PSentScore.\n*   **Sentiment-Aware Summarization:**\n    *   **Definition:** Models or training objectives that explicitly incorporate sentiment information to generate sentiment-consistent summaries.\n*   **Controllable Summarization:**\n    *   **Definition:** Increasing interest in controlling summary length or compression ratio for better usability and readability.\n*   **Domain / Multilingual Nuance:**\n    *   **Definition:** Recent work exploring language and domain differences in summarization, with extractive methods often outperforming abstractive ones in sentiment preservation in many contexts.\n---\n## Supporting References\n1.  **PSentScore: Evaluating Sentiment Polarity in Dialogue Summarization (2023)**\n2.  **Multilingual Sentiment Analysis of Summarized Texts (2025)**\n3.  **Compression Ratio Controlled Text Summarization (Stanford project / report)**\n4.  **How Does Compression Rate Affect Summary Readability (2019)**\n5.  **Sentiment-Lossless / Sentiment-Aware Summarization work (2021)**\n---\n## Proposed Idea\nThis study proposes a systematic evaluation of sentiment preservation across different summarization lengths using both extractive and abstractive models.\n*   **Summarizers to be Used:**\n    *   **Extractive:** TextRank, SBERT\n    *   **Abstractive:** T5, BART\n*   **Datasets:** Benchmark sentiment review datasets (freely available and sentiment-labelled).\n*   **Compression Ratios:** Multiple levels will be tested (e.g., 25%, 50%, 75%).\n*   **Sentiment Analysis:** A pretrained sentiment classifier will be used to analyze the sentiment of each summary.\n*   **Evaluation Metrics:**\n    *   Accuracy loss\n    *   Polarity flip rate\n    *   Confidence change\n*   **Goal:** To identify which summarization techniques and compression thresholds best preserve sentiment polarity, providing practical insights for sentiment-sensitive applications.\n---\n## How It Works?\n1.  **Datasets:** Utilize freely available sentiment-labelled review datasets.\n2.  **Summarization Techniques:** Employ open-source models: TextRank, SBERT, T5, and BART.\n3.  **Sentiment Classification:**\n    *   Use pretrained and self-trained sentiment models.\n    *   Evaluate sentiment of original text and generated summaries.\n    *   Measure:\n        *   Polarity flips (change from positive to negative, or vice versa).\n        *   Confidence drops in sentiment prediction.\n4.  **Metrics Measure at Each Compression Level:**\n    *   **Sentiment Accuracy:** Percentage of samples where the predicted sentiment matches the true label.\n    *   **Polarity Flip Rate (PFR):** Percentage of samples whose sentiment polarity changes after summarization.\n    *   **Sentiment Confidence Drop (SCD):** Average decrease in the sentiment classifier\'s confidence score after summarization.\n    *   **PSentScore:** A specific metric designed to evaluate sentiment preservation.\n5.  **Statistical Tests:**\n    *   **Paired Significance Tests:** To determine if observed differences are statistically significant.\n        *   **McNemar’s test:** For comparing accuracy differences on a per-sample basis.\n        *   **Wilcoxon signed-rank test:** For comparing differences in confidence scores.\n---\n## Example Output Table Structure\nThe study will present results in tables, comparing different datasets, summarizers, and compression levels.\n**Example Output Table (Pre-trained LLM)**\n| Dataset | Summarizer | Compression | Accuracy | Flip Rate | Conf. Drop | p-value |\n| :------ | :--------- | :---------- | :------- | :-------- | :--------- | :------ |\n| A       | TextRank   | 25%         | ...      | ...       | ...        | ...     |\n|         | ...        | 25%         | ...      | ...       | ...        | ...     |\n|         | BART       | 25%         | ...      | ...       | ...        | ...     |\n|         | TextRank   | 50%         | ...      | ...       | ...        | ...     |\n|         | ...        | 50%         | ...      | ...       | ...        | ...     |\n|         | BART       | 50%         | ...      | ...       | ...        | ...     |\n|         | TextRank   | 75%         | ...      | ...       | ...        | ...     |\n|         | ...        | 75%         | ...      | ...       | ...        | ...     |\n|         | BART       | 75%         | ...      | ...       | ...        | ...     |\n*(Similar tables will be generated for Dataset B and for results using a Self-Trained Model)*\n---\n## Contribution\n*   **Insights:** Provides valuable understanding of the trade-off between summary length and sentiment fidelity.\n*   **Guidance:** Helps practitioners select appropriate summarization techniques for sentiment-critical tasks.\n*   **Benchmark Data:** Contributes benchmark data for evaluating sentiment preservation across extractive and abstractive methods.\n*   **Model Development:** Supports the creation of new sentiment-aware summarization models.\n*   **Reliability Enhancement:** Improves the reliability of NLP pipelines in domains like opinion mining, e-commerce, and social media analytics.\n---\n## Key Takeaways\n*   **Sentiment is Vulnerable:** Aggressive text summarization can significantly impact the preservation of sentiment polarity.\n*   **Method Matters:** Different summarization techniques (extractive vs. abstractive) and their specific implementations likely have varying effects on sentiment.\n*   **Compression is Key:** The degree of compression (summary length) is a critical factor influencing sentiment integrity.\n*   **Need for Evaluation:** Systematic evaluation using specific metrics (accuracy, flip rate, confidence drop) is necessary to understand these impacts.\n*   **Practical Implications:** This research will inform the development and application of summarization tools for tasks where sentiment analysis is crucial.', 'user_upload/1/content/3a47cde7-2abd-45df-a78d-fa9356ed00bf.txt', '2025-11-08');

-- --------------------------------------------------------

--
-- Table structure for table `flashcard`
--

CREATE TABLE `flashcard` (
  `flashcardID` int(11) NOT NULL,
  `fileID` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `term` longtext NOT NULL,
  `definition` longtext NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `folder`
--

CREATE TABLE `folder` (
  `folderID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `parentFolderID` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `folderPath` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mindmap`
--

CREATE TABLE `mindmap` (
  `mindmapID` int(11) NOT NULL,
  `fileID` int(11) NOT NULL,
  `data` longtext NOT NULL,
  `title` varchar(500) NOT NULL,
  `imagePath` varchar(500) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `note`
--

CREATE TABLE `note` (
  `noteID` int(11) NOT NULL,
  `fileID` int(11) NOT NULL,
  `title` varchar(500) NOT NULL,
  `content` longtext NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question`
--

CREATE TABLE `question` (
  `questionID` int(11) NOT NULL,
  `quizID` int(11) NOT NULL,
  `type` varchar(255) NOT NULL,
  `question` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `quizID` int(11) NOT NULL,
  `fileID` int(11) NOT NULL,
  `totalQuestions` int(11) NOT NULL,
  `examMode` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `totalScore` int(11) NOT NULL,
  `markAt` datetime DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `summary`
--

CREATE TABLE `summary` (
  `summaryID` int(11) NOT NULL,
  `fileID` int(11) NOT NULL,
  `title` varchar(500) NOT NULL,
  `content` longtext NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `userID` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `profilePic` varchar(255) DEFAULT NULL,
  `isActive` enum('TRUE','FALSE') NOT NULL DEFAULT 'TRUE'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`userID`, `email`, `password`, `username`, `profilePic`, `isActive`) VALUES
(1, 'mantik3333@gmail.com', '$2y$10$Q4PBspy3bXk.GYy7XlmvMevN0Q6xlLetBdi1Ad5Q3.Fiz2vdgSyF6', 'Yeoh Man Tik', NULL, 'TRUE');

-- --------------------------------------------------------

--
-- Table structure for table `useranswer`
--

CREATE TABLE `useranswer` (
  `userAnswerID` int(11) NOT NULL,
  `questionID` int(11) NOT NULL,
  `userAnswer` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `file`
--
ALTER TABLE `file`
  ADD PRIMARY KEY (`fileID`),
  ADD KEY `folderID` (`folderID`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `flashcard`
--
ALTER TABLE `flashcard`
  ADD PRIMARY KEY (`flashcardID`),
  ADD KEY `fileID` (`fileID`);

--
-- Indexes for table `folder`
--
ALTER TABLE `folder`
  ADD PRIMARY KEY (`folderID`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `mindmap`
--
ALTER TABLE `mindmap`
  ADD PRIMARY KEY (`mindmapID`);

--
-- Indexes for table `note`
--
ALTER TABLE `note`
  ADD PRIMARY KEY (`noteID`),
  ADD KEY `fileID` (`fileID`);

--
-- Indexes for table `question`
--
ALTER TABLE `question`
  ADD PRIMARY KEY (`questionID`),
  ADD KEY `quizID` (`quizID`);

--
-- Indexes for table `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`quizID`),
  ADD KEY `fileID` (`fileID`);

--
-- Indexes for table `summary`
--
ALTER TABLE `summary`
  ADD PRIMARY KEY (`summaryID`),
  ADD KEY `fileID` (`fileID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`userID`);

--
-- Indexes for table `useranswer`
--
ALTER TABLE `useranswer`
  ADD PRIMARY KEY (`userAnswerID`),
  ADD KEY `questionID` (`questionID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `file`
--
ALTER TABLE `file`
  MODIFY `fileID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `flashcard`
--
ALTER TABLE `flashcard`
  MODIFY `flashcardID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `folder`
--
ALTER TABLE `folder`
  MODIFY `folderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `mindmap`
--
ALTER TABLE `mindmap`
  MODIFY `mindmapID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `note`
--
ALTER TABLE `note`
  MODIFY `noteID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `question`
--
ALTER TABLE `question`
  MODIFY `questionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `quizID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `summary`
--
ALTER TABLE `summary`
  MODIFY `summaryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `useranswer`
--
ALTER TABLE `useranswer`
  MODIFY `userAnswerID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `file`
--
ALTER TABLE `file`
  ADD CONSTRAINT `file_ibfk_1` FOREIGN KEY (`folderID`) REFERENCES `folder` (`folderID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `file_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`);

--
-- Constraints for table `flashcard`
--
ALTER TABLE `flashcard`
  ADD CONSTRAINT `flashcard_ibfk_1` FOREIGN KEY (`fileID`) REFERENCES `file` (`fileID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `folder`
--
ALTER TABLE `folder`
  ADD CONSTRAINT `folder_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `note`
--
ALTER TABLE `note`
  ADD CONSTRAINT `note_ibfk_1` FOREIGN KEY (`fileID`) REFERENCES `file` (`fileID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `question`
--
ALTER TABLE `question`
  ADD CONSTRAINT `question_ibfk_1` FOREIGN KEY (`quizID`) REFERENCES `quiz` (`quizID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quiz`
--
ALTER TABLE `quiz`
  ADD CONSTRAINT `quiz_ibfk_1` FOREIGN KEY (`fileID`) REFERENCES `file` (`fileID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `summary`
--
ALTER TABLE `summary`
  ADD CONSTRAINT `summary_ibfk_1` FOREIGN KEY (`fileID`) REFERENCES `file` (`fileID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `useranswer`
--
ALTER TABLE `useranswer`
  ADD CONSTRAINT `useranswer_ibfk_1` FOREIGN KEY (`questionID`) REFERENCES `question` (`questionID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
