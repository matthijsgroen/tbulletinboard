<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
	 */

	/**
	 * User. an TBB user, with its configuration and settings.
	 */
	require_once($ivLibDir . 'LibDateTime.class.php');
	require_once($TBBclassDir . 'AvatarList.class.php');
	require_once($TBBclassDir . 'UserSettings.bean.php');
	require_once($TBBclassDir . 'User.bean.php');
	require_once($TBBclassDir . 'TopicRead.bean.php');
	require_once($TBBclassDir . 'Administrator.bean.php');
	require_once($TBBclassDir . 'SendPassword.bean.php');

	class User {
		var $privateVars;

		function User() {
			$this->privateVars['userID'] = -1;
			$this->privateVars['readFromDB'] = true;
		}

		function isGuest() {
			return ($this->privateVars['userID'] == -1) ? true : false;
		}

		function setUserID($userID) {
			$this->privateVars['userID'] = $userID;

		}

		function getUserID() {
			return $this->privateVars['userID'];
		}

		function isCurrentUser() {
			global $TBBcurrentUser;
			return ($this->getUserID() == $TBBcurrentUser->getUserID()) ? true : false;
		}

		function login($username, $password) {
			global $TBBconfiguration;

			//$selectQuery = "SELECT * FROM ".$TBBconfiguration->tablePrefix."users as usr, ".$TBBconfiguration->tablePrefix."usersettings as settin ".
			// "WHERE usr.ID = settin.userID AND usr.username='".trim(addSlashes($username))."' AND settin.password = ENCODE('".trim(addSlashes($password))."', '".$TBBconfiguration->getEncodeKey()."')";
			$database = $TBBconfiguration->getDatabase();

			$userTable = new UserTable($database);
			$userSettingsTable = new UserSettingsTable($database);

			$userFilter = new DataFilter();
			$userFilter->addEquals("username", trim($username));
			$userSettingsFilter = new DataFilter();
			$userSettingsFilter->addEquals("password", md5(trim($password)));

			$userFilter->addJoinDataFilter("ID", "ID", $userSettingsTable, $userSettingsFilter);
			$sorting = new ColumnSorting();

			$joinedResult = $database->selectMultiTableRows(
				array($userTable,	$userSettingsTable),
				$userFilter, $sorting
			);
			if ($userInfo = $joinedResult->getJoinedRow()) {
				$userData = $joinedResult->extractRow($userTable, $userInfo);
				$userSettings = $joinedResult->extractRow($userSettingsTable, $userInfo);

				$this->privateVars['userData'] = $userData;
				$this->privateVars['userSettings'] = $userSettings;
				$this->privateVars['userID'] = $userData->getValue('ID');
				$this->privateVars['readFromDB'] = false;

				$GLOBALS['TBBsession']->setUserID($userData->getValue('ID'));
				if (!$userData->isNull("lastLogged")) {
					$lastLogged = $userData->getValue("lastLogged");

					$readThreshold = new LibDateTime();
					$readThreshold->setTimestamp($lastLogged->getTimestamp());
					$readThreshold->sub(LibDateTime::month(), 1);
					//strToTime("-1 month", $lastLogged);
					$userData->setValue("readThreshold", $readThreshold);

					$filter = new DataFilter();
					$filter->addEquals("userID", $userData->getValue('ID'));
					$filter->addLessThan("lastRead", $readThreshold);
					$topicReadTable = new TopicReadTable($database);
					$topicReadTable->deleteRows($filter);

				}
				$userData->setValue("lastLogged", new LibDateTime());
				$userData->setValue("loggedIn", true);
				$userData->setValue("lastSession", session_id());
				$userData->store();


				//$updateQuery = "UPDATE ".$TBBconfiguration->tablePrefix."users SET logged_in='yes', last_session='".session_id()."' WHERE ID='".addSlashes($userdata['ID'])."'";
				//$database->executeQuery($updateQuery);
				return true;
			}
			return false;
			/*
			$selectQuery = "SELECT * FROM ".$TBBconfiguration->tablePrefix."users as usr, ".$TBBconfiguration->tablePrefix."usersettings as settin ".
			 "WHERE usr.ID = settin.userID AND usr.username='".trim(addSlashes($username))."' AND settin.password = '".md5(trim(addSlashes($password)))."'";
			$result = $database->executeQuery($selectQuery);
			*/
		}

		function logout() {
			if ($this->privateVars['userID'] < 1) return false;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$GLOBALS['TBBsession']->setUserID('');
			$this->privateVars['readFromDB'] = true;

			$userData = $this->privateVars['userData'];
			$userData->setValue("loggedIn", false);
			$userData->store();
			$this->privateVars['userData'] = false;
			$this->privateVars['userSettings'] = false;
			//$updateQuery = "UPDATE ".$TBBconfiguration->tablePrefix."users SET logged_in='no' WHERE ID='".addSlashes($this->privateVars['userID'])."'";
			//$database->executeQuery($updateQuery);
			$this->privateVars['userID'] = -1;
			return true;
		}

		function register($username, $nickname, $password, $email) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			if (strlen(trim($password)) < $TBBconfiguration->minimumPasswordLength) return false;

			$userTable = new UserTable($database);
			$userSettingsTable = new UserSettingsTable($database);

			$newUser = $userTable->addRow();
			$newUser->setValue("username", trim($username));
			$newUser->setValue("date", new LibDateTime());
			$newUser->setValue("nickname", trim($nickname));
			if (!$newUser->store()) return false;
			/*
			$insertQuery = "INSERT INTO " . $TBBconfiguration->tablePrefix . "users(username, date, nickname, homepage) VALUES(" .
			 "'". addSlashes(trim($username)) . "', NOW(), '" . addSlashes(trim($nickname)) . "', '" . addSlashes($homepage) . "')";
			$result = $database->executeQuery($insertQuery);
			if ($result === false) return false;
			if ($result->getNumAffectedRows() != 1) return false;
			$userID = $result->getInsertID();
			*/

			$newSettings = $userSettingsTable->addRow();
			$newSettings->setValue("ID", $newUser->getValue("ID"));
			$newSettings->setValue("password", md5(trim($password)));
			$newSettings->setValue("email", trim($email));
			if ($newSettings->store()) return false;
			/*
			$insertQuery = "INSERT INTO ".$TBBconfiguration->tablePrefix."usersettings(userID, password, email) VALUES(".
			 "'".$userID."', MD5('".addSlashes(trim($password))."'), '".addSlashes(trim($email))."')";
			$result = $database->executeQuery($insertQuery);
			if ($result === false) return false;
			if ($result->getNumAffectedRows() != 1) return false;
			*/
			$this->setUserID($newUser->getValue("ID"));
			return $newUser->getValue("ID");
		}

		function changePassword($oldPassword, $newPassword) {
			if ($this->getUserID() == -1) return false; // guests cannot change a password, they don't have one!
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			if (strlen(trim($newPassword)) < $TBBconfiguration->minimumPasswordLength) return false;

			$dataFilter = new DataFilter();
			$dataFilter->addEquals("password", md5(trim($oldPassword)));
			$dataFilter->addEquals("ID", $this->getUserID());

			$settingsTable = new UserSettingsTable($database);
			$settingsTable->selectRows($dataFilter, new ColumnSorting());

			/*
			$selectQuery = "SELECT count(*) as nr FROM ".$TBBconfiguration->tablePrefix."usersettings as settin ".
			 "WHERE settin.password = '".md5(trim(addSlashes($oldPassword)))."' AND settin.userID='".addSlashes($this->getUserID())."'";
			$result = $database->executeQuery($selectQuery);
			*/
			if ($settings = $settingsTable->getRow()) {
				$settings->setValue("password", md5(trim($newPassword)));
				if ($settings->rowChanged()) {
					return $settings->store();
				} else return true;
				/*
				$updateQuery = "UPDATE ".$TBBconfiguration->tablePrefix."usersettings ".
					"SET password='".md5(addSlashes(trim($newPassword)))."'".
					"WHERE userID='".addSlashes($this->getUserID())."'";
				$updateResult = $database->executeQuery($updateQuery);
				if ($updateResult === false) return false;
				if ($updateResult->getNumAffectedRows() != 1) return false;
				return true;
				*/
			}
			return false;
		}

		function readUserData() {
			if ($this->privateVars['readFromDB'] != true) return;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$userTable = new UserTable($database);
			$userSettingsTable = new UserSettingsTable($database);

			$userFilter = new DataFilter();
			$userFilter->addEquals("ID", $this->getUserID());
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

				$this->privateVars['userData'] = $userData;
				$this->privateVars['userSettings'] = $userSettings;
				$this->privateVars['userID'] = $userData->getValue("ID");
				$this->privateVars['readFromDB'] = false;
				return true;
			} else {
				$this->privateVars['userData'] = false;
				$this->privateVars['userSettings'] = false;
				$this->privateVars['readFromDB'] = false;
				return false;
			}
			/*
			$selectQuery = "SELECT * FROM ".$TBBconfiguration->tablePrefix."users as usr, ".$TBBconfiguration->tablePrefix."usersettings as settin ".
			 "WHERE usr.ID = settin.userID AND usr.ID='".trim(addSlashes($this->privateVars['userID']))."'";
			$result = $database->executeQuery($selectQuery);
			if ($result === false) {
				$this->privateVars['readFromDB'] = false;
				return false;
			}
			if ($userdata = $result->getRow()) {
				$this->privateVars['databaseData'] = $userdata;
				$this->privateVars['userID'] = $userdata['ID'];
				$this->privateVars['readFromDB'] = false;
				return true;
			} else return false;
			*/
		}

		function p_setUserData(&$userdata, &$userSettings) {
			$this->privateVars['userData'] = $userdata;
			$this->privateVars['userSettings'] = $userSettings;
			$this->privateVars['userID'] = $userdata->getValue("ID");
			$this->privateVars['readFromDB'] = false;
			return true;
		}

		function p_setAdminData(&$adminData) {
			$this->privateVars['adminType'] = $adminData->getValue('typeAdmin');
			$this->privateVars['secLevel'] = $adminData->getValue('security');
			$this->privateVars['activeAdmin'] = $adminData->getValue('active');
		}

		function getNickname() {
			$this->readUserData();
			$userData = $this->privateVars['userData'];
			if ($userData === false) return "Gast";
			return $userData->getValue("nickname");
		}

		function getUsername() {
			$this->readUserData();
			$userData = $this->privateVars['userData'];
			if ($userData === false) return "Gast";
			return $userData->getValue("username");
		}

		function getReadThreshold() {
			$this->readUserData();
			$userData = $this->privateVars['userData'];
			if ($userData === false) {
				$dateTime = new LibDateTime();
				$dateTime->sub(LibDateTime::month(), 2);
				return $dateTime;
			}
			if ($userData->isNull("readThreshold")) {
				$dateTime = new LibDateTime();
				$dateTime->sub(LibDateTime::month(), 2);
				return $dateTime;
			}
			return $userData->getValue("readThreshold");
		}

		function getEmail() {
			$this->readUserData();
			$userData = $this->privateVars['userSettings'];
			if ($userData === false) return "";
			return $userData->getValue("email");
		}

		function getLastSession() {
			$this->readUserData();
			$userData = $this->privateVars['userData'];
			if ($userData === false) return "";
			return $userData->getValue("lastSession");
		}

		function sendPasswordNotification() {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$this->readUserData();
			if ($TBBconfiguration->smtpServer !== "") {
				ini_set('SMTP', $TBBconfiguration->smtpServer);
			}

			$validation = md5(uniqID(''));
			// fetch the password
			$sendPasswordTable = new SendPasswordTable($database);
			$newRow = $sendPasswordTable->addRow();
			$newRow->setValue("userID", $this->getUserID());
			$newRow->setValue("validation", $validation);
			$newRow->setValue("insertTime", new LibDateTime());
			$newRow->store();

			/*
			$insertQuery = sprintf(
				"REPLACE INTO `%ssendpassword`(`userID`, `insertTime`, `validation`) VALUES('%s', NOW(), '%s')",
				$TBBconfiguration->tablePrefix,
				$this->getUserID(),
				$validation
			);
			$database->executeQuery($insertQuery);
			*/

			$subject = sprintf("Paswoord terugzetten op %s", $TBBconfiguration->getBoardName());
			$sender = sprintf("%s <%s>", $TBBconfiguration->getBoardName(), $TBBconfiguration->getAdminEmail());
			$to = sprintf("%s <%s>", $this->getNickname(), $this->getEmail());

			$message = sprintf("Hallo %s!\r\n", $this->getNickname());
			$message .= sprintf("Je hebt gevraagt om je wachtwoord terug te zetten op %s.\r\n", $TBBconfiguration->getBoardName());
			$message .= "Om deze terugzetting te voltooien, klik op de volgende link:\r\n";
			$message .= $TBBconfiguration->getAbsoluteUri("resetpassword.php?code=") . $validation . "\r\n";
			$message .= "Na het bezoeken van de link zal je een nieuw wachtwoord toegestuurd krijgen.\r\n";
			$message .= "\r\n";
			$message .= "Mocht je niet hebben aangevraagt om je wachtwoord terug te zetten, dan kan je deze e-mail negeren.\r\n";
			$message .= "\r\n";
			$message .= "Mzzl!\r\n";
			$headers = sprintf("From: %s\r\n", $sender);
			if (!@mail($to, $subject, $message, $headers)) return false;
			return true;
		}

		function isAdministrator() {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			if (isSet($this->privateVars['adminType'])) {
				if ($this->privateVars['adminType'] == 'none') return false;
				if ($this->privateVars['adminType'] == 'admin') return true;
				if ($this->privateVars['adminType'] == 'master') return true;
			}
			$administratorTable = new AdministratorTable($database);
			$adminData = $administratorTable->getRowByKey($this->getUserID());

			/*
			$selectQuery = "SELECT * FROM ".$TBBconfiguration->tablePrefix."administrators WHERE userID='".addSlashes($this->getUserID())."'";
			$selectResult = $database->executeQuery($selectQuery);
			*/
			if (is_Object($adminData)) {
				$this->p_setAdminData($adminData);
				if ($this->privateVars['adminType'] == 'none') return false;
				if ($this->privateVars['adminType'] == 'admin') return true;
				if ($this->privateVars['adminType'] == 'master') return true;
			} else {
				$this->privateVars['adminType'] = 'none';
				$this->privateVars['secLevel'] = 'none';
				$this->privateVars['activeAdmin'] = false;
				return false;
			}
		}

		function isMaster() {
			$this->isAdministrator();
			return ($this->privateVars['adminType'] == 'master');
		}

		function isActiveAdmin() {
			$this->isAdministrator();
			if (!isSet($this->privateVars['activeAdmin'])) return false;
			return $this->privateVars['activeAdmin'];
		}

		function setAdminActive($on) {
			if (!$this->isAdministrator()) return;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$administratorTable = new AdministratorTable($database);
			$adminData = $administratorTable->getRowByKey($this->getUserID());
			if (is_Object($adminData)) {
				$adminData->setValue("active", $on);
				$adminData->store();
				$this->privateVars['activeAdmin'] = $on;
			}
			/*
			$value = ($on) ? "yes" : "no";
			$updateQuery = sprintf("UPDATE %sadministrators SET `active`='%s' WHERE `userID`='%s'",
				$TBBconfiguration->tablePrefix,
				$value,
				$this->getUserID());
			$database->executeQuery($updateQuery);
			*/
		}

		function getAdminType() {
			$this->isAdministrator(); // Use to read the database data
			return $this->privateVars['adminType'];
		}

		function getSecurityLevel() {
			$this->isAdministrator(); // Use to read the database data
			return $this->privateVars['secLevel'];
		}

		function removeAdminRights() {
			global $TBBcurrentUser;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			if (@$TBBcurrentUser->isMaster()) {
				$administratorTable = new AdministratorTable($database);
				$adminData = $administratorTable->getRowByKey($this->getUserID());
				$adminData->delete();
				//$deleteQuery = "DELETE FROM ".$TBBconfiguration->tablePrefix."administrators WHERE userID='".addSlashes($this->getUserID())."'";
				unSet($TBBcurrentUser->privateVars['adminType']);
				// Reset the admin info of the current user. The current user could have removed himself
				//$result = $database->executeQuery($deleteQuery);
				//return ($result->ggetNumAffectedRows() == 1) ? true : false;
				return true;
			}
		}

		function getTopicsPerPage() {
			global $TBBconfiguration;
			$systemTopicPage = $TBBconfiguration->getTopicsPerPage();
			if ($this->isGuest()) return $systemTopicPage;
			$data = $this->privateVars['userSettings'];
			if ($data->isNull('topicPage')) return $systemTopicPage;
			return $data->getValue("topicPage");
		}

		function isSystemTopicsPerPage() {
			if ($this->isGuest()) return true;
			$data = $this->privateVars['userSettings'];
			return $data->isNull('topicPage');
		}

		function setTopicsPerPage($number) {
			global $TBBconfiguration;
			$data = $this->privateVars['userSettings'];
			if ($number < 1) {
				$data->setNull('topicPage');
			} else {
				$data->setValue("topicPage", $number);
			}
			$data->store();
		}

		function getReactionsPerPage() {
			global $TBBconfiguration;
			$systemReactionPage = $TBBconfiguration->getTopicsPerPage();
			if ($this->isGuest()) return $systemReactionPage;
			$data = $this->privateVars['userSettings'];
			if ($data->isNull('reactionPage')) return $systemReactionPage;
			return $data->getValue("reactionPage");
		}

		function isSystemReactionsPerPage() {
			if ($this->isGuest()) return true;
			$data = $this->privateVars['userSettings'];
			return $data->isNull('reactionPage');
		}

		function setReactionsPerPage($number) {
			global $TBBconfiguration;
			$data = $this->privateVars['userSettings'];
			if ($number < 1) {
				$data->setNull('reactionPage');
			} else {
				$data->setValue("reactionPage", $number);
			}
			$data->store();
		}

		function getDaysPrune() {
			global $TBBconfiguration;
			$systemPrune = $TBBconfiguration->getDaysPrune();
			if ($this->isGuest()) return $systemPrune;
			$data = $this->privateVars['userSettings'];
			if ($data->isNull('daysPrune')) return $systemPrune;
			return $data->getValue("daysPrune");
		}

		function isSystemDaysPrune() {
			if ($this->isGuest()) return true;
			$data = $this->privateVars['userSettings'];
			return $data->isNull('daysPrune');
		}

		function setDaysPrune($days) {
			global $TBBconfiguration;
			$data = $this->privateVars['userSettings'];
			if ($days < 1) {
				$data->setNull('daysPrune');
			} else {
				$data->setValue("daysPrune", $days);
			}
			$data->store();
		}

		function showSignatures() {
			if ($this->isGuest()) return true;
			$data = $this->privateVars['userSettings'];
			return $data->getValue("showSignature");
		}

		function setShowSignatures($show) {
			$data = $this->privateVars['userSettings'];
			$data->setValue("showSignature", $show);
			$data->store();
		}

		function showAvatars() {
			if ($this->isGuest()) return true;
			$data = $this->privateVars['userSettings'];
			return $data->getValue("showAvatar");
		}

		function setShowAvatars($show) {
			$data = $this->privateVars['userSettings'];
			$data->setValue("showAvatar", $show);
			$data->store();
		}

		function showEmoticons() {
			if ($this->isGuest()) return true;
			$data = $this->privateVars['userSettings'];
			return $data->getValue("showEmoticon");
		}

		function setShowEmoticons($show) {
			$data = $this->privateVars['userSettings'];
			$data->setValue("showEmoticon", $show);
			$data->store();
		}

		function getPostCount() {
			if ($this->isGuest()) return 0;
			$data = $this->privateVars['userData'];
			return ($data->getValue("posts") + $data->getValue("topic"));
			//$postCount = $this->privateVars['databaseData']['posts'] + $this->privateVars['databaseData']['topic'];
			//return $postCount;
		}

		function getAvatarID() {
			if ($this->isGuest()) return false;
			$data = $this->privateVars['userData'];
			$avatarID = $data->getValue("avatarID");
			if ($avatarID == 0) return false;
			return $avatarID;
		}

		function changeAvatar($newID) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$avatarList = new AvatarList();
			$oldAvatarID = $this->getAvatarID();
			//if (($oldAvatarID !== false) && (!$avatarList->isSystemAvatar($oldAvatarID))) {
			//	$avatarList->removeAvatar($oldAvatarID);
			//}
			$data = $this->privateVars['userData'];
			$data->setValue("avatarID", $newID);
			$data->store();

			return true;
		}

		function getSignature() {
			if ($this->isGuest()) return "";
			$data = $this->privateVars['userData'];
			return $data->getValue("signature");
		}

		function getTopicCount() {
			if ($this->isGuest()) return 0;
			$data = $this->privateVars['userData'];
			return $data->getValue("topic");
		}

		function getLastSeenString() {
			if ($this->isGuest()) return 0;
			$data = $this->privateVars['userData'];
			global $TBBconfiguration;
			$lastSeen = new LibDateTime();
			$lastSeen->sub(LibDateTime::minute(), $TBBconfiguration->onlineTimeout);
			if (!$lastSeen->after($data->getValue("lastSeen"))) {
				return "Online";
			} else {
				return $TBBconfiguration->parseDate($data->getValue("lastSeen"));
			}
		}

		function getMemberSinceString() {
			if ($this->isGuest()) return 0;
			$data = $this->privateVars['userData'];
			global $TBBconfiguration;
			return $TBBconfiguration->parseDate($data->getValue("date"));
		}

		function getSignatureHTML() {
			if (isSet($this->privateVars['signatureCache'])) {
				return $this->privateVars['signatureCache'];
			}
			global $TBBconfiguration;
			global $TBBcurrentUser;
			global $textParser;
			global $TBBclassDir;

			require_once($TBBclassDir . 'TBBEmoticonList.class.php');
			global $TBBemoticonList;

			$boardProfile = $TBBconfiguration->getSignatureProfile();
			if ($boardProfile) {
				$tbbTags = $boardProfile->getTBBtagList();
			} else {
				global $TBBtagListManager;
				$tbbTags = $TBBtagListManager->getTagList(array());
			}
			$emoticons = $TBBemoticonList;
			if (!$TBBcurrentUser->showEmoticons()) $emoticons = false;
			
			$sigHTML = $textParser->parseMessageText($this->getSignature(), $emoticons, $tbbTags);
			$this->privateVars['signatureCache'] = $sigHTML;
			return $sigHTML;
		}

		function setSignature($signature) {
			$data = $this->privateVars['userData'];
			$data->setValue("signature", $signature);
			$data->store();

			$this->privateVars['readFromDB'] = true;
			$this->readUserData();
		}

	}
?>
