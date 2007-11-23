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

	importClass("util.LibDateTime");
	importClass("orm.DataObjects");

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
