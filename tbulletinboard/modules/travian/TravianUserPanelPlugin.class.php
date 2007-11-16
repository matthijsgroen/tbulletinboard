<?php

	global $TBBclassDir;
	require_once($TBBclassDir . "AdminPlugin.class.php");
	global $ivLibDir;
	require_once($ivLibDir . "Table.class.php");

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
				"panelplugin.php?id=".$this->getModulename()."&screen=sitters", '', '', 0, false, '');
		}

		function selectMenuItem(&$menu) {
			$menu->itemIndex = "linkSitter";
		}

		function getLocation(&$location) {
			$location->addLocation("Travian Sitters opgeven", "panelplugin.php?id=".$this->getModuleName()."&screen=sitters");
		}

		function getPageTitle() {
			return "Sitters opgeven";
		}

		function getPage() {
			global $TBBcurrentUser;
			global $TBBconfiguration;
			global $TBBsession;
			$database = $TBBconfiguration->getDatabase();
			
			$selectQuery = sprintf("SELECT * FROM tbb_travian_user WHERE tbbID='%s'", $TBBcurrentUser->getUserID());
			$selectResult = $database->executeQuery($selectQuery);
			if ($isRow = $selectResult->getRow()) {
			} else return;
		
			$moduleDir = $this->getModuleDir();
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
					$connection->setValue("userTravianID", $isRow['travianID']);
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
