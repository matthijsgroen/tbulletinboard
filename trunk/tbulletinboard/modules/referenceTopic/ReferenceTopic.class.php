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


	class ReferenceTopic extends BoardTopic {

		var $discVars;

		function ReferenceTopic(&$topic) {
			$this->BoardTopic($topic->privateVars['dbData'], $topic->board);
			$this->discVars = array();
			$this->p_readDBdata();
		}

		function p_readDBdata() {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$topicTable = new ReferenceTopicTable($database);
			$topicData = $topicTable->getRowByKey($this->getID());
			$this->discVars['dbData'] = $topicData;
		}

		function getType() {
			$data = $this->discVars['dbData'];
			return $data->getValue("type");
		}

		function getNewWindow() {
			$data = $this->discVars['dbData'];
			return $data->getValue("newWindow");
		}

		function getValue() {
			$data = $this->discVars['dbData'];
			return $data->getValue("value");
		}

		function getCreated() {
			$data = $this->discVars['dbData'];
			return $data->getValue("created");
		}

	}

?>
