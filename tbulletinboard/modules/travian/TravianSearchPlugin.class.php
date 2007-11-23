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

	importClass("board.search.SearchPlugin");
	importClass("board.BoardFormFields");
	importClass("interface.FormFields");

	class TravianSearchPlugin extends SearchPlugin {
		
		private $userAccessCache = Array();
		private $userTravianCache = Array();

		function TravianSearchPlugin() {
			$this->SearchPlugin();
		}

		function getSearchName() {
			return "Travian Doelwit";
		}

		function hasAccess(&$user) {
			if (isSet($this->userAccessCache[$user->getUserID()])) return $this->userAccessCache[$user->getUserID()];
			global $TBBcurrentUser;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$selectQuery = sprintf("SELECT * FROM tbb_travian_user WHERE tbbID='%s'", $user->getUserID());
			$selectResult = $database->executeQuery($selectQuery);
			if ($isRow = $selectResult->getRow()) {
				$this->userAccessCache[$user->getUserID()] = true;
				$this->userTravianCache[$user->getUserID()] = $isRow;
				return true;
			}
			$this->userAccessCache[$user->getUserID()] = false;
			return false;
		}

		function buildSearchForm(&$form, $step, $boardID) {
			global $libraryClassDir;
			global $formTitleTemplate;
			includeFormComponents("TextField", "Submit", "Select", "TemplateField", "RadioGroup", "RadioButton", "Time", "NumberField", "Checkbox");
		
			$form->addComponent(new FormTemplateField($formTitleTemplate, "Doelwit zoeken"));
			// $rows = 1, $change="", $focus="",
			//	$enabled=true, $prefix="", $postfix="", $multiple = false, $minChoice = 0, $maxChoice = 0
			$raceSelect = new FormSelect("Ras", "Voorkeursras", "race", 3, "", "", true, "", "", true, 1);
			//$raceSelect->addComponent(new FormOption("Maakt niet uit", 0));
			$raceSelect->addComponent(new FormOption("Romeins", 1));
			$raceSelect->addComponent(new FormOption("Germaans", 2));
			$raceSelect->addComponent(new FormOption("Gallisch", 3));
			$form->addComponent($raceSelect);
			
			global $TBBcurrentUser;
			$viewRace = $this->userTravianCache[$TBBcurrentUser->getUserID()]['race'];
			
			$villageSelect = new FormSelect("Dorp", "Aanvalsdorp", "village", 1, "villageSelectChange(element)");

			global $TBBconfiguration;
			$moduleDir = $this->getModuleDir();
			$database = $TBBconfiguration->getDatabase();

			require_once($moduleDir . "TravianPlace.bean.php");
			$travianTable = new TravianPlaceTable($database);

			$userFilter = new DataFilter();
			$userFilter->addEquals("playerID", $this->userTravianCache[$TBBcurrentUser->getUserID()]['travianID']);
			$sorting = new ColumnSorting();
			$sorting->addColumnSort("villageName", true);
			$travianTable->selectRows($userFilter, $sorting);
			
			$ownVillages = new FormOptionGroup("Eigen dorpen");
			$defaultMax = 0;
			
			$presel = "";
			if (isSet($_GET['presel'])) {
				$form->setValue("village", $_GET['presel']);
				$presel = $_GET['presel'];
				$info = explode("|", $_GET['presel']);
				$viewRace = $info[2];
			}

			$travianArray = array();
			while ($village = $travianTable->getRow()) {
				if ($defaultMax == 0) $defaultMax = $village->getValue("population");
				$value = $village->getValue("x")."|".$village->getValue("y")."|".$village->getValue("race");
				$travianArray[$village->getValue("ID")] = $value;
				if ($presel == $value) $defaultMax = $village->getValue("population");
				$ownVillages->addComponent(new FormOption($village->getValue("villageName")." (".$village->getValue("population").")", 
					$value));
			}
			$villageSelect->addComponent($ownVillages);
			
			require_once($moduleDir . "TravianSitter.bean.php");
			$sitterTable = new TravianSitterTable($database);

			$filter = new DataFilter();
			$filter->addEquals("travianID", $this->userTravianCache[$TBBcurrentUser->getUserID()]['travianID']);
			$sitterTable->selectRows($filter, new ColumnSorting());
			if ($sitterTable->getSelectedRowCount() > 0) {
				$sitterVillages = new FormOptionGroup("Sitter dorpen");
				while ($villageInfo = $sitterTable->getRow()) {
					$userFilter = new DataFilter();
					
					$userFilter->addEquals("playerID", $villageInfo->getValue("userTravianID"));

					$sorting = new ColumnSorting();
					$sorting->addColumnSort("villageName", true);
					$travianTable->selectRows($userFilter, $sorting);
			
					while ($village = $travianTable->getRow()) {
						$value = $village->getValue("x")."|".$village->getValue("y")."|".$village->getValue("race");
						$travianArray[$village->getValue("ID")] = $value;
						if ($presel == $value) $defaultMax = $village->getValue("population");

						$sitterVillages->addComponent(new FormOption($village->getValue("villageName")." (".$village->getValue("population").")", 
							$value));
					}				
				}				
				$villageSelect->addComponent($sitterVillages);
			}
			
			$form->addComponent($villageSelect);
			$script = new Javascript();
			$script->startFunction("villageSelectChange", array("selectbox"));
			$script->addLine("var viewRace = '".$viewRace."';");
			$script->addLine("var value = selectbox.options[selectbox.selectedIndex].value;");
			$script->addLine("var selRace = value.substr(value.lastIndexOf(\"|\") + 1);");
			$script->addLine("if (selRace != viewRace)");
			$script->addLine("document.location.href = 'search.php?type=travian&presel='+value;");
			$script->endBlock();
			print $script->toString();			
			
					
			$unitTypes = new FormRadioGroup("Traagste", "Langzaamste unit", "speed");
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
			
			$selected = true;
			$index = 1;
			foreach($unitList as $unitName => $speed) {
				$unitTypes->addComponent(new FormRadioButton(
					sprintf('<img src="%1$s/images/%2$s.gif" alt="icon" title="%3$s" /> %3$s', 
					$this->getModuleOnlineDir(), ($index + (($viewRace-1) * 10)), $unitName), 
					"(".$speed." fields/hour)", "speed", $speed, $selected));
				$selected = false;
				$index++;
			}			
			
			$form->addComponent($unitTypes);
			$form->addComponent(new FormNumberField("minpop", "Min grootte", "optioneel", 5, false, false, "", " inwoners"));
			$form->addComponent(new FormNumberField("maxpop", "Max grootte", "verplicht", 5, true, false, "", " inwoners"));
			$form->setValue("maxpop", ($defaultMax));
			
			$form->addComponent(new FormTime("Minimale reistijd", "optioneel", "mintime", false));
			$form->addComponent(new FormTime("Maximale reistijd", "verplicht", "maxtime", true));
			$form->setValue("maxtime", "1:00");
			
			$form->addComponent(new FormCheckbox("Alleen zonder alliantie tonen", "Alliantie", "", "noAlliance", "no"));
			$form->addComponent(new FormTextField("alliance", "Alliantie", "optioneel", 25, false));
			$form->addComponent(new FormSubmit("Zoeken", "", "", "submitButton")); //$caption, $title, $description, $name, $onclick = ""
		}

		function hasMoreSearchFormSteps($wizzStep) {
			return ($wizzStep < 1);
		}

		function handleSearchForm(&$feedback, &$form, $step) {
			return $form->checkPostedFields($feedback);
		}

		function executeSearch(&$searchResult, &$feedback) {
			$searchResult->setSearchSubject("doelwit", "doelwitten");
			
			$searchResult->defineColumnNames("Farm", "x", "y", "Ras", "Village", "Player", "Population", "Alliance", "Time");
			$searchResult->defineColumnTypes("text", "number", "number", "number", "text", "text", "number", "text", "number");
			$searchResult->defineSortColumns(3, 6, 8);
			
			/*			
			
			maxtravel = 1:10 = 70 mins

			fieldspeed = (60 / 19) = 3,15789
			maxtravel / fieldspeed = 22.16667


			SELECT *
			FROM `x_world`
			WHERE x > ( 56 -23 )
			AND x < ( 56 +23 )
			AND y > ( -200 -23 )
			AND y < ( -200 +23 )
			AND tid =1
			AND `population` <200
			LIMIT 0 , 30
			*/
			$moduleDir = $this->getModuleDir();
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			require_once($moduleDir . "TravianPlace.bean.php");
			$travianPlaceTable = new TravianPlaceTable($database);
			$coords = explode("|", $_POST['village']);
			$speed = $_POST['speed'];
			$minTime = 0;
			if ($_POST['mintime_hours'] != "") {
				$minTime = $_POST['mintime_hours'] * 60 + $_POST['mintime_minutes'];
			}
			$maxTime = $_POST['maxtime_hours'] * 60 + $_POST['maxtime_minutes'];
			$fieldDistance = $maxTime / (60 / $speed);
			
			$searchFilter = new DataFilter();
			$searchFilter->addGreaterThan("x", $coords[0] - $fieldDistance);
			$searchFilter->addLessThan("x", $coords[0] + $fieldDistance);
			$searchFilter->addGreaterThan("y", $coords[1] - $fieldDistance);
			$searchFilter->addLessThan("y", $coords[1] + $fieldDistance);
			//print_r($_POST);
			
			$raceFilter = new DataFilter();
			$raceFilter->setMode("or");
			for ($i = 0; $i < count($_POST['race']); $i++) {
				$raceFilter->addEquals("race", $_POST['race'][$i]);
			}
			
			$searchFilter->addDataFilter($raceFilter);
			//if ($_POST['race'] != 0)
			//	$searchFilter->addEquals("race", $_POST['race']);
			if ($_POST['minpop'] != "")
				$searchFilter->addGreaterThanOrEquals("population", $_POST['minpop']);
			$searchFilter->addLessThanOrEquals("population", $_POST['maxpop']);
			
			if (isSet($_POST['noAlliance']) && ($_POST['noAlliance'] == "no")) {
				$searchFilter->addLike("allianceID", 0);
			} else
			if ($_POST['alliance'] != "")
				$searchFilter->addLike("allianceName", "%".$_POST['alliance']."%");
			
			$travianPlaceTable->selectRows($searchFilter, new ColumnSorting());
			
			while ($resultRow = $travianPlaceTable->getRow()) {			
				$xDist = abs($coords[0] - $resultRow->getValue("x"));
				$yDist = abs($coords[1] - $resultRow->getValue("y"));
				
				$fieldDistance = sqrt(($xDist * $xDist) + ($yDist * $yDist));
				
				$minutes = $fieldDistance * (60.0 / $speed);
				if (($minutes < $maxTime) && ($minutes > $minTime)) {
					$mins = (floor($minutes) % 60);
					$secs = floor(($minutes - floor($minutes)) * 60);
					$time = floor($minutes / 60) . ":" . (($mins < 10) ? "0" : ""). $mins . ":" . (($secs < 10) ? "0" : ""). $secs;
					$searchResult->addResultRow(9000.0 - $fieldDistance,
						"",
						sprintf('<img src="%1$s/farmtarget.php?id=%2$s" alt="" title="Groen = farm, Rood = do not farm" onclick="this.src=\'%1$s/farmtarget.php?id=%2$s&toggle=true&rnd=\'+Math.round(100*Math.random())" />',
							$this->getModuleOnlineDir(), $resultRow->getValue("ID")),
						$resultRow->getValue("x"), 
						$resultRow->getValue("x"), 
						$resultRow->getValue("y"), 
						$resultRow->getValue("y"),
						$resultRow->getValue("race"),
						$this->getRaceName($resultRow->getValue("race")), 
						$resultRow->getValue("villageName"),
						$resultRow->getValue("villageName"),
						$resultRow->getValue("playerName"),
						$resultRow->getValue("playerName"),
						$resultRow->getValue("population"), 
						$resultRow->getValue("population"), 
						$resultRow->getValue("allianceName"), 
						$resultRow->getValue("allianceName"), 
						$fieldDistance, 
						$time);
				}
			}
			/*
			$searchResult->addResultRow(1.5, 40, 40, -40, -40, "smurfendorp", "smurfendorp", 50, 50, "SMURF", "SMURF", 70, "1:10");
			$searchResult->addResultRow(1.3, 24, 24, -65, -65, "dinges", "dinges", 150, 150, "SMURF", "SMURF", 35, "0:35");
			*/
			return true;
		}
		
		function getRaceName($raceID) {
			switch($raceID) {
				case "1": return "Romeins";
				case "2": return "Germaans";
				case "3": return "Gallisch";
			}
		}

	}

?>
