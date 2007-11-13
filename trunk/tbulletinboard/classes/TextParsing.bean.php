<?php
	/**
	 * A Database Bean
	 *
	 *@package Beans
	 *@author Matthijs Groen (matthijs at ivinity.nl)
	 *@version 1.0
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
