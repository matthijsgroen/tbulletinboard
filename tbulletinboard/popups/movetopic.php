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

	importClass("board.Board");	
	importClass("board.plugin.ModulePlugin");	
	importClass("interface.Form");	
	importClass("interface.FormFields");	

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Onderwerp verplaatsen';
	include($TBBincludeDir . 'popuptop.php');

	$topicID = 0;
	if (isSet($_GET['id'])) $topicID = $_GET['id'];
	if (isSet($_POST['ID'])) $topicID = $_POST['ID'];

	$topic = $GLOBALS['TBBtopicList']->getTopic($topicID);
	if (!is_object($topic)) {
		?>
		<h2>Onderwerp niet gevonden!</h2>
		<?
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}

	$boardID = $topic->board->getID();

	?>
		<h2>Onderwerp verplaatsen</h2>
	<?
	if (!$TBBsession->isLoggedIn()) {
		$text = new Text();
		$text->addHTMLText("Sorry, gasten hebben geen toegang tot deze functionaliteit!");
		$text->showText();
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}

	if (!$TBBcurrentUser->isAdministrator()) {
		$text = new Text();
		$text->addHTMLText("Sorry, deze functionaliteit is alleen voor Administrators!");
		$text->showText();
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}

	if (isSet($_POST['actionName']) && isSet($_POST['actionID'])) {
		if (($_POST['actionName'] == 'movetopic') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			$boardID = $_POST['newBoard'];
			$correct = true;
			if ($boardID == $topic->board->getID()) {
				$feedback->addMessage("Gekozen board bevat dit onderwerp nu!");
				$correct = false;
			}
			if ($correct) {
				if ($_POST['leaveTrail'] == "yes") {
					$topicModule = $TBBModuleManager->getPlugin("referenceTopic", "topic");
					$topicModule->createReferenceOfTopic($topic);
				}
				$topic->moveToBoard($_POST['newBoard']);
				$TBBsession->actionHandled();
				$TBBsession->setMessage("topicMoved");
				?>
				<script type="text/javascript">
					window.opener.location.href="<?=$docRoot; ?>topic.php?id=<?=$topic->getID(); ?>";
					window.close();
				</script>
				<?
			}
		}
	}

	$optionList = array();
	function walkBoards(&$board, $level, &$options) {
		global $TBBcurrentUser;
		global $textParser;
		$subBoards = $board->getSubBoards();
		$levelStr = '';
		for ($i = 0; $i < $level; $i++) $levelStr .= '-';
		if ($level > 0) $levelStr .= ' ';
		for ($i = 0; $i < count($subBoards); $i++) {
			$subBoard =& $subBoards[$i];
			$profile = $subBoard->getBoardSettings();
			if (($subBoard->canRead($TBBcurrentUser)) && ((($profile->getViewModusRaw() != 'hidden') && (($profile->getViewModusRaw() != 'openHidden'))) || ($TBBcurrentUser->isActiveAdmin()))) {
				$options[$subBoard->getID()] = $levelStr.htmlConvert($subBoard->getName());
				walkBoards($subBoard, $level+1, $options);
			}
		}
	}
	$jumpBoard = $TBBboardList->getBoard(0);
	walkBoards($jumpBoard, 0, $optionList);

	$form = new Form("movetopic", "movetopic.php");
	$formFields = new StandardFormFields();
	$formFields->activeForm =& $form;
	$form->addFieldGroup($formFields);

	$form->addHiddenField('actionID', $TBBsession->getActionID());
	$form->addHiddenField('actionName', 'movetopic');
	$form->addHiddenField('ID', $topicID);
	$formFields->startGroup("Onderwerp verplaatsen");
	$formFields->addSelect("newBoard", "Nieuw forum kiezen", "Kies het nieuwe forum waar dit onderwerp naar verplaatst wordt", $optionList, $boardID, true);

	$checkboxes = array(
		array(
			"name" => "leaveTrail",
			"value" => "yes",
			"caption" => "Verwijzing achterlaten",
			"description" => "Creert een link zodat gebruikers weten waar het onderwerp naartoe is verplaatst",
			"checked" => true,
		)
	);
		
	if ($TBBModuleManager->getPlugin("referenceTopic", "topic"))
		$formFields->addCheckboxes("Opties", "", $checkboxes);
	else
		$form->addHiddenField("leaveTrail", "no");

	$formFields->addSubmit("Ok", false);
	$formFields->endGroup();


	$feedback->showMessages();
	$form->writeForm();

	include($TBBincludeDir.'popupbottom.php');
?>
