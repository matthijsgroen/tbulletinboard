<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
	 */
	require_once($ivLibDir . 'Menu.class.php');

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
		$plugin = $TBBModuleManager->getPlugin($pluginInfo->getValue("group"), "userpanel");
		if ($plugin->isActive()) $plugin->createMenu($menu);
	}

//	$menu->addGroup('messages', 'Berichten');
//	$menu->addItem('pms', 'messages', 'Priv&eacute;', 'usermessages.php');
//	$menu->addItem('drafts', 'messages', 'Concepten', '');
//	$menu->addItem('drafts', 'messages', 'Concepten', 'userdrafts.php');
//	$menu->addItem('listen', 'messages', 'Automatische Reacties', '');
//	$menu->addItem('listen', 'messages', 'Automatische Reacties', 'usertopicsubscribe.php');
?>
