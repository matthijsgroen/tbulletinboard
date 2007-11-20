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

	class UserManagement {

		var $privateVars;

		function UserManagement() {
			$this->privateVars = array(
				'userCacheName' => array(),
				'userCacheID' => array(),
				'onlineUsers' => array(),
				'onlineRead' => false,
				'adminUsers' => array(),
				'adminRead' => false
			);
		}

		function getUserByUsername($username) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			// check the cache first
			if (isSet($this->privateVars['userCacheName'][$username])) {
				$user = $this->privateVars['userCacheName'][$username];
				return $user;
			}

			$userTable = new UserTable($database);
			$userSettingsTable = new UserSettingsTable($database);

			$userFilter = new DataFilter();
			$userFilter->addEquals("username", $username);
			$userSettingsFilter = new DataFilter();
			$userFilter->addJoinDataFilter("ID", "ID", $userSettingsTable, $userSettingsFilter);
			$sorting = new ColumnSorting();

			$joinedResult = $database->selectMultiTableRows(
				array($userTable,	$userSettingsTable),
				$userFilter, $sorting
			);
			if ($userInfo = $joinedResult->getJoinedRow()) {
				$userData = $joinedResult->extractRow($userTable, $userInfo);
				$userSettings = $joinedResult->extractRow($userSettingsTable, $userInfo);

				$result = new User();
				$result->p_setUserData($userData, $userSettings);
				$this->privateVars['userCacheName'][$username] = $result;
				$this->privateVars['userCacheID'][$result->getUserID()] = $result;
				return $result;
			} else {
				$this->privateVars['userCacheName'][$username] = false;
 				return false;
			}
		}

		function getUserByID($userID) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			// check the cache first
			if (isSet($this->privateVars['userCacheID'][$userID])) {
				$user = $this->privateVars['userCacheID'][$userID];
				return $user;
			}
			$userTable = new UserTable($database);
			$userSettingsTable = new UserSettingsTable($database);
			$userFilter = new DataFilter();
			$userFilter->addEquals("ID", $userID);
			$userSettingsFilter = new DataFilter();
			$userFilter->addJoinDataFilter("ID", "ID", $userSettingsTable, $userSettingsFilter);
			$sorting = new ColumnSorting();

			$joinedResult = $database->selectMultiTableRows(
				array($userTable,	$userSettingsTable),
				$userFilter, $sorting
			);
			if ($userInfo = $joinedResult->getJoinedRow()) {
				$userData = $joinedResult->extractRow($userTable, $userInfo);
				$userSettings = $joinedResult->extractRow($userSettingsTable, $userInfo);

				$result = new User();
				$result->p_setUserData($userData, $userSettings);
				$this->privateVars['userCacheID'][$userID] = $result;
				$this->privateVars['userCacheName'][$result->getUsername()] = $result;
				return $result;
			} else {
				$guest = new User();
				$this->privateVars['userCacheID'][$userID] = $guest;
				return $guest;
			}
		}

		function getOnlineUsers() {
			global $TBBconfiguration;
			if ($this->privateVars['onlineRead']) {
				return $this->privateVars['onlineUsers'];
			}
			$database = $TBBconfiguration->getDatabase();
			$onlineUsers = array();

			$userTable = new UserTable($database);
			$userSettingsTable = new UserSettingsTable($database);
			$userFilter = new DataFilter();
			$userSettingsFilter = new DataFilter();
			$userFilter->addJoinDataFilter("ID", "ID", $userSettingsTable, $userSettingsFilter);
			$userFilter->addEquals("loggedIn", true);
			$lastSeen = new LibDateTime();
			$lastSeen->sub(LibDateTime::minute(), $TBBconfiguration->onlineTimeout);
			//strToTime("-".$TBBconfiguration->onlineTimeout." minutes", time());
			$userFilter->addGreaterThan("lastSeen", $lastSeen);
			$sorting = new ColumnSorting();

			$joinedResult = $database->selectMultiTableRows(
				array($userTable,	$userSettingsTable),
				$userFilter, $sorting
			);
			while($userInfo = $joinedResult->getJoinedRow()) {
				$userData = $joinedResult->extractRow($userTable, $userInfo);
				$userSettings = $joinedResult->extractRow($userSettingsTable, $userInfo);

				$user = new User();
				$user->p_setUserData($userData, $userSettings);
				$this->privateVars['userCacheID'][$user->getUserID()] = $user;
				$this->privateVars['userCacheName'][$user->getUsername()] = $user;
				$onlineUsers[] = $user;

			}
			// Add the users to the cache
			$this->privateVars['onlineUsers'] = $onlineUsers;
			$this->privateVars['onlineRead'] = true;
			return $onlineUsers;
		}

		function getOnlineGuests($user) {
			global $TBBconfiguration;
			if (isSet($this->privateVars['onlineGuests'])) {
				return $this->privateVars['onlineGuests'];
			}
			importBean("board.UserSession");
			
			$database = $TBBconfiguration->getDatabase();

			$userSessionTable = new UserSessionTable($database);
			
			$filter = new DataFilter();
			$filter->addEquals("sessionID", session_id());
			$userSessionTable->selectRows($filter, new ColumnSorting());
			if ($sessionRow = $userSessionTable->getRow()) {
			} else {
				$sessionRow = $userSessionTable->addRow();				
			}					
			$sessionRow->setValue("lastActive", new LibDateTime());
			$sessionRow->setValue("sessionID", session_id());
			if ($user->isGuest()) {
				$sessionRow->setNull("userID");
			} else {
				$sessionRow->setValue("userID", $user->getUserID());
			}
			$sessionRow->store();

			$filter = new DataFilter();
			$lastSeen = new LibDateTime();
			$lastSeen->sub(LibDateTime::minute(), $TBBconfiguration->onlineTimeout);
			$filter->addGreaterThan("lastActive", $lastSeen);
			$filter->addNull("userID");
			$resultCount = $userSessionTable->countRows($filter);

			// Add the users to the cache
			$this->privateVars['onlineGuests'] = $resultCount;
			return $resultCount;
		}


		function getAdministrators() {
			global $TBBconfiguration;
			if ($this->privateVars['adminRead']) {
				return $this->privateVars['adminUsers'];
			}
			$database = $TBBconfiguration->getDatabase();
			$adminUsers = array();
			$userTable = new UserTable($database);
			$adminTable = new AdministratorTable($database);
			$userSettingsTable = new UserSettingsTable($database);

			$userFilter = new DataFilter();
			$userFilter->addJoinDataFilter("ID", "ID", $userSettingsTable, new DataFilter());
			$userFilter->addJoinDataFilter("ID", "ID", $adminTable, new DataFilter());
			$sorting = new ColumnSorting();
			$sorting->addColumnSort("nickname", true);

			$joinedResult = $database->selectMultiTableRows(
				array($userTable,	$userSettingsTable, $adminTable),
				$userFilter, $sorting
			);
			while($userInfo = $joinedResult->getJoinedRow()) {
				$user = new User();

				$userData = $joinedResult->extractRow($userTable, $userInfo);
				$userSettings = $joinedResult->extractRow($userSettingsTable, $userInfo);
				$adminData = $joinedResult->extractRow($adminTable, $userInfo);

				$user->p_setUserData($userData, $userSettings);
				$user->p_setAdminData($adminData);

				$adminUsers[] = $user;
			}
			// Add the users to the cache
			$this->privateVars['adminUsers'] = $adminUsers;
			$this->privateVars['adminRead'] = true;
			return $adminUsers;
		}

		function resetPassword($code) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$sendPassTable = new SendPasswordTable($database);
			$filter = new DataFilter();
			$yesterday = new LibDateTime();
			$yesterday->sub(LibDateTime::day(), 1);
			$filter->addLessThan("insertTime", $yesterday);
			$sendPassTable->deleteRows($filter);

			$filter = new DataFilter();
			$filter->addEquals("validation", $code);
			$sorting = new ColumnSorting();

			$sendPassTable->selectRows($filter, $sorting);
			if ($resultCode = $sendPassTable->getRow()) {
				$userID = $resultCode->getValue('userID');
				$user = $this->getUserByID($userID);
				if (!is_Object($user)) return false;
				$newPassword = subStr(uniqID(''), 0, 8);
				if ($TBBconfiguration->smtpServer !== "") {
					ini_set('SMTP', $TBBconfiguration->smtpServer);
				}

				$userSettingsTable = new UserSettingsTable($database);
				$userSettings = $userSettingsTable->getRowByKey($user->getUserID());
				$userSettings->setValue("password", md5($newPassword));
				$userSettings->store();

				$subject = sprintf("Nieuw wachtwoord op %s", $TBBconfiguration->getBoardName());
				$sender = sprintf("%s <%s>", $TBBconfiguration->getBoardName(), $TBBconfiguration->getAdminEmail());
				$to = sprintf("%s <%s>", $user->getNickname(), $user->getEmail());

				$message = sprintf("Hallo %s!\r\n", $user->getNickname());
				$message .= sprintf("Je nieuwe wachtwoord is: %s.\r\n", $newPassword);
				$message .= "Dit wachtwoord kan je weer veranderen in je instellingen scherm.\r\n";
				$message .= "\r\n";
				$message .= "Mzzl!\r\n";
				$headers = sprintf("From: %s\r\n", $sender);
				if(!@mail($to, $subject, $message, $headers)) return false;
				$resultCode->delete();

				return true;
			}
			return false;
		}
	}

	$GLOBALS['TBBuserManagement'] = new UserManagement();

?>
