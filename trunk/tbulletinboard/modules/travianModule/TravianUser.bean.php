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
	class TravianPlayerTable extends DataTable {

		var $privateVars;

		function TravianPlayerTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "travian_user");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineInt("tbbID", "tbbID", false);
			$this->defineInt("travianID", "travianID", false);
			$this->defineText("travianName", "travianName", 20, false);
			$this->defineInt("pop", "pop", false);
			$this->defineInt("vill", "vill", false);
			$this->defineInt("race", "race", false);
			$this->defineText("allianceName", "alliance", 20, false);
		}
	}

?>
