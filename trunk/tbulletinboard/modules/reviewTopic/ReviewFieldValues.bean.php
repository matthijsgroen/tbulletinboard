<?php

	class ReviewFieldValuesTable extends DataTable {
		var $privateVars;

		function ReviewFieldValuesTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "tm_reviewfieldvalues");
			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->setAutoIncrement("ID");
			$this->defineInt("fieldID", "fieldID", false);
			$this->setHasIndex("fieldID");
			$this->defineText("value", "value", 40, false);
		}
	}

?>