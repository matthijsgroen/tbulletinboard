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

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Tag Help';
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');

	importClass("interface.Form");
	importClass("interface.Location");
	importClass("interface.Table");
	importClass("util.TextParser");
	importClass("board.Board");
	importClass("interface.Text");
	importClass("board.TagListManager");
	importClass("board.TBBEmoticonList");
	importClass("board.ActionHandler");

	$feedback->showMessages();

	$board = $TBBboardList->getBoard($TBBconfiguration->getHelpBoardID());
	$here = $board->getLocation();
	$here->addLocation('TBBtags&trade;', 'tbbtags.php');
	$here->showLocation();

	$tbbTags = $GLOBALS['TBBtagListManager']->getTBBtags();

	$table = new Table();
	$table->setHeader("Naam", "Omschrijving", "Voorbeeld", "Voorbeeld code");

	for ($i = 0; $i < $tbbTags->getTagCount(); $i++) {
		$tbbTag = $tbbTags->getTag($i);
		if ($tbbTag->isActive()) {
			$table->addRow(
				"[".$tbbTag->getName()."]",
				htmlConvert($tbbTag->getDescription()),
				$textParser->parseMessageText($tbbTag->getExample(), $GLOBALS['TBBemoticonList'], $tbbTags),
				htmlConvert($tbbTag->getExample())
			);
		}
	}

	if ($table->getRowCount() > 0) {
		$table->showTable();
	} else {
		$text = new Text();
		$text->addHTMLText("Geen tags gevonden of actief!");
		$text->showText();
	}

	include($TBBincludeDir.'htmlbottom.php');
?>
