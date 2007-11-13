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
	require_once($ivLibDir.'Form.class.php');
	require_once($ivLibDir.'FormFields.class.php');
	require_once($TBBclassDir.'Text.class.php');

	if (isSet($_POST['actionName']) && isSet($_POST['actionID']) && $TBBsession->isLoggedIn()) {
		if (($_POST['actionName'] == 'changePassword') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			$correct = 0;
			if ((strlen(trim($_POST['password'])) < 1) ||
				(strlen(trim($_POST['newpassword'])) < $TBBconfiguration->minimumPasswordLength) ||
				(strlen(trim($_POST['newpasswordrepeat'])) < $TBBconfiguration->minimumPasswordLength)) {
				$feedback->addMessage(sprintf('Paswoord niet ingevult of het nieuwe paswoord is minder dan %s karakters!', $TBBconfiguration->minimumPasswordLength));
				$correct = 1;
			}
			if (trim($_POST['newpassword']) != trim($_POST['newpasswordrepeat'])) {
				$feedback->addMessage('Het nieuwe paswoord is niet correct herhaald!');
				$correct = 1;
			}
			if ($correct == 0) {
				if ($TBBcurrentUser->changePassword($_POST['password'], $_POST['newpassword'])) {
					$feedback->addMessage('Het wachtwoord is gewijzigd');
				} else {
					$feedback->addMessage('Het wachtwoord is onjuist!');
				}
			}
		}
	}

	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation(sprintf('Instellingen voor %s', htmlConvert($TBBcurrentUser->getNickname())), 'usercontrol.php');
	$here->addLocation('Wachtwoord wijzigen', 'userpassword.php');
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
	$menu->itemIndex = 'password';
	$menu->showMenu('adminMenu');

		$changePasswordForm = new Form('changePassword', 'userpassword.php');
		$formFields = new StandardFormFields();
		$changePasswordForm->addFieldGroup($formFields);
		$formFields->activeForm = $changePasswordForm;

		$changePasswordForm->addHiddenField('actionID', $TBBsession->getActionID());
		$changePasswordForm->addHiddenField('actionName', 'changePassword');
		$formFields->startGroup('Wachtwoord veranderen');
		$formFields->addPasswordfield('password', 'Wachtwoord', 'huidig wachtwoord.<br /><a href="sendpassword.php">Wachtwoord vergeten?</a>', 30);
		$formFields->addPasswordfield('newpassword', 'Nieuw Wachtwoord', 'het nieuwe wachtwoord', 30);
		$formFields->addPasswordfield('newpasswordrepeat', 'Wachtwoord bevestiging', 'nogmaals om vergissingen te voorkomen', 30);
		$formFields->endGroup();
		$formFields->addSubmit('Wijzigen', false);
		$changePasswordForm->writeForm();

	writeJumpLocationField(-1, "usercontrol");

	include($TBBincludeDir.'htmlbottom.php');
?>
