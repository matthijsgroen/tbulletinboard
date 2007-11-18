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
	require_once($TBBclassDir . 'Board.class.php');
	require_once($libraryClassDir . 'Form.class.php');
	require_once($libraryClassDir . 'FormFields.class.php');
	require_once($libraryClassDir . 'formcomponents/ButtonBar.class.php');
	require_once($libraryClassDir . 'formcomponents/Button.class.php');

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Onderwerpen verplaatsen';
	include($TBBincludeDir . 'popuptop.php');

	$boardID = $_GET['boardID'];
	?>
		<h2><?=$_GET['nrTopics']; ?> onderwerpen verplaatsen</h2>
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

	$optionList = array();
	function walkBoards(&$board, $level, &$options) {
		global $TBBcurrentUser;
		global $textParser;
		$subBoards = $board->getSubBoards();
		$levelStr = '';
		for ($i = 0; $i < $level; $i++) $levelStr .= '-';
		if ($level > 0) $levelStr .= ' ';
		for ($i = 0; $i < count($subBoards); $i++) {
			$subBoard = $subBoards[$i];
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
	$formFields->activeForm = $form;
	$form->addFieldGroup($formFields);

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
	if ($TBBconfiguration->getReferenceID() != false)
		$formFields->addCheckboxes("Opties", "", $checkboxes);
	else
		$form->addHiddenField("leaveTrail", "no");

	$buttonBar = new FormButtonBar("", "", "", array());
	$okButton = new FormButton("Ok", "", "okButton", "return doClick(this.form)");

	$buttonBar->addComponent($okButton);
	$form->addComponent($buttonBar);

	$formFields->endGroup();
?>
	<script type="text/javascript"><!--
		function doClick(moveForm) {
			var orDoc = window.opener.document;
			var form = orDoc.getElementById('adminTopicForm');
			if (moveForm['newBoard'].value == <?=$boardID; ?>) {
				alert('Dat is hetzelfde board waar de onderwerpen nu in zitten!');
				return false;
			}
			form.newLocation.value = moveForm['newBoard'].value;
			form.leaveTrails.value = (moveForm['leaveTrail'].checked) ? "yes" : "no";
			form.submit();
			window.close();
			return false;
		}
	// -->
	</script>
<?

	$feedback->showMessages();
	$form->writeForm();

	include($TBBincludeDir.'popupbottom.php');
?>
