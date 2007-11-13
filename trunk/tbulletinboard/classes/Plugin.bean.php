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
	class PluginTable extends DataTable {

		var $privateVars;

		function PluginTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "plugin");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");

			$this->defineText("name", "name", 250, false);
			$this->defineText("version", "version", 250, false);
			$this->defineInt("build", "build", false);
			$this->defineText("group", "group", 40, false);
			$this->defineText("type", "type", 250, false);
			$this->defineBool("active", "active");
			$this->defineDefaultValue('active', false);
			$this->defineDate("installDate", "installDate", false);
			$this->defineDefaultValue('installDate', new LibDateTime());
			$this->defineText("filename", "filename", 250, false);
			$this->defineText("classname", "classname", 250, false);
		}
	}

?>
