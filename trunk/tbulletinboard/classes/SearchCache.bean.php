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
	class SearchCacheTable extends DataTable {

		var $privateVars;

		function SearchCacheTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "searchcache");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineText("sessionID", "sessionID", 40, false);
			$this->defineDate("date", "date", false);
			$this->defineText("searchCache", "searchCache", 2000, false);
		}
	}

?>