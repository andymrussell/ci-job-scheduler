-- phpMyAdmin SQL Dump
-- version 2.11.9.6
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: May 10, 2011 at 06:44 PM
-- Server version: 5.0.77
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `handmade_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `job_schedule`
--

CREATE TABLE IF NOT EXISTS `job_schedule` (
  `id` int(11) NOT NULL auto_increment,
  `module_path` tinytext,
  `module_method` tinytext,
  `params` text,
  `status` varchar(45) default '0',
  `finished` tinyint(4) default '0',
  `error` tinyint(4) default NULL,
  `error_count` tinyint(4) default '0',
  `error_msg` tinytext,
  `date_start` datetime default NULL,
  `date_end` datetime default NULL,
  `server_id` int(11) default '1',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=429 ;

-- --------------------------------------------------------

--
-- Table structure for table `job_schedule_status`
--

CREATE TABLE IF NOT EXISTS `job_schedule_status` (
  `id` int(11) NOT NULL,
  `live` tinyint(4) default '0',
  `start_time` int(10) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
