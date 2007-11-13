<?php

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
