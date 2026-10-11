-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 11, 2026 at 07:27 PM
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
-- Database: `project_ghosttrack`
--

-- --------------------------------------------------------

--
-- Table structure for table `alert`
--

CREATE TABLE `alert` (
  `Alert_ID` int(11) NOT NULL,
  `Project_ID` int(11) NOT NULL,
  `Contract_ID` int(11) DEFAULT NULL,
  `Assigned_Officer_ID` int(11) NOT NULL,
  `Alert_Type` varchar(100) NOT NULL,
  `Alert_Date` date NOT NULL,
  `Severity` varchar(50) NOT NULL,
  `Alert_Status` varchar(50) NOT NULL,
  `Reviewed_By` int(11) DEFAULT NULL,
  `Reviewed_At` datetime DEFAULT NULL,
  `Resolution_Notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alert`
--

INSERT INTO `alert` (`Alert_ID`, `Project_ID`, `Contract_ID`, `Assigned_Officer_ID`, `Alert_Type`, `Alert_Date`, `Severity`, `Alert_Status`, `Reviewed_By`, `Reviewed_At`, `Resolution_Notes`) VALUES
(1, 1, 1, 1, 'Budget Mismatch Alert', '2023-11-15', 'High', 'Resolved', 3, '2023-11-18 14:30:00', 'Verified with revised invoice; discrepancy adjusted.'),
(2, 2, 3, 2, 'Milestone Delay', '2018-03-20', 'Medium', 'Resolved', 3, '2018-03-25 10:15:00', 'Contractor provided valid extension justification.'),
(3, 3, 5, 2, 'Fund Discrepancy Alert', '2023-08-10', 'High', 'Resolved', 3, '2023-08-12 16:00:00', 'Verified final billing documents; matched with approved allocation.');

-- --------------------------------------------------------

--
-- Table structure for table `approval_request`
--

CREATE TABLE `approval_request` (
  `Request_ID` int(11) NOT NULL,
  `Project_ID` int(11) NOT NULL,
  `Allocation_ID` int(11) DEFAULT NULL,
  `Requested_By` int(11) NOT NULL,
  `Reviewed_By` int(11) NOT NULL,
  `Change_Type` varchar(100) NOT NULL,
  `Old_Amount` decimal(15,2) DEFAULT NULL,
  `Requested_Amount` decimal(15,2) NOT NULL,
  `Reason` text NOT NULL,
  `Request_Status` varchar(50) NOT NULL,
  `Request_Date` date NOT NULL,
  `Decision_Date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `approval_request`
--

INSERT INTO `approval_request` (`Request_ID`, `Project_ID`, `Allocation_ID`, `Requested_By`, `Reviewed_By`, `Change_Type`, `Old_Amount`, `Requested_Amount`, `Reason`, `Request_Status`, `Request_Date`, `Decision_Date`) VALUES
(1, 1, 1, 1, 3, 'Fund Revision', 30000000000.00, 32000000000.00, 'Additional material cost adjustment due to inflation.', 'Approved', '2023-11-10', '2023-11-12'),
(2, 2, 3, 2, 3, 'Budget Enhancement', 5000000000.00, 5500000000.00, 'Additional safety equipment installation at depot.', 'Approved', '2018-03-15', '2018-03-18'),
(3, 3, 5, 2, 3, 'Final Settlement Adjustment', 3000000000.00, 2800000000.00, 'Cost optimization and unused fund return adjustment.', 'Approved', '2023-09-10', '2023-09-12');

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `Log_ID` int(11) NOT NULL,
  `Officer_ID` int(11) NOT NULL,
  `Action_Type` varchar(50) NOT NULL,
  `Table_Name` varchar(100) NOT NULL,
  `Record_ID` int(11) NOT NULL,
  `Old_Value` text DEFAULT NULL,
  `New_Value` text DEFAULT NULL,
  `Action_Time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`Log_ID`, `Officer_ID`, `Action_Type`, `Table_Name`, `Record_ID`, `Old_Value`, `New_Value`, `Action_Time`) VALUES
(1, 2, 'INSERT', 'CONTRACT', 1, NULL, 'Contract Amount: 30000000000.00 for Padma Bridge Rail Link (WD-1)', '2016-08-10 10:00:00'),
(2, 2, 'INSERT', 'CONTRACT', 3, NULL, 'Contract Amount: 5000000000.00 for Metro Rail Line-6 (CP-01)', '2014-09-25 12:15:00'),
(3, 2, 'INSERT', 'CONTRACT', 5, NULL, 'Contract Amount: 8400000000.00 for Karnafuli Tunnel', '2016-03-15 11:30:00'),
(4, 3, 'UPDATE', 'ALERT', 1, 'Status: Pending', 'Status: Resolved', '2023-11-18 14:30:00'),
(5, 3, 'UPDATE', 'ALERT', 2, 'Status: Pending', 'Status: Resolved', '2018-03-25 10:15:00'),
(6, 3, 'UPDATE', 'ALERT', 3, 'Status: Pending', 'Status: Resolved', '2023-08-12 16:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `bid`
--

CREATE TABLE `bid` (
  `Bid_ID` int(11) NOT NULL,
  `Tender_ID` int(11) NOT NULL,
  `Contractor_ID` int(11) NOT NULL,
  `Proposed_Amount` decimal(15,2) NOT NULL,
  `Submission_Date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bid`
--

INSERT INTO `bid` (`Bid_ID`, `Tender_ID`, `Contractor_ID`, `Proposed_Amount`, `Submission_Date`) VALUES
(1, 1, 1, 118500000000.00, '2016-04-25'),
(2, 1, 5, 122000000000.00, '2016-04-28'),
(3, 2, 1, 34000000000.00, '2017-03-15'),
(4, 3, 3, 14800000000.00, '2014-07-10'),
(5, 4, 3, 44200000000.00, '2015-11-05'),
(6, 5, 2, 8400000000.00, '2016-02-20'),
(7, 5, 1, 8900000000.00, '2016-02-22'),
(8, 6, 4, 21000000000.00, '2019-05-20');

-- --------------------------------------------------------

--
-- Table structure for table `bid_evaluation`
--

CREATE TABLE `bid_evaluation` (
  `Evaluation_ID` int(11) NOT NULL,
  `Bid_ID` int(11) NOT NULL,
  `Technical_Score` decimal(5,2) NOT NULL,
  `Eligibility_Status` varchar(50) NOT NULL,
  `Capacity_Status` varchar(50) NOT NULL,
  `Financial_Status` varchar(50) NOT NULL,
  `Evaluation_Result` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bid_evaluation`
--

INSERT INTO `bid_evaluation` (`Evaluation_ID`, `Bid_ID`, `Technical_Score`, `Eligibility_Status`, `Capacity_Status`, `Financial_Status`, `Evaluation_Result`) VALUES
(1, 1, 95.50, 'Eligible', 'Sufficient', 'Passed', 'Responsive'),
(2, 2, 82.00, 'Eligible', 'Sufficient', 'Passed', 'Non-Responsive'),
(3, 3, 88.00, 'Eligible', 'Sufficient', 'Passed', 'Responsive'),
(4, 4, 91.00, 'Eligible', 'Sufficient', 'Passed', 'Responsive'),
(5, 5, 94.20, 'Eligible', 'Sufficient', 'Passed', 'Responsive'),
(6, 6, 96.00, 'Eligible', 'Sufficient', 'Passed', 'Responsive'),
(7, 7, 85.50, 'Eligible', 'Sufficient', 'Passed', 'Non-Responsive');

-- --------------------------------------------------------

--
-- Table structure for table `contract`
--

CREATE TABLE `contract` (
  `Contract_ID` int(11) NOT NULL,
  `Bid_ID` int(11) NOT NULL,
  `Contract_Amount` decimal(15,2) NOT NULL,
  `Award_Date` date NOT NULL,
  `Start_Date` date NOT NULL,
  `End_Date` date NOT NULL,
  `Contract_Status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contract`
--

INSERT INTO `contract` (`Contract_ID`, `Bid_ID`, `Contract_Amount`, `Award_Date`, `Start_Date`, `End_Date`, `Contract_Status`) VALUES
(1, 1, 118500000000.00, '2016-06-01', '2016-07-01', '2024-06-30', 'Completed'),
(2, 3, 34000000000.00, '2017-04-10', '2017-05-01', '2022-12-31', 'Completed'),
(3, 4, 14800000000.00, '2014-08-15', '2014-09-01', '2018-05-31', 'Completed'),
(4, 5, 44200000000.00, '2015-12-01', '2016-01-01', '2022-12-31', 'Completed'),
(5, 6, 8400000000.00, '2016-03-15', '2016-04-01', '2023-10-31', 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `contractor`
--

CREATE TABLE `contractor` (
  `Contractor_ID` int(11) NOT NULL,
  `Contractor_Name` varchar(150) NOT NULL,
  `Registration_No` varchar(100) NOT NULL,
  `Contact_Info` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contractor`
--

INSERT INTO `contractor` (`Contractor_ID`, `Contractor_Name`, `Registration_No`, `Contact_Info`) VALUES
(1, 'China Railway Group Limited (CREC)', 'C-REG-2015-889', 'info@crec.cn'),
(2, 'China Communications Construction Company (CCCC)', 'C-REG-2014-412', 'contact@cccc.ltd'),
(3, 'Thai-ITD Joint Venture (Italian-Thai Development)', 'C-REG-2013-105', 'contact@itd.co.th'),
(4, 'Samsung C&T Corporation', 'C-REG-2017-902', 'samsung.cnt@samsung.com'),
(5, 'Toma Construction & Co. Ltd.', 'C-REG-2010-044', 'info@tomagroup-bd.com');

-- --------------------------------------------------------

--
-- Table structure for table `district`
--

CREATE TABLE `district` (
  `District_ID` int(11) NOT NULL,
  `District_Name` varchar(100) NOT NULL,
  `Division_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `district`
--

INSERT INTO `district` (`District_ID`, `District_Name`, `Division_ID`) VALUES
(1, 'Dhaka', 1),
(5, 'Faridpur', 1),
(7, 'Gazipur', 1),
(6, 'Gopalganj', 1),
(4, 'Madaripur', 1),
(2, 'Munshiganj', 1),
(8, 'Narayanganj', 1),
(3, 'Shariatpur', 1),
(9, 'Chattogram', 2),
(10, 'Coxs Bazar', 2),
(12, 'Jashore', 3),
(11, 'Narail', 3);

-- --------------------------------------------------------

--
-- Table structure for table `division`
--

CREATE TABLE `division` (
  `Division_ID` int(11) NOT NULL,
  `Division_Name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `division`
--

INSERT INTO `division` (`Division_ID`, `Division_Name`) VALUES
(5, 'Barishal'),
(2, 'Chattogram'),
(1, 'Dhaka'),
(4, 'Khulna'),
(8, 'Mymensingh'),
(3, 'Rajshahi'),
(7, 'Rangpur'),
(6, 'Sylhet');

-- --------------------------------------------------------

--
-- Table structure for table `fund_allocation`
--

CREATE TABLE `fund_allocation` (
  `Allocation_ID` int(11) NOT NULL,
  `Contract_ID` int(11) NOT NULL,
  `Amount_Allocated` decimal(15,2) NOT NULL,
  `Allocation_Date` date NOT NULL,
  `Installment_No` int(11) NOT NULL,
  `Allocation_Status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fund_allocation`
--

INSERT INTO `fund_allocation` (`Allocation_ID`, `Contract_ID`, `Amount_Allocated`, `Allocation_Date`, `Installment_No`, `Allocation_Status`) VALUES
(1, 1, 30000000000.00, '2016-08-15', 1, 'Released'),
(2, 1, 40000000000.00, '2018-03-10', 2, 'Released'),
(3, 2, 10000000000.00, '2017-06-20', 1, 'Released'),
(4, 3, 5000000000.00, '2014-10-01', 1, 'Released'),
(5, 4, 15000000000.00, '2016-02-15', 1, 'Released'),
(6, 5, 3000000000.00, '2016-05-10', 1, 'Released');

-- --------------------------------------------------------

--
-- Table structure for table `implementing_agency`
--

CREATE TABLE `implementing_agency` (
  `Agency_ID` int(11) NOT NULL,
  `Agency_Name` varchar(150) NOT NULL,
  `Agency_Type` varchar(100) DEFAULT NULL,
  `Contact_Info` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `implementing_agency`
--

INSERT INTO `implementing_agency` (`Agency_ID`, `Agency_Name`, `Agency_Type`, `Contact_Info`) VALUES
(1, 'Roads and Highways Department', 'Government Department', NULL),
(2, 'Local Government Engineering Department', 'Government Department', NULL),
(3, 'Department of Public Health Engineering', 'Government Department', NULL),
(4, 'Public Works Department', 'Government Department', NULL),
(5, 'Bangladesh Water Development Board', 'Government Agency', NULL),
(6, 'Bangladesh Railway', 'Government Agency', NULL),
(7, 'Bangladesh Bridge Authority', 'Government Authority', NULL),
(8, 'Bangladesh Rural Electrification Board', 'Government Board', NULL),
(9, 'Department of Agricultural Extension', 'Government Department', NULL),
(10, 'Bangladesh Agricultural Development Corporation', 'Government Corporation', NULL),
(11, 'Bangladesh Inland Water Transport Authority', 'Government Authority', NULL),
(12, 'Directorate General of Health Services', 'Government Directorate', NULL),
(13, 'Department of Disaster Management', 'Government Department', NULL),
(14, 'Department of Environment', 'Government Department', NULL),
(15, 'Department of Public Libraries', 'Government Department', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `invoice`
--

CREATE TABLE `invoice` (
  `Invoice_ID` int(11) NOT NULL,
  `Contract_ID` int(11) NOT NULL,
  `Claimed_Amount` decimal(15,2) NOT NULL,
  `Invoice_Date` date NOT NULL,
  `Verification_Status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoice`
--

INSERT INTO `invoice` (`Invoice_ID`, `Contract_ID`, `Claimed_Amount`, `Invoice_Date`, `Verification_Status`) VALUES
(1, 1, 25000000000.00, '2023-11-10', 'Verified'),
(2, 2, 9500000000.00, '2022-10-05', 'Verified'),
(3, 3, 4800000000.00, '2018-04-12', 'Verified'),
(4, 4, 14000000000.00, '2022-09-20', 'Verified'),
(5, 5, 2800000000.00, '2023-09-15', 'Verified');

-- --------------------------------------------------------

--
-- Table structure for table `ministry`
--

CREATE TABLE `ministry` (
  `Ministry_ID` int(11) NOT NULL,
  `Ministry_Name` varchar(150) NOT NULL,
  `Contact_Info` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ministry`
--

INSERT INTO `ministry` (`Ministry_ID`, `Ministry_Name`, `Contact_Info`) VALUES
(1, 'Ministry of Road Transport and Bridges', 'minister@rthd.gov.bd'),
(2, 'Ministry of Local Government, Rural Development and Co-operatives', 'info@lgd.gov.bd'),
(3, 'Ministry of Housing and Public Works', 'secretary@mohpw.gov.bd '),
(4, 'Ministry of Planning', 'secretary@plandiv.gov.bd'),
(5, 'Ministry of Finance', 'minister@finance.gov.bd'),
(6, 'Ministry of Water Resources', NULL),
(7, 'Ministry of Power, Energy and Mineral Resources', NULL),
(8, 'Ministry of Agriculture', NULL),
(9, 'Ministry of Shipping', NULL),
(10, 'Ministry of Railways', NULL),
(11, 'Ministry of Disaster Management and Relief', NULL),
(12, 'Ministry of Environment, Forest and Climate Change', NULL),
(13, 'Ministry of Industries', NULL),
(14, 'Ministry of Education', NULL),
(15, 'Ministry of Land', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `officer`
--

CREATE TABLE `officer` (
  `Officer_ID` int(11) NOT NULL,
  `Officer_Name` varchar(150) NOT NULL,
  `Designation` varchar(100) NOT NULL,
  `Department` varchar(100) NOT NULL,
  `Role_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `officer`
--

INSERT INTO `officer` (`Officer_ID`, `Officer_Name`, `Designation`, `Department`, `Role_ID`) VALUES
(1, 'Dr. Rafiqul Islam', 'Joint Secretary', 'Ministry of Planning', 1),
(2, 'Engr. Farhana Ahmed', 'Director of Procurement', 'Bangladesh Railway', 2),
(3, 'Md. Abul Kashem', 'Director General (Monitoring)', 'Implementation Monitoring and Evaluation Division (IMED)', 3);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `Payment_ID` int(11) NOT NULL,
  `Invoice_ID` int(11) NOT NULL,
  `Paid_Amount` decimal(15,2) NOT NULL,
  `Payment_Date` date NOT NULL,
  `Payment_Status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`Payment_ID`, `Invoice_ID`, `Paid_Amount`, `Payment_Date`, `Payment_Status`) VALUES
(1, 1, 25000000000.00, '2023-11-25', 'Completed'),
(2, 2, 9500000000.00, '2022-10-20', 'Completed'),
(3, 3, 4800000000.00, '2018-04-28', 'Completed'),
(4, 4, 14000000000.00, '2022-10-05', 'Completed'),
(5, 5, 2800000000.00, '2023-09-30', 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `procurement_package`
--

CREATE TABLE `procurement_package` (
  `Package_ID` int(11) NOT NULL,
  `Project_ID` int(11) NOT NULL,
  `Package_Number` varchar(50) NOT NULL,
  `Package_Description` text DEFAULT NULL,
  `Estimated_Cost` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `procurement_package`
--

INSERT INTO `procurement_package` (`Package_ID`, `Project_ID`, `Package_Number`, `Package_Description`, `Estimated_Cost`) VALUES
(1, 1, 'WD-1', 'Civil Works for Railway Embankment and Track Construction (Dhaka to Bhanga)', 120000000000.00),
(2, 1, 'WD-2', 'Signaling and Telecommunication System Installation', 35000000000.00),
(3, 2, 'CP-01', 'Depot Land Development and Civil Works at Uttara', 15000000000.00),
(4, 2, 'CP-03', 'Elevated Viaduct and Station Structures (Uttara to Agargaon)', 45000000000.00),
(5, 3, 'PKG-01', 'Tunnel Boring and Main Structure Construction under Karnafuli River', 8500000000.00),
(6, 4, 'HAT-01', 'Construction of Third Terminal Building, Apron, and Cargo Complex', 21398000000.00);

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

CREATE TABLE `project` (
  `Project_ID` int(11) NOT NULL,
  `Project_Name` varchar(200) NOT NULL,
  `Ministry_ID` int(11) NOT NULL,
  `Agency_ID` int(11) NOT NULL,
  `Approved_Budget` decimal(15,2) NOT NULL,
  `Project_Start_Date` date DEFAULT NULL,
  `Approval_Date` date DEFAULT NULL,
  `Expected_End_Date` date DEFAULT NULL,
  `Project_Status` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project`
--

INSERT INTO `project` (`Project_ID`, `Project_Name`, `Ministry_ID`, `Agency_ID`, `Approved_Budget`, `Project_Start_Date`, `Approval_Date`, `Expected_End_Date`, `Project_Status`) VALUES
(1, 'Padma Bridge Rail Link Project', 10, 6, 392468000000.00, '2016-01-01', '2015-05-03', '2024-06-30', 'Ongoing'),
(2, 'Dhaka Mass Rapid Transit Development Project (Line-6)', 1, 1, 334720000000.00, '2013-07-01', '2012-12-18', '2026-12-31', 'Ongoing'),
(3, 'Bangabandhu Sheikh Mujibur Rahman Tunnel(KarnafuliTunnel)', 1, 7, 10689000000.00, '2015-11-01', '2015-10-05', '2023-10-31', 'Completed'),
(4, 'Hazrat Shahjalal International Airport Expansion Project (Third Terminal)', 1, 1, 21398000000.00, '2019-12-01', '2017-10-24', '2024-12-31', 'Ongoing');

-- --------------------------------------------------------

--
-- Table structure for table `project_location`
--

CREATE TABLE `project_location` (
  `Location_ID` int(11) NOT NULL,
  `Project_ID` int(11) NOT NULL,
  `Upazila_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_location`
--

INSERT INTO `project_location` (`Location_ID`, `Project_ID`, `Upazila_ID`) VALUES
(1, 1, 4),
(2, 1, 5),
(3, 1, 6),
(4, 1, 7),
(5, 1, 12),
(6, 2, 1),
(7, 2, 2),
(8, 2, 3),
(9, 2, 4),
(10, 3, 8),
(11, 3, 9),
(12, 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `Role_ID` int(11) NOT NULL,
  `Role_Name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`Role_ID`, `Role_Name`) VALUES
(2, 'Procurement Officer'),
(1, 'Project Officer'),
(3, 'Reviewing Officer');

-- --------------------------------------------------------

--
-- Table structure for table `tender`
--

CREATE TABLE `tender` (
  `Tender_ID` int(11) NOT NULL,
  `Package_ID` int(11) NOT NULL,
  `Tender_Reference` varchar(100) NOT NULL,
  `Publication_Date` date NOT NULL,
  `Submission_Deadline` date NOT NULL,
  `Tender_Status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tender`
--

INSERT INTO `tender` (`Tender_ID`, `Package_ID`, `Tender_Reference`, `Publication_Date`, `Submission_Deadline`, `Tender_Status`) VALUES
(1, 1, 'IFT-PBRLP-WD1-2016', '2016-02-15', '2016-04-30', 'Awarded'),
(2, 2, 'IFT-PBRLP-WD2-2017', '2017-01-10', '2017-03-20', 'Awarded'),
(3, 3, 'IFT-DMRT-CP01-2014', '2014-05-01', '2014-07-15', 'Awarded'),
(4, 4, 'IFT-DMRT-CP03-2015', '2015-08-12', '2015-11-10', 'Awarded'),
(5, 5, 'IFT-BKTT-PKG1-2015', '2015-12-01', '2016-02-28', 'Awarded'),
(6, 6, 'IFT-HSIA-HAT01-2019', '2019-03-10', '2019-06-05', 'Awarded');

-- --------------------------------------------------------

--
-- Table structure for table `upazila`
--

CREATE TABLE `upazila` (
  `Upazila_ID` int(11) NOT NULL,
  `Upazila_Name` varchar(100) NOT NULL,
  `District_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `upazila`
--

INSERT INTO `upazila` (`Upazila_ID`, `Upazila_Name`, `District_ID`) VALUES
(3, 'Mirpur', 1),
(4, 'Motijheel', 1),
(2, 'Tejgaon', 1),
(1, 'Uttara', 1),
(5, 'Lohajang', 2),
(6, 'Zajira', 3),
(7, 'Bhanga', 5),
(8, 'Anwara', 9),
(9, 'Karnafuli', 9),
(10, 'Patiya', 9),
(11, 'Maheshkhali', 10),
(13, 'Abhaynagar', 12),
(12, 'Jashore Sadar', 12);

-- --------------------------------------------------------

--
-- Table structure for table `work_progress`
--

CREATE TABLE `work_progress` (
  `Progress_ID` int(11) NOT NULL,
  `Contract_ID` int(11) NOT NULL,
  `Report_Date` date NOT NULL,
  `Physical_Progress` decimal(5,2) NOT NULL,
  `Progress_State` varchar(100) NOT NULL,
  `Verification_Video` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `work_progress`
--

INSERT INTO `work_progress` (`Progress_ID`, `Contract_ID`, `Report_Date`, `Physical_Progress`, `Progress_State`, `Verification_Video`) VALUES
(1, 1, '2023-12-31', 88.50, 'Track laying almost completed', 'https://drive.google.com/file/d/ghosttrack_pbrlp_001/view'),
(2, 2, '2022-11-30', 100.00, 'Signaling and telecommunication work fully completed', 'https://drive.google.com/file/d/ghosttrack_pbrlp_002/view'),
(3, 3, '2018-05-15', 100.00, 'Depot land development and civil structures completed', 'https://drive.google.com/file/d/ghosttrack_dmrt_001/view'),
(4, 4, '2022-10-15', 100.00, 'Elevated viaduct and station structures completed', 'https://drive.google.com/file/d/ghosttrack_dmrt_003/view'),
(5, 5, '2023-10-15', 100.00, 'Tunnel excavation and pavement fully completed', 'https://drive.google.com/file/d/ghosttrack_karnafuli_001/view');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alert`
--
ALTER TABLE `alert`
  ADD PRIMARY KEY (`Alert_ID`),
  ADD KEY `Project_ID` (`Project_ID`),
  ADD KEY `Contract_ID` (`Contract_ID`),
  ADD KEY `Assigned_Officer_ID` (`Assigned_Officer_ID`),
  ADD KEY `Reviewed_By` (`Reviewed_By`);

--
-- Indexes for table `approval_request`
--
ALTER TABLE `approval_request`
  ADD PRIMARY KEY (`Request_ID`),
  ADD KEY `Project_ID` (`Project_ID`),
  ADD KEY `Allocation_ID` (`Allocation_ID`),
  ADD KEY `Requested_By` (`Requested_By`),
  ADD KEY `Reviewed_By` (`Reviewed_By`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`Log_ID`),
  ADD KEY `Officer_ID` (`Officer_ID`);

--
-- Indexes for table `bid`
--
ALTER TABLE `bid`
  ADD PRIMARY KEY (`Bid_ID`),
  ADD KEY `Tender_ID` (`Tender_ID`),
  ADD KEY `Contractor_ID` (`Contractor_ID`);

--
-- Indexes for table `bid_evaluation`
--
ALTER TABLE `bid_evaluation`
  ADD PRIMARY KEY (`Evaluation_ID`),
  ADD UNIQUE KEY `Bid_ID` (`Bid_ID`);

--
-- Indexes for table `contract`
--
ALTER TABLE `contract`
  ADD PRIMARY KEY (`Contract_ID`),
  ADD UNIQUE KEY `Bid_ID` (`Bid_ID`);

--
-- Indexes for table `contractor`
--
ALTER TABLE `contractor`
  ADD PRIMARY KEY (`Contractor_ID`),
  ADD UNIQUE KEY `Registration_No` (`Registration_No`);

--
-- Indexes for table `district`
--
ALTER TABLE `district`
  ADD PRIMARY KEY (`District_ID`),
  ADD UNIQUE KEY `UQ_Division_District` (`Division_ID`,`District_Name`);

--
-- Indexes for table `division`
--
ALTER TABLE `division`
  ADD PRIMARY KEY (`Division_ID`),
  ADD UNIQUE KEY `Division_Name` (`Division_Name`);

--
-- Indexes for table `fund_allocation`
--
ALTER TABLE `fund_allocation`
  ADD PRIMARY KEY (`Allocation_ID`),
  ADD UNIQUE KEY `UQ_Contract_Installment` (`Contract_ID`,`Installment_No`);

--
-- Indexes for table `implementing_agency`
--
ALTER TABLE `implementing_agency`
  ADD PRIMARY KEY (`Agency_ID`),
  ADD UNIQUE KEY `Agency_Name` (`Agency_Name`);

--
-- Indexes for table `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`Invoice_ID`),
  ADD KEY `Contract_ID` (`Contract_ID`);

--
-- Indexes for table `ministry`
--
ALTER TABLE `ministry`
  ADD PRIMARY KEY (`Ministry_ID`),
  ADD UNIQUE KEY `Ministry_Name` (`Ministry_Name`);

--
-- Indexes for table `officer`
--
ALTER TABLE `officer`
  ADD PRIMARY KEY (`Officer_ID`),
  ADD KEY `Role_ID` (`Role_ID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`Payment_ID`),
  ADD KEY `Invoice_ID` (`Invoice_ID`);

--
-- Indexes for table `procurement_package`
--
ALTER TABLE `procurement_package`
  ADD PRIMARY KEY (`Package_ID`),
  ADD UNIQUE KEY `UQ_Project_Package` (`Project_ID`,`Package_Number`);

--
-- Indexes for table `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`Project_ID`),
  ADD KEY `Ministry_ID` (`Ministry_ID`),
  ADD KEY `Agency_ID` (`Agency_ID`);

--
-- Indexes for table `project_location`
--
ALTER TABLE `project_location`
  ADD PRIMARY KEY (`Location_ID`),
  ADD UNIQUE KEY `UQ_Project_Upazila` (`Project_ID`,`Upazila_ID`),
  ADD KEY `Upazila_ID` (`Upazila_ID`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`Role_ID`),
  ADD UNIQUE KEY `Role_Name` (`Role_Name`);

--
-- Indexes for table `tender`
--
ALTER TABLE `tender`
  ADD PRIMARY KEY (`Tender_ID`),
  ADD KEY `Package_ID` (`Package_ID`);

--
-- Indexes for table `upazila`
--
ALTER TABLE `upazila`
  ADD PRIMARY KEY (`Upazila_ID`),
  ADD UNIQUE KEY `UQ_District_Upazila` (`District_ID`,`Upazila_Name`);

--
-- Indexes for table `work_progress`
--
ALTER TABLE `work_progress`
  ADD PRIMARY KEY (`Progress_ID`),
  ADD KEY `Contract_ID` (`Contract_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alert`
--
ALTER TABLE `alert`
  MODIFY `Alert_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `approval_request`
--
ALTER TABLE `approval_request`
  MODIFY `Request_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `Log_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `bid`
--
ALTER TABLE `bid`
  MODIFY `Bid_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `bid_evaluation`
--
ALTER TABLE `bid_evaluation`
  MODIFY `Evaluation_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `contract`
--
ALTER TABLE `contract`
  MODIFY `Contract_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `contractor`
--
ALTER TABLE `contractor`
  MODIFY `Contractor_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `district`
--
ALTER TABLE `district`
  MODIFY `District_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `division`
--
ALTER TABLE `division`
  MODIFY `Division_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `fund_allocation`
--
ALTER TABLE `fund_allocation`
  MODIFY `Allocation_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `implementing_agency`
--
ALTER TABLE `implementing_agency`
  MODIFY `Agency_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `invoice`
--
ALTER TABLE `invoice`
  MODIFY `Invoice_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `ministry`
--
ALTER TABLE `ministry`
  MODIFY `Ministry_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `officer`
--
ALTER TABLE `officer`
  MODIFY `Officer_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `Payment_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `procurement_package`
--
ALTER TABLE `procurement_package`
  MODIFY `Package_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `project`
--
ALTER TABLE `project`
  MODIFY `Project_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `project_location`
--
ALTER TABLE `project_location`
  MODIFY `Location_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `Role_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tender`
--
ALTER TABLE `tender`
  MODIFY `Tender_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `upazila`
--
ALTER TABLE `upazila`
  MODIFY `Upazila_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `work_progress`
--
ALTER TABLE `work_progress`
  MODIFY `Progress_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alert`
--
ALTER TABLE `alert`
  ADD CONSTRAINT `alert_ibfk_1` FOREIGN KEY (`Project_ID`) REFERENCES `project` (`Project_ID`),
  ADD CONSTRAINT `alert_ibfk_2` FOREIGN KEY (`Contract_ID`) REFERENCES `contract` (`Contract_ID`),
  ADD CONSTRAINT `alert_ibfk_3` FOREIGN KEY (`Assigned_Officer_ID`) REFERENCES `officer` (`Officer_ID`),
  ADD CONSTRAINT `alert_ibfk_4` FOREIGN KEY (`Reviewed_By`) REFERENCES `officer` (`Officer_ID`);

--
-- Constraints for table `approval_request`
--
ALTER TABLE `approval_request`
  ADD CONSTRAINT `approval_request_ibfk_1` FOREIGN KEY (`Project_ID`) REFERENCES `project` (`Project_ID`),
  ADD CONSTRAINT `approval_request_ibfk_2` FOREIGN KEY (`Allocation_ID`) REFERENCES `fund_allocation` (`Allocation_ID`),
  ADD CONSTRAINT `approval_request_ibfk_3` FOREIGN KEY (`Requested_By`) REFERENCES `officer` (`Officer_ID`),
  ADD CONSTRAINT `approval_request_ibfk_4` FOREIGN KEY (`Reviewed_By`) REFERENCES `officer` (`Officer_ID`);

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`Officer_ID`) REFERENCES `officer` (`Officer_ID`);

--
-- Constraints for table `bid`
--
ALTER TABLE `bid`
  ADD CONSTRAINT `bid_ibfk_1` FOREIGN KEY (`Tender_ID`) REFERENCES `tender` (`Tender_ID`),
  ADD CONSTRAINT `bid_ibfk_2` FOREIGN KEY (`Contractor_ID`) REFERENCES `contractor` (`Contractor_ID`);

--
-- Constraints for table `bid_evaluation`
--
ALTER TABLE `bid_evaluation`
  ADD CONSTRAINT `bid_evaluation_ibfk_1` FOREIGN KEY (`Bid_ID`) REFERENCES `bid` (`Bid_ID`);

--
-- Constraints for table `contract`
--
ALTER TABLE `contract`
  ADD CONSTRAINT `contract_ibfk_1` FOREIGN KEY (`Bid_ID`) REFERENCES `bid` (`Bid_ID`);

--
-- Constraints for table `district`
--
ALTER TABLE `district`
  ADD CONSTRAINT `district_ibfk_1` FOREIGN KEY (`Division_ID`) REFERENCES `division` (`Division_ID`) ON DELETE CASCADE;

--
-- Constraints for table `fund_allocation`
--
ALTER TABLE `fund_allocation`
  ADD CONSTRAINT `fund_allocation_ibfk_1` FOREIGN KEY (`Contract_ID`) REFERENCES `contract` (`Contract_ID`);

--
-- Constraints for table `invoice`
--
ALTER TABLE `invoice`
  ADD CONSTRAINT `invoice_ibfk_1` FOREIGN KEY (`Contract_ID`) REFERENCES `contract` (`Contract_ID`);

--
-- Constraints for table `officer`
--
ALTER TABLE `officer`
  ADD CONSTRAINT `officer_ibfk_1` FOREIGN KEY (`Role_ID`) REFERENCES `role` (`Role_ID`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`Invoice_ID`) REFERENCES `invoice` (`Invoice_ID`);

--
-- Constraints for table `procurement_package`
--
ALTER TABLE `procurement_package`
  ADD CONSTRAINT `procurement_package_ibfk_1` FOREIGN KEY (`Project_ID`) REFERENCES `project` (`Project_ID`);

--
-- Constraints for table `project`
--
ALTER TABLE `project`
  ADD CONSTRAINT `project_ibfk_1` FOREIGN KEY (`Ministry_ID`) REFERENCES `ministry` (`Ministry_ID`),
  ADD CONSTRAINT `project_ibfk_2` FOREIGN KEY (`Agency_ID`) REFERENCES `implementing_agency` (`Agency_ID`);

--
-- Constraints for table `project_location`
--
ALTER TABLE `project_location`
  ADD CONSTRAINT `project_location_ibfk_1` FOREIGN KEY (`Project_ID`) REFERENCES `project` (`Project_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_location_ibfk_2` FOREIGN KEY (`Upazila_ID`) REFERENCES `upazila` (`Upazila_ID`) ON DELETE CASCADE;

--
-- Constraints for table `tender`
--
ALTER TABLE `tender`
  ADD CONSTRAINT `tender_ibfk_1` FOREIGN KEY (`Package_ID`) REFERENCES `procurement_package` (`Package_ID`);

--
-- Constraints for table `upazila`
--
ALTER TABLE `upazila`
  ADD CONSTRAINT `upazila_ibfk_1` FOREIGN KEY (`District_ID`) REFERENCES `district` (`District_ID`) ON DELETE CASCADE;

--
-- Constraints for table `work_progress`
--
ALTER TABLE `work_progress`
  ADD CONSTRAINT `work_progress_ibfk_1` FOREIGN KEY (`Contract_ID`) REFERENCES `contract` (`Contract_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
