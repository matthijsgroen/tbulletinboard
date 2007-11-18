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
	require_once($libraryClassDir."DataObjects.class.php");
	require_once($libraryClassDir."DataOrderHelper.class.php");

	/**
	 * Usefull for editing schedules
	 */
	class EmoticonTable extends DataTable {

		var $privateVars;

		function EmoticonTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "emoticons");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->defineText("name", "name", 20, false);
			$this->defineText("imgUrl", "imgUrl", 40, false);
			$this->defineText("code", "code", 40, false);
			$this->defineInt("order", "order", true);

			$this->setEventHandler(new EmoticonEventListener());
		}
		
		function addRow() {
			return new EmoticonRow($this);
		}
	}
	
	class EmoticonRow extends DataRow {
		
		function EmoticonRow(&$table) {
			$this->DataRow($table);
		}
	}	
	
	class EmoticonEventListener extends DataEventListener {
		
		function beforeRowDelete(&$row) {
			$dataOrderHelper = new DataOrderHelper($row->getTable(), "name", "order");
			$dataOrderHelper->removeOrder($row);
		}
		
		function beforeRowInsert(&$row) {
			$dataOrderHelper = new DataOrderHelper($row->getTable(), "name", "order");
			$dataOrderHelper->setNewOrder($row, "bottom");
		}
	}

/*
ID 				bigint(20) 	UNSIGNED	No 	 	auto_increment
name 			varchar(20)	 	No
imgUrl	 	varchar(40)	 	No
code 			varchar(40)	 	No
*/

?>
