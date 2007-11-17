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

	require_once($TBBclassDir . "GlobalSettings.bean.php");
	//require_once($TBBclassDir . "MemberModules.bean.php");

	/**
	 * Configuration. A settings class to read and store all TBB settings
	 */
	class Configuration {

		// The Configuration variables.
		// Public variables
		var $tablePrefix;
		var $smtpServer;
		var $minimumPasswordLength;
		var $onlineTimeout;
		var $imageOnlineDir;

		var $uploadDir;
		var $uploadOnlineDir;

		// Private variables
		var $privateVars;

		/**
		 * Constructor
		 */
		function Configuration(&$database) {
			// Set the default values
			$this->tablePrefix = 'tbb_';
			$this->privateVars['configRead'] = false;
			$this->minimumPasswordLength = 6;
			$this->onlineTimeout = 10;
			$this->privateVars['memberModules'] = array();
			$this->privateVars['topicModules'] = array();
			$this->privateVars['database'] = $database;
		}

		function getDatabase() {
			return $this->privateVars['database'];
		}

		function getConfiguration() {
			if ($this->privateVars['configRead']) return $this->privateVars['config'];
			$database = $this->getDatabase();
			$globalSettingsTable = new GlobalSettingsTable($database);
			$globalSettingsTable->selectAll();
			$this->privateVars['config'] = $globalSettingsTable->getRow();
			$this->privateVars['configRead'] = true;
			return $this->privateVars['config'];
		}

		function isOnline() {
			$config = $this->getConfiguration();
			return $config->getValue("online");
		}

		function getOfflineReason() {
			$config = $this->getConfiguration();
			return $config->getValue("offlineReason");
		}

		function getBoardName() {
			$config = $this->getConfiguration();
			return $config->getValue("boardName");
		}

		function setBoardName($name) {
			$config = $this->getConfiguration();
			$config->setValue("boardName", $name);
			$config->store();
		}

		function getAdminEmail() {
			$config = $this->getConfiguration();
			return $config->getValue("adminContact");
		}

		function getEncodeKey() {
			return "tG23CAc";
		}

		function getAbsoluteUri($fileName) {
			$redirect = "http://".$_SERVER['SERVER_NAME'];
			if ($_SERVER['SERVER_PORT'] <> 80)
				$redirect .= ":".$_SERVER['SERVER_PORT'];
			$redirect .= $_SERVER['PHP_SELF'];
			$redirect = subStr($redirect, 0, strRPos($redirect, '/')+1) . $fileName;
			return $redirect;
		}

		function redirectUri($fileName) {
			$redirect = $this->getAbsoluteUri($fileName);
			header("Location: " . $redirect);
			exit;
		}

		function validateMail($email) {
			return eregi("^[a-z0-9\._-]+@+[a-z0-9\._-]+\.+[a-z]{2,3}$", $email);
		}
		
		function getMessageToolbar(&$toolbar, &$user) {
			$toolbar->addItem('profile','', 'profiel', 'user.php?id='.$user->getUserID(), '', $this->imageOnlineDir.'profile.gif', 0, false, '');
		}

		function parseDate($dateTime) {
			//if (!is_numeric($timeStamp))
			//	$timeStamp = strToTime($timeStamp);

			//$dateInfo = getDate($timeStamp);
			$month = "jan";
			switch($dateTime->get(LibDateTime::month())) {
				case 2: $month = 'feb'; break;
				case 3: $month = 'mrt'; break;
				case 4: $month = 'apr'; break;
				case 5: $month = 'mei'; break;
				case 6: $month = 'jun'; break;
				case 7: $month = 'jul'; break;
				case 8: $month = 'aug'; break;
				case 9: $month = 'sept'; break;
				case 10: $month = 'okt'; break;
				case 11: $month = 'nov'; break;
				case 12: $month = 'dec'; break;
			}
			return $dateTime->get(LibDateTime::day()) . ' ' . $month . ' ' . $dateTime->toString("Y H:i");
		}

		function getTopicsPerPage() {
			$config = $this->getConfiguration();
			return $config->getValue("topicPage");
		}

		function getReactionsPerPage() {
			$config = $this->getConfiguration();
			return $config->getValue("postPage");
		}

		function getHotViews() {
			$config = $this->getConfiguration();
			return $config->getValue("hotViews");
		}

		function getHotReactions() {
			$config = $this->getConfiguration();
			return $config->getValue("hotReactions");
		}

		function getDaysPrune() {
			$config = $this->getConfiguration();
			return $config->getValue("daysPrune");
		}

		function getHelpBoardID() {
			$config = $this->getConfiguration();
			if ($config->isNull("helpBoard")) return false;
			return $config->getValue("helpBoard");
		}

		function setHelpBoardID($id) {
			$config = $this->getConfiguration();
			$config->setValue("helpBoard", $id);
			$config->store();
		}

		function clearHelpBoardID() {
			$config = $this->getConfiguration();
			$config->setNull("helpBoard");
			$config->store();
		}

		function getBinBoardID() {
			$config = $this->getConfiguration();
			if ($config->isNull("binboard")) return false;
			return $config->getValue("binboard");
		}

		function setBinBoardID($id) {
			$config = $this->getConfiguration();
			$config->setValue("binboard", $id);
			$config->store();
		}

		function clearBinBoardID() {
			$config = $this->getConfiguration();
			$config->setNull("binboard");
			$config->store();
		}

		function getSignaturesAllowed() {
			$config = $this->getConfiguration();
			return $config->getValue("signatures");
		}

		function getSignatureProfile() {
			global $TBBboardProfileList;
			$config = $this->getConfiguration();
			$sigProfileID = $config->getValue("signatureProfile");
			return $TBBboardProfileList->getBoardProfile($sigProfileID);
		}

		function setSignatureProfileID($id) {
			global $TBBboardProfileList;
			$config = $this->getConfiguration();
			$config->setValue("signatureProfile", $id);
			$config->store();
			return true;
		}

		function allowSignatures() {
			global $TBBboardProfileList;
			$config = $this->getConfiguration();
			return $config->getValue("signatures");
		}

		function setAllowSignatures($value) {
			global $TBBboardProfileList;
			$config = $this->getConfiguration();
			$config->setValue("signatures", $value);
			$config->store();
			return true;
		}

		function getUserInfoBlock(&$user) {
			global $TBBModuleManager;
			$info = $TBBModuleManager->getPluginInfoType("usertype", true);

			//$info = $this->getMemberModulesInfo();
			$result = "";
			for ($i = 0; $i < count($info); $i++) {
				$module = $TBBModuleManager->getPlugin($info[$i]->getValue("group"), "usertype");
				//$this->getMemberModule($info[$i]['ID']);
				$result .= $module->getUserInfo($user);
			}
			return $result;
		}
	}

?>
