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
	class ReactionTable extends DataTable {

		var $privateVars;

		function ReactionTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "reaction");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineInt("topicID", "topicID", false);
			$this->defineInt("poster", "poster", false);
			$this->defineDate("date", "date", false);
			$this->defineDate("lastChange", "lastchange", true);
			$this->defineInt("changeBy", "changeby", true);
			$this->defineEnum("state", "state", array( 1 => "online", 2 => "draft"), false);
			$this->defineDefaultValue("state", "online");
		}
	}

?>
