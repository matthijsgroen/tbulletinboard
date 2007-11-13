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
	require_once($TBBclassDir.'tbblib.php');

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Instellingen';
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');
	require_once($TBBclassDir.'Location.class.php');
	require_once($TBBclassDir.'Text.class.php');
	require_once($ivLibDir.'Form.class.php');
	require_once($ivLibDir.'FormFields.class.php');
	require_once($ivLibDir.'FileUpload.class.php');
	require_once($TBBclassDir.'AvatarList.class.php');
	require_once($TBBclassDir.'ActionHandler.class.php');
	require_once($TBBclassDir.'BoardFormFields.class.php');
	require_once($TBBclassDir.'BoardProfiles.class.php');

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
