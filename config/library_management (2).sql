-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Generation Time: Dec 16, 2024 at 02:36 PM
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
(119, 29, 'login', 'User logged in successfully', '::1', '2024-12-12 12:27:42');

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
(11, 28, 'Frank Herbert', '122515XA12', 'Chilton Books', 'First edition', '1965-09-12'),
(12, 29, 'Paulo Coelho', '1251125AC6', 'N/A', 'First edition', '1988-01-01'),
(13, 30, ' Dante Alighieri', '112233111', 'N/A', 'N/A', '1321-01-01'),
(14, 31, 'Harper lee', '121212AA', 'J. B. Lippincott & Co.', 'First edition', '1988-11-07'),
(16, 39, 'ebs lapaz', 'gdfasgds', '1213213', '12', '2000-12-12');

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
  `status` enum('active','returned','overdue') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrowings`
--

INSERT INTO `borrowings` (`borrowing_id`, `user_id`, `resource_id`, `borrow_date`, `due_date`, `return_date`, `fine_amount`, `status`) VALUES
(63, 9, 31, '2024-12-10 13:00:38', '2025-01-09 06:00:38', '2024-12-10 06:01:00', 0.00, 'returned'),
(64, 9, 29, '2024-12-10 13:01:09', '2025-01-09 06:01:09', '2024-12-10 06:01:19', 0.00, 'returned'),
(65, 9, 38, '2024-12-10 13:28:15', '2025-01-09 06:28:15', '2024-12-10 06:28:31', 0.00, 'returned'),
(66, 9, 32, '2024-12-10 13:36:40', '2025-01-09 06:36:40', '2024-12-10 06:41:10', 0.00, 'returned'),
(67, 9, 33, '2024-12-10 13:43:03', '2025-01-09 06:43:03', '2024-12-10 06:43:58', 0.00, 'returned'),
(68, 9, 30, '2024-12-11 14:01:22', '2024-01-10 07:01:22', '2024-12-10 07:02:52', 336.00, 'returned'),
(69, 9, 39, '2024-11-10 15:01:59', '2024-01-01 08:01:59', '2024-12-10 08:07:12', 345.00, 'returned'),
(70, 29, 39, '2024-12-12 20:46:06', '2024-12-15 13:46:06', '2024-12-16 13:48:50', 2.00, 'returned'),
(71, 29, 31, '2024-12-12 20:46:09', '2024-12-15 13:46:09', '2024-12-16 13:49:41', 2.00, 'returned'),
(72, 29, 30, '2024-12-12 20:46:10', '2024-12-15 13:46:10', '2024-12-12 13:51:56', 0.00, 'returned'),
(73, 29, 39, '2024-12-12 20:52:46', '2024-12-15 13:52:46', '2024-12-16 14:21:54', 2.00, 'returned'),
(74, 29, 38, '2024-12-12 21:20:38', '2024-12-15 14:20:38', '2024-12-16 14:22:15', 2.00, 'returned'),
(75, 29, 33, '2024-12-12 21:20:42', '2024-12-15 14:20:42', '2024-12-16 14:22:17', 2.00, 'returned'),
(76, 29, 39, '2024-12-16 21:26:14', '2024-12-19 14:26:14', '2024-12-20 14:27:26', 2.00, 'returned'),
(77, 29, 38, '2024-12-16 21:26:37', '2024-12-19 14:26:37', '2024-12-20 14:29:29', 2.00, 'returned'),
(78, 29, 33, '2024-12-16 21:26:39', '2024-12-19 14:26:39', '2024-12-20 14:29:52', 2.00, 'returned'),
(79, 29, 31, '2024-12-12 21:33:34', '2024-12-15 14:33:34', '2024-12-16 14:34:19', 2.00, 'returned'),
(80, 29, 38, '2024-12-12 21:33:37', '2024-12-15 14:33:37', '2024-12-16 14:34:35', 2.00, 'returned'),
(81, 29, 33, '2024-12-12 21:33:40', '2024-12-15 14:33:40', '2024-12-16 14:34:33', 2.00, 'returned');

-- --------------------------------------------------------

--
-- Table structure for table `fine_configurations`
--

CREATE TABLE `fine_configurations` (
  `config_id` int(11) NOT NULL,
  `resource_type` varchar(20) NOT NULL,
  `fine_amount` decimal(10,2) NOT NULL DEFAULT 1.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fine_configurations`
--

INSERT INTO `fine_configurations` (`config_id`, `resource_type`, `fine_amount`, `updated_at`) VALUES
(1, 'book', 10.00, '2024-12-12 13:33:08'),
(2, 'periodical', 15.00, '2024-12-12 13:33:08'),
(3, 'media', 20.00, '2024-12-12 13:33:08');

-- --------------------------------------------------------

--
-- Table structure for table `fine_payments`
--

CREATE TABLE `fine_payments` (
  `payment_id` int(11) NOT NULL,
  `borrowing_id` int(11) NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `library_resources`
--

CREATE TABLE `library_resources` (
  `resource_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `accession_number` varchar(20) NOT NULL,
  `category` varchar(50) NOT NULL,
  `status` enum('available','borrowed','maintenance') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `cover_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `library_resources`
--

INSERT INTO `library_resources` (`resource_id`, `title`, `accession_number`, `category`, `status`, `created_at`, `updated_at`, `cover_image`) VALUES
(20, 'The Greatest', 'P-2024-003', 'Magazine', 'available', '2024-12-02 14:32:07', '2024-12-09 12:38:32', 'uploads/covers/cover_67554b0c79270.png'),
(28, 'Dune', 'B-2024-001', 'Fiction', 'available', '2024-12-08 07:43:07', '2024-12-08 16:19:27', 'uploads/covers/cover_67554e0b1a1f0.jpg'),
(29, 'The alchemist', 'B-2024-002', 'Academic', 'available', '2024-12-08 07:46:23', '2024-12-10 05:01:19', 'uploads/covers/cover_67554ecf566bb.jpg'),
(30, 'The divine comedy', 'B-2024-004', 'Fiction', 'available', '2024-12-08 07:52:19', '2024-12-12 12:51:56', 'uploads/covers/cover_675552c3812fa.jpg'),
(31, 'How to kill a mocking bird', 'B-2024-005', 'Non-Fiction', 'available', '2024-12-08 08:04:40', '2024-12-16 13:34:19', 'uploads/covers/cover_67555318caf06.jpg'),
(32, 'Wow signal', 'R-2024-001', 'Documentary', 'available', '2024-12-08 08:08:04', '2024-12-10 05:41:10', 'uploads/covers/cover_675553e45bf98.jpg'),
(33, 'The edge of all known', 'R-2024-002', 'Documentary', 'available', '2024-12-08 08:12:17', '2024-12-16 13:34:33', 'uploads/covers/cover_675554e11d962.jpg'),
(34, 'tfvhfasf', 'B-2024-006', 'Fiction', '', '2024-12-09 12:26:58', '2024-12-09 12:33:30', NULL),
(35, 'test', 'P-2024-004', 'Newsletter', '', '2024-12-09 12:35:33', '2024-12-09 12:36:02', NULL),
(36, 'test1', 'P-2024-005', 'Other', '', '2024-12-09 12:38:00', '2024-12-09 12:38:40', NULL),
(37, 'test', 'R-2024-003', 'Reference', '', '2024-12-09 12:58:43', '2024-12-09 13:00:34', NULL),
(38, 'Joestar', 'P-2024-006', 'Magazine', 'available', '2024-12-09 13:05:12', '2024-12-16 13:34:35', 'uploads/covers/cover_6756eb0810e2d.png'),
(39, 'information management', 'B-2024-007', 'Non-Fiction', 'available', '2024-12-10 06:59:05', '2024-12-20 13:27:26', NULL),
(40, 'information managemen1', 'P-2024-007', 'Academic Journal', 'available', '2024-12-10 06:59:48', '2024-12-10 06:59:48', NULL),
(41, 'info management', 'R-2024-004', 'Academic', 'available', '2024-12-10 07:00:17', '2024-12-10 07:00:17', NULL);

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
(11, 33, 'CD', 99, 'Video'),
(13, 41, 'DVD', 12, 'Video');

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
(11, 38, '12121212', '1', '1', '2000-02-12'),
(12, 40, '12121212', '1', '1', '2000-12-12');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `membership_id`, `username`, `password`, `first_name`, `last_name`, `email`, `role`, `max_books`, `created_at`, `updated_at`) VALUES
(9, NULL, 'user1', '$2y$10$ikFVY6FlstJFUvrG.lL9seZd7QEvwa0EPZHShzYK2ovBONcmGdh9q', 'John', 'Doe', 'user1@gmail.com', 'faculty', 5, '2024-11-23 11:14:11', '2024-12-08 08:15:35'),
(10, NULL, 'admin1', '$2y$10$EOgSJ.NhTX4KtWmPK45aVeF3ylHV5WQCsIw5MDc8Y95vfbBZf0uyi', 'dsad', 'asdasd', 'itsebs758@gmail.com', 'admin', 10, '2024-11-23 11:19:06', '2024-12-06 14:07:40'),
(28, 'S20244337', 'staff1', '$2y$10$zYxXz2B58es5nT/itaiF7Oo8yujpQwYruvgHb99UVP2Jg/7KvswJa', 'shaghjsa', 'hghasdgfas', 'staff1@gmail.com', 'staff', 4, '2024-12-10 06:54:07', '2024-12-12 12:26:20'),
(29, 'S20249490', 'student1', '$2y$10$SZVcfAfudYUZ1BTcrW0XSuOKwVJVv1uzDw56kQgNF9L49lqiFtXme', 'sdaasd', 'asasd', 'student1@gmail.com', 'student', 3, '2024-12-10 06:54:32', '2024-12-12 12:27:32'),
(30, 'F20248589', 'faculty1', '$2y$10$.6M.jyeMPjv1tsKVpcLkquGhWSeCchnZKTVVA1r00nmoM9m71CPnq', 'dsad', 'asdasd', 'faculty1@gmail.com', 'faculty', 5, '2024-12-10 06:54:59', '2024-12-10 06:54:59');

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
  ADD KEY `resource_id` (`resource_id`);

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
  ADD KEY `borrowing_id` (`borrowing_id`);

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
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `borrowings`
--
ALTER TABLE `borrowings`
  MODIFY `borrowing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `fine_configurations`
--
ALTER TABLE `fine_configurations`
  MODIFY `config_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `fine_payments`
--
ALTER TABLE `fine_payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `library_resources`
--
ALTER TABLE `library_resources`
  MODIFY `resource_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `media_resources`
--
ALTER TABLE `media_resources`
  MODIFY `media_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `periodicals`
--
ALTER TABLE `periodicals`
  MODIFY `periodical_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
  ADD CONSTRAINT `borrowings_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `library_resources` (`resource_id`);

--
-- Constraints for table `fine_payments`
--
ALTER TABLE `fine_payments`
  ADD CONSTRAINT `fine_payments_ibfk_1` FOREIGN KEY (`borrowing_id`) REFERENCES `borrowings` (`borrowing_id`);

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
