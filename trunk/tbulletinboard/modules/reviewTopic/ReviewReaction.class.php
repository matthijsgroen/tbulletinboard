<?php


	class ReviewReaction extends TopicReaction {

		function ReviewReaction($reactionData, $reviewData, &$topic) {
			$this->TopicReaction($reactionData, $topic);
			$this->privateVars['reviewData'] = $reviewData;
		}

		function hasIcon() {
			$data = $this->privateVars['reviewData'];
			return ($data->getValue("icon") != 0) ? true : false;
		}

		function getIconInfo() {
			$data = $this->privateVars['reviewData'];
			$iconID = $data->getValue("icon");
			global $TBBtopicIconList;
			$iconInfo = $TBBtopicIconList->getIconInfo($iconID);
			return $iconInfo;
		}

		function getIconID() {
			$data = $this->privateVars['reviewData'];
			return $data->getValue("icon");
		}

		function getTitle() {
			$data = $this->privateVars['reviewData'];
			return $data->getValue("title");
		}

		function getMessage() {
			$data = $this->privateVars['reviewData'];
			return $data->getValue("message");
		}

		function smiliesOn() {
			$data = $this->privateVars['reviewData'];
			return $data->getValue("smileys");
		}

		function isReview() {
			$data = $this->privateVars['reviewData'];
			return ($data->getValue("replyType") == "review");
		}

		function getScore() {
			$data = $this->privateVars['reviewData'];
			return $data->getValue("score");
		}

		function hasSignature() {
			$data = $this->privateVars['reviewData'];
			return $data->getValue("signature");
		}
	}

?>
