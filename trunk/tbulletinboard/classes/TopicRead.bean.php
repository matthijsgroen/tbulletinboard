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
	class TopicReadTable extends DataTable {

		var $privateVars;

		function TopicReadTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "topicread");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineInt("userID", "UserID", false);
			$this->defineInt("topicID", "TopicID", false);
			$this->defineDate("lastRead", "lastRead", false);
		}
	}



?>