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
	class TopicTable extends DataTable {

		var $privateVars;

		function TopicTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "topic");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineInt("boardID", "boardID", false);
			$this->defineDate("date", "date", false);
			$this->defineInt("poster", "poster", false);
			$this->defineText("title", "title", 80, false);
			$this->defineInt("icon", "icon", false);
			//$this->defineInt("typeID", "typeID", false);
			$this->defineInt("views", "views", false);
			$this->defineDefaultValue("views", 0);

			$this->defineEnum("state", "state", array( "online" => "online", "draft" => "draft"), false);
			$this->defineDefaultValue("state", "online");
			$this->defineDate("lastReaction", "lastReaction", false);
			$this->defineBool("closed", "closed", false);
			$this->defineEnum("special", "special", array( "no" => "no", "sticky" => "sticky", "announcement" => "announcement"), false);

			$this->defineText("plugin", "plugin", 40, true);
		}
	}

?>
