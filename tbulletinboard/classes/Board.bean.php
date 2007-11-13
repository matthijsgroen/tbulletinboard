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
	class BoardTable extends DataTable {

		var $privateVars;

		function BoardTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "board");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineInt("parentID", "parentID", true);
			$this->defineText("name", "name", 80, false);
			$this->defineInt("read", "read", false);
			$this->defineInt("write", "write", false);
			$this->defineInt("topic", "topic", false);
			$this->defineText("comment", "comment", 250, false);
			$this->defineInt("order", "order", false);
			$this->defineInt("settingsID", "settingsID", false);
			$this->defineInt("views", "boardviews", false);
		}
	}

?>