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
	require_once($TBBclassDir.'tbblib.php');
	require_once($TBBclassDir.'ModulePlugin.class.php');

	$moduleID = -1;
	if (isSet($_GET['id'])) $moduleID = $_GET['id'];
	if (isSet($_POST['id'])) $moduleID = $_POST['id'];
	if ($moduleID != -1) {
		$module = $TBBModuleManager->getPlugin($moduleID, "admin");
	}

	if (!is_Object($module)) {
		$TBBconfiguration->redirectUri('adminextra.php');
	}

	$module->handlePageActions($feedback);

	$pageTitle = $TBBconfiguration->getBoardName() . ' - '. $module->getPageTitle();
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');
	require_once($TBBclassDir.'Location.class.php');
	require_once($TBBclassDir.'Text.class.php');

	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation('Plugin instellingen', 'adminmodules.php');
	$module->getLocation($here);
	$here->showLocation();

	if (!$TBBsession->isLoggedIn()) {
		$text = new Text();
		$text->addHTMLText("Sorry, gasten hebben geen instellingen venster!");
		$text->showText();
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}

	if (!$TBBcurrentUser->isAdministrator()) {
		$text = new Text();
		$text->addHTMLText("Sorry, dit venster is alleen voor administrators!");
		$text->showText();
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}

	include($TBBincludeDir.'configmenu.php');
	$adminMenu->itemIndex = 'plugin';
	$adminMenu->showMenu('configMenu');

	include($TBBincludeDir.'admin_extra.php');
	$module->selectMenuItem($menu);
	$menu->showMenu('adminMenu');
?>
	<div class="adminContent">
		<? $module->getPage(); ?>
	</div>
<?php
	writeJumpLocationField(-1, "plugincontrol");

	include($TBBincludeDir.'htmlbottom.php');
?>
