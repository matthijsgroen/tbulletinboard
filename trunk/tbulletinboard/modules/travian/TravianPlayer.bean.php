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
	global $libraryClassDir;
	require_once($libraryClassDir."DataObjects.class.php");

	/**
	 * Usefull for editing schedules
	 */
	class TravianPlayerTable extends DataTable {

		var $privateVars;

		function TravianPlayerTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "travian_user");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineInt("tbbID", "tbbID", false);
			$this->defineInt("travianID", "travianID", false);
			$this->defineText("travianName", "travianName", 20, false);
			$this->defineInt("allianceID", "allianceID", false);
			$this->defineInt("pop", "pop", false);
			$this->defineInt("vill", "vill", false);
			$this->defineInt("race", "race", false);
			$this->defineText("allianceName", "alliance", 20, false);
		}
	}

?>
