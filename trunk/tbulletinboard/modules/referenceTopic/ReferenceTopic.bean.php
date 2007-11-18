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
