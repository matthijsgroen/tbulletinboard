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

	importClass("orm.DataObjects");
	importBean("board.BoardTopics");
	importBean("board.BoardTags");

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
