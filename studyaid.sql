-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 09, 2025 at 04:52 PM
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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `questionchat`
--
ALTER TABLE `questionchat`
  ADD PRIMARY KEY (`questionChatID`),
  ADD KEY `chatBotID` (`chatbotID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `questionchat`
--
ALTER TABLE `questionchat`
  MODIFY `questionChatID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `questionchat`
--
ALTER TABLE `questionchat`
  ADD CONSTRAINT `questionchat_ibfk_1` FOREIGN KEY (`chatBotID`) REFERENCES `chatbot` (`chatBotID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
