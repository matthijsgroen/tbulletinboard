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

	importClass("interface.Form");
	importClass("interface.Location");
	importClass("board.user.UserManagement");
	importClass("interface.Text");
	importClass("board.ActionHandler");

	if (isSet($_GET['code'])) {
		if ($TBBuserManagement->resetPassword($_GET['code'])) {
			$feedback->addMessage('Wachtwoord veranderd. Het nieuwe wachtwoord is gemaild.');
		} else {
			$feedback->addMessage('Code onjuist.');
		}
	} else {
		$TBBconfiguration->redirectUri('index.php');
	}

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Wachtwoord terugzetten';
	include($TBBincludeDir.'htmltop.php');
	$feedback->showMessages();


	include($TBBincludeDir.'htmlbottom.php');

?>
