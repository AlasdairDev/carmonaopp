-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 29, 2026 at 07:52 PM
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

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `related_department_id` int(11) DEFAULT NULL,
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

INSERT INTO `activity_logs` (`id`, `user_id`, `department_id`, `related_department_id`, `action`, `description`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(925, NULL, NULL, NULL, 'clear_logs', 'All activity logs cleared', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 12:27:07'),
(926, 3, NULL, NULL, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 12:52:39'),
(927, 3, NULL, NULL, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 12:52:47'),
(928, 3, NULL, NULL, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-29 20:52:47\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 12:52:47'),
(929, 3, NULL, NULL, 'Change Password', 'Changed account password', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 12:53:16'),
(930, 3, NULL, NULL, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 12:53:18'),
(931, 3, NULL, NULL, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 12:53:22'),
(932, 3, NULL, NULL, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-29 20:53:22\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 12:53:22'),
(933, 3, NULL, NULL, 'Create User', 'Created new user: Admin (ID: 21)', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 13:02:09'),
(934, 3, NULL, NULL, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 13:24:14'),
(935, NULL, NULL, NULL, 'LOGIN_FAILURE', 'Invalid password for: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-29 21:24:22\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 13:24:22'),
(936, NULL, NULL, NULL, 'LOGIN_FAILURE', 'Invalid password for: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-29 21:24:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 13:24:25'),
(937, NULL, NULL, NULL, 'LOGIN_FAILURE', 'Invalid password for: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-29 21:24:28\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 13:24:28'),
(938, NULL, NULL, NULL, 'LOGIN_FAILURE', 'Invalid password for: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-29 21:24:36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 13:24:36'),
(939, NULL, NULL, NULL, 'RATE_LIMIT_EXCEEDED', 'Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-29 21:24:51\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 13:24:51'),
(940, NULL, NULL, NULL, 'LOGIN_FAILURE', 'Invalid password for: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-29 21:25:01\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 13:25:01'),
(941, NULL, NULL, NULL, 'LOGIN_FAILURE', 'Invalid password for: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-29 21:25:05\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 13:25:05'),
(942, NULL, NULL, NULL, 'LOGIN_FAILURE', 'Invalid password for: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-29 21:26:05\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 13:26:05'),
(943, NULL, NULL, NULL, 'LOGIN_FAILURE', 'Unverified email: keichoo57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-29 21:26:18\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 13:26:18'),
(944, NULL, NULL, NULL, 'LOGIN_FAILURE', 'Unverified email: keichoo57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 21:27:31\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 13:27:31'),
(945, 2, NULL, NULL, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 13:37:46'),
(946, 2, NULL, NULL, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-29 21:37:46\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 13:37:46'),
(947, 2, NULL, NULL, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 13:45:32'),
(948, 2, NULL, NULL, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 13:46:43'),
(949, 2, NULL, NULL, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-29 21:46:43\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 13:46:43'),
(950, 2, NULL, NULL, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 13:47:16'),
(951, 3, NULL, NULL, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 13:47:21'),
(952, 3, NULL, NULL, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-29 21:47:21\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 13:47:21'),
(953, 3, NULL, NULL, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 13:57:23'),
(954, 3, NULL, NULL, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 14:05:21'),
(955, 3, NULL, NULL, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-29 22:05:21\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 14:05:21'),
(956, 3, NULL, NULL, 'Update User', 'Updated user: Adminin (ID: 21)', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 14:08:10'),
(957, 3, NULL, NULL, 'Update User', 'Updated user: Adminin (ID: 21)', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 14:08:24'),
(958, NULL, NULL, NULL, 'LOGIN_FAILURE', 'Invalid password for: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:01\",\"reason\":\"Invalid password\",\"email\":\"admin@lgu.gov.ph\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:01'),
(959, 3, NULL, NULL, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:03'),
(960, 3, NULL, NULL, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:03\",\"user_id\":3,\"email\":\"admin@lgu.gov.ph\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:03'),
(961, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:04\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:04'),
(962, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:04\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:04'),
(963, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:04\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:04'),
(964, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:04\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:04'),
(965, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:04\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:04'),
(966, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:04\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:04'),
(967, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:04\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:04'),
(968, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:04\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:04'),
(969, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:04\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:04'),
(970, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:04\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:04'),
(971, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:05\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:05'),
(972, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:05\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:05'),
(973, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:05\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:05'),
(974, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:06\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:06'),
(975, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:06\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:06'),
(976, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:06\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:06'),
(977, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:06\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:06'),
(978, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:06\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:06'),
(979, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:06\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:06'),
(980, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:06\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:06'),
(981, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:08\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:08'),
(982, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:08\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:08'),
(983, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:08\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:08'),
(984, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:08\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:08'),
(985, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:09\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:09'),
(986, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:09\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:09'),
(987, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:09\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:09'),
(988, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:09\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:09'),
(989, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:09\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:09'),
(990, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:09\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:09'),
(991, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:09\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:09'),
(992, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:09\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:09'),
(993, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:09\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:09'),
(994, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:09\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:09'),
(995, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:09\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:09'),
(996, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:09\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:09'),
(997, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:09\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:09'),
(998, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:09\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:09'),
(999, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:15\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:15'),
(1000, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:15\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:15'),
(1001, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:15\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:15'),
(1002, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:15\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:15'),
(1003, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:15\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:15'),
(1004, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:15\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:15'),
(1005, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:15\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:15'),
(1006, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:15\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:15'),
(1007, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:15\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:15'),
(1008, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:15\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:15'),
(1009, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:45\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:45'),
(1010, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:45\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:45'),
(1011, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:45\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:45'),
(1012, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:45\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:45'),
(1013, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:46\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:46'),
(1014, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:46\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:46'),
(1015, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:46\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:46'),
(1016, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:46\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:46'),
(1017, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:46\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:46'),
(1018, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:51:46\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:51:46'),
(1019, 3, NULL, NULL, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:04'),
(1020, 2, NULL, NULL, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:11'),
(1021, 2, NULL, NULL, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:11\",\"user_id\":2,\"email\":\"keithjustine57@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:11'),
(1022, 2, NULL, NULL, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:13'),
(1023, 3, NULL, NULL, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:17'),
(1024, 3, NULL, NULL, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:17\",\"user_id\":3,\"email\":\"admin@lgu.gov.ph\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:17'),
(1025, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:17\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:17'),
(1026, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:17\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:17'),
(1027, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:17\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:17'),
(1028, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:17\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:17'),
(1029, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:17\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:17'),
(1030, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:17\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:17'),
(1031, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:17\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:17'),
(1032, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:17\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:17'),
(1033, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:17\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:17'),
(1034, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:17\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:17'),
(1035, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:19\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:19'),
(1036, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:19\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:19'),
(1037, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:19\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:19'),
(1038, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:19\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:19'),
(1039, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:19\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:19'),
(1040, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:19\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:19'),
(1041, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:19\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:19'),
(1042, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:19\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:19'),
(1043, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:19\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:19'),
(1044, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:19\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:19'),
(1045, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:19\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:19'),
(1046, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:20\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:20');
INSERT INTO `activity_logs` (`id`, `user_id`, `department_id`, `related_department_id`, `action`, `description`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1047, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:20\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:20'),
(1048, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:20\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:20'),
(1049, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:20\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:20'),
(1050, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:20\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:20'),
(1051, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:21\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:21'),
(1052, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:21\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:21'),
(1053, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:21\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:21'),
(1054, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:21\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:21'),
(1055, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:21\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:21'),
(1056, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:22\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:22'),
(1057, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:22\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:22'),
(1058, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:22\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:22'),
(1059, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:22\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:22'),
(1060, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:22\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:22'),
(1061, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:22\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:22'),
(1062, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:22\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:22'),
(1063, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:22\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:22'),
(1064, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:22\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:22'),
(1065, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:22\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:22'),
(1066, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:23\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:23'),
(1067, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:24'),
(1068, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:24'),
(1069, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:24'),
(1070, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:24'),
(1071, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:24'),
(1072, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:24'),
(1073, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:24'),
(1074, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:24'),
(1075, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:24'),
(1076, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:24'),
(1077, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:24'),
(1078, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:24'),
(1079, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:24'),
(1080, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:24'),
(1081, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:24'),
(1082, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:25'),
(1083, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:25'),
(1084, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:25'),
(1085, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:27'),
(1086, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:27'),
(1087, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:27'),
(1088, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:27'),
(1089, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:27'),
(1090, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:27'),
(1091, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:27'),
(1092, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:27'),
(1093, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:27'),
(1094, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:27'),
(1095, 3, NULL, NULL, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:31'),
(1096, 2, NULL, NULL, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:38'),
(1097, 2, NULL, NULL, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:52:38\",\"user_id\":2,\"email\":\"keithjustine57@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:52:38'),
(1098, 2, NULL, NULL, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:58:39'),
(1099, NULL, NULL, NULL, 'LOGIN_FAILURE', 'Invalid password for: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:58:44\",\"reason\":\"Invalid password\",\"email\":\"admin@lgu.gov.ph\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:58:44'),
(1100, NULL, NULL, NULL, 'LOGIN_FAILURE', 'Invalid password for: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:58:47\",\"reason\":\"Invalid password\",\"email\":\"admin@lgu.gov.ph\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:58:47'),
(1101, 3, NULL, NULL, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:58:53'),
(1102, 3, NULL, NULL, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 22:58:53\",\"user_id\":3,\"email\":\"admin@lgu.gov.ph\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 14:58:53'),
(1103, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:22\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:22'),
(1104, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:22\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:22'),
(1105, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:22\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:22'),
(1106, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:22\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:22'),
(1107, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:23\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:23'),
(1108, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:23\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:23'),
(1109, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:23\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:23'),
(1110, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:23\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:23'),
(1111, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:23\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:23'),
(1112, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:23\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:23'),
(1113, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:24'),
(1114, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:24'),
(1115, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:24'),
(1116, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:24'),
(1117, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:24'),
(1118, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:24'),
(1119, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:24'),
(1120, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:25'),
(1121, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:25'),
(1122, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:25'),
(1123, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:25'),
(1124, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:25'),
(1125, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:25'),
(1126, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:25'),
(1127, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:25'),
(1128, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:25'),
(1129, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:25'),
(1130, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:25'),
(1131, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:25'),
(1132, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:25'),
(1133, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:26\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:26'),
(1134, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:26\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:26'),
(1135, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:26\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:26'),
(1136, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:26\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:26'),
(1137, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:26\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:26'),
(1138, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:26\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:26'),
(1139, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:26\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:26'),
(1140, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:26\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:26'),
(1141, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:26\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:26'),
(1142, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:27'),
(1143, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:29\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:29'),
(1144, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:29\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:29'),
(1145, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:29\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:29'),
(1146, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:29\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:29'),
(1147, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:30\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:30'),
(1148, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:30\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:30'),
(1149, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:30\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:30'),
(1150, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:30\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:30'),
(1151, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:30\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:30'),
(1152, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:30\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:30'),
(1153, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access user dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:35\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:35'),
(1154, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:35\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:35'),
(1155, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:35\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:35'),
(1156, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:35\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:35'),
(1157, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:35\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:35');
INSERT INTO `activity_logs` (`id`, `user_id`, `department_id`, `related_department_id`, `action`, `description`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1158, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:35\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:35'),
(1159, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:35\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:35'),
(1160, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:35\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:35'),
(1161, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:35\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:35'),
(1162, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:35\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:35'),
(1163, 3, NULL, NULL, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:36'),
(1164, 2, NULL, NULL, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:42'),
(1165, 2, NULL, NULL, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:04:42\",\"user_id\":2,\"email\":\"keithjustine57@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:42'),
(1166, 2, NULL, NULL, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:04:44'),
(1167, 3, NULL, NULL, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:46'),
(1168, 3, NULL, NULL, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:46\",\"user_id\":3,\"email\":\"admin@lgu.gov.ph\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:46'),
(1169, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:46\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:46'),
(1170, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:46\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:46'),
(1171, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:46\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:46'),
(1172, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:46\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:46'),
(1173, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:46\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:46'),
(1174, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:46\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:46'),
(1175, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:46\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:46'),
(1176, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:47\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:47'),
(1177, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:47\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:47'),
(1178, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:47\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:47'),
(1179, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:48\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:48'),
(1180, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:48\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:48'),
(1181, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:48\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:48'),
(1182, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:48\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:48'),
(1183, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:48\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:48'),
(1184, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:48\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:48'),
(1185, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:48\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:48'),
(1186, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:48\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:48'),
(1187, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:48\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:48'),
(1188, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:48\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:48'),
(1189, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:53\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:53'),
(1190, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:54\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:54'),
(1191, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:54\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:54'),
(1192, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:54\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:54'),
(1193, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:54\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:54'),
(1194, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:54\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:54'),
(1195, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:54\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:54'),
(1196, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:54\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:54'),
(1197, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:54\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:54'),
(1198, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:05:54\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:05:54'),
(1199, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:06:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:06:24'),
(1200, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:06:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:06:24'),
(1201, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:06:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:06:24'),
(1202, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:06:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:06:24'),
(1203, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:06:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:06:24'),
(1204, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:06:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:06:24'),
(1205, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:06:24\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:06:24'),
(1206, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:06:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:06:25'),
(1207, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:06:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:06:25'),
(1208, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:06:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:06:25'),
(1209, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:07:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:07:25'),
(1210, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:07:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:07:25'),
(1211, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:07:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:07:25'),
(1212, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:07:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:07:25'),
(1213, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:07:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:07:25'),
(1214, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:07:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:07:25'),
(1215, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:07:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:07:25'),
(1216, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:07:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:07:25'),
(1217, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:07:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:07:25'),
(1218, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:07:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:07:25'),
(1219, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:12:26\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:12:26'),
(1220, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:12:26\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:12:26'),
(1221, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:12:26\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:12:26'),
(1222, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:12:26\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:12:26'),
(1223, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:12:26\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:12:26'),
(1224, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:12:26\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:12:26'),
(1225, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:12:26\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:12:26'),
(1226, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:12:26\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:12:26'),
(1227, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:12:26\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:12:26'),
(1228, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:12:26\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:12:26'),
(1229, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:22:26\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:22:26'),
(1230, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:22:26\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:22:26'),
(1231, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:22:26\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:22:26'),
(1232, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:22:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:22:27'),
(1233, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:22:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:22:27'),
(1234, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:22:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:22:27'),
(1235, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:22:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:22:27'),
(1236, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:22:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:22:27'),
(1237, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:22:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:22:27'),
(1238, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:22:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:22:27'),
(1239, NULL, NULL, NULL, 'LOGIN_FAILURE', 'Invalid password for: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:34\",\"reason\":\"Invalid password\",\"email\":\"admin@lgu.gov.ph\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:34'),
(1240, 3, NULL, NULL, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:36'),
(1241, 3, NULL, NULL, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:36\",\"user_id\":3,\"email\":\"admin@lgu.gov.ph\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:36'),
(1242, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:36'),
(1243, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:36'),
(1244, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:36'),
(1245, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:36'),
(1246, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:36\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:36'),
(1247, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:37\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:37'),
(1248, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:37\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:37'),
(1249, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:37\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:37'),
(1250, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:37\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:37'),
(1251, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:37\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:37'),
(1252, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:38\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:38'),
(1253, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:38\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:38'),
(1254, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:38\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:38'),
(1255, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:38\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:38'),
(1256, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:38\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:38'),
(1257, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:38\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:38'),
(1258, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:38\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:38'),
(1259, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:38\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:38'),
(1260, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:38\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:38'),
(1261, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:38\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:38'),
(1262, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:43\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:43'),
(1263, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:43\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:43'),
(1264, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:43\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:43'),
(1265, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:44\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:44'),
(1266, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:44\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:44'),
(1267, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:44\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:44'),
(1268, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:44\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:44'),
(1269, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:44\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:44');
INSERT INTO `activity_logs` (`id`, `user_id`, `department_id`, `related_department_id`, `action`, `description`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1270, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:44\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:44'),
(1271, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:29:44\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:29:44'),
(1272, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access user dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:30:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:30:25'),
(1273, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:30:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:30:25'),
(1274, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:30:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:30:25'),
(1275, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:30:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:30:25'),
(1276, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:30:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:30:25'),
(1277, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:30:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:30:25'),
(1278, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:30:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:30:25'),
(1279, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:30:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:30:25'),
(1280, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:30:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:30:25'),
(1281, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:30:25\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:30:25'),
(1282, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:30:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:30:27'),
(1283, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:30:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:30:27'),
(1284, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:30:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:30:27'),
(1285, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:30:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:30:27'),
(1286, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:30:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:30:27'),
(1287, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:30:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:30:27'),
(1288, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:30:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:30:27'),
(1289, 3, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access admin dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:30:27\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:30:27'),
(1290, 3, NULL, NULL, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:30:28'),
(1291, 2, NULL, NULL, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:30:32'),
(1292, 2, NULL, NULL, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:30:32\",\"user_id\":2,\"email\":\"keithjustine57@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:30:32'),
(1293, 2, NULL, NULL, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:31:58'),
(1294, 3, NULL, NULL, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:32:03'),
(1295, 3, NULL, NULL, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:32:03\",\"user_id\":3,\"email\":\"admin@lgu.gov.ph\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:32:03'),
(1296, 3, NULL, NULL, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:41:56'),
(1297, 24, NULL, NULL, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:42:18'),
(1298, 24, NULL, NULL, 'LOGIN_SUCCESS', 'User ID: 24, Email: DEPTADMIN@TEST.COM', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-29 23:42:18\",\"user_id\":24,\"email\":\"DEPTADMIN@TEST.COM\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 15:42:18'),
(1299, 24, NULL, NULL, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 15:44:05'),
(1300, 24, NULL, NULL, 'LOGIN_SUCCESS', 'User ID: 24, Email: deptadmin@test.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-29 23:44:05\",\"user_id\":24,\"email\":\"deptadmin@test.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 15:44:05'),
(1301, 24, NULL, NULL, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 15:46:30'),
(1302, 3, NULL, NULL, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 15:46:34'),
(1303, 3, NULL, NULL, 'LOGIN_SUCCESS', 'User ID: 3, Email: admin@lgu.gov.ph', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/144.0.0.0 Safari\\/537.36\",\"timestamp\":\"2026-01-29 23:46:34\",\"user_id\":3,\"email\":\"admin@lgu.gov.ph\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 15:46:34'),
(1304, 3, NULL, NULL, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:55:34'),
(1305, 24, NULL, NULL, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:55:41'),
(1306, 24, NULL, NULL, 'LOGIN_SUCCESS', 'User ID: 24, Email: deptadmin@test.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-29 23:55:41\",\"user_id\":24,\"email\":\"deptadmin@test.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 15:55:41'),
(1307, 24, NULL, NULL, 'UNAUTHORIZED_ACCESS', 'Attempt to access user dashboard', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-30 00:16:55\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 16:16:55'),
(1308, 24, NULL, NULL, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 16:16:59'),
(1309, 3, NULL, NULL, 'Create User', 'Created new user: Admin OCM (ID: 25)', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 16:26:25'),
(1310, 24, NULL, NULL, 'Logout', 'User logged out', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 16:26:35'),
(1311, NULL, NULL, NULL, 'LOGIN_FAILURE', 'Unverified email: OCMAdmin@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-30 00:26:41\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 16:26:41'),
(1312, 25, NULL, NULL, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 16:29:00'),
(1313, 25, NULL, NULL, 'LOGIN_SUCCESS', 'User ID: 25, Email: OCMAdmin@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/143.0.0.0 Safari\\/537.36 Edg\\/143.0.0.0\",\"timestamp\":\"2026-01-30 00:29:00\",\"user_id\":25,\"email\":\"OCMAdmin@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 16:29:00'),
(1314, 25, NULL, NULL, 'Update Application Status', 'Updated application CRMN-2026-949441 from pending to Rejected', '{\"application_id\":54,\"tracking_number\":\"CRMN-2026-949441\",\"old_status\":\"pending\",\"new_status\":\"Rejected\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 17:06:58'),
(1315, 25, NULL, NULL, 'Update Application Status', 'Updated application CRMN-2026-949441 from rejected to Approved', '{\"application_id\":54,\"tracking_number\":\"CRMN-2026-949441\",\"old_status\":\"rejected\",\"new_status\":\"Approved\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 17:08:12'),
(1316, 25, NULL, NULL, 'Update Application Status', 'Updated application CRMN-2026-949441 from approved to Rejected', '{\"application_id\":54,\"tracking_number\":\"CRMN-2026-949441\",\"old_status\":\"approved\",\"new_status\":\"Rejected\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 17:08:27'),
(1317, 25, NULL, NULL, 'Update Application Status', 'Updated application CRMN-2026-965351 from pending to Approved', '{\"application_id\":51,\"tracking_number\":\"CRMN-2026-965351\",\"old_status\":\"pending\",\"new_status\":\"Approved\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 17:12:29'),
(1318, 25, NULL, NULL, 'Update Application Status', 'Updated application CRMN-2026-949441 from rejected to Processing', '{\"application_id\":54,\"tracking_number\":\"CRMN-2026-949441\",\"old_status\":\"rejected\",\"new_status\":\"Processing\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 17:13:08'),
(1319, 25, NULL, NULL, 'Update Application Status', 'Updated application CRMN-2026-949441 from processing to Approved', '{\"application_id\":54,\"tracking_number\":\"CRMN-2026-949441\",\"old_status\":\"processing\",\"new_status\":\"Approved\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 17:19:08'),
(1320, 25, NULL, NULL, 'Update Application Status', 'Updated application CRMN-2026-949441 from approved to Processing', '{\"application_id\":54,\"tracking_number\":\"CRMN-2026-949441\",\"old_status\":\"approved\",\"new_status\":\"Processing\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 17:19:26'),
(1321, 25, NULL, NULL, 'Update Application Status', 'Updated application CRMN-2026-949441 from processing to Rejected', '{\"application_id\":54,\"tracking_number\":\"CRMN-2026-949441\",\"old_status\":\"processing\",\"new_status\":\"Rejected\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 17:20:29'),
(1322, 25, NULL, NULL, 'Update Application Status', 'Updated application CRMN-2026-949441 from rejected to Approved', '{\"application_id\":54,\"tracking_number\":\"CRMN-2026-949441\",\"old_status\":\"rejected\",\"new_status\":\"Approved\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 17:20:51'),
(1323, 25, NULL, NULL, 'Update Application Status', 'Updated application CRMN-2026-949441 from approved to Rejected', '{\"application_id\":54,\"tracking_number\":\"CRMN-2026-949441\",\"old_status\":\"approved\",\"new_status\":\"Rejected\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 17:21:05'),
(1324, 25, NULL, NULL, 'Update Application Status', 'Updated application CRMN-2026-949441 from rejected to Approved', '{\"application_id\":54,\"tracking_number\":\"CRMN-2026-949441\",\"old_status\":\"rejected\",\"new_status\":\"Approved\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 17:23:32'),
(1325, 2, NULL, NULL, 'Login', 'User logged in', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 17:23:55'),
(1326, 2, NULL, NULL, 'LOGIN_SUCCESS', 'User ID: 2, Email: keithjustine57@gmail.com', '{\"ip\":\"::1\",\"user_agent\":\"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/142.0.0.0 Safari\\/537.36 OPR\\/126.0.0.0\",\"timestamp\":\"2026-01-30 01:23:55\",\"user_id\":2,\"email\":\"keithjustine57@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 17:23:55'),
(1327, 25, NULL, NULL, 'Update Application Status', 'Updated application CRMN-2026-949441 from approved to Processing', '{\"application_id\":54,\"tracking_number\":\"CRMN-2026-949441\",\"old_status\":\"approved\",\"new_status\":\"Processing\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 17:29:51'),
(1328, 25, NULL, NULL, 'Update Application Status', 'Updated application CRMN-2026-949441 from processing to Pending', '{\"application_id\":54,\"tracking_number\":\"CRMN-2026-949441\",\"old_status\":\"processing\",\"new_status\":\"Pending\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 17:30:03'),
(1329, 25, NULL, NULL, 'Update Application Status', 'Updated application CRMN-2026-949441 from pending to Approved', '{\"application_id\":54,\"tracking_number\":\"CRMN-2026-949441\",\"old_status\":\"pending\",\"new_status\":\"Approved\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 17:37:33'),
(1330, 25, NULL, NULL, 'Update Application Status', 'Updated application CRMN-2026-949441 from approved to Rejected', '{\"application_id\":54,\"tracking_number\":\"CRMN-2026-949441\",\"old_status\":\"approved\",\"new_status\":\"Rejected\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 17:38:01'),
(1331, 2, NULL, NULL, 'Submit Payment', 'Submitted payment for application CRMN-2026-827607 - Status changed to Paid', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36 OPR/126.0.0.0', '2026-01-29 17:52:36'),
(1332, 3, NULL, NULL, 'Verify Payment', 'Verified payment for application CRMN-2026-827607', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 17:52:59'),
(1333, 25, 61, 61, 'Update Application Status', 'Updated application CRMN-2026-949441 from rejected to Completed', '{\"application_id\":54,\"tracking_number\":\"CRMN-2026-949441\",\"old_status\":\"rejected\",\"new_status\":\"Completed\",\"email_sent\":true,\"sms_sent\":false}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', '2026-01-29 18:38:47');

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
  `status` enum('pending','processing','approved','paid','completed','rejected','cancelled') DEFAULT 'pending',
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
(1, 2, 61, 92, 'Request Data for Thesis/Research', 'CRMN-2026-309487', '4', '', 'assets/uploads/Topic_2_20260122_141514.pdf', 556487, '', '', 'approved', 1, 51.00, 'pending', '2026-01-25 22:38:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-22 06:15:14', '2026-01-22 14:38:19'),
(2, 2, 63, 94, 'Issuance of Certification', 'CRMN-2026-137077', '4', 'f', 'assets/uploads/2-FOR-Capstone-Project-Concept-Paper-Template-updated-letterhead_20260122_141548.pdf', 575054, '', '', 'approved', 1, 10.00, 'pending', '2026-01-25 22:37:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-22 06:15:48', '2026-01-22 14:37:12'),
(3, 2, 63, 94, 'Issuance of Certification', 'CRMN-2026-012806', '4', '', 'assets/uploads/Doc1_20260122_141827.pdf', 243428, '', '', 'approved', 1, 124321.00, 'verified', '2026-01-25 22:36:16', NULL, 'gsdf', 'assets/uploads/payments/payment_3_1769136937.jpg', 3048992, 'fsa', '2026-01-23 10:55:37', 3, '2026-01-23 16:38:33', NULL, NULL, 0.00, 0.00, NULL, '2026-01-22 06:18:27', '2026-01-23 08:38:33'),
(4, 2, 31, 40, 'Mayor\'s Endorsement', 'CRMN-2026-483625', '5t13', '', 'assets/uploads/Rank_2_20260122_142024.pdf', 556495, '', '', 'approved', 1, 4512.00, 'pending', '2026-01-26 10:10:49', NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, 'f', NULL, 0.00, 0.00, NULL, '2026-01-22 06:20:24', '2026-01-25 13:12:54'),
(5, 2, 31, 40, 'Mayor\'s Endorsement', 'CRMN-2026-403300', 'r21', '', 'assets/uploads/Topic_2_20260123_101022.pdf', 556487, '', '', 'completed', 1, 1111.00, 'verified', '2026-01-26 10:11:57', NULL, 'gega', 'assets/uploads/payments/payment_5_1769135735.jpg', 3048992, '', '2026-01-23 10:35:35', 3, '2026-01-23 16:40:22', NULL, NULL, 0.00, 0.00, NULL, '2026-01-23 02:10:22', '2026-01-23 08:40:22'),
(6, 2, 62, 93, 'Tricycle Franchise Dropping', 'CRMN-2026-994356', '5123', 'r123', 'assets/uploads/Topic_2_20260123_160004.pdf', 556487, '', '', 'completed', 1, 300.00, 'verified', '2026-01-26 16:11:40', NULL, 'fasfas', 'assets/uploads/payments/payment_6_1769156109.jpg', 3048992, '', '2026-01-23 16:15:09', 3, '2026-01-23 16:38:14', NULL, NULL, 0.00, 0.00, NULL, '2026-01-23 08:00:04', '2026-01-23 08:38:14'),
(7, 2, 31, 40, 'Mayor\'s Endorsement', 'CRMN-2026-353892', 'gwaw', '', 'assets/uploads/Topic_2_20260123_164115.pdf', 556487, '', '', 'paid', 1, 100.00, 'verified', '2026-01-26 16:42:21', NULL, '4231', 'assets/uploads/payments/payment_7_1769291243.jpg', 3048992, '3', '2026-01-25 05:47:23', 3, '2026-01-25 05:48:13', NULL, NULL, 0.00, 0.00, NULL, '2026-01-23 08:41:15', '2026-01-24 21:48:13'),
(8, 2, 61, 91, 'Feedback Processing', 'CRMN-2026-289462', 'f', 'f', 'assets/uploads/Topic_2_20260123_165610.pdf', 556487, '', '', 'completed', 1, 2.00, 'verified', '2026-01-26 16:56:28', NULL, 'fgSDAS', 'assets/uploads/payments/payment_8_1769259867.png', 120165, '', '2026-01-24 21:04:27', 3, '2026-01-24 22:46:17', NULL, NULL, 0.00, 0.00, NULL, '2026-01-23 08:56:10', '2026-01-24 14:46:53'),
(9, 2, 61, 91, 'Feedback Processing', 'CRMN-2026-813237', '5', '412', 'assets/uploads/Topic_2_20260123_175337.pdf', 556487, '', '', 'completed', 1, 2.00, 'verified', '2026-01-26 18:39:00', NULL, '4123', 'assets/uploads/payments/payment_9_1769164780.png', 120165, '', '2026-01-23 18:39:40', 3, '2026-01-24 19:21:35', NULL, NULL, 0.00, 0.00, NULL, '2026-01-23 09:53:37', '2026-01-24 11:25:13'),
(10, 2, 31, 88, 'OTR (Occupational Tax Receipt)', 'CRMN-2026-544595', '45123', '', 'assets/uploads/Rank_2_20260124_225346.pdf', 556495, '', '', 'approved', 1, 4123.00, 'verified', '2026-01-27 22:54:23', NULL, '42', 'assets/uploads/payments/payment_10_1769284023.jpg', 3048992, '', '2026-01-25 03:47:03', 3, '2026-01-25 03:47:36', NULL, NULL, 0.00, 0.00, NULL, '2026-01-24 14:53:46', '2026-01-25 13:18:42'),
(11, 2, 61, 91, 'Feedback Processing', 'CRMN-2026-065156', '41', '', 'assets/uploads/Topic_2_20260125_210707.pdf', 556487, '', '', 'approved', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-25 13:07:07', '2026-01-25 13:17:53'),
(12, 2, 60, 105, 'Professional Tax Receipt', 'CRMN-2026-520447', 'fas', '', 'assets/uploads/Topic_2_20260125_211007.pdf', 556487, '', '', 'approved', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-25 13:10:07', '2026-01-25 13:20:25'),
(13, 2, 31, 88, 'OTR (Occupational Tax Receipt)', 'CRMN-2026-673697', 'r2', '', 'assets/uploads/QRPh_Integration_Guide_-_Best_Option_for_LGUs__20260125_212059.pdf', 537508, '', '', 'approved', 1, 50.00, 'pending', '2026-01-28 22:09:16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-25 13:20:59', '2026-01-25 14:09:16'),
(15, 2, 62, 93, 'Tricycle Franchise Dropping', 'CRMN-2026-462816', '42', '', 'assets/uploads/Topic_2_20260126_021809.pdf', 556487, NULL, '', 'cancelled', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-25 18:18:09', '2026-01-29 06:21:46'),
(16, 2, 31, 88, 'OTR (Occupational Tax Receipt)', 'CRMN-2026-053670', 'r', '', 'assets/uploads/Topic_2_20260126_022131.pdf', 556487, NULL, '', 'cancelled', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-25 18:21:31', '2026-01-27 05:10:56'),
(17, 2, 64, 95, 'Evaluation and Recommendation for Electrical and Water Connection', 'CRMN-2026-315126', '2', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31_20260126_060237.pdf', 91825, '', '', 'approved', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-25 22:02:37', '2026-01-25 22:04:08'),
(18, 2, 63, 94, 'Issuance of Certification', 'CRMN-2026-931368', '2', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31_20260126_060458.pdf', 91825, '', '', 'approved', 1, 90.00, 'pending', '2026-01-29 06:05:28', NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, 'boom', NULL, 0.00, 0.00, NULL, '2026-01-25 22:04:58', '2026-01-25 22:48:41'),
(19, 2, 31, 88, 'OTR (Occupational Tax Receipt)', 'CRMN-2026-355514', '42', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31_20260126_062721.pdf', 91825, '', '', 'paid', 1, 50.00, 'verified', '2026-01-29 06:28:16', NULL, '24141242142187632167321', 'assets/uploads/payments/payment_19_1769380165.jpg', 66185, '', '2026-01-26 06:29:25', 3, '2026-01-26 06:36:44', NULL, NULL, 0.00, 0.00, NULL, '2026-01-25 22:27:21', '2026-01-25 22:36:44'),
(20, 2, 64, 95, 'Evaluation and Recommendation for Electrical and Water Connection', 'CRMN-2026-847773', '2321', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31_20260126_062738.pdf', 91825, '', '', 'approved', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-25 22:27:38', '2026-01-25 22:28:30'),
(21, 2, 63, 94, 'Issuance of Certification', 'CRMN-2026-690228', 'f', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31_20260126_065020.pdf', 91825, '', 'fasdf', 'paid', 1, 90.00, 'verified', '2026-01-29 07:18:55', NULL, '1412412', 'assets/uploads/payments/payment_21_1769383768.jpg', 66185, '4124124214214', '2026-01-26 07:29:28', 3, '2026-01-26 07:29:41', 'g', NULL, 0.00, 0.00, NULL, '2026-01-25 22:50:20', '2026-01-25 23:29:41'),
(22, 2, 31, 87, 'Permit for Motorcade', 'CRMN-2026-102477', '`rt31', '', 'assets/uploads/Report_2026-01-23_to_2026-01-24__1__20260126_073411.pdf', 66324, '', 'r31r13', 'rejected', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, 'f', NULL, 0.00, 0.00, NULL, '2026-01-25 23:34:11', '2026-01-26 05:23:11'),
(23, 2, 31, 88, 'OTR (Occupational Tax Receipt)', 'CRMN-2026-487666', 'fsa', '', 'assets/uploads/Report_2026-01-23_to_2026-01-24_20260126_094802.pdf', 66324, '', '', 'cancelled', 0, NULL, NULL, NULL, NULL, 'fgasfa', 'assets/uploads/payments/payment_23_1769401334.jpg', 3048992, '', '2026-01-26 12:22:14', 3, '2026-01-26 12:22:29', 'her', NULL, 0.00, 0.00, NULL, '2026-01-26 01:48:02', '2026-01-27 05:10:56'),
(24, 2, 64, 95, 'Evaluation and Recommendation for Electrical and Water Connection', 'CRMN-2026-815214', 'rawr', '', 'assets/uploads/Report_2026-01-23_to_2026-01-24__1__20260126_100313.pdf', 66324, '', '', 'cancelled', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-26 02:03:13', '2026-01-27 05:10:56'),
(25, 2, 31, 88, 'OTR (Occupational Tax Receipt)', 'CRMN-2026-878666', 'fs', '', 'assets/uploads/Report_2026-01-23_to_2026-01-24_20260126_134027.pdf', 66324, '', '', 'rejected', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-26 05:40:27', '2026-01-26 12:47:25'),
(26, 2, 63, 94, 'Issuance of Certification', 'CRMN-2026-128368', 'fasfs', '', 'assets/uploads/Ababao__Keith_Justine_S__-_FINALS_ONLINE_ACTIVITY_20260126_134135.pdf', 601532, '', '', 'cancelled', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-26 05:41:35', '2026-01-27 05:10:56'),
(27, 2, 61, 91, 'Feedback Processing', 'CRMN-2026-248079', 'fas', '', 'assets/uploads/2-FOR-Capstone-Project-Concept-Paper-Template-updated-letterhead_20260126_134220.pdf', 575054, '', '', 'completed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-26 05:42:20', '2026-01-26 12:51:06'),
(28, 2, 63, 94, 'Issuance of Certification', 'CRMN-2026-789543', 'f', '', 'assets/uploads/LIST_OF_PAID_20260126_134528.pdf', 116524, '', '', 'paid', 1, 90.00, 'verified', '2026-01-29 16:56:12', NULL, 'fasf21424', 'assets/uploads/payments/payment_28_1769668616.jpg', 3048992, '', '2026-01-29 14:36:56', 3, '2026-01-29 18:40:39', NULL, NULL, 0.00, 0.00, NULL, '2026-01-26 05:45:28', '2026-01-29 10:40:39'),
(29, 2, 31, 87, 'Permit for Motorcade', 'CRMN-2026-110566', 'fASf', '', 'assets/uploads/carmonaopp_db_20260126_172456.pdf', 727858, 'Wrong files boss', '', 'rejected', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-26 09:24:56', '2026-01-27 04:16:21'),
(30, 2, 31, 88, 'OTR (Occupational Tax Receipt)', 'CRMN-2026-274777', 'fwf', '', 'assets/uploads/Admin_Header_Debug_Tool_20260126_212154.pdf', 926597, 'Hehe joke lang completed na', '', 'completed', 0, NULL, NULL, NULL, NULL, 'fsa241412', 'assets/uploads/payments/payment_30_1769483728.jpg', 3048992, '', '2026-01-27 11:15:28', NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-26 13:21:54', '2026-01-27 04:57:29'),
(31, 2, 31, 88, 'OTR (Occupational Tax Receipt)', 'CRMN-2026-353167', 'asfasfas', '', 'assets/uploads/carmonaopp_db_20260127_083221.pdf', 727858, '', '', 'cancelled', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-27 00:32:21', '2026-01-27 05:10:56'),
(32, 2, 31, 87, 'Permit for Motorcade', 'CRMN-2026-213328', 'fasfas', '', 'assets/uploads/RUBRICS-WEB-DEVELOPMENT-PROJECT-PRESENTATION_20260127_132938.pdf', 178618, '', '', 'rejected', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-27 05:29:38', '2026-01-29 05:52:16'),
(33, 2, 61, 91, 'Feedback Processing', 'CRMN-2026-972363', 'fsa', 'fasf', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_143744.pdf', 114314, NULL, '', 'cancelled', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-29 06:37:44', '2026-01-29 06:50:28'),
(34, 2, 31, 40, 'Mayor\'s Endorsement', 'CRMN-2026-504254', 'f', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_150800.pdf', 114314, '', '', 'approved', 1, 50.00, 'rejected', '2026-02-01 15:11:38', NULL, NULL, NULL, NULL, '', NULL, NULL, NULL, 'f12', NULL, 0.00, 0.00, NULL, '2026-01-29 07:08:00', '2026-01-29 11:44:31'),
(35, 2, 62, 93, 'Tricycle Franchise Dropping', 'CRMN-2026-874566', '4124', '', 'assets/uploads/Rank_2_20260129_151244.pdf', 556495, NULL, '21', 'pending', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-29 07:12:44', '2026-01-29 07:12:44'),
(36, 2, 31, 40, 'Mayor\'s Endorsement', 'CRMN-2026-900569', '412', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_151600.pdf', 114314, NULL, '', 'pending', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-29 07:16:00', '2026-01-29 07:16:00'),
(37, 2, 61, 92, 'Request Data for Thesis/Research', 'CRMN-2026-446422', '41241', '421', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_151656.pdf', 114314, NULL, '', 'pending', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-29 07:16:56', '2026-01-29 07:16:56'),
(38, 2, 62, 93, 'Tricycle Franchise Dropping', 'CRMN-2026-917310', '553212', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_151728.pdf', 114314, NULL, '', 'pending', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-29 07:17:28', '2026-01-29 07:17:28'),
(39, 2, 60, 102, 'Real Property Tax Payment', 'CRMN-2026-088536', 'rqwr3151', 'fr12', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_153028.pdf', 114314, NULL, '14124', 'pending', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-29 07:30:28', '2026-01-29 07:30:28'),
(40, 2, 31, 40, 'Mayor\'s Endorsement', 'CRMN-2026-500954', '412', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31__1__20260129_153232.pdf', 104319, NULL, '', 'pending', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-29 07:32:32', '2026-01-29 07:32:32'),
(41, 2, 63, 94, 'Issuance of Certification', 'CRMN-2026-920458', '412412', '421412', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_153345.pdf', 114314, NULL, '', 'pending', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-29 07:33:45', '2026-01-29 07:33:45'),
(42, 2, 62, 93, 'Tricycle Franchise Dropping', 'CRMN-2026-398123', '4512412', '412412', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_153407.pdf', 114314, NULL, '', 'pending', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-29 07:34:07', '2026-01-29 07:34:07'),
(43, 2, 61, 91, 'Feedback Processing', 'CRMN-2026-715210', '51212', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_153826.pdf', 114314, '', '', 'cancelled', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-29 07:38:26', '2026-01-29 10:15:40'),
(44, 2, 61, 91, 'Feedback Processing', 'CRMN-2026-179494', '512351', '512512', 'assets/uploads/View_Application_-_Carmona_Online_Permit_Portal_20260129_154054.pdf', 619873, NULL, '', 'pending', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-29 07:40:54', '2026-01-29 07:40:54'),
(45, 2, 31, 40, 'Mayor\'s Endorsement', 'CRMN-2026-239525', '512512', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_154713.pdf', 114314, NULL, '241', 'pending', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-29 07:47:13', '2026-01-29 07:47:13'),
(46, 2, 63, 94, 'Issuance of Certification', 'CRMN-2026-226241', '51512', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_154740.pdf', 114314, NULL, '', 'pending', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-29 07:47:40', '2026-01-29 07:47:40'),
(47, 2, 31, 40, 'Mayor\'s Endorsement', 'CRMN-2026-842957', 'tq214514', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31__1__20260129_154811.pdf', 104319, NULL, '', 'pending', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-29 07:48:11', '2026-01-29 07:48:11'),
(48, 2, 31, 86, 'Certification of No Major Source of Income/No Business', 'CRMN-2026-122976', '4', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_155000.pdf', 114314, NULL, '', 'pending', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-29 07:50:00', '2026-01-29 07:50:00'),
(49, 2, 63, 94, 'Issuance of Certification', 'CRMN-2026-104736', '5 1231', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_155519.pdf', 114314, NULL, '', 'pending', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-29 07:55:19', '2026-01-29 07:55:19'),
(50, 2, 31, 87, 'Permit for Motorcade', 'CRMN-2026-229838', 'e21', '', 'assets/uploads/Report_2026-01-01_to_2026-01-22_20260129_170904.pdf', 78229, NULL, '', 'pending', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-29 09:09:04', '2026-01-29 09:09:04'),
(51, 2, 61, 91, 'Feedback Processing', 'CRMN-2026-965351', '451241', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_173215.pdf', 114314, '', '', 'approved', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-29 09:32:15', '2026-01-29 17:12:24'),
(52, 2, 32, 90, 'Sorteo ng Carmona Registration', 'CRMN-2026-064082', '421412', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_173246.pdf', 114314, NULL, '', 'pending', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-29 09:32:46', '2026-01-29 09:32:46'),
(53, 2, 62, 93, 'Tricycle Franchise Dropping', 'CRMN-2026-915558', '42141', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_173323.pdf', 114314, NULL, '54124', 'pending', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-29 09:33:23', '2026-01-29 09:33:23'),
(54, 2, 61, 91, 'Feedback Processing', 'CRMN-2026-949441', '41241', '4124', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_173806.pdf', 114314, '', '', 'completed', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-29 09:38:06', '2026-01-29 18:38:42'),
(55, 2, 62, 93, 'Tricycle Franchise Dropping', 'CRMN-2026-088553', '41', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_174056.pdf', 114314, NULL, '', 'pending', 0, 0.00, 'not_required', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-29 09:40:56', '2026-01-29 09:40:56'),
(56, 2, 62, 93, 'Tricycle Franchise Dropping', 'CRMN-2026-827607', '42141', '5125', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_174530.pdf', 114314, '', '', 'paid', 1, 50.00, 'verified', '2026-02-02 01:50:50', NULL, 'ee1312312', 'assets/uploads/payments/payment_56_1769709156.jpg', 66185, '', '2026-01-30 01:52:36', 3, '2026-01-30 01:52:59', NULL, NULL, 0.00, 0.00, NULL, '2026-01-29 09:45:30', '2026-01-29 17:52:59'),
(57, 2, 62, 93, 'Tricycle Franchise Dropping', 'CRMN-2026-047439', '41242141', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_174605.pdf', 114314, '', '', 'paid', 1, 50.00, 'verified', '2026-02-01 18:01:53', NULL, '215124', 'assets/uploads/payments/payment_57_1769680938.png', 120165, '', '2026-01-29 18:02:18', 3, '2026-01-29 18:03:02', NULL, NULL, 0.00, 0.00, NULL, '2026-01-29 09:46:05', '2026-01-29 10:03:02'),
(58, 2, 61, 91, 'Feedback Processing', 'CRMN-2026-421344', '531', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_184110.pdf', 114314, NULL, '', 'cancelled', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, '2026-01-29 10:41:10', '2026-01-29 10:56:03'),
(59, 2, 31, 40, 'Mayor\'s Endorsement', 'CRMN-2026-691968', '421412', '', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_185413.pdf', 114314, '', '', 'approved', 1, 50.00, 'rejected', '2026-02-01 18:54:59', NULL, NULL, NULL, NULL, '52151', NULL, NULL, NULL, 'ge', NULL, 0.00, 0.00, NULL, '2026-01-29 10:54:13', '2026-01-29 11:57:38');

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
,`status` enum('pending','processing','approved','paid','completed','rejected','cancelled')
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
(65, 21, 'Approved', 'Payment rejected - Status reverted to Approved. Reason: g', 3, '2026-01-25 22:51:42'),
(66, 21, 'Rejected', '', 3, '2026-01-25 23:05:25'),
(67, 21, 'Approved', '\n\nPayment Required: ₱90.00\nDeadline: Jan 29, 2026 07:18 AM', 3, '2026-01-25 23:18:55'),
(68, 21, 'Paid', 'Status automatically changed to Paid after user submitted payment proof', 2, '2026-01-25 23:19:12'),
(69, 21, 'Approved', 'Payment rejected - Status reverted to Approved. Reason: g', 3, '2026-01-25 23:19:59'),
(70, 21, 'Paid', 'Status automatically changed to Paid after user submitted payment proof', 2, '2026-01-25 23:29:28'),
(71, 21, 'Paid', 'Payment verified by admin - Ready for document claiming', 3, '2026-01-25 23:29:41'),
(72, 22, 'Approved', '\n\nPayment Required: ₱50.00\nDeadline: Jan 29, 2026 07:34 AM', 3, '2026-01-25 23:34:24'),
(73, 22, 'Paid', 'Status automatically changed to Paid after user submitted payment proof', 2, '2026-01-25 23:34:49'),
(74, 22, 'Approved', 'Payment rejected - Status reverted to Approved. Reason: f', 3, '2026-01-25 23:35:00'),
(75, 22, 'Pending', '', 3, '2026-01-25 23:57:13'),
(76, 22, 'Processing', '', 3, '2026-01-25 23:57:21'),
(77, 22, 'Approved', '\n\nPayment Required: ₱50.00\nDeadline: Jan 29, 2026 07:58 AM', 3, '2026-01-25 23:58:15'),
(78, 22, 'Rejected', '', 3, '2026-01-25 23:58:32'),
(79, 22, 'Approved', '\n\nPayment Required: ₱50.00\nDeadline: Jan 29, 2026 08:01 AM', 3, '2026-01-26 00:01:05'),
(80, 22, 'Pending', '', 3, '2026-01-26 00:01:36'),
(81, 22, 'Approved', '\n\nPayment Required: ₱50.00\nDeadline: Jan 29, 2026 08:09 AM', 3, '2026-01-26 00:09:33'),
(82, 22, 'Approved', 'w\n\nPayment Required: ₱50.00\nDeadline: Jan 29, 2026 08:27 AM', 3, '2026-01-26 00:27:11'),
(83, 22, 'Rejected', '', 3, '2026-01-26 00:29:06'),
(84, 22, 'Approved', '\n\nPayment Required: ₱50.00\nDeadline: Jan 29, 2026 08:29 AM', 3, '2026-01-26 00:29:39'),
(85, 22, 'Processing', '', 3, '2026-01-26 00:30:03'),
(86, 22, 'Approved', '\n\nPayment Required: ₱50.00\nDeadline: Jan 29, 2026 08:30 AM', 3, '2026-01-26 00:30:24'),
(87, 22, 'Approved', 'f\n\nPayment Required: ₱50.00\nDeadline: Jan 29, 2026 08:30 AM', 3, '2026-01-26 00:30:55'),
(88, 22, 'Approved', 'f\n\nPayment Required: ₱50.00\nDeadline: Jan 29, 2026 08:31 AM', 3, '2026-01-26 00:31:21'),
(89, 22, 'Rejected', '', 3, '2026-01-26 00:37:48'),
(90, 24, 'Processing', '', 3, '2026-01-26 02:24:12'),
(91, 24, 'Completed', '', 3, '2026-01-26 02:33:18'),
(92, 24, 'Pending', '', 3, '2026-01-26 02:33:27'),
(93, 24, 'Processing', '', 3, '2026-01-26 02:33:32'),
(94, 24, 'Rejected', '', 3, '2026-01-26 02:33:40'),
(95, 24, 'Approved', '', 3, '2026-01-26 03:06:42'),
(96, 22, 'Approved', '\n\nPayment Required: ₱50.00\nDeadline: Jan 29, 2026 12:18 PM', 3, '2026-01-26 04:18:44'),
(97, 23, 'Approved', '\n\nPayment Required: ₱50.00\nDeadline: Jan 29, 2026 12:19 PM', 3, '2026-01-26 04:19:30'),
(98, 23, 'Paid', 'Status automatically changed to Paid after user submitted payment proof', 2, '2026-01-26 04:20:34'),
(99, 23, 'Approved', 'Payment rejected - Status reverted to Approved. Reason: her', 3, '2026-01-26 04:21:22'),
(100, 23, 'Paid', 'Status automatically changed to Paid after user submitted payment proof', 2, '2026-01-26 04:22:14'),
(101, 23, 'Paid', 'Payment verified by admin - Ready for document claiming', 3, '2026-01-26 04:22:29'),
(102, 24, 'Completed', '', 3, '2026-01-26 04:23:12'),
(103, 23, 'Completed', '', 3, '2026-01-26 04:23:41'),
(104, 24, 'Processing', '', 3, '2026-01-26 05:20:21'),
(105, 22, 'Rejected', '', 3, '2026-01-26 05:23:11'),
(106, 28, 'Approved', '\n\nPayment Required: ₱90.00\nDeadline: Jan 29, 2026 04:55 PM', 3, '2026-01-26 08:55:20'),
(107, 28, 'Rejected', '', 3, '2026-01-26 08:55:55'),
(108, 28, 'Approved', '\n\nPayment Required: ₱90.00\nDeadline: Jan 29, 2026 04:56 PM', 3, '2026-01-26 08:56:12'),
(109, 27, 'Processing', '', 3, '2026-01-26 08:56:46'),
(110, 29, 'Processing', '', 3, '2026-01-26 09:41:40'),
(111, 29, 'Approved', '\n\nPayment Required: ₱50.00\nDeadline: Jan 29, 2026 08:13 PM', 3, '2026-01-26 12:13:44'),
(112, 29, 'Processing', '', 3, '2026-01-26 12:14:20'),
(113, 29, 'Pending', '', 3, '2026-01-26 12:20:41'),
(114, 29, 'Approved', '\n\nPayment Required: â‚±50.00\nDeadline: Jan 29, 2026 08:21 PM', 3, '2026-01-26 12:21:27'),
(115, 29, 'Rejected', '', 3, '2026-01-26 12:23:32'),
(116, 29, 'Approved', '\n\nPayment Required: â‚±50.00\nDeadline: Jan 29, 2026 08:24 PM', 3, '2026-01-26 12:24:18'),
(117, 27, 'Approved', '', 3, '2026-01-26 12:30:20'),
(118, 26, 'Approved', '\n\nPayment Required: â‚±90.00\nDeadline: Jan 29, 2026 08:30 PM', 3, '2026-01-26 12:30:44'),
(119, 26, 'Processing', '', 3, '2026-01-26 12:34:28'),
(120, 26, 'Approved', '\n\nPayment Required: â‚±90.00\nDeadline: Jan 29, 2026 08:34 PM', 3, '2026-01-26 12:34:45'),
(121, 27, 'Pending', '', 3, '2026-01-26 12:36:24'),
(122, 27, 'Processing', '', 3, '2026-01-26 12:36:41'),
(123, 26, 'Processing', '', 3, '2026-01-26 12:36:55'),
(124, 27, 'Approved', '', 3, '2026-01-26 12:37:12'),
(125, 23, 'Approved', '\n\nPayment Required: â‚±50.00\nDeadline: Jan 29, 2026 08:39 PM', 3, '2026-01-26 12:39:19'),
(126, 23, 'Pending', '', 3, '2026-01-26 12:39:54'),
(127, 23, 'Processing', '', 3, '2026-01-26 12:40:00'),
(128, 23, 'Pending', '', 3, '2026-01-26 12:40:15'),
(129, 23, 'Approved', '\n\nPayment Required: â‚±50.00\nDeadline: Jan 29, 2026 08:44 PM', 3, '2026-01-26 12:44:34'),
(130, 23, 'Pending', '', 3, '2026-01-26 12:44:43'),
(131, 23, 'Processing', '', 3, '2026-01-26 12:45:01'),
(132, 25, 'Processing', '', 3, '2026-01-26 12:46:32'),
(133, 25, 'Approved', '\n\nPayment Required: â‚±50.00\nDeadline: Jan 29, 2026 08:46 PM', 3, '2026-01-26 12:46:52'),
(134, 25, 'Rejected', '', 3, '2026-01-26 12:47:25'),
(135, 27, 'Pending', '', 3, '2026-01-26 12:50:20'),
(136, 27, 'Processing', '', 3, '2026-01-26 12:50:30'),
(137, 27, 'Approved', '', 3, '2026-01-26 12:50:38'),
(138, 27, 'Rejected', '', 3, '2026-01-26 12:50:57'),
(139, 27, 'Completed', '', 3, '2026-01-26 12:51:06'),
(140, 29, 'Approved', 'f\n\nPayment Required: â‚±50.00\nDeadline: Jan 29, 2026 08:51 PM', 3, '2026-01-26 12:51:39'),
(141, 30, 'Approved', '\n\nPayment Required: â‚±50.00\nDeadline: Jan 29, 2026 09:22 PM', 3, '2026-01-26 13:22:16'),
(142, 30, 'Processing', '', 3, '2026-01-26 14:42:23'),
(143, 30, 'Approved', '\n\nPayment Required: â‚±50.00\nDeadline: Jan 30, 2026 07:11 AM', 3, '2026-01-26 23:11:51'),
(144, 30, 'Paid', 'Status automatically changed to Paid after user submitted payment proof', 2, '2026-01-27 03:15:28'),
(145, 31, 'Processing', '', 3, '2026-01-27 03:30:24'),
(146, 30, 'Rejected', '', 3, '2026-01-27 04:00:05'),
(147, 29, 'Rejected', 'Wrong files boss', 3, '2026-01-27 04:16:21'),
(148, 30, 'Completed', 'Hehe joke lang completed na', 3, '2026-01-27 04:57:29'),
(149, 26, 'cancelled', 'Cancelled by user', 2, '2026-01-27 04:59:58'),
(150, 16, 'cancelled', 'Cancelled by user', 2, '2026-01-27 05:00:36'),
(151, 24, 'cancelled', 'Cancelled by user. Reason: gege', 2, '2026-01-27 05:04:10'),
(152, 23, 'cancelled', 'Cancelled by user. Reason: ndAKFDA', 2, '2026-01-27 05:07:14'),
(153, 32, 'Rejected', '', 3, '2026-01-29 05:52:16'),
(154, 15, 'cancelled', 'Cancelled by user. Reason: ge', 2, '2026-01-29 06:21:46'),
(155, 28, 'Paid', 'Status automatically changed to Paid after user submitted payment proof', 2, '2026-01-29 06:36:56'),
(156, 33, 'cancelled', 'Cancelled by user. Reason: g', 2, '2026-01-29 06:50:28'),
(157, 34, 'Approved', '\n\nPayment Required: â‚±50.00\nDeadline: Feb 01, 2026 03:11 PM', 3, '2026-01-29 07:11:38'),
(158, 34, 'Paid', 'Status automatically changed to Paid after user submitted payment proof', 2, '2026-01-29 07:12:04'),
(159, 43, 'Processing', '', 3, '2026-01-29 07:38:38'),
(160, 57, 'Processing', '', 3, '2026-01-29 10:00:56'),
(161, 57, 'Approved', '\n\nPayment Required: â‚±50.00\nDeadline: Feb 01, 2026 06:01 PM', 3, '2026-01-29 10:01:53'),
(162, 57, 'Paid', 'Status automatically changed to Paid after user submitted payment proof', 2, '2026-01-29 10:02:18'),
(163, 57, 'Paid', 'Payment verified by admin - Ready for document claiming', 3, '2026-01-29 10:03:02'),
(164, 34, 'Approved', 'Payment rejected - Status reverted to Approved. Reason: ampangit', 3, '2026-01-29 10:06:36'),
(165, 43, 'cancelled', 'Cancelled by user. Reason: rt12', 2, '2026-01-29 10:15:40'),
(166, 28, 'Paid', 'Payment verified by admin - Ready for document claiming', 3, '2026-01-29 10:40:39'),
(167, 59, 'Approved', '\n\nPayment Required: â‚±50.00\nDeadline: Feb 01, 2026 06:54 PM', 3, '2026-01-29 10:54:59'),
(168, 59, 'Paid', 'Status automatically changed to Paid after user submitted payment proof', 2, '2026-01-29 10:55:23'),
(169, 58, 'cancelled', 'Cancelled by user. Reason: fasfa', 2, '2026-01-29 10:56:03'),
(170, 34, 'Paid', 'Status automatically changed to Paid after user submitted payment proof', 2, '2026-01-29 10:57:20'),
(171, 34, 'Approved', 'Payment rejected - Status reverted to Approved. Reason: f12', 3, '2026-01-29 11:44:31'),
(172, 59, 'Approved', 'Payment rejected - Status reverted to Approved. Reason: Gusto ko lang ireject', 3, '2026-01-29 11:48:49'),
(173, 59, 'Paid', 'Status automatically changed to Paid after user submitted payment proof', 2, '2026-01-29 11:50:30'),
(174, 59, 'Approved', 'Payment rejected - Status reverted to Approved. Reason: Pangit', 3, '2026-01-29 11:52:13'),
(175, 59, 'Paid', 'Status automatically changed to Paid after user submitted payment proof', 2, '2026-01-29 11:53:33'),
(176, 59, 'Approved', 'Payment rejected - Status reverted to Approved. Reason: r123', 3, '2026-01-29 11:53:52'),
(177, 59, 'Paid', 'Status automatically changed to Paid after user submitted payment proof', 2, '2026-01-29 11:57:21'),
(178, 59, 'Approved', 'Payment rejected - Status reverted to Approved. Reason: ge', 3, '2026-01-29 11:57:38'),
(179, 54, 'Rejected', '', 25, '2026-01-29 17:06:52'),
(180, 54, 'Approved', '', 25, '2026-01-29 17:08:08'),
(181, 54, 'Rejected', '', 25, '2026-01-29 17:08:23'),
(182, 51, 'Approved', '', 25, '2026-01-29 17:12:24'),
(183, 54, 'Processing', '', 25, '2026-01-29 17:13:04'),
(184, 54, 'Approved', '', 25, '2026-01-29 17:19:03'),
(185, 54, 'Processing', '', 25, '2026-01-29 17:19:21'),
(186, 54, 'Rejected', 'Reject ka sakin boy', 25, '2026-01-29 17:20:24'),
(187, 54, 'Approved', '', 25, '2026-01-29 17:20:47'),
(188, 54, 'Rejected', 'Hoy', 25, '2026-01-29 17:21:01'),
(189, 54, 'Approved', '', 25, '2026-01-29 17:23:27'),
(190, 54, 'Processing', '', 25, '2026-01-29 17:29:46'),
(191, 54, 'Pending', 'hehe', 25, '2026-01-29 17:29:58'),
(192, 54, 'Approved', '', 25, '2026-01-29 17:37:29'),
(193, 54, 'Rejected', 'Bengboy', 25, '2026-01-29 17:37:56'),
(194, 56, 'Approved', '\n\nPayment Required: â‚±50.00\nDeadline: Feb 02, 2026 01:50 AM', 3, '2026-01-29 17:50:50'),
(195, 56, 'Paid', 'Status automatically changed to Paid after user submitted payment proof', 2, '2026-01-29 17:52:36'),
(196, 56, 'Paid', 'Payment verified by admin - Ready for document claiming', 3, '2026-01-29 17:52:59'),
(197, 54, 'Completed', '', 25, '2026-01-29 18:38:42');

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
(66, 'Local Economic Development and Investment Promotions Office', 'LEDIPO', 'DTI business registration, BMBE registration, and business certifications', NULL, '(046) 430-0042', 'business@carmona.gov.ph', NULL, 1, '2026-01-22 05:12:16', '2026-01-22 05:17:55'),
(82, 'f', 'f', 'f', NULL, NULL, NULL, NULL, 0, '2026-01-26 10:58:52', '2026-01-29 05:37:23');

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
(21, 21, 'Report_2026-01-01_to_2026-01-31.pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31_20260126_065020.pdf', 91825, '2026-01-25 22:50:20'),
(22, 22, 'Report_2026-01-23_to_2026-01-24 (1).pdf', 'assets/uploads/Report_2026-01-23_to_2026-01-24__1__20260126_073411.pdf', 66324, '2026-01-25 23:34:11'),
(23, 23, 'Report_2026-01-23_to_2026-01-24.pdf', 'assets/uploads/Report_2026-01-23_to_2026-01-24_20260126_094802.pdf', 66324, '2026-01-26 01:48:02'),
(24, 24, 'Report_2026-01-23_to_2026-01-24 (1).pdf', 'assets/uploads/Report_2026-01-23_to_2026-01-24__1__20260126_100313.pdf', 66324, '2026-01-26 02:03:13'),
(25, 25, 'Report_2026-01-23_to_2026-01-24.pdf', 'assets/uploads/Report_2026-01-23_to_2026-01-24_20260126_134027.pdf', 66324, '2026-01-26 05:40:27'),
(26, 26, 'Ababao, Keith Justine S. - FINALS ONLINE ACTIVITY.pdf', 'assets/uploads/Ababao__Keith_Justine_S__-_FINALS_ONLINE_ACTIVITY_20260126_134135.pdf', 601532, '2026-01-26 05:41:35'),
(27, 27, '2-FOR-Capstone-Project-Concept-Paper-Template-updated-letterhead.pdf', 'assets/uploads/2-FOR-Capstone-Project-Concept-Paper-Template-updated-letterhead_20260126_134220.pdf', 575054, '2026-01-26 05:42:20'),
(28, 28, 'LIST OF PAID.pdf', 'assets/uploads/LIST_OF_PAID_20260126_134528.pdf', 116524, '2026-01-26 05:45:28'),
(29, 29, 'carmonaopp_db.pdf', 'assets/uploads/carmonaopp_db_20260126_172456.pdf', 727858, '2026-01-26 09:24:56'),
(30, 30, 'Admin Header Debug Tool.pdf', 'assets/uploads/Admin_Header_Debug_Tool_20260126_212154.pdf', 926597, '2026-01-26 13:21:54'),
(31, 31, 'carmonaopp_db.pdf', 'assets/uploads/carmonaopp_db_20260127_083221.pdf', 727858, '2026-01-27 00:32:21'),
(32, 32, 'RUBRICS-WEB-DEVELOPMENT-PROJECT-PRESENTATION.pdf', 'assets/uploads/RUBRICS-WEB-DEVELOPMENT-PROJECT-PRESENTATION_20260127_132938.pdf', 178618, '2026-01-27 05:29:38'),
(33, 33, 'Report_2026-01-01_to_2026-01-31 (2).pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_143744.pdf', 114314, '2026-01-29 06:37:44'),
(34, 34, 'Report_2026-01-01_to_2026-01-31 (2).pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_150800.pdf', 114314, '2026-01-29 07:08:00'),
(35, 35, 'Rank 2.pdf', 'assets/uploads/Rank_2_20260129_151244.pdf', 556495, '2026-01-29 07:12:44'),
(36, 36, 'Report_2026-01-01_to_2026-01-31 (2).pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_151600.pdf', 114314, '2026-01-29 07:16:00'),
(37, 37, 'Report_2026-01-01_to_2026-01-31 (2).pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_151656.pdf', 114314, '2026-01-29 07:16:56'),
(38, 38, 'Report_2026-01-01_to_2026-01-31 (2).pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_151728.pdf', 114314, '2026-01-29 07:17:28'),
(39, 39, 'Report_2026-01-01_to_2026-01-31 (2).pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_153028.pdf', 114314, '2026-01-29 07:30:28'),
(40, 40, 'Report_2026-01-01_to_2026-01-31 (1).pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31__1__20260129_153232.pdf', 104319, '2026-01-29 07:32:32'),
(41, 41, 'Report_2026-01-01_to_2026-01-31 (2).pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_153345.pdf', 114314, '2026-01-29 07:33:45'),
(42, 42, 'Report_2026-01-01_to_2026-01-31 (2).pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_153407.pdf', 114314, '2026-01-29 07:34:07'),
(43, 43, 'Report_2026-01-01_to_2026-01-31 (2).pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_153826.pdf', 114314, '2026-01-29 07:38:26'),
(44, 44, 'View Application - Carmona Online Permit Portal.pdf', 'assets/uploads/View_Application_-_Carmona_Online_Permit_Portal_20260129_154054.pdf', 619873, '2026-01-29 07:40:54'),
(45, 45, 'Report_2026-01-01_to_2026-01-31 (2).pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_154713.pdf', 114314, '2026-01-29 07:47:13'),
(46, 46, 'Report_2026-01-01_to_2026-01-31 (2).pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_154740.pdf', 114314, '2026-01-29 07:47:40'),
(47, 47, 'Report_2026-01-01_to_2026-01-31 (1).pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31__1__20260129_154811.pdf', 104319, '2026-01-29 07:48:11'),
(48, 48, 'Report_2026-01-01_to_2026-01-31 (2).pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_155000.pdf', 114314, '2026-01-29 07:50:00'),
(49, 49, 'Report_2026-01-01_to_2026-01-31 (2).pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_155519.pdf', 114314, '2026-01-29 07:55:19'),
(50, 50, 'Report_2026-01-01_to_2026-01-22.pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-22_20260129_170904.pdf', 78229, '2026-01-29 09:09:04'),
(51, 51, 'Report_2026-01-01_to_2026-01-31 (2).pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_173215.pdf', 114314, '2026-01-29 09:32:15'),
(52, 52, 'Report_2026-01-01_to_2026-01-31 (2).pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_173246.pdf', 114314, '2026-01-29 09:32:46'),
(53, 53, 'Report_2026-01-01_to_2026-01-31 (2).pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_173323.pdf', 114314, '2026-01-29 09:33:23'),
(54, 54, 'Report_2026-01-01_to_2026-01-31 (2).pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_173806.pdf', 114314, '2026-01-29 09:38:06'),
(55, 55, 'Report_2026-01-01_to_2026-01-31 (2).pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_174056.pdf', 114314, '2026-01-29 09:40:56'),
(56, 56, 'Report_2026-01-01_to_2026-01-31 (2).pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_174530.pdf', 114314, '2026-01-29 09:45:30'),
(57, 57, 'Report_2026-01-01_to_2026-01-31 (2).pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_174605.pdf', 114314, '2026-01-29 09:46:05'),
(58, 58, 'Report_2026-01-01_to_2026-01-31 (2).pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_184110.pdf', 114314, '2026-01-29 10:41:10'),
(59, 59, 'Report_2026-01-01_to_2026-01-31 (2).pdf', 'assets/uploads/Report_2026-01-01_to_2026-01-31__2__20260129_185413.pdf', 114314, '2026-01-29 10:54:13');

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `application_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `recipient` varchar(255) NOT NULL,
  `subject` varchar(500) NOT NULL,
  `status` enum('sent','failed','pending') DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_logs`
--

INSERT INTO `email_logs` (`id`, `user_id`, `application_id`, `department_id`, `recipient`, `subject`, `status`, `error_message`, `created_at`) VALUES
(1, NULL, NULL, NULL, 'keithjustine57@gmail.com', 'Application Requires Revision - CRMN-2026-949441', 'sent', NULL, '2026-01-29 17:06:58'),
(2, NULL, NULL, NULL, 'keithjustine57@gmail.com', 'Application Approved - CRMN-2026-949441', 'sent', NULL, '2026-01-29 17:08:12'),
(3, NULL, NULL, NULL, 'keithjustine57@gmail.com', 'Application Requires Revision - CRMN-2026-949441', 'sent', NULL, '2026-01-29 17:08:27'),
(4, NULL, NULL, NULL, 'keithjustine57@gmail.com', 'Application Approved - CRMN-2026-965351', 'sent', NULL, '2026-01-29 17:12:29'),
(5, NULL, NULL, NULL, 'keithjustine57@gmail.com', 'Application Now Being Processed - CRMN-2026-949441', 'sent', NULL, '2026-01-29 17:13:08'),
(6, NULL, NULL, NULL, 'keithjustine57@gmail.com', 'Application Approved - CRMN-2026-949441', 'sent', NULL, '2026-01-29 17:19:08'),
(7, NULL, NULL, NULL, 'keithjustine57@gmail.com', 'Application Now Being Processed - CRMN-2026-949441', 'sent', NULL, '2026-01-29 17:19:26'),
(8, NULL, NULL, NULL, 'keithjustine57@gmail.com', 'Application Requires Revision - CRMN-2026-949441', 'sent', NULL, '2026-01-29 17:20:29'),
(9, NULL, NULL, NULL, 'keithjustine57@gmail.com', 'Application Approved - CRMN-2026-949441', 'sent', NULL, '2026-01-29 17:20:51'),
(10, NULL, NULL, NULL, 'keithjustine57@gmail.com', 'Application Requires Revision - CRMN-2026-949441', 'sent', NULL, '2026-01-29 17:21:05'),
(11, NULL, NULL, NULL, 'keithjustine57@gmail.com', 'Application Approved - CRMN-2026-949441', 'sent', NULL, '2026-01-29 17:23:32'),
(12, NULL, NULL, NULL, 'keithjustine57@gmail.com', 'Application Now Being Processed - CRMN-2026-949441', 'sent', NULL, '2026-01-29 17:29:51'),
(13, NULL, NULL, NULL, 'keithjustine57@gmail.com', 'Application Status Update - CRMN-2026-949441', 'sent', NULL, '2026-01-29 17:30:03'),
(14, NULL, NULL, NULL, 'keithjustine57@gmail.com', 'Application Approved - CRMN-2026-949441', 'sent', NULL, '2026-01-29 17:37:33'),
(15, NULL, NULL, NULL, 'keithjustine57@gmail.com', 'Application Requires Revision - CRMN-2026-949441', 'sent', NULL, '2026-01-29 17:38:01'),
(16, NULL, NULL, NULL, 'keithjustine57@gmail.com', 'Application Approved - Payment Required [CRMN-2026-827607]', 'sent', NULL, '2026-01-29 17:50:54'),
(17, NULL, NULL, NULL, 'keithjustine57@gmail.com', '✅ Payment Submitted - Status: PAID [CRMN-2026-827607]', 'sent', NULL, '2026-01-29 17:52:41'),
(18, NULL, NULL, NULL, 'keithjustine57@gmail.com', '✅ Payment Verified - Ready for Claiming [CRMN-2026-827607]', 'sent', NULL, '2026-01-29 17:53:04'),
(19, NULL, NULL, NULL, 'keithjustine57@gmail.com', 'Application Completed - CRMN-2026-949441', 'sent', NULL, '2026-01-29 18:38:47');

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
(211, 2, 30, 'Payment Submitted - Status Changed to PAID', 'Your payment proof for application CRMN-2026-274777 has been submitted successfully! Your application status is now PAID and is under verification.', 'success', 1, '2026-01-27 03:15:28'),
(213, 2, 31, 'Application Status Updated', 'Your application (CRMN-2026-353167) status has been updated to: Processing', 'info', 1, '2026-01-27 03:30:24'),
(214, 2, 30, 'Application Status Updated', 'Your application (CRMN-2026-274777) status has been updated to: Rejected', 'danger', 1, '2026-01-27 04:00:05'),
(215, 2, 29, 'Application Status Updated', 'Your application (CRMN-2026-110566) status has been updated to: Rejected. Remarks: Wrong files boss', 'danger', 1, '2026-01-27 04:16:21'),
(216, 2, 30, 'Application Status Updated', 'Your application (CRMN-2026-274777) status has been updated to: Completed. Remarks: Hehe joke lang completed na', 'success', 1, '2026-01-27 04:57:29'),
(221, 2, 32, 'Application Submitted Successfully', 'Your application for Permit for Motorcade has been submitted. Tracking Number: CRMN-2026-213328', 'success', 1, '2026-01-27 05:29:38'),
(223, 2, 32, 'Application Status Updated', 'Your application (CRMN-2026-213328) status has been updated to: Rejected', 'danger', 1, '2026-01-29 05:52:16'),
(225, 2, 28, 'Payment Submitted - Status Changed to PAID', 'Your payment proof for application CRMN-2026-789543 has been submitted successfully! Your application status is now PAID and is under verification.', 'success', 1, '2026-01-29 06:36:56'),
(227, 2, 33, 'Application Submitted Successfully', 'Your application for Feedback Processing has been submitted. Tracking Number: CRMN-2026-972363', 'success', 1, '2026-01-29 06:37:44'),
(230, 2, 34, 'Application Submitted Successfully', 'Your application for Mayor\'s Endorsement has been submitted. Tracking Number: CRMN-2026-504254', 'success', 1, '2026-01-29 07:08:00'),
(232, 2, 34, 'Application Approved - Payment Required', 'Your application CRMN-2026-504254 has been approved! Please submit payment of â‚±50.00 within 3 days (by Feb 01, 2026).', 'success', 1, '2026-01-29 07:11:38'),
(233, 2, 34, 'Payment Submitted - Status Changed to PAID', 'Your payment proof for application CRMN-2026-504254 has been submitted successfully! Your application status is now PAID and is under verification.', 'success', 1, '2026-01-29 07:12:04'),
(235, 2, 35, 'Application Submitted Successfully', 'Your application for Tricycle Franchise Dropping has been submitted. Tracking Number: CRMN-2026-874566', 'success', 1, '2026-01-29 07:12:44'),
(237, 2, 36, 'Application Submitted Successfully', 'Your application for Mayor\'s Endorsement has been submitted. Tracking Number: CRMN-2026-900569', 'success', 1, '2026-01-29 07:16:00'),
(239, 2, 37, 'Application Submitted Successfully', 'Your application for Request Data for Thesis/Research has been submitted. Tracking Number: CRMN-2026-446422', 'success', 1, '2026-01-29 07:16:56'),
(241, 2, 38, 'Application Submitted Successfully', 'Your application for Tricycle Franchise Dropping has been submitted. Tracking Number: CRMN-2026-917310', 'success', 1, '2026-01-29 07:17:28'),
(243, 2, 39, 'Application Submitted Successfully', 'Your application for Real Property Tax Payment has been submitted. Tracking Number: CRMN-2026-088536', 'success', 1, '2026-01-29 07:30:28'),
(245, 2, 40, 'Application Submitted Successfully', 'Your application for Mayor\'s Endorsement has been submitted. Tracking Number: CRMN-2026-500954', 'success', 1, '2026-01-29 07:32:32'),
(247, 2, 41, 'Application Submitted Successfully', 'Your application for Issuance of Certification has been submitted. Tracking Number: CRMN-2026-920458', 'success', 1, '2026-01-29 07:33:45'),
(249, 2, 42, 'Application Submitted Successfully', 'Your application for Tricycle Franchise Dropping has been submitted. Tracking Number: CRMN-2026-398123', 'success', 1, '2026-01-29 07:34:07'),
(251, 2, 43, 'Application Submitted Successfully', 'Your application for Feedback Processing has been submitted. Tracking Number: CRMN-2026-715210', 'success', 1, '2026-01-29 07:38:26'),
(253, 2, 43, 'Application Status Updated', 'Your application (CRMN-2026-715210) status has been updated to: Processing', 'info', 1, '2026-01-29 07:38:38'),
(254, 2, 44, 'Application Submitted Successfully', 'Your application for Feedback Processing has been submitted. Tracking Number: CRMN-2026-179494', 'success', 1, '2026-01-29 07:40:54'),
(256, 2, 45, 'Application Submitted Successfully', 'Your application for Mayor\'s Endorsement has been submitted. Tracking Number: CRMN-2026-239525', 'success', 1, '2026-01-29 07:47:13'),
(258, 2, 46, 'Application Submitted Successfully', 'Your application for Issuance of Certification has been submitted. Tracking Number: CRMN-2026-226241', 'success', 1, '2026-01-29 07:47:40'),
(260, 2, 47, 'Application Submitted Successfully', 'Your application for Mayor\'s Endorsement has been submitted. Tracking Number: CRMN-2026-842957', 'success', 1, '2026-01-29 07:48:11'),
(262, 2, 48, 'Application Submitted Successfully', 'Your application for Certification of No Major Source of Income/No Business has been submitted. Tracking Number: CRMN-2026-122976', 'success', 1, '2026-01-29 07:50:00'),
(264, 2, 49, 'Application Submitted Successfully', 'Your application for Issuance of Certification has been submitted. Tracking Number: CRMN-2026-104736', 'success', 1, '2026-01-29 07:55:19'),
(266, 2, 50, 'Application Submitted Successfully', 'Your application for Permit for Motorcade has been submitted. Tracking Number: CRMN-2026-229838', 'success', 1, '2026-01-29 09:09:04'),
(268, 2, 51, 'Application Submitted Successfully', 'Your application for Feedback Processing has been submitted. Tracking Number: CRMN-2026-965351', 'success', 0, '2026-01-29 09:32:15'),
(270, 2, 52, 'Application Submitted Successfully', 'Your application for Sorteo ng Carmona Registration has been submitted. Tracking Number: CRMN-2026-064082', 'success', 0, '2026-01-29 09:32:46'),
(272, 2, 53, 'Application Submitted Successfully', 'Your application for Tricycle Franchise Dropping has been submitted. Tracking Number: CRMN-2026-915558', 'success', 0, '2026-01-29 09:33:23'),
(274, 2, 54, 'Application Submitted Successfully', 'Your application for Feedback Processing has been submitted. Tracking Number: CRMN-2026-949441', 'success', 0, '2026-01-29 09:38:06'),
(276, 2, 55, 'Application Submitted Successfully', 'Your application for Tricycle Franchise Dropping has been submitted. Tracking Number: CRMN-2026-088553', 'success', 0, '2026-01-29 09:40:56'),
(278, 2, 56, 'Application Submitted Successfully', 'Your application for Tricycle Franchise Dropping has been submitted. Tracking Number: CRMN-2026-827607', 'success', 0, '2026-01-29 09:45:30'),
(280, 2, 57, 'Application Submitted Successfully', 'Your application for Tricycle Franchise Dropping has been submitted. Tracking Number: CRMN-2026-047439', 'success', 0, '2026-01-29 09:46:05'),
(282, 2, 57, 'Application Status Updated', 'Your application (CRMN-2026-047439) status has been updated to: Processing', 'processing', 0, '2026-01-29 10:00:56'),
(283, 2, 57, 'Application Approved - Payment Required', 'Your application CRMN-2026-047439 has been approved! Please submit payment of â‚±50.00 within 3 days (by Feb 01, 2026).', 'success', 1, '2026-01-29 10:01:53'),
(284, 2, 57, 'Payment Submitted - Status Changed to PAID', 'Your payment proof for application CRMN-2026-047439 has been submitted successfully! Your application status is now PAID and is under verification.', 'payment_submitted', 0, '2026-01-29 10:02:18'),
(286, 2, 57, '✅ Payment Verified - Ready for Claiming', 'Great news! Your payment for application CRMN-2026-047439 has been verified! You may now claim your permit/document at our office.', 'payment_verified', 0, '2026-01-29 10:03:02'),
(287, 2, 34, '❌ Payment Rejected - Status Reverted to APPROVED', 'Your payment proof for application CRMN-2026-504254 has been rejected. Status changed back to APPROVED. Reason: ampangit. Please submit a new payment proof.', 'payment_rejected', 1, '2026-01-29 10:06:36'),
(289, 2, 28, '✅ Payment Verified - Ready for Claiming', 'Great news! Your payment for application CRMN-2026-789543 has been verified! You may now claim your permit/document at our office.', 'payment_verified', 1, '2026-01-29 10:40:39'),
(290, 2, 58, '<i class=\"fas fa-check-circle\"></i> Application Submitted Successfully', '<i class=\'fas fa-paper-plane\'></i> Your application for Feedback Processing has been submitted. Tracking Number: CRMN-2026-421344', 'success', 1, '2026-01-29 10:41:10'),
(292, 2, 59, 'Application Submitted Successfully', 'Your application for Mayor\'s Endorsement has been submitted. Tracking Number: CRMN-2026-691968', 'success', 0, '2026-01-29 10:54:13'),
(294, 2, 59, 'Application Approved - Payment Required', 'Your application CRMN-2026-691968 has been approved! Please submit payment of â‚±50.00 within 3 days (by Feb 01, 2026).', 'success', 1, '2026-01-29 10:54:59'),
(295, 2, 59, 'Payment Submitted - Status Changed to PAID', 'Your payment proof for application CRMN-2026-691968 has been submitted successfully! Your application status is now PAID and is under verification.', 'payment_submitted', 0, '2026-01-29 10:55:23'),
(298, 2, 34, 'Payment Submitted - Status Changed to PAID', 'Your payment proof for application CRMN-2026-504254 has been submitted successfully! Your application status is now PAID and is under verification.', 'payment_submitted', 0, '2026-01-29 10:57:20'),
(300, 2, 34, 'Payment Rejected - Status Reverted to APPROVED', 'Your payment proof for application CRMN-2026-504254 has been rejected. Status changed back to APPROVED. Reason: f12. Please submit a new payment proof.', 'payment_rejected', 0, '2026-01-29 11:44:31'),
(301, 2, 59, 'Payment Rejected - Status Reverted to APPROVED', 'Your payment proof for application CRMN-2026-691968 has been rejected. Status changed back to APPROVED. Reason: Gusto ko lang ireject. Please submit a new payment proof.', 'payment_rejected', 0, '2026-01-29 11:48:49'),
(302, 2, 59, 'Payment Submitted - Status Changed to PAID', 'Your payment proof for application CRMN-2026-691968 has been submitted successfully! Your application status is now PAID and is under verification.', 'payment_submitted', 0, '2026-01-29 11:50:30'),
(303, 3, 59, 'New Payment - Status: PAID', 'Payment submitted for application CRMN-2026-691968. Status changed to PAID. Please verify payment proof.', 'payment_submitted', 1, '2026-01-29 11:50:30'),
(304, 2, 59, 'Payment Rejected - Status Reverted to APPROVED', 'Your payment proof for application CRMN-2026-691968 has been rejected. Status changed back to APPROVED. Reason: Pangit. Please submit a new payment proof.', 'payment_rejected', 0, '2026-01-29 11:52:13'),
(305, 2, 59, 'Payment Submitted - Status Changed to PAID', 'Your payment proof for application CRMN-2026-691968 has been submitted successfully! Your application status is now PAID and is under verification.', 'payment_submitted', 0, '2026-01-29 11:53:33'),
(306, 3, 59, 'New Payment - Status: PAID', 'Payment submitted for application CRMN-2026-691968. Status changed to PAID. Please verify payment proof.', 'payment_submitted', 1, '2026-01-29 11:53:33'),
(307, 2, 59, 'Payment Rejected - Status Reverted to APPROVED', 'Your payment proof for application CRMN-2026-691968 has been rejected. Status changed back to APPROVED. Reason: r123. Please submit a new payment proof.', 'payment_rejected', 0, '2026-01-29 11:53:52'),
(308, 2, 59, 'Payment Submitted - Status Changed to PAID', 'Your payment proof for application CRMN-2026-691968 has been submitted successfully! Your application status is now PAID and is under verification.', 'payment_submitted', 0, '2026-01-29 11:57:21'),
(309, 3, 59, 'New Payment - Status: PAID', 'Payment submitted for application CRMN-2026-691968. Status changed to PAID. Please verify payment proof.', 'payment_submitted', 1, '2026-01-29 11:57:21'),
(310, 2, 59, 'Payment Rejected - Status Reverted to APPROVED', 'Your payment proof for application CRMN-2026-691968 has been rejected. Status changed back to APPROVED. Reason: ge. Please submit a new payment proof.', 'payment_rejected', 0, '2026-01-29 11:57:38'),
(311, 2, 54, 'Application Status Updated', 'Your application (CRMN-2026-949441) status has been updated to: Rejected', 'rejected', 0, '2026-01-29 17:06:52'),
(312, 2, 54, 'Application Status Updated', 'Your application (CRMN-2026-949441) status has been updated to: Approved', 'approved', 0, '2026-01-29 17:08:08'),
(313, 2, 54, 'Application Status Updated', 'Your application (CRMN-2026-949441) status has been updated to: Rejected', 'rejected', 0, '2026-01-29 17:08:23'),
(314, 2, 51, 'Application Status Updated', 'Your application (CRMN-2026-965351) status has been updated to: Approved', 'approved', 0, '2026-01-29 17:12:24'),
(315, 2, 54, 'Application Status Updated', 'Your application (CRMN-2026-949441) status has been updated to: Processing', 'processing', 0, '2026-01-29 17:13:04'),
(316, 2, 54, 'Application Status Updated', 'Your application (CRMN-2026-949441) status has been updated to: Approved', 'approved', 0, '2026-01-29 17:19:03'),
(317, 2, 54, 'Application Status Updated', 'Your application (CRMN-2026-949441) status has been updated to: Processing', 'processing', 0, '2026-01-29 17:19:21'),
(318, 2, 54, 'Application Status Updated', 'Your application (CRMN-2026-949441) status has been updated to: Rejected. Remarks: Reject ka sakin boy', 'rejected', 0, '2026-01-29 17:20:24'),
(319, 2, 54, 'Application Status Updated', 'Your application (CRMN-2026-949441) status has been updated to: Approved', 'approved', 0, '2026-01-29 17:20:47'),
(320, 2, 54, 'Application Status Updated', 'Your application (CRMN-2026-949441) status has been updated to: Rejected. Remarks: Hoy', 'rejected', 0, '2026-01-29 17:21:01'),
(321, 2, 54, 'Application Status Updated', 'Your application (CRMN-2026-949441) status has been updated to: Approved', 'approved', 1, '2026-01-29 17:23:27'),
(322, 2, 54, 'Application Status Updated', 'Your application (CRMN-2026-949441) status has been updated to: Processing', 'processing', 0, '2026-01-29 17:29:46'),
(323, 2, 54, 'Application Status Updated', 'Your application (CRMN-2026-949441) status has been updated to: Pending. Remarks: hehe', 'status_update', 1, '2026-01-29 17:29:58'),
(324, 2, 54, 'Application Status Updated', 'Your application (CRMN-2026-949441) status has been updated to: Approved', 'approved', 0, '2026-01-29 17:37:29'),
(325, 2, 54, 'Application Status Updated', 'Your application (CRMN-2026-949441) status has been updated to: Rejected. Remarks: Bengboy', 'rejected', 0, '2026-01-29 17:37:56'),
(326, 2, 56, 'Application Approved - Payment Required', 'Your application CRMN-2026-827607 has been approved! Please submit payment of â‚±50.00 within 3 days (by Feb 02, 2026).', 'success', 1, '2026-01-29 17:50:50'),
(327, 2, 56, 'Payment Submitted - Status Changed to PAID', 'Your payment proof for application CRMN-2026-827607 has been submitted successfully! Your application status is now PAID and is under verification.', 'payment_submitted', 0, '2026-01-29 17:52:36'),
(328, 2, 56, '✅ Payment Verified - Ready for Claiming', 'Great news! Your payment for application CRMN-2026-827607 has been verified! You may now claim your permit/document at our office.', 'payment_verified', 0, '2026-01-29 17:52:59'),
(329, 2, 54, 'Application Status Updated', 'Your application (CRMN-2026-949441) status has been updated to: Completed', 'completed', 0, '2026-01-29 18:38:42');

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

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token`, `used`, `expires_at`, `created_at`) VALUES
(3, 2, 'ec4710c9eca292d51d23def7c67d18ed8e26fdaa32e75a5b1c666abdb85d5d1e', 0, '2026-01-26 12:40:04', '2026-01-26 11:40:04');

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
(45, 21, 'payment_rejected', NULL, NULL, NULL, 'g', 3, '2026-01-25 22:51:42'),
(46, 21, 'payment_required', 90.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-25 23:18:55'),
(47, 21, 'payment_submitted', 90.00, 'GCash', 'f2f', 'fw', 2, '2026-01-25 23:19:12'),
(48, 21, 'payment_rejected', NULL, NULL, NULL, 'g', 3, '2026-01-25 23:19:59'),
(49, 21, 'payment_submitted', 90.00, 'GCash', '1412412', '4124124214214', 2, '2026-01-25 23:29:28'),
(50, 21, 'payment_verified', 90.00, NULL, NULL, 'Payment verified by admin', 3, '2026-01-25 23:29:41'),
(51, 22, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-25 23:34:24'),
(52, 22, 'payment_submitted', 50.00, 'GCash', 'r', '', 2, '2026-01-25 23:34:49'),
(53, 22, 'payment_rejected', NULL, NULL, NULL, 'f', 3, '2026-01-25 23:35:00'),
(54, 22, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-25 23:58:15'),
(55, 22, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-26 00:01:05'),
(56, 22, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-26 00:09:33'),
(57, 22, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-26 00:27:11'),
(58, 22, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-26 00:29:39'),
(59, 22, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-26 00:30:24'),
(60, 22, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-26 00:30:55'),
(61, 22, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-26 00:31:21'),
(62, 22, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-26 04:18:44'),
(63, 23, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-26 04:19:30'),
(64, 23, 'payment_submitted', 50.00, 'GCash', 'gasfga', '', 2, '2026-01-26 04:20:34'),
(65, 23, 'payment_rejected', NULL, NULL, NULL, 'her', 3, '2026-01-26 04:21:22'),
(66, 23, 'payment_submitted', 50.00, 'GCash', 'fgasfa', '', 2, '2026-01-26 04:22:14'),
(67, 23, 'payment_verified', 50.00, NULL, NULL, 'Payment verified by admin', 3, '2026-01-26 04:22:29'),
(68, 28, 'payment_required', 90.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-26 08:55:20'),
(69, 28, 'payment_required', 90.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-26 08:56:12'),
(70, 29, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-26 12:13:44'),
(71, 29, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-26 12:21:27'),
(72, 29, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-26 12:24:18'),
(73, 26, 'payment_required', 90.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-26 12:30:44'),
(74, 26, 'payment_required', 90.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-26 12:34:45'),
(75, 23, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-26 12:39:19'),
(76, 23, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-26 12:44:34'),
(77, 25, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-26 12:46:52'),
(78, 29, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-26 12:51:39'),
(79, 30, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Jan 29, 2026', 3, '2026-01-26 13:22:16'),
(80, 30, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Jan 30, 2026', 3, '2026-01-26 23:11:51'),
(81, 30, 'payment_submitted', 50.00, 'GCash', 'fsa241412', '', 2, '2026-01-27 03:15:28'),
(82, 28, 'payment_submitted', 90.00, 'GCash', 'fasf21424', '', 2, '2026-01-29 06:36:56'),
(83, 34, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Feb 01, 2026', 3, '2026-01-29 07:11:38'),
(84, 34, 'payment_submitted', 50.00, 'GCash', '51324512', '', 2, '2026-01-29 07:12:04'),
(85, 57, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Feb 01, 2026', 3, '2026-01-29 10:01:53'),
(86, 57, 'payment_submitted', 50.00, 'GCash', '215124', '', 2, '2026-01-29 10:02:18'),
(87, 57, 'payment_verified', 50.00, NULL, NULL, 'Payment verified by admin', 3, '2026-01-29 10:03:02'),
(88, 34, 'payment_rejected', NULL, NULL, NULL, 'ampangit', 3, '2026-01-29 10:06:36'),
(89, 28, 'payment_verified', 90.00, NULL, NULL, 'Payment verified by admin', 3, '2026-01-29 10:40:39'),
(90, 59, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Feb 01, 2026', 3, '2026-01-29 10:54:59'),
(91, 59, 'payment_submitted', 50.00, 'GCash', '412432141', '41221', 2, '2026-01-29 10:55:23'),
(92, 34, 'payment_submitted', 50.00, 'GCash', '412412', '', 2, '2026-01-29 10:57:20'),
(93, 34, 'payment_rejected', NULL, NULL, NULL, 'f12', 3, '2026-01-29 11:44:31'),
(94, 59, 'payment_rejected', NULL, NULL, NULL, 'Gusto ko lang ireject', 3, '2026-01-29 11:48:49'),
(95, 59, 'payment_submitted', 50.00, 'GCash', '421321', '', 2, '2026-01-29 11:50:30'),
(96, 59, 'payment_rejected', NULL, NULL, NULL, 'Pangit', 3, '2026-01-29 11:52:13'),
(97, 59, 'payment_submitted', 50.00, 'GCash', '421312', '21', 2, '2026-01-29 11:53:33'),
(98, 59, 'payment_rejected', NULL, NULL, NULL, 'r123', 3, '2026-01-29 11:53:52'),
(99, 59, 'payment_submitted', 50.00, 'GCash', '51312', '52151', 2, '2026-01-29 11:57:21'),
(100, 59, 'payment_rejected', NULL, NULL, NULL, 'ge', 3, '2026-01-29 11:57:38'),
(101, 56, 'payment_required', 50.00, NULL, NULL, 'Payment deadline set to Feb 02, 2026', 3, '2026-01-29 17:50:50'),
(102, 56, 'payment_submitted', 50.00, 'GCash', 'ee1312312', '', 2, '2026-01-29 17:52:36'),
(103, 56, 'payment_verified', 50.00, NULL, NULL, 'Payment verified by admin', 3, '2026-01-29 17:52:59');

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
(99, 66, 'Barangay Micro-Business Enterprise (BMBE) Registration', 'LEDIPO-003', 'Registration for BMBE tax exemption and benefits', 'Duly accomplished BMBE Application Form 01 signed by owner\r\nDuly signed Consent Form\r\nOriginal Certificate of Business Name Registration\r\nAuthorization Letter of Owner (if representative)\r\nRepresentative&#039;s Valid ID (if representative)', 5, 1.00, 1, '2026-01-22 05:12:16', '2026-01-29 05:37:50'),
(100, 66, 'Request for Business Name Certification', 'LEDIPO-004', 'Certification of business name availability or registration', 'Duly accomplished Other BN Related Application Form signed by owner\nValid ID of Requesting Individual', 3, 80.00, 1, '2026-01-22 05:12:16', '2026-01-22 05:12:16'),
(101, 66, 'Endorsement Letter for Manila Southwoods RFID Tag/Car Sticker', 'LEDIPO-005', 'City endorsement for Manila Southwoods subdivision access', 'Application form\nCopy of Official Receipt and Certificate of Registration (OR/CR) of vehicle\nValid ID of Requesting Individual', 3, 0.00, 1, '2026-01-22 05:12:16', '2026-01-29 05:37:51'),
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
  `user_id` int(11) DEFAULT NULL,
  `application_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
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

INSERT INTO `sms_logs` (`id`, `user_id`, `application_id`, `department_id`, `phone_number`, `message`, `status`, `error_message`, `sent_at`, `created_at`) VALUES
(1, NULL, NULL, NULL, '09690805903', 'Carmona Online Permit Portal: Your application CRMN-2026-949441 requires revision. Check your email for details.', 'failed', 'Invalid API key', NULL, '2026-01-29 17:06:58'),
(2, NULL, NULL, NULL, '09690805903', 'Carmona Online Permit Portal: Good news! Your application CRMN-2026-949441 has been APPROVED.', 'failed', 'Invalid API key', NULL, '2026-01-29 17:08:12'),
(3, NULL, NULL, NULL, '09690805903', 'Carmona Online Permit Portal: Your application CRMN-2026-949441 requires revision. Check your email for details.', 'failed', 'Invalid API key', NULL, '2026-01-29 17:08:27'),
(4, NULL, NULL, NULL, '09690805903', 'Carmona Online Permit Portal: Good news! Your application CRMN-2026-965351 has been APPROVED.', 'failed', 'Invalid API key', NULL, '2026-01-29 17:12:29'),
(5, NULL, NULL, NULL, '09690805903', 'Carmona Online Permit Portal: Your application CRMN-2026-949441 is now being processed. We\'ll notify you of updates.', 'failed', 'Invalid API key', NULL, '2026-01-29 17:13:08'),
(6, NULL, NULL, NULL, '09690805903', 'Carmona Online Permit Portal: Good news! Your application CRMN-2026-949441 has been APPROVED.', 'failed', 'Invalid API key', NULL, '2026-01-29 17:19:08'),
(7, NULL, NULL, NULL, '09690805903', 'Carmona Online Permit Portal: Your application CRMN-2026-949441 is now being processed. We\'ll notify you of updates.', 'failed', 'Invalid API key', NULL, '2026-01-29 17:19:26'),
(8, NULL, NULL, NULL, '09690805903', 'Carmona Online Permit Portal: Your application CRMN-2026-949441 requires revision. Check your email for details.', 'failed', 'Invalid API key', NULL, '2026-01-29 17:20:29'),
(9, NULL, NULL, NULL, '09690805903', 'Carmona Online Permit Portal: Good news! Your application CRMN-2026-949441 has been APPROVED.', 'failed', 'Invalid API key', NULL, '2026-01-29 17:20:51'),
(10, NULL, NULL, NULL, '09690805903', 'Carmona Online Permit Portal: Your application CRMN-2026-949441 requires revision. Check your email for details.', 'failed', 'Invalid API key', NULL, '2026-01-29 17:21:05'),
(11, NULL, NULL, NULL, '09690805903', 'Carmona Online Permit Portal: Good news! Your application CRMN-2026-949441 has been APPROVED.', 'failed', 'Invalid API key', NULL, '2026-01-29 17:23:32'),
(12, NULL, NULL, NULL, '09690805903', 'Carmona Online Permit Portal: Your application CRMN-2026-949441 is now being processed. We\'ll notify you of updates.', 'failed', 'Invalid API key', NULL, '2026-01-29 17:29:51'),
(13, NULL, NULL, NULL, '09690805903', 'Carmona Online Permit Portal: Application CRMN-2026-949441 status updated to Pending', 'failed', 'Invalid API key', NULL, '2026-01-29 17:30:03'),
(14, NULL, NULL, NULL, '09690805903', 'Carmona Online Permit Portal: Good news! Your application CRMN-2026-949441 has been APPROVED.', 'failed', 'Invalid API key', NULL, '2026-01-29 17:37:33'),
(15, NULL, NULL, NULL, '09690805903', 'Carmona Online Permit Portal: Your application CRMN-2026-949441 requires revision. Check your email for details.', 'failed', 'Invalid API key', NULL, '2026-01-29 17:38:01'),
(16, NULL, NULL, NULL, '09690805903', '✅ PAYMENT VERIFIED! Your application CRMN-2026-827607 is ready for claiming. Visit our office with valid ID and tracking number. Thank you!', 'failed', 'Invalid API key', NULL, '2026-01-29 17:53:04'),
(17, NULL, NULL, NULL, '09690805903', 'Carmona Online Permit Portal: Your application CRMN-2026-949441 is COMPLETED and ready for pickup!', 'failed', 'Invalid API key', NULL, '2026-01-29 18:38:47');

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
  `department_id` int(11) DEFAULT NULL,
  `role` enum('user','admin','department_admin','superadmin') DEFAULT 'user',
  `is_active` tinyint(1) DEFAULT 1,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Email verification status',
  `verification_token` varchar(64) DEFAULT NULL COMMENT 'Email verification token',
  `token_expiry` datetime DEFAULT NULL COMMENT 'Token expiration time',
  `email_verified_at` datetime DEFAULT NULL COMMENT 'When email was verified',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `mobile`, `address`, `department_id`, `role`, `is_active`, `is_verified`, `verification_token`, `token_expiry`, `email_verified_at`, `created_at`, `updated_at`) VALUES
(2, 'Keith Justine Ababao', 'keithjustine57@gmail.com', '$2y$10$WRYbQfDWS1eLp5aO6LYZ8OkQQjyHQp7C/iBVqQZwuDiUX50qMN2TW', '09690805903', 'farr', NULL, 'user', 1, 1, NULL, NULL, '2026-01-27 07:44:29', '2025-12-12 15:32:15', '2026-01-29 13:24:06'),
(3, 'Administrator', 'admin@lgu.gov.ph', '$2y$10$WRYbQfDWS1eLp5aO6LYZ8OkQQjyHQp7C/iBVqQZwuDiUX50qMN2TW', '09222512321', 'Carmona, Cavite', NULL, 'superadmin', 1, 1, NULL, NULL, '2026-01-27 07:44:29', '2025-12-12 15:51:18', '2026-01-29 14:46:27'),
(24, 'Test Department Admin', 'deptadmin@test.com', '$2y$10$WbROUCA15HLttniB5.8nlu2iFOBx.66HelVo8NCq5..vr0F.bUngq', '', NULL, 31, 'department_admin', 1, 1, NULL, NULL, NULL, '2026-01-29 15:40:14', '2026-01-29 15:40:14'),
(25, 'Admin OCM', 'OCMAdmin@gmail.com', '$2y$10$C.SoQYGa1qTxmuP/ZmrT/OzlsFunuCK56HCKFU7p28Ib5CbPY5JKK', '09412312398', 'Carmona', 61, 'department_admin', 1, 1, NULL, NULL, NULL, '2026-01-29 16:26:25', '2026-01-29 16:28:57');

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
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_department_id` (`department_id`),
  ADD KEY `idx_related_department_id` (`related_department_id`);

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
  ADD KEY `idx_status_date` (`status`,`created_at`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_application_id` (`application_id`),
  ADD KEY `idx_department_id` (`department_id`);

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
  ADD KEY `idx_status_date` (`status`,`created_at`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_application_id` (`application_id`),
  ADD KEY `idx_department_id` (`department_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_verification_token` (`verification_token`),
  ADD KEY `idx_is_verified` (`is_verified`),
  ADD KEY `idx_department_id` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1334;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `application_status_history`
--
ALTER TABLE `application_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=198;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=330;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payment_config`
--
ALTER TABLE `payment_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payment_history`
--
ALTER TABLE `payment_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT for table `sms_logs`
--
ALTER TABLE `sms_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD CONSTRAINT `payment_history_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_history_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
