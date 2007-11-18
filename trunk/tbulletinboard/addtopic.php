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

	require_once($TBBclassDir.'Board.class.php');
	require_once($libraryClassDir.'Form.class.php');
	require_once($TBBclassDir.'Text.class.php');
	require_once($TBBclassDir.'ModulePlugin.class.php');

	$boardID = 0;
	if (isSet($_GET['boardID'])) $boardID = $_GET['boardID'];
	if (isSet($_POST['boardID'])) $boardID = $_POST['boardID'];
	$board = $GLOBALS['TBBboardList']->getBoard($boardID);
	if (!is_object($board)) {
		$TBBsession->setMessage("boardNotFound");
		$TBBconfiguration->redirectUri('index.php');
	}
	if (!$board->canAddTopics($TBBcurrentUser)) {
		$TBBsession->setMessage("noTopicBoard");
		$TBBconfiguration->redirectUri('index.php?id='.$board->getID());
	}

	$wizzStep = 0;
	if ((isSet($_POST['actionName']) && isSet($_POST['actionID'])) && ($board->canAddTopics($TBBcurrentUser))) {
		if (isSet($_POST['wizzStep']) && is_numeric($_POST['wizzStep'])) $wizzStep = $_POST['wizzStep'];
		$correct = true;

		if (isSet($_POST['topicModuleID'])) {
			$topicModuleID = trim($_POST['topicModuleID']);
			$topicModule = $TBBModuleManager->getPlugin($topicModuleID, "topic");
			if ($correct && (!is_object($topicModule))) {
				$feedback->addMessage("module niet gevonden (".htmlConvert($modID).")!");
				$correct = false;
			}
		}

		if (($wizzStep > 0) && (is_object($topicModule)) && ($_POST['actionID'] == $TBBsession->getActionID())) {
			if ($topicModule->handleAddTopicAction($feedback, $board)) {
				if (!$topicModule->hasMoreAddTopicSteps($wizzStep)) {
					$wizzStep = 0;
					$TBBsession->actionHandled();
					$TBBsession->setMessage("topicPosted");
					$TBBconfiguration->redirectUri('index.php?id='.$board->getID());
				}
			} else $wizzStep--;
		}
		if (($wizzStep > 0) && (is_object($topicModule)) && ($_POST['actionID'] != $TBBsession->getActionID())) {
			$TBBsession->setMessage("doubleTopic");
			$TBBconfiguration->redirectUri('index.php?id='.$board->getID());
		}
	}
	$pageTitle = $TBBconfiguration->getBoardName().' - '.'Onderwerp starten';
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');

	$feedback->showMessages();

	$here = $board->getLocation();
	$here->addLocation("Nieuw onderwerp", "addtopic.php?boardID=".$boardID);
	$here->showLocation();

	if (!$board->canAddTopics($TBBcurrentUser)) {
		$text = new Text();
		$text->addHTMLText("Er mogen geen nieuwe onderwerpen worden gestart in dit forum");
		$text->showText();
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}

	$topicModules = $TBBModuleManager->getPluginInfoType("topic");
	//$topicModules = $TBBconfiguration->getTopicModulesInfo();

	$activeTopics = 0;
	$profile = $board->getBoardSettings();
	$selectedTopicModules = $profile->getAllowedTopicPlugins();

	for ($i = 0; $i < count($topicModules); $i++) {
		$topicModule = $topicModules[$i];
		if (($topicModule->getValue('active') == true) &&
			(in_array($topicModule->getValue('group'), $selectedTopicModules))) $activeTopics++;
		//if (($topicModule['active'] == false) &&
		//	$TBBcurrentUser->isActiveAdmin()) && ($topicModule['ID'] == $TBBconfiguration->getReferenceID())) $activeTopics++;
	}

	if ((($activeTopics == 0) && (!$TBBcurrentUser->isActiveAdmin())) || (count($selectedTopicModules) == 0)) {
		$text = new Text();
		$text->addHTMLText("Er zijn geen onderwerp modules ge&iuml;nstalleerd of geactiveerd!");
		$text->showText();
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}

	if ($wizzStep == 0) {
?>
	<div class="center">
		<form id="topicTypeForm" action="addtopic.php" method="post">
		<div id="topicType">
			<input type="hidden" name="boardID" value="<?=$board->getID(); ?>" />
			<span class="topicTypeName">Selecteer soort onderwerp:</span>
			<select name="topicModuleID" onchange="form.submit()">
<?php
	$topicModuleID = $profile->getDefaultTopicTypeID();
	if ($topicModuleID === false) {
		$topicModuleID = $topicModules[0]->getValue('group'); // Selecteer de eerste de beste
	}
	if (isSet($_POST['topicModuleID'])) {
		$topicModuleID = $_POST['topicModuleID'];
	}
	for ($i = 0; $i < count($topicModules); $i++) {
		$topicModule = $topicModules[$i];
		if (in_Array($topicModule->getValue('group'), $selectedTopicModules)) {
			if (($topicModule->getValue('active') == true) || (($topicModule->getValue('active') == false) && ($TBBcurrentUser->isActiveAdmin()))) {
				$topicPlugin = $TBBModuleManager->getPlugin($topicModule->getValue('group'), "topic");

				printf("\t\t\t\t<option value=\"%s\"%s>%s</option>\n",
					htmlConvert($topicModule->getValue('group')),
					($topicModule->getValue('group') == $topicModuleID) ? " selected=\"selected\"" : "",
					htmlConvert($topicPlugin->getSelectionName()));
			}
		}
	}
?>
			</select>
		</div>
		</form>
	</div>
<?php
	}
	$topicModule = $TBBModuleManager->getPlugin($topicModuleID, "topic");
	$form = new Form("newTopic", "addtopic.php");
	$form->addHiddenField("actionName", "addTopic");
	$form->addHiddenField("actionID", $TBBsession->getActionID());
	$form->addHiddenField("boardID", $board->getID());
	$form->addHiddenField("topicModuleID", $topicModuleID);
	$form->addHiddenField("wizzStep", $wizzStep+1);

	$topicModule->setAddTopicForm($form, $wizzStep, $board);
	$form->writeForm();


	include($TBBincludeDir.'htmlbottom.php');
?>
