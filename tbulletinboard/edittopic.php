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

	importClass("interface.Form");
	importClass("board.Board");
	importClass("board.Text");

	$topicID = 0;
	if (isSet($_GET['id'])) $topicID = $_GET['id'];
	if (isSet($_POST['topicID'])) $topicID = $_POST['topicID'];

	$topic = $GLOBALS['TBBtopicList']->getTopic($topicID);
	if (!is_object($topic)) {
		$TBBsession->setMessage("topicNotFound");
		$TBBconfiguration->redirectUri('index.php');
	}
	$board = $topic->board;

	if (!$board->canRead($TBBcurrentUser)) {
		$TBBsession->setMessage("noReadBoard");
		$TBBconfiguration->redirectUri('index.php');
	}

	$topicModule = $topic->getTopicModule();

	$wizzStep = 0;
	if ((isSet($_POST['actionName']) && isSet($_POST['actionID'])) && ($board->canAddTopics($TBBcurrentUser))) {
		if (isSet($_POST['wizzStep']) && is_numeric($_POST['wizzStep'])) $wizzStep = $_POST['wizzStep'];
		$correct = true;

		if (($wizzStep > 0) && (is_object($topicModule)) && ($_POST['actionID'] == $TBBsession->getActionID())) {
			if ($topicModule->handleEditTopicAction($feedback, $topic)) {
				if (!$topicModule->hasMoreEditTopicSteps($wizzStep)) {
					$wizzStep = 0;
					$TBBsession->actionHandled();
					$TBBsession->setMessage("topicPosted");
					$TBBconfiguration->redirectUri('topic.php?id='.$topicID);
				}
				$TBBsession->actionHandled();
			} else $wizzStep--;
		}
		if (($wizzStep > 0) && (is_object($topicModule)) && ($_POST['actionID'] != $TBBsession->getActionID())) {
			$TBBsession->setMessage("doubleTopic");
			$TBBconfiguration->redirectUri('topic.php?id='.$topicID);
		}
	}

	$pageTitle = $TBBconfiguration->getBoardName().' - '.'Onderwerp bewerken';
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');
	$feedback->showMessages();

	$here = $board->getLocation();
	$here->addLocation(htmlConvert($topic->getTitle()), 'topic.php?id='.$topicID);
	$here->addLocation("bewerken", 'edittopic.php?id='.$topicID);
	$here->showLocation();

	$author = $topic->getStarter();
	if (($author->getUserID() != $TBBcurrentUser->getUserID()) && (!$TBBcurrentUser->isActiveAdmin())) {
		$text = new Text();
		$text->addHTMLText("Dit onderwerp mag je niet bewerken!");
		$text->showText();
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}

	$form = new Form("editTopic", "edittopic.php");
	$form->addHiddenField("actionName", "editTopic");
	$form->addHiddenField("actionID", $TBBsession->getActionID());
	$form->addHiddenField("topicID", $topic->getID());
	$form->addHiddenField("wizzStep", $wizzStep+1);

	$topicModule->setEditTopicForm($form, $wizzStep, $topic);
	$form->writeForm();


	include($TBBincludeDir.'htmlbottom.php');
?>
