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

	class TravianMemberModule extends MemberModule {

		function TravianMemberModule() {
		}

		function getModuleDescription() {
			return "Travian Alliance leden systeem";
		}

		function hasMoreAddGroupSteps($currentStep) {
			return ($currentStep < 2);
		}

		function getAddGroupForm(&$form, &$formFields, $currentStep) {
			global $ivLibDir;			
			require_once($ivLibDir.'formcomponents/TextField.class.php');

			if ($currentStep == 1) {
				$form->addHiddenField("actionName", "setAlliance");
				//$formFields->addSelect("groupID", "Groep", "TBB groep typen", $values, "0");
				$form->addComponent(new FormTextField("alliance", "Alliance", "naam van de alliance in Travian", 50, true));
				//$name, $title, $description, $maxlength, $required = false
				$formFields->addSubmit("Toevoegen", false);
			}
		}

		function handleAddGroupAction(&$feedback) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			if ($_POST['actionName'] == "setAlliance") {
				if (!isSet($_POST['alliance'])) {
					$feedback->addMessage("alliance niet gevonden!");
					return false;
				}

				$allianceName = $_POST['alliance'];
				$allianceID = -1;
				$selectQuery = sprintf("SELECT * FROM x_world WHERE alliance='%s'",
					addSlashes($allianceName));
				$selectResult = $database->executeQuery($selectQuery);
				if ($isRow = $selectResult->getRow()) {
					$allianceID = $isRow['aid'];
				} else {
					$feedback->addMessage("Travian alliantie niet gevonden! (<strong>".htmlConvert($_POST['alliance'])."</strong>)");
					return false;
				}

				$selectQuery = sprintf("SELECT * FROM %sgroup WHERE moduleID='%s' AND groupID='%s'",
					$TBBconfiguration->tablePrefix, addSlashes($this->getModuleName()), addSlashes($allianceID));
				$selectResult = $database->executeQuery($selectQuery);
				if ($isRow = $selectResult->getRow()) {
					$feedback->addMessage("Soortgelijke groep bestaat al! (<strong>".htmlConvert($isRow['name'])."</strong>)");
					return false;
				}
				global $TBBmemberGroupList;
				$TBBmemberGroupList->addMemberGroup($allianceName, $this->getModuleName(), $allianceID);
				$feedback->addMessage("Groep <strong>".htmlConvert($allianceName)."</strong> toegevoegd!");
				return true;
			}
			return false;
		}

		function isMemberOfGroup(&$user, $groupIDstr) {
			global $TBBsession;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$selectQuery = sprintf("SELECT * FROM tbb_travian_user WHERE allianceID='%s' AND tbbID='%s'",
				addSlashes($groupIDstr), $user->getUserID());
			$selectResult = $database->executeQuery($selectQuery);
			if ($isRow = $selectResult->getRow()) {
				return true;
			}
			return false;
		}

		function getUserInfo(&$user) {
			global $TBBcurrentUser;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$selectQuery = sprintf("SELECT * FROM tbb_travian_user WHERE tbbID='%s'", $TBBcurrentUser->getUserID());
			$selectResult = $database->executeQuery($selectQuery);
			if ($isRow = $selectResult->getRow()) {

			} else return "";

			$selectQuery = sprintf("SELECT * FROM tbb_travian_user WHERE tbbID='%s'", $user->getUserID());
			$selectResult = $database->executeQuery($selectQuery);
			if ($isRow = $selectResult->getRow()) {
				$race = "unknown";
				// 1 = Roman, 2 = Teuton, 3 = Gaul, 4 = Nature and 5 = Natars
				if ($isRow['race'] == '1') $race = 'Romeins';
				if ($isRow['race'] == '2') $race = 'Germaans';
				if ($isRow['race'] == '3') $race = 'Gallier';
				return sprintf(
					'<br />'.
					'<span class="postCount"><b>%s</b></span><br />'.
					'<span class="postCount">Alliantie: %s</span><br />'.
					'<span class="postCount">Volk: %s</span><br />'.
					'<span class="postCount">Pop: %s</span><br />'.
					'<span class="postCount">Dorpen: %s</span><br />',
					$isRow['travianName'],
					$isRow['alliance'], $race, $isRow['pop'], $isRow['vill']);
			}
			return "";

		}

		function getUserPageData(&$user, &$table) {
			global $TBBcurrentUser;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$selectQuery = sprintf("SELECT * FROM tbb_travian_user WHERE tbbID='%s'", $TBBcurrentUser->getUserID());
			$selectResult = $database->executeQuery($selectQuery);
			if ($isRow = $selectResult->getRow()) {

			} else return;

			$selectQuery = sprintf("SELECT * FROM tbb_travian_user WHERE tbbID='%s'", $user->getUserID());
			$selectResult = $database->executeQuery($selectQuery);
			if ($isRow = $selectResult->getRow()) {
				$race = "unknown";
				// 1 = Roman, 2 = Teuton, 3 = Gaul, 4 = Nature and 5 = Natars
				if ($isRow['race'] == '1') $race = 'Romeins';
				if ($isRow['race'] == '2') $race = 'Germaans';
				if ($isRow['race'] == '3') $race = 'Gallier';
				/*
				return sprintf(
					'<br />'.
					'<span class="postCount"><b>%s</b></span><br />'.
					'<span class="postCount">Alliance: %s</span><br />'.
					'<span class="postCount">Race: %s</span><br />'.
					'<span class="postCount">Pop: %s</span><br />'.
					'<span class="postCount">Villages: %s</span><br />',
					$isRow['travianName'],
					$isRow['alliance'], $race, $isRow['pop'], $isRow['vill']);
				*/
				$table->addGroup("Travian");
				$table->addRow("Name", $isRow['travianName']);
				$table->addRow("Alliantie", $isRow['alliance']);
				$table->addRow("Volk", $race);
				$table->addRow("Bevolking", $isRow['pop']);
				$table->addRow("Dorpen", $isRow['vill']);
				
			}
		
		}

	}

?>
