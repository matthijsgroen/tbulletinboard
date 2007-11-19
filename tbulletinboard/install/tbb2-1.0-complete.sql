-- phpMyAdmin SQL Dump
-- version 2.10.3deb1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Nov 19, 2007 at 10:56 PM
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `tbb_globalsettings`
-- 

INSERT INTO `tbb_globalsettings` (`ID`, `online`, `offlineReason`, `onlineTime`, `version`, `adminContact`, `hotViews`, `hotReactions`, `avatars`, `customtitles`, `signatures`, `name`, `topicPage`, `postPage`, `floodDelay`, `daysPrune`, `helpBoard`, `sigProfile`, `referenceID`, `binboard`) VALUES 
(1, 'yes', '', '0000-00-00 00:00:00', '2.0', 'matthijs.groen@gmail.com', 500, 30, 'yes', 'yes', 'yes', 'TBB2', 30, 30, 10, 30, NULL, NULL, NULL, NULL);

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
-- Table structure for table `tbb_message_global`
-- 

CREATE TABLE IF NOT EXISTS `tbb_message_global` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `settingID` bigint(20) unsigned NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `tbb_message_global`
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `tbb_structurecache`
-- 

INSERT INTO `tbb_structurecache` (`ID`, `date`, `structureCache`) VALUES 
(1, '2007-11-19 22:55:04', 'a:2:{s:9:"structure";a:7:{s:2:"ID";i:0;s:4:"name";s:9:"Overzicht";s:7:"comment";s:0:"";s:10:"settingsID";b:0;s:9:"readGroup";b:1;s:6:"hidden";b:0;s:6:"childs";a:0:{}}s:4:"list";a:1:{i:0;a:7:{s:2:"ID";i:0;s:8:"parentID";b:0;s:4:"name";s:9:"Overzicht";s:7:"comment";s:0:"";s:10:"settingsID";b:0;s:9:"readGroup";b:1;s:6:"hidden";b:0;}}}');

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `tbb_textparsing`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_tm_discreaction`
-- 

CREATE TABLE IF NOT EXISTS `tbb_tm_discreaction` (
  `reactionID` bigint(20) unsigned NOT NULL default '0',
  `icon` bigint(20) unsigned NOT NULL default '0',
  `title` varchar(80) NOT NULL default '',
  `message` text NOT NULL,
  `signature` enum('yes','no') NOT NULL default 'yes',
  `smilies` enum('yes','no') NOT NULL default 'yes',
  `parseurls` enum('yes','no') NOT NULL default 'yes',
  PRIMARY KEY  (`reactionID`),
  FULLTEXT KEY `title` (`title`,`message`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_tm_discreaction`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_tm_disctopic`
-- 

CREATE TABLE IF NOT EXISTS `tbb_tm_disctopic` (
  `topicID` bigint(20) unsigned NOT NULL default '0',
  `message` text NOT NULL,
  `signature` enum('yes','no') NOT NULL default 'yes',
  `smilies` enum('yes','no') NOT NULL default 'yes',
  `parseurls` enum('yes','no') NOT NULL default 'yes',
  `lastChange` datetime default NULL,
  `changeby` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`topicID`),
  FULLTEXT KEY `message` (`message`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_tm_disctopic`
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `tbb_tm_reviewtypes`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_tm_votereaction`
-- 

CREATE TABLE IF NOT EXISTS `tbb_tm_votereaction` (
  `reactionID` bigint(20) unsigned NOT NULL default '0',
  `icon` bigint(20) unsigned NOT NULL default '0',
  `title` varchar(80) NOT NULL default '',
  `message` text NOT NULL,
  `signature` enum('yes','no') NOT NULL default 'yes',
  `smilies` enum('yes','no') NOT NULL default 'yes',
  `parseurls` enum('yes','no') NOT NULL default 'yes',
  PRIMARY KEY  (`reactionID`),
  FULLTEXT KEY `title` (`title`,`message`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_tm_votereaction`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_tm_votetopic`
-- 

CREATE TABLE IF NOT EXISTS `tbb_tm_votetopic` (
  `topicID` bigint(20) unsigned NOT NULL default '0',
  `message` text NOT NULL,
  `signature` enum('yes','no') NOT NULL default 'yes',
  `smilies` enum('yes','no') NOT NULL default 'yes',
  `parseurls` enum('yes','no') NOT NULL default 'yes',
  `lastChange` datetime default NULL,
  `changeby` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`topicID`),
  FULLTEXT KEY `message` (`message`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `tbb_tm_votetopic`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_tm_votevote`
-- 

CREATE TABLE IF NOT EXISTS `tbb_tm_votevote` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `userID` bigint(20) unsigned NOT NULL,
  `topicID` bigint(20) unsigned NOT NULL,
  `vote` enum('yes','no') NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `topicID` (`topicID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `tbb_tm_votevote`
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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
-- Table structure for table `tbb_travian_details`
-- 

CREATE TABLE IF NOT EXISTS `tbb_travian_details` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `userID` bigint(20) unsigned NOT NULL,
  `travianID` int(10) unsigned NOT NULL,
  `woodph` int(11) default NULL,
  `clayph` int(11) default NULL,
  `ironph` int(11) default NULL,
  `cropph` int(11) default NULL,
  `unittype1` int(10) unsigned default NULL,
  `unittype2` int(10) unsigned default NULL,
  `unittype3` int(10) unsigned default NULL,
  `unittype4` int(10) unsigned default NULL,
  `unittype5` int(10) unsigned default NULL,
  `unittype6` int(10) unsigned default NULL,
  `unittype7` int(10) unsigned default NULL,
  `unittype8` int(10) unsigned default NULL,
  `unittype9` int(10) unsigned default NULL,
  `unittype10` int(10) unsigned default NULL,
  `herolevel` int(10) unsigned default NULL,
  `heroxp` int(10) unsigned default NULL,
  `woodtrade` enum('yes','no') NOT NULL default 'no',
  `claytrade` enum('yes','no') NOT NULL default 'no',
  `irontrade` enum('yes','no') NOT NULL default 'no',
  `croptrade` enum('yes','no') NOT NULL default 'no',
  `camping` enum('yes','no','unknown') NOT NULL default 'unknown',
  `lastUpdated` datetime NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `tbb_travian_details`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_travian_farm`
-- 

CREATE TABLE IF NOT EXISTS `tbb_travian_farm` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `travianID` bigint(20) unsigned NOT NULL,
  `farm` enum('yes','no') NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `travianID` (`travianID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `tbb_travian_farm`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_travian_sitter`
-- 

CREATE TABLE IF NOT EXISTS `tbb_travian_sitter` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `userID` bigint(20) unsigned NOT NULL,
  `userTravianID` int(10) unsigned NOT NULL,
  `travianID` int(10) unsigned NOT NULL,
  `travianName` varchar(50) NOT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `tbb_travian_sitter`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `tbb_travian_user`
-- 

CREATE TABLE IF NOT EXISTS `tbb_travian_user` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `tbbID` bigint(20) unsigned NOT NULL,
  `travianID` bigint(20) unsigned NOT NULL,
  `allianceID` bigint(20) unsigned NOT NULL,
  `travianName` varchar(255) NOT NULL,
  `pop` bigint(20) unsigned NOT NULL,
  `vill` bigint(20) unsigned NOT NULL,
  `race` smallint(5) unsigned NOT NULL,
  `alliance` varchar(255) NOT NULL,
  PRIMARY KEY  (`ID`),
  KEY `tbbID` (`tbbID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `tbb_travian_user`
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

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


-- --------------------------------------------------------

-- 
-- Table structure for table `x_world`
-- 

CREATE TABLE IF NOT EXISTS `x_world` (
  `id` int(9) unsigned NOT NULL default '0',
  `x` smallint(3) NOT NULL default '0',
  `y` smallint(3) NOT NULL default '0',
  `tid` tinyint(1) unsigned NOT NULL default '0',
  `vid` int(9) unsigned NOT NULL default '0',
  `village` varchar(20) NOT NULL default '',
  `uid` int(9) NOT NULL default '0',
  `player` varchar(20) NOT NULL default '',
  `aid` int(9) unsigned NOT NULL default '0',
  `alliance` varchar(8) NOT NULL default '',
  `population` smallint(5) unsigned NOT NULL default '0',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- 
-- Dumping data for table `x_world`
-- 

