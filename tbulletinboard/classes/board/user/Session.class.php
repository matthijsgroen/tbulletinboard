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
	importClass("util.Session");

	importClass("board.user.User");
	importBean("board.user.User");

	class TBBSession extends Session {

		function TBBSession($path = "/", $domain = "", $lifetime=86400) {
			$this->Session($path, $domain, $lifetime);

			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$GLOBALS['TBBcurrentUser'] = new User();
			global $TBBcurrentUser;
			if ($this->isLoggedIn()) {
				$TBBcurrentUser->setUserID($this->getUserID());
				if ($TBBcurrentUser->getLastSession() == session_id()) {

					$userTable = new UserTable($database);
					$user = $userTable->getRowByKey($this->getUserID());
					$user->setValue("lastSeen", new LibDateTime());
					$user->store();
					/*
					$updateQuery = "UPDATE ".$TBBconfiguration->tablePrefix."users SET last_seen = NOW() WHERE ID='".addSlashes($this->getUserID())."'";
					$database->executeQuery($updateQuery);
					*/
				} else {
					$_SESSION['userID'] = '';
					global $feedback;
					$feedback->addMessage('Je bent op een andere lokatie aangemeld!');
					$GLOBALS['TBBcurrentUser'] = new User(); // Reset..
					$this->setValue("tbbSessID", uniqId("tbbS"));
					$this->setUserID("");
				}
			}
			if (!$this->hasValue("tbbSessID")) {
				$this->setValue("tbbSessID", uniqId("tbbS"));
			}
		}

	}

?>
