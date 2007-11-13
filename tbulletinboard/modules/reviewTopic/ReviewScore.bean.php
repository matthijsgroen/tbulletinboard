<?php
	
	global $ivLibDir;
	require_once($ivLibDir."DataObjects.class.php");

	class ReviewScoreTable extends DataTable {
		var $privateVars;

		function ReviewScoreTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "tm_reviewscores");
			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->setAutoIncrement("ID");
			$this->defineInt("reviewType", "reviewType", false);
			$this->setHasIndex("reviewType");
			$this->defineText("name", "name", 40, false);
			$this->defineInt("maxScore", "maxScore", false);
		}
	}

?>