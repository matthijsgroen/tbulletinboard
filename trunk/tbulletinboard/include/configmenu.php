<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
	 */

	require_once($ivLibDir . 'Menu.class.php');

	$adminMenu = new Menu();
	$adminMenu->addItem('user', '', 'Persoonlijke instellingen', 'usercontrol.php', '', '', 0, false, '');
	$adminMenu->addItem('system', '', 'Systeem instellingen', ($TBBcurrentUser->isAdministrator()) ? 'adminboard.php' : '', '', '', 0, false, '');
	$adminMenu->addItem('plugin', '', 'Plugin instellingen', ($TBBcurrentUser->isAdministrator()) ? 'adminmodules.php' : '', '', '', 0, false, '');

?>
