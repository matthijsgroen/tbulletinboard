<?php

	global $ivLibDir;
	require_once($ivLibDir."DataObjects.class.php");

	class ReviewFieldsTable extends DataTable {
		var $privateVars;

		function ReviewFieldsTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "tm_reviewfields");
			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->setAutoIncrement("ID");
			$this->defineInt("reviewType", "reviewType", false);
			$this->setHasIndex("reviewType");
			$this->defineText("name", "name", 20, false);
			$this->defineText("prefix", "prefix", 10, false);
			$this->defineText("postfix", "postfix", 10, false);
			$this->defineEnum("type", "type", array( 1 => "text", 2 => "number", 3 => "select", 4 => "float", 5 => "time", 6 => "date"), false);
		}
	}

?>