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

	$loggedIn = 0;
	if (isSet($_POST['actionName']) && isSet($_POST['actionID'])) {
		if (($_POST['actionName'] == 'userLogin') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			$correct = true;
			if ((strlen(trim($_POST['username'])) == 0) || (strlen(trim($_POST['password'])) == 0)) {
				$feedback->addMessage('Geen gebruikersnaam of wachtwoord opgegeven!');
				$correct = false;
			}
			if ($correct) {
				if (!$TBBcurrentUser->login($_POST['username'], $_POST['password'])) {
					$feedback->addMessage('Inloggen mislukt! ongeldige gebruikersnaam of wachtwoord!');
				} else {
					$loggedIn = 1;
					$TBBsession->actionHandled();
					$TBBsession->setMessage("loggedIn");
					$TBBconfiguration->redirectUri('index.php');
				}
			}
		}
	}

	if (isSet($_GET['actionName']) && ($_GET['actionName'] == 'logout')) {
		$TBBcurrentUser->logout();
		$feedback->addMessage('Je bent succesvol uitgelogd!');
	}

	require_once($ivLibDir.'Form.class.php');
	require_once($ivLibDir.'FormFields.class.php');
	require_once($TBBclassDir.'Location.class.php');
	require_once($TBBclassDir.'Text.class.php');
	$pageTitle = $TBBconfiguration->getBoardName() . ' - Inloggen';
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');


	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation('Inloggen', 'login.php');
	$here->showLocation();

	if ($loggedIn == 0) {
		$loginForm = new Form('userLogin', 'login.php');
		$formFields = new StandardFormFields();
		$loginForm->addFieldGroup($formFields);
		$formFields->activeForm = $loginForm;

		$loginForm->addHiddenField('actionID', $TBBsession->getActionID());
		$loginForm->addHiddenField('actionName', 'userLogin');
		$formFields->startGroup('Inloggen bij ' . $TBBconfiguration->getBoardName());
		$formFields->addTextfield('username', 'Inlognaam', '', 15);
		$formFields->addPasswordfield('password', 'Wachtwoord', '<a href="sendpassword.php">Wachtwoord vergeten?</a>', 30);
		$formFields->endGroup();
		$formFields->addSubmit('Inloggen', true);
		$loginForm->writeForm();
	} else {
		$text = new Text();
		$text->addHTMLText(sprintf("Welkom terug <strong>%s</strong>!", $TBBcurrentUser->getNickname()));
		$text->showText();
	}
	include($TBBincludeDir.'htmlbottom.php');
?>
