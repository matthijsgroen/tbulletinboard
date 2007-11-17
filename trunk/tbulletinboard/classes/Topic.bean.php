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

	/**
	 *
	 */
	global $ivLibDir;
	require_once($ivLibDir."DataObjects.class.php");

	/**
	 * Usefull for editing schedules
	 */
	class TopicTable extends DataTable {

		var $privateVars;

		function TopicTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "topic");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineInt("boardID", "boardID", false);
			$this->defineDate("date", "date", false);
			$this->defineInt("poster", "poster", false);
			$this->defineText("title", "title", 80, false);
			$this->defineInt("icon", "icon", false);
			//$this->defineInt("typeID", "typeID", false);
			$this->defineInt("views", "views", false);
			$this->defineDefaultValue("views", 0);

			$this->defineEnum("state", "state", array( "online" => "online", "draft" => "draft"), false);
			$this->defineDefaultValue("state", "online");
			$this->defineDate("lastReaction", "lastReaction", false);
			$this->defineBool("closed", "closed", false);
			$this->defineEnum("special", "special", array( "no" => "no", "sticky" => "sticky", "announcement" => "announcement"), false);

			$this->defineText("plugin", "plugin", 40, true);
		}
	}

?>
