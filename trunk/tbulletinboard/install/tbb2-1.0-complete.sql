# phpMyAdmin SQL Dump
# version 2.5.4
# http://www.phpmyadmin.net
#
# Host: localhost
# Generation Time: Jan 05, 2004 at 09:30 PM
# Server version: 4.0.2
# PHP Version: 4.3.3
# 
# Database : `tbb2`
# 

# --------------------------------------------------------

#
# Table structure for table `tbb_administrators`
#

CREATE TABLE `tbb_administrators` (
  `userID` bigint(20) unsigned NOT NULL default '0',
  `security` enum('low','medium','high') NOT NULL default 'low',
  `typeAdmin` enum('admin','master') NOT NULL default 'admin',
  `active` enum('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (`userID`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `tbb_avatar`
#

CREATE TABLE `tbb_avatar` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `imgUrl` varchar(50) NOT NULL default '',
  `type` enum('custom','system') NOT NULL default 'custom',
  PRIMARY KEY  (`ID`)
) TYPE=MyISAM AUTO_INCREMENT=15 ;

# --------------------------------------------------------

#
# Table structure for table `tbb_banlist`
#

CREATE TABLE `tbb_banlist` (
  `userID` bigint(20) unsigned NOT NULL default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `reason` varchar(250) NOT NULL default '',
  `byID` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`userID`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `tbb_board`
#

CREATE TABLE `tbb_board` (
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
) TYPE=MyISAM AUTO_INCREMENT=16 ;

# --------------------------------------------------------

#
# Table structure for table `tbb_boardsettings`
#

CREATE TABLE `tbb_boardsettings` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `viewmode` enum('open','hidden','standard') NOT NULL default 'standard',
  `seclevel` enum('low','medium','high','none') NOT NULL default 'none',
  `name` varchar(50) NOT NULL default '',
  `inc_count` enum('yes','no') NOT NULL default 'yes',
  `signatures` enum('yes','no') NOT NULL default 'yes',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `name` (`name`)
) TYPE=MyISAM AUTO_INCREMENT=6 ;

# --------------------------------------------------------

#
# Table structure for table `tbb_boardtags`
#

CREATE TABLE `tbb_boardtags` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `settingID` bigint(20) unsigned NOT NULL default '0',
  `tagID` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `settingID` (`settingID`,`tagID`)
) TYPE=MyISAM AUTO_INCREMENT=69 ;

# --------------------------------------------------------

#
# Table structure for table `tbb_emoticons`
#

CREATE TABLE `tbb_emoticons` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `name` varchar(20) NOT NULL default '',
  `imgUrl` varchar(40) NOT NULL default '',
  `code` varchar(40) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) TYPE=MyISAM AUTO_INCREMENT=30 ;

# --------------------------------------------------------

#
# Table structure for table `tbb_globalsettings`
#

CREATE TABLE `tbb_globalsettings` (
  `ID` bigint(20) NOT NULL auto_increment,
  `online` enum('yes','no') NOT NULL default 'no',
  `offlineReason` varchar(255) NOT NULL default '',
  `onlineTime` datetime NOT NULL default '0000-00-00 00:00:00',
  `version` varchar(20) NOT NULL default '2.0',
  `adminContact` varchar(50) NOT NULL default 'thaisi@thaboo.com',
  `hot_views` bigint(20) NOT NULL default '500',
  `hot_reactions` bigint(20) NOT NULL default '30',
  `avatars` enum('yes','no') NOT NULL default 'yes',
  `customtitles` enum('yes','no') NOT NULL default 'yes',
  `signatures` enum('yes','no') NOT NULL default 'yes',
  `name` varchar(50) NOT NULL default '',
  `topic_page` int(11) NOT NULL default '30',
  `post_page` int(11) NOT NULL default '30',
  `flood_delay` smallint(6) NOT NULL default '10',
  `days_prune` mediumint(9) NOT NULL default '30',
  `help_board` bigint(20) unsigned NOT NULL default '0',
  `sigProfile` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

# --------------------------------------------------------

#
# Table structure for table `tbb_group`
#

CREATE TABLE `tbb_group` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `moduleID` bigint(20) unsigned NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  `groupID` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) TYPE=MyISAM AUTO_INCREMENT=6 ;

# --------------------------------------------------------

#
# Table structure for table `tbb_ignorelist`
#

CREATE TABLE `tbb_ignorelist` (
  `userID` bigint(20) unsigned NOT NULL default '0',
  `ignoredID` bigint(20) unsigned NOT NULL default '0',
  KEY `userID` (`userID`,`ignoredID`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `tbb_kicklist`
#

CREATE TABLE `tbb_kicklist` (
  `userID` bigint(20) unsigned NOT NULL default '0',
  `byID` bigint(20) unsigned NOT NULL default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `reason` varchar(250) NOT NULL default '',
  `boardID` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`userID`),
  KEY `boardID` (`boardID`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `tbb_membermodules`
#

CREATE TABLE `tbb_membermodules` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `name` varchar(20) NOT NULL default '',
  `classname` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `name` (`name`,`classname`)
) TYPE=MyISAM AUTO_INCREMENT=4 ;

# --------------------------------------------------------

#
# Table structure for table `tbb_moderators`
#

CREATE TABLE `tbb_moderators` (
  `userID` bigint(20) unsigned NOT NULL default '0',
  `boardID` bigint(20) unsigned NOT NULL default '0',
  KEY `userID` (`userID`,`boardID`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `tbb_reaction`
#

CREATE TABLE `tbb_reaction` (
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
) TYPE=MyISAM AUTO_INCREMENT=812 ;

# --------------------------------------------------------

#
# Table structure for table `tbb_sendpassword`
#

CREATE TABLE `tbb_sendpassword` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `userID` bigint(20) unsigned NOT NULL default '0',
  `insertTime` datetime NOT NULL default '0000-00-00 00:00:00',
  `validation` char(32) NOT NULL default '',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `userID` (`userID`)
) TYPE=MyISAM AUTO_INCREMENT=10 ;

# --------------------------------------------------------

#
# Table structure for table `tbb_skins`
#

CREATE TABLE `tbb_skins` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `name` varchar(20) NOT NULL default '',
  `folder` varchar(20) NOT NULL default '',
  `comment` varchar(250) NOT NULL default '',
  `compatibility` varchar(250) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

# --------------------------------------------------------

#
# Table structure for table `tbb_textparsing`
#

CREATE TABLE `tbb_textparsing` (
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
) TYPE=MyISAM AUTO_INCREMENT=24 ;

# --------------------------------------------------------

#
# Table structure for table `tbb_tm_discreaction`
#

CREATE TABLE `tbb_tm_discreaction` (
  `reactionID` bigint(20) unsigned NOT NULL default '0',
  `icon` bigint(20) unsigned NOT NULL default '0',
  `title` varchar(80) NOT NULL default '',
  `message` text NOT NULL,
  `signature` enum('yes','no') NOT NULL default 'yes',
  `smilies` enum('yes','no') NOT NULL default 'yes',
  `parseurls` enum('yes','no') NOT NULL default 'yes',
  PRIMARY KEY  (`reactionID`),
  FULLTEXT KEY `message` (`message`),
  FULLTEXT KEY `title` (`title`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `tbb_tm_disctopic`
#

CREATE TABLE `tbb_tm_disctopic` (
  `topicID` bigint(20) unsigned NOT NULL default '0',
  `message` text NOT NULL,
  `signature` enum('yes','no') NOT NULL default 'yes',
  `smilies` enum('yes','no') NOT NULL default 'yes',
  `parseurls` enum('yes','no') NOT NULL default 'yes',
  `lastChange` datetime default NULL,
  `changeby` bigint(20) unsigned NOT NULL default '0',
  PRIMARY KEY  (`topicID`),
  FULLTEXT KEY `message` (`message`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `tbb_topic`
#

CREATE TABLE `tbb_topic` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `boardID` bigint(20) unsigned NOT NULL default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `poster` bigint(20) unsigned NOT NULL default '0',
  `title` varchar(80) NOT NULL default '',
  `icon` bigint(20) unsigned NOT NULL default '0',
  `typeID` bigint(20) unsigned NOT NULL default '0',
  `views` bigint(20) unsigned NOT NULL default '0',
  `state` enum('online','draft') NOT NULL default 'online',
  `lastReaction` datetime NOT NULL default '0000-00-00 00:00:00',
  `closed` enum('no','yes') NOT NULL default 'no',
  `special` enum('no','sticky','announcement') NOT NULL default 'no',
  PRIMARY KEY  (`ID`),
  KEY `lastReaction` (`lastReaction`),
  KEY `poster` (`poster`),
  KEY `boardID` (`boardID`),
  FULLTEXT KEY `title` (`title`)
) TYPE=MyISAM AUTO_INCREMENT=324 ;

# --------------------------------------------------------

#
# Table structure for table `tbb_topicicons`
#

CREATE TABLE `tbb_topicicons` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `name` varchar(30) NOT NULL default '',
  `imgUrl` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`ID`)
) TYPE=MyISAM AUTO_INCREMENT=18 ;

# --------------------------------------------------------

#
# Table structure for table `tbb_topicmodules`
#

CREATE TABLE `tbb_topicmodules` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  `classname` varchar(50) NOT NULL default '',
  `active` enum('yes','no') NOT NULL default 'no',
  `default` enum('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `default` (`default`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

# --------------------------------------------------------

#
# Table structure for table `tbb_topicsubscribe`
#

CREATE TABLE `tbb_topicsubscribe` (
  `topicID` bigint(20) unsigned NOT NULL default '0',
  `userID` bigint(20) unsigned NOT NULL default '0',
  `isMailed` enum('yes','no') NOT NULL default 'yes',
  KEY `topicID` (`topicID`,`userID`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `tbb_users`
#

CREATE TABLE `tbb_users` (
  `ID` bigint(20) unsigned NOT NULL auto_increment,
  `username` varchar(15) NOT NULL default '',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `posts` bigint(20) unsigned NOT NULL default '0',
  `topic` bigint(20) unsigned NOT NULL default '0',
  `nickname` varchar(30) NOT NULL default '',
  `avatarID` bigint(20) unsigned NOT NULL default '0',
  `customtitle` varchar(50) NOT NULL default '',
  `homepage` varchar(50) NOT NULL default '',
  `last_seen` datetime NOT NULL default '0000-00-00 00:00:00',
  `signature` varchar(255) NOT NULL default '',
  `logged_in` enum('yes','no') NOT NULL default 'no',
  `last_session` varchar(60) NOT NULL default '',
  PRIMARY KEY  (`ID`),
  UNIQUE KEY `username` (`username`,`nickname`),
  FULLTEXT KEY `nickname` (`nickname`)
) TYPE=MyISAM AUTO_INCREMENT=26 ;

# --------------------------------------------------------

#
# Table structure for table `tbb_usersettings`
#

CREATE TABLE `tbb_usersettings` (
  `userID` bigint(20) unsigned NOT NULL default '0',
  `password` varchar(32) NOT NULL default '',
  `email` varchar(60) NOT NULL default '',
  `showEmoticon` enum('yes','no') NOT NULL default 'yes',
  `showSignature` enum('yes','no') NOT NULL default 'yes',
  `skin` bigint(20) unsigned NOT NULL default '0',
  `showAvatar` enum('yes','no') NOT NULL default 'yes',
  `daysPrune` bigint(20) NOT NULL default '30',
  PRIMARY KEY  (`userID`)
) TYPE=MyISAM;
