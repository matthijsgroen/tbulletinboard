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

	importClass("orm.DataObjects");
	
	/**
	 * Usefull for editing schedules
	 */
	class SendPasswordTable extends DataTable {

		var $privateVars;

		function SendPasswordTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "sendpassword");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineInt("userID", "userID", false);
			$this->defineText("validation", "validation", 32, false);
			$this->defineDate("insertTime", "insertTime", false);
		}
	}

/**
ID 	bigint(20) 	UNSIGNED	No 	 	auto_increment
userID 	bigint(20) 	UNSIGNED	No 	0
insertTime 	datetime	 	No 	0000-00-00 00:00:00
validation 	char(32)	 	No
*/

?>
