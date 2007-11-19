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
	importClass("board.ModulePlugin");	

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
