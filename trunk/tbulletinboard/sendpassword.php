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

	require_once($ivLibDir.'Form.class.php');
	require_once($ivLibDir.'FormFields.class.php');
	require_once($TBBclassDir.'Location.class.php');
	require_once($TBBclassDir.'UserManagement.class.php');
	require_once($TBBclassDir.'Text.class.php');
	require_once($TBBclassDir.'ActionHandler.class.php');

	if (isSet($_POST['actionName']) && isSet($_POST['actionID'])) {
		if (($_POST['actionName'] == 'sendPassword') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			$action = new ActionHandler($feedback, $_POST);
			$action->notEmpty('username', 'Geen gebruikersnaam opgegeven!');
			$action->notEmpty('email', 'Geen email adres opgegeven!');
			$user = false;
			if ($action->correct) {
				$userManagement = new UserManagement();
				$user = $userManagement->getUserByUsername(trim($_POST['username']));
				$action->check($user != false, sprintf('Gebruiker <strong>%s</strong> niet gevonden!', htmlConvert(trim($_POST['username']))));
			}
			if (($action->correct) && ($user->getEmail() != $_POST['email'])) {
				if ($TBBconfiguration->validateMail($user->getEmail()))
					$feedback->addMessage(sprintf('Gebruiker <strong>%s</strong> heeft een ander email adres!', htmlConvert(trim($_POST['username']))));
				else
					$feedback->addMessage(sprintf('Gebruiker <strong>%s</strong> heeft een ongeldig email adres!', htmlConvert(trim($_POST['username']))));
				$action->correct = false;
			}
			if ($action->correct)
				$action->check($TBBconfiguration->validateMail($user->getEmail()), sprintf('Gebruiker <strong>%s</strong> heeft geen geldig email adres!', htmlConvert(trim($_POST['username']))));
			if ($action->correct) {
				$user->sendPasswordNotification();
				$action->finish("Wachtwoord verstuurd!");
			}
		}
	}

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Wachtwoord terugzetten';
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');

	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation('Wachtwoord terugzetten', 'sendpassword.php');
	$here->showLocation();

	$text = new Text();
	$text->addHTMLText("Let op, deze optie werkt alleen als je een geldig email adres hebt ingevult bij registratie!");
	$text->showText();

	$loginForm = new Form('sendPassword', 'sendpassword.php');
	$formFields = new StandardFormFields();
	$loginForm->addFieldGroup($formFields);
	$formFields->activeForm = $loginForm;
	$loginForm->addHiddenField('actionID', $TBBsession->getActionID());
	$loginForm->addHiddenField('actionName', 'sendPassword');
	$formFields->startGroup('Wachtwoord terugzetten');
	$formFields->addTextfield('username', 'Inlognaam', '', 15);
	$formFields->addTextfield('email', 'Email adres', '', 60);
	$formFields->endGroup();
	$formFields->addSubmit('Verstuur', true);
	$loginForm->writeForm();

	include($TBBincludeDir.'htmlbottom.php');

?>
