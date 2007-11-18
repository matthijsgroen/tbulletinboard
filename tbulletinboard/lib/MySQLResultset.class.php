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
	 * The superclass Resultset
	 */
	require_once($libraryClassDir."Resultset.class.php");
	require_once($libraryClassDir."LibDateTime.class.php");

	/**
	 * Resultset is a class that represent a database result from a selectquery
	 */
	class MySQLResultset extends Resultset {

		/**
		 * Instantiates a Database object
		 *@param string $result the resultset from a query
		 */
		function MySQLResultset($resultset, &$database, $query) {
			$this->Resultset($resultset, $database, $query);
			$this->privateVars['affectedrows'] = mysql_affected_rows();
		}

		/**
		 * Returns the number of rows this resultset contains
		 *@return int the number of rows
		 */
		function getNumRows() {
			if($this->privateVars['resultset']) {
				if(@mysql_num_rows($this->privateVars['resultset']))
					return mysql_num_rows($this->privateVars['resultset']);
			}
			return -1;
		}

		/**
		 * Moves the cursor of the resultset to the specified row
		 *@param int $rowNumber the number of row to move the cursor to
		 *@return bool true if succesfull, false otherwise
		 */
		function moveCursor($rowNumber) {
			if($this->privateVars['resultset']) {
				$result = mysql_data_seek($this->privateVars['resultset'], $rowNumber);
				if ($result === true) $this->privateVars['resultpointer'] = $rowNumber;
				return $result;
			}
			return false;
		}

		/**
		 * Returns the number of rows affected by the executed query
		 *@return int the number of afftected rows
		 */
		function getNumAffectedRows() {
			return($this->privateVars['affectedrows']);
		}

		/**
		 * Return the pointed row and increases the pointer
		 *@return array the collection of data
		 */
		function getRow() {
			if($this->privateVars['resultset']) {
				$resultArray = mysql_fetch_array($this->privateVars['resultset']);
				if ($resultArray !== false) $this->privateVars['resultpointer']++;
				return $resultArray;
			}
			return false;
		}

		/**
		 * Return the pointed row and increases the pointer
		 *@return array the collection of data with only columnnumbers
		 */
		function getJoinedRow($withAssociatives = false) {
			if($this->privateVars['resultset']) {
				if($withAssociatives)
					$resultRow = mysql_fetch_array($this->privateVars['resultset']);
				else
					$resultRow = mysql_fetch_row($this->privateVars['resultset']);
				if ($resultRow !== false) $this->privateVars['resultpointer']++;
				return $resultRow;
			}
			return false;
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
			$row = $table->addRow();
			//$row->setLanguage($this->getLanguage());
			reset($rowData);
			$nr = $table->getColumnCount();
			for ($i = 0; $i < $nr; $i++) {
				$index = $i + $startIndex;
				$val = $rowData[$index];
				if ($val !== null) {
					//$val = stripSlashes($val);
					$alias = $table->getAlias($i);
					if ($alias !== false) {
						$type = $table->getColumnType($alias);

						// Convert the value of the database to the correct PHP format
						switch($type) {
							case 'date': // 2003-09-23 21:04:23
								list($date, $time) = explode(" ", $val);
								list($year, $month, $dayOfMonth) = explode("-", $date);
								list($hour, $minute, $second) = explode(":", $time);
								$dateTime = new LibDateTime($hour, $minute, $second, $month, $dayOfMonth, $year);
								$row->setValue($alias, $dateTime);
								//$row->setValue($alias, strToTime($val));
								break;
							case 'time': // 21:04:23
								list($hour, $minute, $second) = explode(":", $val);
								$dateTime = new LibDateTime($hour, $minute, $second);
								$row->setValue($alias, $dateTime);
								//$row->setValue($alias, strToTime($val));
								break;
							case 'bool':
								$row->setValue($alias, ($val == "yes") ? true : false);
								break;
							case 'enum':
								$row->setValue($alias, $table->getEnumAliasValue($alias, $val));
								break;
							default:
								$row->setValue($alias, $val);
								break;
						}
					}
				}
			}
			$row->setStored(); // tell that the data is in the database
			return $row;
		}

		function clear() {
			if (is_resource($this->privateVars['resultset']))
				mysql_free_result($this->privateVars['resultset']);
		}

	}

?>
