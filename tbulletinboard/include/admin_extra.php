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
	importClass("board.plugin.ModulePlugin");

	$menu = new Menu();
	$menu->addGroup('modules', 'Modules');
	$menu->addItem('modulelist', 'modules', 'Module beheer', $TBBcurrentUser->isAdministrator() ? 'adminmodules.php' : '', '', '', 0, false, '');

	//$menu->addItem('stats', '', 'Algemeen', 'adminboard.php', '', '', 0, false, '');
	//$menu->addItem('extra', '', 'Extra', 'adminextra.php', '', '', 0, true, '');

	$adminPlugins = $TBBModuleManager->getPluginInfoType("admin");
	for ($i = 0; $i < count($adminPlugins); $i++) {
		$pluginInfo = $adminPlugins[$i];
		$adminplugin = $TBBModuleManager->getPluginByID($pluginInfo->getValue("ID"));
		if ($adminplugin->isActive()) $adminplugin->createMenu($menu);
	}

?>
