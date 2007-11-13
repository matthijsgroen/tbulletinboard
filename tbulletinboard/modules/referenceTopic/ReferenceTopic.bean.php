<?php

	class ReferenceTopicTable extends DataTable {
		var $privateVars;

		function ReferenceTopicTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "tm_referencetopic");
			$this->defineInt("ID", "topicID", false);
			$this->setPrimaryKey("ID");

			$this->defineEnum("type", "type", array("topic", "board", "url"), false);
			$this->defineDefaultValue("type", "topic");
			$this->defineBool("newWindow", "newWindow");
			$this->defineDefaultValue("newWindow", false);
			$this->defineText("value", "value", 255, false);
			$this->defineEnum("created", "created", array("user", "system"), false);
			$this->defineDefaultValue("created", "user");
		}
	}

?>