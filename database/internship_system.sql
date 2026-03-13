-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 13, 2026 at 04:25 AM
-- Server version: 5.7.24
-- PHP Version: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `internship_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `assessments`
--

CREATE TABLE `assessments` (
  `assessment_id` varchar(10) NOT NULL,
  `internship_id` varchar(10) DEFAULT NULL,
  `score_tasks` int(11) DEFAULT '0',
  `score_safety` int(11) DEFAULT '0',
  `score_theory` int(11) DEFAULT '0',
  `score_presentation` int(11) DEFAULT '0',
  `score_clarity` int(11) DEFAULT '0',
  `score_learning` int(11) DEFAULT '0',
  `score_project_mgmt` int(11) DEFAULT '0',
  `score_time_mgmt` int(11) DEFAULT '0',
  `total_mark` decimal(5,2) DEFAULT NULL,
  `comments` text,
  `date_evaluated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `internships`
--

CREATE TABLE `internships` (
  `internship_id` varchar(20) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `assessor_id` varchar(20) DEFAULT NULL,
  `company_name` varchar(150) NOT NULL,
  `internship_status` enum('Ongoing','Completed','Evaluated') DEFAULT 'Ongoing'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `internships`
--

INSERT INTO `internships` (`internship_id`, `student_id`, `assessor_id`, `company_name`, `internship_status`) VALUES
('INT-1', '20810450', 'LEC-002', 'Intel Malaysia', 'Ongoing'),
('INT-10', '20709815', 'LEC-002', 'Grab Holdings', 'Ongoing'),
('INT-11', '20799262', 'LEC-002', 'AWS Malaysia', 'Ongoing'),
('INT-12', '20806316', 'LEC-002', 'AWS Malaysia', 'Ongoing'),
('INT-13', '20807535', 'LEC-002', 'Shopee MY', 'Ongoing'),
('INT-14', '20700617', 'LEC-002', 'Shopee MY', 'Ongoing'),
('INT-15', '20723858', 'LEC-002', 'Tesla Services', 'Ongoing'),
('INT-16', '20713226', 'LEC-002', 'Tesla Services', 'Ongoing'),
('INT-17', '20793978', 'LEC-002', 'IBM Malaysia', 'Ongoing'),
('INT-18', '20715078', 'LEC-002', 'IBM Malaysia', 'Ongoing'),
('INT-19', '20805281', 'LEC-002', 'Oracle Corp', 'Ongoing'),
('INT-2', '20713102', 'LEC-002', 'Intel Malaysia', 'Ongoing'),
('INT-20', '20796056', 'LEC-002', 'Oracle Corp', 'Ongoing'),
('INT-21', '20708501', 'LEC-002', 'Dell Technologies', 'Ongoing'),
('INT-22', '20808856', 'LEC-002', 'Dell Technologies', 'Ongoing'),
('INT-23', '20791965', 'LEC-002', 'Cisco Systems', 'Ongoing'),
('INT-24', '20806311', 'LEC-002', 'Cisco Systems', 'Ongoing'),
('INT-25', '20797569', 'LEC-002', 'Samsung Electronics', 'Ongoing'),
('INT-26', '20713495', 'LEC-002', 'Samsung Electronics', 'Ongoing'),
('INT-27', '20718823', 'LEC-002', 'Apple Malaysia', 'Ongoing'),
('INT-28', '20706107', 'LEC-002', 'Apple Malaysia', 'Evaluated'),
('INT-29', '20716333', 'LEC-002', 'Maxis Berhad', 'Ongoing'),
('INT-3', '20722321', 'LEC-002', 'Google MY', 'Ongoing'),
('INT-30', '20806478', 'LEC-002', 'Maxis Berhad', 'Ongoing'),
('INT-4', '20709820', 'LEC-002', 'Google MY', 'Ongoing'),
('INT-5', '20804611', 'LEC-002', 'Microsoft KL', 'Ongoing'),
('INT-6', '20808475', 'LEC-002', 'Microsoft KL', 'Ongoing'),
('INT-7', '20801771', 'LEC-002', 'Petronas', 'Ongoing'),
('INT-8', '20797468', 'LEC-002', 'Petronas', 'Ongoing'),
('INT-9', '20709091', 'LEC-002', 'Grab Holdings', 'Ongoing');

-- --------------------------------------------------------

--
-- Table structure for table `programmes`
--

CREATE TABLE `programmes` (
  `programme_id` varchar(20) NOT NULL,
  `programme_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `programmes`
--

INSERT INTO `programmes` (`programme_id`, `programme_name`) VALUES
('PRG-2', 'BEng Software Engineering'),
('PRG-1', 'BSc Computer Science'),
('PRG-3', 'BSc Data Science');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` varchar(20) NOT NULL,
  `internship_id` varchar(20) DEFAULT NULL,
  `student_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `programme_id` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `supervisor_id` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `internship_id`, `student_name`, `email`, `programme_id`, `created_at`, `supervisor_id`) VALUES
('20609637', 'INT-20609637', 'Siyu Ge', 'hfysg5@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-002'),
('20675394', 'INT-20675394', 'Kamila Mahenti', 'hfykm5@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-003'),
('20676720', 'INT-20676720', 'Jialue Liao', 'hfyjl36@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-004'),
('20679455', 'INT-20679455', 'Mohamed Hany Abdelmaksoud', 'hfyma18@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-005'),
('20683692', 'INT-20683692', 'Youssef Mahran', 'hfyym6@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54', 'LEC-004'),
('20690412', 'INT-20690412', 'Mariah Azmir Faizal', 'efyma48@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-006'),
('20700617', 'INT-20700617', 'Zhe Gao', 'hfyzg1@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-002'),
('20701943', 'INT-20701943', 'Jun Zhe Yeong', 'hfyjy12@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54', 'LEC-005'),
('20703441', 'INT-20703441', 'Jia En Sai', 'hfyjs12@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-003'),
('20705302', 'INT-20705302', 'Hin Joong Soo', 'hfyhs5@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54', 'LEC-006'),
('20705375', 'INT-20705375', 'Husam Feras Hosam Boshnaq', 'efyhh14@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54', 'LEC-006'),
('20706107', 'INT-20706107', 'Shunxi Yang', 'hfysy8@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54', 'LEC-002'),
('20708501', 'INT-20708501', 'You Sheng, Ciaran Ooi', 'hfyyo2@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54', 'LEC-002'),
('20708827', 'INT-20708827', 'Rachel Huey Yen Lee', 'hfyrl4@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54', 'LEC-003'),
('20709091', 'INT-20709091', 'Yun Xin Ng', 'efyyn8@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54', 'LEC-004'),
('20709815', 'INT-20709815', 'Lang Qin', 'efylq1@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54', 'LEC-005'),
('20709820', 'INT-20709820', 'Junta Suzuki', 'hfyjs13@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-004'),
('20710895', 'INT-20710895', 'Jian Yun Tan', 'hfyjt17@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-005'),
('20711344', 'INT-20711344', 'Aiko Yi Rou Wong', 'hfyaw3@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-006'),
('20711677', 'INT-20711677', 'Wei Feng Hue', 'hfywh4@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-002'),
('20712533', 'INT-20712533', 'Yoonjae Lee', 'hfyyl16@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-003'),
('20713102', 'INT-20713102', 'Jun Jiet Jong', 'hfyjj4@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-004'),
('20713226', 'INT-20713226', 'Li You Lee', 'hfyll10@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-005'),
('20713495', 'INT-20713495', 'Lei Su', 'hfyls4@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54', 'LEC-006'),
('20713862', 'INT-20713862', 'Zikai Wang', 'hfyzw6@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54', 'LEC-003'),
('20713959', 'INT-20713959', 'Meenakshi Murugappan', 'hfymm21@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-006'),
('20713963', 'INT-20713963', 'Seann Ryu Hearn Kwan', 'hfysk9@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54', 'LEC-004'),
('20714943', 'INT-20714943', 'Linjie Fu', 'hfylf1@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-002'),
('20715075', 'INT-20715075', 'Jiexun Tang', 'efyjt32@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-003'),
('20715078', 'INT-20715078', 'Muhammad Faysal Md Mijanur Rahman', 'hfymm22@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54', 'LEC-005'),
('20716333', 'INT-20716333', 'Grace Shuang Yee', 'hfygy1@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-004'),
('20717493', 'INT-20717493', 'Kang Wei Chan', 'hfykc15@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54', 'LEC-006'),
('20718823', 'INT-20718823', 'Menghuan Wu', 'hcymw2@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54', 'LEC-002'),
('20722321', 'INT-20722321', 'Clarissa Jia Yi Kiew', 'hfyck5@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-005'),
('20723833', 'INT-20723833', 'Ian Yu Sheng Yap', 'hfyiy1@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54', 'LEC-002'),
('20723858', 'INT-20723858', 'Kiara Leshan Kwo', 'hfykk7@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-006'),
('20749006', 'INT-20749006', 'Chenhaoxi Zhou', 'hcycz1@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54', 'LEC-003'),
('20780335', 'INT-20780335', 'Fathima Sakinah Dil Fairaz', 'hcyfd1@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-002'),
('20782788', 'INT-20782788', 'Ruining Ding', 'hcyrd2@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54', 'LEC-004'),
('20791953', 'INT-20791953', 'Tianli Chen', 'hcytc2@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54', 'LEC-003'),
('20791965', 'INT-20791965', 'Chaoyuan Zhang', 'hcycz2@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-003'),
('20792685', 'INT-20792685', 'Mingcai Ling', 'hcyml3@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54', 'LEC-005'),
('20793088', 'INT-20793088', 'Changhui Deng', 'hcycd1@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54', 'LEC-006'),
('20793790', 'INT-20793790', 'Wu Han Hue', 'hcywh5@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54', 'LEC-004'),
('20793978', 'INT-20793978', 'Terence Kian Seng Lee', 'hcytl3@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54', 'LEC-002'),
('20794457', 'INT-20794457', 'Ali Ibrahim Alkomey', 'hcyai1@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54', 'LEC-003'),
('20796056', 'INT-20796056', 'Mingxu Liu', 'hcyml4@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54', 'LEC-004'),
('20796507', 'INT-20796507', 'Yifei Xie', 'hcyyx2@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54', 'LEC-005'),
('20797468', 'INT-20797468', 'Guangbing Liu', 'hcygl2@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54', 'LEC-006'),
('20797569', 'INT-20797569', 'Lang Chen', 'hcylc3@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54', 'LEC-002'),
('20799262', 'INT-20799262', 'Nanxi Zhang', 'hcynz1@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54', 'LEC-003'),
('20800273', 'INT-20800273', 'Jun Bin Wong', 'hcyjw4@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54', 'LEC-004'),
('20800646', 'INT-20800646', 'Shiyu Cao', 'hcysc4@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54', 'LEC-005'),
('20801522', 'INT-20801522', 'Hao Yin Ng', 'edyhn2@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54', 'LEC-006'),
('20801771', 'INT-20801771', 'Huda Amin', 'hcyha1@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54', 'LEC-005'),
('20802060', 'INT-20802060', 'Atsuhiro Tsukata', 'hcyat2@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-004'),
('20803923', 'INT-20803923', 'Chenyu Li', 'hcycl7@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54', 'LEC-002'),
('20803979', 'INT-20803979', 'Zhiling Tang', 'hcyzt5@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54', 'LEC-006'),
('20804611', 'INT-20804611', 'Sixuan Wang', 'hcysw2@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-005'),
('20804966', 'INT-20804966', 'Samay Rayapuram', 'hcysr4@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54', 'LEC-002'),
('20805040', 'INT-20805040', 'Aazzu Adam Khalid', 'hcyaa11@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54', 'LEC-003'),
('20805230', 'INT-20805230', 'Ying Qi Tan', 'hcyyt3@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-006'),
('20805281', 'INT-20805281', 'Chen Yi Lee', 'hcycl8@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54', 'LEC-004'),
('20806299', 'INT-20806299', 'Daniyal Khan', 'hcydk2@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54', 'LEC-005'),
('20806311', 'INT-20806311', 'Yangting Zhuang', 'hcyyz2@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-002'),
('20806316', 'INT-20806316', 'Hanrui Zou', 'hcyhz3@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54', 'LEC-003'),
('20806377', 'INT-20806377', 'Khalid Mohamad Shaker', 'hcykm1@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54', 'LEC-006'),
('20806478', 'INT-20806478', 'Jiachang Ying', 'hcyjy6@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-003'),
('20806629', 'INT-20806629', 'Qianqian Yang', 'hcyqy1@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-004'),
('20807243', 'INT-20807243', 'Jacques Milton', 'hcyjm2@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54', 'LEC-004'),
('20807535', 'INT-20807535', 'Richard Erkhov', 'hcyre1@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-005'),
('20807958', 'INT-20807958', 'Xitai Li', 'hcyxl7@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54', 'LEC-002'),
('20808475', 'INT-20808475', 'Canshuo Yu', 'hcycy1@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-006'),
('20808713', 'INT-20808713', 'Lei Zhu', 'hcylz1@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54', 'LEC-003'),
('20808856', 'INT-20808856', 'Muhammad Rafay Shahid', 'hcyms6@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54', 'LEC-005'),
('20809859', 'INT-20809859', 'Khizer Asim', 'hcyka4@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54', 'LEC-004'),
('20810450', 'INT-20810450', 'Yuqing He', 'hcyyh3@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-002'),
('20811803', 'INT-20811803', 'Shi Qi Koh', 'hcysk3@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54', 'LEC-003'),
('20814150', 'INT-20814150', 'Qoid Rafif Mohd Fadly', 'hcyqm1@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54', 'LEC-005');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` varchar(20) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('Admin','Assessor') NOT NULL DEFAULT 'Assessor',
  `last_login` datetime DEFAULT NULL,
  `otp_code` varchar(6) DEFAULT NULL,
  `otp_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `full_name`, `role`, `last_login`, `otp_code`, `otp_expires`) VALUES
('ADM-1', 'admin1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Aris Brown', 'Admin', NULL, NULL, NULL),
('LEC-002', 'lec1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Robert Low', 'Assessor', NULL, NULL, NULL),
('LEC-003', 'lec2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ms. Sarah Lim', 'Assessor', NULL, NULL, NULL),
('LEC-004', 'lec3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mr. Kevin Tan', 'Assessor', NULL, NULL, NULL),
('LEC-005', 'lec4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Jane Smith', 'Assessor', NULL, NULL, NULL),
('LEC-006', 'lec5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Prof. Wong Kar', 'Assessor', NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assessments`
--
ALTER TABLE `assessments`
  ADD PRIMARY KEY (`assessment_id`),
  ADD UNIQUE KEY `internship_id` (`internship_id`);

--
-- Indexes for table `internships`
--
ALTER TABLE `internships`
  ADD PRIMARY KEY (`internship_id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD KEY `internships_ibfk_2` (`assessor_id`);

--
-- Indexes for table `programmes`
--
ALTER TABLE `programmes`
  ADD PRIMARY KEY (`programme_id`),
  ADD UNIQUE KEY `programme_name` (`programme_name`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `student_name` (`student_name`),
  ADD KEY `fk_student_programme` (`programme_id`),
  ADD KEY `fk_student_supervisor` (`supervisor_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assessments`
--
ALTER TABLE `assessments`
  ADD CONSTRAINT `assessments_ibfk_1` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`internship_id`) ON DELETE CASCADE;

--
-- Constraints for table `internships`
--
ALTER TABLE `internships`
  ADD CONSTRAINT `internships_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `internships_ibfk_2` FOREIGN KEY (`assessor_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_student_programme` FOREIGN KEY (`programme_id`) REFERENCES `programmes` (`programme_id`),
  ADD CONSTRAINT `fk_student_supervisor` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
