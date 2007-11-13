<?php

	class VoteTopicTable extends DataTable {
		var $privateVars;

		function VoteTopicTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "tm_votetopic");
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
