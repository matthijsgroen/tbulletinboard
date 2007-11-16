<?php

	class ReviewTopicTable extends DataTable {
		var $privateVars;

		function ReviewTopicTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "tm_reviewtopic");
			$this->defineInt("ID", "topicID", false);
			$this->setPrimaryKey("ID");
			$this->defineText("message", "message", 2000000, false);
			$this->defineBool("signature", "signature", false);
			$this->defineDefaultValue("signature", true);
			$this->defineBool("smileys", "smilies", false);
			$this->defineDefaultValue("smileys", true);
			$this->defineBool("parseUrls", "parseurls", false);
			$this->defineDefaultValue("parseUrls", true);
			$this->defineDate("lastChange", "lastChange", true);
			$this->defineInt("changeBy", "changeby", true);
			$this->defineInt("reviewType", "reviewType", false);
			$this->defineIndex("reviewType");
			$this->defineFloat("score", "score", false);
			$this->defineFloat("userScore", "userScore", true);
		}
	}

?>
