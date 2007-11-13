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

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Instellingen';
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');
	require_once($TBBclassDir.'Location.class.php');
	require_once($ivLibDir.'Messages.class.php');
	require_once($ivLibDir.'Table.class.php');
	require_once($TBBclassDir.'Text.class.php');
	require_once($TBBclassDir.'ActionHandler.class.php');

	if (isSet($_GET['actionName']) && isSet($_GET['actionID']) && ($TBBcurrentUser->isMaster())) {
		if (($_GET['actionName'] == 'deleteAdmin') && ($_GET['actionID'] == $TBBsession->getActionID())) {
			$action = new ActionHandler($feedback, $_GET);
			$action->check($TBBcurrentUser->isMaster(), 'Deze actie is alleen voor Masters!');
			$action->isNumeric('userID', 'Geen geldige userID!');
			if ($action->correct)
				$user = $TBBuserManagement->getUserByID($_GET['userID']);
			if ($action->correct)
				$action->check($user != false, 'Gebruiker niet gevonden!');
			if ($action->correct)
				$action->check($user->isAdministrator(), sprintf('<strong>%s</strong> is geen administrator!', htmlConvert($user->getNickname())));
			if ($action->correct) {
				$user->removeAdminRights();
				$action->finish(sprintf('<strong>%s</strong> is geen administrator meer', htmlConvert($user->getNickname())));
			}
		}
	}
	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation('Systeem instellingen', 'adminboard.php');
	$here->addLocation('Administrators', 'administrators.php');
	$here->showLocation();

	if (!$TBBsession->isLoggedIn()) {
		$text = new Text();
		$text->addHTMLText("Sorry, gasten hebben geen instellingen venster!");
		$text->showText();
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}

	if (!$TBBcurrentUser->isMaster()) {
		$text = new Text();
		$text->addHTMLText("Sorry, dit venster is alleen voor masters!");
		$text->showText();
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}

	include($TBBincludeDir.'configmenu.php');
	$adminMenu->itemIndex = 'system';
	$adminMenu->showMenu('configMenu');

	include($TBBincludeDir.'admin_menu.php');
	$menu->itemIndex = 'admins';
	$menu->showMenu('adminMenu');

?>
	<div class="adminContent">
<?php

	$admins = $TBBuserManagement->getAdministrators();

	$text = new Text();
	$text->addHTMLText(sprintf("%s administrator(s) aanwezig", count($admins)));
	$text->showText();

	$table = new Table();
	$table->setHeader("Nick", "Type", "<abbr title=\"Beveiligings\">Bev.</abbr> nivo", "Opties");
		for ($i = 0; $i < count($admins); $i++) {
			$admin = $admins[$i];
			$table->addRow(sprintf("<a href=\"user.php?id=%s\" class=\"nicklink\">%s</a>", $admin->getUserID(), htmlConvert($admin->getNickname())),
				$admin->getAdminType(),
				$admin->getSecurityLevel(),
				sprintf("<a href=\"administrators.php?actionName=deleteAdmin&amp;actionID=%s&amp;userID=%s\" class=\"actionlink\" title=\"%s als administrator verwijderen\">verwijder</a>",
					$TBBsession->getActionID(), $admin->getUserID(), htmlConvert($admin->getNickname())));
		}

	$table->showTable();
?>
	</div>
<?php
	writeJumpLocationField(-1, "admincontrol");

	include($TBBincludeDir.'htmlbottom.php');
?>