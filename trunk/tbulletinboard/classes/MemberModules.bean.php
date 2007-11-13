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
	class MemberModulesTable extends DataTable {

		var $privateVars;

		function MemberModulesTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "membermodules");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineText("name", "name", 20, false);
			$this->defineText("classname", "classname", 50, false);
		}
	}

?>