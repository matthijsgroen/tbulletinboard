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
	require_once($TBBclassDir.'tbblib.php');

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Instellingen';
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');

	importClass("interface.Location");
	importClass("interface.Text");
	importClass("interface.Form");
	importClass("interface.FormFields");
	importClass("util.FileUpload");
	importClass("board.AvatarList");
	importClass("board.ActionHandler");
	importClass("board.BoardFormFields");
	importClass("board.BoardProfiles");

	if (isSet($_POST['actionName']) && isSet($_POST['actionID'])) {
		if (($_POST['actionName'] == 'changeDisplay') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			$TBBcurrentUser->setDaysPrune($_POST['daysPrune']);
			$TBBcurrentUser->setTopicsPerPage($_POST['topicPage']);
			$TBBcurrentUser->setReactionsPerPage($_POST['reactionPage']);
			$TBBcurrentUser->setShowSignatures(isSet($_POST['signature']));
			$TBBcurrentUser->setShowAvatars(isSet($_POST['avatar']));
			$TBBcurrentUser->setShowEmoticons(isSet($_POST['emoticon']));
			$TBBsession->actionHandled();
			$feedback->addMessage("Weergave instellingen aangepast");
		}
	}

	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation(sprintf('Instellingen voor %s', htmlConvert($TBBcurrentUser->getNickname())), 'usercontrol.php');
	$here->addLocation('Weergave', 'userdisplay.php');
	$here->showLocation();

	if (!$TBBsession->isLoggedIn()) {
		$text = new Text();
		$text->addHTMLText("Sorry, gasten hebben geen instellingen venster!");
		$text->showText();
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}

	include($TBBincludeDir.'configmenu.php');
	$adminMenu->itemIndex = 'user';
	$adminMenu->showMenu('configMenu');

	include($TBBincludeDir.'user_control_menu.php');
	$menu->itemIndex = 'display';
	$menu->showMenu('adminMenu');

?>
	<div class="adminContent">
<?php

	$form = new Form("displayForm", "userdisplay.php");
	$formFields = new StandardFormFields();
	$form->addFieldGroup($formFields);
	$formFields->activeForm = $form;

	$boardFormFields = new BoardFormFields();
	$form->addFieldGroup($boardFormFields);
	$boardFormFields->activeForm = $form;

	$form->addHiddenField('actionID', $TBBsession->getActionID());
	$form->addHiddenField('actionName', 'changeDisplay');
	$formFields->startGroup('Weergave');

	$pruneOptions = array(
		"-1" => "Systeem standaard (".$TBBconfiguration->getDaysPrune().")",
		"7" => "7",
		"14" => "14",
		"30" => "30",
		"60" => "60",
		"120" => "120",
		"360" => "360"
	);
	$formFields->addSelect("daysPrune", "Dagen tonen", "Toon alleen de berichten van de afgelopen x dagen", $pruneOptions, -1);

	$topicOptions = array(
		"-1" => "Systeem standaard (".$TBBconfiguration->getTopicsPerPage().")",
		"10" => "10",
		"15" => "15",
		"30" => "30",
		"50" => "50"
	);
	$formFields->addSelect("topicPage", "Onderwerpen per pagina", "Het aantal onderwerpen dat op een pagina getoont wordt", $topicOptions, -1);

	$reactionOptions = array(
		"-1" => "Systeem standaard (".$TBBconfiguration->getReactionsPerPage().")",
		"10" => "10",
		"15" => "15",
		"30" => "30",
		"50" => "50"
	);
	$formFields->addSelect("reactionPage", "Reacties per pagina", "Het aantal reacties dat op een pagina getoont wordt", $reactionOptions, -1);

	$checkOptions = array(
		array("value" => "yes", "caption" => "Toon signatures", "description" => "", "name" => "signature", "checked" => true),
		array("value" => "yes", "caption" => "Toon avatars", "description" => "", "name" => "avatar", "checked" => true),
		array("value" => "yes", "caption" => "Toon emoticons", "description" => "", "name" => "emoticon", "checked" => true)
	);
	$formFields->addCheckboxes("Grafisch", "", $checkOptions);

	$formFields->endGroup();
	$formFields->addSubmit('Wijzigen', false);

	if ($TBBcurrentUser->isSystemDaysPrune()) $form->setValue("daysPrune", -1);
	else $form->setValue("daysPrune", $TBBcurrentUser->getDaysPrune());
	if ($TBBcurrentUser->isSystemTopicsPerPage()) $form->setValue("topicPage", -1);
	else $form->setValue("topicPage", $TBBcurrentUser->getTopicsPerPage());
	if ($TBBcurrentUser->isSystemReactionsPerPage()) $form->setValue("reactionPage", -1);
	else $form->setValue("reactionPage", $TBBcurrentUser->getReactionsPerPage());
	$form->setValue("signature", $TBBcurrentUser->showSignatures() ? "yes" : false);
	$form->setValue("avatar", $TBBcurrentUser->showAvatars() ? "yes" : false);
	$form->setValue("emoticon", $TBBcurrentUser->showEmoticons() ? "yes" : false);

	$form->writeForm();


?>
	</div>
<?php
	writeJumpLocationField(-1, "usercontrol");

	include($TBBincludeDir.'htmlbottom.php');
?>
