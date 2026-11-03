CREATE DATABASE IF NOT EXISTS `unicare_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;
USE `unicare_db`;

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `role` varchar(50) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `student_id_or_staff_id` varchar(50) NOT NULL,
  `contact_info` varchar(100) NOT NULL,
  PRIMARY KEY (`patient_id`),
  UNIQUE KEY `student_id_or_staff_id` (`student_id_or_staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `doctors` (
  `doctor_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `schedule` text NOT NULL,
  PRIMARY KEY (`doctor_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  PRIMARY KEY (`appointment_id`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`),
  CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `queue` (
  `queue_id` int(11) NOT NULL AUTO_INCREMENT,
  `appointment_id` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'Waiting',
  PRIMARY KEY (`queue_id`),
  KEY `appointment_id` (`appointment_id`),
  CONSTRAINT `queue_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `medical_records` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `diagnosis` text NOT NULL,
  `prescription` text NOT NULL,
  `notes` text DEFAULT NULL,
  `blood_pressure` varchar(20) DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`record_id`),
  KEY `appointment_id` (`appointment_id`),
  KEY `patient_id` (`patient_id`),
  KEY `doctor_id` (`doctor_id`),
  CONSTRAINT `medical_records_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE,
  CONSTRAINT `medical_records_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE,
  CONSTRAINT `medical_records_ibfk_3` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `clinic_closure` (
  `closure_id` int(11) NOT NULL AUTO_INCREMENT,
  `closure_date` date NOT NULL,
  `reason` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`closure_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
