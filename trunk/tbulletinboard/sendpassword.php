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

	importClass("interface.Form");
	importClass("interface.FormFields");
	importClass("interface.Location");
	importClass("board.UserManagement");
	importClass("interface.Text");
	importClass("board.ActionHandler");

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
