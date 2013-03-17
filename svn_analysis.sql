-- phpMyAdmin SQL Dump
-- version 3.3.0
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 12, 2013 at 11:28 PM
-- Server version: 5.1.33
-- PHP Version: 5.2.9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `svn_analysis`
--

-- --------------------------------------------------------

--
-- Table structure for table `changes`
--

CREATE TABLE IF NOT EXISTS `changes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dev` varchar(255) NOT NULL,
  `when` datetime NOT NULL,
  `action` char(1) NOT NULL,
  `path` text NOT NULL,
  `rev` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `rev` (`rev`),
  KEY `path` (`path`(255),`dev`,`when`),
  KEY `when` (`when`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `log_entries`
--

CREATE TABLE IF NOT EXISTS `log_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dev` varchar(255) NOT NULL,
  `when` datetime NOT NULL,
  `msg` text NOT NULL,
  `rev` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rev` (`rev`),
  KEY `when` (`when`),
  KEY `dev` (`dev`,`when`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
