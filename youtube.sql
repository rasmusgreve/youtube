-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Vært: localhost
-- Genereringstid: 07. 11 2013 kl. 21:58:05
-- Serverversion: 5.6.12-log
-- PHP-version: 5.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `youtube`
--
CREATE DATABASE IF NOT EXISTS `youtube` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `youtube`;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `series`
--

CREATE TABLE IF NOT EXISTS `series` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `author` text NOT NULL,
  `search` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `video`
--

CREATE TABLE IF NOT EXISTS `video` (
  `id` varchar(64) NOT NULL COMMENT 'youtube id',
  `added` int(10) NOT NULL COMMENT 'timestamp',
  `watched` int(1) NOT NULL DEFAULT '0' COMMENT 'boolean',
  `title` text NOT NULL,
  `duration` int(10) NOT NULL DEFAULT '0' COMMENT 'seconds',
  `series` int(10) NOT NULL DEFAULT '0',
  `part` int(10) NOT NULL,
  `author` text NOT NULL,
  `published` datetime NOT NULL,
  `xml` text NOT NULL,
  PRIMARY KEY (`added`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
