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
	 * Resultset is a class that represent a database result from a selectquery
	 */
	class Resultset {


		/**
		 * Private vars for this object
		 *@var Array $privateVars
		 */
		var $privateVars;

		/**
		 * Instantiates a Database object
		 *@param string $result the resultset from a query
		 */
		function Resultset($resultset, &$database, $query) {
			$this->privateVars = array();
			$this->privateVars['database'] = $database;
			$this->privateVars['query'] = $query;
			$this->privateVars['resultset'] = $resultset;
			$this->privateVars['table'] = null;
			$this->privateVars['tables'] = array();
			$this->privateVars['resultpointer'] = 0;
		}

		/**
		 * Returns the number of rows this resultset contains
		 *@return int the number of rows
		 */
		function getNumRows() {
		}

		/**
		 * Moves the cursor of the resultset to the specified row
		 *@param int $rowNumber the number of row to move the cursor to
		 *@return bool true if succesfull, false otherwise
		 */
		function moveCursor($rowNumber) {
		}

		/**
		 * Returns the numbers of rows affected by the executed query
		 *@return int the number of affected rows
		 */
		function getNumAffectedRows() {
		}

		/**
		 * Return the pointed row and increases the pointer
		 *@return array the collection of data
		 */
		function getRow() {
		}

		/**
		 * Return the pointed row and increases the pointer
		 *@return array the collection of data with only columnnumbers
		 */
		function getJoinedRow() {
		}

		/**
		 * Returns the database used for the executed query
		 *@return Database the databse used for the query
		 */
		function getDatabase() {
			return $this->privateVars['database'];
		}

		/**
		 * Sets the id of the last insert query
		 *@param string $id the id of the last inserted row
		 */
		function setInsertID($id) {
			$this->privateVars['insertID'] = $id;
		}

		/**
		 * Returns the insert ID of this query
		 *@return mixed the value of the last inserted ID
		 */
		function getInsertID() {
			return $this->privateVars['insertID'];
		}

		/**
		 * The executed query
		 *@return string the query executed
		 */
		function getQuery() {
			return $this->privateVars['query'];
		}

		/**
		 * Define that the results are of the given table
		 *@param DataTable $table the table this result is from
		 */
		function setTable(&$table) {
			$this->privateVars['table'] = $table;
		}

		/**
		 * Define that the results are of the given tables
		 *@param array $tables a list of DataTables that are selected
		 */
		function setTables($tables) {
			$this->privateVars['tables'] = $tables;
		}

		/**
		 * Creates a Row object from the next available Rowdata in the
		 * resultset
		 *@return DataRow the rowdata in object form, or FALSE if an error occures
		 */
		function createRow() {
			$table = $this->getTable();
			if (!is_Object($table)) return false;
			$rowData = $this->getJoinedRow();
			if ($rowData === false) return false;
			return $this->createRowJoined($table, 0, $rowData);
		}

		/**
		 * Create a row using a joined tablerow and extracting its data into
		 * a DataRow of the given DataTable. The extraction will start at column startIndex
		 *@param DataTable $table the datatable to extract a row in
		 *@param int $startIndex the startindex of in the row where the data of the given table starts
		 *@param array an array containing all row data, retrieved using getJoinedRow
		 *@return DataRow the row containing fields of the given table
		 */
		function createRowJoined(&$table, $startIndex, $rowData) {
			return false;
		}

		/**
		 * Returns the table this resultset is from
		 *@return DataTable the table of this resultset, null if not defined
		 */
		function getTable() {
			return $this->privateVars['table'];
		}

		/**
		 * Extract a DataRow of the given table using the rowinfo
		 *@param DataTable $table table to get a row from in this mixed resultset.
		 *  The table must be in the selected list (using Database->selectMultiTableRows
		 *@param array $rowInfo a row retrieved with getJoinedRow()
		 *@return DataRow a row of the specified table
		 */
		function extractRow(&$table, $rowInfo) {
			$columnIndex = 0;
			for ($i = 0; $i < count($this->privateVars['tables']); $i++) {
				$tableCheck = $this->privateVars['tables'][$i];
				if ($tableCheck->getTableName() != $table->getTableName()) {
					$columnIndex += $tableCheck->getColumnCount();
				} else break;
			}
			return $this->createRowJoined($table, $columnIndex, $rowInfo);
		}

	}
?>
