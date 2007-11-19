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

	importClass("board.ModulePlugin");

	$moduleID = -1;
	if (isSet($_GET['id'])) $moduleID = $_GET['id'];
	if (isSet($_POST['id'])) $moduleID = $_POST['id'];
	if ($moduleID != -1) {
		$module = $TBBModuleManager->getPlugin($moduleID, "userpanel");
	}

	if (!is_Object($module)) {
		$TBBconfiguration->redirectUri('usercontrol.php');
	}

	$module->handlePageActions($feedback);

	$pageTitle = $TBBconfiguration->getBoardName() . ' - '. $module->getPageTitle();
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');
	importClass("board.Location");
	importClass("board.Text");

	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation(sprintf('Instellingen voor %s', htmlConvert($TBBcurrentUser->getNickname())), 'usercontrol.php');
	$module->getLocation($here);
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
	$module->selectMenuItem($menu);
	$menu->showMenu('adminMenu');
?>
	<div class="adminContent">
		<? $module->getPage(); ?>
	</div>
<?php
	writeJumpLocationField(-1, "usercontrol");

	include($TBBincludeDir.'htmlbottom.php');
?>
