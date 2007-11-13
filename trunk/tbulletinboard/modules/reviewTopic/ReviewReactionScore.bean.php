<?php

	class ReviewReactionScoresTable extends DataTable {
		var $privateVars;

		function ReviewReactionScoresTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "tm_reviewreactionscores");
			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->setAutoIncrement("ID");

			$this->defineInt("reactionID", "reactionID", false);
			$this->defineInt("scoreID", "scoreID", false);
			$this->defineInt("value", "value", false);
			$this->setHasIndex("reactionID");
			$this->setHasIndex("scoreID");
		}
	}

?>