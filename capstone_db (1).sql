-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 11, 2025 at 12:31 PM
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
-- Database: `capstone_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `client_users`
--

CREATE TABLE `client_users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client_users`
--

INSERT INTO `client_users` (`id`, `username`, `password`) VALUES
(1, 'client1', 'client123');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `lastname` varchar(100) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `middlename` varchar(100) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact_no` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Active',
  `position` varchar(100) DEFAULT NULL,
  `last_active` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `photo`, `lastname`, `firstname`, `middlename`, `birthday`, `gender`, `age`, `address`, `contact_no`, `status`, `position`, `last_active`) VALUES
(2, 'uploads/6878bc5527a50_6T.jpeg', 'Sanchez', 'Leonard Khin', 'Sumagang', '2000-01-04', 'Male', NULL, 'Bangbang , Cordova ,Cebu', '09105006060', 'Active', 'Mason', NULL),
(3, 'uploads/6878bc84e2151_1.png', 'Sumagang', 'James', 'Andales', '2004-05-11', 'Male', NULL, 'Sanmiguel , Cordova , Cebu', '09927904755', 'Active', 'Mason', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payroll_entries`
--

CREATE TABLE `payroll_entries` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `salary` decimal(10,2) NOT NULL,
  `days_of_attendance` int(11) DEFAULT 0,
  `halfday` int(11) DEFAULT 0,
  `absent` decimal(10,2) DEFAULT 0.00,
  `holiday_pay` decimal(10,2) DEFAULT 0.00,
  `overtime_pay` decimal(10,2) DEFAULT 0.00,
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll_entries`
--

INSERT INTO `payroll_entries` (`id`, `name`, `position`, `salary`, `days_of_attendance`, `halfday`, `absent`, `holiday_pay`, `overtime_pay`, `subtotal`, `date`, `created_at`, `updated_at`) VALUES
(2, 'James A. Sumagang', 'Foreman', 500.00, 4, 1, 250.00, 1.00, 1.00, 2560.00, '2025-07-09', '2025-07-31 04:22:41', '2025-07-31 04:22:41'),
(3, 'Leonard Khin S. Sanchez', 'Mason', 450.00, 4, 1, 1000.00, 1.00, 1.00, 1535.00, '2025-07-31', '2025-07-31 04:23:57', '2025-07-31 04:23:57');

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` int(11) NOT NULL,
  `position_name` varchar(100) NOT NULL,
  `daily_rate` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `project_id` int(11) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `deadline` date NOT NULL,
  `location` varchar(255) NOT NULL,
  `project_cost` decimal(15,2) NOT NULL,
  `foreman` varchar(255) NOT NULL,
  `project_type` varchar(100) NOT NULL,
  `project_status` varchar(50) NOT NULL,
  `project_divisions` varchar(255) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`project_id`, `project_name`, `start_date`, `deadline`, `location`, `project_cost`, `foreman`, `project_type`, `project_status`, `project_divisions`, `image_path`) VALUES
(2, 'Sample project', '2025-07-17', '2025-07-17', 'Cagay , Sibonga , Cebu City', 300000.00, 'Marlon Loonie Peroramas', 'Building', 'Ongoing', 'Phase , 1 , Phase 2 , Phase 3 , Phase 4 , Phase 5', 'uploads/6878ca7860f84.png');

-- --------------------------------------------------------

--
-- Table structure for table `project_divisions`
--

CREATE TABLE `project_divisions` (
  `id` int(11) NOT NULL,
  `division_name` varchar(100) NOT NULL,
  `project_type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_divisions`
--

INSERT INTO `project_divisions` (`id`, `division_name`, `project_type`) VALUES
(3, 'Phase1', 'Building'),
(4, 'Phase2', 'House/Building');

-- --------------------------------------------------------

--
-- Table structure for table `project_phase_steps`
--

CREATE TABLE `project_phase_steps` (
  `project_id` int(11) NOT NULL,
  `phase_name` varchar(255) NOT NULL,
  `step_name` varchar(255) NOT NULL,
  `step_order` int(11) DEFAULT 1,
  `description` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `date_started` date DEFAULT NULL,
  `date_completed` date DEFAULT NULL,
  `division_name` varchar(255) DEFAULT NULL,
  `step_description` text DEFAULT NULL,
  `is_finished` tinyint(1) DEFAULT 0,
  `image_path` varchar(255) DEFAULT NULL,
  `step_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_phase_steps`
--

INSERT INTO `project_phase_steps` (`project_id`, `phase_name`, `step_name`, `step_order`, `description`, `status`, `date_started`, `date_completed`, `division_name`, `step_description`, `is_finished`, `image_path`, `step_id`) VALUES
(2, '', '', 1, NULL, 'Pending', NULL, NULL, 'Phase 1', 'Clearing', 1, 'uploads/step_images/step_2_Phase 1_1_1752746930_0.jpg', 8),
(2, '', '', 1, NULL, 'Pending', NULL, NULL, 'Phase 1', 'Clearing', 1, 'uploads/step_images/step_2_Phase 1_1_1752746930_1.jpg', 9),
(2, '', '', 1, NULL, 'Pending', NULL, NULL, 'Phase 1', 'Clearing', 1, 'uploads/step_images/step_2_Phase 1_1_1752746930_2.jpg', 10);

-- --------------------------------------------------------

--
-- Table structure for table `project_progress`
--

CREATE TABLE `project_progress` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `division` varchar(255) NOT NULL,
  `phase` varchar(255) NOT NULL,
  `step` varchar(255) NOT NULL,
  `progress_percent` decimal(5,2) DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp(),
  `division_name` varchar(255) DEFAULT NULL,
  `progress_percentage` decimal(5,2) DEFAULT 0.00,
  `date_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_progress`
--

INSERT INTO `project_progress` (`id`, `project_id`, `division`, `phase`, `step`, `progress_percent`, `remarks`, `updated_at`, `division_name`, `progress_percentage`, `date_updated`) VALUES
(6, 2, '', '', '', 0.00, NULL, '2025-07-17 18:09:07', 'Phase 1', 100.00, '2025-07-17 00:00:00'),
(7, 2, '', '', '', 0.00, NULL, '2025-07-17 18:09:07', 'Phase 2', 0.00, '2025-07-17 00:00:00'),
(8, 2, '', '', '', 0.00, NULL, '2025-07-17 18:09:07', 'Phase 3', 0.00, '2025-07-17 00:00:00'),
(9, 2, '', '', '', 0.00, NULL, '2025-07-17 18:09:07', 'Phase 4', 0.00, '2025-07-17 00:00:00'),
(10, 2, '', '', '', 0.00, NULL, '2025-07-17 18:09:07', 'Phase 5', 0.00, '2025-07-17 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `project_team`
--

CREATE TABLE `project_team` (
  `id` int(11) NOT NULL,
  `foreman_id` int(11) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_team`
--

INSERT INTO `project_team` (`id`, `foreman_id`, `status`, `created_at`, `updated_at`) VALUES
(3, 2, 'Active', '2025-07-17 09:50:48', '2025-07-17 09:50:48');

-- --------------------------------------------------------

--
-- Table structure for table `project_teams`
--

CREATE TABLE `project_teams` (
  `id` int(11) NOT NULL,
  `team_name` varchar(100) NOT NULL,
  `division_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_team_members`
--

CREATE TABLE `project_team_members` (
  `id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_team_members`
--

INSERT INTO `project_team_members` (`id`, `team_id`, `employee_id`) VALUES
(6, 3, 2),
(7, 3, 3);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_logged_in` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `is_logged_in`) VALUES
(1, 'admin', 'admin123', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `client_users`
--
ALTER TABLE `client_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payroll_entries`
--
ALTER TABLE `payroll_entries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`project_id`);

--
-- Indexes for table `project_divisions`
--
ALTER TABLE `project_divisions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `project_phase_steps`
--
ALTER TABLE `project_phase_steps`
  ADD PRIMARY KEY (`step_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `project_progress`
--
ALTER TABLE `project_progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `project_team`
--
ALTER TABLE `project_team`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_foreman` (`foreman_id`);

--
-- Indexes for table `project_teams`
--
ALTER TABLE `project_teams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `division_id` (`division_id`);

--
-- Indexes for table `project_team_members`
--
ALTER TABLE `project_team_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `team_id` (`team_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `client_users`
--
ALTER TABLE `client_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payroll_entries`
--
ALTER TABLE `payroll_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `project_divisions`
--
ALTER TABLE `project_divisions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `project_phase_steps`
--
ALTER TABLE `project_phase_steps`
  MODIFY `step_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `project_progress`
--
ALTER TABLE `project_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `project_team`
--
ALTER TABLE `project_team`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `project_teams`
--
ALTER TABLE `project_teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_team_members`
--
ALTER TABLE `project_team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `project_phase_steps`
--
ALTER TABLE `project_phase_steps`
  ADD CONSTRAINT `project_phase_steps_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`);

--
-- Constraints for table `project_progress`
--
ALTER TABLE `project_progress`
  ADD CONSTRAINT `project_progress_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`);

--
-- Constraints for table `project_team`
--
ALTER TABLE `project_team`
  ADD CONSTRAINT `fk_foreman` FOREIGN KEY (`foreman_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `project_team_ibfk_1` FOREIGN KEY (`foreman_id`) REFERENCES `employees` (`id`);

--
-- Constraints for table `project_teams`
--
ALTER TABLE `project_teams`
  ADD CONSTRAINT `project_teams_ibfk_1` FOREIGN KEY (`division_id`) REFERENCES `project_divisions` (`id`);

--
-- Constraints for table `project_team_members`
--
ALTER TABLE `project_team_members`
  ADD CONSTRAINT `project_team_members_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `project_team` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_team_members_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
