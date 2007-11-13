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
	$menu->addItem('stats', '', 'Algemeen', 'adminboard.php', '', '', 0, true, '');

	$menu->addGroup('board', 'Board');
	$menu->addItem('bprofile', 'board', 'Board profielen', 'boardprofiles.php', '', '', 0, false, '');

	$menu->addGroup('members', 'Leden');
	$menu->addItem('admins', 'members', 'Administrators', $TBBcurrentUser->isMaster() ? 'administrators.php' : '', '', '', 0, false, '');
	$menu->addItem('groups', 'members', 'Groepen', 'admingroups.php', '', '', 0, false, '');
	$menu->addItem('avatars', 'members', 'Avatars', 'adminavatars.php', '', '', 0, false, '');

	$menu->addGroup('text', 'Tekst');
	$menu->addItem('tags', 'text', 'TBBtags&trade;', $TBBcurrentUser->isAdministrator() ? 'admintags.php' : '', '', '', 0, false, '');
	$menu->addItem('emoticons', 'text', 'Emoticons', 'admintext.php', '', '', 0, false, '');
	$menu->addItem('topicIcons', 'text', 'Onderwerp iconen', 'admintopicicons.php', '', '', 0, false, '');
	/*
	$menu->addItem('modadmin', 'modules', 'Extra', 'adminextra.php', '', '', 0, false, '');

	$menu->addGroup('old', 'Ouwe meuk');
	$menu->addItem('membermodules', 'old', 'Leden modules', $TBBcurrentUser->isMaster() ? 'adminmodmem.php' : '', '', '', 0, false, '');
	*/

?>
