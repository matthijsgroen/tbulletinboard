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
		if (($_POST['actionName'] == 'changeSignature') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			$TBBcurrentUser->setSignature($_POST['signature']);
		}
	}

	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation(sprintf('Instellingen voor %s', htmlConvert($TBBcurrentUser->getNickname())), 'usercontrol.php');
	$here->addLocation('Signature', 'usersignature.php');
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
	$menu->itemIndex = 'signature';
	$menu->showMenu('adminMenu');

	if (!$TBBconfiguration->getSignaturesAllowed()) {
		?>
			<div class="adminContent">
		<?php
		$text = new Text();
		$text->addHTMLText("Signatures zijn niet toegestaan!");
		$text->showText();
		?>
			</div>
		<?php
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}
?>
	<div class="adminContent">
<?php

	$text = new Text();
	$text->addHTMLText($TBBcurrentUser->getSignatureHTML());
	$text->showText();

	$form = new Form("signatureForm", "usersignature.php");
	$formFields = new StandardFormFields();
	$form->addFieldGroup($formFields);
	$formFields->activeForm = $form;

	$boardFormFields = new BoardFormFields();
	$form->addFieldGroup($boardFormFields);
	$boardFormFields->activeForm = $form;

	$form->addHiddenField('actionID', $TBBsession->getActionID());
	$form->addHiddenField('actionName', 'changeSignature');
	$formFields->startGroup('Signature');
	$boardFormFields->addPostTextField('signature', 'Signature', '', true, true, false);
	$formFields->endGroup();
	$formFields->addSubmit('Wijzigen', false);
	$form->setValue("signature", $TBBcurrentUser->getSignature());

	$form->writeForm();


?>
	</div>
<?php
	writeJumpLocationField(-1, "usercontrol");

	include($TBBincludeDir.'htmlbottom.php');
?>
