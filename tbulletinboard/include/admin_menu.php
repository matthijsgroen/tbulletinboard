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
