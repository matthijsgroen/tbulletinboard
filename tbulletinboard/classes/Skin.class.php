<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
	 */
	require_once($ivLibDir.'Table.class.php');

	class Skin {

		var $subBoardsOpen;

		function Skin() {
		}

		function showSubBoards($boardID) {
			global $TBBconfiguration;
			global $TBBcurrentUser;
			global $TBBboardList;
			global $textParser;

			$subBoardList = $TBBboardList->getBoardCache($boardID);
			$mayRead = 0;
			$subBoards = $subBoardList['childs'];
			for ($i = 0; $i < count($subBoards); $i++) {
				$subBoard = $subBoards[$i];
				if ($TBBboardList->canReadBoard($subBoard['ID'], $TBBcurrentUser) && ((!$subBoard['hidden']) || $TBBcurrentUser->isActiveAdmin())) $mayRead++;
			}
			if ($mayRead == 0) return;
			$boardsTable = new Table();
			$boardsTable->cellSpacing = 0;
			$boardsTable->setClass("subBoard-table");
			$boardsTable->setHeader("&nbsp;", "Forum", "Berichten", "Onderwerpen", "Laatst geplaatst", "Moderator");
			$boardsTable->setHeaderClasses("read", "forum", "messages", "topics", "lastpost", "moderator");
			$boardsTable->setRowClasses("read", "forum", "messages", "topics", "lastpost", "moderator");
?>
	<div class="center">
		<div id="subBoards">
<?php
			for ($i = 0; $i < count($subBoards); $i++) {
				$subBoard = $subBoards[$i];
				if ($TBBboardList->canReadBoard($subBoard['ID'], $TBBcurrentUser)) {
					if (!$subBoard['hidden'] || $TBBcurrentUser->isActiveAdmin()) {
						if ($subBoard['open']) {
							if ($boardsTable->getRowCount() > 0) {
								$boardsTable->showTable();
								$boardsTable->clear();
							}
	?>
			<div class="subboard-header">
				<h3><a href="index.php?id=<?=$subBoard["ID"] ?>"><?=htmlConvert($subBoard["name"]); ?></a></h3>
				<small><?=htmlConvert($subBoard["comment"]); ?></small>
			</div>
	<?php
							$openBoards = $subBoard['childs'];
							$subIndex = 0;
							for ($j = 0; $j < count($openBoards); $j++) {
								$openBoard = $openBoards[$j];
								if ($TBBboardList->canReadBoard($openBoard['ID'], $TBBcurrentUser) && ((!$openBoard['hidden']) || ($TBBcurrentUser->isActiveAdmin()))) {
									$this->addBoardRow($boardsTable, $openBoard);
								}
							}
							$boardsTable->showTable();
							$boardsTable->clear();
						} else {
							$this->addBoardRow($boardsTable, $subBoard);
						}
					}
				}
			}
			if ($boardsTable->getRowCount() > 0)
				$boardsTable->showTable();
?>
		</div>
	</div>
<?php
		}

		function addBoardRow(&$table, $boardInfo) {
			global $TBBconfiguration;
			global $textParser;
			global $TBBcurrentUser;
			global $TBBboardList;

			$lastTopic = "&nbsp;";
			$boardStatsCache = $TBBboardList->getBoardStatsCache($boardInfo['ID']);

			if ($boardStatsCache['postDate'] !== false) {
				global $TBBuserManagement;
				$user = $TBBuserManagement->getUserByID($boardStatsCache['postUser']);
				$time = $boardStatsCache['postDate'];
				$title = $boardStatsCache['topicTitle'];
				if (strLen($title) > 20) $title = subStr($title, 0, 18)."...";
				//$newWindow = $topic->openInNewWindow();
				$newWindow = false;

				$lastTopic = sprintf(
					'<a href="topic.php?id=%s&amp;goto=lastpost#lastpost" title="Ga naar laatste bericht"%s><img src="images/lastpost.gif" alt="" />%s</a><br /><span class="messageAuthor">(%s)</span><br />%s',
					$boardStatsCache['topicID'],
					($newWindow ? ' target="_blank"' : ''),
					htmlConvert($title),
					htmlConvert($user->getNickName()),
					$TBBconfiguration->parseDate($time)
				);
			}

			$subBoardsStr = '';
			$subBoards = $boardInfo['childs'];
			if (count($subBoards) > 0) {
				$subBoardsStr = '<br /><small class="subboards">Subfora: ';
				for ($i = 0; $i < count($subBoards); $i++) {
					$subje = $subBoards[$i];
					$subBoardsStr .= sprintf('<a href="index.php?id=%s">%s</a>',
						$subje['ID'],
						htmlConvert($subje['name'])
					);
					if ($i < (count($subBoards) - 1)) $subBoardsStr .= ', ';
				}
				$subBoardsStr .= '</small>';
			}

			$unreadStr = "";
			$nrUnread = 1;
			if (!$TBBcurrentUser->isGuest()) {
				$nrUnread = $TBBboardList->getNrUnreadBoardTopics($boardInfo['ID'], $TBBcurrentUser);
				$nrUnreadReactions = $TBBboardList->getNrUnreadBoardReactions($boardInfo['ID'], $TBBcurrentUser);
				if (($nrUnread > 0) || ($nrUnreadReactions > 0)) {
					$unreadArray = array();

					if ($nrUnreadReactions > 0) {
						$unreadArray[] = sprintf('%s ongelezen reactie%s',
							$nrUnreadReactions,
							($nrUnreadReactions == 1) ? "" : "s"
						);
					}
					if ($nrUnread > 0) {
						$unreadArray[] = sprintf('%s ongelezen onderwerp%s',
							$nrUnread,
							($nrUnread == 1) ? "" : "en"
						);
					}
					$unreadStr = '<br /><small class="unreadStats">'.implode(', ', $unreadArray).'</small>';
				} else $unreadStr = "";
			}

			$readBoard = '<img src="images/on.gif" alt="" />';
			$boardLink = '<a href="index.php?id=%s" class="unreadBoard">%s</a><br /><small>%s</small>%s%s';
			if (($nrUnread == 0) && ($nrUnreadReactions == 0)) {
				$readBoard = '<img src="images/off.gif" alt="" />';
				$boardLink = '<a href="index.php?id=%s" class="readBoard">%s</a><br /><small>%s</small>%s%s';
			}
			$table->addRow(
				$readBoard,
				sprintf($boardLink,
					$boardInfo['ID'],
					htmlConvert($boardInfo['name']),
					htmlConvert($boardInfo['comment']),
					$subBoardsStr,
					$unreadStr
				),
				$boardStatsCache['posts'],//'-',
				$boardStatsCache['topics'],//'-',
				$lastTopic,
				"&nbsp;"
			);
		}
	}

	$GLOBALS['TBBskin'] = new Skin();

?>
