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
	require_once($ivLibDir."DataObjects.class.php");

	/**
	 * Usefull for editing schedules
	 */
	class StructureCacheTable extends DataTable {

		var $privateVars;

		function StructureCacheTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "structurecache");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");

			$this->defineText("structureCache", "structureCache", 2000, true);
			$this->defineDate("date", "date", false);
		}
	}

?>