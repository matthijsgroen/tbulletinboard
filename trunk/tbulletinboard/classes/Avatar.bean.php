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
	class AvatarTable extends DataTable {

		var $privateVars;

		function AvatarTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "avatar");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");

			$this->defineText("imgUrl", "imgUrl", 50, false);
			$this->defineEnum("type", "type", array("custom" => "custom", "system" => "system"), false);
			$this->defineInt("userID", "userID", true);

		}
	}

?>
