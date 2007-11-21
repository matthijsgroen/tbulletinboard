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

	importClass("board.MemberModule");
	importClass("board.MemberGroups");

	class BoardMemberModule extends MemberModule {

		var $groupNames;

		function BoardMemberModule() {
			$this->groupNames = array();
			$this->groupNames['0'] = 'Iedereen';
			$this->groupNames['1'] = 'Leden';
			$this->groupNames['2'] = 'Moderators';
			$this->groupNames['3'] = 'Administrators';
			$this->groupNames['4'] = 'Masters';
		}

		function getModuleDescription() {
			return "TBB leden systeem (administrators, moderators, leden, iedereen)";
		}

		function hasMoreAddGroupSteps($currentStep) {
			return ($currentStep < 2);
		}

		function getAddGroupForm(&$form, &$formFields, $currentStep) {
			if ($currentStep == 1) {
				$form->addHiddenField("actionName", "setGroup");
				$values = array();
				reset($this->groupNames);
				while (list($name, $value) = each($this->groupNames)) {
					$values[$name] = $value;
				}
				$formFields->addSelect("groupID", "Groep", "TBB groep typen", $values, "0");
				$formFields->addSubmit("Toevoegen", false);
			}
		}

		function handleAddGroupAction(&$feedback) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			if ($_POST['actionName'] == "setGroup") {
				if (!isSet($_POST['groupID'])) {
					$feedback->addMessage("GroupID niet gevonden!");
					return false;
				}
				if (!is_numeric($_POST['groupID'])) {
					$feedback->addMessage("ongeldige GroupID!");
					return false;
				}
				$groupID = $_POST['groupID'];
				$selectQuery = sprintf("SELECT * FROM %sgroup WHERE moduleID='%s' AND groupID='%s'",
					$TBBconfiguration->tablePrefix, addSlashes($this->getModuleName()), addSlashes($groupID));
				$selectResult = $database->executeQuery($selectQuery);
				if ($isRow = $selectResult->getRow()) {
					$feedback->addMessage("Soortgelijke groep bestaat al! (<strong>".htmlConvert($isRow['name'])."</strong>)");
					return false;
				}
				global $TBBmemberGroupList;
				$TBBmemberGroupList->addMemberGroup($this->groupNames[$groupID], $this->getModuleName(), $groupID);
				$feedback->addMessage("Groep <strong>".htmlConvert($this->groupNames[$groupID])."</strong> toegevoegd!");
				return true;
			}
			return false;
		}

		function isMemberOfGroup(&$user, $groupIDstr) {
			global $TBBsession;
			if ($groupIDstr == '0') return true;
			if ($groupIDstr == '1') {
				return $TBBsession->isLoggedIn();
			}
			if ($groupIDstr == '3') {
				return $user->isAdministrator();
			}
			if ($groupIDstr == '4') {
				return $user->isMaster();
			}

			return false;
		}

		function getUserInfo(&$user) {
			global $TBBcurrentUser;
			return sprintf(
				'%s<br /><span class="postCount">Aantal berichten: %s</span>',
				(($user->getAvatarID() != 0) && ($TBBcurrentUser->showAvatars())) ? '<img src="avatar.php?id='.$user->getAvatarID().'" alt="avatar" />' : '',
				$user->getPostCount());
		}

	}

?>
