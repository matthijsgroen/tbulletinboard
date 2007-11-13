<?php

	class DiscussionTopic extends BoardTopic {

		var $discVars;
		var $plugin;

		function DiscussionTopic(&$topic, &$plugin) {
			$this->BoardTopic($topic->privateVars['dbData'], $topic->board);
			$this->plugin = $plugin;
			$this->discVars = array();
			$this->p_readDBdata();
		}

		function p_readDBdata() {
			require_once($this->plugin->getModuleDir(). 'DiscussionTopic.bean.php');

			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$topicTable = new DiscussionTopicTable($database);
			$topicData = $topicTable->getRowByKey($this->getID());
			$this->discVars['dbData'] = $topicData;
		}

		function getTopicText() {
			$data = $this->discVars['dbData'];
			return $data->getValue("message");
		}

		function getReactions($start, $count) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			require_once($this->plugin->getModuleDir(). 'DiscussionReaction.bean.php');

			$reactionTable = new ReactionTable($database);
			$discReactionTable = new DiscussionReactionTable($database);

			$dataFilter = new DataFilter();
			$dataFilter->addEquals("topicID", $this->getID());
			$dataFilter->addJoinDataFilter("ID", "ID", $discReactionTable, new DataFilter());
			$dataFilter->setLimit($count);
			$dataFilter->setOffset($start);

			$sorting = new ColumnSorting();
			$sorting->addColumnSort("date", true);
			$joinedResult = $database->selectMultiTableRows(
				array($reactionTable, $discReactionTable),
				$dataFilter, $sorting
			);
			$result = array();
			while ($reactionInfo = $joinedResult->getJoinedRow()) {
				require_once($this->plugin->getModuleDir(). 'DiscussionReaction.class.php');

				$reactionData = $joinedResult->extractRow($reactionTable, $reactionInfo);
				$discussionData = $joinedResult->extractRow($discReactionTable, $reactionInfo);
				$reactionObj = new DiscussionReaction($reactionData, $discussionData, $this);
				$result[] = $reactionObj;
			}
			return $result;
		}

		function getReaction($reactionID) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$reactionTable = new ReactionTable($database);
			$discReactionTable = new DiscussionReactionTable($database);

			$dataFilter = new DataFilter();
			$dataFilter->addEquals("ID", $reactionID);
			$dataFilter->addJoinDataFilter("ID", "ID", $discReactionTable, new DataFilter());

			$sorting = new ColumnSorting();
			$sorting->addColumnSort("date", true);
			$joinedResult = $database->selectMultiTableRows(
				array($reactionTable, $discReactionTable),
				$dataFilter, $sorting
			);
			if ($reactionInfo = $joinedResult->getJoinedRow()) {
				$reactionData = $joinedResult->extractRow($reactionTable, $reactionInfo);
				$discussionData = $joinedResult->extractRow($discReactionTable, $reactionInfo);
				$reactionObj = new DiscussionReaction($reactionData, $discussionData, $this);
				return $reactionObj;
			}
			return false;
		}

		function smiliesOn() {
			$data = $this->discVars['dbData'];
			return $data->getValue("smileys");
		}

		function isEdited() {
			$data = $this->discVars['dbData'];
			return !$data->isNull("lastChange");
		}

		function editedBy() {
			global $TBBuserManagement;
			$data = $this->discVars['dbData'];
			$userID = $data->getValue("changeBy");
			return $TBBuserManagement->getUserByID($userID);
		}

		function lastChange() {
			$data = $this->discVars['dbData'];
			return $data->getValue("lastChange");
		}

		function hasSignature() {
			$data = $this->discVars['dbData'];
			return $data->getValue("signature");
		}

	}

?>
