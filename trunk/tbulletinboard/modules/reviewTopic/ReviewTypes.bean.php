<?php
	
	global $ivLibDir;
	require_once($ivLibDir."DataObjects.class.php");

	class ReviewTypesTable extends DataTable {
		var $privateVars;

		function ReviewTypesTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "tm_reviewtypes");
			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->setAutoIncrement("ID");
			$this->defineText("name", "name", 40, false);
			$this->defineText("prefix", "prefix", 10, true);
			$this->defineText("postfix", "postfix", 10, true);
			$this->defineFloat("maxValue", "maxValue", false);
		}
	}

?>