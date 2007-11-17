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
	require_once($TBBconfigDir . 'configuration.php');
	require_once($TBBclassDir . 'Board.class.php');
	require_once($TBBclassDir . 'ActionHandler.class.php');
	require_once($TBBclassDir . 'TopicRead.bean.php');
	require_once($TBBclassDir . 'tbblib.php');

	$topicID = 0;
	if (isSet($_GET['id'])) $topicID = $_GET['id'];
	$pageNr = 0;
	if (isSet($_GET['pageNr'])) $pageNr = ($_GET['pageNr'] -1);
	if ($pageNr < 0) $pageNr = 0;

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
	if ($TBBsession->getMessage()) {
		if ($TBBsession->getMessage() == "doublePost") {
			$feedback->addMessage("Berichten kunnen maar 1 keer worden geplaatst!");
			$TBBsession->eraseMessage();
		}
		if ($TBBsession->getMessage() == "topicMoved") {
			$feedback->addMessage("Onderwerp verplaatst");
			$TBBsession->eraseMessage();
		}
	}

	if (isSet($_GET['action']) && isSet($_GET['actionID'])) {
		if (($_GET['action'] == 'sticky') && ($_GET['actionID'] == $TBBsession->getActionID())) {
			$action = new ActionHandler($feedback, $_GET);
			$action->check($TBBcurrentUser->isAdministrator(), 'Deze actie is alleen voor administrators');
			$action->check($topic->setSpecial('sticky'), 'Actie mislukt!');
			$action->finish('Onderwerp is nu sticky');
		}
		if (($_GET['action'] == 'normal') && ($_GET['actionID'] == $TBBsession->getActionID())) {
			$action = new ActionHandler($feedback, $_GET);
			$action->check($TBBcurrentUser->isAdministrator(), 'Deze actie is alleen voor administrators');
			$action->check($topic->setSpecial('no'), 'Actie mislukt!');
			$action->finish('Onderwerp is nu normaal');
		}
		if (($_GET['action'] == 'lock') && ($_GET['actionID'] == $TBBsession->getActionID())) {
			$action = new ActionHandler($feedback, $_GET);
			$action->check($TBBcurrentUser->isAdministrator(), 'Deze actie is alleen voor administrators');
			$action->check($topic->setLocked(true), 'Actie mislukt!');
			$action->finish('Onderwerp is nu gesloten');
		}
		if (($_GET['action'] == 'unlock') && ($_GET['actionID'] == $TBBsession->getActionID())) {
			$action = new ActionHandler($feedback, $_GET);
			$action->check($TBBcurrentUser->isAdministrator(), 'Deze actie is alleen voor administrators');
			$action->check($topic->setLocked(false), 'Actie mislukt!');
			$action->finish('Onderwerp is nu geopend');
		}
		if (($_GET['action'] == 'adminOn') && ($_GET['actionID'] == $TBBsession->getActionID())) {
			$TBBcurrentUser->setAdminActive(true);
			$TBBsession->actionHandled();
			$feedback->addMessage('Administrator modus aan');
		}
		if (($_GET['action'] == 'adminOff') && ($_GET['actionID'] == $TBBsession->getActionID())) {
			$TBBcurrentUser->setAdminActive(false);
			$TBBsession->actionHandled();
			$feedback->addMessage('Administrator modus uit');
		}
	}


	$pageTitle = $TBBconfiguration->getBoardName().' - '.htmlConvert($topic->getTitle());
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');
	$feedback->showMessages();

	$here = $board->getLocation();
	$here->addLocation(htmlConvert($topic->getTitle()), 'topic.php?id='.$topicID);
	$here->showLocation();

	$viewOptions = array();
	$viewOptions['mode'] = "normal";
	$viewOptions['toolbar'] = true;
	$viewOptions['userInfo'] = 'complete';

	if (isSet($_GET['highlight'])) { $viewOptions['highlight'] = explode(' ', $_GET['highlight']); }
	if (isSet($_GET['goto'])) { 
		if ($_GET['goto'] == 'lastpost') $viewOptions['goto'] = 'lastpost'; 
		if (is_numeric($_GET['goto'])) $viewOptions['goto'] = $_GET['goto']; 
	}

	$topicModule = $topic->getTopicModule();
	$topicModule->showTopic($topic, $pageNr, $viewOptions);

	// Handle topic reading
	$lastPagePostDate = $topicModule->getLastPostDate($topic, $pageNr);
	if (($lastPagePostDate !== false) && (!$TBBcurrentUser->isGuest()) && ($lastPagePostDate > $TBBcurrentUser->getReadThreshold())) {

		// write read status for this topic.
		$topicReadTable = new TopicReadTable($database);
		$dataFilter = new DataFilter();
		$dataFilter->addEquals("userID", $TBBcurrentUser->getUserID());
		$dataFilter->addEquals("topicID", $topic->getID());

		$topicReadTable->selectRows($dataFilter, new ColumnSorting());
		if ($row = $topicReadTable->getRow()) {
			$lastReadDate = $row->getValue("lastRead");
			if ($lastPagePostDate > $lastReadDate) {
				$row->setValue("lastRead", $lastPagePostDate);
				$row->store();
			}
		} else {
			// no time stored!
			$newRow = $topicReadTable->addRow();
			$newRow->setValue("userID", $TBBcurrentUser->getUserID());
			$newRow->setValue("topicID", $topic->getID());
			$newRow->setValue("lastRead", $lastPagePostDate);
			$newRow->store();
		}
	}

	$here->showLocation();
	if ($TBBcurrentUser->isAdministrator()) {
		$adminOptions = new Menu();
		$adminOptions->addGroup('opt', 'Adminstrator opties');
		if ($TBBcurrentUser->isActiveAdmin()) {
			$adminOptions->addItem('modeOn', 'opt', 'Zet admin mode uit', 'topic.php?id='.$topicID.'&amp;action=adminOff&amp;actionID='.$TBBsession->getActionID().'&amp;pageNr='.($pageNr+1), '', '', 0, false, '');
		} else {
			$adminOptions->addItem('modeOff', 'opt', 'Zet admin mode aan', 'topic.php?id='.$topicID.'&amp;action=adminOn&amp;actionID='.$TBBsession->getActionID().'&amp;pageNr='.($pageNr+1), '', '', 0, false, '');
		}
		if (!$topic->isSticky()) {
			$adminOptions->addItem('setSticky', 'opt', 'Maak sticky', 'topic.php?id='.$topicID.'&amp;action=sticky&amp;actionID='.$TBBsession->getActionID().'&amp;pageNr='.($pageNr+1), '', '', 0, false, '');
		}
		if (!$topic->isNormal()) {
			$adminOptions->addItem('setNormal', 'opt', 'Maak Normaal', 'topic.php?id='.$topicID.'&amp;action=normal&amp;actionID='.$TBBsession->getActionID().'&amp;pageNr='.($pageNr+1), '', '', 0, false, '');
		}
		if (!$topic->isLocked()) {
			$adminOptions->addItem('setLocked', 'opt', 'Sluit onderwerp', 'topic.php?id='.$topicID.'&amp;action=lock&amp;actionID='.$TBBsession->getActionID().'&amp;pageNr='.($pageNr+1), '', '', 0, false, '');
		}
		if ($topic->isLocked()) {
			$adminOptions->addItem('setLocked', 'opt', 'Open onderwerp', 'topic.php?id='.$topicID.'&amp;action=unlock&amp;actionID='.$TBBsession->getActionID().'&amp;pageNr='.($pageNr+1), '', '', 0, false, '');
		}
		$adminOptions->addItem('move', 'opt', 'Verplaatsen', "javascript:popupWindow('popups/movetopic.php?id=".$topicID."', 500, 400, 'topicMove')", '', '', 0, false, '');
		$adminOptions->addItem('delete', 'opt', 'Verwijderen', "javascript:popupWindow('popups/deletetopic.php?id=".$topicID."', 500, 400, 'topicDelete')", '', '', 0, false, '');
		$adminOptions->showMenu("adminOptions");
	}
	writeJumpLocationField($topic->board->getID(), "");


	include($TBBincludeDir.'htmlbottom.php');
?>
