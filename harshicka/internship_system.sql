-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 24, 2026 at 07:46 AM
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
  `internship_id` int(11) DEFAULT NULL,
  `assessor_id` varchar(20) DEFAULT NULL,
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

--
-- Dumping data for table `assessments`
--

INSERT INTO `assessments` (`assessment_id`, `internship_id`, `assessor_id`, `assessor_type`, `score_tasks`, `score_safety`, `score_theory`, `score_presentation`, `score_clarity`, `score_learning`, `score_project_mgmt`, `score_time_mgmt`, `total_mark`, `comments`, `date_evaluated`) VALUES
('ASS-L-001', NULL, 'LEC-002', 'Lecturer', 80, 75, 78, 82, 76, 80, 79, 81, 79.20, 'Student demonstrates good understanding of workplace requirements and strong project management.', '2026-04-18 09:00:00'),
('ASS-L-003', NULL, 'LEC-002', 'Lecturer', 90, 88, 92, 91, 89, 93, 90, 88, 90.20, 'Outstanding performance across all criteria. Clarissa is a model intern for this cohort.', '2026-04-18 10:30:00'),
('ASS-L-028', NULL, 'LEC-003', 'Lecturer', 85, 83, 87, 86, 84, 88, 85, 83, 85.20, 'Shunxi demonstrated excellent written communication and strong technical skills throughout.', '2026-04-15 09:00:00'),
('ASS-S-007', NULL, 'SUP-004', 'Supervisor', 77, 80, 75, 78, 76, 79, 78, 77, 77.60, 'Good performance with consistent attendance and a proactive attitude on the floor.', '2026-04-19 08:30:00'),
('ASS-S-028', NULL, 'SUP-004', 'Supervisor', 84, 82, 86, 88, 85, 87, 84, 82, 84.85, 'A reliable and self-motivated intern. Completed all assigned tasks ahead of schedule.', '2026-04-16 10:00:00');

-- --------------------------------------------------------

--
-- Stand-in structure for view `final_results`
-- (See below for the actual view)
--
CREATE TABLE `final_results` (
`internship_id` int(11)
,`student_id` varchar(20)
,`student_name` varchar(100)
,`email` varchar(100)
,`programme_name` varchar(100)
,`company_name` varchar(150)
,`internship_status` enum('Ongoing','Completed','Evaluated')
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
  `internship_status` enum('Ongoing','Completed','Evaluated') DEFAULT 'Ongoing',
  `internship_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `internships`
--

INSERT INTO `internships` (`student_id`, `lecturer_id`, `supervisor_id`, `company_name`, `internship_status`, `internship_id`) VALUES
('20609637', 'LEC-003', 'SUP-001', 'Huawei Malaysia', 'Ongoing', 1),
('20675394', 'LEC-003', 'SUP-001', 'Huawei Malaysia', 'Ongoing', 2),
('20676720', 'LEC-004', 'SUP-002', 'Axiata Group', 'Ongoing', 3),
('20679455', 'LEC-004', 'SUP-002', 'Axiata Group', 'Ongoing', 4),
('20683692', 'LEC-004', 'SUP-003', 'Celcom Digi', 'Ongoing', 5),
('20690412', 'LEC-004', 'SUP-003', 'Celcom Digi', 'Ongoing', 6),
('20700617', 'LEC-002', 'SUP-002', 'Shopee MY', 'Ongoing', 7),
('20701943', 'LEC-004', 'SUP-004', 'TM Malaysia', 'Ongoing', 8),
('20703441', 'LEC-004', 'SUP-004', 'TM Malaysia', 'Ongoing', 9),
('20705302', 'LEC-004', 'SUP-005', 'Mimos Berhad', 'Ongoing', 10),
('20705375', 'LEC-004', 'SUP-005', 'Mimos Berhad', 'Ongoing', 11),
('20706107', 'LEC-003', 'SUP-004', 'Apple Malaysia', 'Evaluated', 12),
('20708501', 'LEC-003', 'SUP-001', 'Dell Technologies', 'Ongoing', 13),
('20708827', 'LEC-004', 'SUP-001', 'Ericsson Malaysia', 'Ongoing', 14),
('20709091', 'LEC-002', 'SUP-005', 'Grab Holdings', 'Ongoing', 15),
('20709815', 'LEC-002', 'SUP-005', 'Grab Holdings', 'Ongoing', 16),
('20709820', 'LEC-002', 'SUP-002', 'Google MY', 'Ongoing', 17),
('20710895', 'LEC-004', 'SUP-001', 'Ericsson Malaysia', 'Ongoing', 18),
('20711344', 'LEC-004', 'SUP-002', 'Motorola Solutions', 'Ongoing', 19),
('20711677', 'LEC-004', 'SUP-002', 'Motorola Solutions', 'Ongoing', 20),
('20712533', 'LEC-004', 'SUP-003', 'Lenovo Malaysia', 'Ongoing', 21),
('20713102', 'LEC-002', 'SUP-001', 'Intel Malaysia', 'Ongoing', 22),
('20713226', 'LEC-002', 'SUP-003', 'Tesla Services', 'Ongoing', 23),
('20713495', 'LEC-003', 'SUP-003', 'Samsung Electronics', 'Ongoing', 24),
('20713862', 'LEC-004', 'SUP-003', 'Lenovo Malaysia', 'Ongoing', 25),
('20713959', 'LEC-004', 'SUP-004', 'NTT Malaysia', 'Ongoing', 26),
('20713963', 'LEC-004', 'SUP-004', 'NTT Malaysia', 'Ongoing', 27),
('20714943', 'LEC-005', 'SUP-005', 'Bosch Malaysia', 'Ongoing', 28),
('20715075', 'LEC-005', 'SUP-005', 'Bosch Malaysia', 'Ongoing', 29),
('20715078', 'LEC-003', 'SUP-004', 'IBM Malaysia', 'Ongoing', 30),
('20716333', 'LEC-003', 'SUP-005', 'Maxis Berhad', 'Ongoing', 31),
('20717493', 'LEC-005', 'SUP-001', 'Siemens Malaysia', 'Ongoing', 32),
('20718823', 'LEC-003', 'SUP-004', 'Apple Malaysia', 'Ongoing', 33),
('20722321', 'LEC-002', 'SUP-002', 'Google MY', 'Ongoing', 34),
('20723833', 'LEC-005', 'SUP-001', 'Siemens Malaysia', 'Ongoing', 35),
('20723858', 'LEC-002', 'SUP-003', 'Tesla Services', 'Ongoing', 36),
('20749006', 'LEC-005', 'SUP-002', 'HP Malaysia', 'Ongoing', 37),
('20780335', 'LEC-005', 'SUP-002', 'HP Malaysia', 'Ongoing', 38),
('20782788', 'LEC-005', 'SUP-003', 'Accenture MY', 'Ongoing', 39),
('20791953', 'LEC-005', 'SUP-003', 'Accenture MY', 'Ongoing', 40),
('20791965', 'LEC-003', 'SUP-002', 'Cisco Systems', 'Ongoing', 41),
('20792685', 'LEC-005', 'SUP-004', 'Deloitte Malaysia', 'Ongoing', 42),
('20793088', 'LEC-005', 'SUP-004', 'Deloitte Malaysia', 'Ongoing', 43),
('20793790', 'LEC-005', 'SUP-005', 'KPMG Malaysia', 'Ongoing', 44),
('20793978', 'LEC-003', 'SUP-004', 'IBM Malaysia', 'Ongoing', 45),
('20794457', 'LEC-005', 'SUP-005', 'KPMG Malaysia', 'Ongoing', 46),
('20796056', 'LEC-003', 'SUP-005', 'Oracle Corp', 'Ongoing', 47),
('20796507', 'LEC-005', 'SUP-001', 'PwC Malaysia', 'Ongoing', 48),
('20797468', 'LEC-002', 'SUP-004', 'Petronas', 'Ongoing', 49),
('20797569', 'LEC-003', 'SUP-003', 'Samsung Electronics', 'Ongoing', 50),
('20799262', 'LEC-002', 'SUP-001', 'AWS Malaysia', 'Ongoing', 51),
('20800273', 'LEC-005', 'SUP-001', 'PwC Malaysia', 'Ongoing', 52),
('20800646', 'LEC-005', 'SUP-002', 'EY Malaysia', 'Ongoing', 53),
('20801522', 'LEC-006', 'SUP-002', 'EY Malaysia', 'Ongoing', 54),
('20801771', 'LEC-002', 'SUP-004', 'Petronas', 'Ongoing', 55),
('20802060', 'LEC-006', 'SUP-003', 'Maybank', 'Ongoing', 56),
('20803923', 'LEC-006', 'SUP-003', 'Maybank', 'Ongoing', 57),
('20803979', 'LEC-006', 'SUP-004', 'CIMB Group', 'Ongoing', 58),
('20804611', 'LEC-002', 'SUP-003', 'Microsoft KL', 'Ongoing', 59),
('20804966', 'LEC-006', 'SUP-004', 'CIMB Group', 'Ongoing', 60),
('20805040', 'LEC-006', 'SUP-005', 'RHB Bank', 'Ongoing', 61),
('20805230', 'LEC-006', 'SUP-005', 'RHB Bank', 'Ongoing', 62),
('20805281', 'LEC-003', 'SUP-005', 'Oracle Corp', 'Ongoing', 63),
('20806299', 'LEC-006', 'SUP-001', 'Telekom Malaysia', 'Ongoing', 64),
('20806311', 'LEC-003', 'SUP-002', 'Cisco Systems', 'Ongoing', 65),
('20806316', 'LEC-002', 'SUP-001', 'AWS Malaysia', 'Ongoing', 66),
('20806377', 'LEC-006', 'SUP-001', 'Telekom Malaysia', 'Ongoing', 67),
('20806478', 'LEC-003', 'SUP-005', 'Maxis Berhad', 'Ongoing', 68),
('20806629', 'LEC-006', 'SUP-002', 'Hong Leong Bank', 'Ongoing', 69),
('20807243', 'LEC-006', 'SUP-002', 'Hong Leong Bank', 'Ongoing', 70),
('20807535', 'LEC-002', 'SUP-002', 'Shopee MY', 'Ongoing', 71),
('20807958', 'LEC-006', 'SUP-003', 'Tenaga Nasional', 'Ongoing', 72),
('20808475', 'LEC-002', 'SUP-003', 'Microsoft KL', 'Ongoing', 73),
('20808713', 'LEC-006', 'SUP-003', 'Tenaga Nasional', 'Ongoing', 74),
('20808856', 'LEC-003', 'SUP-001', 'Dell Technologies', 'Ongoing', 75),
('20809859', 'LEC-006', 'SUP-004', 'Sime Darby', 'Ongoing', 76),
('20810450', 'LEC-002', 'SUP-001', 'Intel Malaysia', 'Ongoing', 77),
('20811803', 'LEC-006', 'SUP-004', 'Sime Darby', 'Ongoing', 78),
('20814150', 'LEC-006', 'SUP-005', 'Petronas Dagangan', 'Ongoing', 79);

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
  `email` varchar(100) NOT NULL,
  `programme_id` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `student_name`, `email`, `programme_id`, `created_at`) VALUES
('20609637', 'Siyu Ge', 'hfysg5@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20675394', 'Kamila Mahenti', 'hfykm5@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20676720', 'Jialue Liao', 'hfyjl36@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20679455', 'Mohamed Hany Abdelmaksoud', 'hfyma18@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20683692', 'Youssef Mahran', 'hfyym6@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54'),
('20690412', 'Mariah Azmir Faizal', 'efyma48@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20700617', 'Zhe Gao', 'hfyzg1@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20701943', 'Jun Zhe Yeong', 'hfyjy12@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54'),
('20703441', 'Jia En Sai', 'hfyjs12@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20705302', 'Hin Joong Soo', 'hfyhs5@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54'),
('20705375', 'Husam Feras Hosam Boshnaq', 'efyhh14@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54'),
('20706107', 'Shunxi Yang', 'hfysy8@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54'),
('20708501', 'You Sheng, Ciaran Ooi', 'hfyyo2@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54'),
('20708827', 'Rachel Huey Yen Lee', 'hfyrl4@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54'),
('20709091', 'Yun Xin Ng', 'efyyn8@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54'),
('20709815', 'Lang Qin', 'efylq1@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54'),
('20709820', 'Junta Suzuki', 'hfyjs13@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20710895', 'Jian Yun Tan', 'hfyjt17@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20711344', 'Aiko Yi Rou Wong', 'hfyaw3@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20711677', 'Wei Feng Hue', 'hfywh4@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20712533', 'Yoonjae Lee', 'hfyyl16@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20713102', 'Jun Jiet Jong', 'hfyjj4@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20713226', 'Li You Lee', 'hfyll10@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20713495', 'Lei Su', 'hfyls4@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54'),
('20713862', 'Zikai Wang', 'hfyzw6@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54'),
('20713959', 'Meenakshi Murugappan', 'hfymm21@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20713963', 'Seann Ryu Hearn Kwan', 'hfysk9@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54'),
('20714943', 'Linjie Fu', 'hfylf1@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20715075', 'Jiexun Tang', 'efyjt32@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20715078', 'Muhammad Faysal Md Mijanur Rahman', 'hfymm22@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54'),
('20716333', 'Grace Shuang Yee', 'hfygy1@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20717493', 'Kang Wei Chan', 'hfykc15@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54'),
('20718823', 'Menghuan Wu', 'hcymw2@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54'),
('20722321', 'Clarissa Jia Yi Kiew', 'hfyck5@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20723833', 'Ian Yu Sheng Yap', 'hfyiy1@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54'),
('20723858', 'Kiara Leshan Kwo', 'hfykk7@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20749006', 'Chenhaoxi Zhou', 'hcycz1@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54'),
('20780335', 'Fathima Sakinah Dil Fairaz', 'hcyfd1@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20782788', 'Ruining Ding', 'hcyrd2@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54'),
('20791953', 'Tianli Chen', 'hcytc2@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54'),
('20791965', 'Chaoyuan Zhang', 'hcycz2@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20792685', 'Mingcai Ling', 'hcyml3@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54'),
('20793088', 'Changhui Deng', 'hcycd1@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54'),
('20793790', 'Wu Han Hue', 'hcywh5@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54'),
('20793978', 'Terence Kian Seng Lee', 'hcytl3@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54'),
('20794457', 'Ali Ibrahim Alkomey', 'hcyai1@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54'),
('20796056', 'Mingxu Liu', 'hcyml4@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54'),
('20796507', 'Yifei Xie', 'hcyyx2@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54'),
('20797468', 'Guangbing Liu', 'hcygl2@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54'),
('20797569', 'Lang Chen', 'hcylc3@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54'),
('20799262', 'Nanxi Zhang', 'hcynz1@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54'),
('20800273', 'Jun Bin Wong', 'hcyjw4@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54'),
('20800646', 'Shiyu Cao', 'hcysc4@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54'),
('20801522', 'Hao Yin Ng', 'edyhn2@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54'),
('20801771', 'Huda Amin', 'hcyha1@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54'),
('20802060', 'Atsuhiro Tsukata', 'hcyat2@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20803923', 'Chenyu Li', 'hcycl7@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54'),
('20803979', 'Zhiling Tang', 'hcyzt5@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54'),
('20804611', 'Sixuan Wang', 'hcysw2@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20804966', 'Samay Rayapuram', 'hcysr4@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54'),
('20805040', 'Aazzu Adam Khalid', 'hcyaa11@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54'),
('20805230', 'Ying Qi Tan', 'hcyyt3@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20805281', 'Chen Yi Lee', 'hcycl8@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54'),
('20806299', 'Daniyal Khan', 'hcydk2@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54'),
('20806311', 'Yangting Zhuang', 'hcyyz2@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20806316', 'Hanrui Zou', 'hcyhz3@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54'),
('20806377', 'Khalid Mohamad Shaker', 'hcykm1@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54'),
('20806478', 'Jiachang Ying', 'hcyjy6@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20806629', 'Qianqian Yang', 'hcyqy1@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20807243', 'Jacques Milton', 'hcyjm2@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54'),
('20807535', 'Richard Erkhov', 'hcyre1@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20807958', 'Xitai Li', 'hcyxl7@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54'),
('20808475', 'Canshuo Yu', 'hcycy1@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20808713', 'Lei Zhu', 'hcylz1@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54'),
('20808856', 'Muhammad Rafay Shahid', 'hcyms6@nottingham.edu.my', 'PRG-2', '2026-03-02 06:52:54'),
('20809859', 'Khizer Asim', 'hcyka4@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54'),
('20810450', 'Yuqing He', 'hcyyh3@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20811803', 'Shi Qi Koh', 'hcysk3@nottingham.edu.my', 'PRG-1', '2026-03-02 06:52:54'),
('20814150', 'Qoid Rafif Mohd Fadly', 'hcyqm1@nottingham.edu.my', 'PRG-3', '2026-03-02 06:52:54');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` varchar(20) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('Admin','Lecturer','Supervisor') NOT NULL DEFAULT 'Lecturer',
  `last_login` datetime DEFAULT NULL,
  `otp_code` varchar(6) DEFAULT NULL,
  `otp_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `full_name`, `role`, `last_login`, `otp_code`, `otp_expires`) VALUES
('ADM-1', 'admin1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Aris Brown', 'Admin', NULL, NULL, NULL),
('LEC-002', 'lec1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Robert Low', 'Lecturer', NULL, NULL, NULL),
('LEC-003', 'lec2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ms. Sarah Lim', 'Lecturer', NULL, NULL, NULL),
('LEC-004', 'lec3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mr. Kevin Tan', 'Lecturer', NULL, NULL, NULL),
('LEC-005', 'lec4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Jane Smith', 'Lecturer', NULL, NULL, NULL),
('LEC-006', 'lec5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Prof. Wong Kar', 'Lecturer', NULL, NULL, NULL),
('SUP-001', 'sup1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mr. Raj Kumar', 'Supervisor', NULL, NULL, NULL),
('SUP-002', 'sup2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ms. Priya Nair', 'Supervisor', NULL, NULL, NULL),
('SUP-003', 'sup3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mr. Ahmad Zaki', 'Supervisor', NULL, NULL, NULL),
('SUP-004', 'sup4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ms. Chen Li Hua', 'Supervisor', NULL, NULL, NULL),
('SUP-005', 'sup5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mr. David Yong', 'Supervisor', NULL, NULL, NULL);

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `internships`
--
ALTER TABLE `internships`
  MODIFY `internship_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

-- --------------------------------------------------------

--
-- Structure for view `final_results`
--
DROP TABLE IF EXISTS `final_results`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `final_results`  AS SELECT `i`.`internship_id` AS `internship_id`, `i`.`student_id` AS `student_id`, `s`.`student_name` AS `student_name`, `s`.`email` AS `email`, `p`.`programme_name` AS `programme_name`, `i`.`company_name` AS `company_name`, `i`.`internship_status` AS `internship_status`, `lu`.`full_name` AS `lecturer_name`, `su`.`full_name` AS `supervisor_name`, `lec`.`total_mark` AS `lecturer_total`, `sup`.`total_mark` AS `supervisor_total`, round(((coalesce(`lec`.`total_mark`,0) + coalesce(`sup`.`total_mark`,0)) / nullif(((case when (`lec`.`total_mark` is not null) then 1 else 0 end) + (case when (`sup`.`total_mark` is not null) then 1 else 0 end)),0)),2) AS `final_score` FROM ((((((`internships` `i` join `students` `s` on((`s`.`student_id` = `i`.`student_id`))) join `programmes` `p` on((`p`.`programme_id` = `s`.`programme_id`))) left join `users` `lu` on((`lu`.`user_id` = `i`.`lecturer_id`))) left join `users` `su` on((`su`.`user_id` = `i`.`supervisor_id`))) left join `assessments` `lec` on(((`lec`.`internship_id` = `i`.`internship_id`) and (`lec`.`assessor_type` = 'Lecturer')))) left join `assessments` `sup` on(((`sup`.`internship_id` = `i`.`internship_id`) and (`sup`.`assessor_type` = 'Supervisor')))) ;

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
