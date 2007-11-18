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
	require_once($libraryClassDir."DataObjects.class.php");

	/**
	 * Usefull for editing schedules
	 */
	class ModuleTable extends DataTable {

		var $privateVars;

		function ModuleTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "module");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineText("group", "group", 40, false);
			$this->defineText("name", "name", 250, false);
			$this->defineText("version", "version", 250, false);
			$this->defineText("author", "author", 250, false);
			$this->defineText("authorUrl", "authorUrl", 250, false);
			$this->defineText("authorEmail", "authorEmail", 250, false);
			$this->defineText("description", "description", 1000, false);
		}
	}

?>
