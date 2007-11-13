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
	class GlobalSettingsTable extends DataTable {

		var $privateVars;

		function GlobalSettingsTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "globalsettings");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineBool("online", "online");
			$this->defineText("offlineReason", "offlineReason", 255, false);
			$this->defineDate("onlineTime", "onlineTime", false);
			$this->defineText("version", "version", 20, false);
			$this->defineText("adminContact", "adminContact", 50, false);
			$this->defineInt("hotViews", "hotViews", false);
			$this->defineDefaultValue("hotViews", 500);
			$this->defineInt("hotReactions", "hotReactions", false);
			$this->defineDefaultValue("hotReactions", 30);
			$this->defineBool("avatars", "avatars");
			$this->defineBool("customtitles", "customtitles");
			$this->defineBool("signatures", "signatures");
			$this->defineText("boardName", "name", 50, false);

			$this->defineInt("topicPage", "topicPage", false);
			$this->defineDefaultValue("topicPage", 30);
			$this->defineInt("postPage", "postPage", false);
			$this->defineDefaultValue("postPage", 30);
			$this->defineInt("floodDelay", "floodDelay", false);
			$this->defineDefaultValue("floodDelay", 10);
			$this->defineInt("daysPrune", "daysPrune", false);
			$this->defineDefaultValue("daysPrune", 30);

			$this->defineInt("helpBoard", "helpBoard", false);
			$this->defineInt("signatureProfile", "sigProfile", false);
			$this->defineText("referenceID", "referenceID", 40, true);
			$this->defineInt("binboard", "binboard", true);

		}
	}

?>