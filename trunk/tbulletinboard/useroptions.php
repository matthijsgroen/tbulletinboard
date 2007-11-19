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

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Instellingen';
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');

	importClass("interface.Form");
	importClass("interface.FormFields");
	importClass("util.FileUpload");
	importClass("board.Location");
	importClass("board.Text");
	importClass("board.AvatarList");
	importClass("board.ActionHandler");
	importClass("board.BoardFormFields");
	importClass("board.Board");

	$avatarList = new AvatarList();

	$iconUpload = new FileUpload("avatarFile", $TBBconfiguration->uploadDir.'systemavatars/', "Avatar", 6);
	$iconUpload->setExtensions(".gif", ".jpg", ".png");
	$iconUpload->setMimeTypes("image/gif", "image/pjpeg", "image/jpeg", "image/png");
	$iconUpload->setRandomName('sav_');
	$iconUpload->setMaximumResolution(60, 60);

	if (isSet($_POST['actionName']) && isSet($_POST['actionID'])) {
		if (($_POST['actionName'] == 'changeAvatar') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			$action = new ActionHandler($feedback, $_POST);
			$action->check(!$TBBcurrentUser->isGuest(), 'Alleen voor ingelogde gebruikers mogelijk!');
			if ($action->correct) {
				$action->check($TBBcurrentUser->changeAvatar($_POST['avatar']), 'Avatar kon niet worden veranderd!');
				$action->finish('Avatar veranderd!');
			}
		}
		if (($_POST['actionName'] == 'addAvatar') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			$action = new ActionHandler($feedback, $_POST);
			$action->check(!$TBBcurrentUser->isGuest(), 'Alleen voor ingelogde gebruikers mogelijk!');
			$action->checkUpload($iconUpload, '');
			if ($action->correct) {
				$id = $avatarList->addUserAvatar($iconUpload->getFileName());
				$action->check($TBBcurrentUser->changeAvatar($id), 'Avatar kon niet worden veranderd!');
				$action->finish('Avatar veranderd!');
			}
		}
	}

	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation(sprintf('Instellingen voor %s', htmlConvert($TBBcurrentUser->getNickname())), 'usercontrol.php');
	$here->addLocation('Gegevens', 'useroptions.php');
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
	$menu->itemIndex = 'avatar';
	$menu->showMenu('adminMenu');

	$avatarID = $TBBcurrentUser->getAvatarID();
	if (!$avatarID) {
		$text = new Text();
		$text->addHTMLText("Je hebt geen actieve avatar op moment");
		$text->showText();
	} else {
		$text = new Text();
		$text->addHTMLText("Huidige avatar: <img src=\"avatar.php?id=".$avatarID."\" alt=\"avatar\" />");
		$text->showText();
	}
	$avatarForm = new Form('changeAvatar', 'useroptions.php');
	$formFields = new StandardFormFields();
	$avatarForm->addFieldGroup($formFields);
	$formFields->activeForm = $avatarForm;

	$boardFormFields = new BoardFormFields();
	$avatarForm->addFieldGroup($boardFormFields);
	$boardFormFields->activeForm = $avatarForm;

	$avatarForm->addHiddenField('actionID', $TBBsession->getActionID());
	$avatarForm->addHiddenField('actionName', 'changeAvatar');
	$formFields->startGroup('Avatar');
	$boardFormFields->addSystemAvatars('avatar', 'Standaard avatar', 'Als geen avatar gekozen wordt word een eventuele persoonlijke avatar verwijderd!');
	$formFields->endGroup();
	$formFields->addSubmit('Wijzigen', false);
	$avatarForm->writeForm();

	$form = new Form("addAvatar", "useroptions.php");
	$form->addFieldGroup($formFields);
	$formFields->activeForm = $form;

	$form->addHiddenField("actionName", "addAvatar");
	$form->addHiddenField("actionID", $TBBsession->getActionID());
	$formFields->startGroup("Avatar uploaden");
	$iconUpload->addFormField($formFields);
	$formFields->addSubmit("Uploaden", false);
	$formFields->endGroup();
	$form->writeForm($avatarForm->getTabIndex());


	include($TBBincludeDir.'htmlbottom.php');
?>
