-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Generation Time: Feb 21, 2025 at 03:24 AM
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
-- Database: `library_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `action_description` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `user_id`, `action_type`, `action_description`, `ip_address`, `timestamp`) VALUES
(99, 9, 'logout', 'User logged out', '::1', '2024-12-09 13:06:10'),
(100, 10, 'logout', 'User logged out', '::1', '2024-12-09 13:19:56'),
(101, 10, 'login', 'User logged in successfully', '::1', '2024-12-09 13:22:54'),
(102, 10, 'logout', 'User logged out', '::1', '2024-12-09 13:22:59'),
(103, 10, 'login', 'User logged in successfully', '::1', '2024-12-10 04:30:13'),
(104, 9, 'login', 'User logged in successfully', '::1', '2024-12-10 04:30:41'),
(105, 9, 'logout', 'User logged out', '::1', '2024-12-10 06:25:06'),
(106, 9, 'login', 'User logged in successfully', '::1', '2024-12-10 06:25:20'),
(107, 10, 'update', 'Account details updated for user: staff', '::1', '2024-12-10 06:38:12'),
(108, 9, 'logout', 'User logged out', '::1', '2024-12-10 06:38:16'),
(109, NULL, 'login', 'User logged in successfully', '::1', '2024-12-10 06:38:27'),
(110, NULL, 'logout', 'User logged out', '::1', '2024-12-10 06:46:33'),
(111, 9, 'login', 'User logged in successfully', '::1', '2024-12-10 06:48:28'),
(112, 9, 'login', 'User logged in successfully', '::1', '2024-12-12 06:49:06'),
(113, 9, 'logout', 'User logged out', '::1', '2024-12-12 07:17:58'),
(114, 10, 'login', 'User logged in successfully', '::1', '2024-12-12 07:18:06'),
(115, 10, 'login', 'User logged in successfully', '::1', '2024-12-12 12:25:15'),
(116, 10, 'update', 'Account details updated for user: staff1', '::1', '2024-12-12 12:26:20'),
(117, 28, 'login', 'User logged in successfully', '::1', '2024-12-12 12:26:41'),
(118, 10, 'update', 'Account details updated for user: student1', '::1', '2024-12-12 12:27:32'),
(119, 29, 'login', 'User logged in successfully', '::1', '2024-12-12 12:27:42'),
(120, 28, 'login', 'User logged in successfully', '::1', '2024-12-12 13:50:05'),
(121, 28, 'approve_borrowing', 'Approved borrowing ID: 89', '::1', '2024-12-12 14:30:40'),
(122, 28, 'approve_borrowing', 'Approved borrowing ID: 90', '::1', '2024-12-12 14:31:22'),
(123, 28, 'approve_borrowing', 'Approved borrowing ID: 91', '::1', '2024-12-12 14:40:28'),
(124, 28, 'approve_borrowing', 'Approved borrowing ID: 92', '::1', '2024-12-12 14:40:38'),
(125, 28, 'approve_borrowing', 'Approved borrowing ID: 93', '::1', '2024-12-12 14:40:39'),
(126, 28, 'approve_borrowing', 'Approved borrowing ID: 94', '::1', '2024-12-12 14:40:39'),
(127, 28, 'approve_borrowing', 'Approved borrowing ID: 95', '::1', '2024-12-12 14:48:49'),
(128, 28, 'approve_borrowing', 'Approved borrowing ID: 96', '::1', '2024-12-12 14:48:50'),
(129, 28, 'approve_borrowing', 'Approved borrowing ID: 97', '::1', '2024-12-12 14:51:22'),
(130, 10, 'update', 'Account details updated for user: staff1', '::1', '2024-12-12 14:52:20'),
(131, 28, 'approve_borrowing', 'Approved borrowing request - Resource: How to kill a mocking bird, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2025-01-11 16:16:23', '::1', '2024-12-12 15:16:23'),
(132, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The alchemist, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2025-01-11 16:20:28', '::1', '2024-12-12 15:20:28'),
(133, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The divine comedy, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2025-01-11 16:35:41', '::1', '2024-12-12 15:35:41'),
(134, 10, 'logout', 'User logged out', '::1', '2024-12-12 15:49:09'),
(135, 28, 'logout', 'User logged out', '::1', '2024-12-12 15:49:18'),
(136, 10, 'login', 'User logged in successfully', '::1', '2024-12-13 00:25:51'),
(137, 9, 'login', 'User logged in successfully', '::1', '2024-12-13 00:29:37'),
(138, 28, 'login', 'User logged in successfully', '::1', '2024-12-13 00:30:14'),
(139, 28, 'approve_borrowing', 'Approved borrowing request - Resource: info management, Borrower: John Doe (ID: ), Due Date: 2025-01-12 01:59:00', '::1', '2024-12-13 00:59:00'),
(140, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The divine comedy, Borrower: John Doe (ID: ), Due Date: 2025-01-12 01:59:01', '::1', '2024-12-13 00:59:01'),
(141, 28, 'approve_borrowing', 'Approved borrowing request - Resource: Joestar, Borrower: John Doe (ID: ), Due Date: 2025-01-12 01:59:02', '::1', '2024-12-13 00:59:02'),
(142, 28, 'approve_borrowing', 'Approved borrowing request - Resource: How to kill a mocking bird, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2025-01-12 01:59:03', '::1', '2024-12-13 00:59:03'),
(143, 28, 'approve_borrowing', 'Approved borrowing request - Resource: information managemen1, Borrower: John Doe (ID: ), Due Date: 2025-01-12 02:24:52', '::1', '2024-12-13 01:24:52'),
(144, 28, 'approve_borrowing', 'Approved borrowing request - Resource: Joestar, Borrower: John Doe (ID: ), Due Date: 2025-01-12 02:24:53', '::1', '2024-12-13 01:24:53'),
(145, 28, 'approve_borrowing', 'Approved borrowing request - Resource: How to kill a mocking bird, Borrower: John Doe (ID: ), Due Date: 2025-01-12 02:24:54', '::1', '2024-12-13 01:24:54'),
(146, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The edge of all known, Borrower: John Doe (ID: ), Due Date: 2025-01-12 02:24:54', '::1', '2024-12-13 01:24:54'),
(147, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The alchemist, Borrower: John Doe (ID: ), Due Date: 2025-01-12 02:32:47', '::1', '2024-12-13 01:32:47'),
(148, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The edge of all known, Borrower: John Doe (ID: ), Due Date: 2025-01-12 02:33:16', '::1', '2024-12-13 01:33:16'),
(149, 28, 'approve_borrowing', 'Approved borrowing request - Resource: information managemen1, Borrower: John Doe (ID: ), Due Date: 2025-01-12 02:34:17', '::1', '2024-12-13 01:34:17'),
(150, 28, 'approve_borrowing', 'Approved borrowing request - Resource: Joestar, Borrower: John Doe (ID: ), Due Date: 2025-01-12 02:34:18', '::1', '2024-12-13 01:34:19'),
(151, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The Greatest, Borrower: John Doe (ID: ), Due Date: 2025-01-12 02:34:19', '::1', '2024-12-13 01:34:19'),
(152, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The Greatest, Borrower: John Doe (ID: ), Due Date: 2025-01-12 03:09:53', '::1', '2024-12-13 02:09:53'),
(153, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The divine comedy, Borrower: John Doe (ID: ), Due Date: 2025-01-12 03:09:54', '::1', '2024-12-13 02:09:54'),
(154, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The alchemist, Borrower: John Doe (ID: ), Due Date: 2025-01-12 03:09:55', '::1', '2024-12-13 02:09:55'),
(155, 28, 'approve_borrowing', 'Approved borrowing request - Resource: How to kill a mocking bird, Borrower: John Doe (ID: ), Due Date: 2025-01-12 03:09:56', '::1', '2024-12-13 02:09:56'),
(156, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The edge of all known, Borrower: John Doe (ID: ), Due Date: 2025-01-12 03:15:38', '::1', '2024-12-13 02:15:38'),
(157, 28, 'approve_borrowing', 'Approved borrowing request - Resource: information managemen1, Borrower: John Doe (ID: ), Due Date: 2025-01-12 03:15:39', '::1', '2024-12-13 02:15:39'),
(158, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The divine comedy, Borrower: John Doe (ID: ), Due Date: 2025-01-12 03:15:40', '::1', '2024-12-13 02:15:40'),
(159, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The divine comedy, Borrower: John Doe (ID: ), Due Date: 2025-01-12 03:16:08', '::1', '2024-12-13 02:16:08'),
(160, 9, 'logout', 'User logged out', '::1', '2024-12-13 02:20:06'),
(161, 9, 'login', 'User logged in successfully', '::1', '2024-12-13 02:20:12'),
(162, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The alchemist, Borrower: John Doe (ID: ), Due Date: 2025-01-12 03:24:36', '::1', '2024-12-13 02:24:36'),
(163, 9, 'logout', 'User logged out', '::1', '2024-12-13 02:35:05'),
(164, 10, 'login', 'User logged in successfully', '::1', '2024-12-13 13:32:44'),
(165, 28, 'login', 'User logged in successfully', '::1', '2024-12-13 13:33:00'),
(166, 29, 'login', 'User logged in successfully', '::1', '2024-12-13 13:33:14'),
(167, 28, 'approve_borrowing', 'Approved borrowing request - Resource: dsadsadasdas, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2025-01-12 14:36:09', '::1', '2024-12-13 13:36:09'),
(168, 28, 'approve_borrowing', 'Approved borrowing request - Resource: dsadsadasdas, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2025-01-12 14:36:44', '::1', '2024-12-13 13:36:44'),
(169, 28, 'approve_borrowing', 'Approved borrowing request - Resource: dsadsadasdas, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2025-01-12 14:37:45', '::1', '2024-12-13 13:37:45'),
(170, 10, 'update', 'Account details updated for user: faculty1', '::1', '2024-12-13 15:03:36'),
(171, 10, 'update', 'Account details updated for user: faculty1', '::1', '2024-12-13 15:05:00'),
(172, 10, 'update', 'Account details updated for user: faculty1', '::1', '2024-12-13 15:05:04'),
(173, 10, 'update', 'Account details updated for user: faculty1', '::1', '2024-12-13 15:05:08'),
(174, 10, 'update', 'Account details updated for user: faculty1', '::1', '2024-12-13 15:06:08'),
(175, 10, 'update', 'Account details updated for user: faculty1', '::1', '2024-12-13 15:06:26'),
(176, 10, 'update', 'Account details updated for user: student1', '::1', '2024-12-13 15:15:16'),
(177, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The alchemist, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2025-01-12 16:17:30', '::1', '2024-12-13 15:17:30'),
(178, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The divine comedy, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2025-01-12 16:17:31', '::1', '2024-12-13 15:17:31'),
(179, 28, 'approve_borrowing', 'Approved borrowing request - Resource: Wow signal, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2025-01-12 16:22:45', '::1', '2024-12-13 15:22:45'),
(180, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The Greatest, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2025-01-12 16:22:46', '::1', '2024-12-13 15:22:46'),
(181, 28, 'approve_borrowing', 'Approved borrowing request - Resource: Joestar, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2025-01-12 16:22:46', '::1', '2024-12-13 15:22:46'),
(182, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The edge of all known, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2025-01-12 16:22:47', '::1', '2024-12-13 15:22:47'),
(183, 28, 'approve_borrowing', 'Approved borrowing request - Resource: Wow signal, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2025-01-12 16:24:56', '::1', '2024-12-13 15:24:56'),
(184, 10, 'update', 'Account details updated for user: student1', '::1', '2024-12-13 15:25:32'),
(185, 10, 'login', 'User logged in successfully', '::1', '2024-12-14 03:58:49'),
(186, 9, 'login', 'User logged in successfully', '::1', '2024-12-14 06:05:02'),
(187, 9, 'logout', 'User logged out', '::1', '2024-12-14 06:05:17'),
(188, 28, 'login', 'User logged in successfully', '::1', '2024-12-14 06:05:22'),
(189, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The alchemist, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2025-01-13 07:05:30', '::1', '2024-12-14 06:05:30'),
(190, 10, 'login', 'User logged in successfully', '::1', '2024-12-14 10:02:16'),
(191, 29, 'login', 'User logged in successfully', '::1', '2024-12-14 10:04:19'),
(192, 28, 'login', 'User logged in successfully', '::1', '2024-12-14 10:04:35'),
(193, 28, 'approve_borrowing', 'Approved borrowing request - Resource: Joestar, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2025-01-13 11:04:50', '::1', '2024-12-14 10:04:50'),
(194, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The Greatest, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2025-01-13 11:15:53', '::1', '2024-12-14 10:15:53'),
(195, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The divine comedy, Borrower: John Doe (ID: ), Due Date: 2025-01-13 11:15:54', '::1', '2024-12-14 10:15:54'),
(196, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The alchemist, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2025-01-13 11:23:00', '::1', '2024-12-14 10:23:00'),
(197, 10, 'update', 'Account details updated for user: student1', '::1', '2024-12-14 10:36:38'),
(198, 10, 'update', 'Account details updated for user: student1', '::1', '2024-12-14 10:36:57'),
(199, 10, 'update', 'Account details updated for user: student1', '::1', '2024-12-14 10:38:48'),
(200, 10, 'update', 'Account details updated for user: student1', '::1', '2024-12-14 10:39:01'),
(201, 28, 'approve_borrowing', 'Approved borrowing request - Resource: How to kill a mocking bird, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2025-01-13 11:40:07', '::1', '2024-12-14 10:40:07'),
(202, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The alchemist, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2024-12-16 11:48:55', '::1', '2024-12-14 10:48:55'),
(203, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The alchemist, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2024-12-17 12:12:49', '::1', '2024-12-14 11:12:49'),
(204, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The divine comedy, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2024-12-17 14:55:34', '::1', '2024-12-14 13:55:34'),
(205, 28, 'approve_borrowing', 'Approved borrowing request - Resource: How to kill a mocking bird, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2024-12-20 16:15:38', '::1', '2024-12-19 15:15:38'),
(206, 10, 'fine_payment', 'Fine payment processed for borrowing ID: 144. Amount: $40.00', '::1', '2024-12-21 15:42:27'),
(207, 10, 'fine_payment', 'Fine payment processed for borrowing ID: 144. Amount: $40.00', '::1', '2024-12-21 15:42:29'),
(208, 10, 'fine_payment', 'Fine payment processed for borrowing ID: 144. Amount: $40.00', '::1', '2024-12-21 15:58:27'),
(209, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The alchemist, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2024-12-22 17:12:30', '::1', '2024-12-21 16:12:30'),
(210, 10, 'login', 'User logged in successfully', '::1', '2024-12-15 12:40:22'),
(211, 29, 'login', 'User logged in successfully', '::1', '2024-12-15 12:46:21'),
(212, 28, 'login', 'User logged in successfully', '::1', '2024-12-15 12:46:58'),
(213, 28, 'approve_borrowing', 'Approved borrowing request - Resource: How to kill a mocking bird, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2024-12-16 13:47:17', '::1', '2024-12-15 12:47:17'),
(214, 28, 'login', 'User logged in successfully', '::1', '2024-12-17 13:21:18'),
(215, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The edge of all known, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2024-12-18 14:36:53', '::1', '2024-12-17 13:36:53'),
(216, 10, 'login', 'User logged in successfully', '::1', '2024-12-16 03:33:12'),
(217, 28, 'login', 'User logged in successfully', '::1', '2024-12-16 04:49:53'),
(218, 29, 'login', 'User logged in successfully', '::1', '2024-12-16 04:51:19'),
(219, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The divine comedy, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2024-12-17 06:00:30', '::1', '2024-12-16 05:00:30'),
(220, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The edge of all known, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2024-12-17 06:00:32', '::1', '2024-12-16 05:00:32'),
(221, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The edge of all known, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2024-12-19 06:01:36', '::1', '2024-12-16 05:01:36'),
(222, 28, 'approve_borrowing', 'Approved borrowing request - Resource: How to kill a mocking bird, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2024-12-18 07:49:41', '::1', '2024-12-16 06:49:41'),
(223, 10, 'login', 'User logged in successfully', '::1', '2024-12-16 10:32:26'),
(224, 10, 'fine_payment', 'Processed fine payment of $20.00 for borrowing ID: 152', '::1', '2024-12-16 10:32:42'),
(225, 10, 'fine_payment', 'Processed fine payment of $20.00 for borrowing ID: 152', '::1', '2024-12-16 10:38:51'),
(226, 10, 'fine_payment', 'Processed fine payment of $20.00 for borrowing ID: 152', '::1', '2024-12-16 10:42:26'),
(227, 29, 'login', 'User logged in successfully', '::1', '2024-12-16 10:43:22'),
(228, 28, 'login', 'User logged in successfully', '::1', '2024-12-16 10:44:31'),
(229, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The edge of all known, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2024-12-18 11:44:40', '::1', '2024-12-16 10:44:40'),
(230, 28, 'approve_borrowing', 'Approved borrowing request - Resource: Joestar, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2024-12-18 11:46:27', '::1', '2024-12-16 10:46:27'),
(231, 10, 'fine_payment', 'Processed fine payment of $111.00 for borrowing ID: 154', '::1', '2024-12-16 10:54:14'),
(232, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The Greatest, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2024-12-18 12:14:19', '::1', '2024-12-16 11:14:19'),
(233, 10, 'fine_payment', 'Processed fine payment of $111.00 for borrowing ID: 155', '::1', '2024-12-16 11:16:41'),
(234, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The divine comedy, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2024-12-18 12:26:03', '::1', '2024-12-16 11:26:03'),
(235, 10, 'fine_payment', 'Processed fine payment of $30.00 for borrowing ID: 156', '::1', '2024-12-16 11:36:09'),
(236, 28, 'approve_borrowing', 'Approved borrowing request - Resource: Joestar, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2024-12-18 12:54:24', '::1', '2024-12-16 11:54:24'),
(237, 10, 'fine_payment', 'Processed fine payment of $111.00 for borrowing ID: 157', '::1', '2024-12-16 12:27:43'),
(238, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The edge of all known, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2024-12-18 14:54:04', '::1', '2024-12-16 13:54:04'),
(239, 10, 'fine_payment', 'Processed fine payment of $90.00 for borrowing ID: 158', '::1', '2024-12-16 14:00:57'),
(240, 10, 'fine_payment', 'Processed fine payment of $90.00 for borrowing ID: 158', '::1', '2024-12-16 14:07:26'),
(241, 10, 'fine_payment', 'Processed fine payment of $90.00 for borrowing ID: 158', '::1', '2024-12-16 14:58:19'),
(242, 10, 'fine_payment', 'Processed fine payment of $90.00 for borrowing ID: 158', '::1', '2024-12-16 15:28:09'),
(243, 10, 'login', 'User logged in successfully', '::1', '2024-12-17 04:52:26'),
(244, 10, 'fine_payment', 'Processed fine payment of $180.00 for borrowing ID: 158', '::1', '2024-12-17 05:17:52'),
(245, 10, 'fine_payment', 'Processed fine payment of $180.00 for borrowing ID: 158', '::1', '2024-12-17 05:30:42'),
(246, 28, 'login', 'User logged in successfully', '::1', '2024-12-17 05:41:07'),
(247, 28, 'fine_payment', 'Processed fine payment of $180.00 for borrowing ID: 158', '::1', '2024-12-17 05:59:50'),
(248, 28, 'fine_payment', 'Processed fine payment of $180.00 for borrowing ID: 158', '::1', '2024-12-17 06:15:56'),
(249, 29, 'login', 'User logged in successfully', '::1', '2024-12-17 06:19:37'),
(250, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The edge of all known, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2024-12-19 07:33:22', '::1', '2024-12-17 06:33:22'),
(251, 28, 'approve_borrowing', 'Approved borrowing request - Resource: How to kill a mocking bird, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2024-12-19 07:36:16', '::1', '2024-12-17 06:36:16'),
(252, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The divine comedy, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2024-12-19 07:55:50', '::1', '2024-12-17 06:55:50'),
(253, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The alchemist, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2024-12-19 07:58:42', '::1', '2024-12-17 06:58:42'),
(254, 10, 'fine_payment', 'Processed fine payment of $20.00 for borrowing ID: 162', '::1', '2024-12-17 06:59:48'),
(255, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The edge of all known, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2024-12-19 08:03:40', '::1', '2024-12-17 07:03:40'),
(256, 10, 'login', 'User logged in successfully', '::1', '2024-12-17 11:54:07'),
(257, 10, 'logout', 'User logged out', '::1', '2024-12-17 12:28:06'),
(258, 28, 'login', 'User logged in successfully', '::1', '2024-12-17 12:28:14'),
(259, 28, 'logout', 'User logged out', '::1', '2024-12-17 13:35:56'),
(260, 29, 'login', 'User logged in successfully', '::1', '2024-12-17 13:36:14'),
(261, 29, 'logout', 'User logged out', '::1', '2024-12-17 13:36:27'),
(262, 28, 'login', 'User logged in successfully', '::1', '2024-12-17 13:36:35'),
(263, 28, 'logout', 'User logged out', '::1', '2024-12-17 13:36:52'),
(264, 29, 'login', 'User logged in successfully', '::1', '2024-12-17 13:37:06'),
(265, 29, 'logout', 'User logged out', '::1', '2024-12-17 13:37:11'),
(266, 28, 'login', 'User logged in successfully', '::1', '2024-12-17 13:37:17'),
(267, 28, 'logout', 'User logged out', '::1', '2024-12-17 13:57:58'),
(268, 10, 'login', 'User logged in successfully', '::1', '2024-12-17 13:58:04'),
(269, 10, 'logout', 'User logged out', '::1', '2024-12-17 14:13:15'),
(270, 28, 'login', 'User logged in successfully', '::1', '2024-12-17 14:13:21'),
(271, 28, 'approve_borrowing', 'Approved borrowing request - Resource: The divine comedy, Borrower: sdaasd asasd (ID: S20249490), Due Date: 2024-12-19 15:13:26', '::1', '2024-12-17 14:13:26'),
(272, 28, 'logout', 'User logged out', '::1', '2024-12-17 14:21:50'),
(273, 10, 'login', 'User logged in successfully', '::1', '2024-12-17 14:21:58'),
(274, 10, 'login', 'User logged in successfully', '::1', '2025-02-09 11:39:00'),
(275, 28, 'login', 'User logged in successfully', '::1', '2025-02-09 12:45:34'),
(276, 9, 'login', 'User logged in successfully', '::1', '2025-02-09 12:56:05'),
(277, 28, 'fine_payment', 'Processed fine payment of $520.00 for borrowing ID: 164', '::1', '2025-02-09 13:03:16');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `book_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `author` varchar(100) NOT NULL,
  `isbn` varchar(13) NOT NULL,
  `publisher` varchar(100) NOT NULL,
  `edition` varchar(20) DEFAULT NULL,
  `publication_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`book_id`, `resource_id`, `author`, `isbn`, `publisher`, `edition`, `publication_date`) VALUES
(12, 29, 'Paulo Coelho', '1251125AC6', 'N/A', 'First edition', '1988-01-01'),
(13, 30, ' Dante Alighieri', '112233111', 'N/A', 'N/A', '1321-01-01'),
(14, 31, 'Harper lee', '121212AA', 'J. B. Lippincott & Co.', 'First edition', '1988-11-07');

-- --------------------------------------------------------

--
-- Table structure for table `borrowings`
--

CREATE TABLE `borrowings` (
  `borrowing_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `borrow_date` datetime DEFAULT current_timestamp(),
  `due_date` datetime DEFAULT NULL,
  `return_date` datetime DEFAULT NULL,
  `fine_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','active','returned','overdue') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `returned_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrowings`
--

INSERT INTO `borrowings` (`borrowing_id`, `user_id`, `resource_id`, `borrow_date`, `due_date`, `return_date`, `fine_amount`, `status`, `approved_by`, `approved_at`, `returned_by`) VALUES
(116, 9, 31, '2024-12-13 10:05:18', '2025-01-12 03:09:56', '2024-12-13 03:15:35', 0.00, 'returned', 28, '2024-12-13 10:09:56', 28),
(117, 9, 30, '2024-12-13 10:07:37', '2025-01-12 03:09:54', '2024-12-13 03:11:16', 0.00, 'returned', 28, '2024-12-13 10:09:54', 28),
(118, 9, 29, '2024-12-13 10:07:48', '2025-01-12 03:09:55', '2024-12-13 03:15:32', 0.00, 'returned', 28, '2024-12-13 10:09:55', 28),
(119, 9, 20, '2024-12-13 10:09:36', '2025-01-12 03:09:53', '2024-12-13 03:15:30', 0.00, 'returned', 28, '2024-12-13 10:09:53', 28),
(120, 9, 33, '2024-12-13 10:13:00', '2025-01-12 03:15:38', '2024-12-13 03:15:49', 0.00, 'returned', 28, '2024-12-13 10:15:38', 28),
(121, 9, 40, '2024-12-13 10:13:43', '2025-01-12 03:15:39', '2024-12-13 03:15:51', 0.00, 'returned', 28, '2024-12-13 10:15:39', 28),
(122, 9, 30, '2024-12-13 10:15:11', '2025-01-12 03:15:40', '2024-12-13 03:15:55', 0.00, 'returned', 28, '2024-12-13 10:15:40', 28),
(123, 9, 30, '2024-12-13 10:16:01', '2025-01-12 03:16:08', '2024-12-13 03:16:26', 0.00, 'returned', 28, '2024-12-13 10:16:08', 28),
(124, 9, 29, '2024-12-13 10:20:34', '2025-01-12 03:24:36', '2024-12-13 14:36:00', 0.00, 'returned', 28, '2024-12-13 10:24:36', 10),
(125, 29, 45, '2024-12-13 21:35:46', '2025-01-12 14:36:09', '2024-12-13 14:36:19', 0.00, 'returned', 28, '2024-12-13 21:36:09', 28),
(126, 29, 45, '2024-12-13 21:36:36', '2025-01-12 14:36:44', '2024-12-13 14:36:48', 0.00, 'returned', 28, '2024-12-13 21:36:44', 28),
(127, 29, 45, '2024-12-13 21:37:10', '2025-01-12 14:37:45', '2024-12-13 14:37:51', 0.00, 'returned', 28, '2024-12-13 21:37:45', 28),
(128, 29, 29, '2024-12-13 23:15:22', '2024-12-16 16:15:22', '2024-12-13 16:17:01', 0.00, 'returned', NULL, NULL, 28),
(129, 29, 30, '2024-12-13 23:17:23', '2025-01-12 16:17:31', '2024-12-13 16:22:52', 0.00, 'returned', 28, '2024-12-13 23:17:31', 28),
(130, 29, 29, '2024-12-13 23:17:26', '2025-01-12 16:17:30', '2024-12-13 16:22:50', 0.00, 'returned', 28, '2024-12-13 23:17:30', 28),
(131, 29, 38, '2024-12-13 23:22:33', '2025-01-12 16:22:46', '2024-12-13 16:22:56', 0.00, 'returned', 28, '2024-12-13 23:22:46', 28),
(132, 29, 20, '2024-12-13 23:22:34', '2025-01-12 16:22:46', '2024-12-13 16:22:55', 0.00, 'returned', 28, '2024-12-13 23:22:46', 28),
(133, 29, 33, '2024-12-13 23:22:36', '2025-01-12 16:22:47', '2024-12-13 16:22:58', 0.00, 'returned', 28, '2024-12-13 23:22:47', 28),
(134, 29, 32, '2024-12-13 23:22:37', '2025-01-12 16:22:45', '2024-12-13 16:22:53', 0.00, 'returned', 28, '2024-12-13 23:22:45', 28),
(135, 29, 32, '2024-12-13 23:24:51', '2025-01-12 16:24:56', '2024-12-13 16:25:04', 0.00, 'returned', 28, '2024-12-13 23:24:56', 28),
(136, 29, 29, '2024-12-13 23:25:07', '2025-01-13 07:05:30', '2024-12-14 07:27:47', 0.00, 'returned', 28, '2024-12-14 14:05:30', 28),
(139, 9, 30, '2024-12-14 14:05:05', '2025-01-13 11:15:54', '2024-12-14 11:39:25', 0.00, 'returned', 28, '2024-12-14 18:15:54', 28),
(140, 29, 29, '2024-12-14 18:22:54', '2025-01-13 11:23:00', '2024-12-14 11:39:26', 0.00, 'returned', 28, '2024-12-14 18:23:00', 28),
(141, 29, 31, '2024-12-14 18:39:44', '2025-01-13 11:40:07', '2024-12-14 11:48:24', 0.00, 'returned', 28, '2024-12-14 18:40:07', 28),
(142, 29, 29, '2024-12-14 18:48:32', '2024-12-16 11:48:55', '2024-12-14 12:11:57', 0.00, 'returned', 28, '2024-12-14 18:48:55', 28),
(144, 29, 30, '2024-12-14 21:55:20', '2024-12-17 14:55:34', '2024-12-21 17:11:20', 50.00, 'returned', 28, '2024-12-14 21:55:34', 10),
(146, 29, 29, '2024-12-22 00:12:22', '2024-12-22 17:12:30', '2024-12-17 14:35:07', 0.00, 'returned', 28, '2024-12-22 00:12:30', 10),
(147, 29, 31, '2024-12-15 20:46:24', '2024-12-16 13:47:17', '2024-12-17 14:34:35', 20.00, 'returned', 28, '2024-12-15 20:47:17', 10),
(148, 29, 33, '2024-12-11 21:36:34', '2024-12-15 14:36:53', '2024-12-16 05:50:35', 99.00, 'returned', 28, '2024-12-17 21:36:53', 10),
(149, 29, 33, '2024-12-19 22:19:16', '2024-12-17 06:00:32', '2024-12-16 06:01:07', 0.00, 'returned', 28, '2024-12-16 13:00:32', 10),
(150, 29, 30, '2024-12-11 12:59:33', '2024-12-15 06:00:30', '2024-12-16 06:17:12', 20.00, 'returned', 28, '2024-12-16 13:00:30', 10),
(151, 29, 33, '2024-12-11 13:01:28', '2024-12-12 06:01:36', '2024-12-16 06:12:37', 450.00, 'returned', 28, '2024-12-16 13:01:36', 28),
(152, 29, 31, '2024-12-11 14:49:34', '2024-12-14 14:49:34', '2024-12-16 11:43:41', 20.00, 'returned', 28, '2024-12-16 14:49:41', 10),
(153, 29, 33, '2024-12-11 18:44:05', '2024-12-15 11:44:40', '2024-12-16 11:46:06', 180.00, 'returned', 28, '2024-12-16 18:44:40', 10),
(154, 29, 38, '2024-12-11 18:46:23', '2024-12-15 11:46:27', '2024-12-16 12:17:49', 222.00, 'returned', 28, '2024-12-16 18:46:27', 10),
(155, 29, 20, '2024-12-11 19:13:19', '2024-12-15 12:14:19', '2024-12-16 12:17:47', 222.00, 'returned', 28, '2024-12-16 19:14:19', 10),
(156, 29, 30, '2024-12-11 19:25:59', '2024-12-13 12:26:03', '2024-12-16 12:53:54', 40.00, 'returned', 28, '2024-12-16 19:26:03', 28),
(157, 29, 38, '2024-12-11 19:54:20', '2024-12-15 12:54:24', '2024-12-16 13:29:07', 222.00, 'returned', 28, '2024-12-16 19:54:24', 28),
(158, 29, 33, '2024-12-11 21:53:57', '2024-12-15 14:54:04', '2024-12-17 07:31:11', 180.00, 'returned', 28, '2024-12-16 21:54:04', 10),
(159, 29, 33, '2024-12-11 14:32:48', '2024-12-15 07:33:22', '2024-12-17 07:34:15', 270.00, 'returned', 28, '2024-12-17 14:33:22', 10),
(160, 29, 31, '2024-12-11 14:35:28', '2024-12-15 07:36:16', '2024-12-17 14:36:41', 30.00, 'returned', 28, '2024-12-17 14:36:16', 28),
(161, 29, 30, '2024-12-17 14:55:15', '2024-12-19 07:55:50', '2024-12-17 14:36:47', 0.00, 'returned', 28, '2024-12-17 14:55:50', 28),
(162, 29, 29, '2024-12-11 14:58:39', '2024-12-15 07:58:42', '2024-12-17 14:36:44', 30.00, 'returned', 28, '2024-12-17 14:58:42', 28),
(163, 29, 33, '2024-12-17 15:03:30', '2024-12-19 08:03:40', '2024-12-17 14:36:48', 0.00, 'returned', 28, '2024-12-17 15:03:40', 28),
(164, 29, 30, '2024-12-17 21:37:09', '2024-12-19 15:13:26', NULL, 0.00, 'active', 28, '2024-12-17 22:13:26', NULL),
(165, 9, 29, '2025-02-09 21:00:21', NULL, NULL, 0.00, 'pending', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `fine_configurations`
--

CREATE TABLE `fine_configurations` (
  `config_id` int(11) NOT NULL,
  `resource_type` enum('book','periodical','media') NOT NULL,
  `fine_amount` decimal(10,2) NOT NULL DEFAULT 1.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fine_configurations`
--

INSERT INTO `fine_configurations` (`config_id`, `resource_type`, `fine_amount`, `updated_at`) VALUES
(1, 'book', 10.00, '2024-12-17 06:58:22'),
(2, 'periodical', 111.00, '2024-12-12 13:50:39'),
(3, 'media', 90.00, '2024-12-16 05:06:15');

-- --------------------------------------------------------

--
-- Table structure for table `fine_payments`
--

CREATE TABLE `fine_payments` (
  `payment_id` int(11) NOT NULL,
  `borrowing_id` int(11) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `payment_status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `processed_by` int(11) DEFAULT NULL,
  `payment_notes` text DEFAULT NULL,
  `cash_received` decimal(10,2) NOT NULL,
  `change_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fine_payments`
--

INSERT INTO `fine_payments` (`payment_id`, `borrowing_id`, `amount_paid`, `payment_date`, `payment_status`, `processed_by`, `payment_notes`, `cash_received`, `change_amount`) VALUES
(5, 144, 40.00, '2024-12-21 23:58:27', 'paid', 10, 'ok', 0.00, 0.00),
(33, 152, 20.00, '2024-12-16 18:42:26', 'paid', 10, 'ok nadaw', 0.00, 0.00),
(36, 156, 30.00, '2024-12-16 19:36:09', 'paid', 10, 'bayad na ', 50.00, 20.00),
(46, 162, 20.00, '2024-12-17 14:59:48', 'paid', 10, '', 500.00, 480.00),
(47, 164, 520.00, '2025-02-09 21:03:16', 'paid', 28, '', 10000.00, 9480.00);

-- --------------------------------------------------------

--
-- Table structure for table `library_resources`
--

CREATE TABLE `library_resources` (
  `resource_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `accession_number` varchar(20) NOT NULL,
  `category` enum('book','periodical','media') NOT NULL,
  `status` enum('available','borrowed','maintenance') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `cover_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `library_resources`
--

INSERT INTO `library_resources` (`resource_id`, `title`, `accession_number`, `category`, `status`, `created_at`, `updated_at`, `cover_image`) VALUES
(20, 'The Greatest', 'P-2024-003', 'periodical', 'available', '2024-12-02 14:32:07', '2024-12-16 11:17:47', 'uploads/covers/cover_67554b0c79270.png'),
(29, 'The alchemist', 'B-2024-002', 'book', '', '2024-12-08 07:46:23', '2025-02-09 13:00:21', 'uploads/covers/cover_67554ecf566bb.jpg'),
(30, 'The divine comedy', 'B-2024-004', 'book', 'borrowed', '2024-12-08 07:52:19', '2024-12-17 14:13:26', 'uploads/covers/cover_675552c3812fa.jpg'),
(31, 'How to kill a mocking bird', 'B-2024-005', 'book', 'available', '2024-12-08 08:04:40', '2024-12-17 13:36:41', 'uploads/covers/cover_67555318caf06.jpg'),
(32, 'Wow signal', 'R-2024-001', 'media', 'available', '2024-12-08 08:08:04', '2024-12-13 15:25:04', 'uploads/covers/cover_675553e45bf98.jpg'),
(33, 'The edge of all known', 'R-2024-002', 'media', 'available', '2024-12-08 08:12:17', '2024-12-17 13:36:48', 'uploads/covers/cover_675554e11d962.jpg'),
(38, 'Joestar', 'P-2024-006', 'periodical', 'available', '2024-12-09 13:05:12', '2024-12-16 12:29:07', 'uploads/covers/cover_6756eb0810e2d.png'),
(40, 'information managemen1', 'P-2024-007', 'periodical', '', '2024-12-10 06:59:48', '2024-12-13 13:40:19', NULL),
(41, 'info management', 'R-2024-004', 'media', '', '2024-12-10 07:00:17', '2024-12-13 13:40:55', NULL),
(45, 'dsadsadasdas', 'B-2024-007', 'book', '', '2024-12-13 02:34:51', '2024-12-13 13:38:39', NULL),
(46, 'test1', 'B-2024-008', 'book', '', '2024-12-13 13:38:22', '2024-12-13 13:38:41', NULL),
(47, 'ewewr', 'P-2024-008', 'periodical', '', '2024-12-13 13:38:59', '2024-12-13 13:40:17', NULL),
(48, 'wewewe', 'R-2024-005', 'media', '', '2024-12-13 13:40:33', '2024-12-13 13:40:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `media_resources`
--

CREATE TABLE `media_resources` (
  `media_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `format` varchar(50) NOT NULL,
  `runtime` int(11) DEFAULT NULL,
  `media_type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `media_resources`
--

INSERT INTO `media_resources` (`media_id`, `resource_id`, `format`, `runtime`, `media_type`) VALUES
(10, 32, 'DVD', 65, 'Video'),
(11, 33, 'CD', 99, 'Video');

-- --------------------------------------------------------

--
-- Table structure for table `periodicals`
--

CREATE TABLE `periodicals` (
  `periodical_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `issn` varchar(8) NOT NULL,
  `volume` varchar(20) DEFAULT NULL,
  `issue` varchar(20) DEFAULT NULL,
  `publication_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `periodicals`
--

INSERT INTO `periodicals` (`periodical_id`, `resource_id`, `issn`, `volume`, `issue`, `publication_date`) VALUES
(4, 20, '1122544x', '1', '21', '2024-12-08'),
(11, 38, '12121212', '1', '1', '2000-02-12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `membership_id` varchar(20) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','student','faculty','staff') NOT NULL,
  `max_books` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `borrowing_days_limit` int(11) DEFAULT 7
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `membership_id`, `username`, `password`, `first_name`, `last_name`, `email`, `role`, `max_books`, `created_at`, `updated_at`, `borrowing_days_limit`) VALUES
(9, NULL, 'user1', '$2y$10$ikFVY6FlstJFUvrG.lL9seZd7QEvwa0EPZHShzYK2ovBONcmGdh9q', 'John', 'Doe', 'user1@gmail.com', 'faculty', 5, '2024-11-23 11:14:11', '2024-12-08 08:15:35', 7),
(10, NULL, 'admin1', '$2y$10$EOgSJ.NhTX4KtWmPK45aVeF3ylHV5WQCsIw5MDc8Y95vfbBZf0uyi', 'dsad', 'asdasd', 'itsebs758@gmail.com', 'admin', 10, '2024-11-23 11:19:06', '2024-12-06 14:07:40', 7),
(28, 'S20244337', 'staff1', '$2y$10$zYxXz2B58es5nT/itaiF7Oo8yujpQwYruvgHb99UVP2Jg/7KvswJa', 'ebszar', 'lapaz', 'staff1@gmail.com', 'staff', 4, '2024-12-10 06:54:07', '2024-12-12 14:52:20', 7),
(29, 'S20249490', 'student1', '$2y$10$SZVcfAfudYUZ1BTcrW0XSuOKwVJVv1uzDw56kQgNF9L49lqiFtXme', 'sdaasd', 'asasd', 'student1@gmail.com', 'student', 4, '2024-12-10 06:54:32', '2024-12-16 05:04:36', 2),
(30, 'F20248589', 'faculty1', '$2y$10$.6M.jyeMPjv1tsKVpcLkquGhWSeCchnZKTVVA1r00nmoM9m71CPnq', 'dsad', 'asdasd', 'faculty1@gmail.com', 'faculty', 1, '2024-12-10 06:54:59', '2024-12-19 15:00:43', 7);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_user_id_activity_logs` (`user_id`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`),
  ADD KEY `resource_id` (`resource_id`);

--
-- Indexes for table `borrowings`
--
ALTER TABLE `borrowings`
  ADD PRIMARY KEY (`borrowing_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `resource_id` (`resource_id`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `returned_by` (`returned_by`);

--
-- Indexes for table `fine_configurations`
--
ALTER TABLE `fine_configurations`
  ADD PRIMARY KEY (`config_id`),
  ADD UNIQUE KEY `resource_type` (`resource_type`);

--
-- Indexes for table `fine_payments`
--
ALTER TABLE `fine_payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `borrowing_id` (`borrowing_id`),
  ADD KEY `processed_by` (`processed_by`);

--
-- Indexes for table `library_resources`
--
ALTER TABLE `library_resources`
  ADD PRIMARY KEY (`resource_id`),
  ADD UNIQUE KEY `accession_number` (`accession_number`);

--
-- Indexes for table `media_resources`
--
ALTER TABLE `media_resources`
  ADD PRIMARY KEY (`media_id`),
  ADD KEY `resource_id` (`resource_id`);

--
-- Indexes for table `periodicals`
--
ALTER TABLE `periodicals`
  ADD PRIMARY KEY (`periodical_id`),
  ADD KEY `resource_id` (`resource_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `membership_id` (`membership_id`),
  ADD KEY `idx_user_id_users` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=278;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `borrowings`
--
ALTER TABLE `borrowings`
  MODIFY `borrowing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=166;

--
-- AUTO_INCREMENT for table `fine_configurations`
--
ALTER TABLE `fine_configurations`
  MODIFY `config_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `fine_payments`
--
ALTER TABLE `fine_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `library_resources`
--
ALTER TABLE `library_resources`
  MODIFY `resource_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `media_resources`
--
ALTER TABLE `media_resources`
  MODIFY `media_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `periodicals`
--
ALTER TABLE `periodicals`
  MODIFY `periodical_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `books_ibfk_1` FOREIGN KEY (`resource_id`) REFERENCES `library_resources` (`resource_id`) ON DELETE CASCADE;

--
-- Constraints for table `borrowings`
--
ALTER TABLE `borrowings`
  ADD CONSTRAINT `borrowings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `borrowings_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `library_resources` (`resource_id`),
  ADD CONSTRAINT `borrowings_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `borrowings_ibfk_4` FOREIGN KEY (`returned_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `fine_payments`
--
ALTER TABLE `fine_payments`
  ADD CONSTRAINT `fine_payments_ibfk_1` FOREIGN KEY (`borrowing_id`) REFERENCES `borrowings` (`borrowing_id`),
  ADD CONSTRAINT `fine_payments_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `media_resources`
--
ALTER TABLE `media_resources`
  ADD CONSTRAINT `media_resources_ibfk_1` FOREIGN KEY (`resource_id`) REFERENCES `library_resources` (`resource_id`) ON DELETE CASCADE;

--
-- Constraints for table `periodicals`
--
ALTER TABLE `periodicals`
  ADD CONSTRAINT `periodicals_ibfk_1` FOREIGN KEY (`resource_id`) REFERENCES `library_resources` (`resource_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
