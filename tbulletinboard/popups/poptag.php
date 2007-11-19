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

	importClass("board.TagListManager");	
	importClass("board.Text");	
	importClass("util.TextParser");	
	importClass("interface.Menu");	

	$pageTitle = 'Tag informatie';
	include($TBBincludeDir.'popuptop.php');

	if (!isSet($_GET["id"])) {
		?>
			<h2>Geen id meegegeven</h2>
		<?php
		$text = new Text();
		$text->addHTMLText("Geen tag id parameter opgegeven!");
		$text->showText();
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}

	if (!$TBBcurrentUser->isAdministrator()) {
		?>
			<h2>Geen toegang!</h2>
		<?php
		$text = new Text();
		$text->addHTMLText("Dit scherm is alleen voor administrators!");
		$text->showText();
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}

	$tbbTags = $GLOBALS['TBBtagListManager']->getTBBtags(true);
	$tagIndex = $tbbTags->indexOf($_GET["id"]);

	$tag = $tbbTags->getTag($tagIndex);
?>
	<h2>TBBTag&trade; Info "<?=$tag->getName() ?>"</h2>
<?php
	$navMenu = new Menu();

	if ($tagIndex > 0) {
		$prevTag = $tbbTags->getTag($tagIndex -1);
		$navMenu->addItem('previous', '', 'Vorige', '?id='.$prevTag->getID(), '', '', 0, false, '');
	} else {
		$navMenu->addItem('previous', '', 'Vorige', '', '', '', 0, false, '');
	}
	if ($tagIndex < ($tbbTags->getTagCount()-1)) {
		$nextTag = $tbbTags->getTag($tagIndex +1);
		$navMenu->addItem('next', '', 'Volgende', '?id='.$nextTag->getID(), '', '', 0, false, '');
	} else {
		$navMenu->addItem('next', '', 'Volgende', '', '', '', 0, false, '');
	}
	$navMenu->showMenu("configMenu");


	$TBBtextParser = new TextParser();
	$text = new Text();

	$text->addHTMLText(htmlConvert($tag->getDescription()));
	$text->addHTMLText($TBBtextParser->parseMessageText($tag->getExample(), true, false));
	$text->addHTMLText($TBBtextParser->parseMessageText($tag->getExample(), true, $tbbTags));
	$text->addHTMLText(htmlConvert($tag->getHtmlReplace()));
	$text->showText();


	include($TBBincludeDir.'popupbottom.php');
?>
