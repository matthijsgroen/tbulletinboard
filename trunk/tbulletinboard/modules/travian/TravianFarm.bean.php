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
	class TravianFarmTable extends DataTable {

		var $privateVars;

		function TravianFarmTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "travian_farm");

			$this->defineInt("ID", "id", false);
			$this->setPrimaryKey("ID");
			$this->defineInt("travianID", "travianID", false);
			$this->defineBool("farm", "farm", false);
		}
	}

?>
