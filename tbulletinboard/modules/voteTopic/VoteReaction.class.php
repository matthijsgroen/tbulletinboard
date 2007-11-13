<?php

	class VoteReaction extends TopicReaction {

		function VoteReaction($reactionData, $discussData, &$topic) {
			$this->TopicReaction($reactionData, $topic);
			$this->privateVars['discData'] = $discussData;
		}

		function hasIcon() {
			$data = $this->privateVars['discData'];
			return ($data->getValue("icon") != 0) ? true : false;
		}

		function getIconInfo() {
			global $TBBclassDir;
			require_once($TBBclassDir.'TopicIconList.class.php');

			$data = $this->privateVars['discData'];
			$iconID = $data->getValue("icon");
			$iconInfo = $GLOBALS['TBBtopicIconList']->getIconInfo($iconID);
			return $iconInfo;
		}

		function getIconID() {
			$data = $this->privateVars['discData'];
			return $data->getValue("icon");
		}

		function getTitle() {
			$data = $this->privateVars['discData'];
			return $data->getValue("title");
		}

		function getMessage() {
			$data = $this->privateVars['discData'];
			return $data->getValue("message");
		}

		function smiliesOn() {
			$data = $this->privateVars['discData'];
			return $data->getValue("smileys");
		}

		function hasSignature() {
			$data = $this->privateVars['discData'];
			return $data->getValue("signature");
		}
		
		function getVote() {
			$data = $this->privateVars['discData'];
			return $data->getTable()->getVoteForUserAndTopic($this->getUser()->getUserID(), $this->topic->getID());
		}
	}


?>
