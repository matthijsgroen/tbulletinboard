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
	require_once($ivLibDir . 'Form.class.php');
	require_once($ivLibDir . 'FormFields.class.php');
	require_once($ivLibDir . 'PackFile.class.php');
	require_once($ivLibDir . 'FileUpload.class.php');
	require_once($ivLibDir . 'formcomponents/PlainText.class.php');

	require_once($TBBclassDir . 'Board.class.php');
	require_once($TBBclassDir . 'ModulePlugin.class.php');

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Nieuwe module installeren';
	include($TBBincludeDir . 'popuptop.php');

	$modAdmin = $TBBModuleManager;

	?>
		<h2>Nieuwe module installeren</h2>
	<?
	if (!$TBBsession->isLoggedIn()) {
		//$text = new Text();
		$feedback->addMessage("Sorry, gasten hebben geen toegang tot deze functionaliteit!");
		$feedback->showMessages();
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}

	if (!$TBBcurrentUser->isMaster()) {
		//$text = new Text();
		$feedback->addMessage("Sorry, deze functionaliteit is alleen voor Masters!");
		$feedback->showMessages();
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}

	$state = 'upload';
	if (isSet($_POST['state'])) $state = $_POST['state'];
	$uploadFolder = $TBBconfiguration->uploadDir."temp/";

	$fileUpload = new FileUpload("moduleFile", $uploadFolder, "Bestand", 400);
	$fileUpload->setExtensions(".tbbmod");
	$fileUpload->setMimeTypes("application/octet-stream");
	$fileUpload->overwriteFile("newmodule.tbbmod");
	$fileUpload->forceFilename("newmodule.tbbmod");

	if (isSet($_POST['actionName']) && ($_POST['actionID'] == $TBBsession->getActionID())) {
		if (($_POST['actionName'] == 'uploadmodule') && ($fileUpload->checkUpload($feedback))) {
			$correct = true;
			$filename = $fileUpload->getFileName();
			$completeFilename = $uploadFolder . $filename;
			$packFile = new PackFile();
			if (!$packFile->load($completeFilename)) {
				$feedback->addMessage("Ongeldig module bestand");
				unlink($completeFilename);
				$correct = false;
			}
			if (($correct) && (!$packFile->hasFile('deploy.xml'))) {
				$feedback->addMessage("Module bevat geen deploy.xml");
				unlink($completeFilename);
				$correct = false;
			}
			if ($correct) {
				$state = 'view';
			}
		}
		if ($_POST['actionName'] == 'acceptinstall') {
			if ($modAdmin->installModule($uploadFolder . 'newmodule.tbbmod', $_POST['groupName'], $feedback)) {
				$state = "installed";
			} else {
				$state = 'error';
			}
		}
	}

	if ($state == 'upload') {
		$form = new Form("uploadmodule", "uploadmodule.php");
		$formFields = new StandardFormFields();
		$formFields->activeForm = $form;
		$form->addFieldGroup($formFields);
		$form->addHiddenField('actionID', $TBBsession->getActionID());
		$form->addHiddenField('actionName', 'uploadmodule');
		$formFields->startGroup("Module uploaden");
		$fileUpload->addFormField($formFields);
		$formFields->addSubmit("Volgende &raquo;", false);
		$formFields->endGroup();
	} else
	if ($state == 'view') {
		$form = new Form("uploadmodule", "uploadmodule.php");
		$formFields = new StandardFormFields();
		$formFields->activeForm = $form;
		$form->addFieldGroup($formFields);
		$form->addHiddenField('actionID', $TBBsession->getActionID());
		$form->addHiddenField('actionName', 'acceptinstall');
		$formFields->startGroup("Module informatie");
		// deploy.xml ontleden
		$packFile->saveFile("deploy.xml", $uploadFolder);
		$packContents = $modAdmin->getPackContents($uploadFolder . "deploy.xml");

		$formFields->addText("Module", "",
			sprintf(
				"Naam: <strong>%s</strong><br />". "Versie: <strong>%s</strong><br />".
				"Plugins: <strong>%s</strong><br />",
				$packContents['info']['name'],  $packContents['info']['version'],
				count($packContents['plugins'])));
		$formFields->addText("Beschrijving", "", $packContents['info']['description']);
		$formFields->addText("Auteur", "",
			sprintf(
				"Naam: <strong>%s</strong><br />". "Url: <strong>%s</strong><br />".
				"Email: <strong>%s</strong><br />",
				$packContents['author']['name'],  $packContents['author']['url'],
				$packContents['author']['email']));
		$newModule = true;
		if ($modAdmin->hasModule($packContents['info']['group'])) {
			$formFields->addText("Waarschuwing", "",
				sprintf("Er is al een versie van deze module ge&iuml;nstalleerd. ".
					"De ge&iuml;nstalleerde versie bevat <strong>%s</strong> plugins.<br />".
					"Ga alleen verder als u zeker weet dat u de huidige versie wilt overschrijven.",
				$modAdmin->getNrPluginsOf($packContents['info']['group'])));
			$newModule = false;
		}
		for ($i = 0; $i < count($packContents['plugins']); $i++) {
			$pluginInfo = $packContents['plugins'][$i];
			$normalName = $modAdmin->getNormalPluginTypeName($pluginInfo['type']);
			$versionInfo = "geen";
			if (!$newModule) {
				if ($currPluginInfo = $modAdmin->getPluginInfo($packContents['info']['group'], $pluginInfo['type']))
					$versionInfo = $currPluginInfo->getValue("version");
			}
			$formFields->addText("Plugin", "",
				sprintf(
					"Naam: <strong>%s</strong><br />". "Type: <strong>%s</strong><br />".
					"Versie: <strong>%s</strong>%s<br />",
					$pluginInfo['name'], $normalName,
					$pluginInfo['version'],
					(($newModule == false) ? " (huidig: ".$versionInfo.")" : "")));
		}
		$form->addHiddenField('groupName', $packContents['info']['group']);

		$formFields->addSubmit("Volgende &raquo;", true);
		$formFields->endGroup();
	}else
	if ($state == 'installed') {
		$form = new Form("uploadmodule", "uploadmodule.php");
		$formFields = new StandardFormFields();
		$formFields->activeForm = $form;
		$form->addFieldGroup($formFields);
		$form->addHiddenField('actionID', $TBBsession->getActionID());
		$form->addHiddenField('actionName', 'installDatabase');
		$form->addHiddenField('groupName', $_POST['groupName']);
		$formFields->startGroup("Module installatie");
		$formFields->addText("Installatie", "", "De module is nu succesvol in het systeem geregistreerd. Klik op volgende om de database bij te werken.");
		$formFields->addSubmit("Volgende &raquo;", true);
		$formFields->endGroup();
	}	else {
		$form = new Form("empty", "uploadmodule.php");
	}
	$feedback->showMessages();
	$form->writeForm();

	include($TBBincludeDir.'popupbottom.php');
?>
