<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
	 */

	require_once("folder.config.php");
	// Load the configuration
	require_once($TBBconfigDir.'configuration.php');

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Instellingen';
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');
	require_once($TBBclassDir.'Location.class.php');
	require_once($ivLibDir.'Form.class.php');
	require_once($ivLibDir.'Table.class.php');
	require_once($ivLibDir.'TextParser.class.php');
	require_once($TBBclassDir.'Board.class.php');
	require_once($TBBclassDir.'Text.class.php');
	require_once($TBBclassDir.'TagListManager.class.php');
	require_once($TBBclassDir.'TBBEmoticonList.class.php');
	require_once($TBBclassDir.'ActionHandler.class.php');

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