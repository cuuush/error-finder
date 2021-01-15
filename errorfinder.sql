-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 15, 2021 at 04:40 PM
-- Server version: 5.7.31
-- PHP Version: 7.3.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `errorfinder`
--
CREATE DATABASE IF NOT EXISTS `errorfinder` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `errorfinder`;

-- --------------------------------------------------------

--
-- Table structure for table `endpoints`
--

DROP TABLE IF EXISTS `endpoints`;
CREATE TABLE IF NOT EXISTS `endpoints` (
  `endpoint_id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` text NOT NULL,
  `devicename` text NOT NULL,
  `devicetype` text NOT NULL,
  PRIMARY KEY (`endpoint_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `errors`
--

DROP TABLE IF EXISTS `errors`;
CREATE TABLE IF NOT EXISTS `errors` (
  `job_id` int(11) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL,
  `current` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `killed` tinyint(1) NOT NULL DEFAULT '0',
  `finished` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`job_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `errors_errorlist`
--

DROP TABLE IF EXISTS `errors_errorlist`;
CREATE TABLE IF NOT EXISTS `errors_errorlist` (
  `error_id` int(11) NOT NULL AUTO_INCREMENT,
  `endpoint_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `level` text NOT NULL,
  `reference` text NOT NULL,
  `type` text NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY (`error_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `errors_newerrors`
--

DROP TABLE IF EXISTS `errors_newerrors`;
CREATE TABLE IF NOT EXISTS `errors_newerrors` (
  `error_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `errors_output`
--

DROP TABLE IF EXISTS `errors_output`;
CREATE TABLE IF NOT EXISTS `errors_output` (
  `time` text NOT NULL,
  `log` mediumtext NOT NULL,
  `endpoint_id` int(11) DEFAULT NULL,
  `job_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
