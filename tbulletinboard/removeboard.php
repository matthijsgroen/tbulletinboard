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
