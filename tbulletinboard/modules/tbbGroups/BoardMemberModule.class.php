<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
	 */
	global $TBBclassDir;
	require_once($TBBclassDir.'MemberModule.class.php');
	require_once($TBBclassDir.'MemberGroups.class.php');

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
