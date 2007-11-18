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
	class TravianDetailsTable extends DataTable {

		var $privateVars;

		function TravianDetailsTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "travian_details");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineInt("userID", "userID", false);
			$this->defineInt("travianID", "travianID", false);

			$this->defineInt("woodPerHour", "woodph", true);
			$this->defineInt("clayPerHour", "clayph", true);
			$this->defineInt("ironPerHour", "ironph", true);
			$this->defineInt("cropPerHour", "cropph", true);

			$this->defineInt("unitType1", "unittype1", true);
			$this->defineInt("unitType2", "unittype2", true);
			$this->defineInt("unitType3", "unittype3", true);
			$this->defineInt("unitType4", "unittype4", true);
			$this->defineInt("unitType5", "unittype5", true);
			$this->defineInt("unitType6", "unittype6", true);
			$this->defineInt("unitType7", "unittype7", true);
			$this->defineInt("unitType8", "unittype8", true);
			$this->defineInt("unitType9", "unittype9", true);
			$this->defineInt("unitType10", "unittype10", true);

			$this->defineInt("heroLevel", "herolevel", false);
			$this->defineInt("heroXP", "heroxp", false);

			$this->defineBool("woodTrade", "woodtrade", false);
			$this->defineDefaultValue("woodTrade", false);
			$this->defineBool("clayTrade", "claytrade", false);
			$this->defineDefaultValue("clayTrade", false);
			$this->defineBool("ironTrade", "irontrade", false);
			$this->defineDefaultValue("ironTrade", false);
			$this->defineBool("cropTrade", "croptrade", false);
			$this->defineDefaultValue("cropTrade", false);

			$this->defineEnum("camping", "camping", array("yes" => "yes", "no" => "no", "unknown" => "unknown"), false);
			$this->defineDefaultValue("camping", "unknown");

			$this->defineDate("lastUpdated", "lastUpdated", false);
		}
	}

?>
