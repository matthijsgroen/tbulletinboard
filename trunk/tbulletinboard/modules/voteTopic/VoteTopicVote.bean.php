<?php

	class VoteTopicVoteTable extends DataTable {
		var $privateVars;

		function VoteTopicVoteTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "tm_votevote");
			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineInt("userID", "userID", false);
			$this->defineInt("topicID", "topicID", false);
			$this->defineBool("vote", "vote", false);
		}
	}

?>
