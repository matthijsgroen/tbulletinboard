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
	class MessageSettingsTable extends DataTable {

		var $privateVars;

		function MessageSettingsTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "message_global");

			$this->defineInt("ID", "id", false);
			$this->setPrimaryKey("ID");
			$this->defineInt("settingID", "settingID", false);
			//$this->defineBool("farm", "farm", false);
		}
	}

?>
