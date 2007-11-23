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

	importClass("board.plugin.AdminPlugin");	
	importClass("interface.Table");	

	class TravianUserPanelPlugin extends AdminPlugin {
		var $privateVars;

		function TravianUserPanelPlugin() {
			$this->AdminPlugin();
		}

		function handlePageActions(&$feedback) {
		}

		function createMenu(&$menu) {
			global $TBBcurrentUser;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			
			$selectQuery = sprintf("SELECT * FROM tbb_travian_user WHERE tbbID='%s'", $TBBcurrentUser->getUserID());
			$selectResult = $database->executeQuery($selectQuery);
			if ($isRow = $selectResult->getRow()) {
			} else return;

			
			$menu->addGroup("travian", "Travian");
			$menu->addItem("linkSitter", "travian", "Sitters opgeven", 
				"panelplugin.php?id=".$this->getPluginID()."&screen=sitters", '', '', 0, false, '');

			$menu->addItem("travianDetails", "travian", "Details", 
				"panelplugin.php?id=".$this->getPluginID()."&screen=details", '', '', 0, false, '');

		}

		function selectMenuItem(&$menu) {
			if ($_GET['screen'] == "sitters") $menu->itemIndex = "linkSitter";
			if ($_GET['screen'] == "details") $menu->itemIndex = "travianDetails";
		}

		function getLocation(&$location) {
			if ($_GET['screen'] == "sitters")
				$location->addLocation("Travian Sitters opgeven", "panelplugin.php?id=".$this->getPluginID()."&screen=sitters");
			if ($_GET['screen'] == "details")
				$location->addLocation("Travian Details opgeven", "panelplugin.php?id=".$this->getPluginID()."&screen=details");
		}

		function getPageTitle() {
			if ($_GET['screen'] == "sitters") return "Sitters opgeven";
			if ($_GET['screen'] == "details") return "Details opgeven";
		}

		function getPage() {
			global $TBBcurrentUser;
			global $TBBconfiguration;
			global $TBBsession;
			$database = $TBBconfiguration->getDatabase();
			$moduleDir = $this->getModuleDir();
			
			require_once($moduleDir . "TravianUser.bean.php");
			
			//$selectQuery = sprintf("SELECT * FROM tbb_travian_user WHERE tbbID='%s'", $TBBcurrentUser->getUserID());
			//$selectResult = $database->executeQuery($selectQuery);
			$travianUserTable = new TravianPlayerTable($database);
			$filter = new DataFilter();
			$filter->addEquals("tbbID", $TBBcurrentUser->getUserID());
			$travianUserTable->selectRows($filter, new ColumnSorting());
			
			if ($travianRow = $travianUserTable->getRow()) {
			} else return;
		
			$step = 1;
			if (isSet($_GET['actionName']) && isSet($_GET['actionID'])) {
				$feedback = new Messages();
				if (($_GET['actionName'] == 'removeSitter') && ($_GET['actionID'] == $TBBsession->getActionID())) {
					require_once($moduleDir . "TravianSitter.bean.php");
					$travianSitterTable = new TravianSitterTable($database);
					$travianSitterTable->deleteRowByKey($_GET['sitterID']);
					
					$feedback->addMessage("Sitter verwijderd!");
					$TBBsession->actionHandled();
				}
				
				$feedback->showMessages();
			}
			if (isSet($_POST['actionName']) && isSet($_POST['actionID'])) {
				global $TBBclassDir;
				$feedback = new Messages();
				if (($_POST['actionName'] == 'addSitter') && ($_POST['actionID'] == $TBBsession->getActionID())) {
					$correct = true;
					$travianNickname = $_POST['traviannick'];
					require_once($moduleDir . "TravianPlace.bean.php");
					$travianPlaceTable = new TravianPlaceTable($database);

					$locationFilter = new DataFilter();
					$locationFilter->addEquals("playerName", $travianNickname);
					$travianPlaceTable->selectRows($locationFilter, new ColumnSorting());

					if ($playerRow = $travianPlaceTable->getRow()) {
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
					if ($correct) {
						$step = 2;					
						$TBBsession->actionHandled();
					}
				}
				if (($_POST['actionName'] == 'addConfirm') && ($_POST['actionID'] == $TBBsession->getActionID())) {
					$database = $TBBconfiguration->getDatabase();
				
					$travianUserID = $_POST['travianuserID'];
					$travianName = $_POST['travianName'];

					require_once($moduleDir . "TravianSitter.bean.php");
					$travianSitterTable = new TravianSitterTable($database);
					$connection = $travianSitterTable->addRow();
					$connection->setValue("userTravianID", $travianRow->getValue('travianID'));
					$connection->setValue("userID", $TBBcurrentUser->getUserID());
					$connection->setValue("travianID", $travianUserID);
					$connection->setValue("travianName", $travianName);
					$connection->store();
					$feedback->addMessage("Opgeslagen dat $travianName jouw sitter is");
				
					$TBBsession->actionHandled();
				}
				
				$feedback->showMessages();
			}

			if (($_GET['screen'] == "sitters") && ($step == 1)) 
				include $moduleDir . "showsitters.screen.php";
			if (($_GET['screen'] == "sitters") && ($step == 2)) 
				include $moduleDir . "sitterconfirm.screen.php";
			if ($_GET['screen'] == "details") 
				include $moduleDir . "accountdetails.screen.php";

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
