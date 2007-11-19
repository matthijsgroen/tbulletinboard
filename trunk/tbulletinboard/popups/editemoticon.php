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

	importClass("util.FileUpload");	
	importClass("interface.Form");	
	importClass("interface.FormFields");	
	importClass("interface.Table");	
	importClass("util.TextParser");	
	importClass("interface.formcomponents.PlainText");	

	importClass("board.Location");	
	importClass("board.Text");	
	importClass("board.ActionHandler");	
	importClass("board.TagListManager");	
	importClass("board.TBBEmoticonList");	

	// Declare the form
	if (isSet($_GET["id"])) $iconId = $_GET["id"];
	if (isSet($_POST["id"])) $iconId = $_POST["id"];

	if (isSet($iconId)) {
		$icon = $GLOBALS['TBBemoticonList']->getEmoticon($iconId);
		if ($icon == false) unSet($icon);
	}

	$iconUpload = new FileUpload("emoticon", $TBBconfiguration->uploadDir . 'emoticons/', "Emoticon", 10);
	$iconUpload->setExtensions(".gif", ".jpg", ".png");
	$iconUpload->setMimeTypes("image/gif", "image/pjpeg", "image/jpeg", "image/png");


	$form = new Form("emoticonUpload", "editemoticon.php");
	$formFields = new StandardFormFields();
	$form->addFieldGroup($formFields);
	$formFields->activeForm = $form;

	$form->addHiddenField("actionID", $TBBsession->getActionID());

	if (isSet($icon)) {
		$form->addHiddenField("action", "editEmoticon");
		$formFields->startGroup("Emoticon bewerken");
		$form->addHiddenField("id", $iconId);
	} else {
		$form->addHiddenField("action", "addEmoticon");
		$formFields->startGroup("Emoticon toevoegen");
	}

	$formFields->addTextfield('name', 'Naam', 'Korte omschrijving', 20);
	$iconUpload->addFormField($formFields);
	if (isSet($icon)) {
		$form->addComponent(new FormPlainText("Huidig", "De huidige emoticon", '<img src="'.$docRoot.$icon['imgUrl'].'" alt="emoticon" />'));
	}
	$formFields->addMultifield('code', 'Codes', 'De codes waar de emoticon op reageert', " ", true);
	$formFields->endGroup();

	if (isSet($icon)) {
		$formFields->addSubmit("Bewerken", false);
		$form->setValue("name", $icon["name"]);
		$form->setValue("code", implode(" ", $icon["textCodes"]));
	} else {
		$formFields->addSubmit("Toevoegen", false);
	}

	$closeAndRefresh = false;
	$pageTitle = $TBBconfiguration->getBoardName() . ' - Instellingen';

	$action = new ActionHandler($feedback);	$action->definePostAction("addEmoticon", "code", "name");
	$action->definePostAction("editEmoticon", "code", "name");

	if ($action->inAction("addEmoticon")) {
		$action->check($TBBcurrentUser->isAdministrator(), 'Deze actie is alleen voor administrators!');
		//$action->notEmpty('code', 'Geen emoticon code opgegeven!');
		//$action->notEmpty('name', 'Geen emoticon naam opgegeven!');
		if ($action->correct)
			$action->check($iconUpload->checkUpload($feedback), '');
		if ($action->correct) {
			$GLOBALS['TBBemoticonList']->addEmoticonToDB(trim($_POST['name']), $iconUpload->getFileName(), trim($_POST['code']));
		}
		//$action->finish('Emoticon toegevoegd');
		$action->actionHandled();
		$TBBsession->setMessage('iconAdd');
		$closeAndRefresh = true;
	}
	if ($action->inAction("editEmoticon")) {
		$action->check($TBBcurrentUser->isAdministrator(), 'Deze actie is alleen voor administrators!');
		//$action->notEmpty('code', 'Geen emoticon code opgegeven!');
		//$action->notEmpty('name', 'Geen emoticon naam opgegeven!');
		if (($action->correct) && ($iconUpload->fileChoosen())) {
			$iconUpload->overwriteFile($icon['filename']);
			$action->check($iconUpload->checkUpload($feedback), '');
			if ($action->correct)
				$GLOBALS['TBBemoticonList']->updateEmoticonInDB($icon['ID'], trim($_POST['name']), $iconUpload->getFileName(), trim($_POST['code']));
		}

		if (($action->correct) && (!$iconUpload->fileChoosen())) {
			$GLOBALS['TBBemoticonList']->updateEmoticonInDB($icon['ID'], trim($_POST['name']), $icon['filename'], trim($_POST['code']));
			$action->actionHandled();
			$TBBsession->setMessage('iconEdit');
			$closeAndRefresh = true;
		}
	}

	include($TBBincludeDir.'popuptop.php');

	if ($closeAndRefresh) {
?>
	<script type="text/javascript">
		window.opener.document.location.href='<?=$docRoot; ?>admintext.php';
		window.close();
	</script>
<?php
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}

?>
	<h2>Emoticon <?=(isSet($icon)) ? "bewerken" : "toevoegen" ?></h2>
<?php

	$feedback->showMessages();

	if (!$TBBsession->isLoggedIn()) {
		$text = new Text();
		$text->addHTMLText("Sorry, gasten hebben geen instellingen venster!");
		$text->showText();
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}

	if (!$TBBcurrentUser->isAdministrator()) {
		$text = new Text();
		$text->addHTMLText("Sorry, dit venster is alleen voor administrators!");
		$text->showText();
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}

	$form->writeForm();

	include($TBBincludeDir.'popupbottom.php');
?>
