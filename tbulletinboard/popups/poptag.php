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
	require_once($TBBclassDir.'TagListManager.class.php');
	require_once($TBBclassDir.'Text.class.php');
	require_once($ivLibDir.'TextParser.class.php');
	require_once($ivLibDir.'Menu.class.php');

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
