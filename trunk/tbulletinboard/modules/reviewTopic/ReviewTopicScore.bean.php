<?php

	class ReviewTopicScoresTable extends DataTable {
		var $privateVars;

		function ReviewTopicScoresTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "tm_reviewtopicscores");
			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->setAutoIncrement("ID");
			$this->defineInt("topicID", "topicID", false);
			$this->defineInt("scoreID", "scoreID", false);
			$this->defineInt("value", "value", false);
			$this->setHasIndex("topicID");
			$this->setHasIndex("scoreID");
		}
	}

?>