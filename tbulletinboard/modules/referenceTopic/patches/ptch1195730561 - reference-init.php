<?php
	/**
	 *	TBB2, an highly configurable and dynamic bulletin board
	 *	Copyright (C) 2007  Matthijs Groen
	 *
	 *	This program is free software: you can redistribute it and/or modify
	 *	it under the terms of the GNU General Public License as published by
	 *	the Free Software Foundation, either version 3 of the License, or
	 *	(at your option) any later version.
	 *	
	 *	This program is distributed in the hope that it will be useful,
	 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
	 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 *	GNU General Public License for more details.
	 *	
	 *	You should have received a copy of the GNU General Public License
	 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
	 *	
	 */

	$patchName = "reference-init";
	$patchFunc = false; // false by no function, name of function otherwise
	$patchAuthor = "Matthijs Groen";

?>
-- phpMyAdmin SQL Dump
-- version 2.10.3deb1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Nov 22, 2007 at 12:22 PM
-- Server version: 5.0.45
-- PHP Version: 5.2.3-1ubuntu6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Database: `tbb2`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_tm_referencetopic`
-- 

CREATE TABLE IF NOT EXISTS `tbb_tm_referencetopic` (
  `topicID` bigint(20) unsigned NOT NULL default '0',
  `type` enum('topic','board','url') NOT NULL default 'topic',
  `newWindow` enum('yes','no') NOT NULL default 'no',
  `value` varchar(255) NOT NULL default '',
  `created` enum('user','system') NOT NULL default 'user',
  PRIMARY KEY  (`topicID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_tm_referencetopic`
-- 


