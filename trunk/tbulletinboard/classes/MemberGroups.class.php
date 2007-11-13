<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
	 */
	require_once($TBBclassDir . "Group.bean.php");

	class MemberGroup {

		var $privateVars;

		function MemberGroup() {
			$this->privateVars = array();
			$this->privateVars['memberCache'] = array();
		}

		function p_setDBdata(&$data) {
			$this->privateVars['dbData'] = $data;
		}

		function getModuleID() {
			$data = $this->privateVars['dbData'];
			return $data->getValue("moduleID");
		}

		function getModule() {
			$id = $this->getModuleID();
			global $TBBModuleManager;
			return $TBBModuleManager->getPlugin($id, "usertype");
		}

		function getName() {
			$data = $this->privateVars['dbData'];
			return $data->getValue("name");
		}

		function getID() {
			$data = $this->privateVars['dbData'];
			return $data->getValue("ID");
		}

		function isMember(&$user) {
			if (isSet($this->privateVars['memberCache'][$user->getUserID()])) {
				return $this->privateVars['memberCache'][$user->getUserID()];
			}
			$data = $this->privateVars['dbData'];
			$groupIDstr = $data->getValue("groupID");
			$module = $this->getModule();
			$isMember = $module->isMemberOfGroup($user, $groupIDstr);
			$this->privateVars['memberCache'][$user->getUserID()] = $isMember;
			return $isMember;
		}

	}

	class MemberGroupList {

		// Private variables
		var $privateVars;

		function MemberGroupList() {
			$this->privateVars = array();
			$this->privateVars['usergroups'] = array();
			$this->privateVars['cacheID'] = array();
			$this->privateVars['usergroupsRead'] = false;
		}

		function addMemberGroup($name, $moduleID, $groupID) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$groupTable = new GroupTable($database);
			$newGroup = $groupTable->addRow();
			$newGroup->setValue("name", $name);
			$newGroup->setValue("moduleID", $moduleID);
			$newGroup->setValue("groupID", $groupID);
			$newGroup->store();
			/*
			$insertQuery = sprintf("INSERT INTO %sgroup(name, moduleID, groupID) VALUES('%s', '%s', '%s')",
				$TBBconfiguration->tablePrefix, addSlashes($name), addSlashes($moduleID), addSlashes($groupID));
			$database->executeQuery($insertQuery);
			*/
		}

		function getMemberGroups() {
			if ($this->privateVars['usergroupsRead']) {
				return $this->privateVars['usergroups'];
			}
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$dataFilter = new DataFilter();
			$sorting = new ColumnSorting();
			$sorting->addColumnSort("moduleID", true);
			$sorting->addColumnSort("name", true);
			$groupTable = new GroupTable($database);
			$groupTable->selectRows($dataFilter, $sorting);
			/*
			$selectQuery = sprintf("SELECT * FROM %sgroup ORDER BY moduleID, name ASC", $TBBconfiguration->tablePrefix);
			$selectResult = $database->executeQuery($selectQuery);
			*/
			$groups = array();
			while($groupInfo = $groupTable->getRow()) {
				$group = new MemberGroup();
				$group->p_setDBdata($groupInfo);
				$this->privateVars['cacheID'][$group->getID()] = $group;
				$groups[] = $group;
			}
			$this->privateVars['usergroups'] = $groups;
			$this->privateVars['usergroupsRead'] =  true;
			return $groups;
		}

		function getMemberGroup($id) {
			if (isSet($this->privateVars['cacheID'][$id])) {
				return $this->privateVars['cacheID'][$id];
			}
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$groupTable = new GroupTable($database);
			if ($groupInfo = $groupTable->getRowByKey($id)) {
				$group = new MemberGroup();
				$group->p_setDBdata($groupInfo);
				$this->privateVars['cacheID'][$group->getID()] = $group;
				return $group;
			}
			$this->privateVars['cacheID'][$id] = false;
			return false;
		}

	}

	$GLOBALS['TBBmemberGroupList'] = new MemberGroupList();

?>
