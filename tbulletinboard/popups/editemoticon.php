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
	require_once($ivLibDir.'FileUpload.class.php');
	require_once($ivLibDir.'Form.class.php');
	require_once($ivLibDir.'FormFields.class.php');
	require_once($ivLibDir.'Table.class.php');
	require_once($ivLibDir.'TextParser.class.php');
	require_once($ivLibDir.'formcomponents/PlainText.class.php');
	require_once($TBBclassDir.'Location.class.php');
	require_once($TBBclassDir.'Text.class.php');
	require_once($TBBclassDir.'ActionHandler.class.php');
	require_once($TBBclassDir.'TagListManager.class.php');
	require_once($TBBclassDir.'TBBEmoticonList.class.php');

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
