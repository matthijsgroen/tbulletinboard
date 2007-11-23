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
	importClass("interface.Menu");
	importClass("board.user.UserManagement");
	importClass("board.plugin.ModulePlugin");

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
			$onlineGuests = $TBBuserManagement->getOnlineGuests($TBBcurrentUser);
			$guestsLine = "";
			if ($onlineGuests > 0) {
				$guestsLine = sprintf("%s %s %s", $onlineGuests, ($onlineGuests != 1) ? "gasten" : "gast", 
					(count($onlineUsers) > 0) ? "en " : "");
			}			
			if (count($onlineUsers) > 0) {
				printf("<span class=\"nrOnline\">%s%s %s online:</span> ",
					$guestsLine,
					count($onlineUsers),
					(count($onlineUsers) == 1) ? "lid" : "leden"
				);
				for ($i = 0; $i < count($onlineUsers); $i++) {
					$user = $onlineUsers[$i];
					printf("<a href=\"user.php?id=%s\">%s</a>", $user->getUserID(), htmlConvert($user->getNickname()));
					if ($i < count($onlineUsers)-1) print "<span class=\"userDivider\">, </span>";
				}
			} else { printf("<span class=\"nrOnline\">%s online</span>", $guestsLine); }
		?>
	</div>
