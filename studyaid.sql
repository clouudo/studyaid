-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 12, 2025 at 09:46 AM
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
(9, 34, 'Software Re-engineering', '2025-11-12 14:18:33');

-- --------------------------------------------------------

--
-- Table structure for table `documentchunks`
--

CREATE TABLE `documentchunks` (
  `documentChunkID` int(11) NOT NULL,
  `fileID` int(11) NOT NULL,
  `chunkText` longtext NOT NULL,
  `embedding` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(33, 2, NULL, 'Chapter 3.pptx.pdf', 'pdf', '# BMSE3014 Software Maintenance\n\n## CHAPTER 3: SOFTWARE CONFIGURATION MANAGEMENT\n\n### 3.1 Introduction to Software Configuration Management (SCM)\n\n*   **Goal of CM:** To manage and control the numerous corrections, extensions, and adaptations that are applied to a system over its lifetime.\n*   **Scope:** It handles the control of all product items and changes to those items.\n*   **SCM Application:** Applied to software products, including documents, executable software, source code, hardware, and disks.\n\n### 3.2 Definition of Software Configuration Management\n\n*   **Definition:** The discipline of identifying the configuration of a system at discrete points in time for the purpose of systematically controlling changes to this configuration and maintaining the integrity and traceability of this configuration throughout the system life cycle.\n*   **Systems Engineering Perspective:** Configuration management is a systems engineering method that ensures a product\'s attributes remain consistent throughout its life cycle.\n*   **IT Management Perspective:** Configuration management is an IT management technique that tracks individual configuration pieces of an IT system in the realm of technology.\n*   **IT Assets:** IT systems are made up of a variety of IT assets with varying levels of granularity. A piece of software, a server, or a cluster of servers can all be considered IT assets.\n*   **Focus:** The following sections concentrate on configuration management as it relates to IT software assets and CI/CD.\n\n### 3.3 Purpose and Importance of SCM\n\n*   **Traceability and Systematic Processes:** SCM ensures that development processes are traceable and systematic so that all changes are precisely managed. Consequently, the product is always in a well-defined state.\n*   **Quality and Productivity Enhancement:** SCM enhances the quality of the delivered system and the productivity of the maintainers.\n\n### 3.4 Key Objectives of SCM\n\n*   Uniquely identify every version of every software at various points in time.\n*   Retain past versions of documentations and software.\n*   Provide a trail of audit for all modifications performed.\n*   Throughout the software lifecycle, maintain the traceability and integrity of the system changes.\n\n### 3.5 Benefits of SCM\n\n#### 3.5.1 General Benefits\n\n*   Confusion is reduced and order is established.\n*   Allows you to evaluate, approve, and implement changes into a configuration item in SCM.\n*   To maintain product integrity, the necessary activities are organized.\n*   Correct product configurations are ensured.\n*   Quality is ensured and better quality software consumes less maintenance efforts.\n\n#### 3.5.2 Productivity and Liability Benefits\n\n*   Productivity is improved, because analysts and programmers know exactly where to find any piece of the software.\n*   Liability is reduced by documenting the trail of actions.\n*   Cost savings are realised across the product development life cycle.\n*   Maintaining product integrity through defined, tracked, and audited CIs ensures that the product released to customers has a well-managed bill of materials.\n*   Conformance with requirements is enabled.\n*   A reliable working environment is provided.\n*   Compliance with standards is enhanced.\n*   Accounting of status is enhanced.\n\n### 3.6 Four Elements of CM\n\n*   **Identification of software configurations:** This includes the definitions of the different artifacts, their baselines or milestones, and the changes to the artifacts.\n*   **Control of software configurations:** This element is about controlling the ways artifacts or configurations are altered with the necessary technical and administrative support.\n*   **Auditing software configurations:** This element is about making the current status of the software system in its lifecycle visible to management and determining whether or not the baselines meet their requirements.\n*   **Accounting software configuration status:** This element is about providing an administrative history of how the software system has been altered, by recording the activities necessitated by the other three SCM elements.\n\n### 3.7 Technical Dimension - SCM\n\n#### 3.7.1 Identification\n\n*   **Function:** Identifies the items whose configurations must be maintained.\n*   **Identified Elements:** Specification, design, documentation, data, drawings, source code, executable code, test plan, test script, hardware components, and software development environment components such as compilers, debuggers, and emulators.\n*   **Configuration Baseline:** The configuration baseline is identified.\n\n#### 3.7.2 Version Control\n\n*   **Mechanism:** Every time an artifact is modified, a new identifier is assigned to the artifact to minimize confusion during the evolution process.\n*   **Consideration:** Giving each alteration of the same artifact a new identification can obscure essential relationships between the objects that are individually recognized.\n\n#### 3.7.3 System Models and Selection\n\n*   **Well-Defined Items:** Requirement specifications, test cases, design, code, test results, and defect reports are all examples of well-defined things in a project that are described in discrete files.\n*   **Aggregate Artifacts:** The requirement for aggregate artifacts develops so that maintenance employees can use linkages between artifacts and attributes to guarantee consistency in large projects.\n*   **Configuration Models:** Models that support the concept of software configurations are used to capture relationships between artifacts and attributes.\n*   **Configuration Definition:** A configuration, by definition, is a collection of versionable elements. The concept of configuration increases the necessity for users to be able to access sections and versions of aggregated artifacts selectively.\n\n#### 3.7.4 Work Space Control\n\n*   **Purpose:** SCM systems use work areas to provide users with a private location. Users undertake the standard responsibilities of editing and maintaining their objects in their own workspaces.\n*   **Workspace Function:** The workspace is the environment that allows the maintainer to make and test changes in a controlled environment.\n*   **Workspace Realization:**\n    *   **Basic:** As basic as the programmer\'s home directory while modifying files.\n    *   **Complex:** A more complex system like an integrated development environment and a database.\n\n#### 3.7.5 Basic Functions in Workspace\n\n*   **Sandbox:** Checked-out files are placed in a workspace where they can be freely changed. The original files in the repository do not need to be locked.\n*   **Building:**\n    *   To save space, an SCM system usually saves the differences between subsequent versions.\n    *   The workspace converts the deltas into entire source files.\n    *   The workspace also contains the derived binaries.\n    *   SCM keeps track of source code (file) modifications (commits) and the revision history of each commit.\n    *   Commits are compressed and will be enlarged (uncompressed/extracted) when the modifications for the specific commits are required.\n    *   **Repository Types:** Single repository and distributed repository.\n*   **Isolation:** Each programmer has at least one workspace. The developer modifies, compiles, tests, and debugs the source code without interfering with the work of other developers.\n\n#### 3.7.6 Building\n\n*   **Efficiency:** A key requirement of SCM systems so that developers can quickly build an executable file from the versioned source files.\n*   **Recovery and Testing:** Enables the building of old versions of the system for recovery, testing, maintenance, or additional release purposes.\n*   **Support:** Most SCM systems support building of software.\n*   **Quality Assurance:** The build process and their products are assessed for quality assurance.\n*   **Records:** Outputs of the build process become quality assurance records, and the records may be needed for future reference.\n\n#### 3.7.7 Change Management\n\n*   **SCM System Requirements:**\n    *   (i) Enable users to understand the impact of modifications.\n    *   (ii) Enable users to identify the products to which a specific modification applies.\n    *   (iii) Provide maintenance personnel with tools for change management so that all activities from specifying requirements to coding can be traced.\n\n#### 3.7.8 Status Accounting\n\n*   **Purpose:** To quantify the properties of the software being developed and the process being used by gathering statistics at the SCM level.\n*   **Primary Purposes:**\n    *   i. Keep formal records of already existing configurations.\n    *   ii. Produce periodic reports about the status of the configurations.\n*   **Record Usage:**\n    *   a. Describe the product correctly.\n    *   b. Are used to verify the configuration of the system for testing and delivery.\n    *   c. Maintain a history of CRs, including both the approved ones and the rejected ones.\n\n#### 3.7.9 Auditing\n\n*   **SCM System Features:**\n    *   (i) Return to prior stable points (Rollback).\n    *   (ii) Determine which alterations were made, why they were made, and who made them.\n*   **Types of Audits (Before Release):**\n    *   **Audit for Functional Configuration:** Determines whether or not the software satisfies both the user and system requirements.\n    *   **Audit for Physical Configuration:** Checks to see if the program is appropriately represented in the reference and design documentation.\n\n### 3.8 SCM Process\n\n#### 3.8.1 Establishing Baselines\n\n*   **Purpose:** To find out if something is subject to configuration management. Only a few instances have code, data, and documentation.\n*   **Software Baseline Library:** After configuration items have been identified, a software baseline library is constructed to make the list of changeable items publicly available.\n*   **Repository:** The library, also known as the repository, is at the heart of the SCM system. All public configurations are saved in the repository, which is a central location. The repository has data on all of the things that have been baselined.\n\n#### 3.8.2 Baseline (or Re-baseline) Process Activities\n\n*   Create a snapshot of the current version of the product and its configuration elements, then assign the complete configuration a configuration identifier.\n*   Check in the configuration and assign version numbers to the configuration objects.\n*   In the repository, store the approved authority information as meta data.\n*   All of the following information should be distributed to all stakeholders.\n*   Create a schema of words, numbers, or letters for common sorts of configuration objects to reliably identify the configuration version. Furthermore, particular nomenclature may be required by project needs.\n\n#### 3.8.3 Controlling, Documenting, and Auditing\n\n*   **Post-Baseline Maintenance:** After establishing a baseline, it is important to maintain consistency between the actual and documented configurations, and ensure that the baseline corresponds with the project\'s requirements document configuration.\n*   **Regular Audits:** At regular intervals, records and products are audited to verify that:\n    *   There is an appropriate match between the documented and actual configurations.\n    *   The configuration complies with the project\'s criteria.\n    *   All change activity documentation is comprehensive and up-to-date.\n*   **Cycle:** Controlling, documenting, and auditing are the three steps in the cycle that are repeated throughout the project\'s life cycle.\n\n### 3.9 SCM Tools - Features\n\n#### 3.9.1 Version Control\n\n*   **Functionality:** One of the key features offered by SCM tools is version control. These tools utilize an archiving method to save every change made to a file, allowing users to revert to a previous version if any issues arise. (Mentor, 2023)\n\n#### 3.9.2 Synchronization\n\n*   **Functionality:** Allows users to check out multiple files or even a complete repository. After working on the necessary files, users can commit their changes back to the repository. Additionally, they can periodically update their local copies to reflect changes made by other team members, which is referred to as synchronization. (Mentor, 2023)\n\n#### 3.9.3 Concurrency Management\n\n*   **Definition:** Concurrency refers to the occurrence of multiple tasks simultaneously. In the context of SCM, it refers to different individuals editing the same file at the same time.\n*   **Importance:** If concurrency is not effectively managed with SCM tools, it can lead to significant issues. (Mentor, 2023)\n\n#### 3.9.4 Automation\n\n*   **Functionality:** SCM tools decisively automate critical tasks such as building, testing, and deploying software. This automation significantly reduces manual effort, boosts efficiency, and minimizes errors.\n*   **Examples:** Jenkins and TeamCity for build automation; Ansible and Chef for automated deployments.\n\n#### 3.9.5 Dependency Mapping\n\n*   **Functionality:** These tools effectively visualize and manage the relationships between different configuration items and services. This capability is essential for understanding the impact of changes and maintaining system health.\n\n### 3.10 SCM Tools – Examples\n\n*   Docker\n*   SolarWinds\n*   Octopus Deploy\n*   PowerShell\n*   ConfigHub\n\n### 3.11 References\n\n*   Blokdyk. G. (2020). Maintenance of software: a complete guide: practical tools for self-assessment. Art of Service.\n*   Blokdyk. G. (2020). Software change management: a complete guide: practical tools for self-assessment. Art of Service.\n*   Maxim. B. R., Pressman. R. S. (2020) Software Engineering: A Practitioner\'s Approach. 9th Edn. McGraw-Hill Education\n*   E. Varga. (2017). Unraveling software maintenance and evolution: thinking outside the box. Springer.\n*   F. Tsui. O. Karam. B. Bernal. (2018). Essentials of Software Engineering. 4th Edition. Jones & Bartlett Learning.\n*   Tripathy, P., Naik K. (2014). Software Evolution and Maintenance – A Practitioner’s Approach. John Wiley and Son. Springer Vieweg.\n*   Mentor, T. (2023, November 26). Features of Software Configuration Management - Software Testing Mentor. Software Testing Mentor. https://www.softwaretestingmentor.com/features-of-scm/', 'user_upload/2/content/e937cb5c-1bb1-463d-b14b-94ede088457d.pdf', '2025-11-12'),
(34, 2, NULL, 'Chapter 4.pptx.pdf', 'pdf', '# BMSE3014 Software Maintenance\n\n## CHAPTER 4: RE-ENGINEERING\n\n### 4.1 Introduction to Re-Engineering\n\nRe-engineering is the examination, analysis, and restructuring of an existing software system to reconstitute it in a new form and the subsequent implementation of the new form.\n\n**Goals of Re-engineering:**\n\n*   Understand the existing software system artifacts, namely, specification, design, implementation, and documentation.\n*   Improve the functionality and quality attributes of the system. Some examples of quality attributes are evolvability, performance, and reusability.\n\n**Risks Involved in Re-engineering:**\n\n*   The target system may not have the same functionality as the existing system.\n*   The target system may be of lower quality than the original system.\n*   The benefits are not realized in the required time frame.\n\n**General Objectives for Software Re-engineering:**\n\n*   Improving maintainability\n*   Migrating to a new technology\n*   Improving quality\n*   Preparing for functional enhancement\n\n### 4.2 Principles of Re-Engineering\n\n#### 4.2.1 Principle of Abstraction\n\nThe level of abstraction of the representation of a system can be gradually increased by successively replacing the details with abstract information. By means of abstraction, one can produce a view that focuses on selected system characteristics by hiding information about other characteristics.\n\n#### 4.2.2 Principle of Refinement\n\nThe level of abstraction of the representation of the system is gradually decreased by successively replacing some aspects of the system with more details.\n\n#### 4.2.3 Principle of Alteration\n\nThe making of some changes to a system representation is known as alteration. Alteration does not involve any change to the degree of abstraction, and it does not involve modification, deletion, and addition of information.\n\n### 4.3 Abstraction and Refinement Levels\n\n#### 4.3.1 Conceptual Level\n\n*   The software is described in terms of very high-level concepts and its reason for existence (why?).\n*   Addresses the “why” aspects of the system, by answering the question: “Why does the system exist?”\n\n#### 4.3.2 Requirements Level\n\n*   Functional characteristics (what?) of the system are described at a high level, while leaving the details out.\n*   Addresses the “what” aspects of the system by answering the question: “What does the system do?”\n\n#### 4.3.3 Design Level\n\n*   System characteristics (what and how?), namely, major components, the architectural style putting the components together, the interfaces among the components, algorithms, major internal data structures, and databases are described in detail.\n*   Addresses more of “what” and “how” aspects of the system by answering the questions: (i) “What are the characteristics of the system?” and (ii) “How is the system going to possess the characteristics to deliver the functionalities?”\n\n#### 4.3.4 Implementation Level\n\n*   The system is described at a very low level in terms of implementation details in a high-level language.\n*   Addresses “how” exactly the system is implemented.\n\n### 4.4 Abstraction and Refinement Processes\n\n*   **Refinement Process:** why? → what? → what and how? → how?\n*   **Abstraction Process:** how? → what and how? → what? → why?\n\n### 4.5 Models of Software Re-Engineering\n\n#### 4.5.1 General Model\n\n*(No specific details provided in the content for the General Model)*\n\n#### 4.5.2 Horse Shoe Model\n\nThe reengineering process is often divided into three stages, as seen in the so-called horseshoe model.\n\n*   **Reverse Engineering:** May be required if the software system\'s technological platform (language, tools, machines, and operating system) has become obsolete, or if the original creators are no longer available.\n*   **Software Reorganization (Software Restructuring):** During this phase, the aim is to improve key components of the system.\n*   **Forward Engineering:** A new running system is designed based on the new, restructured model.\n\n*(Reference: Mens, Tom. (2008). Introduction and Roadmap: History and Challenges of Software Evolution. 10.1007/978-3-540-76440-3_1.)*\n\n#### 4.5.3 Horseshoe Model (Developed by Kazman et al.)\n\n*(No specific details provided in the content for this variation of the Horseshoe Model)*\n\n#### 4.5.4 Reengineering Equation\n\nReengineering = Reverse engineering + Δ + Forward engineering\n\n*   \"Δ\" captures alterations made to the original system.\n*   **Two major dimensions of alteration are:**\n    *   Change in functionality\n    *   Change in implementation technique\n\n### 4.6 Changes on Characteristics of System\n\n#### 4.6.1 Recode\n\n*   The source program\'s implementation properties are altered by recoding it.\n*   Rephrasing and program translation are used to make changes at the source code level.\n\n#### 4.6.2 Redesign\n\n*   Common changes to the software design include:\n    *   Restructuring the architecture.\n    *   Modifying the data model of the system.\n    *   Replacing a procedure or an algorithm with a more efficient one.\n\n#### 4.6.3 Respecify\n\n*   This entails modifying the system\'s required characteristics in two ways:\n    *   Changing the requirements\' form.\n    *   Changing the requirements\' scope.\n\n#### 4.6.4 Rethink\n\n*   Rethinking a system entails changing the concepts embodied in an existing system in order to build a system that solves a different challenge.\n*   It entails altering the system\'s conceptual properties, and it can result in the system being fundamentally altered.\n\n### 4.7 Software Re-Engineering Strategies\n\n#### 4.7.1 Rewrite Strategy\n\n*   This strategy reflects the principle of alteration.\n*   An operational system is turned into a new system while maintaining the previous system\'s abstraction level.\n*   **Example:** The Fortran code of a system is rewritten using C language.\n\n#### 4.7.2 Rework Strategy\n\n*   The rework strategy applies all three principles (abstraction, alteration, refinement).\n*   **Steps:**\n    1.  Use the principle of abstraction to create a system representation that contains fewer details than what is currently available at a particular level.\n    2.  Alter the rebuilt system model to create the desired system representation without modifying the abstraction level.\n    3.  At a lower level of abstraction, refinement is used to produce an acceptable new system representation.\n\n#### 4.7.3 Replace Strategy\n\n*   Applies two principles: abstraction and refinement.\n*   To change a certain characteristic of a system:\n    *   By hiding the details of the characteristic, the system is reconstructed at a higher level of abstraction.\n    *   By applying refinement, an appropriate representation for the target system is formed at a lower degree of abstraction.\n\n### 4.8 Reengineering Process Variation\n\n*(Reference: E. J. Byrne, \"A conceptual foundation for software re-engineering,\" Proceedings Conference on Software Maintenance 1992, Orlando, FL, USA, 1992, pp. 226-235.)*\n\n**Homework:** Read and understand the section “Reengineering process variation” from the journal linked in the original content.\n\n**Possible Outcomes of Reengineering Process Variation:**\n\n*   **Yes:** One can produce a target system.\n*   **Yes\\*:** Same as Yes, but the starting degree of abstraction is lower than the uppermost degree of abstraction within the conceptual abstraction level.\n*   **No:** One cannot start at abstraction level A, make B type of changes by using strategy C, because the starting abstraction level is higher than the abstraction level required by the particular type of change.\n*   **Bad:** A target system can be created, but the likelihood of achieving a good result is low.\n\n### 4.9 Re-Engineering Approaches\n\nTwo aspects are considered:\n\n*   The extent of reengineering performed.\n*   The rate of substitution of the operational system with the new one.\n\n#### 4.9.1 Big Bang Approach\n\n*   Replaces the entire system at the same time.\n*   Once a reengineering project is started, it is continued until all of the project\'s objectives are met and the target system is built.\n*   Usually adopted if part-by-part reengineering isn\'t possible.\n\n#### 4.9.2 Incremental Approach\n\n*   A system is reengineered gradually, one step closer to the target system at a time.\n*   **Benefits:**\n    *   Detecting mistakes becomes easy as newly added components can be easily identified.\n    *   It is easier for the client to detect progress as intermediate versions are issued.\n    *   Less risky than the \"Big Bang\" method.\n*   **Disadvantages:**\n    *   Takes much longer to complete due to multiple interim versions and careful version controls.\n    *   The entire architecture of the system cannot be changed even if there is a need.\n\n#### 4.9.3 Partial Approach\n\n*   Only a part of the system is reengineered and then it is integrated with the non-engineered portion of the system.\n*   **Consists of three steps:**\n    1.  The existing system is partitioned into two pieces: one part to be reengineered, and the remaining part not to be.\n    2.  Either the \"Big Bang\" or the \"Incremental\" approach is used to do reengineering work.\n    3.  The two components of the system (the not-to-be-reengineered part and the reengineered part) are combined to form the new system.\n\n#### 4.9.4 Iterative Approach\n\n*   The reengineering process is carried out on the source code of a few procedures at a time, with each operation lasting only a few minutes.\n*   This procedure is repeated on several components at different stages.\n\n#### 4.9.5 Evolutionary Approach\n\n*   Similar to the \"Incremental\" approach, components of the original system are substituted with reengineered components.\n*   Existing components are grouped by functions and reengineered into new components.\n\n### 4.10 Source Code Reengineering Reference Model (SCORE/RM)\n\n*(For detailed explanation, refer to the attached notes.)*\n\n*   **4 Elements:**\n    *   Functions\n    *   Database repository\n    *   Documentation\n    *   Metrication\n*   **An approach to:**\n    *   Understanding the needs of the software.\n    *   Understanding how to rationalize the system to be reengineered by eliminating redundant data and changing the control flow.\n    *   Understanding how to rebuild the software in accordance with best practices.\n\n*(Content Source: A. Colbrook, C. Smythe and A. Darlison, \"Data abstraction in a software re-engineering reference model,\" Proceedings. Conference on Software Maintenance 1990, San Diego, CA, USA, 1990, pp. 2-11, doi: 10.1109/ICSM.1990.131314.)*\n\n#### 4.10.1 SCORE-RM Processes\n\n*   **Original Source Code**\n    *   Analysis\n    *   Parsing\n    *   Rationalization of Control Flow\n    *   Normalization\n    *   Data Compression\n    *   Representation of Data (Life History of Data)\n    *   Interpretation\n    *   Functionalization (Remove global variable, introduce recursion and polymorphic)\n    *   Programming Reading (Add comments)\n    *   Transformation\n    *   Test Case Generation\n    *   Isolation (External interface)\n    *   Management of Configuration\n    *   Encapsulation\n*   **Transformed Source Code**\n    *   Abstraction\n        *   Identify Object Hierarchies\n        *   Separate operator from data\n        *   Group operators\n        *   Interpretation of object\n        *   Causation\n        *   Specify action that receive by users\n        *   Specify software constraints\n    *   Regeneration\n        *   Generate detailed design, create codes\n        *   Generate test cases (unit and integration test)\n    *   Certification\n        *   Examine software is safe to use.\n    *   Conformance\n        *   Test s/w can perform at least existing functions.\n\n### 4.11 Code Reverse Engineering\n\n#### 4.11.1 Reverse Engineering Definition\n\nA process to:\n\n*   Define the components of operational software.\n*   Define the interactions between those components.\n*   Represent the system at a higher degree of abstraction or in another form. In other words, reverse engineering extracts information from current software artifacts and converts it into abstract models that maintenance people can understand.\n\n#### 4.11.2 Factors Necessitating Reverse Engineering:\n\n*   The original programmers have left the organization.\n*   The language of implementation has become obsolete, and the system needs to be migrated to a newer one.\n*   There is insufficient documentation of the system.\n*   The business relies on software, which many cannot understand.\n*   The company acquired the system as part of a larger acquisition and lacks access to all the source code.\n*   The system requires adaptations and/or enhancements.\n*   The software does not operate as expected.\n\n#### 4.11.3 Reverse Engineering Techniques\n\n*   Lexical Analysis\n*   Syntactic Analysis\n*   Control Flow Analysis\n*   Data Flow Analysis\n*   Program Slicing\n*   Visualization\n*   Program Metrics\n\n#### 4.11.4 Lexical Analysis\n\n*   The process of breaking down a source code sequence of characters into its constituent lexical units.\n*   Allows for a variety of useful representations of program data.\n*   The cross-reference list is probably the most commonly utilized program information.\n*   A lexical analyzer is a program that does lexical analysis and is part of the compiler for a programming language.\n\n#### 4.11.5 Syntactic Analysis\n\n*   Performed by a parser.\n*   Context-free grammars are a mathematical framework that expresses the required language features.\n*   Backus–Naur Form (BNF) is a notation used to describe these grammars. The various program sections of the BNF notation are defined by rules in terms of their constituents.\n*   Parsers, like syntactic analyzers, can be built automatically from a description of a programming language\'s programmatic features.\n*   YACC (Yet Another Compiler Compiler) is an example of parsing tools.\n\n#### 4.11.6 BNF Example\n\n*(Image/Diagram of BNF Example would be placed here if available in original content)*\n\n#### 4.11.7 Control Flow Analysis\n\n*   **Two types:**\n    *   Intra-procedural analysis\n    *   Inter-procedural analysis\n\n#### 4.11.8 Intra-procedural Analysis\n\n*   Shows the order in which statements are executed within a subprogram.\n*   Performed by generating Control Flow Graphs (CFGs) of subprograms.\n\n#### 4.11.9 Inter-procedural Analysis\n\n*   Performed by constructing a call graph.\n*   Calling relationships between subroutines in a program are represented as a call graph, which is basically a directed graph.\n\n#### 4.11.10 Data Flow Analysis (DFA)\n\n*   Concerns how values of defined variables flow through and are used in a program.\n*   Can detect the possibility of loops.\n*   DFA enables the identification of code that can never execute, variables that might not be defined before they are used, and statements that might have to be altered when a bug is fixed.\n\n#### 4.11.11 Program Slicing\n\n*   A program slice is a portion of a program with an execution behavior identical to the initial program with respect to a given criterion but may have a reduced size.\n*   **Backward Slice:** With respect to a variable `v` and a given point `p`, comprises all instructions and predicates which affect the value of `v` at point `p`. Answers the question “What program components might affect a selected computation?”\n*   **Forward Slicing:** Comprises all the instructions and predicates which may depend on the value of `v` at `p`. Answers the question “What program components might be effected by a selected computation?”\n\n### 4.12 References\n\n1.  E. Varga. (2017). *Unraveling software maintenance and evolution: thinking outside the box*. Springer.\n2.  F. Tsui. O. Karam. B. Bernal. (2018). *Essentials of Software Engineering*. 4th Edition. Jones & Bartlett Learning.\n3.  Tripathy, P., Naik K. (2014). *Software Evolution and Maintenance – A Practitioner’s Approach*. John Wiley and Son. Springer Vieweg.\n\n---\n**Continue Next Chapter……**', 'user_upload/2/content/fd5d58a9-782c-4c68-aaf3-db2a3ab800d0.pdf', '2025-11-12');

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
(13, 2, NULL, 'Software Maintenace', NULL);

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
(29, 13, '\"# BMSE3014 Software Maintenance\\n\\n## Introduction to Course Structure\\n### Coursework – 60%\\n#### Group Assignment (question delivered week 1, submission week 5) - 60%\\n#### Test (week 3) – 40%\\n### Exam – 40%\\n\\n## Software Evolution - Basic Concepts\\n### Understanding Evolution\\n#### Crucial for adapting to new user demands and environmental changes.\\n#### Complexity increases unless actively managed; feedback is vital.\\n#### Adhering to sound maintenance processes supports successful growth.\\n\\n## Software Systems Evolution\\n### Definition\\n#### Ongoing enhancement after initial release to meet evolving needs.\\n### Goals\\n#### Supporting additional functionalities.\\n#### Improving system performance.\\n#### Allowing operation on different operating systems.\\n### Core Idea\\n#### Stakeholders and users gain a better understanding over time.\\n### Quote\\n#### *\\\"Over time, what evolves is not the software but our knowledge about a particular type of software\\\"* - Mehdi Jazayeri\\n\\n## Importance of Software Evolution\\n### Relevance & Competitiveness\\n### User Experience Enhancement\\n### Usability & Functionality Increase\\n### Stability & Security Fortification\\n\\n## Software Sustainment\\n### Challenges\\n#### Modern systems\' growing dependence on software.\\n#### Overlooking challenges jeopardizes stability, improvement, and lifespan.\\n### Life-Cycle Sustainment Factors\\n#### Supply\\n#### Maintenance\\n#### Transportation\\n#### Sustaining Engineering\\n#### Data Management\\n#### Configuration Management\\n#### Human Systems Integration (HSI)\\n#### Environmental and Occupational Health and Safety\\n#### Protection of Essential Program Information and Anti-Tamper Measures\\n#### Supportability\\n#### Interoperability\\n\\n### Definition - Software Sustainment\\n#### *\\\"The processes, procedures, people, material, and information required to support, maintain, and operate the software aspects of a system.\\\"* - SEI Working Definition\\n\\n### Criteria to Enter Software Sustainment (Lapham & Sei, 2014)\\n#### Stable software production baseline.\\n#### Complete and current software documentation.\\n#### Authority to Operate (ATO) for an operational system.\\n#### Current and negotiated Sustainment Transition Plan.\\n#### Sustainment staffing and training plan.\\n\\n### Challenges of Sustainment (Lapham & Sei, 2014)\\n#### Sustaining COTS software (obsolescence, technology updates, licensing).\\n#### Programmatic considerations (categorizing sustainment needs).\\n#### Transition to sustainment phase (database migration, infrastructure, workforce, training).\\n#### User support (help desk, manuals, training).\\n#### Information assurance (IA) for COTS products.\\n\\n## Software Maintenance - Basic Concepts\\n### Definition\\n#### Essential for long-term viability throughout the lifecycle.\\n### Process\\n#### Document changes.\\n#### Discover impacts.\\n#### Modify artifacts.\\n#### Perform testing.\\n#### Prepare new release.\\n#### Educate users and provide assistance.\\n### Maintainer\\n#### Company or organization providing maintenance work.\\n\\n### Basic Concepts\\n#### Aims to update and improve existing software efficiently while maintaining integrity.\\n#### Ensures continued function, adaptation to needs, and issue resolution.\\n#### Extends useful life and postpones replacement.\\n#### Uses software engineering principles for systematic updates.\\n#### Rigorous change management and regression testing prevent quality decline.\\n#### Offers ongoing user support, training, and satisfaction monitoring.\\n\\n### Cost Considerations\\n#### Maintenance costs increase significantly in later lifecycle stages.\\n#### Disciplined change control and testing mitigate degradation.\\n#### Impact analysis is essential to limit unintended consequences.\\n#### Designing maintainability into software makes modifications manageable.\\n#### Maintenance cost estimation should begin early, refined with project progression.\\n#### Most expenditures are on enhancements rather than repairs.\\n\\n### Definition – Software Maintenance\\n#### *\\\"The process of modifying a software system or component after delivery to correct faults, improve performance or other attributes, or adapt to a changed environment.\\\"* - IEEE Standard Glossary of Software Engineering Terminology\\n\\n### Reasons of Maintenance\\n#### Prevent software operation failure.\\n#### Fix discovered software bugs.\\n#### Improve software (e.g., link to new devices).\\n#### Ensure software works in different environments.\\n#### Provide better user experiences.\\n#### Comply with organizational goal changes.\\n\\n### Software Development vs Software Maintenance\\n#### **SOFTWARE DEVELOPMENT**\\n##### Requirements driven.\\n##### Begins with designing and implementing a system.\\n##### Primary\\/First Implementation.\\n##### Development from new.\\n##### Deliver new software.\\n#### **SOFTWARE MAINTENANCE**\\n##### Event driven.\\n##### Scheduled in response to an event.\\n##### On-going administration\\/support.\\n##### Modifying or adding features to existing software.\\n##### Prevent software from failure.\\n\\n## DevOps\\n### Definition\\n#### Software development methodology streamlining delivery by integrating and automating Dev and Ops teams.\\n#### Promotes collaboration and coordination.\\n### Key Features\\n#### Continuous Integration and Continuous Delivery (CI\\/CD).\\n#### Smaller, faster software updates through frequent merging, testing, and deployment.\\n#### Evolves from agile software development.\\n#### Focuses on meeting user demands for innovative features and consistent performance.\\n\\n### DevOps Life-Cycle\\n#### **Planning**\\n##### Identify new features and functions.\\n##### Use user feedback and stakeholder insights.\\n##### Create a prioritized backlog.\\n#### **Coding**\\n##### Develop features from the backlog.\\n##### Use practices like TDD, pair programming, code reviews.\\n##### Code written and tested locally before pipeline.\\n#### **Building**\\n##### Integrate new code into existing base.\\n##### Test and package for release.\\n##### Automation for merging, storing, compiling.\\n##### Outputs saved in binary repository.\\n#### **Testing**\\n##### Automated testing throughout development.\\n##### Unit tests during coding, linting after integration.\\n##### Early detection of issues (\\\"shift-left\\\" approach).\\n#### **Release**\\n##### Thorough testing before user access.\\n##### Ensure quality and security standards.\\n##### Correct defects before production launch.\\n##### Often uses automated processes.\\n#### **Deploy**\\n##### Updated application moves to production.\\n##### Initially deployed to a subset of users.\\n##### Verify stability before full rollout.\\n#### **Operate**\\n##### Ensure new features function correctly without interruptions.\\n##### Employ automated tools for continuous monitoring and optimization.\\n#### **Monitor**\\n##### Collect feedback and insights.\\n##### Improve future processes.\\n##### Inform planning for next release.\\n\\n### DevOps Culture\\n#### Emphasizes collaboration, communication, and automation among all stakeholders.\\n#### Requires continuous interaction and shared responsibility.\\n#### Leverages automated tools for testing, deployment, and infrastructure.\\n#### Feedback and measurement are essential for optimization.\\n#### Dismantle silos and form cross-functional teams.\\n#### Promotes accountability and collaboration.\\n\\n### DevOps and Maintenance\\n#### Makes software maintenance appealing again.\\n#### Offers better organizational support and enhances communication.\\n#### \\\"You build it, you own it\\\" philosophy fosters developer responsibility.\\n#### Leads to increased collaboration between developers and IT operations.\\n#### Developers actively involved in resolving production issues and upgrades.\\n\\n#### Conversations about maintenance tasks remain infrequent.\\n#### Coding continues to be celebrated as the most exciting aspect.\\n#### Hasn\'t made maintenance itself a more attractive job aspect.\\n#### Software maintenance is critical for ensuring a smooth application experience.\\n#### This recognition should be explicitly integrated into the DevOps approach.\\n\\n## References\\n### Books\\n#### Blokdyk. G. (2020). Maintenance of software : a complete guide : practical tools for self-assessment. Art of Service.\\n#### Blokdyk. G. (2020). Software change management : a complete guide : practical tools for self-assessment. Art of Service.\\n#### Maxim. B. R., Pressman. R. S. (2020) Software Engineering: A Practitioner\'s Approach. 9th Edn. McGraw-Hill Education.\\n#### E. Varga. (2017). Unraveling software maintenance and evolution : thinking outside the box. Springer.\\n#### F. Tsui. O. Karam. B. Bernal. (2018). Essentials of Software Engineering. 4th Edition. Jones & Bartlett Learning.\\n#### Tripathy, P., Naik K. (2014). Software Evolution and Maintenance – A Practitioner’s Approach. John Wiley and Son. Springer Vieweg.\\n\\n### Papers & Articles\\n#### Cook, S. , Harrison, R. , Lehman, M.M. , Wernick, P. , 2006. Evolution in software systems: foundations of the spe classification scheme. J. Softw. Maintenance 18 (1), 1–35.\\n#### T. Mens, M. Wermelinger, S. Ducasse, S. Demeyer, R. Hirschfeld and M. Jazayeri, \\\"Challenges in software evolution,\\\" Eighth International Workshop on Principles of Software Evolution (IWPSE\'05), 2005, pp. 13-22, doi: 10.1109\\/IWPSE.2005.7.\\n#### Lapham, M., & Sei. (2014). LEGACY SYSTEM SOFTWARE SUSTAINMENT. https:\\/\\/apps.dtic.mil\\/sti\\/tr\\/pdf\\/ADA591337.pdf\\n\\n### Online Resources\\n#### http:\\/\\/swebokwiki.org\\/Chapter_5:_Software_Maintenance\\n#### https:\\/\\/qarea.com\\/blog\\/what-is-software-evolution-and-maintenance\\n#### https:\\/\\/www.ibm.com\\/think\\/topics\\/devops\\n#### https:\\/\\/devops.com\\/making-software-maintenance-cool-devops\\/\"', 'Software Maintenance & DevOps', NULL, '2025-11-11 00:02:42'),
(30, 14, '\"# BMSE3014 Software Maintenance\\n\\n## Introduction to Course Structure\\n### Coursework – 60%\\n#### Group Assignment (Week 1 question, Week 5 submission) - 60%\\n#### Test (Week 3) – 40%\\n### Exam – 40%\\n\\n## Software Evolution - Basic Concepts\\n### Understanding Evolution\\n#### Crucial for adapting to new user demands and environmental changes.\\n#### Complexity increases unless actively managed; feedback is vital.\\n#### Adhering to sound maintenance processes supports successful growth.\\n\\n## Software Systems Evolution\\n### Definition\\n#### Ongoing enhancement after initial release to meet evolving needs.\\n### Goals\\n#### Supporting additional functionalities.\\n#### Improving system performance.\\n#### Allowing operation on different operating systems.\\n### Core Idea\\n#### Stakeholders and users gain better understanding over time.\\n### Quote\\n#### *\\\"Over time, what evolves is not the software but our knowledge about a particular type of software\\\"* - Mehdi Jazayeri\\n\\n## Importance of Software Evolution\\n### Relevance & Competitiveness\\n#### Ensuring the software product remains relevant and competitive.\\n### User Experience\\n#### Enhances the overall user experience.\\n### Usability & Functionality\\n#### Regular upgrades increase usability and functionality, making software more efficient.\\n### Stability & Security\\n#### System becomes increasingly stable, reducing security risks and fortifying against cyberattacks.\\n### Source\\n#### https:\\/\\/qarea.com\\/blog\\/what-is-software-evolution-and-maintenance\\n\\n## Software Sustainment\\n### Challenges\\n#### Modern systems\' dependence on software complicates sustainment.\\n#### Overlooking challenges jeopardizes stability, improvement, and lifespan.\\n### Life-Cycle Sustainment Factors\\n#### Supply\\n#### Maintenance\\n#### Transportation\\n#### Sustaining Engineering\\n#### Data Management\\n#### Configuration Management\\n#### Human Systems Integration (HSI)\\n#### Environmental and Occupational Health and Safety\\n#### Protection of Essential Program Information and Anti-Tamper Measures\\n#### Supportability\\n#### Interoperability\\n\\n## Definition - Software Sustainment\\n### SEI Working Definition\\n#### *\\\"The processes, procedures, people, material, and information required to support, maintain, and operate the software aspects of a system.\\\"*\\n\\n## Criteria to Enter Software Sustainment (Lapham & Sei, 2014)\\n### Stable Software Production Baseline\\n#### Sustainment organizations require stable software before accepting it.\\n### Complete and Current Software Documentation\\n#### Thorough and up-to-date documentation is essential.\\n### Authority to Operate (ATO)\\n#### Granted before a software system is deemed operational and eligible for sustainment.\\n### Current and Negotiated Sustainment Transition Plan\\n#### Crucial for planned transition to sustainment.\\n### Sustainment Staffing and Training Plan\\n#### Properly staffed organization with trained professionals.\\n\\n## Challenges of Sustainment (Lapham & Sei, 2014)\\n### COTS Software\\n#### System obsolescence, technology updates, source code escrow, vendor license management.\\n### Programmatic Considerations\\n#### Categorizing sustainment needs as \\\"minor requirements.\\\"\\n### Transition to Sustainment\\n#### Support database migration, development\\/support infrastructure, workforce needs, training, transition planning.\\n### User Support\\n#### Help desk services, user manuals, user training.\\n### Information Assurance (IA)\\n#### Specific challenges for IA in COTS products and testing.\\n\\n## Software Maintenance - Basic Concepts\\n### Core Purpose\\n#### Essential for long-term viability throughout the software lifecycle.\\n### Process\\n#### Document changes, discover impacts, modify artifacts, perform testing, prepare new release.\\n### Support\\n#### Users are educated, and assistance is available at all times.\\n### Maintainer\\n#### Company or organization providing maintenance work.\\n### Source\\n#### http:\\/\\/swebokwiki.org\\/Chapter_5:_Software_Maintenance\\n\\n## Basic Concepts (Continued)\\n### Objectives\\n#### Update and improve existing software efficiently while maintaining integrity.\\n#### Ensure continued function, adaptation to user needs, and issue resolution.\\n#### Extend useful life and postpone replacement.\\n### Methodology\\n#### Maintenance engineering uses established software engineering principles.\\n#### Rigorous change management and regression testing prevent quality decline.\\n#### Ongoing user support, training, and satisfaction monitoring.\\n\\n## Basic Concepts (Cost & Design)\\n### Cost Trends\\n#### Maintenance costs increase significantly in later life cycle stages.\\n### Change Management\\n#### Disciplined change control and testing mitigate system degradation.\\n#### Impact analysis is essential to limit unintended consequences.\\n### Design for Maintainability\\n#### Designing maintainability into software makes modifications manageable.\\n### Cost Estimation\\n#### Begin early in development planning using historical data.\\n#### Refine estimates with project progression.\\n#### Most expenditures on enhancements rather than repairs.\\n\\n## Definition – Software Maintenance\\n### IEEE Standard Glossary of Software Engineering Terminology\\n#### *\\\"The process of modifying a software system or component after delivery to correct faults, improve performance or other attributes, or adapt to a changed environment.\\\"*\\n\\n## Reasons for Maintenance\\n### Prevent Failure\\n#### Prevent software operation from failure.\\n### Fix Bugs\\n#### Fix discovered software bugs.\\n### Improve Software\\n#### Improve software (e.g., to link or access new devices).\\n### Adapt Environment\\n#### Ensure software works in different environments (if required).\\n### Enhance User Experience\\n#### Provide better experiences to users.\\n### Comply with Goals\\n#### Comply with organizational goals (if changes).\\n\\n## Software Development vs Software Maintenance\\n### Software Development\\n#### Requirements driven.\\n#### Process begins with designing and implementing a system.\\n#### Primary\\/First Implementation.\\n#### Development of software from new.\\n#### Deliver new software.\\n### Software Maintenance\\n#### Event driven.\\n#### Scheduled in response to an event.\\n#### On-going administration\\/support.\\n#### Modifying or adding new features based on existing software.\\n#### May include developing software.\\n#### Prevent software from failure.\\n\\n## DevOps\\n### Definition\\n#### Software development methodology streamlining delivery by integrating and automating Dev and Ops teams.\\n### Key Features\\n#### Continuous Integration and Continuous Delivery (CI\\/CD).\\n#### Smaller, faster software updates through frequent merging, testing, and deployment.\\n#### Evolves from agile software development.\\n### Ultimate Goal\\n#### Meeting user demands for innovative features and consistent performance.\\n### Source\\n#### -IBM\\n\\n## DevOps Life-Cycle\\n### Planning\\n#### Identify new features, using user feedback and stakeholder insights.\\n#### Create a prioritized backlog of features, improvements, and bug fixes.\\n### Coding\\n#### Develop features using practices like TDD, pair programming, peer code reviews.\\n#### Code written and tested locally before pipeline advancement.\\n### Source\\n#### https:\\/\\/www.ibm.com\\/think\\/topics\\/devops\\n\\n## DevOps Life-Cycle (Continued)\\n### Building\\n#### Integrate new code, test, and package for release.\\n#### Automation for merging, storing, and compiling code.\\n#### Outputs saved in a binary repository.\\n### Testing\\n#### Automated testing throughout the process (unit tests, linting).\\n#### Early detection of issues (\\\"shift-left\\\" approach).\\n### Source\\n#### https:\\/\\/www.ibm.com\\/think\\/topics\\/devops\\n\\n## DevOps Life-Cycle (Continued)\\n### Release\\n#### Thorough testing to ensure quality and security standards.\\n#### Defects corrected before launch into production.\\n#### Often uses automated processes.\\n### Deploy\\n#### Updated application moves to a production environment.\\n#### Initially deployed to a subset of users for stability verification.\\n### Source\\n#### https:\\/\\/www.ibm.com\\/think\\/topics\\/devops\\n\\n## DevOps Life-Cycle (Continued)\\n### Operate\\n#### Ensure new features function correctly with no service interruptions.\\n#### Employ automated tools for continuous monitoring and optimization.\\n### Monitor\\n#### Collect feedback from users and previous workflows.\\n#### Improve future processes and inform planning for next releases.\\n### Source\\n#### https:\\/\\/www.ibm.com\\/think\\/topics\\/devops\\n\\n## DevOps Culture\\n### Emphasis\\n#### Collaboration, communication, and automation among all stakeholders (Dev, Ops, Compliance, Security).\\n### Requirements\\n#### Continuous interaction and shared responsibility to foster innovation and prioritize quality.\\n### Technical Aspects\\n#### Leverages automated tools for testing, deployment, and infrastructure provisioning.\\n### Implementation\\n#### Dismantle silos, form cross-functional teams, promote accountability and collaboration.\\n### Source\\n#### https:\\/\\/www.ibm.com\\/think\\/topics\\/devops\\n\\n## DevOps and Maintenance\\n### Impact\\n#### Making software maintenance appealing again.\\n#### Better organizational support and enhanced communication between Dev and Ops.\\n### \\\"You Build It, You Own It\\\"\\n#### Developers responsible for deployment and ongoing maintenance.\\n#### Fosters increased collaboration.\\n### Source\\n#### https:\\/\\/devops.com\\/making-software-maintenance-cool-devops\\/\\n\\n## DevOps and Maintenance (Continued)\\n### Perception Challenge\\n#### Coding is celebrated; maintenance tasks remain infrequent in conversations.\\n#### Maintenance not yet a more attractive aspect of the job.\\n### Evolving Perception\\n#### Software maintenance (updates, monitoring, scaling) is critical for application experience.\\n#### This recognition should be integrated into the DevOps approach.\\n### Source\\n#### https:\\/\\/devops.com\\/making-software-maintenance-cool-devops\\/\\n\\n## References\\n### Books\\n#### Blokdyk. G. (2020). Maintenance of software : a complete guide : practical tools for self-assessment. Art of Service.\\n#### Blokdyk. G. (2020). Software change management : a complete guide : practical tools for self-assessment. Art of Service.\\n#### Maxim. B. R., Pressman. R. S. (2020) Software Engineering: A Practitioner\'s Approach. 9th Edn. McGraw-Hill Education\\n### Other References\\n#### E\"', 'Software Maintenance and DevOps', NULL, '2025-11-11 09:52:49');

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

--
-- Dumping data for table `password_reset`
--

INSERT INTO `password_reset` (`resetID`, `userID`, `token`, `email`, `expiresAt`, `usedAt`, `createdAt`) VALUES
(1, 1, 'f6ddece03c328f59b3ca341819aa309f9939ca524316a444a8a502f2eda9e405', 'mantik3333@gmail.com', '2025-11-11 11:17:25', NULL, '2025-11-11 17:17:25'),
(2, 1, 'fa54648ff5dc4ddb72ffc9504fb6b9902a5e570c6eed5ad9a10ba92814280c55', 'mantik3333@gmail.com', '2025-11-11 11:18:39', NULL, '2025-11-11 17:18:39'),
(3, 1, '7a323f79fee6fb9c2f4aa7652d926628185f706f8443a3eb634cfc5f669b3d17', 'mantik3333@gmail.com', '2025-11-11 11:23:36', NULL, '2025-11-11 17:23:36'),
(4, 1, '04b17c00398ade519f96ee651babf22dc65c725526587948bb49cf18a4e47a94', 'mantik3333@gmail.com', '2025-11-11 11:25:16', NULL, '2025-11-11 17:25:16'),
(5, 1, 'f451c09742b749ea3eac17a087624904a1be1fce3beb93d2903ac015125135e7', 'mantik3333@gmail.com', '2025-11-11 11:26:56', NULL, '2025-11-11 17:26:56'),
(6, 1, 'b29b836361332c71a8f646d1ba22624a9f2cf3ede7027a1bca8dbcfc547a5a24', 'mantik3333@gmail.com', '2025-11-11 11:30:59', NULL, '2025-11-11 17:30:59'),
(7, 1, 'd1004e4c32880c9d8bff7d5b662b8140256271410da25f267ac1df0f0339dd28', 'mantik3333@gmail.com', '2025-11-11 11:31:06', NULL, '2025-11-11 17:31:06'),
(8, 1, '505b488596086b103420b55569fb7e7b6caa6d0c6751e24da0dca09ea8bc7b53', 'mantik3333@gmail.com', '2025-11-11 11:31:16', NULL, '2025-11-11 17:31:16'),
(9, 1, 'b67ef33a39501631deef57ceb3f706b98cfa59b32976f851fbf3574d288abe2c', 'mantik3333@gmail.com', '2025-11-11 11:31:50', NULL, '2025-11-11 17:31:50'),
(10, 1, '89c3558ca25b640725ee00a0f9a43fe9bdda31d3e9c2c4f886f7a2a2a48e2319', 'mantik3333@gmail.com', '2025-11-11 11:31:54', NULL, '2025-11-11 17:31:54'),
(11, 1, '458bfb4e8d190892889a4d62683ff18d80d76aaddf9ae51509559af1ca22ef63', 'mantik3333@gmail.com', '2025-11-11 11:32:52', NULL, '2025-11-11 17:32:52'),
(12, 1, 'd1b8d7e90c2ed1b8a78fe547856e5768b7d0e7bd8768b4dd738569825f2d7cc3', 'mantik3333@gmail.com', '2025-11-11 11:38:31', NULL, '2025-11-11 17:38:31'),
(13, 1, '8187e0687ef20de546302c988980424346113aac6e9a34217b307ffa8afd4baa', 'mantik3333@gmail.com', '2025-11-11 11:43:49', NULL, '2025-11-11 17:43:49'),
(14, 1, '881fa223e7d9b97d7cfb72fec2b6ba679d93105f84ee55e6b92872f37151df22', 'mantik3333@gmail.com', '2025-11-11 11:46:56', '2025-11-11 17:47:35', '2025-11-11 17:46:56');

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
(17, 2, 'What summarisation in simple terms', '2025-11-10 00:16:29');

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
(12, 17, 'Summarization is the process of creating a shorter version of a text while keeping its main points. This study looks at how the length of a summary (how much it\'s shortened) affects the emotional tone or sentiment of the original text. It investigates whether making summaries shorter can change the sentiment (like making a positive review sound negative) and compares different methods of summarization to see which ones preserve sentiment best.', '2025-11-10 00:16:30');

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
(1, 'mantik3333@gmail.com', '$2y$10$Txo2pCQHsYpDUohdOvwqm.AN79/e6S4JPG56i2xM86oYPspuH9cqi', 'Yeoh Man Tik', NULL, 'TRUE'),
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
-- Indexes for dumped tables
--

--
-- Indexes for table `chatbot`
--
ALTER TABLE `chatbot`
  ADD PRIMARY KEY (`chatbotID`),
  ADD KEY `fileID` (`fileID`);

--
-- Indexes for table `documentchunks`
--
ALTER TABLE `documentchunks`
  ADD PRIMARY KEY (`documentChunkID`);

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
  MODIFY `chatbotID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `documentchunks`
--
ALTER TABLE `documentchunks`
  MODIFY `documentChunkID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=668;

--
-- AUTO_INCREMENT for table `file`
--
ALTER TABLE `file`
  MODIFY `fileID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `flashcard`
--
ALTER TABLE `flashcard`
  MODIFY `flashcardID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `folder`
--
ALTER TABLE `folder`
  MODIFY `folderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `mindmap`
--
ALTER TABLE `mindmap`
  MODIFY `mindmapID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `note`
--
ALTER TABLE `note`
  MODIFY `noteID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `password_reset`
--
ALTER TABLE `password_reset`
  MODIFY `resetID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `question`
--
ALTER TABLE `question`
  MODIFY `questionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `questionchat`
--
ALTER TABLE `questionchat`
  MODIFY `questionChatID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `quizID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `responsechat`
--
ALTER TABLE `responsechat`
  MODIFY `responseChatID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `summary`
--
ALTER TABLE `summary`
  MODIFY `summaryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

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
