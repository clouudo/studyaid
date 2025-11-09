-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 09, 2025 at 04:57 PM
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
-- Table structure for table `chatbot`
--

CREATE TABLE `chatbot` (
  `chatbotID` int(11) NOT NULL,
  `fileID` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chatbot`
--

INSERT INTO `chatbot` (`chatbotID`, `fileID`, `title`, `createdAt`) VALUES
(2, 10, 'Summarization Length and Sentiment Preservation', '2025-11-09 22:06:45');

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
(10, 1, NULL, 'Sentiment_Analysis_of_Summarized_Texts_pdf.txt', 'txt', '﻿Sentiment Analysis of Summarized Texts.pdf\n==========================================\n# Analyzing Sentiment Shifts in Texts Summarized at Varying Compression Ratios\n**Date:** 18/10/2025\n**Members:** Yong Jia Ying, To Jia Xuan, and Yeoh Man Tik\n---\n## Introduction\n*   **Problem:** Despite advancements in text summarization, there\'s limited research on how summary length (compression ratio) affects sentiment preservation.\n*   **Current Focus:** Most prior work prioritizes fluency and informativeness, often neglecting how aggressive compression or paraphrasing can distort sentiment-bearing words.\n*   **Consequences:** This can lead to sentiment polarity flips or reduced accuracy in downstream sentiment analysis tasks.\n*   **Gap:** Existing studies rarely compare extractive and abstractive summarization methods under controlled length variations. The relationship between summarization degree and sentiment integrity remains unclear, especially across different types of methods.\n*   **Importance:** Addressing this gap is crucial for developing sentiment-aware summarization practices that maintain emotional tone and reliability for sentiment analysis.\n---\n## Research Gap\n*   Limited systematic examination of how summary length/compression ratio impacts sentiment polarity preservation.\n*   Overlooking of how aggressive summarization can distort sentiment-bearing words.\n*   Lack of comparative studies between extractive and abstractive summarization techniques under controlled length variations.\n*   Unclear relationship between summarization degree and sentiment integrity across different summarization methods.\n---\n## Research Trends\n*   **Metric Development & Diagnostic Studies:**\n    *   **Definition:** Creation of metrics to evaluate sentiment/affect preservation and studies demonstrating sentiment loss in summaries.\n    *   **Example:** Development of metrics like PSentScore.\n*   **Sentiment-Aware Summarization:**\n    *   **Definition:** Models or training objectives that explicitly incorporate sentiment information to generate sentiment-consistent summaries.\n*   **Controllable Summarization:**\n    *   **Definition:** Increasing interest in controlling summary length or compression ratio for better usability and readability.\n*   **Domain / Multilingual Nuance:**\n    *   **Definition:** Recent work exploring language and domain differences in summarization, with extractive methods often outperforming abstractive ones in sentiment preservation in many contexts.\n---\n## Supporting References\n1.  **PSentScore: Evaluating Sentiment Polarity in Dialogue Summarization (2023)**\n2.  **Multilingual Sentiment Analysis of Summarized Texts (2025)**\n3.  **Compression Ratio Controlled Text Summarization (Stanford project / report)**\n4.  **How Does Compression Rate Affect Summary Readability (2019)**\n5.  **Sentiment-Lossless / Sentiment-Aware Summarization work (2021)**\n---\n## Proposed Idea\nThis study proposes a systematic evaluation of sentiment preservation across different summarization lengths using both extractive and abstractive models.\n*   **Summarizers to be Used:**\n    *   **Extractive:** TextRank, SBERT\n    *   **Abstractive:** T5, BART\n*   **Datasets:** Benchmark sentiment review datasets (freely available and sentiment-labelled).\n*   **Compression Ratios:** Multiple levels will be tested (e.g., 25%, 50%, 75%).\n*   **Sentiment Analysis:** A pretrained sentiment classifier will be used to analyze the sentiment of each summary.\n*   **Evaluation Metrics:**\n    *   Accuracy loss\n    *   Polarity flip rate\n    *   Confidence change\n*   **Goal:** To identify which summarization techniques and compression thresholds best preserve sentiment polarity, providing practical insights for sentiment-sensitive applications.\n---\n## How It Works?\n1.  **Datasets:** Utilize freely available sentiment-labelled review datasets.\n2.  **Summarization Techniques:** Employ open-source models: TextRank, SBERT, T5, and BART.\n3.  **Sentiment Classification:**\n    *   Use pretrained and self-trained sentiment models.\n    *   Evaluate sentiment of original text and generated summaries.\n    *   Measure:\n        *   Polarity flips (change from positive to negative, or vice versa).\n        *   Confidence drops in sentiment prediction.\n4.  **Metrics Measure at Each Compression Level:**\n    *   **Sentiment Accuracy:** Percentage of samples where the predicted sentiment matches the true label.\n    *   **Polarity Flip Rate (PFR):** Percentage of samples whose sentiment polarity changes after summarization.\n    *   **Sentiment Confidence Drop (SCD):** Average decrease in the sentiment classifier\'s confidence score after summarization.\n    *   **PSentScore:** A specific metric designed to evaluate sentiment preservation.\n5.  **Statistical Tests:**\n    *   **Paired Significance Tests:** To determine if observed differences are statistically significant.\n        *   **McNemar’s test:** For comparing accuracy differences on a per-sample basis.\n        *   **Wilcoxon signed-rank test:** For comparing differences in confidence scores.\n---\n## Example Output Table Structure\nThe study will present results in tables, comparing different datasets, summarizers, and compression levels.\n**Example Output Table (Pre-trained LLM)**\n| Dataset | Summarizer | Compression | Accuracy | Flip Rate | Conf. Drop | p-value |\n| :------ | :--------- | :---------- | :------- | :-------- | :--------- | :------ |\n| A       | TextRank   | 25%         | ...      | ...       | ...        | ...     |\n|         | ...        | 25%         | ...      | ...       | ...        | ...     |\n|         | BART       | 25%         | ...      | ...       | ...        | ...     |\n|         | TextRank   | 50%         | ...      | ...       | ...        | ...     |\n|         | ...        | 50%         | ...      | ...       | ...        | ...     |\n|         | BART       | 50%         | ...      | ...       | ...        | ...     |\n|         | TextRank   | 75%         | ...      | ...       | ...        | ...     |\n|         | ...        | 75%         | ...      | ...       | ...        | ...     |\n|         | BART       | 75%         | ...      | ...       | ...        | ...     |\n*(Similar tables will be generated for Dataset B and for results using a Self-Trained Model)*\n---\n## Contribution\n*   **Insights:** Provides valuable understanding of the trade-off between summary length and sentiment fidelity.\n*   **Guidance:** Helps practitioners select appropriate summarization techniques for sentiment-critical tasks.\n*   **Benchmark Data:** Contributes benchmark data for evaluating sentiment preservation across extractive and abstractive methods.\n*   **Model Development:** Supports the creation of new sentiment-aware summarization models.\n*   **Reliability Enhancement:** Improves the reliability of NLP pipelines in domains like opinion mining, e-commerce, and social media analytics.\n---\n## Key Takeaways\n*   **Sentiment is Vulnerable:** Aggressive text summarization can significantly impact the preservation of sentiment polarity.\n*   **Method Matters:** Different summarization techniques (extractive vs. abstractive) and their specific implementations likely have varying effects on sentiment.\n*   **Compression is Key:** The degree of compression (summary length) is a critical factor influencing sentiment integrity.\n*   **Need for Evaluation:** Systematic evaluation using specific metrics (accuracy, flip rate, confidence drop) is necessary to understand these impacts.\n*   **Practical Implications:** This research will inform the development and application of summarization tools for tasks where sentiment analysis is crucial.', 'user_upload/1/content/3a47cde7-2abd-45df-a78d-fa9356ed00bf.txt', '2025-11-08'),
(11, 1, 10, 'Lucifer', 'jpg', '', 'user_upload/1/content/English/Tutorial/cc85f85a-dc1f-4781-b228-68b563452ae1.jpg', '2025-11-09');

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

--
-- Dumping data for table `folder`
--

INSERT INTO `folder` (`folderID`, `userID`, `parentFolderID`, `name`, `folderPath`) VALUES
(7, 1, NULL, 'Science', NULL),
(8, 1, NULL, 'Maths', NULL),
(9, 1, NULL, 'English', NULL),
(10, 1, 9, 'Tutorial', NULL);

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

--
-- Dumping data for table `mindmap`
--

INSERT INTO `mindmap` (`mindmapID`, `fileID`, `data`, `title`, `imagePath`, `createdAt`) VALUES
(28, 10, '\"# Analyzing Sentiment Shifts in Texts Summarized at Varying Compression Ratios\\n\\n## Introduction\\n### Problem Statement\\n#### Limited Research on Summary Length and Sentiment\\n#### Aggressive Compression Distorts Sentiment\\n#### Sentiment Polarity Flips and Reduced Accuracy\\n#### Gap in Comparative Studies (Extractive vs. Abstractive)\\n#### Importance for Sentiment-Aware Summarization\\n\\n## Research Gap\\n### Impact of Summary Length on Sentiment Polarity\\n### Distortion of Sentiment-Bearing Words\\n### Lack of Comparative Studies (Extractive vs. Abstractive)\\n### Unclear Relationship: Summarization Degree vs. Sentiment Integrity\\n\\n## Research Trends\\n### Metric Development & Diagnostic Studies\\n#### Sentiment\\/Affect Preservation Metrics\\n#### Studies Demonstrating Sentiment Loss\\n#### Example: PSentScore\\n### Sentiment-Aware Summarization\\n#### Incorporating Sentiment Information\\n#### Generating Sentiment-Consistent Summaries\\n### Controllable Summarization\\n#### Controlling Summary Length\\/Compression\\n#### Improving Usability and Readability\\n### Domain \\/ Multilingual Nuance\\n#### Language and Domain Differences\\n#### Extractive vs. Abstractive Performance in Sentiment Preservation\\n\\n## Supporting References\\n### PSentScore: Evaluating Sentiment Polarity in Dialogue Summarization (2023)\\n### Multilingual Sentiment Analysis of Summarized Texts (2025)\\n### Compression Ratio Controlled Text Summarization (Stanford)\\n### How Does Compression Rate Affect Summary Readability (2019)\\n### Sentiment-Lossless \\/ Sentiment-Aware Summarization work (2021)\\n\\n## Proposed Idea\\n### Systematic Evaluation of Sentiment Preservation\\n#### Across Different Summarization Lengths\\n#### Using Extractive and Abstractive Models\\n### Summarizers to be Used\\n#### Extractive\\n##### TextRank\\n##### SBERT\\n#### Abstractive\\n##### T5\\n##### BART\\n### Datasets\\n#### Benchmark Sentiment Review Datasets\\n#### Freely Available and Sentiment-Labelled\\n### Compression Ratios\\n#### Multiple Levels Tested (e.g., 25%, 50%, 75%)\\n### Sentiment Analysis\\n#### Pretrained Sentiment Classifier\\n#### Analyze Sentiment of Original Text and Summaries\\n### Evaluation Metrics\\n#### Accuracy Loss\\n#### Polarity Flip Rate\\n#### Confidence Change\\n### Goal\\n#### Identify Best Techniques and Thresholds for Sentiment Preservation\\n#### Provide Practical Insights for Sentiment-Critical Tasks\\n\\n## How It Works?\\n### 1. Datasets\\n#### Freely Available Sentiment-Labelled Review Datasets\\n### 2. Summarization Techniques\\n#### Open-Source Models: TextRank, SBERT, T5, BART\\n### 3. Sentiment Classification\\n#### Pretrained and Self-Trained Sentiment Models\\n#### Evaluate Sentiment of Original Text and Summaries\\n#### Measure\\n##### Polarity Flips\\n##### Confidence Drops\\n### 4. Metrics Measure at Each Compression Level\\n#### Sentiment Accuracy\\n#### Polarity Flip Rate (PFR)\\n#### Sentiment Confidence Drop (SCD)\\n#### PSentScore\\n### 5. Statistical Tests\\n#### Paired Significance Tests\\n##### McNemar’s Test (Accuracy Differences)\\n##### Wilcoxon Signed-Rank Test (Confidence Scores)\\n\\n## Example Output Table Structure\\n### Tables Comparing Datasets, Summarizers, and Compression Levels\\n### Example Output Table (Pre-trained LLM)\\n| Dataset | Summarizer | Compression | Accuracy | Flip Rate | Conf. Drop | p-value |\\n| :------ | :--------- | :---------- | :------- | :-------- | :--------- | :------ |\\n| A       | TextRank   | 25%         | ...      | ...       | ...        | ...     |\\n|         | ...        | 25%         | ...      | ...       | ...        | ...     |\\n|         | BART       | 25%         | ...      | ...       | ...        | ...     |\\n|         | TextRank   | 50%         | ...      | ...       | ...        | ...     |\\n|         | ...        | 50%         | ...      | ...       | ...        | ...     |\\n|         | BART       | 50%         | ...      | ...       | ...        | ...     |\\n|         | TextRank   | 75%         | ...      | ...       | ...        | ...     |\\n|         | ...        | 75%         | ...      | ...       | ...        | ...     |\\n|         | BART       | 75%         | ...      | ...       | ...        | ...     |\\n#### *(Similar tables for Dataset B and Self-Trained Model)*\\n\\n## Contribution\\n### Insights\\n#### Trade-off: Summary Length vs. Sentiment Fidelity\\n### Guidance\\n#### Selecting Summarization Techniques for Sentiment-Critical Tasks\\n### Benchmark Data\\n#### Evaluating Sentiment Preservation (Extractive vs. Abstractive)\\n### Model Development\\n#### Supporting Sentiment-Aware Summarization Models\\n### Reliability Enhancement\\n#### Improving NLP Pipelines (Opinion Mining, E-commerce, Social Media)\\n\\n## Key Takeaways\\n### Sentiment is Vulnerable\\n#### Aggressive Summarization Impacts Polarity\\n### Method Matters\\n#### Varying Effects of Extractive vs. Abstractive Techniques\\n### Compression is Key\\n#### Critical Factor for Sentiment Integrity\\n### Need for Evaluation\\n#### Systematic Metrics Required (Accuracy, Flip Rate, Confidence Drop)\\n### Practical Implications\\n#### Informing Development and Application of Summarization Tools\"', 'Sentiment Preservation in Text Summarization', NULL, '2025-11-09 15:41:01');

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

--
-- Dumping data for table `note`
--

INSERT INTO `note` (`noteID`, `fileID`, `title`, `content`, `createdAt`) VALUES
(17, 10, 'Summarization Length and Sentiment Preservation', '# Sentiment Analysis of Summarized Texts Study Notes\n\n## 1. Introduction\n\n*   **Problem:** Limited research on how summary length (compression ratio) affects sentiment preservation in text summarization.\n*   **Current Focus:** Prior work often prioritizes fluency/informativeness over sentiment preservation, leading to potential sentiment distortion.\n*   **Consequences:** Sentiment polarity flips or reduced accuracy in downstream sentiment analysis.\n*   **Gap:** Lack of comparative studies between extractive and abstractive summarization methods under controlled length variations.\n*   **Importance:** Crucial for developing sentiment-aware summarization practices.\n\n## 2. Research Gap\n\n*   **Summary Length & Sentiment:** Limited systematic examination of how compression ratio impacts sentiment polarity preservation.\n*   **Distortion:** Overlooking how aggressive summarization can distort sentiment-bearing words.\n*   **Method Comparison:** Lack of comparative studies between extractive and abstractive methods under controlled lengths.\n*   **Integrity:** Unclear relationship between summarization degree and sentiment integrity across different methods.\n\n## 3. Research Trends\n\n*   **Metric Development & Diagnostic Studies:**\n    *   **Definition:** Creating metrics to evaluate sentiment/affect preservation and studies showing sentiment loss.\n    *   **Example:** PSentScore.\n*   **Sentiment-Aware Summarization:**\n    *   **Definition:** Models/objectives that explicitly incorporate sentiment for consistent summaries.\n*   **Controllable Summarization:**\n    *   **Definition:** Growing interest in controlling summary length/compression ratio.\n*   **Domain / Multilingual Nuance:**\n    *   **Definition:** Exploring language/domain differences; extractive methods often better for sentiment preservation.\n\n## 4. Proposed Idea\n\n*   **Goal:** Systematic evaluation of sentiment preservation across different summarization lengths using extractive and abstractive models.\n*   **Summarizers:**\n    *   **Extractive:** TextRank, SBERT\n    *   **Abstractive:** T5, BART\n*   **Datasets:** Benchmark sentiment review datasets (sentiment-labelled).\n*   **Compression Ratios:** Multiple levels (e.g., 25%, 50%, 75%).\n*   **Sentiment Analysis:** Pretrained sentiment classifier.\n*   **Evaluation Metrics:**\n    *   Accuracy loss\n    *   Polarity flip rate\n    *   Confidence change\n*   **Outcome:** Identify techniques and thresholds that best preserve sentiment polarity.\n\n## 5. How It Works?\n\n1.  **Datasets:** Use freely available sentiment-labelled review datasets.\n2.  **Summarization Techniques:** Employ open-source models (TextRank, SBERT, T5, BART).\n3.  **Sentiment Classification:**\n    *   Use pretrained and self-trained sentiment models.\n    *   Evaluate sentiment of original text and summaries.\n    *   Measure:\n        *   **Polarity flips:** Change from positive to negative or vice versa.\n        *   **Confidence drops:** Decrease in sentiment classifier\'s confidence.\n4.  **Metrics Measured at Each Compression Level:**\n    *   **Sentiment Accuracy:** Percentage of samples with correct predicted sentiment.\n    *   **Polarity Flip Rate (PFR):** Percentage of samples whose sentiment polarity changes.\n    *   **Sentiment Confidence Drop (SCD):** Average decrease in sentiment classifier\'s confidence.\n    *   **PSentScore:** Metric for sentiment preservation.\n5.  **Statistical Tests:**\n    *   **Paired Significance Tests:** To determine if differences are statistically significant.\n        *   **McNemar’s test:** For accuracy differences.\n        *   **Wilcoxon signed-rank test:** For confidence score differences.\n\n## 6. Example Output Table Structure\n\n*   Results presented in tables comparing datasets, summarizers, and compression levels.\n*   **Columns:** Dataset, Summarizer, Compression, Accuracy, Flip Rate, Conf. Drop, p-value.\n\n## 7. Contribution\n\n*   **Insights:** Understanding the trade-off between summary length and sentiment fidelity.\n*   **Guidance:** Helps practitioners select appropriate summarization techniques for sentiment-critical tasks.\n*   **Benchmark Data:** Contributes data for evaluating sentiment preservation.\n*   **Model Development:** Supports creation of new sentiment-aware summarization models.\n*   **Reliability Enhancement:** Improves NLP pipeline reliability in opinion mining, e-commerce, social media.\n\n## 8. Key Takeaways\n\n*   **Sentiment is Vulnerable:** Aggressive summarization can significantly impact sentiment polarity.\n*   **Method Matters:** Different summarization techniques have varying effects on sentiment.\n*   **Compression is Key:** Summary length is a critical factor for sentiment integrity.\n*   **Need for Evaluation:** Systematic evaluation with specific metrics is necessary.\n*   **Practical Implications:** Informs development and application of summarization tools for sentiment-sensitive tasks.', '2025-11-09 15:40:11');

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

--
-- Dumping data for table `question`
--

INSERT INTO `question` (`questionID`, `quizID`, `type`, `question`) VALUES
(2, 5, 'MCQ', '{\"quiz\": [\n    {\n        \"question\": \"What is the primary problem addressed by the research outlined in the document?\",\n        \"answer\": \"Limited research on how summary length affects sentiment preservation.\",\n        \"options\": [\n            \"Lack of efficient text summarization algorithms.\",\n            \"Limited research on how summary length affects sentiment preservation.\",\n            \"Difficulty in training abstractive summarization models.\",\n            \"The high computational cost of sentiment analysis.\"\n        ]\n    },\n    {\n        \"question\": \"According to the introduction, what do most prior works in text summarization primarily prioritize, often neglecting sentiment?\",\n        \"answer\": \"Fluency and informativeness.\",\n        \"options\": [\n            \"Sentiment preservation and accuracy.\",\n            \"Compression ratio and speed.\",\n            \"Fluency and informativeness.\",\n            \"Multilingual support and domain adaptation.\"\n        ]\n    },\n    {\n        \"question\": \"What are the potential consequences of aggressive compression or paraphrasing on sentiment-bearing words, as stated in the document?\",\n        \"answer\": \"Sentiment polarity flips or reduced accuracy in downstream sentiment analysis.\",\n        \"options\": [\n            \"Improved readability and conciseness.\",\n            \"Increased processing speed for sentiment analysis.\",\n            \"Sentiment polarity flips or reduced accuracy in downstream sentiment analysis.\",\n            \"Enhanced multilingual sentiment detection.\"\n        ]\n    },\n    {\n        \"question\": \"Which of the following is identified as a research gap in existing studies regarding summarization methods?\",\n        \"answer\": \"Lack of comparative studies between extractive and abstractive summarization techniques under controlled length variations.\",\n        \"options\": [\n            \"Insufficient development of new summarization algorithms.\",\n            \"Lack of comparative studies between extractive and abstractive summarization techniques under controlled length variations.\",\n            \"The absence of sentiment-labeled datasets for summarization.\",\n            \"Overemphasis on sentiment preservation in current models.\"\n        ]\n    },\n    {\n        \"question\": \"What is PSentScore an example of, according to the \'Research Trends\' section?\",\n        \"answer\": \"A metric developed to evaluate sentiment/affect preservation.\",\n        \"options\": [\n            \"A new abstractive summarization model.\",\n            \"A dataset for multilingual sentiment analysis.\",\n            \"A metric developed to evaluate sentiment/affect preservation.\",\n            \"A technique for controlling summary length.\"\n        ]\n    },\n    {\n        \"question\": \"What is the main objective of \'Sentiment-Aware Summarization\' as described in the research trends?\",\n        \"answer\": \"Models or training objectives that explicitly incorporate sentiment information to generate sentiment-consistent summaries.\",\n        \"options\": [\n            \"To create summaries that are always positive in sentiment.\",\n            \"To develop models that can detect sentiment in any language.\",\n            \"Models or training objectives that explicitly incorporate sentiment information to generate sentiment-consistent summaries.\",\n            \"To reduce the length of summaries without losing any information.\"\n        ]\n    },\n    {\n        \"question\": \"Which of the following pairs represents the extractive summarizers proposed for use in the study?\",\n        \"answer\": \"TextRank, SBERT.\",\n        \"options\": [\n            \"T5, BART.\",\n            \"TextRank, SBERT.\",\n            \"GPT-3, BERT.\",\n            \"Pegasus, Pointer-Generator.\"\n        ]\n    },\n    {\n        \"question\": \"What type of datasets will be utilized in the proposed study?\",\n        \"answer\": \"Benchmark sentiment review datasets.\",\n        \"options\": [\n            \"Proprietary corporate documents.\",\n            \"General news articles.\",\n            \"Benchmark sentiment review datasets.\",\n            \"Scientific abstracts.\"\n        ]\n    },\n    {\n        \"question\": \"What are the specific compression ratios that will be tested in the proposed study?\",\n        \"answer\": \"25%, 50%, 75%.\",\n        \"options\": [\n            \"10%, 20%, 30%.\",\n            \"25%, 50%, 75%.\",\n            \"40%, 60%, 80%.\",\n            \"15%, 45%, 85%.\"\n        ]\n    },\n    {\n        \"question\": \"Which of the following is NOT listed as an evaluation metric for sentiment preservation in the proposed study?\",\n        \"answer\": \"BLEU score.\",\n        \"options\": [\n            \"Accuracy loss.\",\n            \"Polarity flip rate.\",\n            \"Confidence change.\",\n            \"BLEU score.\"\n        ]\n    },\n    {\n        \"question\": \"What does the Polarity Flip Rate (PFR) specifically measure in the context of this study?\",\n        \"answer\": \"Percentage of samples whose sentiment polarity changes after summarization.\",\n        \"options\": [\n            \"The overall accuracy of the sentiment classifier.\",\n            \"The average confidence score of sentiment predictions.\",\n            \"Percentage of samples whose sentiment polarity changes after summarization.\",\n            \"The number of words removed during summarization.\"\n        ]\n    },\n    {\n        \"question\": \"Which statistical test is proposed for comparing accuracy differences on a per-sample basis?\",\n        \"answer\": \"McNemar’s test.\",\n        \"options\": [\n            \"ANOVA.\",\n            \"T-test.\",\n            \"McNemar’s test.\",\n            \"Chi-squared test.\"\n        ]\n    },\n    {\n        \"question\": \"What is one of the key insights the study aims to provide regarding summarization?\",\n        \"answer\": \"Valuable understanding of the trade-off between summary length and sentiment fidelity.\",\n        \"options\": [\n            \"A definitive ranking of all summarization models.\",\n            \"New methods for generating abstractive summaries.\",\n            \"Valuable understanding of the trade-off between summary length and sentiment fidelity.\",\n            \"Improved techniques for multilingual sentiment analysis.\"\n        ]\n    },\n    {\n        \"question\": \"According to the \'Key Takeaways,\' what is significantly impacted by aggressive text summarization?\",\n        \"answer\": \"The preservation of sentiment polarity.\",\n        \"options\": [\n            \"The grammatical correctness of the summary.\",\n            \"The speed of summary generation.\",\n            \"The preservation of sentiment polarity.\",\n            \"The ability to detect factual errors.\"\n        ]\n    },\n    {\n        \"question\": \"What is highlighted as a critical factor influencing sentiment integrity in summaries?\",\n        \"answer\": \"The degree of compression (summary length).\",\n        \"options\": [\n            \"The specific domain of the text.\",\n            \"The computational power used.\",\n            \"The degree of compression (summary length).\",\n            \"The number of authors involved in the summarization.\"\n        ]\n    }\n]}'),
(3, 6, 'MCQ', '{\"quiz\": [\n    {\n        \"question\": \"What is the main problem addressed by the research described in the document?\",\n        \"answer\": \"Limited understanding of how summary length affects sentiment preservation.\",\n        \"options\": [\n            \"Lack of research on text generation models.\",\n            \"Limited understanding of how summary length affects sentiment preservation.\",\n            \"Difficulty in creating multilingual summaries.\",\n            \"The high cost of sentiment analysis tools.\"\n        ]\n    },\n    {\n        \"question\": \"Who are the members listed for this research project?\",\n        \"answer\": \"Yong Jia Ying, To Jia Xuan, Yeoh Man Tik\",\n        \"options\": [\n            \"John Doe, Jane Smith, Peter Jones\",\n            \"Yong Jia Ying, To Jia Xuan, Yeoh Man Tik\",\n            \"Alice Wonderland, Bob Builder, Charlie Chaplin\",\n            \"Sarah Connor, Kyle Reese, T-800\"\n        ]\n    },\n    {\n        \"question\": \"Which of the following summarization models are proposed to be used in this study?\",\n        \"answer\": \"TextRank, SBERT, T5, BART\",\n        \"options\": [\n            \"GPT-3, BERT\",\n            \"TextRank, SBERT, T5, BART\",\n            \"ELMo, Word2Vec\",\n            \"Transformer, RNN\"\n        ]\n    },\n    {\n        \"question\": \"Which of these is an evaluation metric proposed for measuring sentiment preservation in the study?\",\n        \"answer\": \"Polarity Flip Rate (PFR)\",\n        \"options\": [\n            \"BLEU score\",\n            \"ROUGE score\",\n            \"Polarity Flip Rate (PFR)\",\n            \"F1-score\"\n        ]\n    },\n    {\n        \"question\": \"What are the example compression ratios mentioned for testing in the proposed study?\",\n        \"answer\": \"25%, 50%, 75%\",\n        \"options\": [\n            \"10%, 20%, 30%\",\n            \"25%, 50%, 75%\",\n            \"5%, 10%, 15%\",\n            \"40%, 60%, 80%\"\n        ]\n    },\n    {\n        \"question\": \"According to the document, what is a key research gap related to summarization methods?\",\n        \"answer\": \"Limited comparative studies between extractive and abstractive summarization under controlled length variations.\",\n        \"options\": [\n            \"The lack of research on image summarization.\",\n            \"Limited comparative studies between extractive and abstractive summarization under controlled length variations.\",\n            \"The absence of sentiment analysis tools for English.\",\n            \"The inability to summarize very long documents.\"\n        ]\n    },\n    {\n        \"question\": \"What is the primary goal of this study?\",\n        \"answer\": \"To identify which summarization techniques and compression thresholds best preserve sentiment polarity.\",\n        \"options\": [\n            \"To develop new summarization algorithms.\",\n            \"To identify which summarization techniques and compression thresholds best preserve sentiment polarity.\",\n            \"To create new sentiment classification models.\",\n            \"To translate texts into multiple languages.\"\n        ]\n    }\n]}');

-- --------------------------------------------------------

--
-- Table structure for table `questionchat`
--

CREATE TABLE `questionchat` (
  `questionChatID` int(11) NOT NULL,
  `chatbotID` int(11) NOT NULL,
  `userQuestion` longtext NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questionchat`
--

INSERT INTO `questionchat` (`questionChatID`, `chatbotID`, `userQuestion`, `createdAt`) VALUES
(1, 2, 'W', '2025-11-09 22:44:27'),
(2, 2, 'Hello', '2025-11-09 22:44:37'),
(3, 2, 'Hello', '2025-11-09 22:47:23'),
(4, 2, 'Hello', '2025-11-09 22:48:08'),
(5, 2, 'Hello', '2025-11-09 22:48:23'),
(6, 2, 'Hello', '2025-11-09 22:48:44'),
(7, 2, 'Hello', '2025-11-09 22:50:15'),
(8, 2, 'Hello', '2025-11-09 22:50:30'),
(9, 2, 'Hello', '2025-11-09 22:58:15'),
(10, 2, 'Tell me more about bart', '2025-11-09 23:13:52'),
(11, 2, 'Tell me about this document', '2025-11-09 23:36:06'),
(12, 2, 'What is mcnemar test', '2025-11-09 23:36:17'),
(13, 2, 'Explain mcnemar test in detailed', '2025-11-09 23:36:27');

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `quizID` int(11) NOT NULL,
  `fileID` int(11) NOT NULL,
  `totalQuestions` int(11) NOT NULL,
  `examMode` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `totalScore` varchar(255) DEFAULT NULL,
  `markAt` datetime DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz`
--

INSERT INTO `quiz` (`quizID`, `fileID`, `totalQuestions`, `examMode`, `title`, `totalScore`, `markAt`, `createdAt`) VALUES
(5, 10, 15, NULL, 'Sentiment Preservation in Text Summarization', '7', '2025-11-08 23:56:05', '2025-11-08 23:37:27'),
(6, 10, 7, NULL, 'Sentiment Preservation in Text Summarization', NULL, NULL, '2025-11-09 23:35:03');

-- --------------------------------------------------------

--
-- Table structure for table `responsechat`
--

CREATE TABLE `responsechat` (
  `responseChatID` int(11) NOT NULL,
  `questionChatID` int(11) NOT NULL,
  `response` longtext NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `responsechat`
--

INSERT INTO `responsechat` (`responseChatID`, `questionChatID`, `response`, `createdAt`) VALUES
(4, 9, 'Hello! This document outlines a proposed study on analyzing sentiment shifts in texts summarized at varying compression ratios. The research aims to address the gap in understanding how summary length affects sentiment preservation, particularly comparing extractive and abstractive summarization methods. The study will use benchmark sentiment review datasets, employ models like TextRank, SBERT, T5, and BART, and evaluate sentiment using metrics such as accuracy loss, polarity flip rate, and confidence change. The goal is to provide insights into the trade-off between summary length and sentiment fidelity, guiding the selection of summarization techniques for sentiment-critical applications.', '2025-11-09 22:58:16'),
(5, 10, 'BART is an abstractive summarization model that will be used in this study. It is one of the summarizers being evaluated to understand how different summarization techniques affect sentiment preservation at varying compression ratios.', '2025-11-09 23:13:53'),
(6, 11, 'This document outlines a research proposal titled \"Analyzing Sentiment Shifts in Texts Summarized at Varying Compression Ratios.\" The project aims to investigate how the length of a text summary, controlled by compression ratio, affects the preservation of sentiment. The researchers identify a gap in current literature, which often prioritizes fluency and informativeness over sentiment integrity, leading to potential sentiment polarity flips or reduced accuracy in downstream sentiment analysis.\n\nThe study proposes to systematically evaluate sentiment preservation using both extractive (TextRank, SBERT) and abstractive (T5, BART) summarization models on benchmark sentiment review datasets. They will test multiple compression ratios (e.g., 25%, 50%, 75%) and use a pretrained sentiment classifier to analyze the sentiment of original texts and their summaries. Key evaluation metrics will include accuracy loss, polarity flip rate, and confidence change, with statistical tests like McNemar’s test and Wilcoxon signed-rank test to assess significance.\n\nThe expected contributions include insights into the trade-off between summary length and sentiment fidelity, guidance for selecting summarization techniques for sentiment-critical tasks, benchmark data for evaluating sentiment preservation, and support for developing new sentiment-aware summarization models. The key takeaways emphasize that sentiment is vulnerable to aggressive summarization, the method and compression level matter, and systematic evaluation is crucial for practical applications in areas like opinion mining and social media analytics.', '2025-11-09 23:36:08'),
(7, 12, 'McNemar\'s test is a statistical test used for comparing paired categorical data. In the context of the provided content, it is employed as a paired significance test to determine if observed differences in accuracy between two related samples are statistically significant, specifically on a per-sample basis.', '2025-11-09 23:36:18'),
(8, 13, 'McNemar\'s test is a statistical test used for comparing the accuracy differences on a per-sample basis, particularly when dealing with paired categorical data. In the context of sentiment analysis of summarized texts, it is employed to determine if there is a statistically significant difference in the performance of two models or methods when applied to the same set of samples. Specifically, it is used to compare the accuracy of sentiment prediction before and after summarization, or to compare the accuracy of two different summarization techniques on the same original texts. The test focuses on the discordant pairs, i.e., the samples where the two methods or conditions yield different outcomes.', '2025-11-09 23:36:29');

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

--
-- Dumping data for table `summary`
--

INSERT INTO `summary` (`summaryID`, `fileID`, `title`, `content`, `createdAt`) VALUES
(90, 10, 'Summarization\'s Impact on Sentiment Preservation', 'This study aims to systematically evaluate how text summarization, specifically at varying compression ratios and using both extractive and abstractive methods, affects sentiment preservation. By employing sentiment-labelled datasets and analyzing generated summaries with a pretrained classifier, the research will measure accuracy loss, polarity flip rates, and confidence changes. The goal is to identify which summarization techniques and compression levels best maintain sentiment integrity, offering practical guidance for sentiment-sensitive applications and contributing to the development of more reliable NLP pipelines.', '2025-11-09 15:40:32');

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
-- Indexes for table `chatbot`
--
ALTER TABLE `chatbot`
  ADD PRIMARY KEY (`chatbotID`),
  ADD KEY `fileID` (`fileID`);

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
-- Indexes for table `questionchat`
--
ALTER TABLE `questionchat`
  ADD PRIMARY KEY (`questionChatID`),
  ADD KEY `chatBotID` (`chatbotID`);

--
-- Indexes for table `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`quizID`),
  ADD KEY `fileID` (`fileID`);

--
-- Indexes for table `responsechat`
--
ALTER TABLE `responsechat`
  ADD PRIMARY KEY (`responseChatID`),
  ADD KEY `questionChatID` (`questionChatID`);

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
-- AUTO_INCREMENT for table `chatbot`
--
ALTER TABLE `chatbot`
  MODIFY `chatbotID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `file`
--
ALTER TABLE `file`
  MODIFY `fileID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `flashcard`
--
ALTER TABLE `flashcard`
  MODIFY `flashcardID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `folder`
--
ALTER TABLE `folder`
  MODIFY `folderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `mindmap`
--
ALTER TABLE `mindmap`
  MODIFY `mindmapID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `note`
--
ALTER TABLE `note`
  MODIFY `noteID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `question`
--
ALTER TABLE `question`
  MODIFY `questionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `questionchat`
--
ALTER TABLE `questionchat`
  MODIFY `questionChatID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `quizID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `responsechat`
--
ALTER TABLE `responsechat`
  MODIFY `responseChatID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `summary`
--
ALTER TABLE `summary`
  MODIFY `summaryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

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
-- Constraints for table `chatbot`
--
ALTER TABLE `chatbot`
  ADD CONSTRAINT `chatbot_ibfk_1` FOREIGN KEY (`fileID`) REFERENCES `file` (`fileID`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Constraints for table `questionchat`
--
ALTER TABLE `questionchat`
  ADD CONSTRAINT `questionchat_ibfk_1` FOREIGN KEY (`chatBotID`) REFERENCES `chatbot` (`chatBotID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `quiz`
--
ALTER TABLE `quiz`
  ADD CONSTRAINT `quiz_ibfk_1` FOREIGN KEY (`fileID`) REFERENCES `file` (`fileID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `responsechat`
--
ALTER TABLE `responsechat`
  ADD CONSTRAINT `responsechat_ibfk_1` FOREIGN KEY (`questionChatID`) REFERENCES `questionchat` (`questionChatID`) ON DELETE CASCADE ON UPDATE CASCADE;

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
