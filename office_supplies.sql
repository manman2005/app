-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 30, 2025 at 04:39 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `office_supplies`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`) VALUES
(1, 'เครื่องเขียน', '2025-05-23 01:42:01'),
(2, 'กระดาษ', '2025-05-23 01:42:01'),
(3, 'อุปกรณ์สำนักงาน', '2025-05-23 01:42:01'),
(5, 'รองเท้าบูส', '2025-05-23 03:27:07');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `stock_qty` int(11) NOT NULL DEFAULT 0,
  `min_qty` int(11) NOT NULL DEFAULT 5,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `name`, `category_id`, `description`, `stock_qty`, `min_qty`, `created_at`, `updated_at`) VALUES
(1, 'ปากกาลูกลื่น', 1, 'ปากกาลูกลื่นสีน้ำเงิน', 99, 20, '2025-05-23 01:42:01', '2025-05-23 09:07:46'),
(2, 'ดินสอ 2B', 1, 'ดินสอดำ 2B', 39, 10, '2025-05-23 01:42:01', '2025-05-28 06:43:39'),
(3, 'กระดาษ A4', 2, 'กระดาษ A4 80 แกรม', 4999, 100, '2025-05-23 01:42:01', '2025-05-23 09:07:46'),
(4, 'แฟ้มเอกสาร', 3, 'แฟ้มเอกสารสีน้ำเงิน', 0, 5, '2025-05-23 01:42:01', '2025-05-23 03:47:33'),
(5, 'รองเท้าบูสข้อสูง', 5, ' 1 คือ คู่', 29, 1, '2025-05-23 03:28:16', '2025-05-23 09:07:46');

-- --------------------------------------------------------

--
-- Table structure for table `requisitions`
--

CREATE TABLE `requisitions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `requisitions`
--

INSERT INTO `requisitions` (`id`, `user_id`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'approved', '\n', '2025-05-23 01:48:23', '2025-05-23 01:50:42'),
(2, 3, 'rejected', 'ต้องการใช้\n', '2025-05-23 01:52:59', '2025-05-23 02:36:46'),
(3, 3, 'rejected', '\n', '2025-05-23 02:31:26', '2025-05-23 02:36:44'),
(4, 3, 'approved', 'ขอเบิกหน่อย\n', '2025-05-23 02:38:49', '2025-05-23 03:45:18'),
(5, 3, 'approved', '\n', '2025-05-23 02:40:37', '2025-05-23 03:45:17'),
(6, 3, 'approved', '\n', '2025-05-23 02:45:11', '2025-05-23 03:45:18'),
(7, 1, 'approved', 'ขอหน่อยคั้บ\n', '2025-05-23 03:42:18', '2025-05-23 03:45:17'),
(8, 1, 'approved', 'ขอหน่อยคั้บ\n', '2025-05-23 03:45:00', '2025-05-23 03:45:15'),
(9, 1, 'approved', '\n', '2025-05-23 03:47:15', '2025-05-23 03:47:33'),
(10, 1, 'rejected', '\n', '2025-05-23 03:51:29', '2025-05-23 06:24:42'),
(11, 1, 'rejected', 'tctycy\n', '2025-05-23 06:25:00', '2025-05-23 06:25:22'),
(12, 3, 'approved', '\n', '2025-05-23 09:03:11', '2025-05-23 09:03:21'),
(13, 1, 'approved', '\n', '2025-05-23 09:06:20', '2025-05-23 09:07:46'),
(14, 8, 'approved', '\n', '2025-05-28 06:41:50', '2025-05-28 06:43:39'),
(15, 4, 'pending', '', '2025-05-29 04:05:37', '2025-05-29 04:05:37');

-- --------------------------------------------------------

--
-- Table structure for table `requisition_items`
--

CREATE TABLE `requisition_items` (
  `id` int(11) NOT NULL,
  `requisition_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `requisition_items`
--

INSERT INTO `requisition_items` (`id`, `requisition_id`, `item_id`, `qty`, `created_at`) VALUES
(1, 1, 3, 5, '2025-05-23 01:48:23'),
(2, 1, 4, 5, '2025-05-23 01:48:23'),
(3, 2, 3, 10, '2025-05-23 01:52:59'),
(4, 3, 4, 2, '2025-05-23 02:31:26'),
(5, 4, 3, 10, '2025-05-23 02:38:49'),
(6, 5, 3, 19, '2025-05-23 02:40:37'),
(7, 6, 3, 1, '2025-05-23 02:45:11'),
(8, 7, 3, 300, '2025-05-23 03:42:18'),
(9, 8, 3, 300, '2025-05-23 03:45:00'),
(10, 9, 4, 25, '2025-05-23 03:47:15'),
(11, 10, 3, 67, '2025-05-23 03:51:29'),
(12, 11, 3, 4500, '2025-05-23 06:25:00'),
(13, 12, 5, 10, '2025-05-23 09:03:11'),
(14, 13, 3, 1, '2025-05-23 09:06:20'),
(15, 13, 5, 1, '2025-05-23 09:06:20'),
(16, 13, 2, 1, '2025-05-23 09:06:20'),
(17, 13, 1, 1, '2025-05-23 09:06:20'),
(18, 14, 2, 10, '2025-05-28 06:41:50'),
(19, 15, 3, 1, '2025-05-29 04:05:37'),
(20, 15, 5, 1, '2025-05-29 04:05:37'),
(21, 15, 1, 1, '2025-05-29 04:05:37');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2025-05-23 01:42:01', '2025-05-23 01:42:01'),
(2, 'Regular User', 'user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user', '2025-05-23 01:42:01', '2025-05-23 01:42:01'),
(3, 'user_man', 'sagase66@gmail.com', '$2y$10$xkXnmAjdpYknXLZvFN9oO.oRadWY8BJwO/pY9zCf8nA0Mzrd7EXiq', 'user', '2025-05-23 01:52:23', '2025-05-23 01:52:23'),
(4, 'nattaphon Lonun', 'mza641888@gmail.com', '$2y$10$vFVvkBATq4oR0Um/Y1ukbu889NTE0sPglS2na3PCCg.YQ4KWmfdvK', 'user', '2025-05-23 04:15:50', '2025-05-23 04:15:50'),
(6, 'nattaphon Lonun', 'sagsae66@gmail.com', '$2y$10$FwnG08vafx4s8Yf.fBvv0ejzQxw9X1j0TgC4CCRita8expE3gyYNq', 'user', '2025-05-28 04:23:10', '2025-05-28 04:23:10'),
(8, 'mannnn', '123133@gmail.com', '$2y$10$MHZI8xRPyDivsCChh2C4neAj/X1VGfoNMOxuR08p0jnz/EPi/1InG', 'user', '2025-05-28 04:27:53', '2025-05-28 04:27:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `requisitions`
--
ALTER TABLE `requisitions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `requisition_items`
--
ALTER TABLE `requisition_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `requisition_id` (`requisition_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `requisitions`
--
ALTER TABLE `requisitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `requisition_items`
--
ALTER TABLE `requisition_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `requisitions`
--
ALTER TABLE `requisitions`
  ADD CONSTRAINT `requisitions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `requisition_items`
--
ALTER TABLE `requisition_items`
  ADD CONSTRAINT `requisition_items_ibfk_1` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`),
  ADD CONSTRAINT `requisition_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
