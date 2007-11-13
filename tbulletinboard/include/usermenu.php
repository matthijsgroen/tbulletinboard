<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
	 */

	require_once($ivLibDir . 'Menu.class.php');
	require_once($TBBclassDir.'UserManagement.class.php');
	require_once($TBBclassDir.'ModulePlugin.class.php');

	$topMenu = new Menu();

	if ($TBBsession->isLoggedIn()) {
		$topMenu->addItem('user', '', htmlConvert($TBBcurrentUser->getNickname()), "user.php?id=". $TBBcurrentUser->getUserID(), '', '', 0, true, '');
		$topMenu->addItem('login', '', "Log uit", "login.php?actionName=logout", '', '', 0, false, '');
		$topMenu->addItem('settings', '', "Instellingen", "usercontrol.php", '', '', 0, false, '');
	} else {
		$topMenu->addItem('login', '', "Log in", "login.php", '', '', 0, false, '');
		$topMenu->addItem('register', '', "Registreer", "register.php", '', '', 0, false, '');
	}
	$searchPlugins = $TBBModuleManager->getPluginInfoType("search", true);
	if (count($searchPlugins) > 0)
		$topMenu->addItem('search', '', "Zoeken", "search.php".(isSet($boardID) ? "?boardID=".$boardID : ""), '', '', 0, false, '');

	if ($TBBconfiguration->getHelpBoardID() !== false)
		$topMenu->addItem('help', '', "Help", "index.php?id=".$TBBconfiguration->getHelpBoardID(), '', '', 0, false, '');

	$topMenu->showMenu("usermenu");

?>
	<div id="onlineUsers">
		<?php
			$onlineUsers = $TBBuserManagement->getOnlineUsers();
			if (count($onlineUsers) > 0) {
				printf("<span class=\"nrOnline\">%s %s online:</span> ",
					count($onlineUsers),
					(count($onlineUsers) == 1) ? "lid" : "leden"
				);
				for ($i = 0; $i < count($onlineUsers); $i++) {
					$user = $onlineUsers[$i];
					printf("<a href=\"user.php?id=%s\">%s</a>", $user->getUserID(), htmlConvert($user->getNickname()));
					if ($i < count($onlineUsers)-1) print "<span class=\"userDivider\">, </span>";
				}
			} else { print("<span class=\"nrOnline\">Geen ingelogde leden online</span>"); }
		?>
	</div>