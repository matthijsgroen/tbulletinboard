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
			importClass("board.TopicIconList");

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
