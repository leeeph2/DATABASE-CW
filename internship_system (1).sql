-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 26, 2026 at 06:23 AM
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
  `assessment_id` varchar(15) NOT NULL,
  `internship_id` varchar(255) NOT NULL,
  `assessor_id` varchar(20) DEFAULT NULL,
  `assessment_type` enum('Academic','Industry') NOT NULL,
  `assessor_type` enum('Lecturer','Supervisor') NOT NULL,
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
-- Stand-in structure for view `final_results`
-- (See below for the actual view)
--
CREATE TABLE `final_results` (
`internship_id` varchar(255)
,`student_id` varchar(20)
,`student_name` varchar(100)
,`email` varchar(255)
,`programme_name` varchar(100)
,`company_name` varchar(150)
,`internship_status` varchar(50)
,`lecturer_name` varchar(100)
,`supervisor_name` varchar(100)
,`lecturer_total` decimal(5,2)
,`supervisor_total` decimal(5,2)
,`final_score` decimal(7,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `internships`
--

CREATE TABLE `internships` (
  `student_id` varchar(20) NOT NULL,
  `lecturer_id` varchar(20) DEFAULT NULL,
  `supervisor_id` varchar(20) DEFAULT NULL,
  `company_name` varchar(150) NOT NULL,
  `internship_status` varchar(50) DEFAULT 'Pending',
  `internship_id` varchar(255) NOT NULL,
  `final_mark` decimal(5,2) DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `internships`
--

INSERT INTO `internships` (`student_id`, `lecturer_id`, `supervisor_id`, `company_name`, `internship_status`, `internship_id`, `final_mark`) VALUES
('20609637', 'LEC-003', 'SUP-001', 'Huawei Malaysia', 'Ongoing', '1', '0.00'),
('20705302', 'LEC-004', 'SUP-005', 'Mimos Berhad', 'Ongoing', '10', '0.00'),
('20705375', 'LEC-004', 'SUP-005', 'Mimos Berhad', 'Ongoing', '11', '0.00'),
('20706107', 'LEC-003', 'SUP-004', 'Apple Malaysia', 'Evaluated', '12', '0.00'),
('20708501', 'LEC-003', 'SUP-001', 'Dell Technologies', 'Ongoing', '13', '0.00'),
('20708827', 'LEC-004', 'SUP-001', 'Ericsson Malaysia', 'Ongoing', '14', '0.00'),
('20709091', 'LEC-002', 'SUP-005', 'Grab Holdings', 'Ongoing', '15', '0.00'),
('20709815', 'LEC-002', 'SUP-005', 'Grab Holdings', 'Ongoing', '16', '0.00'),
('20709820', 'LEC-002', 'SUP-002', 'Google MY', 'Ongoing', '17', '0.00'),
('20710895', 'LEC-004', 'SUP-001', 'Ericsson Malaysia', 'Ongoing', '18', '0.00'),
('20675394', 'LEC-003', 'SUP-001', 'Huawei Malaysia', 'Ongoing', '2', '0.00'),
('20711677', 'LEC-004', 'SUP-002', 'Motorola Solutions', 'Ongoing', '20', '0.00'),
('20712533', 'LEC-004', 'SUP-003', 'Lenovo Malaysia', 'Ongoing', '21', '0.00'),
('20713102', 'LEC-002', 'SUP-001', 'Intel Malaysia', 'Ongoing', '22', '0.00'),
('20713226', 'LEC-002', 'SUP-003', 'Tesla Services', 'Ongoing', '23', '0.00'),
('20713495', 'LEC-003', 'SUP-003', 'Samsung Electronics', 'Ongoing', '24', '0.00'),
('20713862', 'LEC-004', 'SUP-003', 'Lenovo Malaysia', 'Ongoing', '25', '0.00'),
('20713959', 'LEC-004', 'SUP-004', 'NTT Malaysia', 'Ongoing', '26', '0.00'),
('20713963', 'LEC-004', 'SUP-004', 'NTT Malaysia', 'Ongoing', '27', '0.00'),
('20714943', 'LEC-005', 'SUP-005', 'Bosch Malaysia', 'Ongoing', '28', '0.00'),
('20715075', 'LEC-005', 'SUP-005', 'Bosch Malaysia', 'Ongoing', '29', '0.00'),
('20676720', 'LEC-004', 'SUP-002', 'Axiata Group', 'Ongoing', '3', '0.00'),
('20715078', 'LEC-003', 'SUP-004', 'IBM Malaysia', 'Ongoing', '30', '0.00'),
('20716333', 'LEC-003', 'SUP-005', 'Maxis Berhad', 'Ongoing', '31', '0.00'),
('20717493', 'LEC-005', 'SUP-001', 'Siemens Malaysia', 'Ongoing', '32', '0.00'),
('20718823', 'LEC-003', 'SUP-004', 'Apple Malaysia', 'Ongoing', '33', '0.00'),
('20722321', 'LEC-002', 'SUP-002', 'Google MY', 'Ongoing', '34', '86.00'),
('20723833', 'LEC-005', 'SUP-001', 'Siemens Malaysia', 'Ongoing', '35', '0.00'),
('20723858', 'LEC-002', 'SUP-003', 'Tesla Services', 'Ongoing', '36', '0.00'),
('20749006', 'LEC-005', 'SUP-002', 'HP Malaysia', 'Ongoing', '37', '0.00'),
('20780335', 'LEC-005', 'SUP-002', 'HP Malaysia', 'Ongoing', '38', '0.00'),
('20679455', 'LEC-004', 'SUP-002', 'Axiata Group', 'Ongoing', '4', '0.00'),
('20791953', 'LEC-005', 'SUP-003', 'Accenture MY', 'Ongoing', '40', '0.00'),
('20792685', 'LEC-005', 'SUP-004', 'Deloitte Malaysia', 'Ongoing', '42', '0.00'),
('20793790', 'LEC-005', 'SUP-005', 'KPMG Malaysia', 'Ongoing', '44', '0.00'),
('20793978', 'LEC-003', 'SUP-004', 'IBM Malaysia', 'Ongoing', '45', '0.00'),
('20796056', 'LEC-003', 'SUP-005', 'Oracle Corp', 'Ongoing', '47', '0.00'),
('20796507', 'LEC-005', 'SUP-001', 'PwC Malaysia', 'Ongoing', '48', '0.00'),
('20797468', 'LEC-002', 'SUP-004', 'Petronas', 'Ongoing', '49', '52.00'),
('20683692', 'LEC-004', 'SUP-003', 'Celcom Digi', 'Ongoing', '5', '0.00'),
('20797569', 'LEC-003', 'SUP-003', 'Samsung Electronics', 'Ongoing', '50', '0.00'),
('20799262', 'LEC-002', 'SUP-001', 'AWS Malaysia', 'Ongoing', '51', '0.00'),
('20800273', 'LEC-005', 'SUP-001', 'PwC Malaysia', 'Ongoing', '52', '0.00'),
('20800646', 'LEC-005', 'SUP-002', 'EY Malaysia', 'Ongoing', '53', '0.00'),
('20801522', 'LEC-006', 'SUP-002', 'EY Malaysia', 'Ongoing', '54', '0.00'),
('20801771', 'LEC-002', 'SUP-004', 'Petronas', 'Ongoing', '55', '0.00'),
('20803923', 'LEC-006', 'SUP-003', 'Maybank', 'Ongoing', '57', '0.00'),
('20803979', 'LEC-006', 'SUP-004', 'CIMB Group', 'Ongoing', '58', '0.00'),
('20804611', 'LEC-002', 'SUP-003', 'Microsoft KL', 'Ongoing', '59', '0.00'),
('20690412', 'LEC-004', 'SUP-003', 'Celcom Digi', 'Ongoing', '6', '0.00'),
('20804966', 'LEC-006', 'SUP-004', 'CIMB Group', 'Ongoing', '60', '0.00'),
('20805230', 'LEC-006', 'SUP-005', 'RHB Bank', 'Ongoing', '62', '0.00'),
('20806299', 'LEC-006', 'SUP-001', 'Telekom Malaysia', 'Ongoing', '64', '87.50'),
('20806311', 'LEC-003', 'SUP-002', 'Cisco Systems', 'Ongoing', '65', '0.00'),
('20806316', 'LEC-002', 'SUP-001', 'AWS Malaysia', 'Ongoing', '66', '0.00'),
('20806377', 'LEC-006', 'SUP-001', 'Telekom Malaysia', 'Ongoing', '67', '0.00'),
('20806478', 'LEC-003', 'SUP-005', 'Maxis Berhad', 'Ongoing', '68', '0.00'),
('20806629', 'LEC-006', 'SUP-002', 'Hong Leong Bank', 'Ongoing', '69', '0.00'),
('20700617', 'LEC-002', 'SUP-002', 'Shopee MY', 'Ongoing', '7', '0.00'),
('20807243', 'LEC-006', 'SUP-002', 'Hong Leong Bank', 'Ongoing', '70', '0.00'),
('20807535', 'LEC-002', 'SUP-002', 'Shopee MY', 'Ongoing', '71', '0.00'),
('20807958', 'LEC-006', 'SUP-003', 'Tenaga Nasional', 'Ongoing', '72', '0.00'),
('20808713', 'LEC-006', 'SUP-003', 'Tenaga Nasional', 'Ongoing', '74', '0.00'),
('20808856', 'LEC-003', 'SUP-001', 'Dell Technologies', 'Ongoing', '75', '0.00'),
('20809859', 'LEC-006', 'SUP-004', 'Sime Darby', 'Ongoing', '76', '0.00'),
('20810450', 'LEC-002', 'SUP-001', 'Intel Malaysia', 'Ongoing', '77', '0.00'),
('20811803', 'LEC-006', 'SUP-004', 'Sime Darby', 'Ongoing', '78', '0.00'),
('20814150', 'LEC-006', 'SUP-005', 'Petronas Dagangan', 'Ongoing', '79', '0.00'),
('20701943', 'LEC-004', 'SUP-004', 'TM Malaysia', 'Ongoing', '8', '0.00'),
('20703441', 'LEC-004', 'SUP-004', 'TM Malaysia', 'Ongoing', '9', '0.00');

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
  `student_name` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `programme_id` varchar(20) DEFAULT NULL,
  `lecturer_id` varchar(50) DEFAULT NULL,
  `supervisor_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `profile_picture` varchar(255) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `student_name`, `email`, `programme_id`, `lecturer_id`, `supervisor_id`, `created_at`, `profile_picture`) VALUES
('20609637', 'Siyu Ge', 'hfysg5@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20675394', 'Kamila Mahenti', 'hfykm5@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20676720', 'Jialue Liao', 'hfyjl36@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20679455', 'Mohamed Hany Abdelmaksoud', 'hfyma18@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20683692', 'Youssef Mahran', 'hfyym6@nottingham.edu.my', 'PRG-2', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20690412', 'Mariah Azmir Faizal', 'efyma48@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20700617', 'Zhe Gao', 'hfyzg1@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20701943', 'Jun Zhe Yeong', 'hfyjy12@nottingham.edu.my', 'PRG-2', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20703441', 'Jia En Sai', 'hfyjs12@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20705302', 'Hin Joong Soo', 'hfyhs5@nottingham.edu.my', 'PRG-3', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20705375', 'Husam Feras Hosam Boshnaq', 'efyhh14@nottingham.edu.my', 'PRG-2', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20706107', 'Shunxi Yang', 'hfysy8@nottingham.edu.my', 'PRG-3', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20708501', 'You Sheng, Ciaran Ooi', 'hfyyo2@nottingham.edu.my', 'PRG-2', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20708827', 'Rachel Huey Yen Lee', 'hfyrl4@nottingham.edu.my', 'PRG-2', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20709091', 'Yun Xin Ng', 'efyyn8@nottingham.edu.my', 'PRG-2', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20709815', 'Lang Qin', 'efylq1@nottingham.edu.my', 'PRG-2', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20709820', 'Junta Suzuki', 'hfyjs13@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20710895', 'Jian Yun Tan', 'hfyjt17@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20711677', 'Wei Feng Hue', 'hfywh4@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20712533', 'Yoonjae Lee', 'hfyyl16@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20713102', 'Jun Jiet Jong', 'hfyjj4@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20713226', 'Li You Lee', 'hfyll10@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20713495', 'Lei Su', 'hfyls4@nottingham.edu.my', 'PRG-2', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20713862', 'Zikai Wang', 'hfyzw6@nottingham.edu.my', 'PRG-3', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20713959', 'Meenakshi Murugappan', 'hfymm21@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20713963', 'Seann Ryu Hearn Kwan', 'hfysk9@nottingham.edu.my', 'PRG-3', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20714943', 'Linjie Fu', 'hfylf1@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20715075', 'Jiexun Tang', 'efyjt32@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20715078', 'Muhammad Faysal Md Mijanur Rahman', 'hfymm22@nottingham.edu.my', 'PRG-3', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20716333', 'Grace Shuang Yee', 'hfygy1@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20717493', 'Kang Wei Chan', 'hfykc15@nottingham.edu.my', 'PRG-3', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20718823', 'Menghuan Wu', 'hcymw2@nottingham.edu.my', 'PRG-3', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20722321', 'Clarissa Jia Yi Kiew', 'hfyck5@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20723833', 'Ian Yu Sheng Yap', 'hfyiy1@nottingham.edu.my', 'PRG-2', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20723858', 'Kiara Leshan Kwo', 'hfykk7@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20749006', 'Chenhaoxi Zhou', 'hcycz1@nottingham.edu.my', 'PRG-3', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20780335', 'Fathima Sakinah Dil Fairaz', 'hcyfd1@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20791953', 'Tianli Chen', 'hcytc2@nottingham.edu.my', 'PRG-2', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20792685', 'Mingcai Ling', 'hcyml3@nottingham.edu.my', 'PRG-3', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20793790', 'Wu Han Hue', 'hcywh5@nottingham.edu.my', 'PRG-2', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20793978', 'Terence Kian Seng Lee', 'hcytl3@nottingham.edu.my', 'PRG-3', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20796056', 'Mingxu Liu', 'hcyml4@nottingham.edu.my', 'PRG-3', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20796507', 'Yifei Xie', 'hcyyx2@nottingham.edu.my', 'PRG-2', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20797468', 'Guangbing Liu', 'hcygl2@nottingham.edu.my', 'PRG-2', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20797569', 'Lang Chen', 'hcylc3@nottingham.edu.my', 'PRG-2', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20799262', 'Nanxi Zhang', 'hcynz1@nottingham.edu.my', 'PRG-2', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20800273', 'Jun Bin Wong', 'hcyjw4@nottingham.edu.my', 'PRG-2', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20800646', 'Shiyu Cao', 'hcysc4@nottingham.edu.my', 'PRG-3', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20801522', 'Hao Yin Ng', 'edyhn2@nottingham.edu.my', 'PRG-3', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20801771', 'Huda Amin', 'hcyha1@nottingham.edu.my', 'PRG-2', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20803923', 'Chenyu Li', 'hcycl7@nottingham.edu.my', 'PRG-3', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20803979', 'Zhiling Tang', 'hcyzt5@nottingham.edu.my', 'PRG-2', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20804611', 'Sixuan Wang', 'hcysw2@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20804966', 'Samay Rayapuram', 'hcysr4@nottingham.edu.my', 'PRG-2', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20805230', 'Ying Qi Tan', 'hcyyt3@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20806299', 'Daniyal Khan', 'hcydk2@nottingham.edu.my', 'PRG-3', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20806311', 'Yangting Zhuang', 'hcyyz2@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20806316', 'Hanrui Zou', 'hcyhz3@nottingham.edu.my', 'PRG-2', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20806377', 'Khalid Mohamad Shaker', 'hcykm1@nottingham.edu.my', 'PRG-3', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20806478', 'Jiachang Ying', 'hcyjy6@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20806629', 'Qianqian Yang', 'hcyqy1@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20807243', 'Jacques Milton', 'hcyjm2@nottingham.edu.my', 'PRG-2', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20807535', 'Richard Erkhov', 'hcyre1@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20807958', 'Xitai Li', 'hcyxl7@nottingham.edu.my', 'PRG-3', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20808713', 'Lei Zhu', 'hcylz1@nottingham.edu.my', 'PRG-3', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20808856', 'Muhammad Rafay Shahid', 'hcyms6@nottingham.edu.my', 'PRG-2', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20809859', 'Khizer Asim', 'hcyka4@nottingham.edu.my', 'PRG-3', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20810450', 'Yuqing He', 'hcyyh3@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20811803', 'Shi Qi Koh', 'hcysk3@nottingham.edu.my', 'PRG-1', NULL, NULL, '2026-03-02 06:52:54', 'default.png'),
('20814150', 'Qoid Rafif Mohd Fadly', 'hcyqm1@nottingham.edu.my', 'PRG-3', NULL, NULL, '2026-03-02 06:52:54', 'default.png');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` varchar(20) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('Admin','Lecturer','Supervisor','Student') NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `otp_code` varchar(6) DEFAULT NULL,
  `otp_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `full_name`, `role`, `profile_picture`, `last_login`, `otp_code`, `otp_expires`) VALUES
('ADM-1', 'admin1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Aris Brown', 'Admin', NULL, NULL, NULL, NULL),
('LEC-002', 'lec1', '$2y$10$6O2lRZYQQIGjcZBEPVXCquI9r4dcCg1k3AD1B8.EVn5r9x.BfuzDu', 'Dr. Robert Low', 'Lecturer', NULL, NULL, '276932', '2026-04-25 14:02:56'),
('LEC-003', 'lec2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ms. Sarah Lim', 'Lecturer', NULL, NULL, NULL, NULL),
('LEC-004', 'lec3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mr. Kevin Tan', 'Lecturer', NULL, NULL, NULL, NULL),
('LEC-005', 'lec67', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Jane Smith', 'Lecturer', 'assessor_LEC005_1777184509.jpg', NULL, NULL, NULL),
('LEC-006', 'lec5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Prof. Wong Kar', 'Lecturer', NULL, NULL, NULL, NULL),
('SUP-001', 'sup1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mr. Raj Kumar', 'Supervisor', NULL, NULL, NULL, NULL),
('SUP-002', 'sup2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ms. Priya Nair', 'Supervisor', NULL, NULL, NULL, NULL),
('SUP-003', 'sup3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mr. Ahmad Zaki', 'Supervisor', NULL, NULL, NULL, NULL),
('SUP-004', 'sup4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ms. Chen Li Hua', 'Supervisor', NULL, NULL, NULL, NULL),
('SUP-005', 'sup5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mr. David Yong', 'Supervisor', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure for view `final_results`
--
DROP TABLE IF EXISTS `final_results`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `final_results`  AS SELECT `i`.`internship_id` AS `internship_id`, `i`.`student_id` AS `student_id`, `s`.`student_name` AS `student_name`, `s`.`email` AS `email`, `p`.`programme_name` AS `programme_name`, `i`.`company_name` AS `company_name`, `i`.`internship_status` AS `internship_status`, `lu`.`full_name` AS `lecturer_name`, `su`.`full_name` AS `supervisor_name`, `lec`.`total_mark` AS `lecturer_total`, `sup`.`total_mark` AS `supervisor_total`, round(((coalesce(`lec`.`total_mark`,0) + coalesce(`sup`.`total_mark`,0)) / nullif(((case when (`lec`.`total_mark` is not null) then 1 else 0 end) + (case when (`sup`.`total_mark` is not null) then 1 else 0 end)),0)),2) AS `final_score` FROM ((((((`internships` `i` join `students` `s` on((`s`.`student_id` = `i`.`student_id`))) join `programmes` `p` on((`p`.`programme_id` = `s`.`programme_id`))) left join `users` `lu` on((`lu`.`user_id` = `i`.`lecturer_id`))) left join `users` `su` on((`su`.`user_id` = `i`.`supervisor_id`))) left join `assessments` `lec` on(((`lec`.`internship_id` = `i`.`internship_id`) and (`lec`.`assessor_type` = 'Lecturer')))) left join `assessments` `sup` on(((`sup`.`internship_id` = `i`.`internship_id`) and (`sup`.`assessor_type` = 'Supervisor'))))  ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assessments`
--
ALTER TABLE `assessments`
  ADD PRIMARY KEY (`assessment_id`),
  ADD UNIQUE KEY `uq_internship_assessor_type` (`internship_id`,`assessor_type`),
  ADD KEY `fk_assessment_assessor` (`assessor_id`);

--
-- Indexes for table `internships`
--
ALTER TABLE `internships`
  ADD PRIMARY KEY (`internship_id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD KEY `fk_internship_lecturer` (`lecturer_id`),
  ADD KEY `fk_internship_supervisor` (`supervisor_id`);

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
  ADD KEY `fk_student_programme` (`programme_id`);

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
  ADD CONSTRAINT `fk_assessment_assessor` FOREIGN KEY (`assessor_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_assessment_internship` FOREIGN KEY (`internship_id`) REFERENCES `internships` (`internship_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `internships`
--
ALTER TABLE `internships`
  ADD CONSTRAINT `fk_internship_lecturer` FOREIGN KEY (`lecturer_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `fk_internship_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_internship_supervisor` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_student_programme` FOREIGN KEY (`programme_id`) REFERENCES `programmes` (`programme_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
