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
	require_once($ivLibDir."DataObjects.class.php");
	require_once($TBBclassDir."BoardTopics.bean.php");
	require_once($TBBclassDir."BoardTags.bean.php");

	/**
	 * Usefull for editing schedules
	 */
	class BoardSettingsTable extends DataTable {

		var $privateVars;

		function BoardSettingsTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "boardsettings");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineEnum("viewMode", "viewmode", array(0 => "open", 1 => "hidden", 2 => "standard", 3 => "openHidden"), false);
			$this->defineEnum("secLevel", "seclevel", array(0 => "low", 1 => "medium", 2 => "high", 3 => "none"), false);
			$this->defineText("name", "name", 50, false);

			$this->defineBool("incCount", "inc_count");
			$this->defineBool("signatures", "signatures");
			$this->setEventHandler(new BoardSettingsListener());
		}
	}

	class BoardSettingsListener extends DataEventListener {

		function afterRowDelete(&$row) {
			$table = $row->getTable();
			$database = $table->getDatabase();

			$filter = new DataFilter();
			$filter->addEquals("settingID", $row->getValue("ID"));

			$boardTagsTable = new BoardTagsTable($database);
			$boardTagsTable->deleteRows($filter);

			$boardTopicsTable = new BoardTopicsTable($database);
			$boardTopicsTable->deleteRows($filter);
		}


	}

?>
