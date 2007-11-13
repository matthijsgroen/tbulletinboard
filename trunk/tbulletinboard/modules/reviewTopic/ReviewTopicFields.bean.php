<?php

	class ReviewTopicFieldsTable extends DataTable {
		var $privateVars;

		function ReviewTopicFieldsTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "tm_reviewtopicfields");
			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->setAutoIncrement("ID");
			$this->defineInt("topicID", "topicID", false);
			$this->defineInt("fieldID", "fieldID", false);
			$this->defineInt("intValue", "intValue", true);
			$this->defineText("textValue", "textValue", 40, true);
			$this->defineFloat("floatValue", "floatValue", true);
			$this->defineDate("dateValue", "dateValue", true);
			$this->defineTime("timeValue", "timeValue", true);

			$this->setHasIndex("topicID");
			$this->setHasIndex("fieldID");
		}
	}

?>