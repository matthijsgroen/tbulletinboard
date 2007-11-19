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
	importClass("interface.Form");
	importClass("interface.FormFields");
	importClass("board.Board");
	importClass("board.ActionHandler");
	importClass("board.BoardProfiles");
	importClass("board.MemberGroups");
	importClass("board.Text");
	importBean("board.Board");

	$parentID = 0;
	if (isSet($_GET['parent'])) $parentID = trim($_GET['parent']);
	if (isSet($_POST['parent'])) $parentID = trim($_POST['parent']);
	$parent = $TBBboardList->getBoard($parentID);

	$boardID = 0;
	if (isSet($_GET['id'])) $boardID = trim($_GET['id']);
	if (isSet($_POST['id'])) $boardID = trim($_POST['id']);
	if ($boardID != 0) {
		$board = $TBBboardList->getBoard($boardID);
		$parent = $board->getParent();
	}	else $board = false;

	if (isSet($_POST['actionName']) && isSet($_POST['actionID'])) {
		if (($_POST['actionName'] == 'addBoard') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			$action = new ActionHandler($feedback, $_POST);
			$action->check($TBBcurrentUser->isAdministrator(), 'Deze actie is alleen voor administrators!');
			/*
			$action->notEmpty('boardName', 'Geen naam gegeven!');
			$action->isNumeric('position', 'Ongeldige positie!');
			$action->isNumeric('boardSettings', 'Ongeldige positie!');
			$action->isNumeric('readGroup', 'Ongeldige positie!');
			$action->isNumeric('writeGroup', 'Ongeldige positie!');
			$action->isNumeric('topicGroup', 'Ongeldige positie!');
			*/
			if ($action->correct) {
				$parent->addSubBoard(
						trim($_POST['boardName']),
						trim($_POST['boardComment']),
						$_POST['position'],
						$_POST['boardSettings'],
						$_POST['readGroup'],
						$_POST['writeGroup'],
						$_POST['topicGroup']);

				$TBBsession->actionHandled();
				$TBBsession->setMessage("addBoard");
				$TBBconfiguration->redirectUri('index.php'.($parent->getID() == 0) ? '' : '?id='.$parent->getID());
			}
		}
		if (($_POST['actionName'] == 'editBoard') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			$action = new ActionHandler($feedback, $_POST);
			$action->check($TBBcurrentUser->isAdministrator(), 'Deze actie is alleen voor administrators!');
			/*
			$action->notEmpty('boardName', 'Geen naam gegeven!');
			$action->isNumeric('position', 'Ongeldige positie!');
			$action->isNumeric('boardSettings', 'Ongeldige positie!');
			$action->isNumeric('readGroup', 'Ongeldige positie!');
			$action->isNumeric('writeGroup', 'Ongeldige positie!');
			$action->isNumeric('topicGroup', 'Ongeldige positie!');
			*/
			if ($action->correct) {
				$boardBean = $board->p_getDBdata();
				$boardBean->setValue("name", $_POST['boardName']);
				$boardBean->setValue("comment", $_POST['boardComment']);
				$boardBean->setValue("settingsID", $_POST['boardSettings']);
				$boardBean->setValue("read", $_POST['readGroup']);
				$boardBean->setValue("write", $_POST['writeGroup']);
				$boardBean->setValue("topic", $_POST['topicGroup']);

				$position = $_POST['position'];
				if ($boardBean->getValue("order") <> $position) {
					// Board is going to be moved.
					$newPos = 0;
					// move all Other boards up
					$boardTable = new BoardTable($database);
					$dataFilter = new DataFilter();
					$dataFilter->addEquals("parentID", $boardBean->getValue("parentID"));
					$dataFilter->addGreaterThan("order", $boardBean->getValue("order"));
					$mutations = new DataMutation();
					$mutations->subtractFromColumn('order', 1);
					$boardTable->executeDataMutations($mutations, $dataFilter);

					if ($position == -2) {
						$subBoards = $parent->getSubBoards();
						$lastBoard = $subBoards[count($subBoards) - 1];
						$position = $lastBoard->getID();
					}
					if ($position > 0) {
						$boardPos = $TBBboardList->getBoard($position);
						$newPos = $boardPos->getPosition() +1;
					}

					// move all other boards down
					$dataFilter = new DataFilter();
					$dataFilter->addEquals("parentID", $boardBean->getValue("parentID"));
					$dataFilter->addGreaterThanOrEquals("order", $newPos);
					$mutations = new DataMutation();
					$mutations->addToColumn('order', 1);
					$boardTable->executeDataMutations($mutations, $dataFilter);

					$boardBean->setValue("order", $newPos);
				}
				$boardBean->store();
				$TBBboardList->updateStructureCache();
				$TBBsession->actionHandled();
				$TBBsession->setMessage("editBoard");
				$TBBconfiguration->redirectUri('index.php'.($parent->getID() == 0) ? '' : '?id='.$parent->getID());
			}
		}
	}
	if (is_object($board))
		$pageTitle = $TBBconfiguration->getBoardName().' - Board bewerken';
	else $pageTitle = $TBBconfiguration->getBoardName().' - Board toevoegen';

	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');


	$feedback->showMessages();
	if (is_object($board)) {
		$here = $board->getLocation();
		$here->addLocation('Board bewerken', sprintf('editboard.php?id=%s', $boardID));
	} else {
		$here = $parent->getLocation();
		$here->addLocation('Board toevoegen', sprintf('editboard.php?parent=%s', $parentID));
	}
	$here->showLocation();

	if (!$TBBcurrentUser->isAdministrator()) {
		$text = new Text();
		$text->addHTMLText("Sorry, dit venster is alleen voor administrators!");
		$text->showText();
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}

	$addForm = new Form('addBoard', 'editboard.php');
	$formFields = new StandardFormFields();
	$addForm->addFieldGroup($formFields);
	$formFields->activeForm = $addForm;


	$addForm->addHiddenField('actionID', $TBBsession->getActionID());
	if (is_object($board)) {
		$addForm->addHiddenField('id', $boardID);
		$formFields->startGroup('Board bewerken');
		$addForm->addHiddenField('actionName', 'editBoard');
	} else {
		$addForm->addHiddenField('parent', $parentID);
		$formFields->startGroup('Nieuw board aanmaken');
		$addForm->addHiddenField('actionName', 'addBoard');
	}

	// Gegevens van een board:
	$formFields->addTextField('boardName', 'Naam', 'Naam van het board', 80);
	$formFields->addTextField('boardComment', 'Commentaar', '', 250);

	$values = array();
	$values["-1"] = "Bovenaan";
	$subBoards = $parent->getSubBoards();
	$nrNeeded = 0;
	if (is_Object($board)) $nrNeeded = 1;
	if (count($subBoards) > $nrNeeded) {
		$values["-2"] = "Onderaan";
	}

	for ($i = 0; $i < count($subBoards); $i++) {
		$subBoard = $subBoards[$i];
		if (!is_Object($board) || ($subBoard->getID() <> $board->getID()))
			$values[$subBoard->getID()] = "Onder ".htmlConvert($subBoard->getName());
	}
	$formFields->addSelect('position', 'Positie', 'Plaats in de lijst', $values, 0);

	$profiles = $GLOBALS['TBBboardProfileList']->getProfiles();
	$values = array();
	for ($i = 0; $i < count($profiles); $i++) {
		$profile = $profiles[$i];
		$values[$profile->getID()] = $profile->getName();
	}
	$formFields->addSelect('boardSettings', 'Instellingen', 'Standaard instellingen<br><a href="boardprofiles.php">Profielen bekijken</a>', $values, 0);
	$formFields->endGroup();
	$formFields->startGroup('Toegang');

	$groups = $GLOBALS['TBBmemberGroupList']->getMemberGroups();
	$currentModuleID = -1;

	$options = array();
	$groupOptions = array();
	$groupName = "";
	for ($i = 0; $i < count($groups); $i++) {
		$group = $groups[$i];
		if ($currentModuleID <> $group->getModuleID()) {
			if (count($groupOptions) > 0 ) {
				$options[htmlConvert($groupName)] = $groupOptions;
			}
			$groupOptions = array();
			$groupModule = $group->getModule();
			$groupName = $groupModule->getPluginName();
			$currentModuleID = $group->getModuleID();
		}
		$groupOptions[$group->getID()] = htmlConvert($group->getName());
	}
	if (count($groupOptions) > 0 ) {
		$options[$groupName] = $groupOptions;
	}

	$formFields->addCategorySelect('readGroup', 'Lezen', 'Welke groep mag het board lezen?', $options, 0);
	$formFields->addCategorySelect('writeGroup', 'Schrijven', 'Welke groep mag reacties plaatsen?', $options, 0);
	$formFields->addCategorySelect('topicGroup', 'Onderwerpen', 'Welke groep mag onderwerpen starten?', $options, 0);

	$formFields->endGroup();
	if (is_Object($board)) {
		// Kidnap the bean :-)
		$boardBean = $board->p_getDBdata();
		$addForm->setValue("boardName", $boardBean->getValue("name"));
		$addForm->setValue("boardComment", $boardBean->getValue("comment"));
		$addForm->setValue("boardSettings", $boardBean->getValue("settingsID"));
		$addForm->setValue("readGroup", $boardBean->getValue("read"));
		$addForm->setValue("writeGroup", $boardBean->getValue("write"));
		$addForm->setValue("topicGroup", $boardBean->getValue("topic"));

		$position = -1;
		$subBoards = $parent->getSubBoards();
		for ($i = 0; $i < count($subBoards); $i++) {
			$subBoard = $subBoards[$i];
			if ($subBoard->getPosition() == ($board->getPosition() -1)) {
				$position = $subBoard->getID();
				break;
			}
		}

		$addForm->setValue("position", $position);
		$formFields->addSubmit('Bewerken', true);
	} else {
		$formFields->addSubmit('Aanmaken', true);
	}

	$addForm->writeForm();


	include($TBBincludeDir.'htmlbottom.php');
?>
