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

	$menu = new Menu();
	$menu->addGroup('', '');
	$menu->addItem('start', '', 'Start', 'usercontrol.php', '', '', 0, false, '');

	$menu->addGroup('options', 'Opties');
	if ($TBBconfiguration->getSignaturesAllowed()) {
		$menu->addItem('signature', 'options', 'Signature', 'usersignature.php', '', '', 0, false, '');
	}
	$menu->addItem('avatar', 'options', 'Avatar', 'useravatar.php', '', '', 0, false, '');
	$menu->addItem('display', 'options', 'Weergave', 'userdisplay.php', '', '', 0, false, '');
	$menu->addItem('password', 'options', 'Wachtwoord wijzigen', 'userpassword.php', '', '', 0, false, '');

	$adminPlugins = $TBBModuleManager->getPluginInfoType("userpanel");
	for ($i = 0; $i < count($adminPlugins); $i++) {
		$pluginInfo = $adminPlugins[$i];
		$panelplugin = $TBBModuleManager->getPluginByID($pluginInfo->getValue("ID"));
		if ($panelplugin->isActive()) $panelplugin->createMenu($menu);
	}

?>
