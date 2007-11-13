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
	class BoardTagsTable extends DataTable {

		var $privateVars;

		function BoardTagsTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "boardtags");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineInt("settingID", "settingID", false);
			$this->defineInt("tagID", "tagID", false);
		}
	}

?>