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

	$patchName = "update-table";
	$patchFunc = false; // false by no function, name of function otherwise
	$patchAuthor = "Matthijs Groen"; // 100 = IV, 131 = Matthijs, 120 = Guido, 126 = Urvin

?>
CREATE TABLE IF NOT EXISTS `tbb_update_history` (
	`ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`module` VARCHAR( 255 ) NOT NULL ,
	`name` VARCHAR( 255 ) NOT NULL ,
	`author` VARCHAR( 255 ) NOT NULL ,
	`patchDate` DATETIME NOT NULL ,
	`executeDate` DATETIME NOT NULL
) ENGINE = MYISAM ;
