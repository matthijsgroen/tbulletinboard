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

	$patchName = "travian-init";
	$patchFunc = false; // false by no function, name of function otherwise
	$patchAuthor = "Matthijs Groen"; // 100 = IV, 131 = Matthijs, 120 = Guido, 126 = Urvin

?>
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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

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

