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
	require_once($TBBclassDir."User.bean.php");

	$isRegistered = 0;
	if (isSet($_POST['actionName']) && isSet($_POST['actionID'])) {
		if (($_POST['actionName'] == 'register') && ($_POST['actionID'] == $TBBsession->getActionID())) {

			$correct = true;

			if ((strlen(trim($_POST['nickname'])) < 1) || (strlen(trim($_POST['username'])) < 1)) {
				$feedback->addMessage('Gebruikersnaam en Nick moeten minimaal uit 1 karakter bestaan!');
				$correct = false;
			}
			if ((strlen(trim($_POST['password'])) < $TBBconfiguration->minimumPasswordLength) || (strlen(trim($_POST['passwordrepeat'])) < $TBBconfiguration->minimumPasswordLength)) {
				$feedback->addMessage('Wachtwoord moet uit minimaal '.$TBBconfiguration->minimumPasswordLength.' karakters bestaan!');
				$correct = false;
			}
			if (!($_POST['password'] == $_POST['passwordrepeat'])) {
				$feedback->addMessage('Het wachtwoord is niet correct herhaald!');
				$correct = false;
			}

			if ($correct == true) {
				$userTable = new UserTable($database);
				$dataFilter = new DataFilter();
				$dataFilter->addEquals("username", trim($_POST['username']));
				$userTable->selectRows($dataFilter, new ColumnSorting());

				if ($userTable->getSelectedRowCount() > 0) {
					$feedback->addMessage('De username bestaat al!');
				} else {
					$dataFilter = new DataFilter();
					$dataFilter->addEquals("nickname", trim($_POST['nickname']));
					$userTable->selectRows($dataFilter, new ColumnSorting());

					if ($userTable->getSelectedRowCount() > 0) {
						$feedback->addMessage('De nickname bestaat al!');
					} else {
						// no used nickname or username, insert the new user
						$userID = $TBBcurrentUser->register($_POST['username'], $_POST['nickname'], $_POST['password'], $_POST['email']);
						$TBBcurrentUser->login($_POST['username'], $_POST['password']);
						$isRegistered = 1;
						$TBBsession->actionHandled();

						$feedback->addMessage('Welkom <b>'.htmlConvert($_POST['nickname']).'</b>!');
					}
				}
			}
		}
	}

	require_once($ivLibDir.'Form.class.php');
	require_once($ivLibDir.'FormFields.class.php');
	require_once($TBBclassDir.'Location.class.php');
	require_once($TBBclassDir.'Text.class.php');
	$pageTitle = $TBBconfiguration->getBoardName() . ' - Registreren';
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');

	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation('Registreren', 'register.php');
	$here->showLocation();

	if ($isRegistered == 0) {
		$regForm = new Form('registerUser', 'register.php');
		$formFields = new StandardFormFields();
		$regForm->addFieldGroup($formFields);
		$formFields->activeForm = $regForm;
		$regForm->addHiddenField('actionID', $TBBsession->getActionID());
		$regForm->addHiddenField('actionName', 'register');
		$formFields->startGroup('Registreren bij ' . $TBBconfiguration->getBoardName());
		$formFields->addTextfield('username', 'Inlognaam', 'Naam waarmee ingelogt wordt op het forum', 15);
		$formFields->addTextfield('nickname', 'Nick', 'Naam die bij je berichten wordt geplaatst', 30);
		$formFields->addPasswordfield('password', 'Wachtwoord', 'Wachtwoord om mee in te loggen', 30);
		$formFields->addPasswordfield('passwordrepeat', 'Wachtwoord bevestiging', 'nogmaals om vergissingen te voorkomen', 30);
		$formFields->addTextfield('email', 'Email', 'Email adres voor terugvragen wachtwoord', 60);
		$formFields->endGroup();
		$formFields->addSubmit('Stuur door', true);

		$regForm->writeForm();
	}
	if ($isRegistered == 1) {
		$text = new Text();
		$text->addHTMLText(sprintf("Welkom bij <strong>%s</strong>! Je account is aangemaakt, en je kan nu direct beginnen met berichten plaatsen!", $TBBconfiguration->getBoardName()));
		$text->addHTMLText("Klik <a href=\"index.php\" title=\"Categori&euml;n overzicht\">hier</a> om te beginnen!");
		$text->showText();
	}

	include($TBBincludeDir.'htmlbottom.php');
?>
