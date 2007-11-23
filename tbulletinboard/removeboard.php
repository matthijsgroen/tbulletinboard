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
	importClass("interface.Text");

	$boardID = 0;
	if (isSet($_GET['id'])) $boardID = trim($_GET['id']);
	if (isSet($_POST['id'])) $boardID = trim($_POST['id']);

	if ($boardID == 0) {
		$TBBsession->setMessage("boardNotRemove");
		$TBBconfiguration->redirectUri('index.php');
	}
	$board = $TBBboardList->getBoard($boardID);
	if (!is_object($board)) {
		$TBBsession->setMessage("boardNotFound");
		$TBBconfiguration->redirectUri('index.php');
	}
	if (isSet($_POST['actionName']) && isSet($_POST['actionID'])) {
		if (($_POST['actionName'] == 'removeBoard') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			$action = new ActionHandler($feedback, $_POST);
			$action->check($TBBcurrentUser->isMaster(), 'Deze actie is alleen voor masters!');
			$action->notEmpty('removeWhat', 'Niet opgegeven wat verwijderd moet worden!');
			$action->check((isSet($_POST['confirm'])), 'Geen bevestiging gegeven!');

			if ($action->correct) {
				if ($_POST['removeWhat'] == "all") {
					$board->removeAll();
					?>
					<script type="text/javascript"><!--
						document.location.href = 'index.php?id=<?=$board->getParentID(); ?>';
					// -->
					</script>
					<?
				}
				if ($_POST['removeWhat'] == "subboards") {
					$board->removeSubBoards();
					?>
					<script type="text/javascript"><!--
						document.location.href = 'index.php?id=<?=$board->getID(); ?>';
					// -->
					</script>
					<?
				}
				if ($_POST['removeWhat'] == "topics") {
					$board->clear();
					?>
					<script type="text/javascript"><!--
						document.location.href = 'index.php?id=<?=$board->getID(); ?>';
					// -->
					</script>
					<?
				}
			}
		}
	}
	$pageTitle = $TBBconfiguration->getBoardName().' - Dingen verwijderen';
	$parent = $board->getParent();

	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');

	$feedback->showMessages();
	$here = $board->getLocation();
	$here->addLocation('Dingen verwijderen', sprintf('moveboard.php?id=%s', $boardID));
	$here->showLocation();

	if (!$TBBcurrentUser->isMaster()) {
		$text = new Text();
		$text->addHTMLText("Sorry, dit venster is alleen voor masters!");
		$text->showText();
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}

	$removeForm = new Form('removeBoard', 'removeboard.php');
	$formFields = new StandardFormFields();
	$removeForm->addFieldGroup($formFields);
	$formFields->activeForm = $removeForm;

	$removeForm->addHiddenField('actionID', $TBBsession->getActionID());
	$removeForm->addHiddenField('id', $boardID);
	$formFields->startGroup('Dingen verwijderen');
	$removeForm->addHiddenField('actionName', 'removeBoard');

	$radioOptions = array();

	if (($TBBconfiguration->getBinBoardID() != $board->getID()) &&
			($TBBconfiguration->getHelpBoardID() != $board->getID())) {
		$radioOptions[] = array(
			"value" => "all",
			"caption" => "Alles",
			"description" => "Board, onderwerpen en subboards"
		);
	}
	$radioOptions[] = array(
		"value" => "subboards",
		"caption" => "Subboards",
		"description" => "alleen subboards"
	);
	$radioOptions[] = array(
		"value" => "topics",
		"caption" => "Onderwerpen",
		"description" => "alleen onderwerpen"
	);
	$formFields->addRadio("removeWhat", "Wat", "selecteer wat er verwijderd moet worden", $radioOptions, "topics");
	$removeForm->addComponent(new FormPlainText("Waarschuwing", "", "<strong>Deze actie kan niet ongedaan worden!<br />Advies: Plaats board in een archief categorie en verander rechten in plaats van verwijderen</strong>"));

	$options = array(
		array(
			"value" => "yes",
			"name" => "confirm",
			"caption" => "Ik weet het zeker",
			"description" => "",
			"checked" => false
		)
	);
	$formFields->addCheckboxes("Zeker weten?", "Vink het vakje aan als je het zeker weet", $options);

	$formFields->addSubmit('Verwijderen', true);
	$removeForm->writeForm();

	include($TBBincludeDir.'htmlbottom.php');
?>
