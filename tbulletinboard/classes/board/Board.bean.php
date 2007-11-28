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
	class BoardTable extends DataTable {

		var $privateVars;

		function BoardTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "board");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineInt("parentID", "parentID", true);
			$this->defineText("name", "name", 80, false);
			$this->defineInt("read", "read", false);
			$this->defineInt("write", "write", false);
			$this->defineInt("topic", "topic", false);
			$this->defineText("comment", "comment", 250, false);
			$this->defineInt("order", "order", false);
			$this->defineInt("settingsID", "settingsID", false);
			$this->defineInt("views", "boardviews", false);
			$this->defineText("type", "type", 20, false);
			$this->defineDefaultValue("type", "global");
		}
	}

?>
