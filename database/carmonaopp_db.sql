-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 26, 2026 at 12:00 AM
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
-- Database: `carmonaopp_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `cleanup_expired_password_resets` ()   BEGIN
    DELETE FROM password_resets 
    WHERE expires_at < NOW() OR used = 1;
    
    SELECT ROW_COUNT() as deleted_count;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `log_activity` (IN `p_user_id` INT, IN `p_action` VARCHAR(100), IN `p_description` TEXT, IN `p_details` JSON, IN `p_ip_address` VARCHAR(45))   BEGIN
    INSERT INTO activity_logs (user_id, action, description, details, ip_address, created_at)
    VALUES (p_user_id, p_action, p_description, p_details, p_ip_address, NOW());
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 3, 'Logout', 'User logged out', NULL, '::1', NULL, '2025-12-30 08:50:24'),
(2, 2, 'Login', 'User logged in', NULL, '::1', NULL, '2025-12-30 08:50:29'),
(3, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-11 06:19:47'),
(4, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-11 06:19:53'),
(5, 3, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-11 06:20:00'),
(6, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-11 06:20:01'),
(7, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-11 06:21:16'),
(8, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-11 06:21:17'),
(9, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-22 05:05:11'),
(10, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-22 05:05:21'),
(11, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-22 05:05:52'),
(12, 3, 'Add Service', 'Added service: hehe', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-22 05:17:16'),
(13, 3, 'Delete Service', 'Deleted service ID: 106', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-22 05:17:29'),
(14, 3, 'Update Department', 'Updated department ID: 66', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-22 05:17:50'),
(15, 3, 'Update Department', 'Updated department ID: 66', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-22 05:17:55'),
(16, 3, 'Add Department', 'Added department: Keith Justine', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-22 05:22:25'),
(17, 3, 'Delete Department', 'Deleted department ID: 67', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-22 05:22:27'),
(18, 3, 'Add Department', 'Added department: f', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-22 05:28:00'),
(19, 3, 'Delete Department', 'Deleted department ID: 68', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-22 05:28:03'),
(20, 3, 'Add Department', 'Added department: 34', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-22 05:31:35'),
(21, 3, 'Delete Department', 'Deleted department ID: 69', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-22 05:31:44'),
(22, 3, 'Add Department', 'Added department: 5', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-22 05:31:57'),
(23, 3, 'Delete Department', 'Deleted department ID: 70', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-22 05:32:00'),
(24, 3, 'Add Department', 'Added department: f', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-22 05:32:11'),
(25, 3, 'Delete Department', 'Deleted department ID: 71', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-22 05:32:20'),
(26, 3, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-22 05:57:30'),
(27, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-22 05:57:35'),
(28, 2, 'Submit Application', 'Submitted application: CRMN-2026-309487 for Request Data for Thesis/Research', NULL, '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', '2026-01-22 06:15:14'),
(29, 2, 'Submit Application', 'Submitted application: CRMN-2026-137077 for Issuance of Certification', NULL, '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', '2026-01-22 06:15:48'),
(30, 2, 'Submit Application', 'Submitted application: CRMN-2026-012806 for Issuance of Certification', NULL, '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', '2026-01-22 06:18:27'),
(31, 2, 'Submit Application', 'Submitted application: CRMN-2026-483625 for Mayor\'s Endorsement', NULL, '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', '2026-01-22 06:20:24'),
(32, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', '2026-01-22 06:20:28'),
(33, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', '2026-01-22 06:20:35'),
(34, 3, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-22 10:57:16'),
(35, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-22 10:57:33'),
(36, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-22 11:00:33'),
(37, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-22 11:29:30'),
(38, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-22 11:29:43'),
(39, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-22 11:29:52'),
(40, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-22 12:00:18'),
(41, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-23 02:05:16'),
(42, 2, 'Submit Application', 'Submitted application: CRMN-2026-403300 for Mayor\'s Endorsement', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-23 02:10:22'),
(43, 3, 'Update Application Status', 'Updated application CRMN-2026-403300 from Approved to Completed', '{\"application_id\":5,\"tracking_number\":\"CRMN-2026-403300\",\"old_status\":\"Approved\",\"new_status\":\"Completed\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 02:36:35'),
(44, 2, 'Submit Application', 'Submitted application: CRMN-2026-994356 for Tricycle Franchise Dropping', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-23 08:00:04'),
(45, 2, 'Submit Payment', 'Submitted payment for application CRMN-2026-994356 - Status changed to Paid', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-23 08:15:09'),
(46, 3, 'Update Application Status', 'Updated application CRMN-2026-994356 from Paid to Completed', '{\"application_id\":6,\"tracking_number\":\"CRMN-2026-994356\",\"old_status\":\"Paid\",\"new_status\":\"Completed\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 08:16:11'),
(47, 3, 'UNAUTHORIZED_ACCESS', 'Attempt to access user dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-23 16:28:03\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 08:28:03'),
(48, 3, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 08:28:07'),
(49, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 08:28:13'),
(50, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-23 16:28:13\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 08:28:13'),
(51, 2, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-23 16:30:34\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 08:30:34'),
(52, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 08:30:38'),
(53, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 08:30:42'),
(54, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-23 16:30:42\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 08:30:42'),
(55, 3, 'Verify Payment', 'Verified payment for application CRMN-2026-994356', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 08:38:14'),
(56, 3, 'Verify Payment', 'Verified payment for application CRMN-2026-012806', NULL, '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', '2026-01-23 08:38:33'),
(57, 3, 'Reject Payment', 'Rejected payment for application CRMN-2026-483625 - Status reverted to Approved', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 08:39:53'),
(58, 3, 'Verify Payment', 'Verified payment for application CRMN-2026-403300', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 08:40:22'),
(59, 2, 'Submit Application', 'Submitted application: CRMN-2026-353892 for Mayor\'s Endorsement', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-23 08:41:15'),
(60, 3, 'Update Application Status', 'Updated application CRMN-2026-353892 from Pending to Processing', '{\"application_id\":7,\"tracking_number\":\"CRMN-2026-353892\",\"old_status\":\"Pending\",\"new_status\":\"Processing\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 08:41:44'),
(61, 2, 'Submit Application', 'Submitted application: CRMN-2026-289462 for Feedback Processing', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-23 08:56:10'),
(62, 2, 'Submit Application', 'Submitted application: CRMN-2026-813237 for Feedback Processing', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-23 09:53:37'),
(63, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-23 10:39:15'),
(64, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-23 18:39:15\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-23 10:39:15'),
(65, 2, 'Submit Payment', 'Submitted payment for application CRMN-2026-813237 - Status changed to Paid', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-23 10:39:40'),
(66, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-23 19:44:01\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 11:44:01'),
(67, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 11:44:16'),
(68, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-23 19:44:16\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 11:44:16'),
(69, 3, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 11:47:37'),
(70, 0, 'Registration', 'New user registered', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 11:48:26'),
(71, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 11:54:16'),
(72, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-23 19:54:16\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 11:54:16'),
(73, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 17:50:25'),
(74, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-24 01:50:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 17:50:25'),
(75, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-23 18:41:59'),
(76, NULL, 'PASSWORD_RESET_REQUEST', 'Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-24 02:42:07\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-23 18:42:07'),
(77, 2, 'password_reset', 'Password was reset', NULL, '::1', NULL, '2026-01-23 18:43:14'),
(78, 3, 'PASSWORD_RESET_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-24 02:43:14\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 18:43:14'),
(79, NULL, 'PASSWORD_RESET_REQUEST', 'Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-24 03:35:26\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-23 19:35:26'),
(80, 2, 'password_reset', 'Password was reset', NULL, '::1', NULL, '2026-01-23 19:36:17'),
(81, 3, 'PASSWORD_RESET_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-24 03:36:17\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 19:36:17'),
(82, 0, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 20:30:18'),
(83, 0, 'LOGIN_SUCCESS', 'User ID: 0, Email: keithjustine944@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-24 04:30:18\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 20:30:18'),
(84, 0, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 20:30:31'),
(85, 0, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 20:39:10'),
(86, 0, 'LOGIN_SUCCESS', 'User ID: 0, Email: keithjustine944@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-24 04:39:10\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 20:39:10'),
(87, 0, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 20:39:17'),
(88, NULL, 'LOGIN_FAILURE', 'Invalid password for: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-24 04:39:22\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 20:39:22'),
(89, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 20:39:26'),
(90, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-24 04:39:26\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 20:39:26'),
(91, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 20:50:51'),
(92, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 20:50:56'),
(93, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-24 04:50:56\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-23 20:50:56'),
(94, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 03:20:23'),
(95, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-24 11:20:23\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 03:20:23'),
(96, 3, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 03:21:31'),
(97, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 03:21:40'),
(98, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-24 11:21:40\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 03:21:40'),
(99, NULL, 'CSRF_FAILURE', 'Forgot password with invalid CSRF token', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-24 14:08:06\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-24 06:08:06'),
(100, NULL, 'LOGIN_FAILURE', 'Invalid password for: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-24 14:08:15\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-24 06:08:15'),
(101, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-24 06:08:24'),
(102, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-24 14:08:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-24 06:08:24'),
(103, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-24 06:09:09'),
(104, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-24 14:09:09\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-24 06:09:09'),
(105, NULL, 'LOGIN_FAILURE', 'Invalid password for: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-24 14:09:34\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-24 06:09:34'),
(106, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-24 06:09:38'),
(107, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-24 14:09:38\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-24 06:09:38'),
(108, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-24 06:11:39'),
(109, NULL, 'LOGIN_FAILURE', 'Invalid password for: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-24 14:11:48\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-24 06:11:48'),
(110, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-24 06:11:52'),
(111, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-24 14:11:52\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-24 06:11:52'),
(112, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 10:03:05'),
(113, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-24 18:03:05\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 10:03:05'),
(114, NULL, 'LOGIN_FAILURE', 'Invalid password for: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-24 18:04:01\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 10:04:01'),
(115, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 10:04:05'),
(116, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-24 18:04:05\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 10:04:05'),
(117, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 10:12:40'),
(118, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 10:12:45'),
(119, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-24 18:12:45\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 10:12:45'),
(120, 3, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 10:16:11'),
(121, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 10:16:15'),
(122, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-24 18:16:15\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 10:16:15'),
(123, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 11:04:17'),
(124, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 11:04:20'),
(125, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-24 19:04:20\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 11:04:20'),
(126, 3, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 11:06:42'),
(127, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 11:06:44'),
(128, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-24 19:06:44\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 11:06:44'),
(129, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 11:20:29'),
(130, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 11:20:32'),
(131, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-24 19:20:32\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 11:20:32'),
(132, 3, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 11:20:43'),
(133, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 11:20:45'),
(134, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-24 19:20:45\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 11:20:45'),
(135, 3, 'Verify Payment', 'Verified payment for application CRMN-2026-813237', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 11:21:35'),
(136, 3, 'Update Application Status', 'Updated application CRMN-2026-813237 from Paid to Completed', '{\"application_id\":9,\"tracking_number\":\"CRMN-2026-813237\",\"old_status\":\"Paid\",\"new_status\":\"Completed\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 11:25:17'),
(137, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 11:26:36'),
(138, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 11:26:43'),
(139, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-24 19:26:43\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 11:26:43'),
(140, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 13:04:12'),
(141, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-24 21:04:12\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 13:04:12'),
(142, 2, 'Submit Payment', 'Submitted payment for application CRMN-2026-289462 - Status changed to Paid', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 13:04:27'),
(143, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 14:45:47'),
(144, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-24 22:45:47\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 14:45:47'),
(145, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 14:46:03'),
(146, NULL, 'LOGIN_FAILURE', 'Invalid password for: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-24 22:46:09\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 14:46:09'),
(147, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 14:46:11'),
(148, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-24 22:46:11\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 14:46:11'),
(149, 3, 'Verify Payment', 'Verified payment for application CRMN-2026-289462', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 14:46:17'),
(150, 3, 'Update Application Status', 'Updated application CRMN-2026-289462 from Paid to Completed', '{\"application_id\":8,\"tracking_number\":\"CRMN-2026-289462\",\"old_status\":\"Paid\",\"new_status\":\"Completed\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 14:46:58'),
(151, 3, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 14:47:12'),
(152, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-24 22:53:10\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 14:53:10'),
(153, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 14:53:12'),
(154, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-24 22:53:12\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 14:53:12'),
(155, 2, 'Submit Application', 'Submitted application: CRMN-2026-544595 for OTR (Occupational Tax Receipt)', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 14:53:46'),
(156, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 14:54:02'),
(157, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 14:54:06'),
(158, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-24 22:54:06\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-24 14:54:06'),
(159, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 19:26:17'),
(160, NULL, 'LOGIN_FAILURE', 'User not found: admin@lgu.gov', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-25 03:26:41\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 19:26:41'),
(161, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 19:27:13'),
(162, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-25 03:27:13\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 19:27:13'),
(163, 3, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 19:46:42'),
(164, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 19:46:43'),
(165, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-25 03:46:43\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 19:46:43'),
(166, 2, 'Submit Payment', 'Submitted payment for application CRMN-2026-544595 - Status changed to Paid', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 19:47:03'),
(167, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 19:47:10'),
(168, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 19:47:15'),
(169, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-25 03:47:15\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 19:47:15'),
(170, 3, 'Verify Payment', 'Verified payment for application CRMN-2026-544595', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 19:47:36'),
(171, 3, 'Update Application Status', 'Updated application CRMN-2026-544595 from Paid to Completed', '{\"application_id\":10,\"tracking_number\":\"CRMN-2026-544595\",\"old_status\":\"Paid\",\"new_status\":\"Completed\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 19:47:54'),
(172, 3, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 19:48:06'),
(173, 0, 'Registration', 'New user registered', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 19:48:56'),
(174, 0, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 19:49:10'),
(175, 0, 'LOGIN_SUCCESS', 'User ID: 0, Email: keichoo57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-25 03:49:10\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 19:49:10'),
(176, 0, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 20:43:01'),
(177, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 20:43:05'),
(178, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-25 04:43:05\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 20:43:05'),
(179, 3, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 21:47:04'),
(180, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 21:47:07'),
(181, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-25 05:47:07\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 21:47:07');
INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(182, 2, 'Submit Payment', 'Submitted payment for application CRMN-2026-353892 - Status changed to Paid', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 21:47:23'),
(183, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 21:47:30'),
(184, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 21:47:34'),
(185, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-25 05:47:34\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 21:47:34'),
(186, 3, 'Verify Payment', 'Verified payment for application CRMN-2026-353892', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 21:48:13'),
(187, 3, 'Add Department', 'Added department: Keith Justine', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 22:43:42'),
(188, 3, 'Add Service', 'Added service: 51234', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 22:44:02'),
(189, 3, 'Delete Service', 'Deleted service ID: 107', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 22:44:20'),
(190, 3, 'Update Application Status', 'Updated application CRMN-2026-544595 from Completed to Rejected', '{\"application_id\":10,\"tracking_number\":\"CRMN-2026-544595\",\"old_status\":\"Completed\",\"new_status\":\"Rejected\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 22:55:17'),
(191, 3, 'Update Application Status', 'Updated application CRMN-2026-544595 from Rejected to Completed', '{\"application_id\":10,\"tracking_number\":\"CRMN-2026-544595\",\"old_status\":\"Rejected\",\"new_status\":\"Completed\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-24 23:05:45'),
(192, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-25 08:13:20\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 00:13:20'),
(193, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 00:13:32'),
(194, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-25 08:13:32\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 00:13:32'),
(195, 3, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 00:22:26'),
(196, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 00:22:28'),
(197, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-25 08:22:28\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 00:22:28'),
(198, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 07:25:19'),
(199, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-25 15:25:20\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 07:25:20'),
(200, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 07:26:00'),
(201, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-25 16:02:44\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 08:02:44'),
(202, NULL, 'LOGIN_FAILURE', 'Invalid password for: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-25 16:02:49\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 08:02:49'),
(203, NULL, 'LOGIN_FAILURE', 'Invalid password for: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-25 16:02:51\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 08:02:51'),
(204, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 08:02:53'),
(205, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-25 16:02:53\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 08:02:53'),
(206, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 10:33:09'),
(207, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-25 18:33:09\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 10:33:09'),
(208, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 10:34:14'),
(209, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-25 18:34:14\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 10:34:14'),
(210, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 10:49:56'),
(211, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 10:50:02'),
(212, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-25 18:50:02\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 10:50:02'),
(213, 3, 'Delete Department', 'Deleted department ID: 72', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 10:52:51'),
(214, 3, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 10:53:55'),
(215, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 10:53:58'),
(216, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-25 18:53:58\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 10:53:58'),
(217, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 10:55:03'),
(218, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-25 18:55:03\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 10:55:03'),
(219, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 11:14:37'),
(220, NULL, 'LOGIN_FAILURE', 'Invalid password for: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-25 19:14:41\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 11:14:41'),
(221, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 11:14:45'),
(222, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-25 19:14:45\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 11:14:45'),
(223, 3, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 11:31:05'),
(224, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 11:31:08'),
(225, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-25 19:31:08\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 11:31:08'),
(226, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 13:06:51'),
(227, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-25 21:06:51\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 13:06:51'),
(228, 2, 'Submit Application', 'Submitted application: CRMN-2026-065156 for Feedback Processing', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 13:07:07'),
(229, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 13:07:42'),
(230, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 13:07:46'),
(231, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-25 21:07:46\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 13:07:46'),
(232, 3, 'Update Application Status', 'Updated application CRMN-2026-065156 from Pending to Processing', '{\"application_id\":11,\"tracking_number\":\"CRMN-2026-065156\",\"old_status\":\"Pending\",\"new_status\":\"Processing\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 13:08:23'),
(233, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 13:08:44'),
(234, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 13:08:45'),
(235, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-25 21:08:45\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 13:08:45'),
(236, 3, 'Update Application Status', 'Updated application CRMN-2026-065156 from Processing to Approved', '{\"application_id\":11,\"tracking_number\":\"CRMN-2026-065156\",\"old_status\":\"Processing\",\"new_status\":\"Approved\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 13:09:04'),
(237, 2, 'Submit Application', 'Submitted application: CRMN-2026-520447 for Professional Tax Receipt', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 13:10:07'),
(238, 3, 'Update Application Status', 'Updated application CRMN-2026-520447 from Pending to Approved', '{\"application_id\":12,\"tracking_number\":\"CRMN-2026-520447\",\"old_status\":\"Pending\",\"new_status\":\"Approved\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 13:10:32'),
(239, 2, 'Submit Payment', 'Submitted payment for application CRMN-2026-483625 - Status changed to Paid', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 13:12:34'),
(240, 3, 'Reject Payment', 'Rejected payment for application CRMN-2026-483625 - Status reverted to Approved', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 13:12:54'),
(241, 3, 'Update Application Status', 'Updated application CRMN-2026-065156 from Approved to Processing', '{\"application_id\":11,\"tracking_number\":\"CRMN-2026-065156\",\"old_status\":\"Approved\",\"new_status\":\"Processing\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 13:17:35'),
(242, 3, 'Update Application Status', 'Updated application CRMN-2026-065156 from Processing to Approved', '{\"application_id\":11,\"tracking_number\":\"CRMN-2026-065156\",\"old_status\":\"Processing\",\"new_status\":\"Approved\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 13:17:56'),
(243, 3, 'Update Application Status', 'Updated application CRMN-2026-544595 from Completed to Approved', '{\"application_id\":10,\"tracking_number\":\"CRMN-2026-544595\",\"old_status\":\"Completed\",\"new_status\":\"Approved\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 13:18:46'),
(244, 3, 'Update Application Status', 'Updated application CRMN-2026-520447 from Approved to Processing', '{\"application_id\":12,\"tracking_number\":\"CRMN-2026-520447\",\"old_status\":\"Approved\",\"new_status\":\"Processing\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 13:20:20'),
(245, 3, 'Update Application Status', 'Updated application CRMN-2026-520447 from Processing to Approved', '{\"application_id\":12,\"tracking_number\":\"CRMN-2026-520447\",\"old_status\":\"Processing\",\"new_status\":\"Approved\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 13:20:28'),
(246, 2, 'Submit Application', 'Submitted application: CRMN-2026-673697 for OTR (Occupational Tax Receipt)', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 13:20:59'),
(247, 3, 'Update Application Status', 'Updated application CRMN-2026-673697 from Pending to Approved', '{\"application_id\":13,\"tracking_number\":\"CRMN-2026-673697\",\"old_status\":\"Pending\",\"new_status\":\"Approved\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 13:21:15'),
(248, 3, 'Update Application Status', 'Updated application CRMN-2026-673697 from Approved to Pending', '{\"application_id\":13,\"tracking_number\":\"CRMN-2026-673697\",\"old_status\":\"Approved\",\"new_status\":\"Pending\",\"email_sent\":false,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 13:26:49'),
(249, 3, 'Update Application Status', 'Updated application CRMN-2026-673697 from Pending to Approved', '{\"application_id\":13,\"tracking_number\":\"CRMN-2026-673697\",\"old_status\":\"Pending\",\"new_status\":\"Approved\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 13:26:57'),
(250, 3, 'Update Application Status', 'Updated application CRMN-2026-673697 from Approved to Completed', '{\"application_id\":13,\"tracking_number\":\"CRMN-2026-673697\",\"old_status\":\"Approved\",\"new_status\":\"Completed\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 13:28:58'),
(251, 2, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-25 21:52:42\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 13:52:42'),
(252, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 16:24:12'),
(253, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-26 00:24:12\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 16:24:12'),
(254, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 16:24:15'),
(255, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 16:24:20'),
(256, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-26 00:24:20\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 16:24:20'),
(257, NULL, 'CSRF_FAILURE', 'Login attempt with invalid CSRF token', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-26 02:17:30\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 18:17:30'),
(258, NULL, 'LOGIN_FAILURE', 'Invalid password for: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-26 02:17:32\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 18:17:32'),
(259, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 18:17:34'),
(260, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-26 02:17:34\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 18:17:34'),
(261, 2, 'Submit Application', 'Submitted application: CRMN-2026-462816 for Tricycle Franchise Dropping', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 18:18:09'),
(262, 2, 'Submit Application', 'Submitted application: CRMN-2026-053670 for OTR (Occupational Tax Receipt)', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 18:21:31'),
(263, 3, 'clear_logs', 'Cleared activity logs older than 90 days (0 records deleted)', NULL, '::1', NULL, '2026-01-25 18:46:12'),
(264, 3, 'clear_logs', 'Cleared activity logs older than 90 days (0 records deleted)', NULL, '::1', NULL, '2026-01-25 18:46:22'),
(265, 3, 'clear_logs', 'Cleared activity logs older than 90 days (0 records deleted)', NULL, '::1', NULL, '2026-01-25 18:46:25'),
(266, 3, 'clear_logs', 'Cleared activity logs older than 90 days (0 records deleted)', NULL, '::1', NULL, '2026-01-25 18:46:28'),
(267, 3, 'clear_logs', 'Cleared activity logs older than 90 days (0 records deleted)', NULL, '::1', NULL, '2026-01-25 18:46:33'),
(268, 3, 'clear_logs', 'Cleared activity logs older than 90 days (0 records deleted)', NULL, '::1', NULL, '2026-01-25 18:46:38'),
(269, 3, 'clear_logs', 'Cleared activity logs older than 90 days (0 records deleted)', NULL, '::1', NULL, '2026-01-25 19:07:32'),
(270, 3, 'clear_logs', 'Cleared activity logs older than 90 days (0 records deleted)', NULL, '::1', NULL, '2026-01-25 19:07:34'),
(271, 3, 'clear_logs', 'Cleared activity logs older than 90 days (0 records deleted)', NULL, '::1', NULL, '2026-01-25 19:07:38'),
(272, 3, 'clear_logs', 'Cleared activity logs older than 90 days (0 records deleted)', NULL, '::1', NULL, '2026-01-25 19:07:42'),
(273, 3, 'clear_logs', 'Cleared activity logs older than 90 days (0 records deleted)', NULL, '::1', NULL, '2026-01-25 19:09:22'),
(274, 3, 'clear_logs', 'Cleared activity logs older than 90 days (0 records deleted)', NULL, '::1', NULL, '2026-01-25 19:09:48'),
(275, 2, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 20:09:50'),
(276, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 20:09:54'),
(277, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-26 04:09:54\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 20:09:54'),
(278, 3, 'Add Department', 'Added department: Admin2', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:16:33'),
(279, 3, 'Update Department', 'Updated department ID: 73', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:16:37'),
(280, 3, 'Update Department', 'Updated department ID: 73', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:16:40'),
(281, 3, 'Add Service', 'Added service: 241', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:16:53'),
(282, 3, 'Update Service', 'Updated service ID: 108', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:17:04'),
(283, 3, 'Update Service', 'Updated service ID: 108', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:17:17'),
(284, 3, 'Update Service', 'Updated service ID: 108', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:20:25'),
(285, 3, 'Delete Service', 'Deleted service ID: 108', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:20:47'),
(286, 3, 'Delete Department', 'Deleted department ID: 73', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:20:55'),
(287, 3, 'Add Service', 'Added service: r', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:24:19'),
(288, 3, 'Update Service', 'Updated service ID: 99', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:24:31'),
(289, 3, 'Add Department', 'Added department: f', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:26:34'),
(290, 3, 'Update Department', 'Updated department ID: 74', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:26:39'),
(291, 3, 'Add Service', 'Added service: f', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:26:49'),
(292, 3, 'Add Service', 'Added service: 4', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:27:07'),
(293, 3, 'Delete Service', 'Deleted service ID: 110', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:27:27'),
(294, 3, 'Delete Service', 'Deleted service ID: 111', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:27:30'),
(295, 3, 'Delete Department', 'Deleted department ID: 74', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:27:33'),
(296, 3, 'Add Department', 'Added department: 1', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:27:58'),
(297, 3, 'Delete Department', 'Deleted department ID: 75', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:28:01'),
(298, 3, 'Add Department', 'Added department: 1', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:29:55'),
(299, 3, 'Add Department', 'Added department: 2', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:30:04'),
(300, 3, 'Update Department', 'Updated department ID: 76', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:35:10'),
(301, 3, 'Update Department', 'Updated department ID: 77', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:35:19'),
(302, 3, 'Update Department', 'Updated department ID: 76', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:36:40'),
(303, 3, 'Update Department', 'Updated department ID: 77', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:36:44'),
(304, 3, 'Delete Department', 'Deleted department ID: 76', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:36:53'),
(305, 3, 'Add Service', 'Added service: 2', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:38:25'),
(306, 3, 'Update Service', 'Updated service ID: 112', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:41:41'),
(307, 3, 'Update Service', 'Updated service ID: 112', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:41:54'),
(308, 3, 'Delete Service', 'Deleted service ID: 112', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:42:49'),
(309, 3, 'Add Department', 'Added department: 1', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:43:53'),
(310, 3, 'Delete Department', 'Deleted department ID: 78', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:44:01'),
(311, 3, 'Delete Department', 'Deleted department ID: 77', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:44:05'),
(312, 3, 'Add Department', 'Added department: 24', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:44:56'),
(313, 3, 'Delete Department', 'Deleted department ID: 79', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:45:00'),
(314, 3, 'Add Department', 'Added department: 1', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:45:05'),
(315, 3, 'Update Department', 'Updated department ID: 80', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:45:07'),
(316, 3, 'Delete Department', 'Deleted department ID: 80', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:45:10'),
(317, 3, 'Add Service', 'Added service: 12', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:45:20'),
(318, 3, 'Update Service', 'Updated service ID: 113', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:45:25'),
(319, 3, 'Delete Service', 'Deleted service ID: 113', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 20:45:27'),
(320, 3, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 21:09:54'),
(321, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 21:10:00'),
(322, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-26 05:10:00\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 21:10:00'),
(323, 2, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 21:54:53'),
(324, 2, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/141.0.0.0 Safari\\/537.36 OPR\\/125.0.0.0\",\"timestamp\":\"2026-01-26 05:54:53\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 21:54:53'),
(325, 3, 'delete_user', 'Deleted user account: Keith Justine Ababao (keichoo57@gmail.com) - ID: 1', NULL, '::1', NULL, '2026-01-25 21:57:01'),
(326, 3, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 21:57:54'),
(327, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 21:58:00'),
(328, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-26 05:58:00\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 21:58:00'),
(329, 3, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 21:58:02'),
(330, 3, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 21:58:10'),
(331, 3, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-26 05:58:10\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 21:58:10'),
(332, 3, 'delete_user', 'Deleted user account: Keith Justine Ababao 1 (keithjustine59@gmail.com) - ID: 5', NULL, '::1', NULL, '2026-01-25 21:58:46'),
(333, 3, 'delete_user', 'Deleted user account: Keith Ababao (keithjustine944@gmail.com) - ID: 4', NULL, '::1', NULL, '2026-01-25 22:01:56'),
(334, 2, 'Submit Application', 'Submitted application: CRMN-2026-315126 for Evaluation and Recommendation for Electrical and Water Connection', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 22:02:37'),
(335, 3, 'Update Application Status', 'Updated application CRMN-2026-315126 from Pending to Processing', '{\"application_id\":17,\"tracking_number\":\"CRMN-2026-315126\",\"old_status\":\"Pending\",\"new_status\":\"Processing\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 22:03:15'),
(336, 3, 'Update Application Status', 'Updated application CRMN-2026-315126 from Processing to Approved', '{\"application_id\":17,\"tracking_number\":\"CRMN-2026-315126\",\"old_status\":\"Processing\",\"new_status\":\"Approved\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 22:04:12'),
(337, 2, 'Submit Application', 'Submitted application: CRMN-2026-931368 for Issuance of Certification', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 22:04:58'),
(338, 2, 'Submit Payment', 'Submitted payment for application CRMN-2026-931368 - Status changed to Paid', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 22:07:10'),
(339, 2, 'Submit Application', 'Submitted application: CRMN-2026-355514 for OTR (Occupational Tax Receipt)', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 22:27:21'),
(340, 2, 'Submit Application', 'Submitted application: CRMN-2026-847773 for Evaluation and Recommendation for Electrical and Water Connection', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 22:27:38'),
(341, 3, 'Update Application Status', 'Updated application CRMN-2026-847773 from Pending to Approved', '{\"application_id\":20,\"tracking_number\":\"CRMN-2026-847773\",\"old_status\":\"Pending\",\"new_status\":\"Approved\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 22:28:34'),
(342, 2, 'Submit Payment', 'Submitted payment for application CRMN-2026-355514 - Status changed to Paid', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 22:29:25'),
(343, 3, 'Verify Payment', 'Verified payment for application CRMN-2026-355514', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 22:36:44'),
(344, 3, 'Reject Payment', 'Rejected payment for application CRMN-2026-931368 - Status reverted to Approved', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 22:48:41'),
(345, 2, 'Submit Application', 'Submitted application: CRMN-2026-690228 for Issuance of Certification', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 22:50:20'),
(346, 2, 'Submit Payment', 'Submitted payment for application CRMN-2026-690228 - Status changed to Paid', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36 OPR/125.0.0.0', '2026-01-25 22:51:16'),
(347, 3, 'Reject Payment', 'Rejected payment for application CRMN-2026-690228 - Status reverted to Approved', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-25 22:51:42');

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `service_name` varchar(255) DEFAULT NULL,
  `tracking_number` varchar(50) NOT NULL,
  `purpose` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `compiled_document` varchar(500) DEFAULT NULL,
  `document_file_size` int(11) DEFAULT NULL,
  `admin_remarks` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` enum('Pending','Processing','Approved','Paid','Rejected','Completed') DEFAULT 'Pending',
  `payment_required` tinyint(1) DEFAULT 0,
  `payment_amount` decimal(10,2) DEFAULT 0.00,
  `payment_status` enum('not_required','pending','submitted','verified','rejected') DEFAULT 'not_required',
  `payment_deadline` datetime DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `payment_proof` varchar(500) DEFAULT NULL,
  `payment_proof_size` int(11) DEFAULT NULL,
  `payment_notes` text DEFAULT NULL,
  `payment_submitted_at` datetime DEFAULT NULL,
  `payment_verified_by` int(11) DEFAULT NULL,
  `payment_verified_at` datetime DEFAULT NULL,
  `payment_rejection_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `total_fee` decimal(10,2) DEFAULT 0.00,
  `fee` decimal(10,2) DEFAULT 0.00,
  `processing_time` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `user_id`, `department_id`, `service_id`, `service_name`, `tracking_number`, `purpose`, `location`, `compiled_document`, `document_file_size`, `admin_remarks`, `remarks`, `status`, `payment_required`, `payment_amount`, `payment_status`, `payment_deadline`, `payment_method`, `payment_reference`, `payment_proof`, `payment_proof_size`, `payment_notes`, `payment_submitted_at`, `payment_verified_by`, `payment_verified_at`, `payment_rejection_reason`, `notes`, `total_fee`, `fee`, `processing_time`, `created_at`, `updated_at`) VALUES
(1, 2, 61, 92, 'Request Data for Thesis/Research', 'CRMN-2026-309487', '4', '', 'assets/uploads/Topic_2_20260122_141514.pdf', 556487, '', '', 'Approved', 1, 51.00, 'pending', '2026-01-25 22:38:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-22 06:15:14', '2026-01-22 14:38:19'),
(2, 2, 63, 94, 'Issuance of Certification', 'CRMN-2026-137077', '4', 'f', 'assets/uploads/2-FOR-Capstone-Project-Concept-Paper-Template-updated-letterhead_20260122_141548.pdf', 575054, '', '', 'Approved', 1, 10.00, 'pending', '2026-01-25 22:37:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-22 06:15:48', '2026-01-22 14:37:12'),
(3, 2, 63, 94, 'Issuance of Certification', 'CRMN-2026-012806', '4', '', 'assets/uploads/Doc1_20260122_141827.pdf', 243428, '', '', 'Approved', 1, 124321.00, 'verified', '2026-01-25 22:36:16', NULL, 'gsdf', 'assets/uploads/payments/payment_3_1769136937.jpg', 3048992, 'fsa', '2026-01-23 10:55:37', 3, '2026-01-23 16:38:33', NULL, NULL, 0.00, 0.00, NULL, '2026-01-22 06:18:27', '2026-01-23 08:38:33'),
(4, 2, 31, 40, 'Mayor\'s Endorsement', 'CRMN-2026-483625', '5t13', '', 'assets/uploads/Rank_2_20260122_142024.pdf', 556495, '', '', 'Approved', 1, 4512.00, 'pending', '2026-01-26 10:10:49', NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, 'f', NULL, 0.00, 0.00, NULL, '2026-01-22 06:20:24', '2026-01-25 13:12:54'),
(5, 2, 31, 40, 'Mayor\'s Endorsement', 'CRMN-2026-403300', 'r21', '', 'assets/uploads/Topic_2_20260123_101022.pdf', 556487, '', '', 'Completed', 1, 1111.00, 'verified', '2026-01-26 10:11:57', NULL, 'gega', 'assets/uploads/payments/payment_5_1769135735.jpg', 3048992, '', '2026-01-23 10:35:35', 3, '2026-01-23 16:40:22', NULL, NULL, 0.00, 0.00, NULL, '2026-01-23 02:10:22', '2026-01-23 08:40:22'),
(6, 2, 62, 93, 'Tricycle Franchise Dropping', 'CRMN-2026-994356', '5123', 'r123', 'assets/uploads/Topic_2_20260123_160004.pdf', 556487, '', '', 'Completed', 1, 300.00, 'verified', '2026-01-26 16:11:40', NULL, 'fasfas', 'assets/uploads/payments/payment_6_1769156109.jpg', 3048992, '', '2026-01-23 16:15:09', 3, '2026-01-23 16:38:14', NULL, NULL, 0.00, 0.00, NULL, '2026-01-23 08:00:04', '2026-01-23 08:38:14'),
(7, 2, 31, 40, 'Mayor\'s Endorsement', 'CRMN-2026-353892', 'gwaw', '', 'assets/uploads/Topic_2_20260123_164115.pdf', 556487, '', '', 'Paid', 1, 100.00, 'verified', '2026-01-26 16:42:21', NULL, '4231', 'assets/uploads/payments/payment_7_1769291243.jpg', 3048992, '3', '2026-01-25 05:47:23', 3, '2026-01-25 05:48:13', NULL, NULL, 0.00, 0.00, NULL, '2026-01-23 08:41:15', '2026-01-24 21:48:13'),
(8, 2, 61, 91, 'Feedback Processing', 'CRMN-2026-289462', 'f', 'f', 'assets/uploads/Topic_2_20260123_165610.pdf', 556487, '', '', 'Completed', 1, 2.00, 'verified', '2026-01-26 16:56:28', NULL, 'fgSDAS', 'assets/uploads/payments/payment_8_1769259867.png', 120165, '', '2026-01-24 21:04:27', 3, '2026-01-24 22:46:17', NULL, NULL, 0.00, 0.00, NULL, '2026-01-23 08:56:10', '2026-01-24 14:46:53'),
(9, 2, 61, 91, 'Feedback Processing', 'CRMN-2026-813237', '5', '412', 'assets/uploads/Topic_2_20260123_175337.pdf', 556487, '', '', 'Completed', 1, 2.00, 'verified', '2026-01-26 18:39:00', NULL, '4123', 'assets/uploads/payments/payment_9_1769164780.png', 120165, '', '2026-01-23 18:39:40', 3, '2026-01-24 19:21:35', NULL, NULL, 0.00, 0.00, NULL, '2026-01-23 09:53:37', '2026-01-24 11:25:13'),
(10, 2, 31, 88, 'OTR (Occupational Tax Receipt)', 'CRMN-2026-544595', '45123', '', 'assets/uploads/Rank_2_20260124_225346.pdf', 556495, '', '', 'Approved', 1, 4123.00, 'verified', '2026-01-27 22:54:23', NULL, '42', 'assets/uploads/payments/payment_10_1769284023.jpg', 3048992, '', '2026-01-25 03:47:03', 3, '2026-01-25 03:47:36', NULL, NULL, 0.00, 0.00, NULL, '2026-01-24 14:53:46', '2026-01-25 13:18:42'),
(11, 2, 61, 91, 'Feedback Processing', 'CRMN-2026-065156', '41', '', 'assets/uploads/Topic_2_20260125_210707.pdf', 556487, '', '', 'Approved', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-25 13:07:07', '2026-01-25 13:17:53'),
(12, 2, 60, 105, 'Professional Tax Receipt', 'CRMN-2026-520447', 'fas', '', 'assets/uploads/Topic_2_20260125_211007.pdf', 556487, '', '', 'Approved', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-25 13:10:07', '2026-01-25 13:20:25'),
(13, 2, 31, 88, 'OTR (Occupational Tax Receipt)', 'CRMN-2026-673697', 'r2', '', 'assets/uploads/QRPh_Integration_Guide_-_Best_Option_for_LGUs__20260125_212059.pdf', 537508, '', '', 'Approved', 1, 50.00, 'pending', '2026-01-28 22:09:16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-25 13:20:59', '2026-01-25 14:09:16'),
(15, 2, 62, 93, 'Tricycle Franchise Dropping', 'CRMN-2026-462816', '42', '', 'assets/uploads/Topic_2_20260126_021809.pdf', 556487, NULL, '', 'Pending', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-25 18:18:09', '2026-01-25 18:18:09'),
(16, 2, 31, 88, 'OTR (Occupational Tax Receipt)', 'CRMN-2026-053670', 'r', '', 'assets/uploads/Topic_2_20260126_022131.pdf', 556487, NULL, '', 'Pending', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-25 18:21:31', '2026-01-25 18:21:31'),
(17, 2, 64, 95, 'Evaluation and Recommendation for Electrical and Water Connection', 'CRMN-2026-315126', '2', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31_20260126_060237.pdf', 91825, '', '', 'Approved', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-25 22:02:37', '2026-01-25 22:04:08'),
(18, 2, 63, 94, 'Issuance of Certification', 'CRMN-2026-931368', '2', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31_20260126_060458.pdf', 91825, '', '', 'Approved', 1, 90.00, 'pending', '2026-01-29 06:05:28', NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, 'boom', NULL, 0.00, 0.00, NULL, '2026-01-25 22:04:58', '2026-01-25 22:48:41'),
(19, 2, 31, 88, 'OTR (Occupational Tax Receipt)', 'CRMN-2026-355514', '42', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31_20260126_062721.pdf', 91825, '', '', 'Paid', 1, 50.00, 'verified', '2026-01-29 06:28:16', NULL, '24141242142187632167321', 'assets/uploads/payments/payment_19_1769380165.jpg', 66185, '', '2026-01-26 06:29:25', 3, '2026-01-26 06:36:44', NULL, NULL, 0.00, 0.00, NULL, '2026-01-25 22:27:21', '2026-01-25 22:36:44'),
(20, 2, 64, 95, 'Evaluation and Recommendation for Electrical and Water Connection', 'CRMN-2026-847773', '2321', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31_20260126_062738.pdf', 91825, '', '', 'Approved', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-25 22:27:38', '2026-01-25 22:28:30'),
(21, 2, 63, 94, 'Issuance of Certification', 'CRMN-2026-690228', 'f', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31_20260126_065020.pdf', 91825, '', 'fasdf', 'Approved', 1, 90.00, 'pending', '2026-01-29 06:50:40', NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, 'g', NULL, 0.00, 0.00, NULL, '2026-01-25 22:50:20', '2026-01-25 22:51:42');

-- --------------------------------------------------------

--
-- Stand-in structure for view `application_details`
-- (See below for the actual view)
--
CREATE TABLE `application_details` (
`id` int(11)
,`tracking_number` varchar(50)
,`user_id` int(11)
,`applicant_name` varchar(255)
,`email` varchar(255)
,`mobile` varchar(20)
,`department_name` varchar(255)
,`department_code` varchar(50)
,`service_name` varchar(255)
,`service_code` varchar(50)
,`purpose` text
,`location` varchar(255)
,`status` enum('Pending','Processing','Approved','Paid','Rejected','Completed')
,`compiled_document` varchar(500)
,`admin_remarks` text
,`created_at` timestamp
,`updated_at` timestamp
,`base_fee` decimal(10,2)
,`processing_days` int(11)
);

-- --------------------------------------------------------

--
-- Table structure for table `application_status_history`
--

CREATE TABLE `application_status_history` (
  `id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `remarks` text DEFAULT NULL,
  `updated_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `application_status_history`
--

INSERT INTO `application_status_history` (`id`, `application_id`, `status`, `remarks`, `updated_by`, `created_at`) VALUES
(1, 4, 'Approved', '\n\nPayment Required: ₱500.00\nDeadline: Jan 25, 2026 07:30 PM', 3, '2026-01-22 11:30:27'),
(2, 3, 'Approved', '\n\nPayment Required: ₱12.00\nDeadline: Jan 25, 2026 10:36 PM', 3, '2026-01-22 14:36:04'),
(3, 3, 'Approved', '\n\nPayment Required: ₱124,321.00\nDeadline: Jan 25, 2026 10:36 PM', 3, '2026-01-22 14:36:07'),
(4, 3, 'Approved', '\n\nPayment Required: ₱124,321.00\nDeadline: Jan 25, 2026 10:36 PM', 3, '2026-01-22 14:36:16'),
(5, 2, 'Approved', '\n\nPayment Required: ₱10.00\nDeadline: Jan 25, 2026 10:37 PM', 3, '2026-01-22 14:37:08'),
(6, 2, 'Approved', '\n\nPayment Required: ₱10.00\nDeadline: Jan 25, 2026 10:37 PM', 3, '2026-01-22 14:37:12'),
(7, 1, 'Approved', '\n\nPayment Required: ₱51.00\nDeadline: Jan 25, 2026 10:38 PM', 3, '2026-01-22 14:38:17'),
(8, 1, 'Approved', '\n\nPayment Required: ₱51.00\nDeadline: Jan 25, 2026 10:38 PM', 3, '2026-01-22 14:38:19'),
(9, 4, 'Approved', '\n\nPayment Required: ₱4,512.00\nDeadline: Jan 26, 2026 10:10 AM', 3, '2026-01-23 02:10:43'),
(10, 4, 'Approved', '\n\nPayment Required: ₱4,512.00\nDeadline: Jan 26, 2026 10:10 AM', 3, '2026-01-23 02:10:49'),
(11, 5, 'Approved', '\n\nPayment Required: ₱1,111.00\nDeadline: Jan 26, 2026 10:11 AM', 3, '2026-01-23 02:11:57'),
(12, 5, 'Completed', '', 3, '2026-01-23 02:36:31'),
(13, 6, 'Approved', '\n\nPayment Required: ₱300.00\nDeadline: Jan 26, 2026 04:11 PM', 3, '2026-01-23 08:11:40'),
(14, 6, 'Paid', 'Status automatically changed to Paid after user submitted payment proof', 2, '2026-01-23 08:15:09'),
(15, 6, 'Completed', '', 3, '2026-01-23 08:16:07'),
(16, 6, 'Paid', 'Payment verified by admin - Ready for document claiming', 3, '2026-01-23 08:38:14'),
(17, 3, 'Paid', 'Payment verified by admin - Ready for document claiming', 3, '2026-01-23 08:38:33'),
(18, 4, 'Approved', 'Payment rejected - Status reverted to Approved. Reason: gege', 3, '2026-01-23 08:39:53'),
(19, 5, 'Paid', 'Payment verified by admin - Ready for document claiming', 3, '2026-01-23 08:40:22'),
(20, 7, 'Processing', '', 3, '2026-01-23 08:41:40'),
(21, 7, 'Approved', '\n\nPayment Required: ₱100.00\nDeadline: Jan 26, 2026 04:42 PM', 3, '2026-01-23 08:42:21'),
(22, 8, 'Approved', '\n\nPayment Required: ₱2.00\nDeadline: Jan 26, 2026 04:56 PM', 3, '2026-01-23 08:56:28'),
(23, 9, 'Approved', '\n\nPayment Required: ₱2.00\nDeadline: Jan 26, 2026 06:39 PM', 3, '2026-01-23 10:39:00'),
(24, 9, 'Paid', 'Status automatically changed to Paid after user submitted payment proof', 2, '2026-01-23 10:39:40'),
(25, 9, 'Paid', 'Payment verified by admin - Ready for document claiming', 3, '2026-01-24 11:21:35'),
(26, 9, 'Completed', '', 3, '2026-01-24 11:25:13'),
(27, 8, 'Paid', 'Status automatically changed to Paid after user submitted payment proof', 2, '2026-01-24 13:04:27'),
(28, 8, 'Paid', 'Payment verified by admin - Ready for document claiming', 3, '2026-01-24 14:46:17'),
(29, 8, 'Completed', '', 3, '2026-01-24 14:46:53'),
(30, 10, 'Approved', '\n\nPayment Required: ₱4,123.00\nDeadline: Jan 27, 2026 10:54 PM', 3, '2026-01-24 14:54:17'),
(31, 10, 'Approved', '\n\nPayment Required: ₱4,123.00\nDeadline: Jan 27, 2026 10:54 PM', 3, '2026-01-24 14:54:23'),
(32, 10, 'Paid', 'Status automatically changed to Paid after user submitted payment proof', 2, '2026-01-24 19:47:03'),
(33, 10, 'Paid', 'Payment verified by admin - Ready for document claiming', 3, '2026-01-24 19:47:36'),
(34, 10, 'Completed', '', 3, '2026-01-24 19:47:49'),
(35, 7, 'Paid', 'Status automatically changed to Paid after user submitted payment proof', 2, '2026-01-24 21:47:23'),
(36, 7, 'Paid', 'Payment verified by admin - Ready for document claiming', 3, '2026-01-24 21:48:13'),
(37, 10, 'Rejected', '', 3, '2026-01-24 22:55:13'),
(38, 10, 'Completed', '', 3, '2026-01-24 23:05:40'),
(39, 11, 'Processing', '', 3, '2026-01-25 13:08:19'),
(40, 11, 'Approved', '', 3, '2026-01-25 13:09:00'),
(41, 12, 'Approved', '', 3, '2026-01-25 13:10:28'),
(42, 4, 'Paid', 'Status automatically changed to Paid after user submitted payment proof', 2, '2026-01-25 13:12:34'),
(43, 4, 'Approved', 'Payment rejected - Status reverted to Approved. Reason: f', 3, '2026-01-25 13:12:54'),
(44, 11, 'Processing', '', 3, '2026-01-25 13:17:31'),
(45, 11, 'Approved', '', 3, '2026-01-25 13:17:53'),
(46, 10, 'Approved', '', 3, '2026-01-25 13:18:42'),
(47, 12, 'Processing', '', 3, '2026-01-25 13:20:15'),
(48, 12, 'Approved', '', 3, '2026-01-25 13:20:25'),
(49, 13, 'Approved', '', 3, '2026-01-25 13:21:12'),
(50, 13, 'Pending', '', 3, '2026-01-25 13:26:49'),
(51, 13, 'Approved', '', 3, '2026-01-25 13:26:53'),
(52, 13, 'Completed', '', 3, '2026-01-25 13:28:54'),
(53, 13, 'Approved', '\n\nPayment Required: ₱50.00\nDeadline: Jan 28, 2026 10:09 PM', 3, '2026-01-25 14:09:16'),
(54, 17, 'Processing', '', 3, '2026-01-25 22:03:11'),
(55, 17, 'Approved', '', 3, '2026-01-25 22:04:08'),
(56, 18, 'Approved', '\n\nPayment Required: ₱90.00\nDeadline: Jan 29, 2026 06:05 AM', 3, '2026-01-25 22:05:28'),
(57, 18, 'Paid', 'Status automatically changed to Paid after user submitted payment proof', 2, '2026-01-25 22:07:10'),
(58, 19, 'Approved', '\n\nPayment Required: ₱50.00\nDeadline: Jan 29, 2026 06:28 AM', 3, '2026-01-25 22:28:16'),
(59, 20, 'Approved', '', 3, '2026-01-25 22:28:30'),
(60, 19, 'Paid', 'Status automatically changed to Paid after user submitted payment proof', 2, '2026-01-25 22:29:25'),
(61, 19, 'Paid', 'Payment verified by admin - Ready for document claiming', 3, '2026-01-25 22:36:44'),
(62, 18, 'Approved', 'Payment rejected - Status reverted to Approved. Reason: boom', 3, '2026-01-25 22:48:41'),
(63, 21, 'Approved', '\n\nPayment Required: ₱90.00\nDeadline: Jan 29, 2026 06:50 AM', 3, '2026-01-25 22:50:40'),
(64, 21, 'Paid', 'Status automatically changed to Paid after user submitted payment proof', 2, '2026-01-25 22:51:16'),
(65, 21, 'Approved', 'Payment rejected - Status reverted to Approved. Reason: g', 3, '2026-01-25 22:51:42');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `office_location` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `code`, `description`, `contact_person`, `contact_number`, `email`, `office_location`, `is_active`, `created_at`, `updated_at`) VALUES
(31, 'Office of the City Mayor', 'OCM', 'Handles mayor endorsements, certifications, occupational tax receipts, and motorcade permits', NULL, '(046) 430-0042', 'mayor@carmona.gov.ph', NULL, 1, '2025-12-30 07:11:34', '2026-01-22 05:12:16'),
(32, 'Office of the City Vice Mayor / Sangguniang Panlungsod', 'OCVM', 'Legislative documents, certifications, and Sorteo ng Carmona registration services', NULL, '(046) 430-0042', 'vicemayor@carmona.gov.ph', NULL, 1, '2025-12-30 07:11:34', '2026-01-22 05:12:16'),
(60, 'Office of the City Treasurer', 'OCT', 'Real property tax payments, official receipts, transfer tax, and professional tax services', NULL, '(046) 430-0042', 'treasurer@carmona.gov.ph', NULL, 1, '2025-12-30 07:11:34', '2026-01-22 05:12:16'),
(61, 'Office of the City Human Resource Management Officer', 'CHRMO', 'Feedback processing and research/thesis data request services', NULL, '(046) 430-0042', 'hrmo@carmona.gov.ph', NULL, 1, '2026-01-22 05:12:16', '2026-01-22 05:12:16'),
(62, 'Office of the City Tricycle Franchise & Regulation Board', 'CTFRB', 'Tricycle franchise dropping and regulation services', NULL, '(046) 430-0042', 'tricycle@carmona.gov.ph', NULL, 1, '2026-01-22 05:12:16', '2026-01-22 05:12:16'),
(63, 'Office of the City Planning and Development Coordinating Officer', 'CPDCO', 'Issuance of planning and development certifications', NULL, '(046) 430-0042', 'planning@carmona.gov.ph', NULL, 1, '2026-01-22 05:12:16', '2026-01-22 05:12:16'),
(64, 'Office of the City Urban Development & Housing Officer', 'CUDHO', 'Evaluation and recommendation for electrical and water connections', NULL, '(046) 430-0042', 'housing@carmona.gov.ph', NULL, 1, '2026-01-22 05:12:16', '2026-01-22 05:12:16'),
(65, 'Pagamutang Bayan ng Carmona', 'PBC', 'Hospital record services and issuance/releasing of hospital documents', NULL, '(046) 430-0042', 'hospital@carmona.gov.ph', NULL, 1, '2026-01-22 05:12:16', '2026-01-22 05:12:16'),
(66, 'Local Economic Development and Investment Promotions Office', 'LEDIPO', 'DTI business registration, BMBE registration, and business certifications', NULL, '(046) 430-0042', 'business@carmona.gov.ph', NULL, 1, '2026-01-22 05:12:16', '2026-01-22 05:17:55');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `application_id`, `filename`, `file_path`, `file_size`, `uploaded_at`) VALUES
(1, 1, 'Topic 2.pdf', 'assets/uploads/Topic_2_20260122_141514.pdf', 556487, '2026-01-22 06:15:14'),
(2, 2, '2-FOR-Capstone-Project-Concept-Paper-Template-updated-letterhead.pdf', 'assets/uploads/2-FOR-Capstone-Project-Concept-Paper-Template-updated-letterhead_20260122_141548.pdf', 575054, '2026-01-22 06:15:48'),
(3, 3, 'Doc1.pdf', 'assets/uploads/Doc1_20260122_141827.pdf', 243428, '2026-01-22 06:18:27'),
(4, 4, 'Rank 2.pdf', 'assets/uploads/Rank_2_20260122_142024.pdf', 556495, '2026-01-22 06:20:24'),
(5, 5, 'Topic 2.pdf', 'assets/uploads/Topic_2_20260123_101022.pdf', 556487, '2026-01-23 02:10:22'),
(6, 6, 'Topic 2.pdf', 'assets/uploads/Topic_2_20260123_160004.pdf', 556487, '2026-01-23 08:00:04'),
(7, 7, 'Topic 2.pdf', 'assets/uploads/Topic_2_20260123_164115.pdf', 556487, '2026-01-23 08:41:15'),
(8, 8, 'Topic 2.pdf', 'assets/uploads/Topic_2_20260123_165610.pdf', 556487, '2026-01-23 08:56:10'),
(9, 9, 'Topic 2.pdf', 'assets/uploads/Topic_2_20260123_175337.pdf', 556487, '2026-01-23 09:53:37'),
(10, 10, 'Rank 2.pdf', 'assets/uploads/Rank_2_20260124_225346.pdf', 556495, '2026-01-24 14:53:46'),
(11, 11, 'Topic 2.pdf', 'assets/uploads/Topic_2_20260125_210707.pdf', 556487, '2026-01-25 13:07:07'),
(12, 12, 'Topic 2.pdf', 'assets/uploads/Topic_2_20260125_211007.pdf', 556487, '2026-01-25 13:10:07'),
(13, 13, 'QRPh Integration Guide - Best Option for LGUs!.pdf', 'assets/uploads/QRPh_Integration_Guide_-_Best_Option_for_LGUs__20260125_212059.pdf', 537508, '2026-01-25 13:20:59'),
(15, 15, 'Topic 2.pdf', 'assets/uploads/Topic_2_20260126_021809.pdf', 556487, '2026-01-25 18:18:09'),
(16, 16, 'Topic 2.pdf', 'assets/uploads/Topic_2_20260126_022131.pdf', 556487, '2026-01-25 18:21:31'),
(17, 17, 'Report_2026-01-01_to_2026-01-31.pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31_20260126_060237.pdf', 91825, '2026-01-25 22:02:37'),
(18, 18, 'Report_2026-01-01_to_2026-01-31.pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31_20260126_060458.pdf', 91825, '2026-01-25 22:04:58'),
(19, 19, 'Report_2026-01-01_to_2026-01-31.pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31_20260126_062721.pdf', 91825, '2026-01-25 22:27:21'),
(20, 20, 'Report_2026-01-01_to_2026-01-31.pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31_20260126_062738.pdf', 91825, '2026-01-25 22:27:38'),
(21, 21, 'Report_2026-01-01_to_2026-01-31.pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31_20260126_065020.pdf', 91825, '2026-01-25 22:50:20');

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `recipient` varchar(255) NOT NULL,
  `subject` varchar(500) NOT NULL,
  `status` enum('sent','failed','pending') DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_logs`
--

INSERT INTO `email_logs` (`id`, `recipient`, `subject`, `status`, `error_message`, `created_at`) VALUES
(1, 'keithjustine57@gmail.com', 'Application Now Being Processed - CRMN-2026-315126', 'sent', NULL, '2026-01-25 22:03:15'),
(2, 'keithjustine57@gmail.com', 'Application Approved - CRMN-2026-315126', 'sent', NULL, '2026-01-25 22:04:12'),
(3, 'keithjustine57@gmail.com', 'Application Approved - Payment Required [CRMN-2026-931368]', 'sent', NULL, '2026-01-25 22:05:32'),
(4, 'keithjustine57@gmail.com', '✅ Payment Submitted - Status: PAID [CRMN-2026-931368]', 'sent', NULL, '2026-01-25 22:07:14'),
(5, 'keithjustine57@gmail.com', 'Application Approved - Payment Required [CRMN-2026-355514]', 'sent', NULL, '2026-01-25 22:28:19'),
(6, 'keithjustine57@gmail.com', 'Application Approved - CRMN-2026-847773', 'sent', NULL, '2026-01-25 22:28:34'),
(7, 'keithjustine57@gmail.com', '✅ Payment Submitted - Status: PAID [CRMN-2026-355514]', 'sent', NULL, '2026-01-25 22:29:29'),
(8, 'keithjustine57@gmail.com', '✅ Payment Verified - Ready for Claiming [CRMN-2026-355514]', 'sent', NULL, '2026-01-25 22:36:48'),
(9, 'keithjustine57@gmail.com', '❌ Payment Rejected - Resubmission Required [CRMN-2026-931368]', 'sent', NULL, '2026-01-25 22:48:46'),
(10, 'keithjustine57@gmail.com', 'Application Approved - Payment Required [CRMN-2026-690228]', 'sent', NULL, '2026-01-25 22:50:46'),
(11, 'keithjustine57@gmail.com', '✅ Payment Submitted - Status: PAID [CRMN-2026-690228]', 'sent', NULL, '2026-01-25 22:51:19'),
(12, 'keithjustine57@gmail.com', '❌ Payment Rejected - Resubmission Required [CRMN-2026-690228]', 'sent', NULL, '2026-01-25 22:51:46');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `application_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `application_id`, `title`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 2, 1, 'Application Submitted Successfully', 'Your application for Request Data for Thesis/Research has been submitted. Tracking Number: CRMN-2026-309487', 'success', 1, '2026-01-22 06:15:14'),
(2, 2, 2, 'Application Submitted Successfully', 'Your application for Issuance of Certification has been submitted. Tracking Number: CRMN-2026-137077', 'success', 1, '2026-01-22 06:15:48'),
(3, 2, 3, 'Application Submitted Successfully', 'Your application for Issuance of Certification has been submitted. Tracking Number: CRMN-2026-012806', 'success', 1, '2026-01-22 06:18:27'),
(4, 2, 4, 'Application Submitted Successfully', 'Your application for Mayor\'s Endorsement has been submitted. Tracking Number: CRMN-2026-483625', 'success', 1, '2026-01-22 06:20:24'),
(5, 2, 4, 'Application Approved - Payment Required', 'Your application CRMN-2026-483625 has been approved! Please submit payment of ₱500.00 within 3 days (by Jan 25, 2026).', 'success', 1, '2026-01-22 11:30:27'),
(6, 2, 3, 'Application Approved - Payment Required', 'Your application CRMN-2026-012806 has been approved! Please submit payment of ₱12.00 within 3 days (by Jan 25, 2026).', 'success', 1, '2026-01-22 14:36:04'),
(7, 2, 3, 'Application Approved - Payment Required', 'Your application CRMN-2026-012806 has been approved! Please submit payment of ₱124,321.00 within 3 days (by Jan 25, 2026).', 'success', 1, '2026-01-22 14:36:07'),
(8, 2, 3, 'Application Approved - Payment Required', 'Your application CRMN-2026-012806 has been approved! Please submit payment of ₱124,321.00 within 3 days (by Jan 25, 2026).', 'success', 1, '2026-01-22 14:36:16'),
(9, 2, 2, 'Application Approved - Payment Required', 'Your application CRMN-2026-137077 has been approved! Please submit payment of ₱10.00 within 3 days (by Jan 25, 2026).', 'success', 1, '2026-01-22 14:37:08'),
(10, 2, 2, 'Application Approved - Payment Required', 'Your application CRMN-2026-137077 has been approved! Please submit payment of ₱10.00 within 3 days (by Jan 25, 2026).', 'success', 1, '2026-01-22 14:37:12'),
(11, 2, 1, 'Application Approved - Payment Required', 'Your application CRMN-2026-309487 has been approved! Please submit payment of ₱51.00 within 3 days (by Jan 25, 2026).', 'success', 1, '2026-01-22 14:38:17'),
(12, 2, 1, 'Application Approved - Payment Required', 'Your application CRMN-2026-309487 has been approved! Please submit payment of ₱51.00 within 3 days (by Jan 25, 2026).', 'success', 1, '2026-01-22 14:38:19'),
(13, 2, 5, 'Application Submitted Successfully', 'Your application for Mayor\'s Endorsement has been submitted. Tracking Number: CRMN-2026-403300', 'success', 1, '2026-01-23 02:10:22'),
(14, 2, 4, 'Application Approved - Payment Required', 'Your application CRMN-2026-483625 has been approved! Please submit payment of ₱4,512.00 within 3 days (by Jan 26, 2026).', 'success', 1, '2026-01-23 02:10:43'),
(15, 2, 4, 'Application Approved - Payment Required', 'Your application CRMN-2026-483625 has been approved! Please submit payment of ₱4,512.00 within 3 days (by Jan 26, 2026).', 'success', 1, '2026-01-23 02:10:49'),
(16, 2, 5, 'Application Approved - Payment Required', 'Your application CRMN-2026-403300 has been approved! Please submit payment of ₱1,111.00 within 3 days (by Jan 26, 2026).', 'success', 1, '2026-01-23 02:11:57'),
(17, 2, 5, 'Payment Proof Submitted', 'Your payment proof for application CRMN-2026-403300 has been submitted and is now under verification.', 'info', 1, '2026-01-23 02:35:35'),
(18, 3, 5, 'Payment Proof Received', 'New payment proof submitted for application CRMN-2026-403300. Please verify.', 'info', 1, '2026-01-23 02:35:35'),
(19, 2, 5, 'Application Status Updated', 'Your application (CRMN-2026-403300) status has been updated to: Completed', 'success', 1, '2026-01-23 02:36:31'),
(20, 2, 4, 'Payment Proof Submitted', 'Your payment proof for application CRMN-2026-483625 has been submitted and is now under verification.', 'info', 1, '2026-01-23 02:53:18'),
(21, 3, 4, 'Payment Proof Received', 'New payment proof submitted for application CRMN-2026-483625. Please verify.', 'info', 1, '2026-01-23 02:53:18'),
(22, 2, 3, 'Payment Proof Submitted', 'Your payment proof for application CRMN-2026-012806 has been submitted and is now under verification.', 'info', 1, '2026-01-23 02:55:37'),
(23, 3, 3, 'Payment Proof Received', 'New payment proof submitted for application CRMN-2026-012806. Please verify.', 'info', 1, '2026-01-23 02:55:37'),
(24, 2, 6, 'Application Submitted Successfully', 'Your application for Tricycle Franchise Dropping has been submitted. Tracking Number: CRMN-2026-994356', 'success', 1, '2026-01-23 08:00:04'),
(25, 2, 6, 'Application Approved - Payment Required', 'Your application CRMN-2026-994356 has been approved! Please submit payment of ₱300.00 within 3 days (by Jan 26, 2026).', 'success', 1, '2026-01-23 08:11:40'),
(26, 2, 6, 'Payment Submitted - Status Changed to PAID', 'Your payment proof for application CRMN-2026-994356 has been submitted successfully! Your application status is now PAID and is under verification.', 'success', 1, '2026-01-23 08:15:09'),
(27, 3, 6, 'New Payment - Status: PAID', '✅ Payment submitted for application CRMN-2026-994356. Status changed to PAID. Please verify payment proof.', 'info', 1, '2026-01-23 08:15:09'),
(28, 2, 6, 'Application Status Updated', 'Your application (CRMN-2026-994356) status has been updated to: Completed', 'success', 1, '2026-01-23 08:16:07'),
(29, 2, 6, '✅ Payment Verified - Ready for Claiming', 'Great news! Your payment for application CRMN-2026-994356 has been verified! You may now claim your permit/document at our office.', 'success', 1, '2026-01-23 08:38:14'),
(30, 2, 3, '✅ Payment Verified - Ready for Claiming', 'Great news! Your payment for application CRMN-2026-012806 has been verified! You may now claim your permit/document at our office.', 'success', 1, '2026-01-23 08:38:33'),
(31, 2, 4, '❌ Payment Rejected - Status Reverted to APPROVED', 'Your payment proof for application CRMN-2026-483625 has been rejected. Status changed back to APPROVED. Reason: gege. Please submit a new payment proof.', 'danger', 1, '2026-01-23 08:39:53'),
(32, 2, 5, '✅ Payment Verified - Ready for Claiming', 'Great news! Your payment for application CRMN-2026-403300 has been verified! You may now claim your permit/document at our office.', 'success', 1, '2026-01-23 08:40:22'),
(33, 2, 7, 'Application Submitted Successfully', 'Your application for Mayor\'s Endorsement has been submitted. Tracking Number: CRMN-2026-353892', 'success', 1, '2026-01-23 08:41:15'),
(34, 2, 7, 'Application Status Updated', 'Your application (CRMN-2026-353892) status has been updated to: Processing', 'info', 1, '2026-01-23 08:41:40'),
(35, 2, 7, 'Application Approved - Payment Required', 'Your application CRMN-2026-353892 has been approved! Please submit payment of ₱100.00 within 3 days (by Jan 26, 2026).', 'success', 1, '2026-01-23 08:42:21'),
(36, 2, 8, 'Application Submitted Successfully', 'Your application for Feedback Processing has been submitted. Tracking Number: CRMN-2026-289462', 'success', 1, '2026-01-23 08:56:10'),
(43, 2, 9, 'Application Status Updated', 'Your application (CRMN-2026-813237) status has been updated to: Completed', 'success', 1, '2026-01-24 11:25:13'),
(44, 2, 8, 'Payment Submitted - Status Changed to PAID', 'Your payment proof for application CRMN-2026-289462 has been submitted successfully! Your application status is now PAID and is under verification.', 'success', 1, '2026-01-24 13:04:27'),
(45, 3, 8, 'New Payment - Status: PAID', '✅ Payment submitted for application CRMN-2026-289462. Status changed to PAID. Please verify payment proof.', 'info', 1, '2026-01-24 13:04:27'),
(46, 2, 8, '✅ Payment Verified - Ready for Claiming', 'Great news! Your payment for application CRMN-2026-289462 has been verified! You may now claim your permit/document at our office.', 'success', 1, '2026-01-24 14:46:17'),
(47, 2, 8, 'Application Status Updated', 'Your application (CRMN-2026-289462) status has been updated to: Completed', 'success', 1, '2026-01-24 14:46:53'),
(48, 2, 10, 'Application Submitted Successfully', 'Your application for OTR (Occupational Tax Receipt) has been submitted. Tracking Number: CRMN-2026-544595', 'success', 1, '2026-01-24 14:53:46'),
(49, 2, 10, 'Application Approved - Payment Required', 'Your application CRMN-2026-544595 has been approved! Please submit payment of ₱4,123.00 within 3 days (by Jan 27, 2026).', 'success', 1, '2026-01-24 14:54:17'),
(50, 2, 10, 'Application Approved - Payment Required', 'Your application CRMN-2026-544595 has been approved! Please submit payment of ₱4,123.00 within 3 days (by Jan 27, 2026).', 'success', 1, '2026-01-24 14:54:23'),
(51, 2, 10, 'Payment Submitted - Status Changed to PAID', 'Your payment proof for application CRMN-2026-544595 has been submitted successfully! Your application status is now PAID and is under verification.', 'success', 1, '2026-01-24 19:47:03'),
(52, 3, 10, 'New Payment - Status: PAID', '✅ Payment submitted for application CRMN-2026-544595. Status changed to PAID. Please verify payment proof.', 'info', 1, '2026-01-24 19:47:03'),
(53, 2, 10, '✅ Payment Verified - Ready for Claiming', 'Great news! Your payment for application CRMN-2026-544595 has been verified! You may now claim your permit/document at our office.', 'success', 1, '2026-01-24 19:47:36'),
(54, 2, 10, 'Application Status Updated', 'Your application (CRMN-2026-544595) status has been updated to: Completed', 'success', 1, '2026-01-24 19:47:49'),
(56, 3, 7, 'New Payment - Status: PAID', '✅ Payment submitted for application CRMN-2026-353892. Status changed to PAID. Please verify payment proof.', 'info', 1, '2026-01-24 21:47:23'),
(58, 2, 10, 'Application Status Updated', 'Your application (CRMN-2026-544595) status has been updated to: Rejected', 'danger', 1, '2026-01-24 22:55:13'),
(59, 2, 10, 'Application Status Updated', 'Your application (CRMN-2026-544595) status has been updated to: Completed', 'success', 1, '2026-01-24 23:05:40'),
(60, 2, 11, 'Application Submitted Successfully', 'Your application for Feedback Processing has been submitted. Tracking Number: CRMN-2026-065156', 'success', 1, '2026-01-25 13:07:07'),
(61, 2, 11, 'Application Status Updated', 'Your application (CRMN-2026-065156) status has been updated to: Processing', 'info', 1, '2026-01-25 13:08:19'),
(62, 2, 11, 'Application Status Updated', 'Your application (CRMN-2026-065156) status has been updated to: Approved', 'success', 1, '2026-01-25 13:09:00'),
(63, 2, 12, 'Application Submitted Successfully', 'Your application for Professional Tax Receipt has been submitted. Tracking Number: CRMN-2026-520447', 'success', 1, '2026-01-25 13:10:07'),
(64, 2, 12, 'Application Status Updated', 'Your application (CRMN-2026-520447) status has been updated to: Approved', 'success', 1, '2026-01-25 13:10:28'),
(65, 2, 4, 'Payment Submitted - Status Changed to PAID', 'Your payment proof for application CRMN-2026-483625 has been submitted successfully! Your application status is now PAID and is under verification.', 'success', 1, '2026-01-25 13:12:34'),
(66, 3, 4, 'New Payment - Status: PAID', '✅ Payment submitted for application CRMN-2026-483625. Status changed to PAID. Please verify payment proof.', 'info', 1, '2026-01-25 13:12:34'),
(67, 2, 4, '❌ Payment Rejected - Status Reverted to APPROVED', 'Your payment proof for application CRMN-2026-483625 has been rejected. Status changed back to APPROVED. Reason: f. Please submit a new payment proof.', 'danger', 1, '2026-01-25 13:12:54'),
(68, 2, 11, 'Application Status Updated', 'Your application (CRMN-2026-065156) status has been updated to: Processing', 'info', 1, '2026-01-25 13:17:31'),
(69, 2, 11, 'Application Status Updated', 'Your application (CRMN-2026-065156) status has been updated to: Approved', 'success', 1, '2026-01-25 13:17:53'),
(70, 2, 10, 'Application Status Updated', 'Your application (CRMN-2026-544595) status has been updated to: Approved', 'success', 1, '2026-01-25 13:18:42'),
(71, 2, 12, 'Application Status Updated', 'Your application (CRMN-2026-520447) status has been updated to: Processing', 'info', 1, '2026-01-25 13:20:15'),
(72, 2, 12, 'Application Status Updated', 'Your application (CRMN-2026-520447) status has been updated to: Approved', 'success', 1, '2026-01-25 13:20:25'),
(73, 2, 13, 'Application Submitted Successfully', 'Your application for OTR (Occupational Tax Receipt) has been submitted. Tracking Number: CRMN-2026-673697', 'success', 1, '2026-01-25 13:20:59'),
(74, 2, 13, 'Application Status Updated', 'Your application (CRMN-2026-673697) status has been updated to: Approved', 'success', 1, '2026-01-25 13:21:12'),
(75, 2, 13, 'Application Status Updated', 'Your application (CRMN-2026-673697) status has been updated to: Pending', 'info', 1, '2026-01-25 13:26:49'),
(76, 2, 13, 'Application Status Updated', 'Your application (CRMN-2026-673697) status has been updated to: Approved', 'success', 1, '2026-01-25 13:26:53'),
(77, 2, 13, 'Application Status Updated', 'Your application (CRMN-2026-673697) status has been updated to: Completed', 'success', 1, '2026-01-25 13:28:54'),
(78, 2, 13, 'Application Approved - Payment Required', 'Your application CRMN-2026-673697 has been approved! Please submit payment of ₱50.00 within 3 days (by Jan 28, 2026).', 'success', 1, '2026-01-25 14:09:16'),
(83, 2, 17, 'Application Submitted Successfully', 'Your application for Evaluation and Recommendation for Electrical and Water Connection has been submitted. Tracking Number: CRMN-2026-315126', 'success', 1, '2026-01-25 22:02:37'),
(84, 3, 17, 'New Application Received', 'New application submitted. Tracking Number: CRMN-2026-315126', 'info', 1, '2026-01-25 22:02:37'),
(85, 2, 17, 'Application Status Updated', 'Your application (CRMN-2026-315126) status has been updated to: Processing', 'info', 1, '2026-01-25 22:03:11'),
(86, 2, 17, 'Application Status Updated', 'Your application (CRMN-2026-315126) status has been updated to: Approved', 'success', 1, '2026-01-25 22:04:08'),
(87, 2, 18, 'Application Submitted Successfully', 'Your application for Issuance of Certification has been submitted. Tracking Number: CRMN-2026-931368', 'success', 0, '2026-01-25 22:04:58'),
(88, 3, 18, 'New Application Received', 'New application submitted. Tracking Number: CRMN-2026-931368', 'info', 1, '2026-01-25 22:04:58'),
(89, 2, 18, 'Application Approved - Payment Required', 'Your application CRMN-2026-931368 has been approved! Please submit payment of ₱90.00 within 3 days (by Jan 29, 2026).', 'success', 1, '2026-01-25 22:05:28'),
(90, 2, 18, 'Payment Submitted - Status Changed to PAID', 'Your payment proof for application CRMN-2026-931368 has been submitted successfully! Your application status is now PAID and is under verification.', 'success', 0, '2026-01-25 22:07:10'),
(91, 3, 18, 'New Payment - Status: PAID', '✅ Payment submitted for application CRMN-2026-931368. Status changed to PAID. Please verify payment proof.', 'info', 0, '2026-01-25 22:07:10'),
(92, 2, 19, 'Application Submitted Successfully', 'Your application for OTR (Occupational Tax Receipt) has been submitted. Tracking Number: CRMN-2026-355514', 'success', 0, '2026-01-25 22:27:21'),
(93, 3, 19, 'New Application Received', 'New application submitted. Tracking Number: CRMN-2026-355514', 'info', 0, '2026-01-25 22:27:21'),
(94, 2, 20, 'Application Submitted Successfully', 'Your application for Evaluation and Recommendation for Electrical and Water Connection has been submitted. Tracking Number: CRMN-2026-847773', 'success', 0, '2026-01-25 22:27:38'),
(95, 3, 20, 'New Application Received', 'New application submitted. Tracking Number: CRMN-2026-847773', 'info', 0, '2026-01-25 22:27:38'),
(96, 2, 19, 'Application Approved - Payment Required', 'Your application CRMN-2026-355514 has been approved! Please submit payment of ₱50.00 within 3 days (by Jan 29, 2026).', 'success', 0, '2026-01-25 22:28:16'),
(97, 2, 20, 'Application Status Updated', 'Your application (CRMN-2026-847773) status has been updated to: Approved', 'success', 1, '2026-01-25 22:28:30'),
(98, 2, 19, 'Payment Submitted - Status Changed to PAID', 'Your payment proof for application CRMN-2026-355514 has been submitted successfully! Your application status is now PAID and is under verification.', 'success', 0, '2026-01-25 22:29:25'),
(99, 3, 19, 'New Payment - Status: PAID', '✅ Payment submitted for application CRMN-2026-355514. Status changed to PAID. Please verify payment proof.', 'info', 0, '2026-01-25 22:29:25'),
(100, 2, 19, '✅ Payment Verified - Ready for Claiming', 'Great news! Your payment for application CRMN-2026-355514 has been verified! You may now claim your permit/document at our office.', 'success', 0, '2026-01-25 22:36:44'),
(101, 2, 18, '❌ Payment Rejected - Status Reverted to APPROVED', 'Your payment proof for application CRMN-2026-931368 has been rejected. Status changed back to APPROVED. Reason: boom. Please submit a new payment proof.', 'danger', 0, '2026-01-25 22:48:41'),
(102, 2, 21, 'Application Submitted Successfully', 'Your application for Issuance of Certification has been submitted. Tracking Number: CRMN-2026-690228', 'success', 0, '2026-01-25 22:50:20'),
(103, 3, 21, 'New Application Received', 'New application submitted. Tracking Number: CRMN-2026-690228', 'info', 1, '2026-01-25 22:50:20'),
(104, 2, 21, 'Application Approved - Payment Required', 'Your application CRMN-2026-690228 has been approved! Please submit payment of ₱90.00 within 3 days (by Jan 29, 2026).', 'success', 1, '2026-01-25 22:50:40'),
(105, 2, 21, 'Payment Submitted - Status Changed to PAID', 'Your payment proof for application CRMN-2026-690228 has been submitted successfully! Your application status is now PAID and is under verification.', 'success', 0, '2026-01-25 22:51:16'),
(106, 3, 21, 'New Payment - Status: PAID', '✅ Payment submitted for application CRMN-2026-690228. Status changed to PAID. Please verify payment proof.', 'info', 1, '2026-01-25 22:51:16'),
(107, 2, 21, '❌ Payment Rejected - Status Reverted to APPROVED', 'Your payment proof for application CRMN-2026-690228 has been rejected. Status changed back to APPROVED. Reason: g. Please submit a new payment proof.', 'danger', 0, '2026-01-25 22:51:42');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_config`
--

CREATE TABLE `payment_config` (
  `id` int(11) NOT NULL,
  `config_key` varchar(100) NOT NULL,
  `config_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_config`
--

INSERT INTO `payment_config` (`id`, `config_key`, `config_value`, `description`, `updated_at`) VALUES
(1, 'gcash_number', '09690805901', 'GCash number for payments', '2026-01-22 05:49:03'),
(2, 'gcash_name', 'LGU Carmona', 'GCash account name', '2026-01-22 05:49:03'),
(3, 'payment_deadline_days', '3', 'Number of days to pay after approval', '2026-01-22 05:49:03'),
(4, 'payment_instructions', 'Pay via GCash to 09690805901. Use your tracking number as reference. Upload screenshot as proof.', 'Payment instructions shown to users', '2026-01-22 05:49:03'),
(5, 'auto_expire_enabled', '1', 'Auto-expire unpaid applications after deadline (1=yes, 0=no)', '2026-01-22 05:49:03');

-- --------------------------------------------------------

--
-- Table structure for table `payment_history`
--

CREATE TABLE `payment_history` (
  `id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `performed_by` int(11) NOT NULL,
  `performed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_history`
--

INSERT INTO `payment_history` (`id`, `application_id`, `action`, `amount`, `payment_method`, `reference_number`, `notes`, `performed_by`, `performed_at`) VALUES
(1, 4, 'payment_required', 500.00, NULL, NULL, 'Payment deadline set to Jan 25, 2026', 3, '2026-01-22 11:30:27'),
(2, 3, 'payment_required', 12.00, NULL, NULL, 'Payment deadline set to Jan 25, 2026', 3, '2026-01-22 14:36:04'),
(3, 3, 'payment_required', 124321.00, NULL, NULL, 'Payment deadline set to Jan 25, 2026', 3, '2026-01-22 14:36:07'),
(4, 3, 'payment_required', 124321.00, NULL, NULL, 'Payment deadline set to Jan 25, 2026', 3, '2026-01-22 14:36:16'),
(5, 2, 'payment_required', 10.00, NULL, NULL, 'Payment deadline set to Jan 25, 2026', 3, '2026-01-22 14:37:08'),
(6, 2, 'payment_required', 10.00, NULL, NULL, 'Payment deadline set to Jan 25, 2026', 3, '2026-01-22 14:37:12'),
(7, 1, 'payment_required', 51.00, NULL, NULL, 'Payment deadline set to Jan 25, 2026', 3, '2026-01-22 14:38:17'),
(8, 1, 'payment_required', 51.00, NULL, NULL, 'Payment deadline set to Jan 25, 2026', 3, '2026-01-22 14:38:19'),
(9, 4, 'payment_required', 4512.00, NULL, NULL, 'Payment deadline set to Jan 26, 2026', 3, '2026-01-23 02:10:43'),
(10, 4, 'payment_required', 4512.00, NULL, NULL, 'Payment deadline set to Jan 26, 2026', 3, '2026-01-23 02:10:49'),
(11, 5, 'payment_required', 1111.00, NULL, NULL, 'Payment deadline set to Jan 26, 2026', 3, '2026-01-23 02:11:57'),
(12, 5, 'payment_submitted', 1111.00, 'GCash', 'gega', '', 2, '2026-01-23 02:35:35'),
(13, 4, 'payment_submitted', 4512.00, 'GCash', 'gega', '', 2, '2026-01-23 02:53:18'),
(14, 3, 'payment_submitted', 124321.00, 'GCash', 'gsdf', 'fsa', 2, '2026-01-23 02:55:37'),
(15, 6, 'payment_required', 300.00, NULL, NULL, 'Payment deadline set to Jan 26, 2026', 3, '2026-01-23 08:11:40'),
(16, 6, 'payment_submitted', 300.00, 'GCash', 'fasfas', '', 2, '2026-01-23 08:15:09'),
(17, 6, 'payment_verified', 300.00, NULL, NULL, 'Payment verified by admin', 3, '2026-01-23 08:38:14'),
(18, 3, 'payment_verified', 124321.00, NULL, NULL, 'Payment verified by admin', 3, '2026-01-23 08:38:33'),
(19, 4, 'payment_rejected', NULL, NULL, NULL, 'gege', 3, '2026-01-23 08:39:53'),
(20, 5, 'payment_verified', 1111.00, NULL, NULL, 'Payment verified by admin', 3, '2026-01-23 08:40:22'),
(21, 7, 'payment_required', 100.00, NULL, NULL, 'Payment deadline set to Jan 26, 2026', 3, '2026-01-23 08:42:21'),
(22, 8, 'payment_required', 2.00, NULL, NULL, 'Payment deadline set to Jan 26, 2026', 3, '2026-01-23 08:56:28'),
(23, 9, 'payment_required', 2.00, NULL, NULL, 'Payment deadline set to Jan 26, 2026', 3, '2026-01-23 10:39:00'),
(24, 9, 'payment_submitted', 2.00, 'GCash', '4123', '', 2, '2026-01-23 10:39:40'),
(25, 9, 'payment_verified', 2.00, NULL, NULL, 'Payment verified by admin', 3, '2026-01-24 11:21:35'),
(26, 8, 'payment_submitted', 2.00, 'GCash', 'fgSDAS', '', 2, '2026-01-24 13:04:27'),
(27, 8, 'payment_verified', 2.00, NULL, NULL, 'Payment verified by admin', 3, '2026-01-24 14:46:17'),
(28, 10, 'payment_required', 4123.00, NULL, NULL, 'Payment deadline set to Jan 27, 2026', 3, '2026-01-24 14:54:17'),
(29, 10, 'payment_required', 4123.00, NULL, NULL, 'Payment deadline set to Jan 27, 2026', 3, '2026-01-24 14:54:23'),
(30, 10, 'payment_submitted', 4123.00, 'GCash', '42', '', 2, '2026-01-24 19:47:03'),
(31, 10, 'payment_verified', 4123.00, NULL, NULL, 'Payment verified by admin', 3, '2026-01-24 19:47:36'),
(32, 7, 'payment_submitted', 100.00, 'GCash', '4231', '3', 2, '2026-01-24 21:47:23'),
(33, 7, 'payment_verified', 100.00, NULL, NULL, 'Payment verified by admin', 3, '2026-01-24 21:48:13'),
(34, 4, 'payment_submitted', 4512.00, 'GCash', 'fas', '', 2, '2026-01-25 13:12:34'),
(35, 4, 'payment_rejected', NULL, NULL, NULL, 'f', 3, '2026-01-25 13:12:54'),
(36, 13, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Jan 28, 2026', 3, '2026-01-25 14:09:16'),
(37, 18, 'payment_required', 90.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-25 22:05:28'),
(38, 18, 'payment_submitted', 90.00, 'GCash', 'f', '', 2, '2026-01-25 22:07:10'),
(39, 19, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-25 22:28:16'),
(40, 19, 'payment_submitted', 50.00, 'GCash', '24141242142187632167321', '', 2, '2026-01-25 22:29:25'),
(41, 19, 'payment_verified', 50.00, NULL, NULL, 'Payment verified by admin', 3, '2026-01-25 22:36:44'),
(42, 18, 'payment_rejected', NULL, NULL, NULL, 'boom', 3, '2026-01-25 22:48:41'),
(43, 21, 'payment_required', 90.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-25 22:50:40'),
(44, 21, 'payment_submitted', 90.00, 'GCash', '424', '', 2, '2026-01-25 22:51:16'),
(45, 21, 'payment_rejected', NULL, NULL, NULL, 'g', 3, '2026-01-25 22:51:42');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `service_code` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `processing_days` int(11) DEFAULT 7,
  `base_fee` decimal(10,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `department_id`, `service_name`, `service_code`, `description`, `requirements`, `processing_days`, `base_fee`, `is_active`, `created_at`, `updated_at`) VALUES
(40, 31, 'Mayor\'s Endorsement', 'OCM-001', 'Official endorsement from the City Mayor', 'Valid ID, Letter of Request', 3, 50.00, 1, '2025-12-30 07:23:58', '2025-12-30 07:23:58'),
(86, 31, 'Certification of No Major Source of Income/No Business', 'OCM-002', 'Certification that applicant has no major income source or business', 'Barangay Clearance (Original)\nCertificate of Indigency (Original)\nCertificate of No Business in Barangay Level (Original)', 3, 0.00, 1, '2026-01-22 05:12:16', '2026-01-22 05:12:16'),
(87, 31, 'Permit for Motorcade', 'OCM-003', 'Permission to conduct motorcade within city limits', 'Request Letter and Route Plan', 3, 50.00, 1, '2026-01-22 05:12:16', '2026-01-22 05:12:16'),
(88, 31, 'OTR (Occupational Tax Receipt)', 'OCM-004', 'Occupational tax receipt for professionals and freelancers', 'Latest Certificate of Affiliation/Contract Agreement\nValid ID with Address in Carmona, Cavite (1 Photocopy)', 3, 50.00, 1, '2026-01-22 05:12:16', '2026-01-22 05:12:16'),
(89, 32, 'Issuance of Legislative Documents and Certifications', 'OCVM-001', 'Certified copies of ordinances, resolutions, and other legislative documents', 'Document Request Form\nOfficial Receipt', 5, 50.00, 1, '2026-01-22 05:12:16', '2026-01-22 05:12:16'),
(90, 32, 'Sorteo ng Carmona Registration', 'OCVM-002', 'Registration for Sorteo ng Carmona housing program', 'Official Receipt of Registration Fee (PHP 100.00)\nOne valid ID (government-issued or NBI/Police Clearance)\nOriginal and photocopy of PSA-issued documents:\n- Marriage Certificate (for married)\n- Marriage Certificate and Death Certificate of Spouse (for widow/er)\n- Death Certificates of parents and Birth Certificate (for orphans)\n- Birth Certificate (for single ages 50+)', 5, 100.00, 1, '2026-01-22 05:12:16', '2026-01-22 05:12:16'),
(91, 61, 'Feedback Processing', 'CHRMO-001', 'Processing of citizen feedback, complaints, and suggestions', 'Accomplished Feedback Form/Written Complaint', 7, 0.00, 1, '2026-01-22 05:12:16', '2026-01-22 05:12:16'),
(92, 61, 'Request Data for Thesis/Research', 'CHRMO-002', 'Access to government data for academic research purposes', 'Formal request letter signed by student(s) and Thesis/Research Adviser', 7, 0.00, 1, '2026-01-22 05:12:16', '2026-01-22 05:12:16'),
(93, 62, 'Tricycle Franchise Dropping', 'CTFRB-001', 'Termination/dropping of tricycle franchise registration', 'Photocopy of OR/CR\nOriginal Franchise Owner\'s Copy\nTIN Plate', 5, 50.00, 1, '2026-01-22 05:12:16', '2026-01-22 05:12:16'),
(94, 63, 'Issuance of Certification', 'CPDCO-001', 'Various certifications related to planning and development', 'Request Form\nValid ID', 5, 90.00, 1, '2026-01-22 05:12:16', '2026-01-22 05:12:16'),
(95, 64, 'Evaluation and Recommendation for Electrical and Water Connection', 'CUDHO-001', 'Evaluation for electrical permit and water connection applications', 'Personal Identification (ID of Client)\nPhotocopy of Proof of Ownership (Tax Declaration, Title)\nBarangay Clearance\nElectrical permit form from Building Office\nContract to Sell/Deed of Sale', 7, 0.00, 1, '2026-01-22 05:12:16', '2026-01-22 05:12:16'),
(96, 65, 'Hospital Record Services', 'PBC-001', 'Issuance and releasing of hospital documents and medical records', 'Passlip\nOfficial Receipt\nAuthorization letter (if with representative)\nValid ID', 3, 0.00, 1, '2026-01-22 05:12:16', '2026-01-22 05:12:16'),
(97, 66, 'DTI Business Name Registration (New)', 'LEDIPO-001', 'New business name registration with DTI', 'BN Registration form signed by owner\nOwner\'s Valid Government Issued ID (original)\nAuthorization Letter of Owner (if representative)\nRepresentative\'s Valid ID (if representative)', 5, 230.00, 1, '2026-01-22 05:12:16', '2026-01-22 05:12:16'),
(98, 66, 'DTI Business Name Registration (Renewal)', 'LEDIPO-002', 'Renewal of existing DTI business name registration', 'BN Registration form signed by owner\nOwner\'s Valid Government Issued ID (original)\nOriginal Copy of expired Certificate of Business Name Registration\nTax Identification Number (TIN No.)\nAuthorization Letter of Owner (if representative)\nRepresentative\'s Valid ID (if representative)', 5, 230.00, 1, '2026-01-22 05:12:16', '2026-01-22 05:12:16'),
(99, 66, 'Barangay Micro-Business Enterprise (BMBE) Registration', 'LEDIPO-003', 'Registration for BMBE tax exemption and benefits', 'Duly accomplished BMBE Application Form 01 signed by owner\r\nDuly signed Consent Form\r\nOriginal Certificate of Business Name Registration\r\nAuthorization Letter of Owner (if representative)\r\nRepresentative&#039;s Valid ID (if representative)', 5, 1.00, 1, '2026-01-22 05:12:16', '2026-01-25 20:24:31'),
(100, 66, 'Request for Business Name Certification', 'LEDIPO-004', 'Certification of business name availability or registration', 'Duly accomplished Other BN Related Application Form signed by owner\nValid ID of Requesting Individual', 3, 80.00, 1, '2026-01-22 05:12:16', '2026-01-22 05:12:16'),
(101, 66, 'Endorsement Letter for Manila Southwoods RFID Tag/Car Sticker', 'LEDIPO-005', 'City endorsement for Manila Southwoods subdivision access', 'Application form\nCopy of Official Receipt and Certificate of Registration (OR/CR) of vehicle\nValid ID of Requesting Individual', 3, 0.00, 1, '2026-01-22 05:12:16', '2026-01-22 05:12:16'),
(102, 60, 'Real Property Tax Payment', 'OCT-001', 'Annual real property tax payment', 'Tax Declaration\nPrevious Official Receipt', 1, 0.00, 1, '2026-01-22 05:12:16', '2026-01-22 05:12:16'),
(103, 60, 'Issuance of Official Receipt', 'OCT-002', 'Official receipt for various government fees and payments', 'Order of Payment/Routing Slip', 1, 0.00, 1, '2026-01-22 05:12:16', '2026-01-22 05:12:16'),
(104, 60, 'Transfer Tax', 'OCT-003', 'Tax for property transfer/sale', 'Tax Clearance\nReal Property Tax Receipts', 3, 0.00, 1, '2026-01-22 05:12:16', '2026-01-22 05:12:16'),
(105, 60, 'Professional Tax Receipt', 'OCT-004', 'Annual tax receipt for licensed professionals', 'Photocopy of valid PRC license', 1, 300.00, 1, '2026-01-22 05:12:16', '2026-01-22 05:12:16'),
(109, 31, 'r', 'r', '', '', 2, 2.00, 1, '2026-01-25 20:24:19', '2026-01-25 20:24:19');

-- --------------------------------------------------------

--
-- Table structure for table `sms_logs`
--

CREATE TABLE `sms_logs` (
  `id` int(11) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `status` enum('sent','failed','pending') DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sms_logs`
--

INSERT INTO `sms_logs` (`id`, `phone_number`, `message`, `status`, `error_message`, `sent_at`, `created_at`) VALUES
(1, '09690805901', '✅ PAYMENT VERIFIED! Your application CRMN-2026-355514 is ready for claiming. Visit our office with valid ID and tracking number. Thank you!', 'failed', 'HTTP 403: Your account has not yet been approved for sending messages. Please either top-up your account or wait for your account to be approved. If your account has not been approved within 2 business days, please email support@semaphore.co', NULL, '2026-01-25 22:36:49'),
(2, '09690805901', '❌ Payment REJECTED for CRMN-2026-931368. Status: APPROVED (Payment Pending). Reason: boom. Please resubmit payment proof. Check email for details.', 'failed', 'HTTP 403: Your account has not yet been approved for sending messages. Please either top-up your account or wait for your account to be approved. If your account has not been approved within 2 business days, please email support@semaphore.co', NULL, '2026-01-25 22:48:47'),
(3, '09690805901', '❌ Payment REJECTED for CRMN-2026-690228. Status: APPROVED (Payment Pending). Reason: g. Please resubmit payment proof. Check email for details.', 'failed', 'HTTP 403: Your account has not yet been approved for sending messages. Please either top-up your account or wait for your account to be approved. If your account has not been approved within 2 business days, please email support@semaphore.co', NULL, '2026-01-25 22:51:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `address` text DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `mobile`, `address`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'Keith Justine Ababao', 'keithjustine57@gmail.com', '$2y$10$I4OWl.yokEgB0d8U0qTEleO34fYp3Bq0vrpie0Wg9mMmVJx.J/.k.', '09690805901', '', 'user', 1, '2025-12-12 15:32:15', '2026-01-25 22:01:50'),
(3, 'Admin', 'admin@lgu.gov.ph', '$2y$10$4xtTP5ioWaIrP61QNSltBedjrAJQw5mHZpHp3.59FReP5solatzxK', '09111111111', '', 'admin', 1, '2025-12-12 15:51:18', '2026-01-25 21:57:52');

-- --------------------------------------------------------

--
-- Structure for view `application_details`
--
DROP TABLE IF EXISTS `application_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `application_details`  AS SELECT `a`.`id` AS `id`, `a`.`tracking_number` AS `tracking_number`, `a`.`user_id` AS `user_id`, `u`.`name` AS `applicant_name`, `u`.`email` AS `email`, `u`.`mobile` AS `mobile`, `d`.`name` AS `department_name`, `d`.`code` AS `department_code`, `s`.`service_name` AS `service_name`, `s`.`service_code` AS `service_code`, `a`.`purpose` AS `purpose`, `a`.`location` AS `location`, `a`.`status` AS `status`, `a`.`compiled_document` AS `compiled_document`, `a`.`admin_remarks` AS `admin_remarks`, `a`.`created_at` AS `created_at`, `a`.`updated_at` AS `updated_at`, `s`.`base_fee` AS `base_fee`, `s`.`processing_days` AS `processing_days` FROM (((`applications` `a` left join `users` `u` on(`a`.`user_id` = `u`.`id`)) left join `departments` `d` on(`a`.`department_id` = `d`.`id`)) left join `services` `s` on(`a`.`service_id` = `s`.`id`)) ORDER BY `a`.`created_at` DESC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tracking_number` (`tracking_number`),
  ADD KEY `idx_tracking` (`tracking_number`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_user_status` (`user_id`,`status`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_applications_department` (`department_id`),
  ADD KEY `idx_applications_service` (`service_id`),
  ADD KEY `idx_applications_status_dept` (`status`,`department_id`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_payment_deadline` (`payment_deadline`);

--
-- Indexes for table `application_status_history`
--
ALTER TABLE `application_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_application` (`application_id`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipient` (`recipient`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `idx_status_date` (`status`,`created_at`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_read` (`is_read`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `payment_config`
--
ALTER TABLE `payment_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `config_key` (`config_key`);

--
-- Indexes for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_application` (`application_id`),
  ADD KEY `idx_performed_by` (`performed_by`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_department` (`department_id`),
  ADD KEY `idx_active` (`is_active`);

--
-- Indexes for table `sms_logs`
--
ALTER TABLE `sms_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `phone_number` (`phone_number`),
  ADD KEY `status` (`status`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `idx_status_date` (`status`,`created_at`);

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
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=348;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `application_status_history`
--
ALTER TABLE `application_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment_config`
--
ALTER TABLE `payment_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payment_history`
--
ALTER TABLE `payment_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT for table `sms_logs`
--
ALTER TABLE `sms_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD CONSTRAINT `payment_history_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_history_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
