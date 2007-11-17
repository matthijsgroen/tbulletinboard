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

	/**
	 *
	 */
	global $ivLibDir;
	require_once($ivLibDir."DataObjects.class.php");
	require_once($TBBclassDir."BoardTags.bean.php");

	/**
	 * Usefull for editing schedules
	 */
	class TextParsingTable extends DataTable {

		var $privateVars;

		function TextParsingTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "textparsing");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineBool("active", "active", false);
			$this->defineDefaultValue("active", false);
			$this->defineText("startName", "startName", 20, false);
			$this->defineBool("acceptAll", "acceptAll", false);
			$this->defineText("acceptedParameters", "acceptedParameters", 255, false);
			$this->defineText("endTags", "endTags", 50, false);
			$this->defineBool("endTagRequired", "endTagRequired", false);
			$this->defineText("htmlReplace", "htmlReplace", 5000, false);
			$this->defineText("allowParents", "allowParents", 255, false);
			$this->defineText("allowChilds", "allowChilds", 255, false);
			$this->defineText("description", "description", 255, false);
			$this->defineText("example", "example", 5000, false);
			$this->defineEnum("wordBreaks", "wordBreaks", array(0 => "all", 1 => "none", 2 => "parameter", 3 => "text"), false);

			$this->setEventHandler(new TextParseEventHandler());
		}
	}

	class TextParseEventHandler extends DataEventListener {

		function beforeRowDelete(&$row) {
			// if a tag gets deleted, some other tables also need to be cleaned up
			// - ScheduleRules
			// - ScheduleShifts
			// - SchedulePlanning
			$table = $row->getTable();
			$database = $table->getDatabase();

			$boardTagsTable = new BoardTagsTable($database);
			$dataFilter = new DataFilter();
			$dataFilter->addEquals("tagID", $row->getValue("ID"));
			$boardTagsTable->deleteRows($dataFilter);

			/*
			$deleteQuery = sprintf(
				"DELETE FROM %sboardtags WHERE `tagID`='%s'",
				$TBBconfiguration->tablePrefix,
				addSlashes($tagID)
				);
			*/
		}
	}


?>
