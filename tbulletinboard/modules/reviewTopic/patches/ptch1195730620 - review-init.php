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

	$patchName = "review-init";
	$patchFunc = false; // false by no function, name of function otherwise
	$patchAuthor = "Matthijs Groen";

?>
-- phpMyAdmin SQL Dump
-- version 2.10.3deb1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Nov 22, 2007 at 12:23 PM
-- Server version: 5.0.45
-- PHP Version: 5.2.3-1ubuntu6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Database: `tbb2`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_tm_reviewfields`
-- 

CREATE TABLE IF NOT EXISTS `tbb_tm_reviewfields` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `reviewType` bigint(20) unsigned NOT NULL default '0',
  `name` varchar(20) NOT NULL default '',
  `prefix` varchar(10) NOT NULL default '',
  `postfix` varchar(10) NOT NULL default '',
  `type` enum('text','number','select','float','time','date') NOT NULL default 'text',
  PRIMARY KEY  (`ID`),
  KEY `reviewType` (`reviewType`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_tm_reviewfields`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_tm_reviewfieldvalues`
-- 

CREATE TABLE IF NOT EXISTS `tbb_tm_reviewfieldvalues` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `fieldID` bigint(20) unsigned NOT NULL default '0',
  `value` varchar(40) NOT NULL default '',
  PRIMARY KEY  (`ID`),
  KEY `fieldID` (`fieldID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_tm_reviewfieldvalues`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_tm_reviewreaction`
-- 

CREATE TABLE IF NOT EXISTS `tbb_tm_reviewreaction` (
  `reactionID` bigint(20) unsigned NOT NULL default '0',
  `icon` bigint(20) unsigned NOT NULL default '0',
  `title` varchar(80) default NULL,
  `message` text NOT NULL,
  `signature` enum('yes','no') NOT NULL default 'yes',
  `smilies` enum('yes','no') NOT NULL default 'yes',
  `parseurls` enum('yes','no') NOT NULL default 'yes',
  `score` float default NULL,
  `replyType` enum('comment','review') NOT NULL default 'comment',
  PRIMARY KEY  (`reactionID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_tm_reviewreaction`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_tm_reviewreactionscores`
-- 

CREATE TABLE IF NOT EXISTS `tbb_tm_reviewreactionscores` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `reactionID` bigint(20) unsigned NOT NULL default '0',
  `scoreID` bigint(20) unsigned NOT NULL default '0',
  `value` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `reactionID` (`reactionID`,`scoreID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_tm_reviewreactionscores`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_tm_reviewscores`
-- 

CREATE TABLE IF NOT EXISTS `tbb_tm_reviewscores` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `reviewType` bigint(20) unsigned NOT NULL default '0',
  `name` varchar(40) NOT NULL default '',
  `maxScore` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `reviewType` (`reviewType`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_tm_reviewscores`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_tm_reviewtopic`
-- 

CREATE TABLE IF NOT EXISTS `tbb_tm_reviewtopic` (
  `topicID` bigint(20) unsigned NOT NULL default '0',
  `message` text NOT NULL,
  `signature` enum('yes','no') NOT NULL default 'yes',
  `smilies` enum('yes','no') NOT NULL default 'yes',
  `parseurls` enum('yes','no') NOT NULL default 'yes',
  `lastChange` datetime default NULL,
  `changeby` bigint(20) unsigned default NULL,
  `reviewType` bigint(20) unsigned NOT NULL default '0',
  `score` float NOT NULL default '0',
  `userScore` float default NULL,
  PRIMARY KEY  (`topicID`),
  KEY `reviewType` (`reviewType`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_tm_reviewtopic`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_tm_reviewtopicfields`
-- 

CREATE TABLE IF NOT EXISTS `tbb_tm_reviewtopicfields` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `topicID` bigint(20) unsigned NOT NULL default '0',
  `fieldID` bigint(20) unsigned NOT NULL default '0',
  `intValue` bigint(20) unsigned default NULL,
  `textValue` varchar(40) default NULL,
  `floatValue` float default NULL,
  `dateValue` datetime default NULL,
  `timeValue` time default NULL,
  PRIMARY KEY  (`ID`),
  KEY `topicID` (`topicID`,`fieldID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_tm_reviewtopicfields`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_tm_reviewtopicscores`
-- 

CREATE TABLE IF NOT EXISTS `tbb_tm_reviewtopicscores` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `topicID` bigint(20) unsigned NOT NULL default '0',
  `scoreID` bigint(20) unsigned NOT NULL default '0',
  `value` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `topicID` (`topicID`,`scoreID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_tm_reviewtopicscores`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_tm_reviewtypes`
-- 

CREATE TABLE IF NOT EXISTS `tbb_tm_reviewtypes` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `name` varchar(40) NOT NULL default '',
  `maxValue` float NOT NULL default '0',
  `prefix` varchar(10) NOT NULL default '',
  `postfix` varchar(10) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_tm_reviewtypes`
-- 


