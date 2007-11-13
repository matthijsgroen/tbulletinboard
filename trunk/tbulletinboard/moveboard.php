<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
	 */

	require_once("folder.config.php");
	// Load the configuration
	require_once($TBBconfigDir.'configuration.php');
	require_once($ivLibDir.'Table.class.php');
	require_once($ivLibDir.'Form.class.php');
	require_once($ivLibDir.'FormFields.class.php');
	require_once($ivLibDir.'formcomponents/RecordSelect.class.php');
	require_once($ivLibDir.'formcomponents/PlainText.class.php');

	require_once($TBBclassDir.'Board.bean.php');
	require_once($TBBclassDir.'ActionHandler.class.php');
	require_once($TBBclassDir.'BoardProfiles.class.php');
	require_once($TBBclassDir.'MemberGroups.class.php');
	require_once($TBBclassDir.'Text.class.php');
	require_once($TBBclassDir.'Board.class.php');

	$boardID = 0;
	if (isSet($_GET['id'])) $boardID = trim($_GET['id']);
	if (isSet($_POST['id'])) $boardID = trim($_POST['id']);

	if ($boardID == 0) {
		$TBBsession->setMessage("boardNotMove");
		$TBBconfiguration->redirectUri('index.php');
	}
	$board = $TBBboardList->getBoard($boardID);
	if (!is_object($board)) {
		$TBBsession->setMessage("boardNotFound");
		$TBBconfiguration->redirectUri('index.php');
	}
	if (isSet($_POST['actionName']) && isSet($_POST['actionID'])) {
		if (($_POST['actionName'] == 'moveBoard') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			$action = new ActionHandler($feedback, $_POST);
			$action->check($TBBcurrentUser->isAdministrator(), 'Deze actie is alleen voor administrators!');

			$action->isNumeric('newParentID', 'geen geldige ParentID');
			$action->notEmpty('moveWhat', 'Niet opgegeven wat verplaatst moet worden!');
			if ($action->correct) {
				if ($_POST['moveWhat'] == 'all') {
					// move entire board to other parent
					$board->setParent($_POST['newParentID']);
					$TBBsession->actionHandled();
					$TBBsession->setMessage("editBoard");
					$TBBconfiguration->redirectUri('index.php?id='.$board->getID());
				}
				if ($_POST['moveWhat'] == 'topics') {
					$newBoard = $TBBboardList->getBoard($_POST['newParentID']);
					$board->moveAllTopics($newBoard);
					$TBBsession->actionHandled();
					$TBBconfiguration->redirectUri('index.php?id='.$newBoard->getID());
				}
				if ($_POST['moveWhat'] == 'topicSubboard') {
					$newBoard = $TBBboardList->getBoard($_POST['newParentID']);
					$board->moveAllTopics($newBoard);
					$board->moveAllSubboards($newBoard);
					$TBBsession->actionHandled();
					$TBBconfiguration->redirectUri('index.php?id='.$newBoard->getID());
				}
				if ($_POST['moveWhat'] == 'subboards') {
					$newBoard = $TBBboardList->getBoard($_POST['newParentID']);
					$board->moveAllSubboards($newBoard);
					$TBBsession->actionHandled();
					$TBBconfiguration->redirectUri('index.php?id='.$newBoard->getID());
				}

			}
		}
	}
	$pageTitle = $TBBconfiguration->getBoardName().' - Dingen verplaatsen';
	$parent = $board->getParent();

	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');

?>
	<script type="text/javascript"><!--
		var selectedRow = -1;

		function selectParent(id) {
			var form;
			form = document.getElementById("moveBoard");
			form.newParentID.value = id;
		}
	//-->
	</script>
<?

	$feedback->showMessages();
	$here = $board->getLocation();
	$here->addLocation('Dingen verplaatsen', sprintf('moveboard.php?id=%s', $boardID));
	$here->showLocation();

	if (!$TBBcurrentUser->isAdministrator()) {
		$text = new Text();
		$text->addHTMLText("Sorry, dit venster is alleen voor administrators!");
		$text->showText();
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}

	$moveForm = new Form('moveBoard', 'moveboard.php');
	$formFields = new StandardFormFields();
	$moveForm->addFieldGroup($formFields);
	$formFields->activeForm = $moveForm;

	$moveForm->addHiddenField('actionID', $TBBsession->getActionID());
	$moveForm->addHiddenField('id', $boardID);
	$moveForm->addHiddenField('newParentID', $parent->getID());
	$formFields->startGroup('Dingen verplaatsen');
	$moveForm->addHiddenField('actionName', 'moveBoard');

	$radioOptions = array();
	$radioOptions[] = array(
		"value" => "all",
		"caption" => "Alles",
		"description" => "Board, onderwerpen en subboards"
	);
	$radioOptions[] = array(
		"value" => "topics",
		"caption" => "Onderwerpen",
		"description" => "alleen onderwerpen (boards samenvoegen)"
	);
	$subBoards = $board->getSubboards();
	if (count($subBoards) > 0) {
		$radioOptions[] = array(
			"value" => "subboards",
			"caption" => "Subboards",
			"description" => "Subboards van dit board verplaatsen"
		);
		$radioOptions[] = array(
			"value" => "topicSubboard",
			"caption" => "Alle inhoud",
			"description" => "onderwerpen en subboards"
		);
	}
	$formFields->addRadio("moveWhat", "Wat", "selecteer wat er verplaatst moet worden", $radioOptions, "all");

	$moveForm->addComponent(new FormPlainText("Nieuw board kiezen", "", "Kies hieronder het board waar alles in geplaatst gaat worden"));
	$table = new Table();
	$table->setClass("table bdMove");
	$table->setHeader("ID", "Board", "Type");
	$table->setHeaderClasses("bdID", "bdName", "dbType");
	$table->setRowClasses("bdID", "bdName", "dbType");
	$table->setClickColumn(0, "selectParent", true);
	$table->selectRow($parent->getID());

	$moveForm->addComponent(new FormRecordSelect($table));

	function createBoardList(&$boardList, $level, &$parentBoard, $boardID) {
		$levelSpan = sprintf('<span style="padding-right: %spx">&nbsp;</span>', ($level * 20));

		$subBoards = $parentBoard->getSubBoards();
		for ($i = 0; $i < count($subBoards); $i++) {
			$subBoard = $subBoards[$i];
			if ($subBoard->getID() != $boardID) {
				$profile = $subBoard->getBoardSettings();

				$boardList->addRow($subBoard->getID(), $levelSpan . $subBoard->getName(), $profile->getViewModus());
				createBoardList($boardList, $level+1, $subBoard, $boardID);
			}
		}
		return $boardList;
	}

	$overview = $TBBboardList->getBoard(0);
	$table->addRow($overview->getID(), $overview->getName(), "");
	createBoardList($table, 1, $overview, $board->getID());

	$formFields->addSubmit('Verplaatsen', true);
	$moveForm->writeForm();


	include($TBBincludeDir.'htmlbottom.php');
?>
