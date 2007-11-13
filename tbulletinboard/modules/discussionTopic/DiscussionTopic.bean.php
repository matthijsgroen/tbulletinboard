<?php

	class DiscussionTopicTable extends DataTable {
		var $privateVars;

		function DiscussionTopicTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "tm_disctopic");
			$this->defineInt("ID", "topicID", false);
			$this->setPrimaryKey("ID");
			$this->defineText("message", "message", 2000000000, false);
			$this->defineBool("signature", "signature", false);
			$this->defineBool("smileys", "smilies", false);
			$this->defineBool("parseUrls", "parseurls", false);
			$this->defineDate("lastChange", "lastChange", true);
			$this->defineInt("changeBy", "changeby", true);
		}
	}

?>