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
	class ModuleTable extends DataTable {

		var $privateVars;

		function ModuleTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "module");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineText("group", "group", 40, false);
			$this->defineText("name", "name", 250, false);
			$this->defineText("version", "version", 250, false);
			$this->defineText("author", "author", 250, false);
			$this->defineText("authorUrl", "authorUrl", 250, false);
			$this->defineText("authorEmail", "authorEmail", 250, false);
			$this->defineText("description", "description", 1000, false);
		}
	}

?>
