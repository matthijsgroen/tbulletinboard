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

	importClass("board.plugin.ModulePlugin");

	$pluginID = -1;
	if (isSet($_GET['id'])) $pluginID = $_GET['id'];
	if (isSet($_POST['id'])) $pluginID = $_POST['id'];
	if ($pluginID != -1) {
		$plugin = $TBBModuleManager->getPluginByID($pluginID);
	}

	if (!is_Object($plugin)) {
		$TBBconfiguration->redirectUri('usercontrol.php');
	}
	
	if ($plugin->getPluginType() != "userpanel") {
		$TBBconfiguration->redirectUri('usercontrol.php');
	}

	$plugin->handlePageActions($feedback);

	$pageTitle = $TBBconfiguration->getBoardName() . ' - '. $plugin->getPageTitle();
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');
	importClass("interface.Location");
	importClass("interface.Text");

	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation(sprintf('Instellingen voor %s', htmlConvert($TBBcurrentUser->getNickname())), 'usercontrol.php');
	$plugin->getLocation($here);
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
	$plugin->selectMenuItem($menu);
	$menu->showMenu('adminMenu');
?>
	<div class="adminContent">
		<? $plugin->getPage(); ?>
	</div>
<?php
	writeJumpLocationField(-1, "usercontrol");

	include($TBBincludeDir.'htmlbottom.php');
?>
