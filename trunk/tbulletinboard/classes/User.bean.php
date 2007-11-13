<?php
	/**
	 * A Database Bean
	 *
	 *@package Beans
	 *@author Matthijs Groen (matthijs at ivinity.nl)
	 *@version 1.0
	 */

	require_once($ivLibDir."LibDateTime.class.php");
	/**
	 *
	 */
	require_once($ivLibDir."DataObjects.class.php");

	/**
	 * Usefull for editing schedules
	 */
	class UserTable extends DataTable {

		var $privateVars;

		function UserTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "users");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineText("username", "username", 15, false);
			$this->defineDate("date", "date", false);
			$this->defineInt("posts", "posts", false);
			$this->defineDefaultValue("posts", 0);
			$this->defineInt("topic", "topic", false);
			$this->defineDefaultValue("topic", 0);
			$this->defineText("nickname", "nickname", 30, false);
			$this->defineInt("avatarID", "avatarID", false);
			$this->defineDefaultValue("avatarID", 0);
			$this->defineText("customtitle", "customtitle", 50, false);
			$this->defineDefaultValue("customtitle", '');
			$this->defineDate("lastSeen", "last_seen", false);
			$this->defineDefaultValue("lastSeen", new LibDateTime());
			$this->defineText("signature", "signature", 2000, false);
			$this->defineDefaultValue("signature", '');

			$this->defineBool("loggedIn", "logged_in");
			$this->defineDefaultValue("loggedIn", false);
			$this->defineText("lastSession", "last_session", 60, false);
			$this->defineDefaultValue("lastSession", "");

			$this->defineDate("lastLogged", "last_logged", true);
			$this->defineDate("readThreshold", "read_threshold", true);

		}
	}

?>
