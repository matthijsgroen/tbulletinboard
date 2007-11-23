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

	importClass("util.LibDateTime");
	importClass("interface.Location");
	importClass("board.MemberGroups");
	importClass("board.BoardProfiles");
	importClass("board.TopicIconList");
	importClass("board.plugin.ModulePlugin");
	importClass("board.TopicIconList");
	importClass("board.UserManagement");

	importBean("board.User");	
	importBean("board.TopicRead");	
	importBean("board.Board");	
	importBean("board.Topic");	
	importBean("board.Reaction");	
	importBean("board.StructureCache");	
	importBean("board.BoardCache");	

	class TopicReaction {

		var $topic;
		var $privateVars;

		function TopicReaction($reactionData, &$topic) {
			$this->topic = $topic;
			$this->privateVars = array();
			$this->privateVars['dbData'] = $reactionData;
			$this->privateVars['reaction'] = ($reactionData != false) ? true : false;
		}

		function getID() {
			$data = $this->privateVars['dbData'];
			return $data->getValue("ID");
		}

		function getTime() {
			if ($this->privateVars['reaction']) {
				$data = $this->privateVars['dbData'];
				return $data->getValue("date");
			} else {
				return $this->topic->getTime();
			}
		}

		function getUser() {
			if ($this->privateVars['reaction']) {
				global $TBBuserManagement;
				$data = $this->privateVars['dbData'];
				$userID = $data->getValue("poster");
				return $TBBuserManagement->getUserByID($userID);
			} else {
				return $this->topic->getStarter();
			}
		}

		function isEdited() {
			$data = $this->privateVars['dbData'];
			return (!$data->isNull("lastChange"));
		}

		function editedBy() {
			global $TBBuserManagement;
			$data = $this->privateVars['dbData'];
			$userID = $data->getValue("changeBy");
			return $TBBuserManagement->getUserByID($userID);
		}

		function lastChange() {
			$data = $this->privateVars['dbData'];
			return $data->getValue("lastChange");
		}
		
		function getIndex() {
			$data = $this->privateVars['dbData'];
			$database = $data->getDatabase();
			$reactionTable = new ReactionTable($database);
			$filter = new DataFilter();
			$filter->addEquals("topicID", $data->getValue("topicID"));
			$filter->addLessThan("date", $data->getValue("date"));
			return $reactionTable->countRows($filter);			
		}

	}

	class BoardTopic {

		var $privateVars;
		var $board;

		function BoardTopic($topicData, &$board) {
			$this->privateVars = array();
			$this->privateVars['dbData'] = $topicData;
			$this->board = $board;
		}

		function hasIcon() {
			$data = $this->privateVars['dbData'];
			return ($data->getValue("icon") != 0) ? true : false;
		}

		function getIconInfo() {
			global $TBBtopicIconList;
			$data = $this->privateVars['dbData'];
			$iconID = $data->getValue("icon");
			$iconInfo = $TBBtopicIconList->getIconInfo($iconID);
			return $iconInfo;
		}

		function getIconID() {
			$data = $this->privateVars['dbData'];
			return $data->getValue("icon");
		}

		function getTitle() {
			$data = $this->privateVars['dbData'];
			return $data->getValue("title");
		}

		function getID() {
			$data = $this->privateVars['dbData'];
			return $data->getValue("ID");
		}

		function getNrRead() {
			$data = $this->privateVars['dbData'];
			return $data->getValue("views");
		}

		function getNrPost() {
			if (isSet($this->privateVars['nrPosts'])) {
				return $this->privateVars['nrPosts'];
			}
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$dataFilter = new DataFilter();
			$dataFilter->addEquals("topicID", $this->getID());

			$dataFunction = new FunctionDescriptions();
			$dataFunction->addCount("ID", "nrPosts");

			$reactionTable = new ReactionTable($database);
			$selectResult = $reactionTable->executeDataFunction($dataFunction, $dataFilter);
			if ($row = $selectResult->getRow()) {
				$this->privateVars['nrPosts'] = $row['nrPosts'];
				return $row['nrPosts'];
			}
			$this->privateVars['nrPosts'] = 0;
			return 0;
		}

		function getStarter() {
			$data = $this->privateVars['dbData'];
			$userID = $data->getValue("poster");
			global $TBBuserManagement;
			return $TBBuserManagement->getUserByID($userID);
		}

		function hasTitleInfo() {
			global $TBBModuleManager;
			$data = $this->privateVars['dbData'];
			$moduleID = $data->getValue("plugin");
			$plugin = $TBBModuleManager->getPlugin($moduleID, "topic");
			return $plugin->hasTitleInfo($this);
		}

		function openInNewWindow() {
			global $TBBModuleManager;
			$data = $this->privateVars['dbData'];
			$moduleID = $data->getValue("plugin");
			$plugin = $TBBModuleManager->getPlugin($moduleID, "topic");
			return $plugin->openNewFrame($this);
		}

		function getPrefixInfo() {
			global $TBBModuleManager;
			$data = $this->privateVars['dbData'];
			$moduleID = $data->getValue("plugin");
			$plugin = $TBBModuleManager->getPlugin($moduleID, "topic");
			return $plugin->getTitlePrefix($this);
		}

		function getFirstUnreadLink() {
			global $TBBModuleManager;
			$data = $this->privateVars['dbData'];
			$moduleID = $data->getValue("plugin");
			$plugin = $TBBModuleManager->getPlugin($moduleID, "topic");
			return $plugin->getFirstUnreadLink($this);
		}

		function getTopicModule() {
			global $TBBModuleManager;
			$data = $this->privateVars['dbData'];
			$moduleID = $data->getValue("plugin");
			return $TBBModuleManager->getPlugin($moduleID, "topic");
			//return false;
		}

		function getTitleInfo() {
			global $TBBModuleManager;
			$data = $this->privateVars['dbData'];
			$moduleID = $data->getValue("plugin");
			$plugin = $TBBModuleManager->getPlugin($moduleID, "topic");
			return $plugin->getTitleInfo($this);
		}

		function lastReadTime() {
			global $TBBcurrentUser;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$dataFilter = new DataFilter();
			$dataFilter->addEquals("userID", $TBBcurrentUser->getUserID());
			$dataFilter->addEquals("topicID", $this->getID());

			$topicReadTable = new TopicReadTable($database);
			$topicReadTable->selectRows($dataFilter, new ColumnSorting());
			if ($readData = $topicReadTable->getRow()) {
				return $readData->getValue("lastRead");
			} else {
				return $TBBcurrentUser->getReadThreshold();
			}
		}

		function getLastPost() {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$dataFilter = new DataFilter();
			$dataFilter->addEquals("topicID", $this->getID());
			$dataFilter->setLimit(1);
			$sorting = new ColumnSorting();
			$sorting->addColumnSort("date", false);

			$reactionTable = new ReactionTable($database);
			$reactionTable->selectRows($dataFilter, $sorting);

			if ($lastPost = $reactionTable->getRow()) {
				$lastPost = new TopicReaction($lastPost, $this);
				return $lastPost;
			} else {
				$lastPost = new TopicReaction(false, $this);
				return $lastPost;
			}
		}

		function getPostByID($id) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$dataFilter = new DataFilter();
			$dataFilter->addEquals("topicID", $this->getID());
			$dataFilter->addEquals("ID", $id);
			$dataFilter->setLimit(1);

			$sorting = new ColumnSorting();
			$reactionTable = new ReactionTable($database);
			$reactionTable->selectRows($dataFilter, $sorting);
			if ($post = $reactionTable->getRow()) {
				$post = new TopicReaction($post, $this);
				return $post;
			} else {
				return false;
			}
		}

		function getTime() {
			$data = $this->privateVars['dbData'];
			return $data->getValue("date");
		}

		function getLastReactionTime() {
			$data = $this->privateVars['dbData'];
			return $data->getValue("lastReaction");
		}

		function isRead() {
			$data = $this->privateVars['dbData'];
			$lastReaction = $data->getValue("lastReaction");
			global $TBBcurrentUser;
			if ($TBBcurrentUser->isGuest()) return false;
			if (!$lastReaction->after($TBBcurrentUser->getReadThreshold())) return true;

			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$id = $TBBcurrentUser->getUserID();
			$filter = new DataFilter();
			$filter->addEquals("userID", $id);
			$filter->addEquals("topicID", $data->getValue("ID"));

			$topicReadTable = new TopicReadTable($database);
			$topicReadTable->selectRows($filter, new ColumnSorting());
			if ($topicRead = $topicReadTable->getRow()) {
				$lastRead = $topicRead->getValue("lastRead");
				//print $lastRead->toString() . " - " . $lastReaction->toString() . " -";
				return (!$lastRead->before($lastReaction));
			}
			return false;
		}
		
		function getFirstUnreadReaction() {
			$data = $this->privateVars['dbData'];
			$lastReaction = $data->getValue("lastReaction");
			global $TBBcurrentUser;
			if ($TBBcurrentUser->isGuest()) return false;
			if ($lastReaction <= $TBBcurrentUser->getReadThreshold()) return false;

			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$id = $TBBcurrentUser->getUserID();
			$filter = new DataFilter();
			$filter->addEquals("userID", $id);
			$filter->addEquals("topicID", $data->getValue("ID"));

			$topicReadTable = new TopicReadTable($database);
			$topicReadTable->selectRows($filter, new ColumnSorting());
			if ($topicRead = $topicReadTable->getRow()) {
				$lastRead = $topicRead->getValue("lastRead");
				$reactionTable = new ReactionTable($database);
				$reactionFilter = new DataFilter();
				$reactionFilter->addEquals("topicID", $data->getValue("ID"));
				$reactionFilter->addGreaterThan("date", $lastRead);
				$reactionFilter->setLimit(1);
				
				$sorting = new ColumnSorting();
				$sorting->addColumnSort("date", true);
				
				$reactionTable->selectRows($reactionFilter, $sorting);
				if ($reaction = $reactionTable->getRow()) {
					return new TopicReaction($reaction, $this);
				}				
			}
			return false;
		}

		function isHot() {
			global $TBBconfiguration;
			if (($this->getNrPost() > $TBBconfiguration->getHotReactions()) || ($this->getNrRead() > $TBBconfiguration->getHotViews()))
				return true;
			return false;
		}

		function isLocked() {
			$data = $this->privateVars['dbData'];
			return $data->getValue("closed");
		}

		function getStateIcon() {
			global $TBBModuleManager;
			$data = $this->privateVars['dbData'];
			$moduleID = $data->getValue("plugin");
			$plugin = $TBBModuleManager->getPlugin($moduleID, "topic");
			return $plugin->getTopicStateIcon($this);
		}

		function incView() {
			if ($this->getID() == 0) return;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$dataMutation = new DataMutation();
			$dataMutation->addToColumn("views", 1);
			$filter = new DataFilter();
			$filter->addEquals("ID", $this->getID());
			$topicTable = new TopicTable($database);
			$topicTable->executeDataMutations($dataMutation, $filter);
		}

		function addReaction(&$user, $state) {
			if (!$this->board->canWrite($user)) return false;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$reactionTable = new ReactionTable($database);
			$newReaction = $reactionTable->addRow();
			$newReaction->setValue("topicID", $this->getID());
			$newReaction->setValue("date", new LibDateTime());
			$newReaction->setValue("poster", $user->getUserID());
			$newReaction->setValue("state", $state);
			$newReaction->store();

			$topicTable = new TopicTable($database);
			$topic = $topicTable->getRowByKey($this->getID());
			$topic->setValue("lastReaction", new LibDateTime());
			$topic->store();

			$boardSettings = $this->board->getBoardSettings();
			if ($boardSettings->increasePostCount()) {
				$dataFilter = new DataFilter();
				$dataFilter->addEquals("ID", $user->getUserID());
				$dataFilter->setLimit(1);
				$mutation = new DataMutation();
				$mutation->addToColumn("posts", 1);
				$userTable = new UserTable($database);
				$userTable->executeDataMutations($mutation, $dataFilter);
			}
			global $TBBboardList;
			$TBBboardList->updateBoardCache($this->board->getID());

			return $newReaction->getValue("ID");
		}

		function editReaction($postID, &$user, $state) {
			if (!$this->board->canWrite($user)) return false;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$reactionTable = new ReactionTable($database);
			$reaction = $reactionTable->getRowByKey($postID);
			if ($reaction->getValue("topicID") != $this->getID()) return false;
			$reaction->setValue("lastChange", new LibDateTime());
			$reaction->setValue("changeBy", $user->getUserID());
			$reaction->setValue("state", $state);
			$reaction->store();
			return true;
		}

		function isSticky() {
			$data = $this->privateVars['dbData'];
			$special = $data->getValue("special");
			return ($special == 'sticky') ? true : false;
		}

		function isNormal() {
			$data = $this->privateVars['dbData'];
			$special = $data->getValue("special");
			return ($special == 'no') ? true : false;
		}

		function isAnnouncement() {
			$data = $this->privateVars['dbData'];
			$special = $data->getValue("special");
			return ($special == 'announcement') ? true : false;
		}

		function setSpecial($state) {
			$data = $this->privateVars['dbData'];
			$data->setValue("special", $state);
			$data->store();
			return true;
		}

		function setLocked($state) {
			$data = $this->privateVars['dbData'];
			$data->setValue("closed", $state);
			$data->store();
			return true;
		}

		function editTopicNameIcon($name, $iconID) {
			$data = $this->privateVars['dbData'];
			$data->setValue("title", $name);
			$data->setValue("icon", $iconID);
			$data->store();
			return true;
		}

		function moveToBoard($boardID) {
			$data = $this->privateVars['dbData'];
			$data->setValue("boardID", $boardID);
			$data->store();
			return true;
		}

		function delete() {
			global $TBBcurrentUser;
			if (!$TBBcurrentUser->isAdministrator()) return false;
			return $this->board->deleteTopic($this->getID());
		}
	}

	class Board {

		var $privateVars;

		function Board() {
			$this->privateVars['subBoards'] = array();
			$this->privateVars['readSubBoards'] = false;
		}

		function p_setDBdata(&$data) {
			$this->privateVars['dbData'] = $data;
		}

		function p_getDBdata() {
			return $this->privateVars['dbData'];
		}

		function p_hasDBdata() {
			return isSet($this->privateVars['dbData']);
		}

		function setParent($newParent) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$boardBean = $this->p_getDBdata();

			$oldParent = $boardBean->getValue("parentID");
			$oldOrder = $boardBean->getValue("order");
			// Update the childs of the old position to move up
			$boardTable = new BoardTable($database);
			$rowFilter = new DataFilter();
			$rowFilter->addGreaterThan('order', $oldOrder);
			$rowFilter->addEquals('parentID', $oldParent);
			$mutations = new DataMutation();
			$mutations->subtractFromColumn('order', 1);
			$boardTable->executeDataMutations($mutations, $rowFilter);

			$rowFilter = new DataFilter();
			$rowFilter->addEquals("parentID", $newParent);
			$columnSorting = new ColumnSorting();
			$columnSorting->addColumnSort("order", false);
			$boardTable->selectRows($rowFilter, $columnSorting);
			$order = 0;
			if ($latestBoard = $boardTable->getRow()) {
				$order = $latestBoard->getValue("order") + 1;
			}
			$boardBean->setValue("parentID", $newParent);
			$boardBean->setValue("order", $order);
			$boardBean->store();

			global $TBBboardList;
			$TBBboardList->updateStructureCache();

		}

		function moveAllTopics(&$newBoard) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$topicTable = new TopicTable($database);
			$dataMutation = new DataMutation();
			$dataMutation->setEquals("boardID", $newBoard->getID());

			$filter = new DataFilter();
			$filter->addEquals("boardID", $this->getID());

			$topicTable->executeDataMutations($dataMutation, $filter);
		}

		function moveAllSubboards(&$newBoard) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$boardTable = new BoardTable($database);
			$dataMutation = new DataMutation();
			$dataMutation->setEquals("parentID", $newBoard->getID());

			$filter = new DataFilter();
			$filter->addEquals("parentID", $this->getID());

			$boardTable->executeDataMutations($dataMutation, $filter);
			global $TBBboardList;
			$TBBboardList->updateStructureCache();
		}

		function removeAll() {
			// since this is an important task, check if the user is an administrator!
			global $TBBconfiguration;
			global $TBBcurrentUser;
			if (!$TBBcurrentUser->isAdministrator()) return false;
			if ($this->deletesPermanent()) {
				$this->p_removeSubBoards($this);
				global $TBBboardList;
				$TBBboardList->updateStructureCache();
			} else {
				$this->setParent($TBBconfiguration->getBinBoardID());
			}
			return true;
		}

		function removeSubBoards() {
			global $TBBconfiguration;
			global $TBBcurrentUser;
			if (!$TBBcurrentUser->isAdministrator()) return false;

			$database = $TBBconfiguration->getDatabase();
			$subBoards = $this->getSubboards();
			for ($i = 0; $i < count($subBoards); $i++) {
				$subBoard = $subBoards[$i];
				if ($this->deletesPermanent()) {
					$this->p_removeSubBoards($subBoard);
				} else {
					$subBoard->setParent($TBBconfiguration->getBinBoardID());
				}
			}
			global $TBBboardList;
			$TBBboardList->updateStructureCache();
			return true;
		}

		function p_removeSubBoards(&$board) {
			global $TBBconfiguration;
			global $TBBcurrentUser;
			if (!$TBBcurrentUser->isAdministrator()) return false;

			$database = $TBBconfiguration->getDatabase();
			$subBoards = $board->getSubboards();
			for ($i = 0; $i < count($subBoards); $i++) {
				$subBoard = $subBoards[$i];
				$this->p_removeSubBoards($subBoard);
			}
			// remove the topics
			$board->clear();
			// remove the board
			$boardTable = new BoardTable($database);

			// Move other boards up
			$rowFilter = new DataFilter();
			$rowFilter->addGreaterThan('order', $board->getPosition());
			$rowFilter->addEquals('parentID', $board->getParentID());
			$mutations = new DataMutation();
			$mutations->subtractFromColumn('order', 1);
			$boardTable->executeDataMutations($mutations, $rowFilter);

			$filter = new DataFilter();
			$filter->addEquals("ID", $board->getID());
			$boardTable->deleteRows($filter);
		}

		function getName() {
			if ($this->getID() == 0) { // the root
				return 'Overzicht';
			}
			if (!$this->p_hasDBdata()) return "Naamloos";
			$data = $this->p_getDBdata();
			return $data->getValue("name");
		}

		function getComment() {
			if ($this->getID() == 0) { // the root
				return '';
			}
			if (!$this->p_hasDBdata()) return "";
			$data = $this->p_getDBdata();
			return $data->getValue("comment");
		}

		function getNrUnreadTopics() {
			global $TBBboardList;
			global $TBBcurrentUser;
			return $TBBboardList->getNrUnreadBoardTopics($this->getID(), $TBBcurrentUser);
		}

		function getNrUnreadReactions() {
			global $TBBboardList;
			global $TBBcurrentUser;
			return $TBBboardList->getNrUnreadBoardReactions($this->getID(), $TBBcurrentUser);
		}

		function incView() {
			if ($this->getID() == 0) return;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$boardTable = new BoardTable($database);
	 		$filter = new DataFilter();
	 		$filter->addEquals('ID', $this->getID());
			$mutation = new DataMutation();
			$mutation->addToColumn('views', 1);
	 		$boardTable->executeDataMutations($mutation, $filter);
		}

		function allowTopics() {
			if ($this->getID() == 0) return false;
			$profile = $this->getBoardSettings();
			$modus = $profile->getViewModusRaw();
			return !(($modus == 'open') || ($modus == 'openHidden'));
		}

		function getBoardSettings() {
			if (!$this->p_hasDBdata()) return false;
			global $TBBboardProfileList;
			$data = $this->p_getDBdata();
			return $TBBboardProfileList->getBoardProfile($data->getValue('settingsID'));
		}

		function getID() {
			if (!$this->p_hasDBdata()) return 0;
			$data = $this->p_getDBdata();
			return $data->getValue("ID");
		}

		function getParentID() {
			if (!$this->p_hasDBdata()) return 0;
			$data = $this->p_getDBdata();
			return $data->getValue("parentID");
		}

		function getPosition() {
			if (!$this->p_hasDBdata()) return -1;
			$data = $this->p_getDBdata();
			return $data->getValue("order");
		}

		function getParent() {
			if (!$this->p_hasDBdata()) return false;
			$data = $this->p_getDBdata();
			$parentID = $data->getValue("parentID");
			global $TBBboardList;
			$parent = $TBBboardList->getBoard($parentID);
			return $parent;
		}

		function getPostCount() {
			if (isSet($this->privateVars['postCount'])) {
				return $this->privateVars['postCount'];
			}
			global $TBBboardList;
			$boardStats = $TBBboardList->getBoardStatsCache($this->getID());
			$postCount = $boardStats['posts'];
			$this->privateVars['postCount'] = $postCount;
			return $postCount;
		}

		function getTopicCount() {
			if (isSet($this->privateVars['topicCount'])) {
				return $this->privateVars['topicCount'];
			}
			global $TBBboardList;
			$boardStats = $TBBboardList->getBoardStatsCache($this->getID());
			$topicCount = $boardStats['topics'];
			$this->privateVars['topicCount'] = $topicCount;
			return $topicCount;
		}

		function getPrunedTopicCount($daysPrune) {
			if (isSet($this->privateVars['topicCountPrune'])) {
				return $this->privateVars['topicCountPrune'];
			}
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$topicCount = 0;

			$daysPruneTime = new LibDateTime();
			$daysPruneTime->sub(LibDateTime::day(), $daysPrune);
			//strToTime("-".$daysPrune." days", time());
			$topicTable = new TopicTable($database);
			$functions = new FunctionDescriptions();
			$functions->addCount("ID", "topiccount");

			$topicFilter = new DataFilter();
			$topicFilter->addEquals("boardID", $this->getID());
			$topicFilter->addGreaterThan("lastReaction", $daysPruneTime);
			$selectResult = $topicTable->executeDataFunction($functions, $topicFilter);
			if ($rowData = $selectResult->getRow()) {
				$topicCount = $rowData['topiccount'];
			}
			$this->privateVars['topicCountPrune'] = $topicCount;
			return $topicCount;
		}

		function getLocation() {
			global $TBBboardList;
			return $TBBboardList->getBoardLocation($this->getID());
		}

		function getSubBoards() {
			if ($this->privateVars['readSubBoards']) {
				return $this->privateVars['subBoards'];
			}
			$result = array();

			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$boardTable = new BoardTable($database);
			$filter = new DataFilter();
			$filter->addEquals("parentID", $this->getID());
			$sorting = new ColumnSorting();
			$sorting->addColumnSort("order", true);
			$boardTable->selectRows($filter, $sorting);
			while($boardData = $boardTable->getRow()) {
				//$id = $boardData->getValue('ID');
				$subBoard = new Board();
				$subBoard->p_setDBdata($boardData);
				$result[] = $subBoard;
			}

			$this->privateVars['subBoards'] = $result;
			$this->privateVars['readSubBoards'] = true;
			return $result;
		}

		function addSubBoard($name, $comment, $position, $profile, $read, $write, $topic) {
			global $TBBboardList;
			$parentID = $this->getID();
			if ($position == '-2') {
				$subBoards = $this->getSubBoards();
				$lastBoard = $subBoards[count($subBoards) - 1];
				$position = $lastBoard->getID();
			}
			$TBBboardList->addSubBoard($parentID, $name, $comment, $position, $profile, $read, $write, $topic);
		}

		function canRead(&$user) {
			global $TBBboardList;
			return $TBBboardList->canReadBoard($this->getID(), $user);
		}

		function canAddTopics(&$user) {
			if (!$this->allowTopics()) return false;
			if ($this->getID() == 0) {
				return false;
			}
			if (!$this->p_hasDBdata()) return false;
			$data = $this->p_getDBdata();
			$groupID = $data->getValue("topic");
			//$groupID = $this->privateVars['dbData']['topic'];
			global $TBBmemberGroupList;
			if ($user->isActiveAdmin()) return true;

			$topicGroup = $TBBmemberGroupList->getMemberGroup($groupID);
			if (is_object($topicGroup))
				return $topicGroup->isMember($user);
			else
				return false;
		}

		function canWrite(&$user) {
			if ($this->getID() == 0) {
				return false;
			}
			if (!$this->p_hasDBdata()) return false;
			$data = $this->p_getDBdata();
			$groupID = $data->getValue("write");
			//$groupID = $this->privateVars['dbData']['write'];
			global $TBBmemberGroupList;
			if ($user->isActiveAdmin()) return true;

			$reactGroup = $TBBmemberGroupList->getMemberGroup($groupID);
			if (is_object($reactGroup))
				return $reactGroup->isMember($user);
			else
				return false;
		}

		function addTopic(&$user, $title, $icon, $plugin, $state, $closed, $special) {
			if (!$this->canAddTopics($user)) return false;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			if (strlen(trim($title)) == 0) {
				return false;
			}
			$readTime = new LibDateTime();

			$topicTable = new TopicTable($database);
			$newTopic = $topicTable->addRow();
			$newTopic->setValue("boardID", $this->getID());
			$newTopic->setValue("date", $readTime);
			$newTopic->setValue("poster", $user->getUserID());
			$newTopic->setValue("title", trim($title));
			$newTopic->setValue("icon", $icon);
			$newTopic->setValue("typeID", 100);
			$newTopic->setValue("plugin", $plugin);
			$newTopic->setValue("lastReaction", $readTime);
			$newTopic->setValue("state", $state);
			$newTopic->setValue("closed", ($closed == "yes") ? true : false);
			$newTopic->setValue("special", $special);
			$newTopic->store();

			$topicReadTable = new TopicReadTable($database);
			$topicRead = $topicReadTable->addRow();
			$topicRead->setValue("userID", $user->getUserID());
			$topicRead->setValue("topicID", $newTopic->getValue('ID'));
			$topicRead->setValue("lastRead", $readTime);
			$topicRead->store();

			$topicID = $newTopic->getValue("ID");
			$boardSettings = $this->getBoardSettings();
			if ($boardSettings->increasePostCount()) {
				$userTable = new UserTable($database);
				$rowFilter = new DataFilter();
				$rowFilter->addEquals('ID', $user->getUserID());
				$mutations = new DataMutation();
				$mutations->addToColumn('topic', 1); // Add 1 to to the column
				$userTable->executeDataMutations($mutations, $rowFilter);
			}
			global $TBBboardList;
			$TBBboardList->updateBoardCache($this->getID());
			return $topicID;
		}

		function markAsRead(&$user) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$topicTable = new TopicTable($database);
			// 1. delete all read statusses for the topics of this board
			$topicFilter = new DataFilter();
			$topicFilter->addEquals("userID", $user->getUserID());
			$boardFilter = new DataFilter();
			$boardFilter->addEquals("boardID", $this->getID());
			$topicFilter->addJoinDataFilter("topicID", "ID", $topicTable, $boardFilter);
			$topicReadTable = new TopicReadTable($database);
			$topicReadTable->deleteRows($topicFilter);

			// 2. Select all topics greater than the read threshold, and mark them as read now
			$topicReadTable = new TopicReadTable($database);
			$boardFilter->addGreaterThan("lastReaction", $user->getReadThreshold());
			$topicTable->selectRows($boardFilter, new ColumnSorting());
			while ($topicRow = $topicTable->getRow()) {
				$newRead = $topicReadTable->addRow();
				$newRead->setValue("userID", $user->getUserID());
				$newRead->setValue("topicID", $topicRow->getValue("ID"));
				$newRead->setValue("lastRead", new LibDateTime());
				$newRead->store();
			}
		}

		function readTopics($start, $count, $daysPrune) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$result = array();

			$dataFilter = new DataFilter();
			$dataFilter->addEquals("boardID", $this->getID());
			$dataFilter->addEquals("special", "no");
			//$pruneDate = strToTime("-".$daysPrune." days", time());
			$pruneDate = new LibDateTime();
			$pruneDate->sub(LibDateTime::day(), $daysPrune);
			if ($daysPrune > 0) $dataFilter->addGreaterThan("lastReaction", $pruneDate);
			$dataFilter->setLimit($count);
			$dataFilter->setOffset($start);
			$sorting = new ColumnSorting();
			$sorting->addColumnSort("lastReaction", false);

			$topicTable = new TopicTable($database);
			$topicTable->selectRows($dataFilter, $sorting);
			while ($topicData = $topicTable->getRow()) {
				$topic = new BoardTopic($topicData, $this);
				$result[] = $topic;
			}
			return $result;
		}

		function readStickyTopics() {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$result = array();

			$dataFilter = new DataFilter();
			$dataFilter->addEquals("boardID", $this->getID());
			$dataFilter->addEquals("special", "sticky");

			$sorting = new ColumnSorting();
			$sorting->addColumnSort("lastReaction", false);

			$topicTable = new TopicTable($database);
			$topicTable->selectRows($dataFilter, $sorting);
			while ($topicData = $topicTable->getRow()) {
				$topic = new BoardTopic($topicData, $this);
				$result[] = $topic;
			}
			return $result;
		}

		function getLastTopic() {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$dataFilter = new DataFilter($database);
			$dataFilter->addEquals("boardID", $this->getID());
			$dataFilter->setLimit(1);

			$sorting = new ColumnSorting();
			$sorting->addColumnSort("lastReaction", false);

			$topicTable = new TopicTable($database);
			$topicTable->selectRows($dataFilter, $sorting);
			if ($topicData = $topicTable->getRow()) {
				$topic = new BoardTopic($topicData, $this);
				return $topic;
			}
			return false;
		}

		function deletesPermanent() {
			global $TBBconfiguration;
			$binBoard = $TBBconfiguration->getBinBoardID();
			if (($binBoard == false) ||
				($binBoard == $this->getID()) ||
				($this->hasParent($binBoard))) return true;
			return false;
		}

		function hasParent($id) {
			global $TBBboardList;
			$parentID = $this->getParentID();
			if ($parentID == $id) return true;
			while ($parentID !== 0) {
				$subBoard = $TBBboardList->getBoard($parentID);
				$parentID = $subBoard->getParentID();
				if ($parentID == $id) return true;
			}
			return false;
		}

		function deleteTopic($id) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			global $TBBModuleManager;

			if ($this->deletesPermanent()) {
				$topicTable = new TopicTable($database);
				$topicData = $topicTable->getRowByKey($id);
				$moduleID = $topicData->getValue("plugin");
				$topicModule = $TBBModuleManager->getPlugin($moduleID, "topic");
				$topicModule->deleteTopic($id);

				// Delete reactions
				$reactionTable = new ReactionTable($database);
				$reactionFilter = new DataFilter();
				$reactionFilter->addEquals("topicID", $id);
				$reactionTable->deleteRows($reactionFilter);

				// Delete read information
				$topicReadTable = new TopicReadTable($database);
				$topicReadTable->deleteRows($reactionFilter);

				// Delete topic
				$topicData->delete();
			} else {
				$this->moveTopics(array($id), $TBBconfiguration->getBinBoardID(), false);
			}
			global $TBBboardList;
			$TBBboardList->updateBoardCache($this->board->getID());
			return true;
		}

		function closeTopics($idArray, $closed) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$topicTable = new TopicTable($database);
			$dataFilter = new DataFilter();
			for ($i = 0; $i < count($idArray); $i++)
				$dataFilter->addEquals("ID", $idArray[$i]);
			$dataFilter->setMode("or");

			$mutations = new DataMutation();
			$mutations->setEquals('closed', $closed);
			$topicTable->executeDataMutations($mutations, $dataFilter);
		}

		function stickyTopics($idArray, $value) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$topicTable = new TopicTable($database);
			$dataFilter = new DataFilter();
			for ($i = 0; $i < count($idArray); $i++)
				$dataFilter->addEquals("ID", $idArray[$i]);
			$dataFilter->setMode("or");

			$mutations = new DataMutation();
			$mutations->setEquals('special', $value);
			$topicTable->executeDataMutations($mutations, $dataFilter);
		}

		function deleteTopics($idArray) {
			global $TBBcurrentUser;
			global $TBBconfiguration;
			if (!$TBBcurrentUser->isAdministrator()) return false;
			if ($this->deletesPermanent()) {
				for ($i = 0; $i < count($idArray); $i++) {
					if (!$this->deleteTopic($idArray[$i])) return false;
				}
			} else {
				$this->moveTopics($idArray, $TBBconfiguration->getBinBoardID(), false);
			}
			return true;
		}

		function clear() {
			global $TBBcurrentUser;
			if (!$TBBcurrentUser->isAdministrator()) return false;

			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$topicTable = new TopicTable($database);
			$filter = new DataFilter();
			$filter->addEquals("boardID", $this->getID());
			$topicTable->selectRows($filter, new ColumnSorting());
			while ($topicRow = $topicTable->getRow()) {
				$topicID = $topicRow->getValue("ID");
				if (!$this->deleteTopic($topicID)) return false;
			}
			return true;
		}


		function moveTopics($idArray, $newBoardID, $leaveTrails = false) {
			global $TBBcurrentUser;
			if (!$TBBcurrentUser->isAdministrator()) return false;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$dataFilter = new DataFilter($database);
			$dataFilter->setMode("or");
			for ($i = 0; $i < count($idArray); $i++) {
				$dataFilter->addEquals("ID", $idArray[$i]);
			}
			$topicTable = new TopicTable($database);
			$topicTable->selectRows($dataFilter, new ColumnSorting());
			if ($leaveTrails) {
				$topicModule = $TBBconfiguration->getTopicModule($TBBconfiguration->getReferenceID());
			}
			while ($topicData = $topicTable->getRow()) {
				$topic = new BoardTopic($topicData, $this);
				if ($leaveTrails) {
					$topicModule->createReferenceOfTopic($topic);
				}
				$topic->moveToBoard($newBoardID);
			}
			global $TBBboardList;
			$TBBboardList->updateBoardCache($this->getID());
			$TBBboardList->updateBoardCache($newBoardID);
			return true;
		}

	}

	class BoardList {

		var $privateVars;

		function BoardList() {
			$this->privateVars['cacheID'] = array();
			$this->privateVars['structureCache'] = array();
			$this->privateVars['listCache'] = array();
			$this->privateVars['boardCache'] = array();
			$this->privateVars['structureCacheRead'] = false;
		}

		function addSubBoard($parentID, $name, $comment, $position, $profile, $read, $write, $topic) {
			global $TBBconfiguration;
			// Set the position stuff
			$database = $TBBconfiguration->getDatabase();
			$boardTable = new BoardTable($database);
			$newPos = 0;
			if ($position > 0) {
				$board = $this->getBoard($position);
				if (!is_object($board)) return false;
				$newPos = $board->getPosition() + 1;

				$dataMutation = new DataMutation();
				$dataMutation->addToColumn("order", 1);
				$filter = new DataFilter();
				$filter->addEquals("parentID", $parentID);
				$filter->addGreaterThan("order", $newPos-1);
				$boardTable->executeDataMutations($dataMutation, $filter);
			} else {
				$dataMutation = new DataMutation();
				$dataMutation->addToColumn("order", 1);
				$filter = new DataFilter();
				$filter->addEquals("parentID", $parentID);
				$boardTable->executeDataMutations($dataMutation, $filter);
			}
			$newBoard = $boardTable->addRow();
			$newBoard->setValue("parentID", $parentID);
			$newBoard->setValue("name", $name);
			$newBoard->setValue("comment", $comment);
			$newBoard->setValue("order", $newPos);
			$newBoard->setValue("settingsID", $profile);
			$newBoard->setValue("read", $read);
			$newBoard->setValue("write", $write);
			$newBoard->setValue("topic", $topic);
			$newBoard->setValue("views", 0);
			$newBoard->store();

			$this->updateStructureCache();
		}

		function getBoard($id) {
			if (isSet($this->privateVars['cacheID'][$id])) {
				return $this->privateVars['cacheID'][$id];
			}
			if ($id == 0) {
				$overview = new Board();
				$this->privateVars['cacheID'][$id] = $overview;
				return $overview;
			}
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$boardTable = new BoardTable($database);
			$boardData = $boardTable->getRowByKey($id);
			if ($boardData !== false) {
				$board = new Board();
				$board->p_setDBdata($boardData);
				$this->privateVars['cacheID'][$board->getID()] = $board;
				return $board;
			}
			return false;
		}

		function p_updateSubboardCache($parentID) {
			$result = array();
			global $TBBconfiguration;
			global $TBBboardProfileList;
			$database = $TBBconfiguration->getDatabase();
			$boardTable = new BoardTable($database);
			$filter = new DataFilter();
			$filter->addEquals("parentID", $parentID);
			$sorting = new ColumnSorting();
			$sorting->addColumnSort("order", true);
			$boardTable->selectRows($filter, $sorting);
			while($boardData = $boardTable->getRow()) {
				$boardProfile = $TBBboardProfileList->getBoardProfile($boardData->getValue("settingsID"));
				$isHidden = $boardProfile->isHidden();
				$isOpen = $boardProfile->isOpen();

				$boardID = $boardData->getValue("ID");
				$boardName = $boardData->getValue("name");
				$boardComment = $boardData->getValue("comment");
				$boardSettings = $boardData->getValue("settingsID");
				$boardRead = $boardData->getValue("read");

				$this->privateVars['listCache'][$boardID] = array(
					"ID" => $boardID,
					"parentID" => $boardData->getValue("parentID"),
					"name" => $boardName,
					"comment" => $boardComment,
					"settingsID" => $boardSettings,
					"readGroup" => $boardRead,
					"hidden" => $isHidden,
					"open" => $isOpen
				);

				$result[] = array(
					"ID" => $boardID,
					"name" => $boardName,
					"comment" => $boardComment,
					"settingsID" => $boardSettings,
					"readGroup" => $boardRead,
					"hidden" => $isHidden,
					"open" => $isOpen,
					"childs" => $this->p_updateSubboardCache($boardID)
				);
			}
			return $result;
		}

		function updateStructureCache() {
			$this->privateVars['listCache'] = array();
			$this->privateVars['listCache'][0] = array(
				"ID" => 0,
				"parentID" => false,
				"name" => "Overzicht",
				"comment" => "",
				"settingsID" => false,
				"readGroup" => true,
				"hidden" => false,
			);

			$cache = array(
				"ID" => 0,
				"name" => "Overzicht",
				"comment" => "",
				"settingsID" => false,
				"readGroup" => true,
				"hidden" => false,
				"childs" => $this->p_updateSubboardCache(0),
			);


			$totalCache = array("structure" => $cache, "list" => $this->privateVars['listCache']);
			//print_r($totalCache);

			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$cacheTable = new StructureCacheTable($database);
			$cacheTable->deleteRows(new DataFilter());
			$newCache = $cacheTable->addRow();
			$newCache->setValue("date", new LibDateTime());
			$newCache->setValue("structureCache", serialize($totalCache));
			$newCache->store();

			$this->privateVars['structureCache'] = $cache;
			$this->privateVars['structureCacheRead'] = true;
		}

		function getStructureCache() {
			if ($this->privateVars['structureCacheRead']) {
				return $this->privateVars['structureCache'];
			}
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$cacheTable = new StructureCacheTable($database);
			$cacheTable->selectAll();
			if ($cacheRow = $cacheTable->getRow()) {
				$cache = unSerialize($cacheRow->getValue("structureCache"));
			} else { // no cache found. Build one
				$this->updateStructureCache();
				return $this->privateVars['structureCache'];
			}
			$this->privateVars['structureCache'] = $cache["structure"];
			$this->privateVars['listCache'] = $cache["list"];
			$this->privateVars['structureCacheRead'] = true;
			return $cache["structure"];
		}

		function p_searchStructureCache($structure, $parents, $targetID) {
			for ($i = 0; $i < count($structure); $i++) {
				if ($structure[$i]['ID'] == $targetID) return $structure[$i];
				if (in_array($structure[$i]['ID'], $parents))
					return $this->p_searchStructureCache($structure[$i]['childs'], $parents, $targetID);
			}
			return false;
		}

		function getBoardCache($boardID) {
			$structureCache = $this->getStructureCache();
			if ($boardID == 0) return $structureCache;
			$parentArray = array();
			$boardInfo = $this->privateVars['listCache'][$boardID];
			$searchParent = $boardInfo;
			while ($searchParent['parentID'] !== false) {
				$parentID = $searchParent['parentID'];
				$parentArray[] = $parentID;
				$searchParent = $this->privateVars['listCache'][$parentID];
			}
			return $this->p_searchStructureCache($structureCache["childs"], $parentArray, $boardID);
		}

		function updateBoardCache($boardID) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$topicCount = 0;
			$topicTable = new TopicTable($database);
			$functions = new FunctionDescriptions();
			$functions->addCount("ID", "topiccount");
			$topicFilter = new DataFilter();
			$topicFilter->addEquals("boardID", $boardID);
			$selectResult = $topicTable->executeDataFunction($functions, $topicFilter);
			if ($rowData = $selectResult->getRow()) $topicCount = $rowData['topiccount'];

			$topicTable = new TopicTable($database);
			$reactionTable = new ReactionTable($database);
			$functions = new FunctionDescriptions();
			$functions->addCount("ID", "postCount");
			$postFilter = new DataFilter();
			$topicFilter = new DataFilter();
			$topicFilter->addEquals("boardID", $boardID);
			$postFilter->addJoinDataFilter("topicID", "ID", $topicTable, $topicFilter);
			$selectResult = $reactionTable->executeDataFunction($functions, $postFilter);
			if ($rowData = $selectResult->getRow()) $postCount = $rowData['postCount'];

			$topicID = false;
			$topicTitle = false;
			$postUser = false;
			$postDate = false;

			$dataFilter = new DataFilter($database);
			$dataFilter->addEquals("boardID", $boardID);
			$dataFilter->setLimit(1);
			$sorting = new ColumnSorting();
			$sorting->addColumnSort("lastReaction", false);
			$topicTable = new TopicTable($database);
			$topicTable->selectRows($dataFilter, $sorting);
			if ($topicData = $topicTable->getRow()) {
				$topic = new BoardTopic($topicData, $this);
				$topicID = $topic->getID();
				$topicTitle = $topic->getTitle();
				$postDate = $topic->getTime();
				$user = $topic->getStarter();
				$postUser = $user->getUserID();

				$dataFilter = new DataFilter();
				$dataFilter->addEquals("topicID", $topic->getID());
				$dataFilter->setLimit(1);
				$sorting = new ColumnSorting();
				$sorting->addColumnSort("date", false);

				$reactionTable = new ReactionTable($database);
				$reactionTable->selectRows($dataFilter, $sorting);

				if ($lastPost = $reactionTable->getRow()) {
					$lastPost = new TopicReaction($lastPost, $this);
					$postDate = $lastPost->getTime();
					$user = $lastPost->getUser();
					$postUser = $user->getUserID();
				}
			}

			$cache = array(
				"ID" => $boardID,
				"posts" => $postCount,
				"topics" => $topicCount,
				"postDate" => $postDate,
				"postUser" => $postUser,
				"topicTitle" => $topicTitle,
				"topicID" => $topicID
			);

			$cacheTable = new BoardCacheTable($database);
			$boardCacheRow = $cacheTable->getRowByKey($boardID);
			if (!is_Object($boardCacheRow)) $boardCacheRow = $cacheTable->addRow();
			$boardCacheRow->setValue("ID", $boardID);
			$boardCacheRow->setValue("date", new LibDateTime());
			$boardCacheRow->setValue("posts", $postCount);
			$boardCacheRow->setValue("topics", $topicCount);
			if ($cache['postDate'] === false) $boardCacheRow->setNull('postDate');
			else $boardCacheRow->setValue("postDate", $cache['postDate']);
			if ($cache['postUser'] === false) $boardCacheRow->setNull('postUser');
			else $boardCacheRow->setValue("postUser", $cache['postUser']);
			if ($cache['topicTitle'] === false) $boardCacheRow->setNull('topicTitle');
			else $boardCacheRow->setValue("topicTitle", $cache['topicTitle']);
			if ($cache['topicID'] === false) $boardCacheRow->setNull('topicID');
			else $boardCacheRow->setValue("topicID", $cache['topicID']);

			$boardCacheRow->store();
			$this->privateVars['boardCache'][$boardID] = $cache;
		}

		function getBoardStatsCache($boardID) {
			if (isSet($this->privateVars['boardCache'][$boardID])) {
				return $this->privateVars['boardCache'][$boardID];
			}
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$cacheTable = new BoardCacheTable($database);
			$cacheFilter = new DataFilter();
			$cacheFilter->addEquals("ID", $boardID);
			$cacheTable->selectRows($cacheFilter, new ColumnSorting());
			if ($cacheRow = $cacheTable->getRow()) {
				$cache = array(
					"ID" => $cacheRow->getValue('ID'),
					"posts" => $cacheRow->getValue('posts'),
					"topics" => $cacheRow->getValue('topics'));
				if (!$cacheRow->isNull('postDate')) $cache["postDate"] = $cacheRow->getValue('postDate');
				else $cache["postDate"] = false;
				if (!$cacheRow->isNull('postUser')) $cache["postUser"] = $cacheRow->getValue('postUser');
				else $cache["postUser"] = false;
				if (!$cacheRow->isNull('topicTitle')) $cache["topicTitle"] = $cacheRow->getValue('topicTitle');
				else $cache["topicTitle"] = false;
				if (!$cacheRow->isNull('topicID')) $cache["topicID"] = $cacheRow->getValue('topicID');
				else $cache["topicID"] = false;
			} else { // no cache found. Build one
				$this->updateBoardCache($boardID);
				return $this->privateVars['boardCache'][$boardID];
			}
			$this->privateVars['boardCache'][$boardID] = $cache;
			return $cache;
		}

		function getReadableBoardIDs($boardID, &$user, $IDlist = array(), $boardCache = false) {
			if ($this->canReadBoard($boardID, $user)) $IDlist[] = $boardID;
			if ($boardCache === false) $boardCache = $this->getBoardCache($boardID);

			for ($i = 0; $i < count($boardCache['childs']); $i++) {
				$subBoard = $boardCache['childs'][$i];
				$IDlist = $this->getReadableBoardIDs($subBoard['ID'], $user, $IDlist, $subBoard);
			}
			return $IDlist;
		}

		function canReadBoard($boardID, &$user) {
			if ($boardID == 0) {
				return true;
			}
			$structureCache = $this->getStructureCache();
			$boardInfo = $this->privateVars['listCache'][$boardID];
			$groupID = $boardInfo["readGroup"];
			global $TBBmemberGroupList;
			if ($user->isActiveAdmin()) return true;

			$readGroup = $TBBmemberGroupList->getMemberGroup($groupID);
			if (is_object($readGroup)) {
				return $readGroup->isMember($user);
			} else
				return false;
		}

		function getBoardLocation($boardID) {
			$this->getStructureCache();
			global $textParser;
			global $TBBconfiguration;
			$location = new Location();
			if ($boardID == 0) { // 0 == root!
				$location->addLocation(htmlConvert($TBBconfiguration->getBoardName()), 'index.php');
				return $location;
			}
			$boardInfo = $this->privateVars['listCache'][$boardID];
			$parentID = $boardInfo['parentID'];
			if ($parentID !== false) {
				$location = $this->getBoardLocation($parentID);
				$location->addLocation(htmlConvert($boardInfo['name']), 'index.php?id='.$boardInfo['ID']);
			}
			return $location;
		}

		function getNrUnreadBoardTopics($boardID, &$user) {
			/*
			SELECT count(`tbb_topic`.`ID`)
			FROM `tbb_topicread`
			RIGHT JOIN `tbb_topic` ON `tbb_topicread`.`topicID` = `tbb_topic`.`ID`
			WHERE
			`tbb_topic`.`boardID` = 7
			AND `tbb_topic`.`lastReaction` > date_sub( now( ) , INTERVAL 1 MONTH)
			AND (
				(`tbb_topicread`.`userID` = 2
					AND `tbb_topicread`.`lastRead` < `tbb_topic`.`lastReaction`)
				OR `tbb_topicread`.`ID` IS NULL
			)
			*/
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$topicTable = new TopicTable($database);
			$topicReadTable = new TopicReadTable($database);

			// determine if the board is read by the current user
			$topicFilter = new DataFilter();
			$topicFilter->addEquals("boardID", $boardID);
			$topicFilter->addGreaterThan("lastReaction", $user->getReadThreshold());

			$unreadFilter = new DataFilter();
			$unreadFilter->addNull("ID");

			//$lastreadFilter = new DataFilter();
			//$lastreadFilter->addEquals("userID", $TBBcurrentUser->getUserID());
			//$lastreadFilter->addColumnLessThan("lastRead", "lastReaction", $topicTable);
			//$lastreadFilter->setMode("and");
			//$unreadFilter->addDataFilter($lastreadFilter);
			$unreadFilter->setMode("or");

			$joinFilter = new DataFilter();
			$joinFilter->addEquals("userID", $user->getUserID());

			$topicCountFilter = new DataFilter();
			$topicCountFilter->addDataFilter($unreadFilter);
			$topicCountFilter->addFilterJoinDataFilter("topicID", "ID", $topicTable, $topicFilter, false, $joinFilter);

			$dataFunctions = new FunctionDescriptions();
			$dataFunctions->addJoinCount("ID", $topicTable, "nrUnread");

			$resultSet = $topicReadTable->executeDataFunction($dataFunctions, $topicCountFilter);
			//print $resultSet->getQuery();
			$resultRow = $resultSet->getRow();
			$nrUnread = $resultRow['nrUnread'];

			return $nrUnread;
		}

		function getNrUnreadBoardReactions($boardID, &$user) {
			/*
				SELECT count( `tbb_reaction`.`ID` )
				FROM `tbb_topicread`
				RIGHT JOIN `tbb_topic` ON `tbb_topic`.`ID` = `tbb_topicread`.`TopicID` AND (
				`tbb_topicread`.`UserID` = '2'
				)
				LEFT JOIN `tbb_reaction` ON `tbb_topic`.`ID` = `tbb_reaction`.`topicID`
				WHERE
				(`tbb_topic`.`boardID` = '2' AND `tbb_topic`.`lastReaction` > '2003-12-09 20:12:25') AND

				(
					(`tbb_topicread`.`ID` IS NULL)
				OR
					(`tbb_topicread`.`UserID` = '2' AND `tbb_topicread`.`lastRead` < `tbb_topic`.`lastReaction`
					AND `tbb_reaction`.`date` > `tbb_topicread`.`lastread`)
				)
			*/
			global $TBBconfiguration;
			global $TBBcurrentUser;
			$database = $TBBconfiguration->getDatabase();
			$topicTable = new TopicTable($database);
			$topicReadTable = new TopicReadTable($database);
			$reactionTable = new ReactionTable($database);

			$userID = $TBBcurrentUser->getUserID();
			// determine if the board is read by the current user
			$reactionFilter = new DataFilter();
			$reactionFilter->addGreaterThan("date", $user->getReadThreshold());

			$topicFilter = new DataFilter(); // filter on topic table
			$topicFilter->addEquals("boardID", $boardID);
			$topicFilter->addGreaterThan("lastReaction", $user->getReadThreshold());
			$topicFilter->addJoinDataFilter("ID", "topicID", $reactionTable, $reactionFilter, true);

			$topicReadFilter = new DataFilter();

			$unknownTopicFilter = new DataFilter();
			$unknownTopicFilter->addNull('ID');

			$readTopicFilter = new DataFilter();
			$readTopicFilter->addEquals('userID', $userID);
			$readTopicFilter->addColumnLessThan("lastRead", "lastReaction", $topicTable);
			$readTopicFilter->addColumnLessThan("lastRead", "date", $reactionTable);

			$joinFilter = new DataFilter();
			$joinFilter->addEquals("userID", $userID);


			$topicReadFilter->setMode("or");
			//$topicReadFilter->addDataFilter($unknownTopicFilter);
			$topicReadFilter->addDataFilter($readTopicFilter);

			$dataFilter = new DataFilter();
			$dataFilter->addDataFilter($topicReadFilter);
			$dataFilter->addFilterJoinDataFilter("topicID", "ID", $topicTable, $topicFilter, false, $joinFilter);

			$dataFunctions = new FunctionDescriptions();
			$dataFunctions->addJoinCount("ID", $topicTable, "nrUnread");

			$resultSet = $topicReadTable->executeDataFunction($dataFunctions, $dataFilter);
			//print $resultSet->getQuery();
			$resultRow = $resultSet->getRow();
			$nrUnread = $resultRow['nrUnread'];
			return $nrUnread;
		}

		function removeBoard($id) {
			$board = $this->getBoard($id);
			$board->removeAll();
		}

	}


	class TopicList {

		var $privateVars;

		function TopicList() {
			$this->privateVars = array();
			$this->privateVars['cacheID']	= array();
		}

		function getTopic($id) {
			if (isSet($this->privateVars['cacheID'][$id])) {
				return $this->privateVars['cacheID'][$id];
			}
			global $TBBconfiguration;
			global $TBBboardList;

			$database = $TBBconfiguration->getDatabase();
			$topicTable = new TopicTable($database);
			if ($topicData = $topicTable->getRowByKey($id)) {
				$board = $TBBboardList->getBoard($topicData->getValue("boardID"));
				$topic = new BoardTopic($topicData, $board);
				$this->privateVars['cacheID'][$topic->getID()] = $topic;
				return $topic;
			}
			return false;
		}
	}

	$GLOBALS["TBBboardList"] = new BoardList();
	$GLOBALS["TBBtopicList"] = new TopicList();


?>
