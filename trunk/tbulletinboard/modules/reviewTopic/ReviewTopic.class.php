<?php

	class ReviewTopic extends BoardTopic {

		var $discVars;

		function ReviewTopic(&$topic) {
			$this->BoardTopic($topic->privateVars['dbData'], $topic->board);
			$this->discVars = array();
			$this->p_readDBdata();
		}

		function p_readDBdata() {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$topicTable = new ReviewTopicTable($database);
			$topicData = $topicTable->getRowByKey($this->getID());
			$this->discVars['dbData'] = $topicData;
		}

		function getTopicText() {
			$data = $this->discVars['dbData'];
			return $data->getValue("message");
		}

		function getScore() {
			$data = $this->discVars['dbData'];
			return $data->getValue("score");
		}

		function getReviewType() {
			$data = $this->discVars['dbData'];
			return $data->getValue("reviewType");
		}

		function getReactions($start, $count) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$reactionTable = new ReactionTable($database);
			$reviewReactionTable = new ReviewReactionTable($database);

			$dataFilter = new DataFilter();
			$dataFilter->addEquals("topicID", $this->getID());
			$dataFilter->addJoinDataFilter("ID", "ID", $reviewReactionTable, new DataFilter());
			$dataFilter->setLimit($count);
			$dataFilter->setOffset($start);

			$sorting = new ColumnSorting();
			$sorting->addColumnSort("date", true);
			$joinedResult = $database->selectMultiTableRows(
				array($reactionTable, $reviewReactionTable),
				$dataFilter, $sorting
			);
			$result = array();
			while ($reactionInfo = $joinedResult->getJoinedRow()) {
				$reactionData = $joinedResult->extractRow($reactionTable, $reactionInfo);
				$reviewData = $joinedResult->extractRow($reviewReactionTable, $reactionInfo);
				$reactionObj = new ReviewReaction($reactionData, $reviewData, $this);
				$result[] = $reactionObj;
			}
			return $result;
		}
/*
		function getReaction($reactionID) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$reactionTable = new ReactionTable($database);
			$discReactionTable = new ReviewReactionTable($database);

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
*/
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
