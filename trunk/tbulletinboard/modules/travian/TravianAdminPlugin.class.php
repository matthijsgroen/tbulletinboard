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

	importClass("board.AdminPlugin");
	importClass("interface.Table");

	class TravianAdminPlugin extends AdminPlugin {
		var $privateVars;

		function TravianAdminPlugin() {
			$this->AdminPlugin();
		}

		function handlePageActions(&$feedback) {
		}

		function createMenu(&$menu) {
			$menu->addGroup("travian", "Travian");
			$menu->addItem("linkPlayer", "travian", "Beheer", 
				"adminplugin.php?id=".$this->getPluginID()."&screen=match", '', '', 0, false, '');
		}

		function selectMenuItem(&$menu) {
			$menu->itemIndex = "linkPlayer";
		}

		function getLocation(&$location) {
			$location->addLocation("Beheer", "adminplugin.php?id=".$this->getPluginID()."&screen=match");
		}

		function getPageTitle() {
			return "Beheer";
		}

		function getPage() {
			$moduleDir = $this->getModuleDir();
			$step = 1;
			if (isSet($_POST['actionName']) && isSet($_POST['actionID'])) {
				global $TBBsession;
				global $TBBconfiguration;
				global $TBBclassDir;
				$feedback = new Messages();
				if (($_POST['actionName'] == 'matchPlayer') && ($_POST['actionID'] == $TBBsession->getActionID())) {
					$correct = true;
					
					$database = $TBBconfiguration->getDatabase();
				
					$tbbUserName = $_POST['boardnick'];
					//require_once($TBBclassDir . "User.bean.php");
					importBean("board.User");
					$userTable = new UserTable($database);

					$userFilter = new DataFilter();
					$userFilter->addEquals("nickname", $tbbUserName);
					$userTable->selectRows($userFilter, new ColumnSorting());

					if ($userRow = $userTable->getRow()) {
						//$feedback->addMessage("TBB member found " . $userRow->getValue("nickname"));
					} else {
						$feedback->addMessage("Forum gebruiker niet gevonden");
						$correct = false;
					}
					if ($correct) {
						$travianNickname = $_POST['traviannick'];
						require_once($moduleDir . "TravianPlace.bean.php");
						$travianPlaceTable = new TravianPlaceTable($database);

						$locationFilter = new DataFilter();
						$locationFilter->addEquals("playerName", $travianNickname);
						$travianPlaceTable->selectRows($locationFilter, new ColumnSorting());

						if ($playerRow = $travianPlaceTable->getRow()) {
							//$feedback->addMessage("Player found " . $playerRow->getValue("playerName"));
							$population = $playerRow->getValue("population");
							$villages = 1;
							while ($otherVillages = $travianPlaceTable->getRow()) {
								$villages++;
								$population += $playerRow->getValue("population");
							}
						
						} else {
							$feedback->addMessage("Speler niet gevonden");
							$correct = false;
						}
					}
					if ($correct) {
						$step = 2;					
						$TBBsession->actionHandled();
					}
				}
				if (($_POST['actionName'] == 'matchConfirm') && ($_POST['actionID'] == $TBBsession->getActionID())) {
					$database = $TBBconfiguration->getDatabase();
				
					$boarUserID = $_POST['boaruserID'];
					//require_once($TBBclassDir . "User.bean.php");
					importBean("board.User");
					$userTable = new UserTable($database);

					$userRow = $userTable->getRowByKey($boarUserID);

					$travianUserID = $_POST['travianuserID'];
					require_once($moduleDir . "TravianPlace.bean.php");
					$travianPlaceTable = new TravianPlaceTable($database);

					$locationFilter = new DataFilter();
					$locationFilter->addEquals("playerID", $travianUserID);
					$travianPlaceTable->selectRows($locationFilter, new ColumnSorting());
					if ($playerRow = $travianPlaceTable->getRow()) {
						$population = $playerRow->getValue("population");
						$villages = 1;
						while ($otherVillages = $travianPlaceTable->getRow()) {
							$villages++;
							$population += $playerRow->getValue("population");
						}
					}
					require_once($moduleDir . "TravianPlayer.bean.php");
					$travianPlayerTable = new TravianPlayerTable($database);
					$connection = $travianPlayerTable->addRow();
					$connection->setValue("tbbID", $userRow->getValue("ID"));
					$connection->setValue("travianID", $playerRow->getValue("playerID"));
					$connection->setValue("travianName", $playerRow->getValue("playerName"));
					$connection->setValue("pop", $population);
					$connection->setValue("vill", $villages);
					$connection->setValue("race", $playerRow->getValue("race"));
					$connection->setValue("allianceName", $playerRow->getValue("allianceName"));
					$connection->setValue("allianceID", $playerRow->getValue("allianceID"));
					$connection->store();
					$feedback->addMessage("Travian speler gekoppeld aan Forum gebruiker");					
				
					$TBBsession->actionHandled();
				}
				
				$feedback->showMessages();
			}

			if (($_GET['screen'] == "match") && ($step == 1)) 
				include $moduleDir . "matchplayer.screen.php";
			if (($_GET['screen'] == "match") && ($step == 2)) 
				include $moduleDir . "matchconfirm.screen.php";

		}

		function handlePopupActions(&$feedback) {
		}

		function getPopupTitle() {
			return "Onbekend venster!";
		}

		function getPopupPage() {
		}

	}

?>
