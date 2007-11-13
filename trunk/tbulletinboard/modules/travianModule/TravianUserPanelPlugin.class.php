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
			$menu->addGroup("travian", "Travian");
			$menu->addItem("linkSitter", "travian", "Sitters opgeven", 
				"panelplugin.php?id=".$this->getModulename()."&screen=match", '', '', 0, false, '');
		}

		function selectMenuItem(&$menu) {
			$menu->itemIndex = "linkSitter";
		}

		function getLocation(&$location) {
			$location->addLocation("Travian Sitters opgeven", "panelplugin.php?id=".$this->getModuleName()."&screen=match");
		}

		function getPageTitle() {
			return "Sitters opgeven";
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
					require_once($TBBclassDir . "User.bean.php");
					$userTable = new UserTable($database);

					$userFilter = new DataFilter();
					$userFilter->addEquals("nickname", $tbbUserName);
					$userTable->selectRows($userFilter, new ColumnSorting());

					if ($userRow = $userTable->getRow()) {
						//$feedback->addMessage("TBB member found " . $userRow->getValue("nickname"));
					} else {
						$feedback->addMessage("TBB member not found");
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
							$feedback->addMessage("Player not found");
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
					require_once($TBBclassDir . "User.bean.php");
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
					$feedback->addMessage("Travian player account attached to forum user");					
				
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
