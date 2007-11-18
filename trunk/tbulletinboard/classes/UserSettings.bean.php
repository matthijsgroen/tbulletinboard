<?php
	/**
	 *	TBB2, an highly configurable and dynamic bulletin board
	 *	Copyright (C) 2007  Matthijs Groen
	 *
	 *	This program is free software: you can redistribute it and/or modify
	 *	it under the terms of the GNU General Public License as published by
	 *	the Free Software Foundation, either version 3 of the License, or
	 *	(at your option) any later version.
	 *	
	 *	This program is distributed in the hope that it will be useful,
	 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
	 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 *	GNU General Public License for more details.
	 *	
	 *	You should have received a copy of the GNU General Public License
	 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
	 *	
	 */

	/**
	 *
	 */
	require_once($libraryClassDir."DataObjects.class.php");

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
