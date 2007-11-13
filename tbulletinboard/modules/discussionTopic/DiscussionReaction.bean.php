<?php

	class DiscussionReactionTable extends DataTable {
		var $privateVars;

		function DiscussionReactionTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "tm_discreaction");
			$this->defineInt("ID", "reactionID", false);
			$this->setPrimaryKey("ID");
			$this->defineInt("icon", "icon", false);
			$this->defineText("title", "title", 80, false);
			$this->defineText("message", "message", 2000000000, false);
			$this->defineBool("signature", "signature", false);
			$this->defineBool("smileys", "smilies", false);
			$this->defineBool("parseUrls", "parseurls", false);
		}
	}

?>