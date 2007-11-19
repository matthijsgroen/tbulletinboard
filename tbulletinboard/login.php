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

	importClass("interface.Form");
	importClass("interface.FormFields");
	importClass("board.Location");
	importClass("board.Text");

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
