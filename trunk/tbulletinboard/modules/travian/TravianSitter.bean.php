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
	class TravianSitterTable extends DataTable {

		var $privateVars;

		function TravianSitterTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "travian_sitter");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineInt("userID", "userID", false);
			$this->defineInt("userTravianID", "userTravianID", false);
			$this->defineInt("travianID", "travianID", false);
			$this->defineText("travianName", "travianName", 50, false);
		}
	}

?>
