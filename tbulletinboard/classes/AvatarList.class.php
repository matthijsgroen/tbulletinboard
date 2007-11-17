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

	require_once($TBBclassDir."Avatar.bean.php");

	class AvatarList {

		var $privateVars;

		function TopicIconList() {
			$this->privateVars = array();
			$this->privateVars['readAvatars'] = false;
			$this->privateVars['avatars'] = array();
		}

		function addSystemAvatar($fileName) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$avatarTable = new AvatarTable($database);
			$newRow = $avatarTable->addRow();
			$newRow->setValue("type", "system");
			$newRow->setValue("imgUrl", $fileName);
			$newRow->store();
			return $newRow->getValue("ID");
			/*
			$insertQuery = sprintf(
				"INSERT INTO %savatar(`type`, `imgUrl`) VALUES('system', '%s')",
				$TBBconfiguration->tablePrefix,
				addSlashes($fileName));

			$result = $database->executeQuery($insertQuery);
			return $result->getInsertID();
			*/
		}

		function addUserAvatar($fileName, $userID) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$avatarTable = new AvatarTable($database);
			$newRow = $avatarTable->addRow();
			$newRow->setValue("type", "custom");
			$newRow->setValue("imgUrl", $fileName);
			$newRow->setValue("userID", $userID);
			$newRow->store();
			return $newRow->getValue("ID");
			/*
			$insertQuery = sprintf(
				"INSERT INTO %savatar(`type`, `imgUrl`) VALUES('custom', '%s')",
				$TBBconfiguration->tablePrefix,
				addSlashes($fileName));
			$result = $database->executeQuery($insertQuery);
			return $result->getInsertID();
			*/
		}

		function getSystemAvatarInfo() {
			if ($this->privateVars['readAvatars']) {
				return $this->privateVars['avatars'];
			}
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$result = array();

			$avatarTable = new AvatarTable($database);
			$filter = new DataFilter();
			$filter->addEquals("type", "system");

			$avatarTable->selectRows($filter, new ColumnSorting());

			/*
			$selectQuery = sprintf("SELECT * FROM %savatar WHERE `type` = 'system'",
				$TBBconfiguration->tablePrefix);
			$selectResult = $database->executeQuery($selectQuery);
			*/
			while ($avatarData = $avatarTable->getRow()) {
				$avatar = array();
				$avatar['ID'] = $avatarData->getValue('ID');
				$result[] = $avatar;
			}
			$this->privateVars['readAvatars'] = true;
			$this->privateVars['avatars'] = $result;
			return $result;
		}

		function getUserAvatarInfo($userID) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$result = array();

			$avatarTable = new AvatarTable($database);
			$filter = new DataFilter();
			$filter->addEquals("type", "custom");
			$filter->addEquals("userID", $userID);

			$avatarTable->selectRows($filter, new ColumnSorting());

			/*
			$selectQuery = sprintf("SELECT * FROM %savatar WHERE `type` = 'system'",
				$TBBconfiguration->tablePrefix);
			$selectResult = $database->executeQuery($selectQuery);
			*/
			while ($avatarData = $avatarTable->getRow()) {
				$avatar = array();
				$avatar['ID'] = $avatarData->getValue('ID');
				$result[] = $avatar;
			}
			return $result;
		}

		function getLocalName($id) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			/*
			$selectQuery = sprintf(
				"SELECT * FROM %savatar WHERE `ID`='%s'",
				$TBBconfiguration->tablePrefix,
				addSlashes($id));
			$selectResult = $database->executeQuery($selectQuery);
			*/
			$avatarTable = new AvatarTable($database);
			if ($avatarRow = $avatarTable->getRowByKey($id)) {
				return $TBBconfiguration->uploadDir . 'systemavatars/' . $avatarRow->getValue('imgUrl');
			}
			return false;
		}

		function isSystemAvatar($id) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$avatarTable = new AvatarTable($database);
			/*
			$selectQuery = sprintf(
				"SELECT * FROM %savatar WHERE `ID`='%s'",
				$TBBconfiguration->tablePrefix,
				addSlashes($id));
			$selectResult = $database->executeQuery($selectQuery);
			*/
			if ($avatarRow = $avatarTable->getRowByKey($id)) {
				return ($avatarRow->getValue('type') == 'system') ? true : false;
			}
			return false;
		}

		function removeAvatar($id) {
			$localName = $this->getLocalName($id);
			if (!unlink($localName)) return false;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$avatarTable = new AvatarTable($database);
			$avatarRow = $avatarTable->getRowByKey($id);
			$avatarRow->delete();

			$userTable = new UserTable($database);

			$filter = new DataFilter();
			$filter->addEquals("avatarID", $id);
			$mutations = new DataMutation();
			$mutations->setEquals('avatarID', 0);
			$userTable->executeDataMutations($mutations, $filter);
			return true;
		}

		function removeUserAvatar(&$user, $id) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$avatarTable = new AvatarTable($database);
			$avatarRow = $avatarTable->getRowByKey($id);
			if ($avatarRow->getValue("type") != 'custom') return false;
			if ($avatarRow->getValue("userID") != $user->getUserID()) return false;

			$localName = $TBBconfiguration->uploadDir . 'systemavatars/' . $avatarRow->getValue('imgUrl');
			if (!unlink($localName)) return false;
			$avatarRow->delete();

			$userTable = new UserTable($database);

			$filter = new DataFilter();
			$filter->addEquals("avatarID", $id);
			$mutations = new DataMutation();
			$mutations->setEquals('avatarID', 0);
			$userTable->executeDataMutations($mutations, $filter);
			return true;
		}

	}

?>
