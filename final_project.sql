-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 16, 2026 at 09:40 AM
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
-- Database: `final_project`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `Categories_ID` int(11) NOT NULL,
  `Category_Name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`Categories_ID`, `Category_Name`) VALUES
(3, 'Laboratory Equipment'),
(1, 'Laptop'),
(2, 'Projector'),
(5, 'Room'),
(4, 'Smart TV');

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `Department_ID` int(11) NOT NULL,
  `Department_Name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`Department_ID`, `Department_Name`) VALUES
(4, 'CABEIHM'),
(3, 'CAS'),
(2, 'CET'),
(1, 'CICS');

-- --------------------------------------------------------

--
-- Table structure for table `equipment_status`
--

CREATE TABLE `equipment_status` (
  `Status_ID` int(11) NOT NULL,
  `Status_Name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment_status`
--

INSERT INTO `equipment_status` (`Status_ID`, `Status_Name`) VALUES
(1, 'Available'),
(3, 'In Use'),
(2, 'Reserved'),
(5, 'Returned'),
(4, 'Under Maintenance');

-- --------------------------------------------------------

--
-- Table structure for table `history`
--

CREATE TABLE `history` (
  `History_ID` int(11) NOT NULL,
  `Resource_ID` int(11) NOT NULL,
  `Old_Status` varchar(50) DEFAULT NULL,
  `New_Status` varchar(50) DEFAULT NULL,
  `Reservation_ID` int(11) DEFAULT NULL,
  `Changed_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservation`
--

CREATE TABLE `reservation` (
  `Reservation_ID` int(11) NOT NULL,
  `Users_ID` int(11) DEFAULT NULL,
  `Resource_ID` int(11) DEFAULT NULL,
  `Reservation_Date` datetime DEFAULT NULL,
  `Return_Date` datetime DEFAULT NULL,
  `Department_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resource`
--

CREATE TABLE `resource` (
  `Resource_ID` int(11) NOT NULL,
  `Resource_Name` varchar(100) NOT NULL,
  `Categories_ID` int(11) DEFAULT NULL,
  `Status_ID` int(11) DEFAULT NULL,
  `Department_ID` int(11) DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `resource`
--
DELIMITER $$
CREATE TRIGGER `status_update` AFTER UPDATE ON `resource` FOR EACH ROW BEGIN
    IF OLD.Status_ID <> NEW.Status_ID THEN
        INSERT INTO history (
            Resource_ID, 
            Old_Status, 
            New_Status, 
            Reservation_ID
        )
        VALUES (
            NEW.Resource_ID, 
            OLD.Status_ID, 
            NEW.Status_ID, 
            -- Subquery to find the most recent reservation for this resource
            (SELECT Reservation_ID 
             FROM reservation 
             WHERE resource_ID = NEW.Resource_ID 
             ORDER BY Reservation_Date DESC LIMIT 1)
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `Roles_ID` int(11) NOT NULL,
  `Roles_Name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`Roles_ID`, `Roles_Name`) VALUES
(1, 'Admin'),
(2, 'Faculty');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `Users_ID` int(11) NOT NULL,
  `First_Name` varchar(100) NOT NULL,
  `Last_Name` varchar(100) NOT NULL,
  `Tr_Code` varchar(11) NOT NULL,
  `Users_Email` varchar(255) NOT NULL,
  `Users_Password` varchar(255) NOT NULL,
  `Roles_ID` int(11) NOT NULL,
  `Department_ID` int(11) NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`Categories_ID`),
  ADD UNIQUE KEY `Category_Name` (`Category_Name`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`Department_ID`),
  ADD UNIQUE KEY `Department_Name` (`Department_Name`);

--
-- Indexes for table `equipment_status`
--
ALTER TABLE `equipment_status`
  ADD PRIMARY KEY (`Status_ID`),
  ADD UNIQUE KEY `Status_Name` (`Status_Name`);

--
-- Indexes for table `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`History_ID`),
  ADD KEY `fk_resource` (`Resource_ID`),
  ADD KEY `fk_reservation` (`Reservation_ID`);

--
-- Indexes for table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`Reservation_ID`),
  ADD KEY `Users_ID` (`Users_ID`),
  ADD KEY `Resource_ID` (`Resource_ID`),
  ADD KEY `fk_department` (`Department_ID`);

--
-- Indexes for table `resource`
--
ALTER TABLE `resource`
  ADD PRIMARY KEY (`Resource_ID`),
  ADD KEY `Categories_ID` (`Categories_ID`),
  ADD KEY `Status_ID` (`Status_ID`),
  ADD KEY `fk_name` (`Department_ID`),
  ADD KEY `is_deleted` (`is_deleted`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`Roles_ID`),
  ADD UNIQUE KEY `Roles_Name` (`Roles_Name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`Users_ID`),
  ADD UNIQUE KEY `Tr_Code` (`Tr_Code`),
  ADD KEY `Roles_ID` (`Roles_ID`),
  ADD KEY `Department_ID` (`Department_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `Categories_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `Department_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `equipment_status`
--
ALTER TABLE `equipment_status`
  MODIFY `Status_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `history`
--
ALTER TABLE `history`
  MODIFY `History_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `Reservation_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resource`
--
ALTER TABLE `resource`
  MODIFY `Resource_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `Roles_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `Users_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `history`
--
ALTER TABLE `history`
  ADD CONSTRAINT `fk_reservation` FOREIGN KEY (`Reservation_ID`) REFERENCES `reservation` (`Reservation_ID`),
  ADD CONSTRAINT `fk_resource` FOREIGN KEY (`Resource_ID`) REFERENCES `resource` (`Resource_ID`);

--
-- Constraints for table `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `fk_department` FOREIGN KEY (`Department_ID`) REFERENCES `department` (`Department_ID`),
  ADD CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`Users_ID`) REFERENCES `users` (`Users_ID`),
  ADD CONSTRAINT `reservation_ibfk_2` FOREIGN KEY (`Resource_ID`) REFERENCES `resource` (`Resource_ID`);

--
-- Constraints for table `resource`
--
ALTER TABLE `resource`
  ADD CONSTRAINT `fk_name` FOREIGN KEY (`Department_ID`) REFERENCES `department` (`Department_ID`),
  ADD CONSTRAINT `resource_ibfk_1` FOREIGN KEY (`Categories_ID`) REFERENCES `categories` (`Categories_ID`),
  ADD CONSTRAINT `resource_ibfk_2` FOREIGN KEY (`Status_ID`) REFERENCES `equipment_status` (`Status_ID`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`Roles_ID`) REFERENCES `roles` (`Roles_ID`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`Department_ID`) REFERENCES `department` (`Department_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
