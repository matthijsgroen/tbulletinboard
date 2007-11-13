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
