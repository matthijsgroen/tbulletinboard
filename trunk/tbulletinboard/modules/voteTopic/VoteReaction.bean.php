<?php

	class VoteReactionTable extends DataTable {
		var $privateVars;

		function VoteReactionTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "tm_votereaction");
			$this->defineInt("ID", "reactionID", false);
			$this->setPrimaryKey("ID");
			$this->defineInt("icon", "icon", false);
			$this->defineText("title", "title", 80, false);
			$this->defineText("message", "message", 2000000000, false);
			$this->defineBool("signature", "signature", false);
			$this->defineBool("smileys", "smilies", false);
			$this->defineBool("parseUrls", "parseurls", false);
			
			$this->privateVars["voteCache"] = array();
		}
		
		function getVoteForUserAndTopic($userID, $topicID) {
			if (isSet($this->privateVars["voteCache"][$topicID])) {
				if (isSet($this->privateVars["voteCache"][$topicID][$userID])) 
					return $this->privateVars["voteCache"][$topicID][$userID];
			}
			$voteTable = new VoteTopicVoteTable($this->getDatabase());
			
			$filter = new DataFilter();
			$filter->addEquals("userID", $userID);
			$filter->addEquals("topicID", $topicID);
			$voteTable->selectRows($filter, new ColumnSorting());
			if ($voted = $voteTable->getRow()) {
				$result = $voted->getValue("vote") ? "pro" : "con";
			} else $result = "none";
			if (!isSet($this->privateVars["voteCache"][$topicID])) {
				$this->privateVars["voteCache"][$topicID] = array();
			}
			$this->privateVars["voteCache"][$topicID][$userID] = $result;
			return $result;			
		}
	}

?>
