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

	require_once($TBBclassDir.'tbblib.php');
	require_once($TBBclassDir.'Skin.class.php');
	function handleMessage($name, $text) {
		global $feedback;
		global $TBBsession;
		if ($TBBsession->getMessage() == $name) {
			$feedback->addMessage($text);
			$TBBsession->eraseMessage();
		}
	}

	if ($TBBsession->getMessage()) {
		handleMessage("loggedIn", "Welkom terug <strong>".htmlConvert($TBBcurrentUser->getNickName())."</strong>!");
		handleMessage("addBoard", "Board toegevoegd!");
		handleMessage("editBoard", "Board bewerkt!");
		handleMessage("noReadBoard", "Je hebt geen toegang tot dat board!");
		handleMessage("boardNotFound", "Board niet gevonden!");
		handleMessage("boardNotMove", "Overzicht kan niet worden verplaatst!");
		handleMessage("boardNotRemove", "Overzicht kan niet worden verwijderd!");
		handleMessage("topicNotFound", "Onderwerp niet gevonden!");
		handleMessage("notTopicBoard", "Er kunnen geen nieuwe onderwerpen worden geplaatst!");
		handleMessage("doubleTopic", "Onderwerpen kunnen maar 1 keer worden geplaatst!");
		handleMessage("topicPosted", "Onderwerp geplaatst");
	}

	$boardID = 0;
	if (isSet($_GET['id'])) $boardID = $_GET['id'];
	if (isSet($_POST['id'])) $boardID = $_POST['id'];
	$board = $TBBboardList->getBoard($boardID);
	if (!is_object($board)) {
		$TBBsession->setMessage("boardNotFound");
		$TBBconfiguration->redirectUri('index.php');
	}
	if (!$board->canRead($TBBcurrentUser)) {
		$TBBsession->setMessage("noReadBoard");
		$TBBconfiguration->redirectUri('index.php');
	}
	$board->incView();

	if (isSet($_GET['actionName']) && isSet($_GET['actionID'])) {
		if (($_GET['actionName'] == 'adminOn') && ($_GET['actionID'] == $TBBsession->getActionID())) {
			$TBBcurrentUser->setAdminActive(true);
			$TBBsession->actionHandled();
			$feedback->addMessage('Administrator modus aan');
		}
		if (($_GET['actionName'] == 'adminOff') && ($_GET['actionID'] == $TBBsession->getActionID())) {
			$TBBcurrentUser->setAdminActive(false);
			$TBBsession->actionHandled();
			$feedback->addMessage('Administrator modus uit');
		}
		if (($_GET['actionName'] == 'markAsRead') && ($_GET['actionID'] == $TBBsession->getActionID())) {
			$board->markAsRead($TBBcurrentUser);
			$TBBsession->actionHandled();
			$feedback->addMessage('Berichten gemarkeerd als gelezen');
		}
	}
	if (isSet($_POST['actionName']) && ($_POST['actionName'] == 'jump')) {
		$TBBconfiguration->redirectUri($_POST['page']);
	}
	if (isSet($_POST['actionName']) && isSet($_POST['actionID'])) {
		if (($_POST['actionName'] == 'adminFunc')
			&& ($_POST['actionID'] == $TBBsession->getActionID())
			&& ($TBBcurrentUser->isActiveAdmin())) {
			$topicStr = " onderwerp";
			if (count($_POST['topicID']) != 1) $topicStr .= "en";
			switch($_POST['actionType']) {
				case 'lock':
					$board->closeTopics($_POST['topicID'], true);
					$TBBsession->actionHandled();
					$feedback->addMessage(count($_POST['topicID']).$topicStr.' gesloten');
					unset($_POST['topicID']);
				break;
				case 'open':
					$board->closeTopics($_POST['topicID'], false);
					$TBBsession->actionHandled();
					$feedback->addMessage(count($_POST['topicID']).$topicStr.' geopend');
					unset($_POST['topicID']);
				break;
				case 'sticky':
					$board->stickyTopics($_POST['topicID'], "sticky");
					$TBBsession->actionHandled();
					$feedback->addMessage(count($_POST['topicID']).$topicStr.' sticky gemaakt');
					unset($_POST['topicID']);
				break;
				case 'unsticky':
					$board->stickyTopics($_POST['topicID'], "no");
					$TBBsession->actionHandled();
					$feedback->addMessage(count($_POST['topicID']).$topicStr.' unsticky gemaakt');
					unset($_POST['topicID']);
				break;
				case 'delete':
					$board->deleteTopics($_POST['topicID']);
					$TBBsession->actionHandled();
					$feedback->addMessage(count($_POST['topicID']).$topicStr.' verwijdert');
					unset($_POST['topicID']);
				break;
				case 'move':
					$board->moveTopics($_POST['topicID'], $_POST['newLocation'], ($_POST['leaveTrails'] == "yes"));
					$TBBsession->actionHandled();
					$feedback->addMessage(count($_POST['topicID']).$topicStr.' verplaatst');
					unset($_POST['topicID']);
				break;
			}
		}
	}

	$pageNr = 0;
	if (isSet($_GET['pageNr'])) $pageNr = $_GET['pageNr'] -1;
	if (isSet($_POST['pageNr'])) $pageNr = $_POST['pageNr'] -1;
	if ($pageNr < 0) $pageNr = 0;

	$pageTitle = $TBBconfiguration->getBoardName().' - '.htmlConvert($board->getName());
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');

	$feedback->showMessages();

	$here = $board->getLocation();
	if ($here->locationCount() > 1)
		$here->showLocation();

	$TBBskin->showSubBoards($board->getID());

	function addTopicRow(&$table, &$topic) {
		global $TBBconfiguration;
		global $TBBcurrentUser;
		global $textParser;
		$starter = $topic->getStarter();

		$topicPlugin = $topic->getTopicModule();
		$stateIcon = $topic->getStateIcon();
		$icon = $topic->getIconInfo();
		$lastPost = $topic->getLastPost();
		$time = $lastPost->getTime();
		$user = $lastPost->getUser();
		$newWindow = $topic->openInNewWindow();
		$table->addRow(
			$topic->getID(),
			($stateIcon != false) ? sprintf('<img src="%s" alt="" />', $stateIcon) : "&nbsp;",
			($topic->hasIcon()) ? sprintf('<img src="%s" alt="icon" title="%s" />', $icon['imgUrl'], $icon['name']) : "&nbsp;",
			sprintf(
				'%s%s<a href="topic.php?id=%s"%s>%s</a>%s',
				(($topic->getFirstUnreadLink() === false) ? "" : sprintf('<a href="%s" title="Ga naar het eerste ongelezen bericht"><img src="images/firstnew.gif" title="Ga naar het eerste ongelezen bericht" /></a> ', $topic->getFirstUnreadLink())),
				$topic->getPrefixInfo(),
				$topic->getID(),
				($newWindow ? ' target="_blank"' : ''),
				$textParser->breakLongWords(htmlConvert($topic->getTitle()), 40),
				($topic->hasTitleInfo()) ? " ".$topic->getTitleInfo() : ""
			),
			htmlConvert($starter->getNickName()),
			$topic->getNrPost(),
			$topic->getNrRead(),
			sprintf(
				'<a href="topic.php?id=%s&amp;goto=lastpost#lastpost" title="Ga naar laatste bericht"%s><img src="%slastpost.gif" alt="" /> %s <br />door %s</a>',
				$topic->getID(),
				($newWindow ? ' target="_blank"' : ''),
				$TBBconfiguration->imageOnlineDir,
				$TBBconfiguration->parseDate($time),
				htmlConvert($user->getNickName())
			)
		);
	}

	$pageSize = $TBBcurrentUser->getTopicsPerPage();

	$daysPrune = $TBBcurrentUser->getDaysPrune();
	if ($board->getID() == $TBBconfiguration->getHelpBoardID()) {
		$daysPrune = -1;
	}

	// Topics overview.
	if ($board->allowTopics() && ($board->canRead($TBBcurrentUser))) {
		require_once($ivLibDir.'PageNavigation.class.php');
		$pageBar = new PageNavigation(ceil($board->getPrunedTopicCount($daysPrune) / $pageSize), ($pageNr+1), "index.php?pageNr=%s&amp;id=".$board->getID(), 10);

?>
<form action="index.php" method="post" id="adminTopicForm">
<div class="center">
	<? if (!$TBBcurrentUser->isGuest()) { ?>
	<div id="markAsRead">
		<a href="index.php?id=<?=$boardID ?>&amp;pageNr=<?=($pageNr+1) ?>&amp;actionName=markAsRead&amp;actionID=<?=$TBBsession->getActionID() ?>">Markeer alle onderwerpen als gelezen</a>
	</div>
	<? } ?>
	<div id="topicList">
<?php
		require_once($TBBclassDir.'Buttonbar.class.php');

		$buttonBar = new ButtonBar();
		if ($board->canAddTopics($TBBcurrentUser))
			$buttonBar->addButton("newtopic", "Nieuw onderwerp", "Nieuw onderwerp starten", sprintf("addtopic.php?boardID=%s", $board->getID()));
		$buttonBar->showBar();

		$topicOverview = new Table();
		$topicOverview->cellSpacing = 0;
		$topicOverview->setClass("topics-table");
		$topicOverview->setHeader("topicID", "Onderwerp", "Gestart door", "Reacties", "Gelezen", "Laatste bericht");
		$topicOverview->setHeaderClasses("topID", "subject", "starter", "nrreact", "nrread", "lastreaction");
		$topicOverview->setHeaderColspan(1, 3, 1, 1, 1, 1);
		$topicOverview->setRowClasses("check", "read", "icon", "subject", "starter", "nrreact", "nrread", "lastreaction");

		$topGroups = false;
		require_once($ivLibDir.'TextParser.class.php');
		$textParser = new TextParser();

		$stickyTopics = $board->readStickyTopics();
		if (count($stickyTopics) > 0) {
			$topGroups = true;
			$topicOverview->addGroup('Sticky');
			for ($i = 0; $i < count($stickyTopics); $i++) {
				$topic = $stickyTopics[$i];
				addTopicRow($topicOverview, $topic);
			}
		}
		if ($board->getID() == $TBBconfiguration->getHelpBoardID()) {
			$topGroups = true;
			$topicOverview->addGroup('Speciaal');
			$topicOverview->addRow(-1, "", "", '<a href="emoticons.php">Emoticons</a>', "Help", "", "", "");
			$topicOverview->addRow(-1, "", "", '<a href="tbbtags.php">TBB tags&trade;</a>', "Help", "", "", "");
			$daysPrune = -1;
		}
		if ($topGroups) {
			$topicOverview->addGroup('Onderwerpen');
		}
		$topics = $board->readTopics($pageSize * $pageNr, $pageSize, $daysPrune);
		for ($i = 0; $i < count($topics); $i++) {
			$topic = $topics[$i];
			addTopicRow($topicOverview, $topic);
		}
		if ($TBBcurrentUser->isActiveAdmin()) {
			$topicOverview->setCheckboxColumn(0);
		} else {
			$topicOverview->hideColumn(0);
		}

		$topicOverview->showTable();
		$buttonBar->showBar();
		$pageBar->showPagebar("boardPageBar");
?>
	</div>
<?php if ($TBBcurrentUser->isActiveAdmin()) { ?>
	<div id="adminModeTopic">
		<input type="hidden" name="actionName" value="adminFunc" />
		<input type="hidden" name="actionID" value="<?=$TBBsession->getActionID(); ?>" />
		<input type="hidden" name="newLocation" value="-1" />
		<input type="hidden" name="leaveTrails" value="no" />
		<input type="hidden" name="id" value="<?=$boardID ?>" />
		Met geselecteerde onderwerpen:
		<select name="actionType">
			<option value=""></option>
			<option value="open">Openen</option>
			<option value="lock">Sluiten</option>
			<option value="sticky">Sticky</option>
			<option value="unsticky">Unsticky</option>
			<option value="move">Verplaatsen</option>
			<option value="delete">Verwijderen</option>
		</select>
		<button onclick="return doAdminAction(this.form);">Ok</button>
	</div>
	<script type="text/javascript"><!--

		function doAdminAction(form) {
			var nrChecked = 0;
			var field = form['topicID[]'];
			if (form['topicID[]'] != null) {
				if (field.length == null) {
					if (field.checked) nrChecked++;
				} else {
					for (i = 0; i < field.length; i++) {
						if (field[i].checked == true) nrChecked++;
					}
				}
			}
			if (nrChecked == 0) {
				alert('Geen onderwerpen geselecteerd voor deze bewerking');
				return false;
			}
			var selectRows = ''+nrChecked+' onderwerp';
			if (nrChecked != 1) selectRows += 'en';
			var actionType = form.actionType.value;
			switch(actionType) {
				case 'lock': return true;
				case 'open': return true;
				case 'sticky': return true;
				case 'unsticky': return true;
				<?php if ($board->deletesPermanent()) { ?>
				case 'delete': return confirm(selectRows+' verwijderen\nDeze actie kan niet ongedaan gemaakt worden!\nWeet u het zeker?');
				<?php } else { ?>
				case 'delete': return confirm(selectRows+' verwijderen\nWeet u het zeker?');
				<?php } ?>
				case 'move':
					popupWindow('popups/movetopics.php?boardID=<?=$board->getID(); ?>&nrTopics='+nrChecked, 400, 300, 'moveTopics');
					return false;

				default: alert('Admin action! (moet ik nog ff bouwen dit stukje ;-)) '+actionType);
			}
			return false;
		}

	--></script>
<?php } ?>
</div>
</form>
<?php
	}

	if ($TBBcurrentUser->isAdministrator()) {
		$adminOptions = new Menu();
		$adminOptions->addGroup('opt', 'Adminstrator opties');
		if ($TBBcurrentUser->isActiveAdmin()) {
			$adminOptions->addItem('modeOn', 'opt', 'Zet admin mode uit', 'index.php?id='.$board->getID().'&amp;actionName=adminOff&amp;actionID='.$TBBsession->getActionID(), '', '', 0, false, '');
		} else {
			$adminOptions->addItem('modeOff', 'opt', 'Zet admin mode aan', 'index.php?id='.$board->getID().'&amp;actionName=adminOn&amp;actionID='.$TBBsession->getActionID(), '', '', 0, false, '');
		}
		$adminOptions->addItem('add', 'opt', 'Board toevoegen', 'editboard.php?parent='.$boardID, '', '', 0, false, '');
		if ($boardID != 0) {
			$adminOptions->addItem('edit', 'opt', 'bewerken', 'editboard.php?id='.$boardID, '', '', 0, false, '');
			$adminOptions->addItem('move', 'opt', 'verplaatsen', 'moveboard.php?id='.$boardID, '', '', 0, false, '');
			$adminOptions->addItem('del', 'opt', 'verwijderen', 'removeboard.php?id='.$boardID, '', '', 0, false, '');
		}
		$adminOptions->showMenu("adminOptions");
	}

	writeJumpLocationField($board->getID(), "");

	include($TBBincludeDir.'htmlbottom.php');

?>
