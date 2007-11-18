<?php
	/**
	 * A Database Bean
	 *
	 *@package Beans
	 *@author Matthijs Groen (matthijs at ivinity.nl)
	 *@version 1.0
	 */

	/**
	 *
	 */
	global $ivLibDir;
	require_once($ivLibDir."DataObjects.class.php");

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
