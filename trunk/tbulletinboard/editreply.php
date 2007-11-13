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
	require_once($TBBclassDir.'Board.class.php');
	require_once($TBBclassDir.'Text.class.php');

	$topicID = 0;
	if (isSet($_GET['topicID'])) $topicID = $_GET['topicID'];
	if (isSet($_POST['topicID'])) $topicID = $_POST['topicID'];
	$edit = false;
	if (isSet($_GET['edit'])) $edit = ($_GET['edit'] == 'yes') ? true : false;
	if (isSet($_POST['edit'])) $edit = ($_POST['edit'] == 'yes') ? true : false;
	$postID = 0;
	if (isSet($_GET['postID'])) $postID = $_GET['postID'];
	if (isSet($_POST['postID'])) $postID = $_POST['postID'];

	$topic = $GLOBALS['TBBtopicList']->getTopic($topicID);
	if (!is_object($topic)) {
		$TBBsession->setMessage("topicNotFound");
		$TBBconfiguration->redirectUri('index.php');
	}
	$board = $topic->board;

	$wizzStep = 0;
	if ((isSet($_POST['actionName']) && isSet($_POST['actionID'])) && ($board->canWrite($TBBcurrentUser))) {
		if (isSet($_POST['wizzStep']) && is_numeric($_POST['wizzStep'])) $wizzStep = $_POST['wizzStep'];

		if ($_POST['actionID'] != $TBBsession->getActionID()) {
			$TBBsession->setMessage("doublePost");
			$TBBconfiguration->redirectUri('topic.php?id='.$topicID);
		} else {
			$topicModule = $topic->getTopicModule();
			if ($edit) {
				if ($topicModule->handleEditReactionAction($feedback, $topic)) {
					$wizzStep++;
					$TBBsession->actionHandled();
				};
			} else {
				if ($topicModule->handleAddReactionAction($feedback, $topic)) {
					$wizzStep++;
					$TBBsession->actionHandled();
				};
			}
		}
	}

	if ($edit)
		$pageTitle = $TBBconfiguration->getBoardName() . ' - Reactie bewerken';
	else
		$pageTitle = $TBBconfiguration->getBoardName() . ' - Reactie plaatsen';
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');
	$feedback->showMessages();

	if (!$board->canRead($TBBcurrentUser)) {
		$here = $board->getLocation();
		$here->showLocation();

		$text = new Text();
		if ($edit)
			$text->addHTMLText("Er kunnen geen reacties worden bewerkt in onderwerpen uit een forum waar je geen toegang toe hebt!");
		else
			$text->addHTMLText("Er kunnen geen reacties worden geplaatst op onderwerpen uit een forum waar je geen toegang toe hebt!");
		$text->showText();
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}

	if ($edit) {
		$reaction = $topic->getPostByID($postID);
		if (is_object($reaction)) {
			$author = $reaction->getUser();
			if (($author->getUserID() != $TBBcurrentUser->getUserID()) && (!$TBBcurrentUser->isActiveAdmin())) {
				$text = new Text();
				$text->addHTMLText("Je mag dit bericht niet bewerken!");
				$text->showText();
				include($TBBincludeDir.'htmlbottom.php');
				exit;
			}
		} else {
			$text = new Text();
			$text->addHTMLText("Bericht niet gevonden!");
			$text->showText();
			include($TBBincludeDir.'htmlbottom.php');
			exit;
		}
	}

	$here = $board->getLocation();
	$here->addLocation(htmlConvert($topic->getTitle()), 'topic.php?id='.$topicID);
	if ($edit)
		$here->addLocation('Reactie bewerken', 'addreply.php?topicID='.$topicID.'&amp;edit=yes&amp;postID='.$postID);
	else
		$here->addLocation('Reactie plaatsen', 'addreply.php?topicID='.$topicID);
	$here->showLocation();

	if ($board->canWrite($TBBcurrentUser) && ((!$topic->isLocked()) || ($TBBcurrentUser->isActiveAdmin()))) {
		$topicModule = $topic->getTopicModule();
		$form = new Form("addReaction", "editreply.php");
		$form->addHiddenField("actionName", "addReaction");
		$form->addHiddenField("actionID", $TBBsession->getActionID());
		$form->addHiddenField("topicID", $topic->getID());
		$form->addHiddenField("wizzStep", $wizzStep);
		if ($edit) {
			$form->addHiddenField("edit", "yes");
			$form->addHiddenField("postID", $postID);
			$topicModule->editReactionForm($form, $wizzStep, $topic, $postID);
		} else
			$topicModule->addReactionForm($form, $wizzStep, $topic);
		$form->writeForm();
		
		if (!$edit) {
			$viewOptions = array();
			//print "Nieuwe reactie!! probeer hier maar es wat oude reacties te tonen!";		
			$viewOptions['direction'] = 'descending';
			$viewOptions['limit'] = 10;
			$viewOptions['userInfo'] = 'small';
			$viewOptions['toolbar'] = false;
			$viewOptions['mode'] = 'view';
			$topicModule->showTopic($topic, 0, $viewOptions);
		}
	} else {
		$text = new Text();
		if ($edit)
			$text->addHTMLText("Er kunnen geen reacties worden bewerkt in dit onderwerp");
		else
			$text->addHTMLText("Er kunnen geen reacties worden geplaatst op dit onderwerp");
		$text->showText();
	}

	include($TBBincludeDir.'htmlbottom.php');
?>
