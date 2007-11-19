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
	class TravianPlaceTable extends DataTable {

		var $privateVars;

		function TravianPlaceTable(&$database) {
			$this->DataTable($database, "x_world");

			$this->defineInt("ID", "id", false);
			$this->setPrimaryKey("ID");
			$this->defineInt("x", "x", false);
			$this->defineInt("y", "y", false);
			$this->defineInt("race", "tid", false);
			$this->defineInt("villageID", "vid", false);
			$this->defineText("villageName", "village", 20, false);
			$this->defineInt("playerID", "uid", false);
			$this->defineText("playerName", "player", 20, false);
			$this->defineInt("allianceID", "aid", false);
			$this->defineText("allianceName", "alliance", 20, false);
			$this->defineInt("population", "population", false);
		}
	}

?>
