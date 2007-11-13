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
	require_once($TBBclassDir.'UserManagement.class.php');
	require_once($ivLibDir.'Table.class.php');
	$userID = 0;
	if (isSet($_GET['id'])) $userID = $_GET['id'];
	if (isSet($_POST['id'])) $userID = $_POST['id'];

	require_once($TBBclassDir.'User.bean.php');
	$database = $TBBconfiguration->getDatabase();
	$userProfile = $TBBuserManagement->getUserByID($userID);
	if (!$userProfile->isGuest()) {
		$userName = $userProfile->getNickname();
	} else {
		$userName = "Gebruiker niet gevonden";
	}

	require_once($TBBclassDir.'Location.class.php');
	require_once($TBBclassDir.'Text.class.php');
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
