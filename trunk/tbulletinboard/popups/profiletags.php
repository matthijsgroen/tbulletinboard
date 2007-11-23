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

	importClass("board.TagListManager");	
	importClass("interface.Text");	
	importClass("util.TextParser");	
	importClass("interface.Menu");	
	importClass("interface.Form");	
	importClass("interface.FormFields");	
	importClass("board.Board");	
	importClass("board.ActionHandler");	

	$pageTitle = 'Profiel tags';
	include($TBBincludeDir.'popuptop.php');

	if (isSet($_GET["id"])) $profileID = $_GET["id"];
	if (isSet($_POST["id"])) $profileID = $_POST["id"];

	if (!isSet($profileID)) {
		?>
			<h2>Geen id meegegeven</h2>
		<?php
		$text = new Text();
		$text->addHTMLText("Geen profiel id opgegeven!");
		$text->showText();
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}
	$boardProfile = $GLOBALS['TBBboardProfileList']->getBoardProfile($profileID);
	if (!is_Object($boardProfile)) {
		?>
			<h2>Profiel niet gevonden!</h2>
		<?php
		$text = new Text();
		$text->addHTMLText("Geen geldige profiel id opgegeven!");
		$text->showText();
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}

	if (!$TBBcurrentUser->isAdministrator()) {
		?>
			<h2>Geen toegang!</h2>
		<?php
		$text = new Text();
		$text->addHTMLText("Dit scherm is alleen voor administrators!");
		$text->showText();
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}

	if (isSet($_POST['actionName']) && isSet($_POST['actionID'])) {
		if (($_POST['actionName'] == 'editTags') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			$action = new ActionHandler($feedback, $_POST);
			$action->check($TBBcurrentUser->isAdministrator(), 'Deze actie is alleen voor Administrators!');
			$boardProfile->updateAllowedTags(explode(",", $_POST["tags"]));
			$TBBsession->actionHandled();
			$feedback->addMessage("Toegestane tags bijgewerkt");
		}
	}

?>
	<h2>TBBTags&trade; van profiel: "<?=$boardProfile->getName() ?>"</h2>
<?php

	$feedback->showMessages();

	$navMenu = new Menu();
	$navMenu->itemIndex = "tags";
	$navMenu->addItem('profile', '', 'Algemeen', 'editboardprofile.php?id='.$profileID, '', '', 0, false, '');
	$navMenu->addItem('tags', '', 'Tags', 'profiletags.php?id='.$profileID, '', '', 0, false, '');
	$navMenu->addItem('boards', '', 'Boards ('.$boardProfile->getNrUsed().')', 'profileboards.php?id='.$profileID, '', '', 0, false, '');
	$navMenu->addItem('topics', '', 'Onderwerpen', 'profiletopics.php?id='.$profileID, '', '', 0, false, '');
	$navMenu->showMenu("configMenu");


	$profileTags = new Form("profileTags", "profiletags.php");
	$profileTags->addHiddenField('id', $profileID);
	$profileTags->addHiddenField("actionID", $TBBsession->getActionID());
	$profileTags->addHiddenField("actionName", "editTags");


	$formFields = new StandardFormFields();
	$profileTags->addFieldGroup($formFields);
	$formFields->activeForm = $profileTags;

	$formFields->startGroup("Profiel tags instellen");
	$options = array();

	$completeList = $GLOBALS['TBBtagListManager']->getTBBtags();
	for ($i = 0; $i < $completeList->getTagCount(); $i++) {
		$tag = $completeList->getTag($i);
		$options[$tag->getID()] = $tag->getDescription();
	}


	$formFields->addMultiSelect("tags", "Toegestane Tags&trade;", "De tags in de bovenste box zijn toegestaan in dit profiel", $options, ",", false);
	$formFields->endGroup();
	$formFields->addSubmit("Instellen", false);

	$tagList = $boardProfile->getTBBtagList();
	$selectedTags = array();
	for ($i = 0; $i < $tagList->getTagCount(); $i++) {
		$tag = $tagList->getTag($i);
		$selectedTags[] = $tag->getID();
	}
	$idList = implode(",", $selectedTags);
	$profileTags->setValue("tags", $idList);

	$profileTags->writeForm();


	include($TBBincludeDir.'popupbottom.php');
?>
