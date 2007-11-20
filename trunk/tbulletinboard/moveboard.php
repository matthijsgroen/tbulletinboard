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

	require_once("folder.config.php");
	// Load the configuration
	require_once($TBBconfigDir.'configuration.php');

	importClass("interface.Table");
	importClass("interface.Form");
	importClass("interface.FormFields");
	importClass("interface.formcomponents.RecordSelect");
	importClass("interface.formcomponents.PlainText");
	importBean("board.Board");
	importClass("board.Board");
	importClass("board.ActionHandler");
	importClass("board.BoardProfiles");
	importClass("board.MemberGroups");
	importClass("board.Text");

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
			$action->isEmpty('moveWhat', 'Niet opgegeven wat verplaatst moet worden!');
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
