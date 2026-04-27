-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 27, 2026 at 08:51 AM
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

--
-- Dumping data for table `assessments`
--

INSERT INTO `assessments` (`assessment_id`, `internship_id`, `assessor_id`, `assessment_type`, `assessor_type`, `score_tasks`, `score_safety`, `score_theory`, `score_presentation`, `score_clarity`, `score_learning`, `score_project_mgmt`, `score_time_mgmt`, `total_mark`, `comments`, `date_evaluated`) VALUES
('ACAD-C952871C', '34', 'LEC-002', 'Academic', 'Lecturer', 10, 10, 8, 14, 9, 12, 10, 5, 78.00, 'this should show an error since the value exceeds the max', '2026-04-27 08:37:17'),
('ASS-L-001', '1', 'LEC-003', 'Academic', 'Lecturer', 78, 80, 75, 82, 77, 80, 79, 81, 79.30, 'Student showed strong initiative and completed tasks efficiently throughout the internship period.', '2026-04-05 01:00:00'),
('ASS-L-002', '2', 'LEC-003', 'Academic', 'Lecturer', 85, 83, 88, 86, 84, 87, 85, 83, 85.15, 'Excellent performance across all criteria. Demonstrates outstanding technical and communication skills.', '2026-04-06 01:09:00'),
('ASS-L-003', '3', 'LEC-004', 'Academic', 'Lecturer', 70, 72, 68, 74, 71, 73, 70, 72, 71.45, 'Satisfactory performance. Student needs to improve time management and theoretical application.', '2026-04-07 01:18:00'),
('ASS-L-004', '4', 'LEC-004', 'Academic', 'Lecturer', 90, 88, 92, 91, 89, 93, 90, 88, 90.20, 'Outstanding intern. Consistently exceeded expectations in all assessment areas.', '2026-04-08 01:27:00'),
('ASS-L-005', '5', 'LEC-004', 'Academic', 'Lecturer', 65, 68, 64, 70, 66, 69, 67, 65, 66.95, 'Below average performance. Student struggled with project deliverables and safety compliance.', '2026-04-09 01:36:00'),
('ASS-L-006', '6', 'LEC-004', 'Academic', 'Lecturer', 82, 80, 79, 84, 81, 83, 82, 80, 81.55, 'Good performance with consistent attendance and proactive attitude in all assigned tasks.', '2026-04-10 01:45:00'),
('ASS-L-007', '7', 'LEC-002', 'Academic', 'Lecturer', 76, 75, 77, 78, 74, 79, 76, 77, 76.70, 'Solid understanding of workplace requirements. Written report could be improved in structure.', '2026-04-11 01:54:00'),
('ASS-L-008', '8', 'LEC-004', 'Academic', 'Lecturer', 88, 85, 87, 89, 86, 90, 88, 87, 87.70, 'Exceptional work ethic and technical contribution. A model intern for the cohort.', '2026-04-12 02:03:00'),
('ASS-L-009', '9', 'LEC-004', 'Academic', 'Lecturer', 73, 74, 71, 76, 72, 75, 73, 74, 73.70, 'Adequate performance overall. Needs improvement in connectivity of theoretical knowledge.', '2026-04-13 02:12:00'),
('ASS-L-010', '10', 'LEC-004', 'Academic', 'Lecturer', 92, 90, 91, 93, 89, 94, 92, 91, 91.70, 'Top-performing student with remarkable clarity of communication and project management skills.', '2026-04-14 02:21:00'),
('ASS-L-011', '11', 'LEC-004', 'Academic', 'Lecturer', 69, 70, 67, 72, 68, 71, 70, 69, 69.70, 'Weak performance in several areas. Recommend additional coaching on workplace safety practices.', '2026-04-15 02:30:00'),
('ASS-L-012', '12', 'LEC-003', 'Academic', 'Lecturer', 80, 82, 79, 83, 81, 84, 80, 82, 81.55, 'Student demonstrated good teamwork and met all project milestones consistently on time.', '2026-04-16 02:39:00'),
('ASS-L-013', '13', 'LEC-003', 'Academic', 'Lecturer', 77, 76, 75, 79, 78, 80, 77, 78, 77.70, 'Reliable and consistent performance. Written documentation was well-structured and clear.', '2026-04-17 02:48:00'),
('ASS-L-014', '14', 'LEC-004', 'Academic', 'Lecturer', 86, 84, 85, 88, 83, 87, 85, 86, 85.70, 'Strong technical skills with excellent project management ability demonstrated throughout.', '2026-04-18 02:57:00'),
('ASS-L-015', '15', 'LEC-002', 'Academic', 'Lecturer', 71, 73, 70, 75, 72, 74, 71, 73, 72.55, 'Student showed improvement over the internship period but still needs work on time management.', '2026-04-19 03:06:00'),
('ASS-L-016', '16', 'LEC-002', 'Academic', 'Lecturer', 83, 81, 84, 85, 82, 86, 84, 83, 83.70, 'Very good overall. Presentation of report was particularly impressive and well-referenced.', '2026-04-20 03:15:00'),
('ASS-L-017', '17', 'LEC-002', 'Academic', 'Lecturer', 68, 69, 66, 71, 67, 70, 69, 68, 68.70, 'Student requires more self-direction. Performance was below expectations in several key areas.', '2026-04-21 03:24:00'),
('ASS-L-018', '18', 'LEC-004', 'Academic', 'Lecturer', 79, 78, 80, 81, 77, 82, 80, 79, 79.70, 'Good grasp of theoretical knowledge applied effectively to real-world industry tasks.', '2026-04-22 03:33:00'),
('ASS-L-020', '20', 'LEC-004', 'Academic', 'Lecturer', 87, 85, 88, 89, 86, 90, 87, 88, 87.70, 'Highly competent intern who demonstrated leadership and initiative in all assigned tasks.', '2026-04-23 03:42:00'),
('ASS-L-021', '21', 'LEC-004', 'Academic', 'Lecturer', 74, 75, 73, 77, 76, 78, 74, 75, 75.40, 'Average performance. Student participated adequately but lacked depth in technical areas.', '2026-04-24 03:51:00'),
('ASS-L-022', '22', 'LEC-002', 'Academic', 'Lecturer', 91, 89, 90, 92, 88, 93, 91, 90, 90.70, 'Exceptional in every criterion. One of the strongest interns assessed this evaluation cycle.', '2026-04-25 04:00:00'),
('ASS-L-023', '23', 'LEC-002', 'Academic', 'Lecturer', 66, 67, 65, 69, 66, 68, 67, 66, 66.90, 'Student showed minimal engagement with lifelong learning activities during the placement.', '2026-04-25 04:09:00'),
('ASS-L-024', '24', 'LEC-003', 'Academic', 'Lecturer', 84, 82, 83, 86, 81, 85, 84, 83, 83.70, 'Strong performer with clear communication and well-managed project timelines throughout.', '2026-04-25 04:18:00'),
('ASS-L-025', '25', 'LEC-004', 'Academic', 'Lecturer', 72, 74, 71, 76, 73, 75, 72, 74, 73.55, 'Decent performance but report presentation and clarity of language could be significantly improved.', '2026-04-25 04:27:00'),
('ASS-L-026', '26', 'LEC-004', 'Academic', 'Lecturer', 89, 87, 88, 90, 86, 91, 89, 88, 88.70, 'Very capable intern. Demonstrated excellent time management and project delivery skills.', '2026-04-25 04:36:00'),
('ASS-S-007', '7', 'SUP-002', 'Industry', 'Supervisor', 74, 76, 73, 79, 75, 78, 76, 77, 76.30, 'Intern showed satisfactory workplace conduct but struggled with technical aspects at times.', '2026-04-12 01:00:00'),
('ASS-S-015', '15', 'SUP-005', 'Industry', 'Supervisor', 70, 72, 69, 74, 71, 73, 70, 72, 71.55, 'Student consistently needed prompting. Time management on industry tasks was below standard.', '2026-04-20 01:00:00'),
('ASS-S-022', '22', 'SUP-001', 'Industry', 'Supervisor', 89, 88, 91, 90, 87, 92, 89, 88, 89.35, 'One of the strongest interns we have had. Delivered all tasks ahead of schedule with precision.', '2026-04-25 01:00:00'),
('ASS-S-027', '27', 'SUP-004', 'Industry', 'Supervisor', 76, 78, 74, 80, 77, 79, 76, 78, 77.45, 'Good cultural fit within the team. Completed assignments reliably and communicated well.', '2026-04-05 01:30:00'),
('ASS-S-028', '28', 'SUP-005', 'Industry', 'Supervisor', 84, 82, 86, 85, 83, 87, 84, 82, 84.20, 'Demonstrated excellent industry awareness and applied academic knowledge effectively on-site.', '2026-04-06 01:30:00'),
('ASS-S-029', '29', 'SUP-005', 'Industry', 'Supervisor', 68, 70, 66, 72, 69, 71, 68, 70, 69.45, 'Student required additional supervision. Did not always follow health and safety protocols.', '2026-04-07 01:30:00'),
('ASS-S-030', '30', 'SUP-004', 'Industry', 'Supervisor', 80, 79, 78, 82, 80, 83, 81, 80, 80.60, 'Performed well in a fast-paced environment. Report writing and documentation were commendable.', '2026-04-08 01:30:00'),
('ASS-S-031', '31', 'SUP-005', 'Industry', 'Supervisor', 72, 74, 71, 76, 73, 75, 73, 74, 73.70, 'Intern showed a willingness to learn but lacked initiative in taking on additional responsibilities.', '2026-04-09 01:30:00'),
('ASS-S-032', '32', 'SUP-001', 'Industry', 'Supervisor', 88, 86, 89, 90, 87, 91, 88, 89, 88.70, 'Outstanding performance from day one. A natural fit for the industry with excellent soft skills.', '2026-04-10 01:30:00'),
('ASS-S-033', '33', 'SUP-004', 'Industry', 'Supervisor', 77, 76, 75, 79, 78, 80, 77, 78, 77.70, 'Student struggled to adapt to the workplace culture initially but improved significantly by the end.', '2026-04-11 01:30:00'),
('ASS-S-034', '34', 'SUP-002', 'Industry', 'Supervisor', 93, 91, 92, 94, 90, 95, 93, 92, 92.70, 'Exceptional contributions to team projects. Supervisor was highly impressed with overall professionalism.', '2026-04-12 01:30:00'),
('ASS-S-035', '35', 'SUP-001', 'Industry', 'Supervisor', 65, 67, 64, 69, 66, 68, 65, 67, 66.55, 'Below expectations in meeting project deadlines. Needs more structured time planning going forward.', '2026-04-13 01:30:00'),
('ASS-S-036', '36', 'SUP-003', 'Industry', 'Supervisor', 81, 80, 82, 83, 79, 84, 82, 81, 81.70, 'Good team player. Consistently met targets and communicated progress clearly to supervisors.', '2026-04-14 01:30:00'),
('ASS-S-037', '37', 'SUP-002', 'Industry', 'Supervisor', 69, 71, 68, 73, 70, 72, 69, 71, 70.55, 'Average performance. Student completed required tasks but did not go beyond minimum expectations.', '2026-04-15 01:30:00'),
('ASS-S-038', '38', 'SUP-002', 'Industry', 'Supervisor', 86, 84, 87, 88, 85, 89, 86, 87, 86.70, 'Intern impressed the entire department with creativity and thorough documentation of all work.', '2026-04-16 01:30:00'),
('ASS-S-040', '40', 'SUP-003', 'Industry', 'Supervisor', 75, 74, 73, 77, 76, 78, 75, 76, 75.70, 'Satisfactory attendance and conduct. Technical skills were adequate for the assigned tasks.', '2026-04-17 01:30:00'),
('ASS-S-042', '42', 'SUP-004', 'Industry', 'Supervisor', 90, 88, 91, 92, 89, 93, 90, 91, 90.70, 'Student excelled in all areas. Received commendation from multiple department heads during placement.', '2026-04-18 01:30:00'),
('ASS-S-044', '44', 'SUP-005', 'Industry', 'Supervisor', 63, 65, 62, 67, 64, 66, 63, 65, 64.55, 'Poor engagement with industry tasks. Safety briefings were not taken seriously on multiple occasions.', '2026-04-19 01:30:00'),
('ASS-S-045', '45', 'SUP-004', 'Industry', 'Supervisor', 82, 81, 80, 84, 83, 85, 82, 83, 82.70, 'Strong analytical skills demonstrated throughout. Written reports were detailed and professional.', '2026-04-20 01:30:00'),
('ASS-S-047', '47', 'SUP-005', 'Industry', 'Supervisor', 71, 72, 70, 74, 73, 75, 71, 73, 72.55, 'Student met most requirements but could benefit from more proactive communication with the team.', '2026-04-21 01:30:00'),
('ASS-S-048', '48', 'SUP-001', 'Industry', 'Supervisor', 87, 85, 88, 89, 86, 90, 87, 88, 87.70, 'Very capable and hardworking. Contributed meaningfully to an ongoing company-wide project.', '2026-04-22 01:30:00'),
('ASS-S-049', '49', 'SUP-004', 'Industry', 'Supervisor', 78, 77, 76, 80, 79, 81, 78, 79, 78.70, 'Industry tasks were completed on time and to a high standard. A pleasure to have on placement.', '2026-04-23 01:30:00'),
('ASS-S-050', '50', 'SUP-003', 'Industry', 'Supervisor', 85, 83, 84, 87, 82, 86, 85, 84, 84.70, 'Student showed great potential but needs to work on presenting findings more clearly and concisely.', '2026-04-24 01:30:00'),
('ASS-S-051', '51', 'SUP-001', 'Industry', 'Supervisor', 67, 68, 66, 70, 69, 71, 67, 69, 68.55, 'Adequate performance overall. Some difficulty adapting theory to real-world industry constraints.', '2026-04-25 01:30:00'),
('ASS-S-052', '52', 'SUP-001', 'Industry', 'Supervisor', 92, 90, 91, 93, 88, 94, 92, 91, 91.60, 'Top-tier intern. Demonstrated mastery of all technical tasks and mentored junior team members.', '2026-04-25 02:00:00'),
('ASS-S-053', '53', 'SUP-002', 'Industry', 'Supervisor', 74, 75, 73, 77, 76, 78, 74, 75, 75.40, 'Intern was punctual, cooperative, and delivered consistently good results across all criteria.', '2026-04-25 02:30:00');

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
('20609637', 'LEC-003', 'SUP-001', 'Huawei Malaysia', 'Ongoing', '1', 0.00),
('20705302', 'LEC-004', 'SUP-005', 'Mimos Berhad', 'Ongoing', '10', 0.00),
('20705375', 'LEC-004', 'SUP-005', 'Mimos Berhad', 'Ongoing', '11', 0.00),
('20706107', 'LEC-003', 'SUP-004', 'Apple Malaysia', 'Evaluated', '12', 0.00),
('20708501', 'LEC-003', 'SUP-001', 'Dell Technologies', 'Ongoing', '13', 0.00),
('20708827', 'LEC-004', 'SUP-001', 'Ericsson Malaysia', 'Ongoing', '14', 0.00),
('20709091', 'LEC-002', 'SUP-005', 'Grab Holdings', 'Ongoing', '15', 0.00),
('20709815', 'LEC-002', 'SUP-005', 'Grab Holdings', 'Ongoing', '16', 0.00),
('20709820', 'LEC-002', 'SUP-002', 'Google MY', 'Ongoing', '17', 0.00),
('20710895', 'LEC-004', 'SUP-001', 'Ericsson Malaysia', 'Ongoing', '18', 0.00),
('20675394', 'LEC-003', 'SUP-001', 'Huawei Malaysia', 'Ongoing', '2', 0.00),
('20711677', 'LEC-004', 'SUP-002', 'Motorola Solutions', 'Ongoing', '20', 0.00),
('20712533', 'LEC-004', 'SUP-003', 'Lenovo Malaysia', 'Ongoing', '21', 0.00),
('20713102', 'LEC-002', 'SUP-001', 'Intel Malaysia', 'Ongoing', '22', 0.00),
('20713226', 'LEC-002', 'SUP-003', 'Tesla Services', 'Ongoing', '23', 0.00),
('20713495', 'LEC-003', 'SUP-003', 'Samsung Electronics', 'Ongoing', '24', 0.00),
('20713862', 'LEC-004', 'SUP-003', 'Lenovo Malaysia', 'Ongoing', '25', 0.00),
('20713959', 'LEC-004', 'SUP-004', 'NTT Malaysia', 'Ongoing', '26', 0.00),
('20713963', 'LEC-004', 'SUP-004', 'NTT Malaysia', 'Ongoing', '27', 0.00),
('20714943', 'LEC-005', 'SUP-005', 'Bosch Malaysia', 'Ongoing', '28', 0.00),
('20715075', 'LEC-005', 'SUP-005', 'Bosch Malaysia', 'Ongoing', '29', 0.00),
('20676720', 'LEC-004', 'SUP-002', 'Axiata Group', 'Ongoing', '3', 0.00),
('20715078', 'LEC-003', 'SUP-004', 'IBM Malaysia', 'Ongoing', '30', 0.00),
('20716333', 'LEC-003', 'SUP-005', 'Maxis Berhad', 'Ongoing', '31', 0.00),
('20717493', 'LEC-005', 'SUP-001', 'Siemens Malaysia', 'Ongoing', '32', 0.00),
('20718823', 'LEC-003', 'SUP-004', 'Apple Malaysia', 'Ongoing', '33', 0.00),
('20722321', 'LEC-002', 'SUP-002', 'Google MY', 'Ongoing', '34', 86.00),
('20723833', 'LEC-005', 'SUP-001', 'Siemens Malaysia', 'Ongoing', '35', 0.00),
('20723858', 'LEC-002', 'SUP-003', 'Tesla Services', 'Ongoing', '36', 0.00),
('20749006', 'LEC-005', 'SUP-002', 'HP Malaysia', 'Ongoing', '37', 0.00),
('20780335', 'LEC-005', 'SUP-002', 'HP Malaysia', 'Ongoing', '38', 0.00),
('20679455', 'LEC-004', 'SUP-002', 'Axiata Group', 'Ongoing', '4', 0.00),
('20791953', 'LEC-005', 'SUP-003', 'Accenture MY', 'Ongoing', '40', 0.00),
('20792685', 'LEC-005', 'SUP-004', 'Deloitte Malaysia', 'Ongoing', '42', 0.00),
('20793790', 'LEC-005', 'SUP-005', 'KPMG Malaysia', 'Ongoing', '44', 0.00),
('20793978', 'LEC-003', 'SUP-004', 'IBM Malaysia', 'Ongoing', '45', 0.00),
('20796056', 'LEC-003', 'SUP-005', 'Oracle Corp', 'Ongoing', '47', 0.00),
('20796507', 'LEC-005', 'SUP-001', 'PwC Malaysia', 'Ongoing', '48', 0.00),
('20797468', 'LEC-002', 'SUP-004', 'Petronas', 'Ongoing', '49', 52.00),
('20683692', 'LEC-004', 'SUP-003', 'Celcom Digi', 'Ongoing', '5', 0.00),
('20797569', 'LEC-003', 'SUP-003', 'Samsung Electronics', 'Ongoing', '50', 0.00),
('20799262', 'LEC-002', 'SUP-001', 'AWS Malaysia', 'Ongoing', '51', 0.00),
('20800273', 'LEC-005', 'SUP-001', 'PwC Malaysia', 'Ongoing', '52', 0.00),
('20800646', 'LEC-005', 'SUP-002', 'EY Malaysia', 'Ongoing', '53', 0.00),
('20801522', 'LEC-006', 'SUP-002', 'EY Malaysia', 'Ongoing', '54', 0.00),
('20801771', 'LEC-002', 'SUP-004', 'Petronas', 'Ongoing', '55', 0.00),
('20803923', 'LEC-006', 'SUP-003', 'Maybank', 'Ongoing', '57', 0.00),
('20803979', 'LEC-006', 'SUP-004', 'CIMB Group', 'Ongoing', '58', 0.00),
('20804611', 'LEC-002', 'SUP-003', 'Microsoft KL', 'Ongoing', '59', 0.00),
('20690412', 'LEC-004', 'SUP-003', 'Celcom Digi', 'Ongoing', '6', 0.00),
('20804966', 'LEC-006', 'SUP-004', 'CIMB Group', 'Ongoing', '60', 0.00),
('20805230', 'LEC-006', 'SUP-005', 'RHB Bank', 'Ongoing', '62', 0.00),
('20806299', 'LEC-006', 'SUP-001', 'Telekom Malaysia', 'Ongoing', '64', 87.50),
('20806311', 'LEC-003', 'SUP-002', 'Cisco Systems', 'Ongoing', '65', 0.00),
('20806316', 'LEC-002', 'SUP-001', 'AWS Malaysia', 'Ongoing', '66', 0.00),
('20806377', 'LEC-006', 'SUP-001', 'Telekom Malaysia', 'Ongoing', '67', 0.00),
('20806478', 'LEC-003', 'SUP-005', 'Maxis Berhad', 'Ongoing', '68', 0.00),
('20806629', 'LEC-006', 'SUP-002', 'Hong Leong Bank', 'Ongoing', '69', 0.00),
('20700617', 'LEC-002', 'SUP-002', 'Shopee MY', 'Ongoing', '7', 0.00),
('20807243', 'LEC-006', 'SUP-002', 'Hong Leong Bank', 'Ongoing', '70', 0.00),
('20807535', 'LEC-002', 'SUP-002', 'Shopee MY', 'Ongoing', '71', 0.00),
('20807958', 'LEC-006', 'SUP-003', 'Tenaga Nasional', 'Ongoing', '72', 0.00),
('20808713', 'LEC-006', 'SUP-003', 'Tenaga Nasional', 'Ongoing', '74', 0.00),
('20808856', 'LEC-003', 'SUP-001', 'Dell Technologies', 'Ongoing', '75', 0.00),
('20809859', 'LEC-006', 'SUP-004', 'Sime Darby', 'Ongoing', '76', 0.00),
('20810450', 'LEC-002', 'SUP-001', 'Intel Malaysia', 'Ongoing', '77', 0.00),
('20811803', 'LEC-006', 'SUP-004', 'Sime Darby', 'Ongoing', '78', 0.00),
('20814150', 'LEC-006', 'SUP-005', 'Petronas Dagangan', 'Ongoing', '79', 0.00),
('20701943', 'LEC-004', 'SUP-004', 'TM Malaysia', 'Ongoing', '8', 0.00),
('20703441', 'LEC-004', 'SUP-004', 'TM Malaysia', 'Ongoing', '9', 0.00);

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
('LEC-002', 'lec1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Robert Low', 'Lecturer', NULL, NULL, NULL, NULL),
('LEC-003', 'lec2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ms. Sarah Lim', 'Lecturer', NULL, NULL, NULL, NULL),
('LEC-004', 'lec3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mr. Kevin Tan', 'Lecturer', NULL, NULL, NULL, NULL),
('LEC-005', 'lec67', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dr. Jane Smith', 'Lecturer', 'assessor_LEC005_1777184509.jpg', NULL, NULL, NULL),
('LEC-006', 'lec5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Prof. Wong Kar', 'Lecturer', NULL, NULL, NULL, NULL),
('SUP-001', 'sup1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mr. Raj Kumar', 'Supervisor', 'assessor_SUP001_1777277598.jpg', NULL, NULL, NULL),
('SUP-002', 'sup2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ms. Priya Nair', 'Supervisor', NULL, NULL, NULL, NULL),
('SUP-003', 'sup3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mr. Ahmad Zaki', 'Supervisor', NULL, NULL, NULL, NULL),
('SUP-004', 'sup4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ms. Chen Li Hua', 'Supervisor', NULL, NULL, NULL, NULL),
('SUP-005', 'sup5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mr. David Yong', 'Supervisor', NULL, NULL, NULL, NULL);

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
