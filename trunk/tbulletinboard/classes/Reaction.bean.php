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
	class ReactionTable extends DataTable {

		var $privateVars;

		function ReactionTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "reaction");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineInt("topicID", "topicID", false);
			$this->defineInt("poster", "poster", false);
			$this->defineDate("date", "date", false);
			$this->defineDate("lastChange", "lastchange", true);
			$this->defineInt("changeBy", "changeby", true);
			$this->defineEnum("state", "state", array( 1 => "online", 2 => "draft"), false);
			$this->defineDefaultValue("state", "online");
		}
	}

?>