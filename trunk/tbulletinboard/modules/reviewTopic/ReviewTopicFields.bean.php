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

			$this->defineIndex("topicID");
			$this->defineIndex("fieldID");
		}
	}

?>
