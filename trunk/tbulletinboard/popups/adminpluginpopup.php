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
	require_once($TBBclassDir.'ModulePlugin.class.php');

	$moduleID = -1;
	if (isSet($_GET['id'])) $moduleID = $_GET['id'];
	if (isSet($_POST['id'])) $moduleID = $_POST['id'];
	if ($moduleID != -1) {
		$module = $TBBModuleManager->getPlugin($moduleID, "admin");
	}
	$module->handlePopupActions($feedback);

	$pageTitle = $TBBconfiguration->getBoardName() . ' - '. $module->getPopupTitle();
	include($TBBincludeDir.'popuptop.php');
	?>
		<h2><?=$module->getPopupTitle(); ?></h2>
	<?
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

	$feedback->showMessages();

	$module->getPopupPage();

	include($TBBincludeDir.'popupbottom.php');
?>