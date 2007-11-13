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
	require_once($ivLibDir."DataOrderHelper.class.php");

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
