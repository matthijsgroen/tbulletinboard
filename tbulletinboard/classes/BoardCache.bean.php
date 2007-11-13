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
	class BoardCacheTable extends DataTable {

		var $privateVars;

		function BoardCacheTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "boardcache");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineDate("date", "date", false);

			$this->defineInt("posts", "posts", false);
			$this->defineInt("topics", "topics", false);

			$this->defineDate("postDate", "postDate", true);
			$this->defineInt("postUser", "postUser", true);
			$this->defineText("topicTitle", "topicTitle", 255, true);
			$this->defineInt("topicID", "topicID", true);
		}
	}

?>