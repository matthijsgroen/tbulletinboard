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
	class AdministratorTable extends DataTable {

		var $privateVars;

		function AdministratorTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "administrators");

			$this->defineInt("ID", "UserID", false);
			$this->setPrimaryKey("ID");
			$this->defineEnum("security", "security", array(0 => "low", 1 => "medium", 2 => "high"), false);
			$this->defineEnum("typeAdmin", "typeAdmin", array(0 => "admin", 1 => "master"), false);
			$this->defineBool("active", "active", false);
		}
	}

?>
