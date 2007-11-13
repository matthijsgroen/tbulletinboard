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
	class TopicIconTable extends DataTable {

		var $privateVars;

		function TopicIconTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "topicicons");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineText("name", "name", 30, false);
			$this->defineText("imgUrl", "imgUrl", 50, false);
		}
	}

?>