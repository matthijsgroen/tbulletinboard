-- phpMyAdmin SQL Dump
-- version 2.10.3deb1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Nov 25, 2007 at 10:11 AM
-- Server version: 5.0.45
-- PHP Version: 5.2.3-1ubuntu6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Database: `tbb_blank`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_administrators`
-- 

CREATE TABLE IF NOT EXISTS `tbb_administrators` (
  `userID` bigint(20) unsigned NOT NULL default '0',
  `security` enum('low','medium','high') NOT NULL default 'low',
  `typeAdmin` enum('admin','master') NOT NULL default 'admin',
  `active` enum('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_administrators`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_avatar`
-- 

CREATE TABLE IF NOT EXISTS `tbb_avatar` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `imgUrl` varchar(50) NOT NULL default '',
  `type` enum('custom','system') NOT NULL default 'custom',
  `userID` bigint(20) default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_avatar`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_banlist`
-- 

CREATE TABLE IF NOT EXISTS `tbb_banlist` (
  `userID` bigint(20) unsigned NOT NULL default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `reason` varchar(250) NOT NULL default '',
  `byID` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_banlist`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_board`
-- 

CREATE TABLE IF NOT EXISTS `tbb_board` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `parentID` bigint(20) unsigned NOT NULL default '0',
  `name` varchar(80) NOT NULL default '',
  `read` bigint(20) unsigned NOT NULL default '0',
  `write` bigint(20) unsigned NOT NULL default '0',
  `topic` bigint(20) unsigned NOT NULL default '0',
  `comment` varchar(250) NOT NULL default '',
  `order` int(11) NOT NULL default '0',
  `settingsID` bigint(20) unsigned NOT NULL default '0',
  `boardviews` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `parentID` (`parentID`),
  KEY `read` (`read`,`write`,`topic`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_board`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_boardcache`
-- 

CREATE TABLE IF NOT EXISTS `tbb_boardcache` (
  `ID` bigint(20) unsigned NOT NULL default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `posts` bigint(20) unsigned NOT NULL default '0',
  `topics` bigint(20) unsigned NOT NULL default '0',
  `postDate` datetime default NULL,
  `postUser` bigint(20) unsigned default NULL,
  `topicTitle` varchar(255) default NULL,
  `topicID` bigint(20) unsigned default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_boardcache`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_boardsettings`
-- 

CREATE TABLE IF NOT EXISTS `tbb_boardsettings` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `viewmode` enum('open','hidden','standard','openHidden') NOT NULL default 'standard',
  `seclevel` enum('low','medium','high','none') NOT NULL default 'none',
  `name` varchar(50) NOT NULL default '',
  `inc_count` enum('yes','no') NOT NULL default 'yes',
  `signatures` enum('yes','no') NOT NULL default 'yes',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_boardsettings`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_boardtags`
-- 

CREATE TABLE IF NOT EXISTS `tbb_boardtags` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `settingID` bigint(20) unsigned NOT NULL default '0',
  `tagID` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `settingID` (`settingID`,`tagID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_boardtags`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_boardtopic`
-- 

CREATE TABLE IF NOT EXISTS `tbb_boardtopic` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `settingID` bigint(20) unsigned NOT NULL default '0',
  `plugin` varchar(40) default NULL,
  `default` enum('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (`ID`),
  KEY `settingID` (`settingID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_boardtopic`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_emoticons`
-- 

CREATE TABLE IF NOT EXISTS `tbb_emoticons` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `name` varchar(20) NOT NULL default '',
  `imgUrl` varchar(40) NOT NULL default '',
  `code` varchar(40) NOT NULL default '',
  `order` int(10) unsigned default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_emoticons`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_globalsettings`
-- 

CREATE TABLE IF NOT EXISTS `tbb_globalsettings` (
  `ID` bigint(20) NOT NULL auto_increment,
  `online` enum('yes','no') NOT NULL default 'no',
  `offlineReason` varchar(255) NOT NULL default '',
  `onlineTime` datetime NOT NULL default '0000-00-00 00:00:00',
  `version` varchar(20) NOT NULL default '2.0',
  `adminContact` varchar(50) NOT NULL default 'thaisi@thaboo.com',
  `hotViews` bigint(20) NOT NULL default '500',
  `hotReactions` bigint(20) NOT NULL default '30',
  `avatars` enum('yes','no') NOT NULL default 'yes',
  `customtitles` enum('yes','no') NOT NULL default 'yes',
  `signatures` enum('yes','no') NOT NULL default 'yes',
  `name` varchar(50) NOT NULL default '',
  `topicPage` int(11) NOT NULL default '30',
  `postPage` int(11) NOT NULL default '30',
  `floodDelay` smallint(6) NOT NULL default '10',
  `daysPrune` mediumint(9) NOT NULL default '30',
  `helpBoard` bigint(20) unsigned default NULL,
  `sigProfile` bigint(20) unsigned default NULL,
  `referenceID` varchar(40) default NULL,
  `binboard` bigint(20) unsigned default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_globalsettings`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_group`
-- 

CREATE TABLE IF NOT EXISTS `tbb_group` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `moduleID` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL default '',
  `groupID` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_group`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_ignorelist`
-- 

CREATE TABLE IF NOT EXISTS `tbb_ignorelist` (
  `userID` bigint(20) unsigned NOT NULL default '0',
  `ignoredID` bigint(20) unsigned NOT NULL default '0',
  KEY `userID` (`userID`,`ignoredID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_ignorelist`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_kicklist`
-- 

CREATE TABLE IF NOT EXISTS `tbb_kicklist` (
  `userID` bigint(20) unsigned NOT NULL default '0',
  `byID` bigint(20) unsigned NOT NULL default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `reason` varchar(250) NOT NULL default '',
  `boardID` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`userID`),
  KEY `boardID` (`boardID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_kicklist`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_moderators`
-- 

CREATE TABLE IF NOT EXISTS `tbb_moderators` (
  `userID` bigint(20) unsigned NOT NULL default '0',
  `boardID` bigint(20) unsigned NOT NULL default '0',
  KEY `userID` (`userID`,`boardID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_moderators`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_module`
-- 

CREATE TABLE IF NOT EXISTS `tbb_module` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `group` varchar(40) NOT NULL default '',
  `name` varchar(255) default NULL,
  `version` varchar(20) default NULL,
  `author` varchar(255) default NULL,
  `authorEmail` varchar(255) default NULL,
  `authorUrl` varchar(255) default NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_module`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_plugin`
-- 

CREATE TABLE IF NOT EXISTS `tbb_plugin` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `version` varchar(30) NOT NULL default '',
  `build` int(10) unsigned NOT NULL default '0',
  `group` varchar(30) NOT NULL default '',
  `type` varchar(20) NOT NULL default '',
  `active` enum('yes','no') NOT NULL default 'no',
  `installDate` datetime NOT NULL default '0000-00-00 00:00:00',
  `filename` varchar(255) NOT NULL default '',
  `classname` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_plugin`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_reaction`
-- 

CREATE TABLE IF NOT EXISTS `tbb_reaction` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `topicID` bigint(20) unsigned NOT NULL default '0',
  `poster` bigint(20) unsigned NOT NULL default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `lastchange` datetime default NULL,
  `changeby` bigint(20) unsigned default NULL,
  `state` enum('online','draft') NOT NULL default 'online',
  PRIMARY KEY  (`ID`),
  KEY `topicID` (`topicID`),
  KEY `poster` (`poster`),
  KEY `date` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_reaction`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_searchcache`
-- 

CREATE TABLE IF NOT EXISTS `tbb_searchcache` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `sessionID` varchar(50) NOT NULL default '',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `searchCache` longtext NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_searchcache`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_sendpassword`
-- 

CREATE TABLE IF NOT EXISTS `tbb_sendpassword` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `userID` bigint(20) unsigned NOT NULL default '0',
  `insertTime` datetime NOT NULL default '0000-00-00 00:00:00',
  `validation` char(32) NOT NULL default '',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `userID` (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_sendpassword`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_session`
-- 

CREATE TABLE IF NOT EXISTS `tbb_session` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `sessionID` varchar(255) NOT NULL,
  `lastActive` datetime NOT NULL,
  `userID` bigint(20) unsigned default NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_session`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_skins`
-- 

CREATE TABLE IF NOT EXISTS `tbb_skins` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `name` varchar(20) NOT NULL default '',
  `folder` varchar(20) NOT NULL default '',
  `comment` varchar(250) NOT NULL default '',
  `compatibility` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_skins`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_structurecache`
-- 

CREATE TABLE IF NOT EXISTS `tbb_structurecache` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `structureCache` text,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_structurecache`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_textparsing`
-- 

CREATE TABLE IF NOT EXISTS `tbb_textparsing` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `active` enum('yes','no') NOT NULL default 'no',
  `startName` varchar(20) NOT NULL default '',
  `acceptAll` enum('yes','no') NOT NULL default 'no',
  `acceptedParameters` varchar(255) NOT NULL default '',
  `endTags` varchar(50) NOT NULL default '',
  `endTagRequired` enum('yes','no') NOT NULL default 'yes',
  `htmlReplace` text NOT NULL,
  `allowParents` varchar(255) NOT NULL default '',
  `allowChilds` varchar(50) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `example` text NOT NULL,
  `wordBreaks` enum('all','none','parameter','text') NOT NULL default 'all',
  PRIMARY KEY  (`ID`),
  KEY `active` (`active`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_textparsing`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_topic`
-- 

CREATE TABLE IF NOT EXISTS `tbb_topic` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `boardID` bigint(20) unsigned NOT NULL default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `poster` bigint(20) unsigned NOT NULL default '0',
  `title` varchar(80) NOT NULL default '',
  `icon` bigint(20) unsigned NOT NULL default '0',
  `views` bigint(20) unsigned NOT NULL default '0',
  `state` enum('online','draft') NOT NULL default 'online',
  `lastReaction` datetime NOT NULL default '0000-00-00 00:00:00',
  `closed` enum('no','yes') NOT NULL default 'no',
  `special` enum('no','sticky','announcement') NOT NULL default 'no',
  `plugin` varchar(40) default NULL,
  PRIMARY KEY  (`ID`),
  KEY `lastReaction` (`lastReaction`),
  KEY `poster` (`poster`),
  KEY `boardID` (`boardID`),
  KEY `plugin` (`plugin`),
  FULLTEXT KEY `title` (`title`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_topic`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_topicicons`
-- 

CREATE TABLE IF NOT EXISTS `tbb_topicicons` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `name` varchar(30) NOT NULL default '',
  `imgUrl` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_topicicons`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_topicread`
-- 

CREATE TABLE IF NOT EXISTS `tbb_topicread` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `UserID` bigint(20) unsigned NOT NULL default '0',
  `TopicID` bigint(20) unsigned NOT NULL default '0',
  `lastRead` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`ID`),
  KEY `UserID` (`UserID`,`TopicID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_topicread`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_topicsubscribe`
-- 

CREATE TABLE IF NOT EXISTS `tbb_topicsubscribe` (
  `topicID` bigint(20) unsigned NOT NULL default '0',
  `userID` bigint(20) unsigned NOT NULL default '0',
  `isMailed` enum('yes','no') NOT NULL default 'yes',
  KEY `topicID` (`topicID`,`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_topicsubscribe`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_users`
-- 

CREATE TABLE IF NOT EXISTS `tbb_users` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `username` varchar(15) NOT NULL default '',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `posts` bigint(20) unsigned NOT NULL default '0',
  `topic` bigint(20) unsigned NOT NULL default '0',
  `nickname` varchar(30) NOT NULL default '',
  `avatarID` bigint(20) unsigned NOT NULL default '0',
  `customtitle` varchar(50) NOT NULL default '',
  `last_seen` datetime NOT NULL default '0000-00-00 00:00:00',
  `signature` text NOT NULL,
  `logged_in` enum('yes','no') NOT NULL default 'no',
  `last_session` varchar(60) NOT NULL default '',
  `last_logged` datetime default NULL,
  `read_threshold` datetime default NULL,
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `username` (`username`,`nickname`),
  FULLTEXT KEY `nickname` (`nickname`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_users`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_usersettings`
-- 

CREATE TABLE IF NOT EXISTS `tbb_usersettings` (
  `userID` bigint(20) unsigned NOT NULL default '0',
  `password` varchar(32) NOT NULL default '',
  `email` varchar(60) NOT NULL default '',
  `showEmoticon` enum('yes','no') NOT NULL default 'yes',
  `showSignature` enum('yes','no') NOT NULL default 'yes',
  `skin` bigint(20) unsigned NOT NULL default '0',
  `showAvatar` enum('yes','no') NOT NULL default 'yes',
  `daysPrune` bigint(20) default NULL,
  `topicPage` bigint(20) unsigned default NULL,
  `reactionPage` bigint(20) unsigned default NULL,
  PRIMARY KEY  (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_usersettings`
-- 

