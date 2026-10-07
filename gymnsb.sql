-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 29, 2024 at 02:23 PM
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
-- Database: `gymnsb`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `name`) VALUES
(1, 'admin', 'c93ccd78b2076528346216b3b2f701e6', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `message` varchar(100) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `message`, `date`) VALUES
(9, 'Renovation Going On...', '2020-04-04'),
(14, 'Adding new equipments', '2024-02-29');

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `amount` int(100) NOT NULL,
  `quantity` int(100) NOT NULL,
  `total_amount` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `vendor_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`id`, `name`, `amount`, `quantity`, `total_amount`, `description`, `date`, `vendor_id`) VALUES
(11, 'Dumbell Set', 12000, 2, 24000, 'High Grip Dumbell Set with Ribber plates', '2024-03-30', 1);

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(20) NOT NULL,
  `password` varchar(100) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `dor` date NOT NULL,
  `services_id` int(11) NOT NULL,
  `plan` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `contact` varchar(10) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `reminder` int(11) NOT NULL DEFAULT 0,
  `pay_date` date DEFAULT NULL,
  `plan_link` varchar(255) NOT NULL DEFAULT '',
  `trainer_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `fullname`, `username`, `password`, `gender`, `dor`, `services_id`, `plan`, `address`, `contact`, `status`, `reminder`, `pay_date`, `plan_link`) VALUES
(1, 'Ram Kumar', 'ram', '6a557ed1005dddd940595b8fc6ed47b2', 'male', '2024-02-28', 1, '6', 'Kathmandu', '9876543210', 'active', 0, '2024-04-23', 'https://docs.google.com/spreadsheets/d/12VaPkgRgjo8JVAatI3mfpMsZ1uEzrONAcmYuAywja4g/edit?gid=0#gid=0'),
(2, 'shyam bahadur', 'shyam', 'cffba1722dd649bd7a72a37e48358b0f', 'male', '2024-02-29', 3, '1', 'Ipsa non non laboru', 'At cupidit', 'active', 0, '2024-04-21', 'https://docs.google.com/spreadsheets/d/1pjuJcjsQiXsuFkC2LgYjlyieTzB4xHkJ4eHJ_RCkqQw/edit?gid=279489010#gid=279489010'),
(5, 'Hari Bahadur', 'hari', 'bd1839a2fcfcb41ccc4942ca617cc2a5', 'male', '2024-06-16', 2, '1', 'Dolorem officia illu', 'Quia offic', 'active', 0, '0000-00-00', ''),
(6, 'Roanna Fowler', 'vihifilu', '815188a998047a30ac00400f15beae05', 'female', '2024-06-17', 3, '1', 'Eum adipisci expedit', 'Iste irure', 'Active', 0, '0000-00-00', ''),
(7, 'Blaine Mccray', 'lolywyxyv', '6e4db6a4e1dbadc68e8505d3fe3e21f8', 'female', '2024-06-17', 1, '3', 'Numquam enim eius mo', 'Rerum magn', 'Active', 0, '0000-00-00', ''),
(9, 'Lesley Glover', 'repazogoq', '53539afa6d1e5c80fb8ad29ea5f72115', 'male', '2024-06-19', 1, '3', 'Optio alias sint q', 'Aspernatur', 'Active', 0, '0000-00-00', '');

--
-- Triggers `members`
--
DELIMITER $$
CREATE TRIGGER `InsertProgress` AFTER INSERT ON `members` FOR EACH ROW INSERT INTO progress VALUES(null,0,0,'','',NEW.id)
$$
DELIMITER ;
-- --------------------------------------------------------

--
-- Table structure for table `progress`
--

CREATE TABLE `progress` (
  `id` int(11) NOT NULL,
  `ini_weight` int(11) DEFAULT NULL,
  `curr_weight` int(11) DEFAULT NULL,
  `ini_bodytype` varchar(100) DEFAULT NULL,
  `curr_bodytype` varchar(100) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `progress`
--

INSERT INTO `progress` (`id`, `ini_weight`, `curr_weight`, `ini_bodytype`, `curr_bodytype`, `member_id`) VALUES
(2, 54, 64, 'slim', 'athletic', 1),
(3, 56, 54, 'slim', 'slim', 2),
(6, 0, 0, '', '', 5),
(7, 0, 0, '', '', 6),
(8, 0, 0, '', '', 7),
(9, 0, 0, '', '', 9);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `service_name` varchar(100) NOT NULL,
  `cost` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `service_name`, `cost`) VALUES
(1, 'Fitness', 3500),
(2, 'Sauna', 2500),
(3, 'Cardio', 2000);

-- --------------------------------------------------------

--
-- Table structure for table `staffs`
--

CREATE TABLE `staffs` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact` varchar(100) NOT NULL,
  `address` varchar(100) NOT NULL,
  `designation` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staffs`
--

INSERT INTO `staffs` (`id`, `fullname`, `username`, `password`, `gender`, `email`, `contact`, `address`, `designation`) VALUES
(1, 'Shyam Maharjan', 'shyam', 'shyam1234', 'Male', 'shyam@gmail.com', '1234567890', 'kathmandu', 'Trainer');

-- --------------------------------------------------------

--
-- Table structure for table `todo`
--

CREATE TABLE `todo` (
  `id` int(11) NOT NULL,
  `task_status` varchar(50) NOT NULL,
  `task_desc` varchar(30) NOT NULL,
  `user_id` int(7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `todo`
--

INSERT INTO `todo` (`id`, `task_status`, `task_desc`, `user_id`) VALUES
(3, 'In Progress', 'dunmbell press', 1);

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` int(11) NOT NULL,
  `vendor_name` varchar(100) NOT NULL,
  `address` varchar(100) NOT NULL,
  `contact` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendors`
--

INSERT INTO `vendors` (`id`, `vendor_name`, `address`, `contact`) VALUES
(1, 'Sports hub', 'kathmandu', '9876543210'),
(2, 'Fitness Club', 'Kirtipur', '9876123450');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_vendor_id` (`vendor_id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `services_id` (`services_id`);

--
-- Indexes for table `progress`
--
ALTER TABLE `progress`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staffs`
--
ALTER TABLE `staffs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `todo`
--
ALTER TABLE `todo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `progress`
--
ALTER TABLE `progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `staffs`
--
ALTER TABLE `staffs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `todo`
--
ALTER TABLE `todo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `equipment`
--
ALTER TABLE `equipment`
  ADD CONSTRAINT `fk_vendor_id` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`);

--
-- Constraints for table `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `members_ibfk_1` FOREIGN KEY (`services_id`) REFERENCES `services` (`id`);

--
-- Constraints for table `progress`
--
ALTER TABLE `progress`
  ADD CONSTRAINT `progress_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`);

--
-- Constraints for table `todo`
--
ALTER TABLE `todo`
  ADD CONSTRAINT `todo_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `members` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Class schedules from the documented admin/trainer/member requirements.
CREATE TABLE IF NOT EXISTS class_schedules (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(100) NOT NULL,
  trainer_id INT DEFAULT NULL,
  starts_at DATETIME NOT NULL,
  duration_minutes INT NOT NULL DEFAULT 60,
  location VARCHAR(100) NOT NULL,
  description TEXT NOT NULL,
  KEY schedule_date (starts_at),
  KEY schedule_trainer (trainer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Keep each recorded cash payment and renewal for receipts and earnings.
CREATE TABLE IF NOT EXISTS payments (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  member_id INT NOT NULL,
  monthly_amount DECIMAL(12,2) NOT NULL,
  months INT NOT NULL,
  total_amount DECIMAL(12,2) NOT NULL,
  paid_on DATE NOT NULL,
  recorded_by INT NULL,
  recorded_role VARCHAR(20) NOT NULL,
  KEY member_payments (member_id, paid_on),
  CONSTRAINT payment_member FOREIGN KEY (member_id) REFERENCES members (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Requested local demo credentials for both roles: @spandan / 123.
-- Widen the legacy admin column to store a modern password hash.
ALTER TABLE admin MODIFY password VARCHAR(100) NOT NULL;
START TRANSACTION;
INSERT INTO admin (username, password, name)
SELECT '@spandan', '$2y$10$5ysiuVZk5Iot7dsglArfru7LAO14PfLjv9E.su1.spMvLMgyCoOHu', 'Spandan'
WHERE NOT EXISTS (SELECT 1 FROM admin WHERE username = '@spandan');
UPDATE admin SET password = '$2y$10$5ysiuVZk5Iot7dsglArfru7LAO14PfLjv9E.su1.spMvLMgyCoOHu' WHERE username = '@spandan';
INSERT INTO staffs (fullname, username, password, gender, email, contact, address, designation)
SELECT 'Spandan', '@spandan', '$2y$10$5ysiuVZk5Iot7dsglArfru7LAO14PfLjv9E.su1.spMvLMgyCoOHu', 'Others', 'spandan.demo@example.invalid', '', '', 'Trainer'
WHERE NOT EXISTS (SELECT 1 FROM staffs WHERE username = '@spandan');
UPDATE staffs SET password = '$2y$10$5ysiuVZk5Iot7dsglArfru7LAO14PfLjv9E.su1.spMvLMgyCoOHu', designation = 'Trainer' WHERE username = '@spandan';
COMMIT;
