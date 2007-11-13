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
	class BoardTopicsTable extends DataTable {

		var $privateVars;

		function BoardTopicsTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "boardtopic");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineInt("settingID", "settingID", false);
			//$this->defineInt("topicModuleID", "topicModuleID", false);
			$this->defineText("plugin", "plugin", 40, false);
			$this->defineBool("default", "default");
		}
	}

?>