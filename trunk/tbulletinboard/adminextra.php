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
	
	importClass("board.Location");
	importClass("board.Text");

	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation('Plugin instellingen', 'adminmodules.php');
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
	$menu->itemIndex = 'extra';
	$menu->showMenu('adminMenu');

?>
	<div class="adminContent">
<?php

?>
	</div>
<?php
	writeJumpLocationField(-1, "admincontrol");

	include($TBBincludeDir.'htmlbottom.php');
?>
