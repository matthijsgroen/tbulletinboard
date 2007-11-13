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
	class AdministratorTable extends DataTable {

		var $privateVars;

		function AdministratorTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "administrators");

			$this->defineInt("ID", "UserID", false);
			$this->setPrimaryKey("ID");
			$this->defineEnum("security", "security", array(0 => "low", 1 => "medium", 2 => "high"), false);
			$this->defineEnum("typeAdmin", "typeAdmin", array(0 => "admin", 1 => "master"), false);
			$this->defineBool("active", "active", false);
		}
	}

?>