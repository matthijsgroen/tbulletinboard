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
	class GroupTable extends DataTable {

		var $privateVars;

		function GroupTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "group");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineText("moduleID", "moduleID", 50, false);
			$this->defineText("groupID", "groupID", 30, false);
			$this->defineText("name", "name", 50, false);
		}
	}

?>
