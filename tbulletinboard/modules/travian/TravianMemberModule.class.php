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

	importClass("board.plugin.MemberModule");
	importClass("board.user.MemberGroups");

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
			includeFormComponents("TextField")

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
			$moduleDir = $this->getModuleDir();

			$selectQuery = sprintf("SELECT * FROM tbb_travian_user WHERE tbbID='%s'", $TBBcurrentUser->getUserID());
			$selectResult = $database->executeQuery($selectQuery);
			if ($viewerRow = $selectResult->getRow()) {

			} else return;

			$selectQuery = sprintf("SELECT * FROM tbb_travian_user WHERE tbbID='%s'", $user->getUserID());
			$selectResult = $database->executeQuery($selectQuery);
			if ($userTravianRow = $selectResult->getRow()) {
				$race = "unknown";
				// 1 = Roman, 2 = Teuton, 3 = Gaul, 4 = Nature and 5 = Natars
				if ($userTravianRow['race'] == '1') $race = 'Romeins';
				if ($userTravianRow['race'] == '2') $race = 'Germaans';
				if ($userTravianRow['race'] == '3') $race = 'Gallier';
				$table->addGroup("Travian");
				$table->addRow("Name", $userTravianRow['travianName']);
				$table->addRow("Alliantie", $userTravianRow['alliance']);
				$table->addRow("Volk", $race);
				$table->addRow("Bevolking", $userTravianRow['pop']);
				$table->addRow("Dorpen", $userTravianRow['vill']);
				$selectQuery = sprintf("SELECT count(*) as sitterCount FROM tbb_travian_sitter WHERE userID='%s'", $user->getUserID());
				$selectResult = $database->executeQuery($selectQuery);
				if ($isRow = $selectResult->getRow()) {
					$table->addRow("Sitters", $isRow['sitterCount']);
				}
				if ($viewerRow['allianceID'] == $userTravianRow['allianceID']) {
					require_once($moduleDir . "TravianDetails.bean.php");
					$travianSitterTable = new TravianDetailsTable($database);
					$filter = new DataFilter();
					$filter->addEquals("userID", $user->getUserID());
					$sorting = new ColumnSorting();
					$travianSitterTable->selectRows($filter, $sorting);
					if ($detailInfo = $travianSitterTable->getRow()) {
						$table->addGroup("Travian Details");
						$table->addRow("Bijgewerkt op", $TBBconfiguration->parseDate($detailInfo->getValue("lastUpdated")));
						$production = "";
						if (!$detailInfo->isNull("woodPerHour")) $production .= sprintf('<img src="%1$s/images/%2$s.gif" alt="icon" />%3$s ', 
							$this->getModuleOnlineDir(), "wood", $detailInfo->getValue("woodPerHour"));
						if (!$detailInfo->isNull("clayPerHour")) $production .= sprintf('<img src="%1$s/images/%2$s.gif" alt="icon" />%3$s ', 
							$this->getModuleOnlineDir(), "clay", $detailInfo->getValue("clayPerHour"));
						if (!$detailInfo->isNull("ironPerHour")) $production .= sprintf('<img src="%1$s/images/%2$s.gif" alt="icon" />%3$s ', 
							$this->getModuleOnlineDir(), "iron", $detailInfo->getValue("ironPerHour"));
						if (!$detailInfo->isNull("cropPerHour")) $production .= sprintf('<img src="%1$s/images/%2$s.gif" alt="icon" />%3$s ', 
							$this->getModuleOnlineDir(), "crop", $detailInfo->getValue("cropPerHour"));
						if ($production != "") $table->addRow("Productie per uur", $production);
						$forces = "";
						$viewRace = $userTravianRow['race'];
						if ($viewRace == 1) {
							$unitList = array("Legionnaire" => 6, "Praetorian" => 5, "Imperian" => 7, "Equites Legati" => 16, 
								"Equites Imperatoris" => 14, "Equites Caesaris" => 10, "Battering Ram" => 4, "Fire Catapult" => 3, "Senator" => 4, "Settler" => 5);
						} else
						if ($viewRace == 2) {
							$unitList = array("Clubswinger" => 7, "Spearman" => 7, "Axeman" => 6, "Scout" => 9, 
								"Paladin" => 10, "Teutonic Knight" => 9, "Ram" => 4, "Catapult" => 3, "Chief" => 4, "Settler" => 5);
						} else
						if ($viewRace == 3) {
							$unitList = array("Phalanx" => 7, "Swordmen" => 6, "Scout" => 17, "Teutatis Thunder" => 19, 
								"Druid Rider" => 16, "Haeduan" => 13, "Ram" => 4, "Trebuchet" => 3, "Chieftain" => 5, "Settler" => 5);
						}

						$index = 1;
						foreach($unitList as $unitName => $speed) {
							if (!$detailInfo->isNull("unitType".$index) && ($detailInfo->getValue("unitType".$index) > 0)) {
								$forces .= sprintf('<img src="%1$s/images/%2$s.gif" alt="icon" title="%3$s" />%4$s <wbr />', 
									$this->getModuleOnlineDir(), ($index + (($viewRace-1) * 10)), $unitName, $detailInfo->getValue("unitType".$index));
							}
							$index++;
						}			
						if ($forces != "") $table->addRow("Troepen", $forces);
						$hero = "";
						if (!$detailInfo->isNull("heroLevel")) $hero .= $detailInfo->getValue("heroLevel");
							else $hero .= "?";
						if (!$detailInfo->isNull("heroXP")) $hero .= " (" . $detailInfo->getValue("heroXP") . "%)";
						if ($hero != "?") {
							$table->addRow("Held", sprintf('<img src="%1$s/images/%2$s.gif" alt="icon" /> Level %3$s <wbr />',
								$this->getModuleOnlineDir(), "hero", $hero));
						}
						if ($detailInfo->getValue("camping") == "yes") {
							$table->addRow("Overnachten", "Overnachten kan bij mij,<br /> maar op eigen risico!");
						}
						if ($detailInfo->getValue("camping") == "no") {
							$table->addRow("Overnachten", "Helaas kan je niet bij mij overnachten, <br /> ik wordt nog wel eens aangevallen of<br /> ik heb teweinig crop voor je troepen!");
						}

					}
				}
				
			}
		
		}

	}

?>
