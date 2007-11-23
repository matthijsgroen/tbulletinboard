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

	importClass("board.user.UserManagement");
	importClass("interface.Table");
	importBean("board.user.User");

	$userID = 0;
	if (isSet($_GET['id'])) $userID = $_GET['id'];
	if (isSet($_POST['id'])) $userID = $_POST['id'];

	$database = $TBBconfiguration->getDatabase();
	$userProfile = $TBBuserManagement->getUserByID($userID);
	if (!$userProfile->isGuest()) {
		$userName = $userProfile->getNickname();
	} else {
		$userName = "Gebruiker niet gevonden";
	}

	importClass("interface.Location");
	importClass("interface.Text");
	$pageTitle = $TBBconfiguration->getBoardName() . ' - ' . $userName;
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');

	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation("Gebruikers", 'users.php');
	$here->addLocation($userName, 'user.php?id='.$userID);
	$here->showLocation();
	
	if (!$userProfile->isGuest()) {
		$table = new Table();
		$table->setHeader("Item", "Waarde");
		$table->addGroup("Forum");
		$table->addRow("Nick", $userProfile->getNickname());
		if ($userProfile->getAvatarID()) {
			$table->addRow("Avatar", '<img src="avatar.php?id='.$userProfile->getAvatarID().'" alt="avatar" />');
		}

		$table->addRow("Onderwerpen gestart", $userProfile->getTopicCount());
		$table->addRow("Berichten geplaatst", $userProfile->getPostCount());
		$table->addRow("Lid sinds", $userProfile->getMemberSinceString());
		$table->addRow("Laatst gezien", $userProfile->getLastSeenString());
		if ($userProfile->isMaster()) {
			$table->addRow("Functie", "Master");
		} elseif ($userProfile->isAdministrator()) {
			$table->addRow("Functie", "Administrator");
		}
		
		global $TBBModuleManager;
		$info = $TBBModuleManager->getPluginInfoType("usertype", true);

		$result = "";
		for ($i = 0; $i < count($info); $i++) {
			$module = $TBBModuleManager->getPlugin($info[$i]->getValue("group"), "usertype");
			$module->getUserPageData($userProfile, $table);
		}


		$table->showTable();
		$here->showLocation();
	}

	include($TBBincludeDir.'htmlbottom.php');
?>
