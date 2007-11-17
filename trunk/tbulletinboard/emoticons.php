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

	require_once("folder.config.php");
	// Load the configuration
	require_once($TBBconfigDir.'configuration.php');

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Emoticons';
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');

	require_once($ivLibDir.'Table.class.php');
	require_once($TBBclassDir.'Board.class.php');
	require_once($TBBclassDir.'Location.class.php');
	require_once($TBBclassDir.'TBBEmoticonList.class.php');

	$feedback->showMessages();

	$board = $TBBboardList->getBoard($TBBconfiguration->getHelpBoardID());
	$here = $board->getLocation();
	$here->addLocation('Emoticons', 'emoticons.php');
	$here->showLocation();

	$emoticons = $GLOBALS['TBBemoticonList']->getEmoticons();
	$table = new Table();
	$table->setHeader("Emoticon", "Naam", "Codes");
	for ($i = 0; $i < count($emoticons); $i++) {
		$emoticon = $emoticons[$i];
		$code = "";
		for ($j = 0; $j < count($emoticon['textCodes']); $j++) {
			$code .= "<kbd>" . $emoticon['textCodes'][$j] . "</kbd>";
			if ($j < (count($emoticon['textCodes']) - 1)) $code .= ", ";
		}
		$table->addRow(
			sprintf('<img src="%s" alt="" />', $emoticon['imgUrl']),
			htmlConvert($emoticon['name']),
			$code
		);
	}
	$table->showTable();

	include($TBBincludeDir.'htmlbottom.php');
?>
