-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 16, 2025 at 01:21 PM
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
-- Database: `agritime`
--

-- --------------------------------------------------------

--
-- Table structure for table `break_time`
--

CREATE TABLE `break_time` (
  `id` int(11) NOT NULL,
  `employeeID` varchar(255) NOT NULL,
  `time_break` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `stop_break` timestamp NULL DEFAULT NULL,
  `duration` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daily_status`
--

CREATE TABLE `daily_status` (
  `id` int(11) NOT NULL,
  `employeeID` varchar(255) NOT NULL,
  `attendance_status` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daily_status`
--

INSERT INTO `daily_status` (`id`, `employeeID`, `attendance_status`, `timestamp`) VALUES
(4, 'EMP-23455', 0, '2025-10-12 13:01:38'),
(5, 'EMP-23455', 0, '2025-10-12 13:01:38');

-- --------------------------------------------------------

--
-- Table structure for table `pay_slip`
--

CREATE TABLE `pay_slip` (
  `id` int(11) NOT NULL,
  `employeeID` varchar(255) NOT NULL,
  `startDate` date NOT NULL,
  `endDate` date NOT NULL,
  `totalHours` int(11) NOT NULL,
  `overtimeHours` int(11) NOT NULL,
  `sssDeduction` int(11) NOT NULL,
  `pagibigDeduction` int(11) NOT NULL,
  `philhealthDeduction` int(11) NOT NULL,
  `created` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `time_in`
--

CREATE TABLE `time_in` (
  `id` int(11) NOT NULL,
  `employeeID` varchar(255) NOT NULL,
  `time_in` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `time_in`
--

INSERT INTO `time_in` (`id`, `employeeID`, `time_in`) VALUES
(38, 'EMP-23455', '2025-10-05 14:32:46'),
(39, 'EMP-23455', '2025-10-12 12:35:07');

-- --------------------------------------------------------

--
-- Table structure for table `time_out`
--

CREATE TABLE `time_out` (
  `id` int(11) NOT NULL,
  `employeeID` varchar(255) NOT NULL,
  `time_out` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `time_out`
--

INSERT INTO `time_out` (`id`, `employeeID`, `time_out`) VALUES
(18, 'EMP-23455', '2025-10-12 13:01:38');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `dob` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `contactNo` varchar(20) NOT NULL,
  `role` int(11) NOT NULL,
  `profilePic` varchar(255) NOT NULL,
  `nationality` varchar(50) NOT NULL,
  `maritalStatus` varchar(50) NOT NULL,
  `emergencyContact` varchar(20) NOT NULL,
  `employeeID` varchar(20) NOT NULL,
  `basicPay` int(11) DEFAULT NULL,
  `allowances` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstName`, `lastName`, `dob`, `email`, `password`, `contactNo`, `role`, `profilePic`, `nationality`, `maritalStatus`, `emergencyContact`, `employeeID`, `basicPay`, `allowances`) VALUES
(10, 'Gabriel', 'Malanday', '2000-03-08', 'nanooomannn@gmail.com', 'gabgab', '09222932404', 2, 'views/labels/Malanday', 'Filipimo', 'Married', '09222932404', 'EMP-23455', 25000, 5000);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `break_time`
--
ALTER TABLE `break_time`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `daily_status`
--
ALTER TABLE `daily_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pay_slip`
--
ALTER TABLE `pay_slip`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `time_in`
--
ALTER TABLE `time_in`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `time_out`
--
ALTER TABLE `time_out`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `break_time`
--
ALTER TABLE `break_time`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `daily_status`
--
ALTER TABLE `daily_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pay_slip`
--
ALTER TABLE `pay_slip`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `time_in`
--
ALTER TABLE `time_in`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `time_out`
--
ALTER TABLE `time_out`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
