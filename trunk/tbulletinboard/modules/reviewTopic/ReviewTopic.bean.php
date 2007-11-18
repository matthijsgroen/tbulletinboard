<?php
	/**
	 *	TBB2, an highly configurable and dynamic bulletin board
	 *	Copyright (C) 2007  Matthijs Groen
	 *
	 *	This program is free software: you can redistribute it and/or modify
	 *	it under the terms of the GNU General Public License as published by
	 *	the Free Software Foundation, either version 3 of the License, or
	 *	(at your option) any later version.
	 *	
	 *	This program is distributed in the hope that it will be useful,
	 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
	 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 *	GNU General Public License for more details.
	 *	
	 *	You should have received a copy of the GNU General Public License
	 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
	 *	
	 */


	class ReviewTopicTable extends DataTable {
		var $privateVars;

		function ReviewTopicTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "tm_reviewtopic");
			$this->defineInt("ID", "topicID", false);
			$this->setPrimaryKey("ID");
			$this->defineText("message", "message", 2000000, false);
			$this->defineBool("signature", "signature", false);
			$this->defineDefaultValue("signature", true);
			$this->defineBool("smileys", "smilies", false);
			$this->defineDefaultValue("smileys", true);
			$this->defineBool("parseUrls", "parseurls", false);
			$this->defineDefaultValue("parseUrls", true);
			$this->defineDate("lastChange", "lastChange", true);
			$this->defineInt("changeBy", "changeby", true);
			$this->defineInt("reviewType", "reviewType", false);
			$this->defineIndex("reviewType");
			$this->defineFloat("score", "score", false);
			$this->defineFloat("userScore", "userScore", true);
		}
	}

?>
