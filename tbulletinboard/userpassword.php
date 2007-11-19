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
	
	importClass("board.Location");
	importClass("interface.Form");
	importClass("interface.FormFields");
	importClass("board.Text");

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
