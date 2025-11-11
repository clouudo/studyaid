-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 11, 2025 at 10:12 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

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
(2, 10, 'Summarization Length and Sentiment Preservation', '2025-11-09 22:06:45'),
(4, 14, 'Software Maintenance and Evolution', '2025-11-11 12:21:39');

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
(10, 1, 10, 'Sentiment_Analysis_of_Summarized_Texts_pdf.txt', 'txt', '﻿Sentiment Analysis of Summarized Texts.pdf\n==========================================\n# Analyzing Sentiment Shifts in Texts Summarized at Varying Compression Ratios\n**Date:** 18/10/2025\n**Members:** Yong Jia Ying, To Jia Xuan, and Yeoh Man Tik\n---\n## Introduction\n*   **Problem:** Despite advancements in text summarization, there\'s limited research on how summary length (compression ratio) affects sentiment preservation.\n*   **Current Focus:** Most prior work prioritizes fluency and informativeness, often neglecting how aggressive compression or paraphrasing can distort sentiment-bearing words.\n*   **Consequences:** This can lead to sentiment polarity flips or reduced accuracy in downstream sentiment analysis tasks.\n*   **Gap:** Existing studies rarely compare extractive and abstractive summarization methods under controlled length variations. The relationship between summarization degree and sentiment integrity remains unclear, especially across different types of methods.\n*   **Importance:** Addressing this gap is crucial for developing sentiment-aware summarization practices that maintain emotional tone and reliability for sentiment analysis.\n---\n## Research Gap\n*   Limited systematic examination of how summary length/compression ratio impacts sentiment polarity preservation.\n*   Overlooking of how aggressive summarization can distort sentiment-bearing words.\n*   Lack of comparative studies between extractive and abstractive summarization techniques under controlled length variations.\n*   Unclear relationship between summarization degree and sentiment integrity across different summarization methods.\n---\n## Research Trends\n*   **Metric Development & Diagnostic Studies:**\n    *   **Definition:** Creation of metrics to evaluate sentiment/affect preservation and studies demonstrating sentiment loss in summaries.\n    *   **Example:** Development of metrics like PSentScore.\n*   **Sentiment-Aware Summarization:**\n    *   **Definition:** Models or training objectives that explicitly incorporate sentiment information to generate sentiment-consistent summaries.\n*   **Controllable Summarization:**\n    *   **Definition:** Increasing interest in controlling summary length or compression ratio for better usability and readability.\n*   **Domain / Multilingual Nuance:**\n    *   **Definition:** Recent work exploring language and domain differences in summarization, with extractive methods often outperforming abstractive ones in sentiment preservation in many contexts.\n---\n## Supporting References\n1.  **PSentScore: Evaluating Sentiment Polarity in Dialogue Summarization (2023)**\n2.  **Multilingual Sentiment Analysis of Summarized Texts (2025)**\n3.  **Compression Ratio Controlled Text Summarization (Stanford project / report)**\n4.  **How Does Compression Rate Affect Summary Readability (2019)**\n5.  **Sentiment-Lossless / Sentiment-Aware Summarization work (2021)**\n---\n## Proposed Idea\nThis study proposes a systematic evaluation of sentiment preservation across different summarization lengths using both extractive and abstractive models.\n*   **Summarizers to be Used:**\n    *   **Extractive:** TextRank, SBERT\n    *   **Abstractive:** T5, BART\n*   **Datasets:** Benchmark sentiment review datasets (freely available and sentiment-labelled).\n*   **Compression Ratios:** Multiple levels will be tested (e.g., 25%, 50%, 75%).\n*   **Sentiment Analysis:** A pretrained sentiment classifier will be used to analyze the sentiment of each summary.\n*   **Evaluation Metrics:**\n    *   Accuracy loss\n    *   Polarity flip rate\n    *   Confidence change\n*   **Goal:** To identify which summarization techniques and compression thresholds best preserve sentiment polarity, providing practical insights for sentiment-sensitive applications.\n---\n## How It Works?\n1.  **Datasets:** Utilize freely available sentiment-labelled review datasets.\n2.  **Summarization Techniques:** Employ open-source models: TextRank, SBERT, T5, and BART.\n3.  **Sentiment Classification:**\n    *   Use pretrained and self-trained sentiment models.\n    *   Evaluate sentiment of original text and generated summaries.\n    *   Measure:\n        *   Polarity flips (change from positive to negative, or vice versa).\n        *   Confidence drops in sentiment prediction.\n4.  **Metrics Measure at Each Compression Level:**\n    *   **Sentiment Accuracy:** Percentage of samples where the predicted sentiment matches the true label.\n    *   **Polarity Flip Rate (PFR):** Percentage of samples whose sentiment polarity changes after summarization.\n    *   **Sentiment Confidence Drop (SCD):** Average decrease in the sentiment classifier\'s confidence score after summarization.\n    *   **PSentScore:** A specific metric designed to evaluate sentiment preservation.\n5.  **Statistical Tests:**\n    *   **Paired Significance Tests:** To determine if observed differences are statistically significant.\n        *   **McNemar’s test:** For comparing accuracy differences on a per-sample basis.\n        *   **Wilcoxon signed-rank test:** For comparing differences in confidence scores.\n---\n## Example Output Table Structure\nThe study will present results in tables, comparing different datasets, summarizers, and compression levels.\n**Example Output Table (Pre-trained LLM)**\n| Dataset | Summarizer | Compression | Accuracy | Flip Rate | Conf. Drop | p-value |\n| :------ | :--------- | :---------- | :------- | :-------- | :--------- | :------ |\n| A       | TextRank   | 25%         | ...      | ...       | ...        | ...     |\n|         | ...        | 25%         | ...      | ...       | ...        | ...     |\n|         | BART       | 25%         | ...      | ...       | ...        | ...     |\n|         | TextRank   | 50%         | ...      | ...       | ...        | ...     |\n|         | ...        | 50%         | ...      | ...       | ...        | ...     |\n|         | BART       | 50%         | ...      | ...       | ...        | ...     |\n|         | TextRank   | 75%         | ...      | ...       | ...        | ...     |\n|         | ...        | 75%         | ...      | ...       | ...        | ...     |\n|         | BART       | 75%         | ...      | ...       | ...        | ...     |\n*(Similar tables will be generated for Dataset B and for results using a Self-Trained Model)*\n---\n## Contribution\n*   **Insights:** Provides valuable understanding of the trade-off between summary length and sentiment fidelity.\n*   **Guidance:** Helps practitioners select appropriate summarization techniques for sentiment-critical tasks.\n*   **Benchmark Data:** Contributes benchmark data for evaluating sentiment preservation across extractive and abstractive methods.\n*   **Model Development:** Supports the creation of new sentiment-aware summarization models.\n*   **Reliability Enhancement:** Improves the reliability of NLP pipelines in domains like opinion mining, e-commerce, and social media analytics.\n---\n## Key Takeaways\n*   **Sentiment is Vulnerable:** Aggressive text summarization can significantly impact the preservation of sentiment polarity.\n*   **Method Matters:** Different summarization techniques (extractive vs. abstractive) and their specific implementations likely have varying effects on sentiment.\n*   **Compression is Key:** The degree of compression (summary length) is a critical factor influencing sentiment integrity.\n*   **Need for Evaluation:** Systematic evaluation using specific metrics (accuracy, flip rate, confidence drop) is necessary to understand these impacts.\n*   **Practical Implications:** This research will inform the development and application of summarization tools for tasks where sentiment analysis is crucial.', 'user_upload/1/content/English/Tutorial/3a47cde7-2abd-45df-a78d-fa9356ed00bf.txt', '2025-11-08'),
(11, 1, 10, 'Lucifer', 'jpg', '', 'user_upload/1/content/English/Tutorial/cc85f85a-dc1f-4781-b228-68b563452ae1.jpg', '2025-11-09'),
(14, 2, 13, 'Chapter1', 'pdf', 'BMSE3014 Software Maintenance\nINTRODUCTION TO SOFTWARE \nMAINTENANCE \nCHAPTER 1\nBMSE3014 Software Maintenance\nIntroduction to Course Structure\n Coursework – 60%\n a) Group Assignment (question will be delivered on week 1, week 5 submission) -60%\n B) Test (week 3) – 40%\n Exam – 40%\nBMSE3014 Software Maintenance\nSoftware Evolution - Basic Concepts\n•Understanding evolution in software is crucial, as systems adapt to new user \ndemands and environmental changes.\n•Complexity of software increases unless actively managed; feedback between \nusers and maintenance teams is vital for guiding evolution.\n•Adhering to sound maintenance processes, akin to agile development, supports \nsuccessful system growth.\nBMSE3014 Software Maintenance\nSoftware Systems Evolution\n•Software evolution is the ongoing enhancement of software after its initial release to meet evolving \nstakeholder and market needs. \n•Supporting additional functionalities, improving system performance, and allowing the system to \nrun on a different operating system are among the goals.\n•Essentially, as time passes, stakeholders and users have a better understanding of the system.\n•“Over time, what evolves is not the software but our knowledge about a particular type of \nsoftware”- Mehdi Jazayeri\nBMSE3014 Software Maintenance\nImportance of Software Evolution\n•Ensuring that the software product remains relevant and competitive. \n•Enhances the overall user experience.\n•Regular upgrades significantly increase usability and functionality, making \nsoftware more efficient. \n•Additionally, the system becomes increasingly stable, effectively reducing \nsecurity risks and fortifying it against potential cyberattacks. \nSource form : https://qarea.com/blog/what-is-software-evolution-and-maintenance\nBMSE3014 Software Maintenance\nSoftware Sustainment\n As modern systems grow more dependent on software, sustainment challenges \nalso become more complicated. Overlooking these challenges may jeopardize the \nstability, improvement, and lifespan of systems in operation.\n Life-cycle sustainment factors involve supply, maintenance, transportation, \nsustaining engineering, data management, configuration management, Human \nSystems Integration (HSI), environmental and occupational health and safety \n(including explosives safety), protection of essential program information and \nanti-tamper measures, supportability, and interoperability.\nBMSE3014 Software Maintenance\nDefinition - Software Sustainment\n “The processes, procedures, people, material, and \ninformation required to support, maintain, and operate the \nsoftware aspects of a system.”- SEI working Definition\nBMSE3014 Software Maintenance\nCriteria to Enter Software Sustainment (Lapham & Sei, \n2014) \nStable software production baseline—Sustainment organizations typically require software to be stable \nbefore they will accept it for ongoing maintenance. \nComplete and current software documentation—Having thorough and up-to-date software \ndocumentation is essential for the software sustainment team. \nAuthority to Operate (ATO) for an operational software system—An Authority to Operate must be \ngranted before any software system can be deemed operational in the field and eligible for sustainment. \nCurrent and negotiated Sustainment Transition Plan—Often, a program may have been developed, \ntested, and deemed operational, yet there may be no allocated funding for the creation and negotiation of the \nSustainment Transition Plan. \nSustainment staffing and training plan—It is crucial to properly staff the sustainment organization. The \nteam should consist of trained software professionals who can collaborate with the development \norganization to ensure the necessary system knowledge is transferred.\nBMSE3014 Software Maintenance\nChallenges of Sustainment(Lapham & Sei, 2014) \n•Sustaining COTS (Commercial Off-The-Shelf) software involves addressing various factors such as system \nobsolescence, technology updates, source code escrow, and vendor license management, among other \nrelated aspects. \n•Programmatic considerations highlight the challenges of categorizing sustainment needs as “minor \nrequirements.” \n•The transition to a sustainment phase examines elements like support database migration, the infrastructure \nfor development and software support (including software testing labs, hardware spare parts, and \nprocedures for releases), workforce needs, training for operations, and planning for the transition. \n•User support covers topics such as help desk services, user manuals, and training for users.\n•Information assurance addresses the specific challenges associated with IA in relation to COTS software \nproducts and the testing required for IA.\nBMSE3014 Software Maintenance\nSoftware Maintenance - Basic Concepts\n Software maintenance is essential to ensure the long-term viability of software \nproducts throughout their lifecycle. i.e. from conception to completion.\n Changes to the software product are documented, the effects (impacts) of the \nchanges are discovered, artefacts are modified, testing is performed, and a new \nsoftware release is prepared.\n Users are educated, and assistance is available at all times.\n The term \"maintainer\" refers to a company or organisation that provides \nmaintenance work.\nSource : http://swebokwiki.org/Chapter_5:_Software_Maintenance\nBMSE3014 Software Maintenance\nBasic Concepts\n◦Software maintenance aims to update and improve existing software efficiently \nwhile maintaining its integrity. \n◦It ensures that the software continues to function throughout its lifespan, adapts to \nchanging user needs, and addresses issues that arise after deployment. \n◦The main objective is to extend the software’s useful life and postpone the need for \nreplacement. Maintenance engineering uses established software engineering \nprinciples to systematically update operational software. \n◦Rigorous change management and regression testing prevent quality from declining. \nAdditionally, maintenance offers ongoing user support, training, and monitors \nsatisfaction.\nBMSE3014 Software Maintenance\nBasic Concepts\n•Maintenance costs increase significantly in later life cycle stages, necessitating \nmeticulous software engineering.\n•Disciplined change control and testing are critical to mitigate system \ndegradation from modifications.\n•Conducting impact analysis is essential to limit unintended consequences.\n•Designing maintainability into software helps make modifications \nmanageable.\n•Maintenance cost estimation should begin early in development planning, \nusing historical data as a baseline.\n•Estimates are refined with project progression and additional information.\n•Categorizing costs reveals resource allocation, with most expenditures on \nenhancements rather than repairs.\nBMSE3014 Software Maintenance\nDefinition – Software Maintenance\n “The process of modifying a software system or component after delivery to correct faults, \nimprove performance or other attributes, or adapt to a changed environment.” - IEEE Standard \nGlossary of Software Engineering Terminology\nBMSE3014 Software Maintenance\nReasons of Maintenance\n•Prevent software operation from failure.\n•Fix discovered software bugs.\n•Improve software. Eg. to link or access to other new devices.\n•Ensure software works in different environment (if required)\n•Provide better experiences to users.\n•Comply with organizational goal (if changes)\nBMSE3014 Software Maintenance\nSoftware Development vs Software Maintenance\nSOFTWARE DEVELOPMENT \n requirements driven\n Process begins with the objective of \ndesigning and implementing a system \nto deliver certain functional and \nnonfunctional requirements.\n Primary/First Implementation.\n Development of software from new.\n Deliver new software.\nSOFTWARE MAINTENANCE \n event driven\n scheduled in response to an event.\n on-going administration/support.\n Modifying or adding new features \nbased on existing software.(May \ninclude developing software)\n Prevent software from failure.\nBMSE3014 Software Maintenance\nDevOps\nDevOps is a software development methodology that streamlines the delivery of high-performance \napplications by integrating and automating the work of development (Dev) and IT operations (Ops) teams, \nwhich traditionally functioned in silos. It promotes collaboration and coordination between these teams.\nKey features of DevOps include continuous integration and continuous delivery (CI/CD), allowing for \nsmaller, faster software updates through frequent merging, testing, and deployment of new code. This \napproach evolves from agile software development, which emphasizes iterative processes over the \ntraditional waterfall model that involves lengthy development and testing phases.\nUltimately, DevOps focuses on meeting user demands for innovative features and consistent performance. \n-IBM\nBMSE3014 Software Maintenance\nDevOps Life-Cycle \nSource: https://www.ibm.com/think/topics/devops\nBMSE3014 Software Maintenance\nDevOps Life-Cycle\nPlanning  \nTeams identify new features and functions for upcoming releases, using user feedback and insights from \ninternal stakeholders like engineers and compliance teams. The objective is to create a prioritized backlog of \nfeatures, improvements, and bug fixes.\nCoding  \nThe DevOps team develops features from the backlog using common practices like test-driven development \n(TDD), pair programming, and peer code reviews. Code is initially written and tested on local workstations \nbefore advancing in the continuous delivery pipeline\nSource: https://www.ibm.com/think/topics/devops\nBMSE3014 Software Maintenance\nDevOps Life-Cycle\nBuilding  \nNew code is integrated into the existing code base, then tested and packaged for release. Automation \nplays a key role in merging, storing, and compiling code into executable files, with outputs saved in a \nbinary repository for future use.\nTesting  \nDevOps teams utilise automated testing throughout the development process, including unit tests during \ncoding and linting after integration. Continuous testing allows for early detection of issues, implementing a \n\"shift-left\" approach.\nSource: https://www.ibm.com/think/topics/devops\nBMSE3014 Software Maintenance\nDevOps Life-Cycle\nRelease  \nIn the final workflow before user access, teams conduct thorough testing to ensure the software meets \nquality and security standards. Any identified defects are corrected before the application is launched \ninto the production environment, often using automated processes.\nDeploy*\nThe updated application moves to a production environment, initially deployed to a subset of users to \nverify stability before a full rollout.\nSource: https://www.ibm.com/think/topics/devops\nBMSE3014 Software Maintenance\nDevOps Life-Cycle\nOperate  \nDevOps teams ensure new features are functioning correctly with no service interruptions, employing \nautomated tools for continuous monitoring and optimisation of operations.\nMonitor  \nFeedback from users and insights from previous workflows are collected to improve future processes and \ninform planning for the next release of new features.\nSource: https://www.ibm.com/think/topics/devops\nBMSE3014 Software Maintenance\nDevOps Life-Cycle\nRelease\nIn the final workflow before user access, teams conduct thorough testing to ensure the software meets \nquality and security standards. Any identified defects are corrected before the application is launched into \nthe production environment, often using automated processes.\nDeploy\nThe updated application moves to a production environment, initially deployed to a subset of users to \nverify stability before a full rollout.\nSource: https://www.ibm.com/think/topics/devops\nBMSE3014 Software Maintenance\nDevOps Culture\n•The DevOps culture emphasises collaboration, communication, and automation among \nall stakeholders involved in software delivery, including development, IT operations, \ncompliance, and security teams. \n•It requires continuous interaction and shared responsibility to foster innovation and \nprioritise quality from the outset. At a technical level, DevOps leverages automated tools \nfor testing, deployment, and infrastructure provisioning to enhance project delivery and \nminimise errors.\n•Feedback and measurement are essential for optimising processes and improving \nsoftware quality. To implement a DevOps culture, organisations often need to dismantle \nsilos and form cross-functional teams that manage projects from start to finish, \npromoting accountability and collaboration in alignment with agile practices.\nSource: https://www.ibm.com/think/topics/devops\nBMSE3014 Software Maintenance\nDevOps and Maintenance\nSource: https://devops.com/making-software-maintenance-cool-devops/\nOne of the most significant—yet often overlooked—impacts of the DevOps movement is its effort to make \nsoftware maintenance appealing again. DevOps not only offers better organisational support for software \nmaintenance but also enhances communication between developers and operations teams, leading to \nmore effective maintenance strategies.\nA fundamental principle of DevOps is the \"you build it, you own it\" philosophy, which asserts that the \ndevelopers who create code are also responsible for its deployment and ongoing maintenance. Even if the \norganisation doesn’t fully embrace this principle, it likely fosters increased collaboration between \ndevelopers and IT operations. Consequently, when production issues arise or upgrades are necessary for \nmaintaining reliability, developers are actively involved in resolving these matters.\nBMSE3014 Software Maintenance\nDevOps and Maintenance\nSource:https://devops.com/making-software-maintenance-cool-devops/\nDespite the focus on software maintenance in practice, conversations about maintenance tasks remain \ninfrequent in the DevOps arena. Coding continues to be celebrated as the most exciting aspect of work \nwithin this field. While DevOps has succeeded in connecting those who code with those who maintain, it \nhasn’t yet made maintenance itself a more attractive aspect of the job.\nThis perception may need to evolve. Organizations that are ahead of the curve should acknowledge that \nsoftware maintenance—which encompasses activities like applying updates, monitoring applications, and \nscaling infrastructure—is just as critical for ensuring a smooth application experience as the software itself. \nThis recognition should be explicitly integrated into the DevOps approach.\nBMSE3014 Software Maintenance\nReferences\n◦Blokdyk. G. ((2020). Maintenance of software : a complete guide : practical tools for self-assessment. Art of Service. \n◦Blokdyk. G.. (2020). Software change management : a complete guide : practical tools for self-assessment. Art of \nService. \n◦Maxim. B. R., Pressman. R. S. (2020) Software Engineering: A Practitioner\'s Approach. 9th Edn. McGraw-Hill \nEducation\nOther references\n◦E. Varga. (2017). Unraveling software maintenance and evolution : thinking outside the box. Springer.\n◦ F. Tsui. O. Karam. B. Bernal. (2018). Essentials of Software Engineering. 4th Edition. Jones & Bartlett Learning.\n◦Tripathy, P., Naik K. (2014). Software Evolution and Maintenance – A Practitioner’s Approach. John Wiley and Son. \nSpringer Vieweg.\n◦Cook, S. , Harrison, R. , Lehman, M.M. , Wernick, P. , 2006. Evolution in software systems: foundations of the spe \nclassiﬁcation scheme. J. Softw. Maintenance 18 (1), 1–35 . \n◦http://swebokwiki.org/Chapter_5:_Software_Maintenance\n◦T. Mens, M. Wermelinger, S. Ducasse, S. Demeyer, R. Hirschfeld and M. Jazayeri, \"Challenges in software evolution,\" Eighth \nInternational Workshop on Principles of Software Evolution (IWPSE\'05), 2005, pp. 13-22, doi: 10.1109/IWPSE.2005.7.\n◦Lapham, M., & Sei. (2014). LEGACY SYSTEM SOFTWARE SUSTAINMENT . \nhttps://apps.dtic.mil/sti/tr/pdf/ADA591337.pdf', 'user_upload/2/content/Software Maintenace/282f6771-84cd-4be1-b80c-e1bdfa338f20.pdf', '2025-11-11');

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
(7, 1, 11, 'Science', NULL),
(10, 1, 9, 'Tutorial', NULL),
(11, 1, 10, 'Practical', NULL),
(13, 2, NULL, 'Software Maintenace', NULL),
(14, 2, NULL, 'Software Security', NULL);

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
(28, 10, '\"# Analyzing Sentiment Shifts in Texts Summarized at Varying Compression Ratios\\n\\n## Introduction\\n### Problem Statement\\n#### Limited Research on Summary Length and Sentiment\\n#### Aggressive Compression Distorts Sentiment\\n#### Sentiment Polarity Flips and Reduced Accuracy\\n#### Gap in Comparative Studies (Extractive vs. Abstractive)\\n#### Importance for Sentiment-Aware Summarization\\n\\n## Research Gap\\n### Impact of Summary Length on Sentiment Polarity\\n### Distortion of Sentiment-Bearing Words\\n### Lack of Comparative Studies (Extractive vs. Abstractive)\\n### Unclear Relationship: Summarization Degree vs. Sentiment Integrity\\n\\n## Research Trends\\n### Metric Development & Diagnostic Studies\\n#### Sentiment\\/Affect Preservation Metrics\\n#### Studies Demonstrating Sentiment Loss\\n#### Example: PSentScore\\n### Sentiment-Aware Summarization\\n#### Incorporating Sentiment Information\\n#### Generating Sentiment-Consistent Summaries\\n### Controllable Summarization\\n#### Controlling Summary Length\\/Compression\\n#### Improving Usability and Readability\\n### Domain \\/ Multilingual Nuance\\n#### Language and Domain Differences\\n#### Extractive vs. Abstractive Performance in Sentiment Preservation\\n\\n## Supporting References\\n### PSentScore: Evaluating Sentiment Polarity in Dialogue Summarization (2023)\\n### Multilingual Sentiment Analysis of Summarized Texts (2025)\\n### Compression Ratio Controlled Text Summarization (Stanford)\\n### How Does Compression Rate Affect Summary Readability (2019)\\n### Sentiment-Lossless \\/ Sentiment-Aware Summarization work (2021)\\n\\n## Proposed Idea\\n### Systematic Evaluation of Sentiment Preservation\\n#### Across Different Summarization Lengths\\n#### Using Extractive and Abstractive Models\\n### Summarizers to be Used\\n#### Extractive\\n##### TextRank\\n##### SBERT\\n#### Abstractive\\n##### T5\\n##### BART\\n### Datasets\\n#### Benchmark Sentiment Review Datasets\\n#### Freely Available and Sentiment-Labelled\\n### Compression Ratios\\n#### Multiple Levels Tested (e.g., 25%, 50%, 75%)\\n### Sentiment Analysis\\n#### Pretrained Sentiment Classifier\\n#### Analyze Sentiment of Original Text and Summaries\\n### Evaluation Metrics\\n#### Accuracy Loss\\n#### Polarity Flip Rate\\n#### Confidence Change\\n### Goal\\n#### Identify Best Techniques and Thresholds for Sentiment Preservation\\n#### Provide Practical Insights for Sentiment-Critical Tasks\\n\\n## How It Works?\\n### 1. Datasets\\n#### Freely Available Sentiment-Labelled Review Datasets\\n### 2. Summarization Techniques\\n#### Open-Source Models: TextRank, SBERT, T5, BART\\n### 3. Sentiment Classification\\n#### Pretrained and Self-Trained Sentiment Models\\n#### Evaluate Sentiment of Original Text and Summaries\\n#### Measure\\n##### Polarity Flips\\n##### Confidence Drops\\n### 4. Metrics Measure at Each Compression Level\\n#### Sentiment Accuracy\\n#### Polarity Flip Rate (PFR)\\n#### Sentiment Confidence Drop (SCD)\\n#### PSentScore\\n### 5. Statistical Tests\\n#### Paired Significance Tests\\n##### McNemar’s Test (Accuracy Differences)\\n##### Wilcoxon Signed-Rank Test (Confidence Scores)\\n\\n## Example Output Table Structure\\n### Tables Comparing Datasets, Summarizers, and Compression Levels\\n### Example Output Table (Pre-trained LLM)\\n| Dataset | Summarizer | Compression | Accuracy | Flip Rate | Conf. Drop | p-value |\\n| :------ | :--------- | :---------- | :------- | :-------- | :--------- | :------ |\\n| A       | TextRank   | 25%         | ...      | ...       | ...        | ...     |\\n|         | ...        | 25%         | ...      | ...       | ...        | ...     |\\n|         | BART       | 25%         | ...      | ...       | ...        | ...     |\\n|         | TextRank   | 50%         | ...      | ...       | ...        | ...     |\\n|         | ...        | 50%         | ...      | ...       | ...        | ...     |\\n|         | BART       | 50%         | ...      | ...       | ...        | ...     |\\n|         | TextRank   | 75%         | ...      | ...       | ...        | ...     |\\n|         | ...        | 75%         | ...      | ...       | ...        | ...     |\\n|         | BART       | 75%         | ...      | ...       | ...        | ...     |\\n#### *(Similar tables for Dataset B and Self-Trained Model)*\\n\\n## Contribution\\n### Insights\\n#### Trade-off: Summary Length vs. Sentiment Fidelity\\n### Guidance\\n#### Selecting Summarization Techniques for Sentiment-Critical Tasks\\n### Benchmark Data\\n#### Evaluating Sentiment Preservation (Extractive vs. Abstractive)\\n### Model Development\\n#### Supporting Sentiment-Aware Summarization Models\\n### Reliability Enhancement\\n#### Improving NLP Pipelines (Opinion Mining, E-commerce, Social Media)\\n\\n## Key Takeaways\\n### Sentiment is Vulnerable\\n#### Aggressive Summarization Impacts Polarity\\n### Method Matters\\n#### Varying Effects of Extractive vs. Abstractive Techniques\\n### Compression is Key\\n#### Critical Factor for Sentiment Integrity\\n### Need for Evaluation\\n#### Systematic Metrics Required (Accuracy, Flip Rate, Confidence Drop)\\n### Practical Implications\\n#### Informing Development and Application of Summarization Tools\"', 'Sentiment Preservation in Text Summarization', NULL, '2025-11-09 15:41:01'),
(29, 13, '\"# BMSE3014 Software Maintenance\\n\\n## Introduction to Course Structure\\n### Coursework – 60%\\n#### Group Assignment (question delivered week 1, submission week 5) - 60%\\n#### Test (week 3) – 40%\\n### Exam – 40%\\n\\n## Software Evolution - Basic Concepts\\n### Understanding Evolution\\n#### Crucial for adapting to new user demands and environmental changes.\\n#### Complexity increases unless actively managed; feedback is vital.\\n#### Adhering to sound maintenance processes supports successful growth.\\n\\n## Software Systems Evolution\\n### Definition\\n#### Ongoing enhancement after initial release to meet evolving needs.\\n### Goals\\n#### Supporting additional functionalities.\\n#### Improving system performance.\\n#### Allowing operation on different operating systems.\\n### Core Idea\\n#### Stakeholders and users gain a better understanding over time.\\n### Quote\\n#### *\\\"Over time, what evolves is not the software but our knowledge about a particular type of software\\\"* - Mehdi Jazayeri\\n\\n## Importance of Software Evolution\\n### Relevance & Competitiveness\\n### User Experience Enhancement\\n### Usability & Functionality Increase\\n### Stability & Security Fortification\\n\\n## Software Sustainment\\n### Challenges\\n#### Modern systems\' growing dependence on software.\\n#### Overlooking challenges jeopardizes stability, improvement, and lifespan.\\n### Life-Cycle Sustainment Factors\\n#### Supply\\n#### Maintenance\\n#### Transportation\\n#### Sustaining Engineering\\n#### Data Management\\n#### Configuration Management\\n#### Human Systems Integration (HSI)\\n#### Environmental and Occupational Health and Safety\\n#### Protection of Essential Program Information and Anti-Tamper Measures\\n#### Supportability\\n#### Interoperability\\n\\n### Definition - Software Sustainment\\n#### *\\\"The processes, procedures, people, material, and information required to support, maintain, and operate the software aspects of a system.\\\"* - SEI Working Definition\\n\\n### Criteria to Enter Software Sustainment (Lapham & Sei, 2014)\\n#### Stable software production baseline.\\n#### Complete and current software documentation.\\n#### Authority to Operate (ATO) for an operational system.\\n#### Current and negotiated Sustainment Transition Plan.\\n#### Sustainment staffing and training plan.\\n\\n### Challenges of Sustainment (Lapham & Sei, 2014)\\n#### Sustaining COTS software (obsolescence, technology updates, licensing).\\n#### Programmatic considerations (categorizing sustainment needs).\\n#### Transition to sustainment phase (database migration, infrastructure, workforce, training).\\n#### User support (help desk, manuals, training).\\n#### Information assurance (IA) for COTS products.\\n\\n## Software Maintenance - Basic Concepts\\n### Definition\\n#### Essential for long-term viability throughout the lifecycle.\\n### Process\\n#### Document changes.\\n#### Discover impacts.\\n#### Modify artifacts.\\n#### Perform testing.\\n#### Prepare new release.\\n#### Educate users and provide assistance.\\n### Maintainer\\n#### Company or organization providing maintenance work.\\n\\n### Basic Concepts\\n#### Aims to update and improve existing software efficiently while maintaining integrity.\\n#### Ensures continued function, adaptation to needs, and issue resolution.\\n#### Extends useful life and postpones replacement.\\n#### Uses software engineering principles for systematic updates.\\n#### Rigorous change management and regression testing prevent quality decline.\\n#### Offers ongoing user support, training, and satisfaction monitoring.\\n\\n### Cost Considerations\\n#### Maintenance costs increase significantly in later lifecycle stages.\\n#### Disciplined change control and testing mitigate degradation.\\n#### Impact analysis is essential to limit unintended consequences.\\n#### Designing maintainability into software makes modifications manageable.\\n#### Maintenance cost estimation should begin early, refined with project progression.\\n#### Most expenditures are on enhancements rather than repairs.\\n\\n### Definition – Software Maintenance\\n#### *\\\"The process of modifying a software system or component after delivery to correct faults, improve performance or other attributes, or adapt to a changed environment.\\\"* - IEEE Standard Glossary of Software Engineering Terminology\\n\\n### Reasons of Maintenance\\n#### Prevent software operation failure.\\n#### Fix discovered software bugs.\\n#### Improve software (e.g., link to new devices).\\n#### Ensure software works in different environments.\\n#### Provide better user experiences.\\n#### Comply with organizational goal changes.\\n\\n### Software Development vs Software Maintenance\\n#### **SOFTWARE DEVELOPMENT**\\n##### Requirements driven.\\n##### Begins with designing and implementing a system.\\n##### Primary\\/First Implementation.\\n##### Development from new.\\n##### Deliver new software.\\n#### **SOFTWARE MAINTENANCE**\\n##### Event driven.\\n##### Scheduled in response to an event.\\n##### On-going administration\\/support.\\n##### Modifying or adding features to existing software.\\n##### Prevent software from failure.\\n\\n## DevOps\\n### Definition\\n#### Software development methodology streamlining delivery by integrating and automating Dev and Ops teams.\\n#### Promotes collaboration and coordination.\\n### Key Features\\n#### Continuous Integration and Continuous Delivery (CI\\/CD).\\n#### Smaller, faster software updates through frequent merging, testing, and deployment.\\n#### Evolves from agile software development.\\n#### Focuses on meeting user demands for innovative features and consistent performance.\\n\\n### DevOps Life-Cycle\\n#### **Planning**\\n##### Identify new features and functions.\\n##### Use user feedback and stakeholder insights.\\n##### Create a prioritized backlog.\\n#### **Coding**\\n##### Develop features from the backlog.\\n##### Use practices like TDD, pair programming, code reviews.\\n##### Code written and tested locally before pipeline.\\n#### **Building**\\n##### Integrate new code into existing base.\\n##### Test and package for release.\\n##### Automation for merging, storing, compiling.\\n##### Outputs saved in binary repository.\\n#### **Testing**\\n##### Automated testing throughout development.\\n##### Unit tests during coding, linting after integration.\\n##### Early detection of issues (\\\"shift-left\\\" approach).\\n#### **Release**\\n##### Thorough testing before user access.\\n##### Ensure quality and security standards.\\n##### Correct defects before production launch.\\n##### Often uses automated processes.\\n#### **Deploy**\\n##### Updated application moves to production.\\n##### Initially deployed to a subset of users.\\n##### Verify stability before full rollout.\\n#### **Operate**\\n##### Ensure new features function correctly without interruptions.\\n##### Employ automated tools for continuous monitoring and optimization.\\n#### **Monitor**\\n##### Collect feedback and insights.\\n##### Improve future processes.\\n##### Inform planning for next release.\\n\\n### DevOps Culture\\n#### Emphasizes collaboration, communication, and automation among all stakeholders.\\n#### Requires continuous interaction and shared responsibility.\\n#### Leverages automated tools for testing, deployment, and infrastructure.\\n#### Feedback and measurement are essential for optimization.\\n#### Dismantle silos and form cross-functional teams.\\n#### Promotes accountability and collaboration.\\n\\n### DevOps and Maintenance\\n#### Makes software maintenance appealing again.\\n#### Offers better organizational support and enhances communication.\\n#### \\\"You build it, you own it\\\" philosophy fosters developer responsibility.\\n#### Leads to increased collaboration between developers and IT operations.\\n#### Developers actively involved in resolving production issues and upgrades.\\n\\n#### Conversations about maintenance tasks remain infrequent.\\n#### Coding continues to be celebrated as the most exciting aspect.\\n#### Hasn\'t made maintenance itself a more attractive job aspect.\\n#### Software maintenance is critical for ensuring a smooth application experience.\\n#### This recognition should be explicitly integrated into the DevOps approach.\\n\\n## References\\n### Books\\n#### Blokdyk. G. (2020). Maintenance of software : a complete guide : practical tools for self-assessment. Art of Service.\\n#### Blokdyk. G. (2020). Software change management : a complete guide : practical tools for self-assessment. Art of Service.\\n#### Maxim. B. R., Pressman. R. S. (2020) Software Engineering: A Practitioner\'s Approach. 9th Edn. McGraw-Hill Education.\\n#### E. Varga. (2017). Unraveling software maintenance and evolution : thinking outside the box. Springer.\\n#### F. Tsui. O. Karam. B. Bernal. (2018). Essentials of Software Engineering. 4th Edition. Jones & Bartlett Learning.\\n#### Tripathy, P., Naik K. (2014). Software Evolution and Maintenance – A Practitioner’s Approach. John Wiley and Son. Springer Vieweg.\\n\\n### Papers & Articles\\n#### Cook, S. , Harrison, R. , Lehman, M.M. , Wernick, P. , 2006. Evolution in software systems: foundations of the spe classification scheme. J. Softw. Maintenance 18 (1), 1–35.\\n#### T. Mens, M. Wermelinger, S. Ducasse, S. Demeyer, R. Hirschfeld and M. Jazayeri, \\\"Challenges in software evolution,\\\" Eighth International Workshop on Principles of Software Evolution (IWPSE\'05), 2005, pp. 13-22, doi: 10.1109\\/IWPSE.2005.7.\\n#### Lapham, M., & Sei. (2014). LEGACY SYSTEM SOFTWARE SUSTAINMENT. https:\\/\\/apps.dtic.mil\\/sti\\/tr\\/pdf\\/ADA591337.pdf\\n\\n### Online Resources\\n#### http:\\/\\/swebokwiki.org\\/Chapter_5:_Software_Maintenance\\n#### https:\\/\\/qarea.com\\/blog\\/what-is-software-evolution-and-maintenance\\n#### https:\\/\\/www.ibm.com\\/think\\/topics\\/devops\\n#### https:\\/\\/devops.com\\/making-software-maintenance-cool-devops\\/\"', 'Software Maintenance & DevOps', NULL, '2025-11-11 00:02:42');

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
-- Table structure for table `password_reset`
--

CREATE TABLE `password_reset` (
  `resetID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `expiresAt` datetime NOT NULL,
  `usedAt` datetime DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
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

--
-- Dumping data for table `question`
--

INSERT INTO `question` (`questionID`, `quizID`, `type`, `question`) VALUES
(21, 26, 'Short Question', '[{\"question\":\"What percentage of the BMSE3014 Software Maintenance course grade is coursework?\",\"answer\":\"60%\"},{\"question\":\"What is software evolution?\",\"answer\":\"The ongoing enhancement of software after its initial release to meet evolving stakeholder and market needs.\"},{\"question\":\"Name one benefit of software evolution.\",\"answer\":\"Ensuring that the software product remains relevant and competitive (or enhances the overall user experience, increases usability and functionality, reduces security risks).\"},{\"question\":\"What is the SEI working definition of Software Sustainment?\",\"answer\":\"The processes, procedures, people, material, and information required to support, maintain, and operate the software aspects of a system.\"},{\"question\":\"According to IEEE Standard Glossary of Software Engineering Terminology, what is software maintenance?\",\"answer\":\"The process of modifying a software system or component after delivery to correct faults, improve performance or other attributes, or adapt to a changed environment.\"},{\"question\":\"Give one reason for software maintenance.\",\"answer\":\"Prevent software operation from failure (or fix discovered software bugs, improve software, ensure software works in different environments, provide better experiences to users, comply with organizational goals).\"},{\"question\":\"What is DevOps?\",\"answer\":\"A software development methodology that streamlines the delivery of high-performance applications by integrating and automating the work of development (Dev) and IT operations (Ops) teams.\"},{\"question\":\"Is software maintenance requirements-driven or event-driven?\",\"answer\":\"Event-driven.\"}]'),
(22, 27, 'MCQ', '[{\"question\":\"What percentage of the total grade for BMSE3014 Software Maintenance is allocated to Coursework?\",\"answer\":\"60%\",\"options\":[\"40%\",\"50%\",\"60%\",\"70%\"]},{\"question\":\"Why is understanding evolution in software considered crucial?\",\"answer\":\"Systems adapt to new user demands and environmental changes.\",\"options\":[\"To reduce initial development costs\",\"To eliminate the need for future updates\",\"Systems adapt to new user demands and environmental changes\",\"To ensure software never fails\"]},{\"question\":\"Which of the following is a goal of software evolution?\",\"answer\":\"Supporting additional functionalities.\",\"options\":[\"Reducing the initial development time\",\"Eliminating all software bugs permanently\",\"Supporting additional functionalities\",\"Decreasing system performance\"]},{\"question\":\"What is one benefit of regular software upgrades mentioned in the content?\",\"answer\":\"Enhances the overall user experience.\",\"options\":[\"Increases initial software cost\",\"Reduces the need for security measures\",\"Enhances the overall user experience\",\"Makes software less efficient\"]},{\"question\":\"According to the SEI working definition, what does software sustainment involve?\",\"answer\":\"The processes, procedures, people, material, and information required to support, maintain, and operate the software aspects of a system.\",\"options\":[\"Only the initial development of software\",\"Only fixing bugs after deployment\",\"The processes, procedures, people, material, and information required to support, maintain, and operate the software aspects of a system\",\"Marketing and sales of software products\"]},{\"question\":\"According to the IEEE Standard Glossary of Software Engineering Terminology, what is software maintenance?\",\"answer\":\"The process of modifying a software system or component after delivery to correct faults, improve performance or other attributes, or adapt to a changed environment.\",\"options\":[\"The initial design and coding of a software system\",\"The process of marketing and selling software\",\"The process of modifying a software system or component after delivery to correct faults, improve performance or other attributes, or adapt to a changed environment\",\"The act of creating user manuals for software\"]},{\"question\":\"What is a key feature of DevOps methodology?\",\"answer\":\"Continuous integration and continuous delivery (CI\\/CD).\",\"options\":[\"Lengthy development and testing phases\",\"Traditional waterfall model\",\"Continuous integration and continuous delivery (CI\\/CD)\",\"Siloed development and IT operations teams\"]}]');

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
(14, 2, 'Hello', '2025-11-10 00:12:48'),
(15, 2, 'Tell me more about T5', '2025-11-10 00:12:53'),
(16, 2, 'Perfecto', '2025-11-10 00:16:14'),
(17, 2, 'What summarisation in simple terms', '2025-11-10 00:16:29'),
(22, 4, 'Can you mention what is important here', '2025-11-11 16:12:24'),
(23, 4, '**Hello**', '2025-11-11 16:13:09'),
(24, 4, 'Can you highlight important points', '2025-11-11 16:13:23');

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `quizID` int(11) NOT NULL,
  `fileID` int(11) NOT NULL,
  `totalQuestions` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `totalScore` varchar(255) DEFAULT NULL,
  `markAt` datetime DEFAULT NULL,
  `createdAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz`
--

INSERT INTO `quiz` (`quizID`, `fileID`, `totalQuestions`, `title`, `totalScore`, `markAt`, `createdAt`) VALUES
(26, 14, 8, 'Software Maintenance and DevOps', NULL, NULL, '2025-11-11 15:55:20'),
(27, 14, 7, 'Software Maintenance Fundamentals', NULL, NULL, '2025-11-11 15:55:44');

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
(9, 14, 'Hello! This document outlines a proposed study on analyzing sentiment shifts in texts summarized at varying compression ratios. The research aims to address the gap in understanding how summary length affects sentiment preservation, particularly comparing extractive and abstractive summarization methods. The study will use benchmark sentiment review datasets, employ models like TextRank, SBERT, T5, and BART, and evaluate sentiment using metrics such as accuracy loss, polarity flip rate, and confidence change. The goal is to provide insights into the trade-off between summary length and sentiment fidelity, guiding practitioners in selecting appropriate summarization techniques for sentiment-critical tasks.', '2025-11-10 00:12:49'),
(10, 15, 'T5 is an abstractive summarization model that will be used in this study. It is one of the summarizers being evaluated to understand how different summarization techniques and compression ratios affect sentiment preservation in texts.', '2025-11-10 00:12:54'),
(11, 16, 'The proposed study aims to systematically evaluate how summary length, controlled by compression ratios, affects sentiment preservation when using different text summarization techniques. It will compare extractive methods like TextRank and SBERT with abstractive methods like T5 and BART. The research will utilize sentiment-labelled review datasets, apply a pretrained sentiment classifier to original texts and their summaries, and measure sentiment accuracy, polarity flip rate, and sentiment confidence drop. The goal is to identify which summarization techniques and compression levels best maintain sentiment polarity, offering practical insights for sentiment-sensitive applications.', '2025-11-10 00:16:15'),
(12, 17, 'Summarization is the process of creating a shorter version of a text while keeping its main points. This study looks at how the length of a summary (how much it\'s shortened) affects the emotional tone or sentiment of the original text. It investigates whether making summaries shorter can change the sentiment (like making a positive review sound negative) and compares different methods of summarization to see which ones preserve sentiment best.', '2025-11-10 00:16:30'),
(17, 22, 'The provided content is from a course on BMSE3014 Software Maintenance. It covers the course structure, which includes coursework (group assignment and test) and an exam, with specific weightings. The content also delves into the basic concepts of software evolution and its importance, defining it as the ongoing enhancement of software to meet evolving needs, improve performance, and enhance user experience. Software sustainment is discussed as a critical aspect of managing modern software-dependent systems, outlining its definition, criteria for entry, and challenges. The fundamental concepts of software maintenance are explained, emphasizing its role in ensuring long-term viability, updating and improving existing software, and extending its useful life. The content differentiates software development from software maintenance, highlighting that development is requirements-driven while maintenance is event-driven. Finally, it introduces DevOps as a methodology that integrates development and IT operations to streamline software delivery, detailing its life cycle stages (planning, coding, building, testing, release, deploy, operate, monitor) and its cultural emphasis on collaboration, communication, and automation. The relationship between DevOps and software maintenance is explored, noting that while DevOps aims to improve maintenance, the perception of maintenance as less exciting than coding persists.', '2025-11-11 16:12:26'),
(18, 23, 'Hello! This course, BMSE3014 Software Maintenance, covers the essential aspects of keeping software functional and relevant throughout its lifecycle. The course structure includes coursework (60%) with a group assignment and a test, and an exam (40%). We\'ll delve into software evolution, understanding why systems need to adapt to new demands and changes. We\'ll also explore software sustainment, which involves the processes and resources needed to support and maintain software in operation, and the criteria and challenges associated with it. A significant portion will focus on the basic concepts of software maintenance itself, including its definition, reasons for it, and how it differs from software development. Finally, we\'ll examine DevOps, a methodology that integrates development and operations to streamline software delivery, and its impact on software maintenance.', '2025-11-11 16:13:10'),
(19, 24, 'The BMSE3014 Software Maintenance course focuses on several key areas:\n\nCourse Structure:\n*   Coursework accounts for 60% of the grade, consisting of a group assignment (due week 5) and a test (week 3).\n*   The exam is worth 40%.\n\nSoftware Evolution:\n*   Understanding software evolution is crucial as systems adapt to new user demands and environmental changes.\n*   Software evolution is the ongoing enhancement of software after its initial release to meet evolving stakeholder and market needs, aiming to keep it relevant, competitive, and improve user experience, usability, functionality, and stability.\n\nSoftware Sustainment:\n*   This refers to the processes, procedures, people, material, and information required to support, maintain, and operate the software aspects of a system.\n*   Criteria to enter software sustainment include a stable software production baseline, complete and current documentation, an Authority to Operate (ATO), a Sustainment Transition Plan, and a sustainment staffing and training plan.\n*   Challenges in sustainment include dealing with COTS software issues, programmatic considerations, transition planning, user support, and information assurance.\n\nSoftware Maintenance:\n*   Software maintenance is essential for the long-term viability of software products, involving documenting changes, assessing impacts, modifying artifacts, testing, and preparing new releases.\n*   Its aim is to update and improve existing software efficiently, ensure it functions throughout its lifespan, adapts to changing needs, and addresses post-deployment issues, thereby extending its useful life.\n*   Reasons for maintenance include preventing failures, fixing bugs, improving performance, adapting to new environments, enhancing user experience, and complying with organizational goals.\n*   Maintenance is event-driven, unlike development which is requirements-driven.\n*   Maintenance costs increase significantly in later lifecycle stages, making disciplined change control, testing, and impact analysis critical. Designing for maintainability and estimating costs early are important.\n\nDevOps:\n*   DevOps is a methodology that integrates development and IT operations to streamline software delivery through automation and collaboration.\n*   Its lifecycle includes Planning, Coding, Building, Testing, Release, Deploy, Operate, and Monitor.\n*   A key aspect of DevOps culture is collaboration, communication, and automation among all stakeholders.\n*   DevOps aims to make software maintenance more appealing by fostering better organizational support and communication between developers and operations teams, promoting a \"you build it, you own it\" philosophy. However, conversations about maintenance tasks are still infrequent, and coding is often celebrated more. The course suggests that maintenance activities are as critical as the software itself and should be explicitly integrated into the DevOps approach.', '2025-11-11 16:13:26');

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
(90, 10, 'Summarization\'s Impact on Sentiment Preservation', 'This study aims to systematically evaluate how text summarization, specifically at varying compression ratios and using both extractive and abstractive methods, affects sentiment preservation. By employing sentiment-labelled datasets and analyzing generated summaries with a pretrained classifier, the research will measure accuracy loss, polarity flip rates, and confidence changes. The goal is to identify which summarization techniques and compression levels best maintain sentiment integrity, offering practical guidance for sentiment-sensitive applications and contributing to the development of more reliable NLP pipelines.', '2025-11-09 15:40:32'),
(92, 14, 'Software Maintenance Fundamentals', 'This course, BMSE3014 Software Maintenance, covers the fundamental concepts of software evolution and sustainment, emphasizing the importance of adapting software to changing needs and environments. The course structure includes coursework accounting for 60% of the grade (group assignment and test) and a 40% exam. Software evolution is presented as the ongoing enhancement of software after its initial release to remain relevant, improve user experience, and enhance stability, with sustainment involving the complex processes, people, and information required to support operational software. The course also delves into the basic concepts of software maintenance, defining it as modifying software to correct faults, improve performance, or adapt to new environments, and contrasts it with software development. Finally, the course introduces DevOps, a methodology that integrates development and operations to streamline software delivery through collaboration, automation, and continuous processes, highlighting its impact on making software maintenance more appealing and effective.', '2025-11-11 08:58:36');

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
(1, 'mantik3333@gmail.com', '$2y$10$Q4PBspy3bXk.GYy7XlmvMevN0Q6xlLetBdi1Ad5Q3.Fiz2vdgSyF6', 'Yeoh Man Tik', NULL, 'TRUE'),
(2, 'john@gmail.com', '$2y$10$Pku46u/0wQ7fOflXVGlTxeoqS4a1hu5lzsK2q/i6yfprcvFJbMkBe', 'john', NULL, 'TRUE');

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
-- Dumping data for table `useranswer`
--

INSERT INTO `useranswer` (`userAnswerID`, `questionID`, `userAnswer`) VALUES
(1, 21, '{\"questionIndex\":0,\"userAnswer\":\"60%\"}'),
(2, 21, '{\"questionIndex\":1,\"userAnswer\":\"123\"}'),
(3, 21, '{\"questionIndex\":2,\"userAnswer\":\"12\"}'),
(4, 21, '{\"questionIndex\":3,\"userAnswer\":\"1231\"}'),
(5, 21, '{\"questionIndex\":4,\"userAnswer\":\"12\"}'),
(6, 21, '{\"questionIndex\":5,\"userAnswer\":\"123\"}'),
(7, 21, '{\"questionIndex\":6,\"userAnswer\":\"123\"}'),
(8, 21, '{\"questionIndex\":7,\"userAnswer\":\"123\"}');

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
-- Indexes for table `password_reset`
--
ALTER TABLE `password_reset`
  ADD PRIMARY KEY (`resetID`);

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
  MODIFY `chatbotID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `file`
--
ALTER TABLE `file`
  MODIFY `fileID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `flashcard`
--
ALTER TABLE `flashcard`
  MODIFY `flashcardID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `folder`
--
ALTER TABLE `folder`
  MODIFY `folderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `mindmap`
--
ALTER TABLE `mindmap`
  MODIFY `mindmapID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `note`
--
ALTER TABLE `note`
  MODIFY `noteID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `password_reset`
--
ALTER TABLE `password_reset`
  MODIFY `resetID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question`
--
ALTER TABLE `question`
  MODIFY `questionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `questionchat`
--
ALTER TABLE `questionchat`
  MODIFY `questionChatID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `quizID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `responsechat`
--
ALTER TABLE `responsechat`
  MODIFY `responseChatID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `summary`
--
ALTER TABLE `summary`
  MODIFY `summaryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `useranswer`
--
ALTER TABLE `useranswer`
  MODIFY `userAnswerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
  ADD CONSTRAINT `questionchat_ibfk_1` FOREIGN KEY (`chatbotID`) REFERENCES `chatbot` (`chatbotID`) ON DELETE CASCADE ON UPDATE CASCADE;

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
