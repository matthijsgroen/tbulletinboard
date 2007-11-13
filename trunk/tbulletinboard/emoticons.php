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