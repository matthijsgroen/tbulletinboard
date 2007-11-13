<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
	 */
	require_once($ivLibDir . 'Menu.class.php');
	require_once($TBBclassDir . 'ModulePlugin.class.php');

	$menu = new Menu();
	$menu->addGroup('modules', 'Modules');
	$menu->addItem('modulelist', 'modules', 'Module beheer', $TBBcurrentUser->isAdministrator() ? 'adminmodules.php' : '', '', '', 0, false, '');

	//$menu->addItem('stats', '', 'Algemeen', 'adminboard.php', '', '', 0, false, '');
	//$menu->addItem('extra', '', 'Extra', 'adminextra.php', '', '', 0, true, '');

	$adminPlugins = $TBBModuleManager->getPluginInfoType("admin");
	for ($i = 0; $i < count($adminPlugins); $i++) {
		$pluginInfo = $adminPlugins[$i];
		$plugin = $TBBModuleManager->getPlugin($pluginInfo->getValue("group"), "admin");
		if ($plugin->isActive()) $plugin->createMenu($menu);
	}

?>
