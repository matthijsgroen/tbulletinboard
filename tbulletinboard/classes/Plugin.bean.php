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
	require_once($ivLibDir."DataObjects.class.php");

	/**
	 * Usefull for editing schedules
	 */
	class PluginTable extends DataTable {

		var $privateVars;

		function PluginTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "plugin");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");

			$this->defineText("name", "name", 250, false);
			$this->defineText("version", "version", 250, false);
			$this->defineInt("build", "build", false);
			$this->defineText("group", "group", 40, false);
			$this->defineText("type", "type", 250, false);
			$this->defineBool("active", "active");
			$this->defineDefaultValue('active', false);
			$this->defineDate("installDate", "installDate", false);
			$this->defineDefaultValue('installDate', new LibDateTime());
			$this->defineText("filename", "filename", 250, false);
			$this->defineText("classname", "classname", 250, false);
		}
	}

?>
