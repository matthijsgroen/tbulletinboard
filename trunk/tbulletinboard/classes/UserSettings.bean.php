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
	class UserSettingsTable extends DataTable {

		var $privateVars;

		function UserSettingsTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "usersettings");

			$this->defineInt("ID", "userID", false);
			$this->setPrimaryKey("ID");
			$this->defineText("password", "password", 32, false);
			$this->defineText("email", "email", 60, false);
			$this->defineBool("showEmoticon", "showEmoticon", false);
			$this->defineDefaultValue("showEmoticon", true);

			$this->defineBool("showSignature", "showSignature", false);
			$this->defineDefaultValue("showSignature", true);

			$this->defineInt("skin", "skin", false);
			$this->defineDefaultValue("skin", 0);

			$this->defineBool("showAvatar", "showAvatar", false);
			$this->defineDefaultValue("showAvatar", true);

			$this->defineInt("daysPrune", "daysPrune", true);
			$this->defineInt("topicPage", "topicPage", true);
			$this->defineInt("reactionPage", "reactionPage", true);

		}
	}

?>