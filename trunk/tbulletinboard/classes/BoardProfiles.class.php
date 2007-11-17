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

	require_once($TBBclassDir."TagListManager.class.php");
	require_once($TBBclassDir."BoardSettings.bean.php");
	require_once($TBBclassDir."BoardTags.bean.php");
	require_once($TBBclassDir."BoardTopics.bean.php");
	require_once($TBBclassDir."Board.bean.php");

	class BoardProfile {

		var $privateVars;

		function BoardProfile() {
			$this->privateVars = array();
		}

		function p_setDBdata(&$data) {
			$this->privateVars['dbData'] = $data;
		}

		function p_getDBdata() {
			return $this->privateVars['dbData'];
		}

		function getName() {
			$data = $this->p_getDBdata();
			return $data->getValue("name");
		}

		function getViewModus() {
			$modus = $this->getViewModusRaw();
			switch($modus) {
				case 'open': return "Open";
				case 'standard': return "Gesloten";
				case 'hidden': return "Verborgen";
				case 'openHidden': return "Open en verborgen";
			}
			return false;
		}

		function isHidden() {
			$modus = $this->getViewModusRaw();
			switch($modus) {
				case 'open': return false;
				case 'standard': return false;
				case 'hidden': return true;
				case 'openHidden': return true;
			}
		}

		function isOpen() {
			$modus = $this->getViewModusRaw();
			switch($modus) {
				case 'open': return true;
				case 'standard': return false;
				case 'hidden': return false;
				case 'openHidden': return true;
			}
		}

		function getViewModusRaw() {
			$data = $this->p_getDBdata();
			return $data->getValue("viewMode");
		}

		function allowSignatures() {
			$data = $this->p_getDBdata();
			return $data->getValue("signatures");
		}

		function getID() {
			$data = $this->p_getDBdata();
			return $data->getValue("ID");
		}

		function getTBBtagList() {
			global $TBBtagListManager;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$boardTags = new BoardTagsTable($database);

			$filter = new DataFilter();
			$filter->addEquals("settingID", $this->getID());

			$sorting = new ColumnSorting();
			$boardTags->selectRows($filter, $sorting);
			$idList = array();
			while ($row = $boardTags->getRow()) {
				$idList[] = $row->getValue("tagID");
			}
			return $TBBtagListManager->getTagList($idList);
		}

		function getAllowedTopicPlugins() {
			if (isSet($this->privateVars['topicIDcache'])) {
				return $this->privateVars['topicIDcache'];
			}
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$boardTopics = new BoardTopicsTable($database);

			$filter = new DataFilter();
			$filter->addEquals("settingID", $this->getID());
			$sorting = new ColumnSorting();
			$boardTopics->selectRows($filter, $sorting);
			$boardTopics->getSelectionQuery();

			$idList = array();
			while ($row = $boardTopics->getRow()) {
				$idList[] = $row->getValue("plugin");
				if ($row->getValue("default") === true)
					$this->privateVars['topicDefaultIDcache'] = $row->getValue("plugin");
			}
			$this->privateVars['topicIDcache'] = $idList;

			/*
			global $TBBcurrentUser;
			$refID = $TBBconfiguration->getReferenceID();
			if (($refID !== false) && ($TBBcurrentUser->isActiveAdmin())) {
				$idList[] = $refID;
			}
			*/
			return $idList;
		}

		function getDefaultTopicTypeID() {
			if (isSet($this->privateVars['topicDefaultIDcache'])) {
				return $this->privateVars['topicDefaultIDcache'];
			}
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$boardTopics = new BoardTopicsTable($database);
			$filter = new DataFilter();
			$filter->addEquals("settingID", $this->getID());
			$filter->addEquals("default", true);

			$sorting = new ColumnSorting();
			$boardTopics->selectRows($filter, $sorting);
			if ($defaultTopicType = $boardTopics->getRow()) {
				$id = $defaultTopicType->getValue("plugin");
				$this->privateVars['topicDefaultIDcache'] = $id;
				return $id;
			}
			$this->privateVars['topicDefaultIDcache'] = false;
			return false;
		}

		function setDefaultTopicTypeID($id) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$boardTopics = new BoardTopicsTable($database);
			$rowFilter = new DataFilter();
			$rowFilter->addEquals('settingID', $this->getID());
			$mutations = new DataMutation();
			$mutations->setEquals('default', false); // set all topics in this profile to not default
			$boardTopics->executeDataMutations($mutations, $rowFilter);

			$filter = new DataFilter();
			$filter->addEquals("settingID", $this->getID());
			$filter->addEquals("plugin", $id);

			$sorting = new ColumnSorting();
			$boardTopics->selectRows($filter, $sorting);
			if ($topicSetting = $boardTopics->getRow()) {
				$topicSetting->setValue("default", true);
				$topicSetting->store();
				$this->privateVars['topicDefaultIDcache'] = $id;
				return true;
			}
			$this->privateVars['topicDefaultIDcache'] = false;
			return false;
		}

		/**
		 *
		 *@param array $newIDs an array containing the IDs of the allowed TopicTypes.
		 */
		function setAllowedTopicTypes($newIDs) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$profileID = $this->getID();
			$oldAllowed = $this->getAllowedTopicPlugins();

			$boardTopics = new BoardTopicsTable($database);
			$topicFilter = new DataFilter();
			$topicFilter->setMode("or");

			$marking = array();
			for ($i = 0; $i < count($oldAllowed); $i++) {
				$id = $oldAllowed[$i];
				if (!in_array($id, $newIDs)) {
					$topicFilter->addEquals("plugin", $id);
					if (isSet($this->privateVars['topicDefaultIDcache']) && ($this->privateVars['topicDefaultIDcache'] == $id)) {
						$this->privateVars['topicDefaultIDcache'] = false;
					}
				}
			}
			$dataFilter = new DataFilter();
			$dataFilter->addEquals("settingID", $profileID);
			if ($topicFilter->getFilterCount() > 0) {
				$dataFilter->addDataFilter($topicFilter);
				$boardTopics->deleteRows($dataFilter);
			}

			for ($i = 0; $i < count($newIDs); $i++) {
				$id = $newIDs[$i];
				if (!in_array($id, $oldAllowed)) {
					$newRow = $boardTopics->addRow();
					$newRow->setValue("plugin", $id);
					$newRow->setValue("settingID", $profileID);
					$newRow->setValue("default", false);
					$newRow->store();
				}
			}
			$this->privateVars['topicIDcache'] = $newIDs;
		}

		function getNrUsed() {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$boardTable = new BoardTable($database);

			$filter = new DataFilter();
			$filter->addEquals("settingsID", $this->getID());

	 		$functions = new FunctionDescriptions();
			$functions->addCount('ID', 'nrUsed');

			$resultSet = $boardTable->executeDataFunction($functions, $filter);
	 		$resultRow = $resultSet->getRow();
			return $resultRow['nrUsed'];
		}

		function getUsingBoards() {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$boardTable = new BoardTable($database);

			$filter = new DataFilter();
			$filter->addEquals("settingsID", $this->getID());
			$sorting = new ColumnSorting();

			$boardTable->selectRows($filter, $sorting);
			$result = array();

			while ($boardInfo = $boardTable->getRow()) {
				$board = new Board();
				$board->p_setDBdata($boardInfo);
				$result[] = $board;
			}
			return $result;
		}

		function increasePostCount() {
			$data = $this->p_getDBdata();
			return $data->getValue("incCount");
		}


		function updateSettings($name, $viewMode, $secLevel, $incCount, $signatures) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$theSecLevel = 'none';
			switch($secLevel) {
				case '1': $theSecLevel = 'low'; break;
				case '2': $theSecLevel = 'medium'; break;
				case '3': $theSecLevel = 'high'; break;
			}

			$data = $this->p_getDBdata();
			$data->setValue("viewMode", $viewMode);
			$data->setValue("secLevel", $theSecLevel);
			$data->setValue("name", $name);
			$data->setValue("incCount", $incCount);
			$data->setValue("signatures", $signatures);
			$data->store();

			/*
			$insertQuery = sprintf("REPLACE INTO %sboardsettings(ID, viewmode, seclevel, name, inc_count, signatures) ".
				"VALUES('%s', '%s', '%s', '%s', '%s', '%s')",
				$TBBconfiguration->tablePrefix,
				$this->privateVars['dbData']['ID'],
				addSlashes($viewMode),
				addSlashes($theSecLevel),
				addSlashes($name),
				($incCount) ? "yes": "no",
				($signatures) ? "yes": "no"
				);
			$database->executeQuery($insertQuery);
			*/
			return true;
		}

		function updateAllowedTags($tagIDarray) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			// get the current tags
			/*
			$id = addSlashes($this->getID());
			$selectQuery = sprintf(
				"SELECT * FROM %sboardtags WHERE `settingID`='%s'",
				$TBBconfiguration->tablePrefix,
				$id
			);
			$selectResult = $database->executeQuery($selectQuery);
			*/
			$boardTags = new BoardTagsTable($database);
			$filter = new DataFilter();
			$filter->addEquals("settingID", $this->getID());
			$sorting = new ColumnSorting();
			$boardTags->selectRows($filter, $sorting);
			$idList = array();
			while ($row = $boardTags->getRow()) {
				$tagID = $row->getValue("tagID");
				$rowID = $row->getValue("ID");
				$idList[$tagID] = array("id" => $rowID, "tagID" => $tagID, "flag" => "delete");
			}
			$removeList = array();
			$addList = array();

			$addArray = array();
			// Check what needs to be done with the current tags
			for ($i = 0; $i < count($tagIDarray); $i++) {
				$newTagID = $tagIDarray[$i];
				if (isSet($idList[$newTagID])) { // Tag bestaat al
					$idList[$newTagID]["flag"] = "leave";
				} else {
					//$addArray[] = "'".addSlashes($newTagID)."', '".$id."'";
					$addArray[] = $newTagID;
				}
			}

			$deleteArray = array();
			reset($idList);
			while (list($key, $info) = each($idList)) {
				if ($info["flag"] == "delete") $deleteArray[] = $info["id"];
			}

			if (count($deleteArray) > 0) {
				$dataFilter = new DataFilter();
				$dataFilter->setMode("or");
				for ($i = 0; $i < count($deleteArray); $i++) {
					$dataFilter->addEquals("ID", $deleteArray[$i]);
				}
				$boardTags->deleteRows($dataFilter);
				/*
				$deleteQuery = sprintf(
					"DELETE FROM %sboardtags WHERE `ID`='%s' LIMIT %s",
					$TBBconfiguration->tablePrefix,
					implode("' OR `ID`='", $deleteArray),
					count($deleteArray)
				);
				$database->executeQuery($deleteQuery);
				*/
			}
			if (count($addArray) > 0) {
				for ($i = 0; $i < count($addArray); $i++) {
					$newTag = $boardTags->addRow();
					$newTag->setValue("tagID", $addArray[$i]);
					$newTag->setValue("settingID", $this->getID());
					$newTag->store();
				}
				/*
				$insertQuery = sprintf(
					"INSERT INTO %sboardtags (`tagID`, `settingID`) VALUES (%s)",
					$TBBconfiguration->tablePrefix,
					implode("), (", $addArray)
				);
				$database->executeQuery($insertQuery);
				*/
			}
		}

	}

	class BoardProfileList {

		var $privateVars;

		function BoardProfileList() {
			$this->privateVars = array();
			$this->privateVars['profiles'] = array();
			$this->privateVars['cacheID'] = array();
			$this->privateVars['profilesRead'] = false;
		}

		function delBoardProfile($id) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$boardSettingsTable = new BoardSettingsTable($database);
			$boardProfile = $boardSettingsTable->getRowByKey($id);
			$boardProfile->delete();
		}

		function addBoardProfile($name, $viewMode, $secLevel, $incCount, $signatures) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$theSecLevel = 'none';
			switch($secLevel) {
				case '1': $theSecLevel = 'low'; break;
				case '2': $theSecLevel = 'medium'; break;
				case '3': $theSecLevel = 'high'; break;
			}

			$boardSettingsTable = new BoardSettingsTable($database);
			$newSettings = $boardSettingsTable->addRow();
			$newSettings->setValue("viewMode", $viewMode);
			$newSettings->setValue("secLevel", $theSecLevel);
			$newSettings->setValue("name", $name);
			$newSettings->setValue("incCount", $incCount);
			$newSettings->setValue("signatures", $signatures);
			if (!$newSettings->store()) return false;

			$newID = $newSettings->getValue("ID");
			$boardProfile = new BoardProfile();
			$boardProfile->p_setDBdata($newSettings);

			$this->privateVars['cacheID'][$newID] = $boardProfile;
			/*
			$insertQuery = sprintf("INSERT INTO %sboardsettings(viewmode, seclevel, name, inc_count, signatures) ".
				"VALUES('%s', '%s', '%s', '%s', '%s')",
				$TBBconfiguration->tablePrefix,
				addSlashes($viewMode),
				addSlashes($theSecLevel),
				addSlashes($name),
				($incCount) ? "yes": "no",
				($signatures) ? "yes": "no"
				);
			$database->executeQuery($insertQuery);
			*/
			return $newID;
		}

		function getProfiles() {
			if ($this->privateVars['profilesRead']) {
				return $this->privateVars['profiles'];
			}
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$boardSettingsTable = new BoardSettingsTable($database);
			$filter = new DataFilter();
			$sorting = new ColumnSorting();
			$sorting->addColumnSort("name", true);
			$boardSettingsTable->selectRows($filter, $sorting);

			/*
			$selectQuery = sprintf("SELECT * FROM %sboardsettings ORDER BY name ASC", $TBBconfiguration->tablePrefix);
			$selectResult = $database->executeQuery($selectQuery);
			*/
			while ($profileData = $boardSettingsTable->getRow()) {
				$profile = new BoardProfile();
				$profile->p_setDBdata($profileData);
				$this->privateVars['profiles'][] = $profile;
				$this->privateVars['cacheID'][$profile->getID()] = $profile;
			}
			$this->privateVars['profilesRead'] = true;
			return $this->privateVars['profiles'];
		}

		function getBoardProfile($id) {
			if (isSet($this->privateVars['cacheID'][$id])) {
				return $this->privateVars['cacheID'][$id];
			}
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$boardSettingsTable = new BoardSettingsTable($database);
			if ($profileData = $boardSettingsTable->getRowByKey($id)) {
				$profile = new BoardProfile();
				$profile->p_setDBdata($profileData);
				$this->privateVars['cacheID'][$profile->getID()] = $profile;
				return $profile;
			}
			return false;
		}

	}

	$GLOBALS["TBBboardProfileList"] = new BoardProfileList();

?>
