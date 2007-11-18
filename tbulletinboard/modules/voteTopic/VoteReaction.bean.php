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
