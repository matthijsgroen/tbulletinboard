<?php

	class ReviewReactionTable extends DataTable {
		var $privateVars;

		function ReviewReactionTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "tm_reviewreaction");
			$this->defineInt("ID", "reactionID", false);
			$this->setPrimaryKey("ID");
			$this->defineInt("icon", "icon", false);
			$this->defineText("title", "title", 80, true);
			$this->defineText("message", "message", 2000000, false);

			$this->defineBool("signature", "signature", false);
			$this->defineDefaultValue("signature", true);
			$this->defineBool("smileys", "smilies", false);
			$this->defineDefaultValue("smileys", true);
			$this->defineBool("parseUrls", "parseurls", false);
			$this->defineDefaultValue("parseUrls", true);

			$this->defineFloat("score", "score", true);
			$this->defineEnum("replyType", "replyType", array( 1 => "comment", 2 => "review"), false);
			$this->defineDefaultValue("replyType", "comment");

		}
	}

?>