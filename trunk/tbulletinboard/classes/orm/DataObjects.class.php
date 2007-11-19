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
	 *
	 * This file contains all sorts of objects for manipulating data
	 * from the database.
	 *
	 * HowTo:
	 *
	 * - Select a row:
	 * $row = $table->getRowByKey(12);
	 *
	 * - Insert a row:
	 * $newRow = $table->addRow();<br />
	 * $newRow->setValue('myColumnAlias', 'MyValue');<br />
	 * $newRow->store();<br />
	 *
	 * - Update a row:
	 * $myRow = $table->getRowByKey(12);<br />
	 * $myRow->setValue('myColumnAlias', 'MyNewValue');<br />
	 * $myRow->store();<br />
	 *
	 * - Delete a row:
	 * $myRow = $table->getRowByKey(12);<br />
	 * $myRow->delete();<br />
	 *
	 * - Select and browse multiple rows
	 * $rowFilter = new DataFilter();<br />
	 * $rowFilter->addEquals('myColumnAlias', 'myValue');<br />
	 * $columnSorting = new ColumnSorting();<br />
	 * $columnSorting->addColumnSort('myColumnToSortOnAlias', true);<br />
	 * $table->selectRows($rowFilter, $columnSorting);<br />
	 * while($row = $table->getRow()) {<br />
	 *  -code here-<br />
	 * }<br />
	 *
	 * - Delete multiple rows
	 * $rowFilter = new DataFilter();<br />
	 * $rowFilter->addEquals('myColumnAlias', 'myValue');<br />
	 * $table->deleteRows($rowFilter);<br />
	 *
	 * - Update multiple rows
	 * $rowFilter = new DataFilter();<br />
	 * $rowFilter->addEquals('myColumnAlias', 'myValue');<br />
	 * $mutations = new DataMutation();<br />
	 * $mutations->addToColumn('myColumnAlias', 10); // Add 10 to to the column<br />
	 * $table->executeDataMutations($mutations, $rowFilter);<br />
	 *
	 * - Run database functions (COUNT, SUM)
	 * $functions = new FunctionDescriptions();<br />
	 * $functions->addSum('myColumnAlias', 'resultAlias');<br />
	 * $rowFilter = new DataFilter();<br />
	 * $rowFilter->addEquals('myColumnAlias', 'myValue');<br />
	 * $resultSet = $table->executeDataFunction($functions, $rowFilter);<br />
	 * $resultRow = $resultSet->getRow();<br />
	 * $summedColumnValue = $resultRow['resultAlias'];<br />
	 *
	 *@package Database
	 *@author Matthijs Groen (matthijs at ivinity.nl)
	 *@version 1.2
	 *@copyright (c) 2003 IVinity
	 */

	define("max_limit", 2147483647); // max value of a signed 32 bit int

	define("select_function_substring", "substring");
	define("select_function_absolute", "absolute");
	define("select_function_minus", "minus");
	define("select_function_concat", "concat");

	define("datafilter_mode_or", "or");
	define("datafilter_mode_and", "and");

	define("db_function_search_any", "any");
	define("db_function_search_all", "all");
	define("db_function_search_exact", "exact");
	define("db_function_search_expression", "expression");

	define("union_type_all", "all");
	define("union_type_distinct", "distinct");

	/**
	 * Meta class with database table information.
	 *
	 * This class contains information about a database table. Like the title,
	 * the database it is in and column name and datatype information.
	 * Text - data gets stored as text
	 * Bool - data gets stored as boolean
	 * Int - data gets stored as number
	 * Date - data gets stored as linux timestamp
	 * Time - data gets stored as timestamp, but only the time part is used
	 * Float - data gets stored as float
	 *@author Matthijs Groen (matthijs at ivinity.nl)
	 *@version 1.3
	 */
	class DataTable {

		/**
		 * A set of private vars of this class.
		 * The known variables are:
		 * - database (reference to the database object of this table)
		 * - metaData (array containing column info of this table)
		 * - metaDataAlias (metadata stored on column alias for fast searching)
		 * - primKey (alias of the primary key);
		 * - name (name of the table)
		 * - resultset (active resultset for table browsing)
		 *@var array $privateVars
		 */
		var $privateVars;

		/**
		 * Creates a table gateway for the table with the specified name from the specified database
		 *@param Database $database the database where this table resides
		 *@param string $name the name of the database table
		 */
		function DataTable(&$database, $name) {
			$this->privateVars = array(
				"database" => &$database,
				"metaData" => array(),
				"metaDataAlias" => array(),
				"primKey" => null,
				"name" => $name,
				"resultSet" => null,
				"defaultValues" => array(),
				"autoIncColumns" => array(),
				"indexColumns" => array(),
				"rowCaching" => false,
				"rowCache" => array(),
				"blobs" => array(),
				"isMultiLanguage" => false,
				"multiLanguageFields" => array()
			);
		}

		/**
		 * Control if this class should cache datarows. (default is off)
		 *@param bool $caching true if caching should be on, false if caching should be off
		 */
		function setRowCaching($caching = true) {
			$this->privateVars['rowCaching'] = $caching;
		}

		/**
		 * Sets the eventhandler that handles the data events
		 *@param DataEventListener $eventHandler
		 */
		function setEventHandler(&$eventHandler) {
			$this->privateVars['eventhandler'] = $eventHandler;
		}

		/**
		 * Returns the database where this table resides
		 *@return Database the database where this table resides
		 */
		function &getDatabase() {
			return $this->privateVars['database'];
		}

		/**
		 * Returns the table name
		 *@return string the tablename
		 */
		function getTableName() {
			return $this->privateVars["name"];
		}

		/**
		 * Sets the column alias of the primary key
		 *@param string $alias the alias of the primary column
		 */
		function setPrimaryKey($alias) {
			$this->privateVars["primKey"] = $alias;
		}

		/**
		 * Returns the alias of the primary key column
		 *@return string Alias of the primary key column
		 */
		function getPrimaryKey() {
			return $this->privateVars["primKey"];
		}

		/**
		 * Retuns wether the column with given alias is autoincrementing or not
		 *@return bool true if autoincrement, false otherwise
		 */
		function isAutoIncrement($alias) {
			$autoIncArray = $this->privateVars["autoIncColumns"];
			return in_array($alias, $autoIncArray);
		}

		/**
		 * Declares a column to be auto incrementing
		 *@param string $alias alias of the column to be autoincrementing
		 */
		function setAutoIncrement($alias) {
			$this->privateVars["autoIncColumns"][] = $alias;
		}

		function hasMultiLanguageFields() {
			foreach($this->privateVars["metaData"] as $metaField) {
				if ($metaField['type'] == 'multiLanguageText') return true;
				if ($metaField['type'] == 'multiLanguageInt') return true;

			}
			return false;
		}

		/**
		 * Declares a column to have an index
		 *@param string $alias alias of the column to have an index
		 *@deprecated use defineIndex instead
		 */
		function setHasIndex($alias) {
			trigger_error("setHasIndex is deprecated, use defineIndex instead in (".$this->getTableName().")", E_USER_NOTICE);
			$this->defineIndex($alias);
		}

		/**
		 * Declares a column to have an index
		 *@param string $alias alias of the column to have an index
		 *@param string $indexType the type of index (normal, text)
		 */
		function defineIndex($alias, $indexType="normal") {
			$this->privateVars["indexColumns"][$alias] = array("name" => $alias, "type"=> $indexType);
		}

		function getIndexedColumns($indexType = false) {
			$result = array();
			foreach($this->privateVars['indexColumns'] as $alias => $indexInfo) {
				if ($indexType === false) $result[] = $alias;
				else if ($indexInfo["type"] == $indexType) {
					$result[] = $alias;
				}
			}
			return $result;
		}

		function hasIndex($alias) {
			return array_key_exists($alias, $this->privateVars["indexColumns"]);
		}

		/**
		 * Define the default value for the column with the given alias
		 *@param string $alias column alias to define the value for
		 *@param mixed $value the value for the column, in the appropriate data format
		 */
		function defineDefaultValue($alias, $value) {
			$this->privateVars['defaultValues'][$alias] = $value;
		}

		function hasDefaultValue($alias) {
			return isSet($this->privateVars['defaultValues'][$alias]);
		}

		function getDefaultValue($alias) {
			return $this->privateVars['defaultValues'][$alias];
		}

		/**
		 * Returns wether the column allows null values or not
		 *@param string $alias the alias of the column
		 *@return true if the column allows null values, false otherwise
		 */
		function allowNull($alias) {
			if ((!$this->hasField($alias)) && (!$this->isBlob($alias))) return false;
			if (!$this->isBlob($alias)) {
				return $this->privateVars['metaDataAlias'][$alias]['null'];
			} else {
				return $this->privateVars['blobsAlias'][$alias]['null'];
			}
		}

		/**
		 * Defines a Text column in the table
		 *@param string $alias alias of this column for reference
		 *@param string $name the actual columnname as in the database
		 *@param int $length the maximum length of the text
		 *@param bool $isNull true if null values are accepted, false otherwise
		 */
		function defineText($alias, $name, $length, $isNull) {
			$this->p_addField($alias, $name, $length, "text", $isNull);
		}

		/**
		 * Defines a MultiLanguageText column in the table
		 *@param string $alias alias of this column for reference
		 *@param string $name the actual columnname as in the database
		 *@param int $length the maximum length of the text
		 *@param bool $isNull true if null values are accepted, false otherwise
		 */
		function defineMultiLanguageText($alias, $name, $length, $isNull) {
			$this->p_addField($alias, $name, $length, "multiLanguageText", $isNull);
			$this->privateVars['multiLanguageFields'][] = $alias;
			$this->privateVars['isMultiLanguage'] = true;
		}

		/**
		 * Defines a MultiLanguageInt column in the table
		 *@param string $alias alias of this column for reference
		 *@param string $name the actual columnname as in the database
		 *@param bool $isNull true if null values are accepted, false otherwise
		 */
		function defineMultiLanguageInt($alias, $name, $isNull) {
			$this->p_addField($alias, $name, 0, "multiLanguageInt", $isNull);
			$this->privateVars['multiLanguageFields'][] = $alias;
			$this->privateVars['isMultiLanguage'] = true;
		}

		function isMultiLanguage() {
			return $this->privateVars['isMultiLanguage'];
		}

		function getMultiLanguageFields() {
			return $this->privateVars['multiLanguageFields'];
		}

		/**
		 * Defines a boolean column.
		 *@param string $alias alias of this column for reference
		 *@param string $name the actual columnname as in the database
		 */
		function defineBool($alias, $name, $isNull = false) {
			$this->p_addField($alias, $name, 0, "bool", $isNull);
		}

		/**
		 * Defines a blob column.
		 *@param string $alias alias of this column for reference
		 *@param string $name the actual columnname as in the database
		 *@param bool $isNull true if null values are accepted, false otherwise
		 */
		function defineBlob($alias, $name, $isNull) {
			$this->p_addField($alias, $name, 0, "blob", $isNull);
		}

		/**
		 * Defines a integer column.
		 *@param string $alias alias of this column for reference
		 *@param string $name the actual columnname as in the database
		 *@param bool $isNull true if null values are accepted, false otherwise
		 */
		function defineInt($alias, $name, $isNull) {
			$this->p_addField($alias, $name, 0, "int", $isNull);
		}

		/**
		 * Defines a floating point column.
		 *@param string $alias alias of this column for reference
		 *@param string $name the actual columnname as in the database
		 *@param bool $isNull true if null values are accepted, false otherwise
		 */
		function defineFloat($alias, $name, $isNull) {
			$this->p_addField($alias, $name, 0, "float", $isNull);
		}

		/**
		 * Defines a date column.
		 *@param string $alias alias of this column for reference
		 *@param string $name the actual columnname as in the database
		 *@param bool $isNull true if null values are accepted, false otherwise
		 */
		function defineDate($alias, $name, $isNull) {
			$this->p_addField($alias, $name, 0, "date", $isNull);
		}

		/**
		 * Defines a time column.
		 *@param string $alias alias of this column for reference
		 *@param string $name the actual columnname as in the database
		 *@param bool $isNull true if null values are accepted, false otherwise
		 */
		function defineTime($alias, $name, $isNull) {
			$this->p_addField($alias, $name, 0, "time", $isNull);
		}

		/**
		 * Defines an enumeration column
		 *@param string $alias alias of this column for reference
		 *@param string $name the actual columnname as in the database
		 *@param array $values an array containing the enum values
		 *@param bool $isNull true if null values are accepted, false otherwise
		 */
		function defineEnum($alias, $name, $values, $isNull) {
			$this->p_addField($alias, $name, $values, "enum", $isNull);
		}

		/**
		 * Gets the int value of an enumAlias. This is for databases that
		 * don't support enumerations. Then the int value can be used.
		 *@param string $alias alias of this column for reference
		 *@param string $aliasValue the alias of the enum value
		 *@return int the numeric value of the enumvalue
		 */
		function getEnumIntValue($alias, $aliasValue) {
			if (!$this->hasField($alias)) return false;
			$fieldInfo = $this->privateVars['metaDataAlias'][$alias];
			$enumArray = $fieldInfo['length'];
			reset($enumArray);
			while (list($key, $val) = each($enumArray)) {
			   if ($aliasValue == $val) return $key;
			}
			return false;
		}

		/**
		 * Gets the int value of an enumAlias. This is for databases that
		 * don't support enumerations. Then the int value can be used.
		 *@param string $alias alias of this column for reference
		 *@param mixed $value the datase value of the enum (int or string)
		 *@return string the alias of the given enum value, false if not found
		 */
		function getEnumAliasValue($alias, $value) {
			if (!$this->hasField($alias)) return false;
			$fieldInfo = $this->privateVars['metaDataAlias'][$alias];
			$enumArray = $fieldInfo['length'];
			if (is_numeric($value)) {
				if (isSet($enumArray[$value])) return $enumArray[$value];
			} else {
				if (isSet($enumArray[$value])) return $enumArray[$value];
				/*
				reset($enumArray);
				while (list($key, $val) = each($enumArray)) {
					 if ($value == $val) return $key;
				}
				*/
				if (in_array($value, $enumArray)) return $value;
			}
			return false;
		}

		/**
		 * Gets the int value of an enumAlias. This is for databases that
		 * do support enumerations. Then the string value can be used.
		 *@param string $alias alias of this column for reference
		 *@param string $aliasValue the alias of the enum value
		 *@return string the text value of the enumvalue
		 */
		function getEnumStringValue($alias, $aliasValue) {
			if (!$this->hasField($alias)) return false;
			$fieldInfo = $this->privateVars['metaDataAlias'][$alias];
			$enumArray = $fieldInfo['length'];
			/*
			if (isSet($enumArray[$aliasValue])) {
				$value = $enumArray[$aliasValue];
				return $value;
			}
			*/
			reset($enumArray);
			while (list($key, $val) = each($enumArray)) {
				 if ($aliasValue == $val) return $key;
			}
			return $aliasValue;
		}

		/**
		 * Internal private function for defining columns
		 *@access private
		 *@param string $alias alias of this column for reference
		 *@param string $name the actual columnname as in the database
		 *@param bool $isNull true if null values are accepted, false otherwise
		 */
		function p_addField($alias, $name, $length, $type, $isNull) {
			$field = array(
				"alias" => $alias,
				"name" => $name,
				"length" => $length,
				"type" => $type,
				"null" => $isNull
			);
			if ($type == 'blob') {
				$this->privateVars["blobs"][] = $field;
				$this->privateVars["blobsAlias"][$alias] = $field;
			}
			else {
				$this->privateVars["metaDataAlias"][$alias] = $field;
				$this->privateVars["metaData"][] = $field;
			}
		}

		function renameField($alias, $newAlias, $newName, $changeInDatabase = false) {
			if ($this->hasField($alias)) {
				$oldName = $this->getColumnName($alias);
				if ($this->isBlob($alias)) {
					$oldFieldInfo = $this->privateVars['blobsAlias'][$alias];
					$oldFieldInfo['alias'] = $newAlias;
					$oldFieldInfo['name'] = $newName;
					$this->privateVars['blobsAlias'][$newAlias] = $oldFieldInfo;
					unset($this->privateVars['blobsAlias'][$alias]);
					for ($i = 0; $i < count($this->privateVars['blobs']); $i++) {
						if ($this->privateVars['blobs'][$i]['alias'] == $alias) {
							$this->privateVars['blobs'][$i]['alias'] = $newAlias;
							$this->privateVars['blobs'][$i]['name'] = $newName;
						}
					}
				} else {
					if(isSet($this->privateVars['metaDataAlias'][$alias])) {
						$oldFieldInfo = $this->privateVars['metaDataAlias'][$alias];
						$oldFieldInfo['alias'] = $newAlias;
						$oldFieldInfo['name'] = $newName;
						$this->privateVars['metaDataAlias'][$newAlias] = $oldFieldInfo;
						unset($this->privateVars['metaDataAlias'][$alias]);
						for ($i = 0; $i < count($this->privateVars['metaData']); $i++) {
							if ($this->privateVars['metaData'][$i]['alias'] == $alias) {
								$this->privateVars['metaData'][$i]['alias'] = $newAlias;
								$this->privateVars['metaData'][$i]['name'] = $newName;
							}
						}
					}
				}
			} else {
				$oldName = $alias;
			}

			if ($changeInDatabase) {
				$this->columnRename($oldName, $newAlias);
			}
		}

		function isBlob($alias) {
			return isSet($this->privateVars["blobsAlias"][$alias]);
		}


		/**
		 * Checks if a field is defined with the given alias
		 *@param string $alias column alias
		 *@return bool true if the column is defined, false otherwise
		 */
		function hasField($alias) {
			if ($this->isBlob($alias)) return true;
			return isSet($this->privateVars['metaDataAlias'][$alias]);
		}

		/**
		 * Adds a row to this table.
		 * Use the store function of the returned row to store the data in the database
		 *@return DataRow a new row for this table
		 */
		function addRow() {
			return new DataRow($this);
		}

		/**
		 * Get the database columnname for the column with the given alias
		 *@param string $alias column alias
		 *@return string the database table columnname
		 */
		function getColumnName($alias) {
			if(isset($this->privateVars["metaDataAlias"][$alias]))
				return $this->privateVars["metaDataAlias"][$alias]["name"];
			else if ($this->isBlob($alias)) {
				return $this->privateVars["blobsAlias"][$alias]['name'];
				/*
				for($var = 0; $var < count($this->privateVars["blobs"]); $var++) {
					if(isset($this->privateVars["blobs"][$var]['alias'][$alias]))
						return $this->privateVars["blobs"][$var]['name'];
				}
				*/
			}
			return "";
		}

		/**
		 * Inserts the given row as a record in the database
		 * For regular use, addRow() and $row->store() are recommended
		 *@param DataRow the row to insert
		 *@return Resultset the resultset.
		 * Returns false if an error occurs
		 */
		function insertRow(&$row) {
			$database = $this->privateVars["database"];
			return $database->insertRow($this, $row);
		}

		/**
		 * Updates the given row in the database
		 * For regular use, $row->setValue() and $row->store() are recommended
		 *@param DataRow the row to insert
		 *@return Resultset the resultset.
		 * Returns false if an error occurs
		 */
		function updateRow(&$row) {
			$database = $this->privateVars["database"];
			return $database->updateRow($this, $row);
		}

		/**
		 * Removes the given row from the database
		 * For regular use, $row->delete() is recommended
		 *@param DataRow the row to delete
		 *@return Resultset the resultset.
		 * Returns false if an error occurs
		 */
		function deleteRow(&$row) {
			$database = $this->privateVars["database"];
			// delete functionality is already in the DB, so use that
			$filter = new DataFilter();
			$filter->addEquals($this->getPrimaryKey(), $row->getValue($this->getPrimaryKey()));
			$result = $database->deleteRows($this, $filter);
			if ($result && $this->isMultiLanguage()) {
				$filter = new DataFilter();
				$filter->addEquals("recordID", $row->getValue($this->getPrimaryKey()));
				$table = $row->getTable();
				$filter->addEquals("table", $table->getTableName());
				$mlTable = new MultiLanguageTable($this->getDatabase());
				$mlTable->deleteRows($filter);
			}
			return $result;
		}

		function deleteRowByKey($key) {
			$database = $this->privateVars["database"];
			// delete functionality is already in the DB, so use that
			//$filter = new DataFilter();
			//$filter->addEquals($this->getPrimaryKey(), $key);

			// Database CACHE CHECK
			if($GLOBALS['databaseCache']->hasCache($this->getTableName(),$key)) $row = $GLOBALS['databaseCache']->getCache($this->getTableName(),$key);
			else $row = $this->getRowByKey($key);

			if (is_Object($row)) {
				return $row->delete();
			}
			return true;
			//return $database->deleteRows($this, $filter);
		}

		/**
		 * returns the number of defined columns
		 *@return int the number of defined columns
		 */
		function getColumnCount() {
			return count($this->privateVars["metaData"]);
		}

		function getBlobColumnCount() {
			return count($this->privateVars["blobs"]);
		}

		/**
		 * Moves the cursor of the resultset to the specified row
		 *@param int $rowNumber the number of row to move the cursor to
		 *@return bool true if succesfull, false otherwise
		 */
		function moveCursor($rowNumber) {
			$resultSet = $this->privateVars['resultSet'];
			if (is_Object($resultSet) && $resultSet->getNumRows() > 0) {
				return $this->privateVars['resultSet']->moveCursor($rowNumber);
			}
			return false;
		}

		/**
		 * Returns the type of column for alias.
		 *
		 * Values that are valid are:
		 * - text (text column)
		 * - bool (boolean column)
		 * - int (number/int column)
		 * - date (date column)
		 *@return string the column type for the given alias
		 * Returns false if the alias was not found/declared.
		 */
		function getColumnType($alias) {
			if (!$this->hasField($alias)) return false;
			if ($this->isBlob($alias)) return "blob";
			return $this->privateVars['metaDataAlias'][$alias]['type'];
		}

		/**
		 * Returns the length of column for alias.
		 *@return string the column length for the given alias
		 * Returns false if the alias was not found/declared.
		 */
		function getColumnLength($alias) {
			if (!$this->hasField($alias)) return false;
			return $this->privateVars['metaDataAlias'][$alias]['length'];
		}

		function getEnumDatabaseStrings($alias) {
			if (!$this->hasField($alias)) return false;
			$type = $this->getColumnType($alias);
			if ($type === "enum") {
				$enumArray = $this->getColumnLength($alias);
				$valueArray = array();

				foreach($enumArray as $key => $val) {
					if (is_Numeric($key)) {
						$valueArray[] = $val;
					} else {
						$valueArray[] = $key;
					}
				}
				return $valueArray;
			}
			if ($type === "bool") {
				return array("yes", "no");
			}
			return false;
		}

		/**
		 * Returns the column alias for the given column index.
		 *@param int $index the column index, starting at 0
		 *@return string the alias for the given column index.
		 * Returns false if the column index was not found
		 */
		function getAlias($index) {
			if (isSet($this->privateVars["metaData"][$index]))
				return $this->privateVars["metaData"][$index]["alias"];
			return false;
		}

		function getBlobAlias($index) {
			if (isSet($this->privateVars["blobs"][$index]))
				return $this->privateVars["blobs"][$index]["alias"];
			return false;
		}

		/**
		 * Checks if this table definition has a specific alias defined
		 *@param string $alias the alias to check for existance
		 *@return bool true if the alias was found, false otherwise.
		 */
		function hasAlias($alias) {
			return isSet($this->privateVars['metaDataAlias'][$alias]);
		}

		/**
		 * Returns the column alias for the column with the given name
		 *@param string $columnName the name of the database column
		 *@return string name of the column. Returns false if there was no colum with the given name declared
		 */
		function getAliasForName($columnName) {
			for ($i = 0; $i < count($this->privateVars['metaData']); $i++) {
				$metaData = $this->privateVars['metaData'][$i];
				if ($metaData['name'] == $columnName) {
					return $metaData['alias'];
				}
			}
			return false;
		}

		/**
		 * Selects all rows from this table for displaying in tables
		 * use getRow() for browsing the resultset
		 */
		function selectAll() {
			$filter = new DataFilter();
			$sorting = new ColumnSorting();
			$this->selectRows($filter, $sorting);
		}

		/**
		 * Select rows from this table using a filter and sorting settings
		 * use getRow() for browsing the resultset
		 *@param DataFilter the data requirements
		 *@param ColumnSorting the sort settings of the data
		 */
		function selectRows(&$filter, &$columnSorting) {
			$database = $this->privateVars["database"];
			if (is_Object($this->privateVars['resultSet'])) {
				$this->privateVars['resultSet']->clear();
			}
			$this->privateVars['resultSet']	= $database->selectRows($this, $filter, $columnSorting);
			return $this->privateVars['resultSet']; // use this result for debug purposes.
		}

		/**
		 * Clears the internal selection result and frees up its memory
		 *
		 */
		function clear() {
			if (is_Object($this->privateVars['resultSet'])) {
				$this->privateVars['resultSet']->clear();
			}
		}

		/**
		 * Returns the number of resultrows of the filter selection
		 *
		 * @param DataFilter $filter the filter
		 * @return int the number of resultrows
		 */
		function countRows(&$filter) {
			$database = $this->privateVars["database"];

	 		$functions = new FunctionDescriptions();
	 		$functions->addCount($this->getPrimaryKey(), 'countResult');

	 		$resultSet = $this->executeDataFunction($functions, $filter);
	 		$resultRow = $resultSet->getRow();

	 		return $resultRow['countResult'];
		}

		/**
		 * Removes rows from this table using a filter
		 *@param DataFilter the rows that meet these requirements will be removed
		 */
		function deleteRows(&$filter, $executeEvents = false) {
			if ($this->isMultiLanguage()) $executeEvents = true; // for deletion of language data
			if ($executeEvents) {
				$this->selectRows($filter, new ColumnSorting());
				while($row = $this->getRow()) $row->delete();
			} else {
				$database = $this->privateVars["database"];
				$database->deleteRows($this, $filter);
			}
		}

		/**
		**/
		function copyRows(&$dataFilter, &$copyMutation, &$originalMutation) {
			$result = array();

			$this->selectRows($dataFilter, new ColumnSorting());
			while($row = $this->getRow()) {
				$result[] = $row->copyRowSetValues($copyMutation, $originalMutation);
			}
			return $result;
		}

		/**
		 * Get the query used to get the current resultset
		 *@return string the SQL query used to get the resultset, FALSE if no
		 * Resultset is present
		 */
		function getSelectionQuery() {
			if (!isSet($this->privateVars['resultSet'])) return false;
			if (!is_Object($this->privateVars['resultSet'])) return false;
			return $this->privateVars['resultSet']->getQuery();
		}

		/**
		 * Returns a Row of data from the selected rows.
		 *@return DataRow if there is a row left in the resultset. False otherwise
		 */
		function getRow() {
			if (!isSet($this->privateVars['resultSet'])) return false;
			if (!is_Object($this->privateVars['resultSet'])) return false;
			return $this->privateVars['resultSet']->createRow($this);
		}

		function hasMoreRows() {
			if (!isSet($this->privateVars['resultSet'])) return false;
			if (!is_Object($this->privateVars['resultSet'])) return false;
			return $this->privateVars['resultSet']->hasMoreRows();
		}

		/**
		 * Returns the number of rows in the current selection
		 *@return int the number of rows in the current resultset, FALSE if
		 * no resultset is present
		 */
		function getSelectedRowCount() {
			if (!isSet($this->privateVars['resultSet'])) return 0;
			if (!is_Object($this->privateVars['resultSet'])) return 0;
			$rows = $this->privateVars['resultSet']->getNumRows();
			if ($rows == -1) $rows = 0;
			return $rows;
		}

		/**
		 * Returns a row with the given key. The given key is threaded as a value
		 * of the column that is the table key
		 *@param mixed $key the key to use
		 *@return DataRow the row with the given key, FALSE if an error occured
		 */
		function getRowByKey($key) {
			// database caching
			if($GLOBALS['databaseCache']->hasCache($this->getTableName(),$key))  {
				return $GLOBALS['databaseCache']->getCache($this->getTableName(),$key);
			}

			// caching
			if ($this->privateVars['rowCaching'] == true) {
				if (isSet($this->privateVars['rowCache'][$key])) {
					return $this->privateVars['rowCache'][$key];
				}
			}
			$database = $this->privateVars["database"];
			$result = $database->selectRow($this, $key);
			if (!is_Object($result)) {
				return false;
			}
			$dataRow = $result->createRow($this);
			// caching
			if ($this->privateVars['rowCaching'] == true) {
				$this->privateVars['rowCache'][$key] = $dataRow;
			}

			// database caching
			$GLOBALS['databaseCache']->setCache($dataRow);
			$result->clear();

			return $dataRow;
		}

		/**
		 * Executes data functions on this table with the data that meet the given requirements.
		 * The different with the other table functions is that here an resultset is returned.
		 * This is because the resultset does not contain standard columns.
		 *@param FunctionDescriptions $functionDescriptions the functions to execute
		 *@param DataFilter $filter a list of requirements the data must meet
		 *@param ColumnSorting $columnSorting a list of sort settings
		 *@return Resultset the resultset, or FALSE if an error occured
		 */
		function executeDataFunction(&$functionDescriptions, &$filter, $columnSorting = null, $debug = false) {
			if(!is_object($columnSorting)) $columnSorting = new ColumnSorting();
			$database = $this->privateVars["database"];
			return $database->executeTableFunctions($this, $functionDescriptions, $filter, $columnSorting, $debug);
		}

		/**
		 * Executes data mutations on this table with the data that meet the given requirements.
		 *@param DataMutation $mutationDescriptions the mutations to execute
		 *@param DataFilter $filter a list of requirements the data must meet
		 */
		function executeDataMutations(&$mutationDescriptions, &$filter, $debug=false) {
			$database = $this->privateVars["database"];
			$database->executeTableMutations($this, $mutationDescriptions, $filter, $debug);
		}

		/**
		 *@param DataRow $row
		 */
		function beforeRowInsert(&$row) {
			if (isSet($this->privateVars['eventhandler'])) {
				$handler = $this->privateVars['eventhandler'];
				$handler->beforeRowInsert($row);
			}
		}

		/**
		 *@param DataRow $row
		 */
		function afterRowInsert(&$row) {
			if (isSet($this->privateVars['eventhandler'])) {
				$handler = $this->privateVars['eventhandler'];
				$handler->afterRowInsert($row);
			}
		}

		/**
		 *@param DataRow $row
		 */
		function beforeRowUpdate(&$row) {
			if (isSet($this->privateVars['eventhandler'])) {
				$handler = $this->privateVars['eventhandler'];
				$handler->beforeRowUpdate($row);
			}
		}

		/**
		 *@param DataRow $row
		 */
		function afterRowUpdate(&$row) {
			if (isSet($this->privateVars['eventhandler'])) {
				$handler = $this->privateVars['eventhandler'];
				$handler->afterRowUpdate($row);
			}
		}

		/**
		 *@param DataRow $row
		 */
		function beforeRowDelete(&$row) {
			if (isSet($this->privateVars['eventhandler'])) {
				$handler = $this->privateVars['eventhandler'];
				$handler->beforeRowDelete($row);
			}
		}

		/**
		 *@param DataRow $row
		 */
		function afterRowDelete(&$row) {
			if (isSet($this->privateVars['eventhandler'])) {
				$handler = $this->privateVars['eventhandler'];
				$handler->afterRowDelete($row);
			}
		}

		function createTable() {
			$database = $this->privateVars["database"];
			$database->createTable($this);
		}

		function tableExists() {
			$database = $this->privateVars["database"];
			return $database->tableExists($this);
		}

		function synchronizeStructure() {
			$database = $this->privateVars["database"];
			return $database->synchronizeTableStructure($this);
		}

		function columnRename($oldName, $alias) {
			$database = $this->privateVars["database"];
			return $database->renameTableColumn($this, $oldName, $alias);
		}

		function copyTable($newName) {
			$database = $this->privateVars["database"];
			return $database->copyTable($this, $newName);
		}

		/**
		 * Drops table in database confirmationCode = "tabledropconfirm"
		 * send a confirmationcode to check.
		 */
		function dropTable($confirmationCode) {
			$database = $this->privateVars["database"];
			$database->dropTable($this, $confirmationCode);
		}

	}

	class DataEventListener {

		/**
		 *@param DataRow &$row
		 */
		function beforeRowInsert(&$row) {
		}

		/**
		 *@param DataRow &$row
		 */
		function afterRowInsert(&$row) {
		}

		/**
		 *@param DataRow &$row
		 */
		function beforeRowUpdate(&$row) {
		}

		/**
		 *@param DataRow &$row
		 */
		function afterRowUpdate(&$row) {
		}

		/**
		 *@param DataRow &$row
		 */
		function beforeRowDelete(&$row) {
		}

		/**
		 *@param DataRow &$row
		 */
		function afterRowDelete(&$row) {
		}

	}

	/**
	 * An class describing a database row.
	 *@author Matthijs Groen (matthijs at ivinity.nl)
	 *@version 1.1
	 */
	class DataRow {

		/**
		 * A set of private vars of this class.
		 * The known variables are:
		 * - table (reference to the table where this row is from)
		 * - changed (bool stating if the row is changed)
		 * - values (array containing column values)
		 * - valChanged (array containing the change state of every column)
		 * - inDatabase (bool stating if this row is stored in the database)
		 *@var array $privateVars
		 */
		var $privateVars;

		/**
		 * Constructor
		 *@param DataTable $table the table this row is from
		 */
		function DataRow(&$table) {
			$database = $table->getDatabase();
			$this->privateVars = array(
				"table" => $table,
				"changed" => false,
				"values" => array(),
				"valChanged" => array(),
				"inDatabase" => false,
				"storeQuery" => "",
				"languages" => array(),
				"supportsLanguages" => array($database->getDefaultLanguage()),
				"availableLanguagesLoaded" => false
			);
		}

		function &getTable() {
			return $this->privateVars['table'];
		}

		function &getDatabase() {
			return $this->privateVars['table']->getDatabase();
		}

		function getStoreQuery() {
			return $this->privateVars['storeQuery'];
		}

		/**
		 * Returns if the record exists in the database
		 *@return bool true if the record is in the database, false otherwise
		 */
		function isInDatabase() {
			return $this->privateVars['inDatabase'];
		}

		/**
		 * Returns if the record exists in the database
		 *@return bool true if the record is in the database, false otherwise
		 */
		function clearInDatabase() {
			$table = $this->privateVars["table"];
			$this->privateVars['inDatabase'] = false;
			$this->setNull($table->getPrimaryKey());
		}

		function cacheRow() {
			$GLOBALS['databaseCache']->setCache($this);
		}

		/**
		 * compares two datarows with eachother op matching column values.
		 *@param DataRow $otherRow the row to compare to
		 *@param array $fields the fields to compare
		 */
		function areEqual(&$otherRow, $fields) {
			$thisTable = $this->getTable();
			$otherTable = $otherRow->getTable();

			foreach($fields as $alias) {
				//print "checking: ".$alias."\n";

				if ($thisTable->getColumnType($alias) != $otherTable->getColumnType($alias)) {
					//print "Ongelijk kolomtype\n";
					return false;
				}
				if ($otherRow->isNull($alias) != $this->isNull($alias)) {
					//print "Ongelijk null\n";
					return false;
				}
				if (!$otherRow->isNull($alias) && !$this->isNull($alias)) {
					$valueA = $this->getValue($alias);
					$valueB = $otherRow->getValue($alias);
					//print 'check: '.$thisTable->getColumnType($alias)."\n";

					switch ($thisTable->getColumnType($alias)) {
						case 'date': if (!$valueA->isEqual($valueB, ivDay, ivMonth, ivYear, ivHour, ivMinute, ivSecond))
							return false; break;
						case 'time': if (!$valueA->isEqual($valueB, ivHour, ivMinute, ivSecond)) {
								//print $valueA->toString("H:i:s") . " - " . $valueB->toString("H:i:s") . "\n";
								return false;
							}
							break;
						default: if ($valueA != $valueB) return false; break;
					}
				}
			}
			return true;
		}

		/**
		 * Sets a value of a column.
		 *@param string $alias the alias of the column
		 *@param mixed $value the value of the column, in the appropriate format
		 *  of the column datatype
		 */
		function setValue($alias, $value) {
			$table = $this->privateVars["table"];
			if (!$table->hasField($alias)) return false;
			//if ((!isSet($this->privateVars["values"][$alias])) ||
			//	($this->privateVars["values"][$alias] != $value)) {
			if (!isSet($this->privateVars["values"][$alias])) { // no value, always change
				$this->privateVars["changed"] = true;
				$this->privateVars["valChanged"][$alias] = true;
			} else {
				$fieldType = $this->getFieldType($alias);
				if ($fieldType == "time") { // compare times
					if (!$this->privateVars['values'][$alias]->isEqual($value, ivHour, ivMinute, ivSecond)) {
						$this->privateVars["changed"] = true;
						$this->privateVars["valChanged"][$alias] = true;
					}
				} else if ($fieldType == "date") { // compare dates
					if (!$this->privateVars["values"][$alias]->isEqual($value)) {
						$this->privateVars["changed"] = true;
						$this->privateVars["valChanged"][$alias] = true;
					}
				} else { // compare as string
					if (strcmp($this->privateVars["values"][$alias], $value) != 0) {
						$this->privateVars["changed"] = true;
						$this->privateVars["valChanged"][$alias] = true;
					}
				}
			}
			$this->privateVars["values"][$alias] = $value;
		}

		function hasField($alias) {
			$table = $this->privateVars["table"];
			return $table->hasField($alias);
		}

		function getFieldType($alias) {
			$table = $this->privateVars["table"];
			return $table->getColumnType($alias);
		}

		/**
		 * Returns the value of a column
		 *@param string $alias the alias of the column
		 *@return mixed the value of the column, in the appriopriate format of the
		 *  column datatype
		 */
		function getValue($alias) {
			if (isSet($this->privateVars["values"][$alias])) {return $this->privateVars["values"][$alias];}
			else {
				$table = $this->privateVars["table"];
				if($table->isBlob($alias)) {
					$database = $table->getDatabase();
					return $database->getBlob($table,$alias, $this->getValue($table->getPrimaryKey()));
				}
			}
			return null;
		}

		function getDateString($alias, $nullValue, $format="") {
			if ($this->isNull($alias)) return $nullValue;
			$dateObj = $this->getValue($alias);
			if ($format == "") return $dateObj->toString();
			return $dateObj->toString($format);
		}

		/**
		 * Returns if this row has changed
		 *@return bool true if the row is changed, false otherwise
		 */
		function rowChanged() {
			return $this->privateVars["changed"];
		}

		/**
		 * Sets the row to changed
		 */
		function setChanged() {
			$this->privateVars["changed"] = true;
		}

		/**
		 * Returns if the specified column has changed
		 *@param string alias of the column
		 *@return bool true if the column has changed, false otherwise
		 */
		function isChanged($alias) {
			return ((isSet($this->privateVars["valChanged"][$alias])) && ($this->privateVars["valChanged"][$alias] == true));
		}

		/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

		function getMultiLanguageValue($alias, $languageCode = "") {
			if ($languageCode == "") {
				global $language;
				$languageCode = $language->getDictionary();
			}
			$database = $this->getDatabase();
			if ($languageCode == $database->getDefaultLanguage()) {
				return $this->getValue($alias);
			}
			if (!$this->supportsLanguage($languageCode)) return null;
			if (!isSet($this->privateVars['languages'][$languageCode])) return null;
			if (!isSet($this->privateVars['languages'][$languageCode]['values'][$alias])) return null;
			return $this->privateVars['languages'][$languageCode]['values'][$alias];
		}

		function isMultiLanguageNull($alias, $languageCode = "") {
			if ($languageCode == "") {
				global $language;
				$languageCode = $language->getDictionary();
			}
			$database = $this->getDatabase();
			if ($languageCode == $database->getDefaultLanguage()) {
				return $this->isNull($alias);
			}
			if (!$this->supportsLanguage($languageCode)) return true;
			if (!isSet($this->privateVars['languages'][$languageCode])) return true;
			return !isSet($this->privateVars['languages'][$languageCode]['values'][$alias]);
		}

		function setMultiLanguageValue($alias, $value, $languageCode = "") {
			if ($languageCode == "") {
				global $language;
				$languageCode = $language->getDictionary();
			}
			$database = $this->getDatabase();
			if ($languageCode == $database->getDefaultLanguage()) {
				//print "ok! $alias = $value<br />";
				$this->setValue($alias, $value);
				return;
			}
			if (!$this->supportsLanguage($languageCode)) $this->addLanguageSupport($languageCode);

				// Added by Guido, 14-10-05, if the value was NULL there is no value, create it
				if(!isSet($this->privateVars['languages'][$languageCode]['values'][$alias])) $this->privateVars['languages'][$languageCode]['values'][$alias] = "";

				if ($this->privateVars['languages'][$languageCode]['values'][$alias] != $value) {
				$this->privateVars['languages'][$languageCode]['values'][$alias] = $value;
				$this->privateVars['languages'][$languageCode]['valChanged'][$alias] = true;
				$this->privateVars['languages'][$languageCode]['changed'] = true;
				$this->setChanged();
			}
		}

		function setMultiLanguageNull($alias,$languageCode = "") {
			if ($languageCode == "") {
				global $language;
				$languageCode = $language->getDictionary();
			}
			$database = $this->getDatabase();
			if ($languageCode == $database->getDefaultLanguage()) {
				$this->setNull($alias);
				return;
			}
			if (!$this->supportsLanguage($languageCode)) $this->addLanguageSupport($languageCode);
			if (isSet($this->privateVars['languages'][$languageCode]['values'][$alias])) {

				unSet($this->privateVars['languages'][$languageCode]['values'][$alias]);
				$this->privateVars['languages'][$languageCode]['valChanged'][$alias] = true;
				$this->privateVars['languages'][$languageCode]['changed'] = true;
				$this->setChanged();
			}
		}

		function hasMultiLanguageValue($alias, $languageCode = "") {
			if ($languageCode == "") {
				global $language;
				$languageCode = $language->getDictionary();
			}
			$database = $this->getDatabase();
			if ($languageCode == $database->getDefaultLanguage()) {
				return !$this->isNull($alias);
			}
			if (!$this->supportsLanguage($languageCode)) return false;
			return true;
		}

		function supportsLanguage($languageCode) {
			$this->loadMultiLanguages();
			return in_array($languageCode, $this->privateVars['supportsLanguages']);
		}

		function addLanguageSupport($languageCode) {
			if($this->supportsLanguage($languageCode)) return;
			$table = $this->getTable();
			$langSupport = array(
				"storageID" => false,
				"values" => array(),
				"valChanged" => array(),
				"changed" => true);

			$mlFields = $table->getMultiLanguageFields();
			foreach($mlFields as $mlField) {
				$langSupport['values'][$mlField] = "";
				$langSupport['valChanged'][$mlField] = true;
			}
			$this->privateVars['languages'][$languageCode] = $langSupport;
			$this->privateVars['supportsLanguages'][] = $languageCode;
		}

		function getSupportedLanguages() {
			$this->loadMultiLanguages();
			return $this->privateVars['supportsLanguages'];
		}

		function setLanguageStored($languageCode) {
			if (!$this->supportsLanguage($languageCode)) return false;
			$this->privateVars['languages'][$languageCode]['changed'] = false;
			$table = $this->getTable();
			$mlFields = $table->getMultiLanguageFields();
			foreach($mlFields as $mlField) {
				$this->privateVars['languages'][$languageCode]['valChanged'][$mlField] = false;
			}
		}

		function isLanguageDataChanged($languageCode) {
			$database = $this->getDatabase();
			if ($languageCode == $database->getDefaultLanguage()) {
				return $this->rowChanged();
			}
			if (!$this->supportsLanguage($languageCode)) return false;
			return $this->privateVars['languages'][$languageCode]["changed"];
		}

		function getLanguageData($languageCode) {
			$database = $this->getDatabase();
			if ($languageCode == $database->getDefaultLanguage()) return false;
			if (!$this->supportsLanguage($languageCode)) return false;
			return $this->privateVars['languages'][$languageCode]["values"];
		}

		function getLanguageStorageID($languageCode) {
			$database = $this->getDatabase();
			if ($languageCode == $database->getDefaultLanguage()) return false;
			if (!$this->supportsLanguage($languageCode)) return false;
			return $this->privateVars['languages'][$languageCode]["storageID"];
		}

		function loadMultiLanguages() {
			if ($this->privateVars["availableLanguagesLoaded"]) return true;
			$this->privateVars["availableLanguagesLoaded"] = true;

			$mlTable = new MultiLanguageTable($this->getDatabase());

			$table = $this->getTable();
			$filter = new DataFilter();
			$filter->addEquals("table", $table->getTableName());
			$filter->addEquals("recordID", $this->getValue($table->getPrimaryKey()));
			$mlTable->selectRows($filter, new ColumnSorting());
			//print $mlTable->getSelectionQuery() . " test<br />";
			while ($mlRow = $mlTable->getRow()) {
				$languageCode = $mlRow->getValue("language");
				if (!$this->supportsLanguage($languageCode)) {
					$this->addLanguageSupport($languageCode);
					$texts = unserialize($mlRow->getValue("recordTexts"));
					$this->privateVars['languages'][$languageCode]['values'] = $texts;
					$this->privateVars['languages'][$languageCode]['storageID'] = $mlRow->getValue("ID");
					$this->setLanguageStored($languageCode);
				}
			}
		}

		/* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */

		/**
		 * Returns if a column has no value
		 *@param string $alias the column to check
		 *@return bool true if the column has no value, false otherwise
		 */
		function isNull($alias) {
			return !isSet($this->privateVars["values"][$alias]);
		}

		/**
		 * Sets the value of a specified column to NULL
		 *@param string $alias the column to erase the value of
		 */
		function setNull($alias) {
			$table = $this->privateVars["table"];
			if (!$table->hasField($alias)) return false;
			if (isSet($this->privateVars["values"][$alias]) || ($table->isBlob($alias))) {
				$this->privateVars["changed"] = true;
				$this->privateVars["valChanged"][$alias] = true;
			}
			unSet($this->privateVars["values"][$alias]);
		}

		/**
		 * Marks this row as stored in the database, and resets al change statusses
		 */
		function setStored() {
			$this->privateVars["changed"] = false;
			$this->privateVars["inDatabase"] = true;
			$changedColumns = $this->privateVars['valChanged'];
			reset($changedColumns);
			while (list($key, $val) = each($changedColumns)) {
				$this->privateVars['valChanged'][$key] = false;
			}
		}

		/**
		 * Stores all the changes to this row in the database. If the row is not in
		 * the database it will be inserted
		 */
		function store($handleEvents = true) {

			if ((!$this->privateVars["changed"]) && ($this->isInDatabase())) return false;
			$table = $this->privateVars["table"];
			if (!$this->isInDatabase()) { // Insert

				if ($handleEvents) $table->beforeRowInsert($this);
				$result = $table->insertRow($this);
				if (is_Object($result)) {
					$this->privateVars['storeQuery'] = $result->getQuery();
					$this->setValue($table->getPrimaryKey(), $result->getInsertID());
					$this->setStored();

					// Cache result
					if($GLOBALS['databaseCache']->hasCache($table->getTableName(),$this->getValue($table->getPrimaryKey())))  {
						$GLOBALS['databaseCache']->setCache($this);
					}

					if ($handleEvents) $table->afterRowInsert($this);
					return true;
				}
				return false;
			} else { // Update
				if ($handleEvents) $table->beforeRowUpdate($this);
				$result = $table->updateRow($this);

				if (is_Object($result) || $result === true) {
					if(is_Object($result)) $this->privateVars['storeQuery'] = $result->getQuery();

					// If row is changed, update cache
					if($GLOBALS['databaseCache']->hasCache($table->getTableName(),$this->getValue($table->getPrimaryKey())))  {
						$GLOBALS['databaseCache']->setCache($this);
					}

					if ($handleEvents) $table->afterRowUpdate($this);

					$this->setStored();

					return true;
				}
				return false;
			}
		}

		function getInsertQuery() {
			$table = $this->privateVars["table"];
			$database = $this->getDatabase();
			return $database->getInsertQuery($table, $this);
		}

		/**
		 * Deletes this row from the database, if it is stored
		 * This object will be marked as 'not in database' after the deletion,
		 * this way a store action will insert the row agains
		 */
		function delete($triggerEvents = true) {
			if (!$this->isInDatabase()) return true; // that was easy.
			$table = $this->privateVars["table"];
			if ($triggerEvents) $table->beforeRowDelete($this);
			$table->deleteRow($this);
			$this->privateVars['inDatabase'] = false;

			// Delete from cache
			$GLOBALS['databaseCache']->deleteFromCache($this);

			if ($triggerEvents) $table->afterRowDelete($this);
			return true;
		}

		/**
		 * Copies the data form this row to the target row.
		 * the target rows does not need to be of the same table,
		 * but the fields in this row need to exist in the target row,
		 * including the same datatype.
		 *@param DataRow $target the target to copy the data to
		 *@param bool $includeKey if true the primary key also gets copied.
		 */
		function copyData(&$target, $includeKey = false) {
			$table = $this->getTable();
			$primKey = $table->getPrimaryKey();
			for ($i = 0; $i < $table->getColumnCount(); $i++) {
				$alias = $table->getAlias($i);
				// If the alias is the primary key and keys are not included, don't copy the key
				if ((($alias == $primKey) && ($includeKey)) || ($alias != $primKey)) {
					if ($this->isNull($alias)) {
						$target->setNull($alias);
					} else {
						$target->setValue($alias, $this->getValue($alias));
					}
				}
			}
			for ($i = 0; $i < $table->getBlobColumnCount(); $i++) {
				$alias = $table->getBlobAlias($i);
				if ((($alias == $primKey) && ($includeKey)) || ($alias != $primKey)) {
					$target->setValue($alias, $this->getValue($alias));
				}
			}
		}

		/**
		 * Creates a copy of this row and stores it in the database, returning the copy
		 *@return DataRow the copy of this row
		 */
		function copyRow() {
			return $this->copyRowSetValues(new DataMutation(), new DataMutation());
		}

		/**
		 * Creates a copy of this row with the option to alter the copy and the original with mutations
		 *@param DataMutation $copyMutation the mutation set to alter the copy
		 *@param DataMutation $originalMutation the mutation to alter the original
		 */
		function copyRowSetValues(&$copyMutation, &$originalMutation) {
			// 1. make a copy.
			$table = $this->getTable();
			//printf("Creating a copy of a `%s` row!<br />\n", $table->getTableName());

			$thisCopy = $table->addRow();
			$this->copyData($thisCopy, false);
			$thisCopy->applyMutation($copyMutation);
			// 1b. Store our copy for our new Key
			$thisCopy->store(false);
			// get the key
			$table = $this->getTable();

			if ($table->isMultiLanguage()) {
				$primKey = $table->getPrimaryKey();
				$thisRowKey = $this->getValue($primKey);
				$copyRowKey = $thisCopy->getValue($primKey);
				$mlTable = new MultiLanguageTable($this->getDatabase());
				$filter = new DataFilter();
				$filter->addEquals("table", $table->getTableName());
				$filter->addEquals("recordID", $thisRowKey);
				$mlTable->selectRows($filter, new ColumnSorting());
				$mlCopyMutation = new DataMutation();
				$mlCopyMutation->setEquals("recordID", $copyRowKey);
				while ($languageRow = $mlTable->getRow()) {
					$languageRow->copyRowSetValues($mlCopyMutation, new DataMutation());
				}
			}

			return $thisCopy;
		}

		/**
		 * Does a selection on the given table to select all records with the referenceColumn
		 * having the same value as the parentColumn of this row. This function is ideal for
		 * selecting child elements.
		 *@param DataTable $table the table containing the child records
		 *@param string $referenceColumn the column in the given table containing the key to this row
		 *@param string $parentColumn specifies wich column from this row must be seen as the key for the childs
		 */
		function getChildReferences(&$table, $referenceColumn, $parentColumn, $searchFilter=false) {
			$ownTable = $this->getTable();
			$keyValue = $this->getValue($parentColumn);
			$filter = new DataFilter();
			$filter->addEquals($referenceColumn, $keyValue);
			if (is_Object($searchFilter)) {
				$filter->addDataFilter($searchFilter);
			}
			if ($table->getTableName() == $ownTable->getTableName()) {
				$filter->addEqualsNot($parentColumn, $keyValue);
			}
			$table->selectRows($filter, new ColumnSorting());
		}

		/**
		 * Does a selection on the given table to select all records with their primary key the same
		 * as a column in this row. This function is ideal for selecting parent elements.
		 *@param DataTable $table the table containing the parent rows
		 *@param string $childColumn the column in this row containing the primary key of the parent
		 */
		function getParentReferences(&$table, $childColumn) {
			$ownTable = $this->getTable();
			$primKey = $ownTable->getPrimaryKey();
			$keyValue = $this->getValue($childColumn);

			$filter = new DataFilter();
			$filter->addEquals($primKey, $keyValue);
			$table->selectRows($filter, new ColumnSorting());
		}

		/**
		 * Does a selection on the given table to select all records with the referenceColumn
		 * having the same value as the parentColumn of this row. This function is ideal for
		 * selecting child elements.
		 *@param DataTable $table the table containing the child records
		 *@param string $referenceColumn the column in the given table containing the key to this row
		 *@param string $parentColumn specifies wich column from this row must be seen as the key for the childs
		 */
		function mutateChildReferences(&$table, $referenceColumn, $parentColumn, &$mutation) {
			$ownTable = $this->getTable();
			$keyValue = $this->getValue($parentColumn);
			$filter = new DataFilter();
			$filter->addEquals($referenceColumn, $keyValue);
			if ($table->getTableName() == $ownTable->getTableName()) {
				$filter->addEqualsNot($parentColumn, $keyValue);
			}
			$table->executeDataMutations($mutation, $filter);
		}

		/**
		 * Does a selection on the given table to select all records with their primary key the same
		 * as a column in this row. This function is ideal for selecting parent elements.
		 *@param DataTable $table the table containing the parent rows
		 *@param string $childColumn the column in this row containing the primary key of the parent
		 */
		function mutateParentReferences(&$table, $childColumn, &$mutation) {
			$ownTable = $this->getTable();
			$primKey = $ownTable->getPrimaryKey();
			$keyValue = $this->getValue($childColumn);

			$filter = new DataFilter();
			$filter->addEquals($primKey, $keyValue);
			$table->executeDataMutations($mutation, $filter);
		}

		/**
		 * Applies all mutations specified in the given datamutation set to this row.
		 * Only the specified mutations that have an alias in this row will be executed.
		 *@param DataMutation $dataMutation the set containing the nessecary mutations
		 */
		function applyMutation(&$dataMutation) {
			if (!$dataMutation->getMutationCount()) return;
			$dataMutation->mutate($this);
		}

		/**
		 * Creates a copy of the row with a parent reference and linking the copied parent to the copy
		 * of this row.
		 *@param DataRow $row the copy of this record, where all the copies of the parents will be referencing to
		 *@param DataTable $referencingTable the table containing the record of the parent
		 *@param string $childColumn the column in this row containing the primary key of the parent
		 *@param DataMutation $copyMutation mutations to apply on the copied parent
		 *@param DataMutation $originalMutation mutations to apply on the original parent
		 */
		function copyParentReference(&$row, &$referencingTable, $childColumn, &$copyMutation, &$originalMutation) {
			$this->getParentReferences($referencingTable, $childColumn);
			$primKey = $referencingTable->getPrimaryKey();
			if ($referencingRow = $referencingTable->getRow()) {
				// 2a. Create a copy of row with the reference
				$rowCopy = $referencingRow->copyRowSetValues($copyMutation, $originalMutation);
				// 2b. Alter our reference to point to the copy
				$row->setValue($childColumn, $rowCopy->getValue($primKey));
				// 2c. Store the copy again
			}
		}

		/**
		 * Creates a copy of the rows with a child reference and linking the copied childs to the copy
		 * of this row.
		 *@param DataRow $row the copy of this record, where all the copies of the childs will be referencing to
		 *@param DataTable $referencingTable the table containing the records of the children
		 *@param string $referenceColumn the column in the given table containing the key to this row
		 *@param string $parentColumn specifies wich column from this row must be seen as the key for the childs
		 *@param DataMutation $copyMutation mutations to apply on the copied parent
		 *@param DataMutation $originalMutation mutations to apply on the original parent
		 */
		function copyChildReferences(&$row, &$referencingTable, $referenceColumn, $parentColumn,
			&$copyMutation, &$originalMutation, $searchFilter = false) {

			$this->getChildReferences($referencingTable, $referenceColumn, $parentColumn, $searchFilter);
			while($referencingRow = $referencingTable->getRow()) {
				// 2a. Create a copy of row with the reference
				$rowCopy = $referencingRow->copyRowSetValues($copyMutation, $originalMutation);
				// 2b. Alter the reference to point to our copy
				$rowCopy->setValue($referenceColumn, $row->getValue($parentColumn));
				// 2c. Store the copy again
				$rowCopy->store(false);
			}
		}

		function getDebugInfo() {
			global $libraryClassDir;
			require_once($libraryClassDir . "Table.class.php");

			$debugTable = new Table();
			$debugTable->setHeader("Alias", "Column", "Type", "Value");
			$table = $this->getTable();
			$tableMeta = $table->privateVars['metaData'];
			foreach($tableMeta as $metaRow) {
				if ($this->isNull($metaRow['alias'])) {
					$value = "<i>null</i>";
				} else {
					$value = $this->getValue($metaRow['alias']);
					if (is_Object($value)) {
						$value = $value->toString("d-m-Y h:i");
					}
				}

				$debugTable->addRow($metaRow['alias'], $metaRow['name'], $metaRow['type'] . " (".$metaRow['length'].")",
					$value);
			}
			return $debugTable;
		}

	}

	/**
	 * A class to define a set of filters on data.
	 * This class can be used to specify all sorts of requirements
	 * the data has to meet to be selected
	 *@author Matthijs Groen (matthijs at ivinity.nl)
	 *@version 1.0
	 */
	class DataFilter {

		/**
		 * Private set containing the filters
		 *@var array $privateVars
		 */
		var $privateVars;

		/**
		 * Constructor
		 */
		function DataFilter() {
			$this->privateVars = array(
				"filters" => array(),
				"mode" => "and",
				"limit" => max_limit,
				"offset" => 0
			);
		}

		/**
		 * Returns an array containing the joined tablenames
		 *@return array an array containing the joined tablenames
		 */
		function getJoinedTableNames() {
			$result = array();
			for ($i = 0; $i < $this->getFilterCount(); $i++) {
				$filter = $this->getFilter($i);
				if (($filter['type'] == 'joinFilter') || ($filter['type'] == 'filterJoinFilter')) {
					$joinTable = $filter['joinTable'];
					$result[] = $joinTable->getTableName();
					$subFilter = $filter['filter'];
					$result = array_merge($result, $subFilter->getJoinedTableNames());
				}
				if ($filter['type'] == 'filter') {
					$subFilter = $filter['filter'];
					$result = array_merge($result, $subFilter->getJoinedTableNames());
				}
			}
			return array_unique($result);
		}

		function hasJoins() {
			$result = array();
			for ($i = 0; $i < $this->getFilterCount(); $i++) {
				$filter = $this->getFilter($i);
				if ($filter['type'] == 'joinFilter') return true;
				if ($filter['type'] == 'filterJoinFilter') return true;
				if ($filter['type'] == 'filter') {
					$subFilter = $filter['filter'];
					if ($subFilter->hasJoins()) return true;
				}
			}
			return false;
		}

		function getNumberWhereItems() {
			$result = 0;
			for ($i = 0; $i < $this->getFilterCount(); $i++) {
				$filter = $this->getFilter($i);
				switch($filter['type']) {
					case 'joinFilter':
					case 'filterJoinFilter':
					case 'filter':
						$subFilter = $filter['filter'];
						$result += $subFilter->getNumberWhereItems();
						break;
					default: $result++; break;
				}
			}
			return $result;
		}

		/**
		 * Sets the 'glue' mode of the requirements
		 *@param string $mode 'or' or 'and' allowed
		 */
		function setMode($mode) {
			$this->privateVars['mode'] = $mode;
		}

		/**
		 * returns the glue mode of the requirements
		 *@return string 'or' or 'and'
		 */
		function getMode() {
			return $this->privateVars['mode'];
		}

		/**
		 * Adds an equals constraint to the filters.
		 * the data of the column with the given alias needs to be equal
		 * to the data provided. The data provided will be handled in the
		 * same dataformat as the column.
		 *@param string $alias the column where the constraint is for
		 *@param mixed $value the value the data in the column has to be equal to
		 */
		function addEquals($alias, $value, $isAlias = false) {
			$this->privateVars['filters'][] = array(
				'alias' => $alias,
				'value' => $value,
				'isAlias' => $isAlias,
				'type' => 'equals'
			);
		}

		/**
		 * Adds an not-equals constraint to the filters.
		 * the data of the column with the given alias needs not to be equal
		 * to the data provided. The data provided will be handled in the
		 * same dataformat as the column.
		 *@param string $alias the column where the constraint is for
		 *@param mixed $value the value the data in the column has to not be equal to
		 */
		function addEqualsNot($alias, $value, $isAlias = false) {
			$this->privateVars['filters'][] = array(
				'alias' => $alias,
				'value' => $value,
				'isAlias' => $isAlias,
				'type' => 'equalsNot'
			);
		}

		/**
		 * Adds a 'greater than' constraint to the filters.
		 * the data of the column with the given alias needs to be greater than
		 * the data provided. The data provided will be handled in the
		 * same dataformat as the column.
		 *@param string $alias the column where the constraint is for
		 *@param mixed $value the value the data in the column has to be greater than
		 */
		function addGreaterThan($alias, $value, $isAlias = false) {
			$this->privateVars['filters'][] = array(
				'alias' => $alias,
				'value' => $value,
				'isAlias' => $isAlias,
				'type' => 'greaterThan'
			);
		}

		/**
		 * Adds a 'greater or equals than' constraint to the filters.
		 * the data of the column with the given alias needs to be greater or equal than
		 * the data provided. The data provided will be handled in the
		 * same dataformat as the column.
		 *@param string $alias the column where the constraint is for
		 *@param mixed $value the value the data in the column has to be greater or equal than
		 */
		function addGreaterThanOrEquals($alias, $value, $isAlias = false) {
			$this->privateVars['filters'][] = array(
				'alias' => $alias,
				'value' => $value,
				'isAlias' => $isAlias,
				'type' => 'greaterEqualThan',
			);
		}

		/**
		 * Adds a 'smaller than' constraint to the filters.
		 * the data of the column with the given alias needs to be smaller than
		 * the data provided. The data provided will be handled in the
		 * same dataformat as the column.
		 *@param string $alias the column where the constraint is for
		 *@param mixed $value the value the data in the column has to be smaller than
		 */
		function addLessThan($alias, $value, $isAlias = false) {
			$this->privateVars['filters'][] = array(
				'alias' => $alias,
				'value' => $value,
				'isAlias' => $isAlias,
				'type' => 'smallerThan'
			);
		}

		/**
		 * Adds a 'smaller or equals than' constraint to the filters.
		 * the data of the column with the given alias needs to be smaller or equal than
		 * the data provided. The data provided will be handled in the
		 * same dataformat as the column.
		 *@param string $alias the column where the constraint is for
		 *@param mixed $value the value the data in the column has to be smaller or equal than
		 */
		function addLessThanOrEquals($alias, $value, $isAlias = false) {
			$this->privateVars['filters'][] = array(
				'alias' => $alias,
				'value' => $value,
				'isAlias' => $isAlias,
				'type' => 'smallerEqualThan'
			);
		}

		/**
		 * Adds requirements of another table (join)
		 *@param string $alias name of the column to join with other table
		 *@param string $joinAlias name of the column in the joined table
		 *@param DataTable $joinTable the table to join (and run requirements on, this table
		 *  contains the $joinAlias)
		 *@param DataFilter $filter the requirements to run on the joined table
		 */
		function addJoinDataFilter($alias, $joinAlias, &$joinTable, &$filter, $leftJoin = true, $innerJoin = true, $joinTableAlias='') {
			$this->privateVars['filters'][] = array(
				"alias" => $alias,
				"joinAlias" => $joinAlias,
				"joinTable" => $joinTable,
				"filter" => $filter,
				"joinType" => ($leftJoin) ? 'left' : 'right',
				"joinView" => ($innerJoin) ? 'inner' : 'outer',
				"joinTableAlias" => $joinTableAlias,
				"type" => "joinFilter"
			);
		}

		/**
		 * Adds requirements of another table (join)
		 *@param string $alias name of the column to join with other table
		 *@param string $joinAlias name of the column in the joined table
		 *@param DataTable $joinTable the table to join (and run requirements on, this table
		 *  contains the $joinAlias)
		 *@param DataFilter $filter the requirements to run on the joined table
		 */
		function addFilterJoinDataFilter($alias, $joinAlias, &$joinTable, &$filter, $leftJoin, &$joinFilter, $joinFilterForOriginal = true, $innerJoin = true, $joinTableAlias='') {
			$this->privateVars['filters'][] = array(
				"alias" => $alias,
				"joinAlias" => $joinAlias,
				"joinTable" => $joinTable,
				"filter" => $filter,
				"joinType" => ($leftJoin) ? 'left' : 'right',
				"joinView" => ($innerJoin) ? 'inner' : 'outer',
				"joinFilter" => $joinFilter,
				"joinFilterForOriginal" => $joinFilterForOriginal,
				"joinTableAlias" => $joinTableAlias,
				"type" => "filterJoinFilter"
			);
		}

		/**
		 * Adds a sub requirement
		 *@param DataFilter $filter subfilter
		 */
		function addDataFilter(&$filter) {
			if ($filter->getFilterCount() == 0) return;
			$this->privateVars['filters'][] = array(
				"filter" => $filter,
				"type" => "filter"
			);
		}

		/**
		 * Demands that a column is null
		 *@param string $alias the column alias of the column to be null
		 */
		function addNull($alias) {
			$this->privateVars['filters'][] = array(
				'alias' => $alias,
				'type' => 'null'
			);
		}

		/**
		 * Demands that a column is not null
		 *@param string $alias the column alias of the column to be not null
		 */
		function addNotNull($alias) {
			$this->privateVars['filters'][] = array(
				'alias' => $alias,
				'type' => 'not-null'
			);
		}

		/**
		 * Executes a (mathematical) function and puts its value in alias
		 *@param string $resultName the name in wich the results are available in the resultset
		 *@param DataSelectFunction $function the function to execute
		 */
		function addFunctionEqualsColumn(&$function, $alias, &$table, $tableAlias = "") {
			$this->privateVars['filters'][] = array(
				'function' => $function,
				'alias' => $alias,
				'table' => $table,
				'type' => 'functionEqualsValue'
			);
		}

		/**
		 * Adds an 'like' constraint to the filters.
		 * the data of the column with the given alias needs to be 'like'
		 * to the data provided. The data provided will be handled in the
		 * same dataformat as the column.
		 *@param string $alias the column where the constraint is for
		 *@param mixed $value the value the data in the column has to be like
		 */
		function addLike($alias, $value, $isAlias = false) {
			$this->privateVars['filters'][] = array(
				'alias' => $alias,
				'value' => $value,
				'isAlias' => $isAlias,
				'type' => 'like'
			);
		}

		function addColumnEquals($alias, $joinAlias, &$joinTable, $joinTableAlias = "") {
			$this->privateVars['filters'][] = array(
				'alias' => $alias,
				"joinAlias" => $joinAlias,
				"joinTable" => $joinTable,
				"joinTableAlias" => $joinTableAlias,
				'type' => 'columnEquals'
			);
		}

		function addColumnEqualsNot($alias, $joinAlias, &$joinTable) {
			$this->privateVars['filters'][] = array(
				'alias' => $alias,
				"joinAlias" => $joinAlias,
				"joinTable" => $joinTable,
				'type' => 'columnEqualsNot'
			);
		}

		function addColumnLessThan($alias, $joinAlias, &$joinTable) {
			$this->privateVars['filters'][] = array(
				'alias' => $alias,
				"joinAlias" => $joinAlias,
				"joinTable" => $joinTable,
				'type' => 'columnLessThan'
			);
		}

		function addColumnLessThanOrEquals($alias, $joinAlias, &$joinTable) {
			$this->privateVars['filters'][] = array(
				'alias' => $alias,
				"joinAlias" => $joinAlias,
				"joinTable" => $joinTable,
				'type' => 'columnLessThanOrEquals'
			);
		}

		function addColumnGreaterThan($alias, $joinAlias, &$joinTable) {
			$this->privateVars['filters'][] = array(
				'alias' => $alias,
				"joinAlias" => $joinAlias,
				"joinTable" => $joinTable,
				'type' => 'columnGreaterThan'
			);
		}

		function addColumnGreaterThanOrEquals($alias, $joinAlias, &$joinTable) {
			$this->privateVars['filters'][] = array(
				'alias' => $alias,
				"joinAlias" => $joinAlias,
				"joinTable" => $joinTable,
				'type' => 'columnGreaterThanOrEquals'
			);
		}

		function addTextSearch($text, $searchType) {
			$this->privateVars['filters'][] = array(
				"text" => $text,
				"searchType" => $searchType,
				'type' => 'textRelevance'
			);
		}

		/**
		 * Sets the maximum number of rows to use (select, update, delete)
		 *@param int $count the number of rows to execute the action on
		 */
		function setLimit($count) {
			$this->privateVars['limit'] = $count;
		}

		/**
		 * Returns the maximum number of rows to use in select, update and delete
		 *@return int the maximum number of rows to use
		 */
		function getLimit() {
			return $this->privateVars['limit'];
		}

		/**
		 * Sets the starting row number of the rows to use (select, update, delete)
		 *@param int $offset the number of rows to skip before beginning the action.
		 */
		function setOffset($offset) {
			$this->privateVars['offset'] = $offset;
		}

		/**
		 * Returns the starting offset for rows to use in select, update and delete
		 *@return int the offset of the rows to use
		 */
		function getOffset() {
			return $this->privateVars['offset'];
		}

		/**
		 * Returns the number of filters in this set
		 *@return int the number of filters in this set
		 */
		function getFilterCount() {
			return count($this->privateVars['filters']);
		}

		function getWhereCount() {
			$result = 0;
			for ($i = 0; $i < $this->getFilterCount(); $i++) {
				$filter = $this->getFilter($i);
				if (($filter['type'] == 'joinFilter') || ($filter['type'] == 'filterJoinFilter')) {
					if ($filter['joinView'] == 'inner') $result ++;
				} else {
					$result++;
				}
			}
			return $result;
		}

		/**
		 * Returns the data of the filter with the given index
		 *@param int $index the index of the filter to retrieve
		 *@param array An array containing data for the filter.
		 */
		function getFilter($index) {
			return $this->privateVars['filters'][$index];
		}

		function getStatus($alias, $joinTable = false) {
			$filterArray = array();
			$filterArray["type"] = -1;

			if($this->getFilterCount() == 0) return $filterArray;

			for($i = 0; $i < count($this->getFilterCount()); $i++) {
				$filter = $this->getFilter($i);
				if($filter['alias'] == $alias) {
					$filterArray["type"] = $filter['type'];
					$filterArray["value"] = $filter['value'];
					return $filterArray;
				}
			}
			return $filterArray;
		}
	}

	/**
	 * A set defining functions to execute on a dataset
	 *@author Matthijs Groen (matthijs at ivinity.nl)
	 *@version 1.0
	 */
	class FunctionDescriptions {

		/**
		 * A private set containing the functions
		 *@var array $privateVars
		 */
		var $privateVars;

		/**
		 * Constructor
		 */
		function FunctionDescriptions() {
			$this->privateVars = array(
				"functions" => array(), "groups" => array()
			);
		}

		/**
		 * Sets a count function on a column. The result is the number of
		 * rows in the resultset or group
		 *@param string $alias the column to count the rows of
		 *@param string $resultName the name in wich the results are available in the resultset
		 */
		function addCount($alias, $resultName) {
			$this->privateVars['functions'][] = array(
				'alias' => $alias,
				'resultname' => $resultName,
				'type' => 'count'
			);
		}

		/**
		 * Sets a countdistinct function on a column. The result is the number of
		 * rows in the resultset or group
		 *@param string $alias the column to count the rows of
		 *@param string $resultName the name in wich the results are available in the resultset
		 */
		function addCountDistinct($alias, $resultName) {
			$this->privateVars['functions'][] = array(
				'alias' => $alias,
				'resultname' => $resultName,
				'type' => 'countdistinct'
			);
		}

		/**
		 *@param bool $groupBy if you want to group the result by the resultname
		 **/
		function addJoinCount($alias, &$table, $resultName) {
			$this->privateVars['functions'][] = array(
				'alias' => $alias,
				'joinTable' => $table,
				'resultname' => $resultName,
				'type' => 'joincount'
			);
		}

		/**
		 * Calculates the average value of the given column
		 *@param string $alias the column to calculate the average of
		 *@param string $resultName the name in wich the results are available in the resultset
		 *@param bool $groupBy if you want to group the result by the resultname
		 */
		function addAverage($alias, $resultName) {
			$this->privateVars['functions'][] = array(
				'alias' => $alias,
				'resultname' => $resultName,
				'type' => 'avg'
			);
		}

		/**
		 * Sums up all the values of the given column
		 *@param string $alias the column to sum the rows of
		 *@param string $resultName the name in wich the results are available in the resultset
		 */
		function addSum($alias, $resultName) {
			$this->privateVars['functions'][] = array(
				'alias' => $alias,
				'resultname' => $resultName,
				'type' => 'sum'
			);
		}

		/**
		 * Gets the min value of the given column
		 *@param string $alias the column
		 *@param string $resultName the name in wich the results are available in the resultset
		 */
		function addMin($alias, $resultName) {
			$this->privateVars['functions'][] = array(
				'alias' => $alias,
				'resultname' => $resultName,
				'type' => 'min'
			);
		}

		/**
		 * Gets the max value of the given column
		 *@param string $alias the column
		 *@param string $resultName the name in wich the results are available in the resultset
		 */
		function addMax($alias, $resultName) {
			$this->privateVars['functions'][] = array(
				'alias' => $alias,
				'resultname' => $resultName,
				'type' => 'max'
			);
		}

		/**
		 * Retrieve the disctinct values of the given column
		 *@param string $alias the column to sum the rows of
		 *@param string $resultName the name in wich the results are available in the resultset
		 */
		function addDistinct($alias, $resultName) {
			$this->privateVars['functions'][] = array(
				'alias' => $alias,
				'resultname' => $resultName,
				'type' => 'distinct'
			);
		}

		/**
		 * Retrieve the normal values of the given column
		 *@param string $alias the column to get the value of
		 *@param string $resultName the name in wich the results are available in the resultset
		 */
		function addNormal($alias, $resultName) {
			$this->privateVars['functions'][] = array(
				'alias' => $alias,
				'resultname' => $resultName,
				'type' => 'normal'
			);
		}

		/**
		 * Retrieve the normal values of the given column
		 *@param string $alias the column to get the value of
		 *@param string $resultName the name in wich the results are available in the resultset
		 */
		function addNormalJoin(&$table, $alias, $resultName) {
			$this->privateVars['functions'][] = array(
				'table' => $table,
				'alias' => $alias,
				'resultname' => $resultName,
				'type' => 'normalJoin'
			);
		}

		function addNormalAliasJoin(&$table, $tableAlias, $alias, $resultName) {
			$this->privateVars['functions'][] = array(
				'table' => $table,
				'tableAlias' => $tableAlias,
				'alias' => $alias,
				'resultname' => $resultName,
				'type' => 'normalAliasJoin'
			);
		}

		function addDistinctAliasJoin(&$table, $tableAlias, $alias, $resultName) {
			$this->privateVars['functions'][] = array(
				'table' => $table,
				'tableAlias' => $tableAlias,
				'alias' => $alias,
				'resultname' => $resultName,
				'type' => 'distinctAliasJoin'
			);
		}

		function addAll(&$table) {
			$this->privateVars['functions'][] = array(
				'table' => $table,
				'type' => 'all'
			);
		}

		/**
		 * Retrieve the relevance of the textsearch of a record
		 *@param string $text the textquery to search
		 *@param string $searchType the type of textsearch to perform
		 *@param string $resultName the name in wich the results are available in the resultset
		 */
		function addTextRelevance($text, $searchType, $resultName) {
			$this->privateVars['functions'][] = array(
				'text' => $text,
				'searchType' => $searchType,
				'resultname' => $resultName,
				'type' => 'textrelevance'
			);
		}

		/**
		 * Executes a (mathematical) function and puts its value in alias
		 *@param string $resultName the name in wich the results are available in the resultset
		 *@param DataSelectFunction $function the function to execute
		 */
		function addFunction(&$function, $resultName) {
			$this->privateVars['functions'][] = array(
				'function' => $function,
				'resultname' => $resultName,
				'type' => 'function'
			);
		}

		/**
		 * Retrieve the disctinct values of the given column
		 *@param string $alias the column to sum the rows of
		 *@param string $resultName the name in wich the results are available in the resultset
		 */
		function addJoinDistinct($alias, &$table, $resultName) {
			$this->privateVars['functions'][] = array(
				'alias' => $alias,
				'joinTable' => $table,
				'resultname' => $resultName,
				'type' => 'joindistinct'
			);
		}

		/**
		 * Returns the number of functions in this set
		 *@return int the number of functions in this set
		 */
		function getFunctionCount() {
			return count($this->privateVars['functions']);
		}

		/**
		 * Returns the data of the function with the given index
		 *@param int $index the index of the function to retrieve
		 *@param array An array containing data for the function.
		 */
		function getFunction($index) {
			return $this->privateVars['functions'][$index];
		}

		function addGroupBy($alias) {
			$this->privateVars['groups'][] = $alias;
		}
	}

	class DataSelectFunction {

		var $functionName;
		var $parameters;

		/**
		* Creates a function that can be executed in the FunctionDescription class
		*@param string $functionName the name of this function, see the select_function defines
		* at the top of this file
		*/
		function DataSelectFunction($functionName) {
		  $this->parameters = array();
		  $this->functionName = $functionName;
		}

		/**
		* adds a direct value in this function
		*@parameter string $value adds a direct value in this function
		*/
		function addParameterString($value) {
		  $this->parameters[] = array("type" => "string", "data" => $value);
		}

		/**
		* adds a direct value in this function
		*@parameter int $value adds a direct value in this function
		*/
		function addParameterInt($value) {
		  $this->parameters[] = array("type" => "int", "data" => $value);
		}

		/**
		* adds a direct value in this function
		*@parameter DateTime $value adds a direct value in this function
		*/
		function addParameterDateTime($value) {
		  $this->parameters[] = array("type" => "datetime", "data" => $value);
		}

		/**
		* adds a direct value in this function
		*@parameter float $value adds a direct value in this function
		*/
		function addParameterFloat($value) {
		  $this->parameters[] = array("type" => "float", "data" => $value);
		}

		/**
		* adds a column to this function
		*@parameter string $alias adds a column in this function
		*/
		function addParameterColumn($alias, $table = null, $tableAlias="") {
		  $this->parameters[] = array(
					"type" => "column",
					"data" => $alias,
					"table" => $table,
					"tableAlias" => $tableAlias
			);
		}

		/**
		* adds a function to this function
		*@parameter DataSelectFunction $function adds a function in this function
		*/
		function addParameterFunction(&$function) {
		  $this->parameters[] = array("type" => "function", "data" => $function);
		}

		/**
		* Returns the name of this select function, eg. substring.
		* Check the select_function defines at the top of this file
		*@return string the name of this function
		*/
		function getName() {
			return $this->functionName;
		}

		/**
		* Returns the amount of parameters this function has
		*@return int the amount of parameters of this function
		*/
		function getParameterCount() {
			return count($this->parameters);
		}

		/**
		* returns the value of the parameter with the given index
		*@param int $index the index of the item to get the data from
		*@return mixed the parameter value. To check wich type the value is, use getParameterType
		*@see getParameterType
		*/
		function getParameterValue($index) {
			if (isSet($this->parameters[$index])) return $this->parameters[$index]["data"];
			return null;
		}

		function getParameterTable($index) {
			if (isSet($this->parameters[$index])) return $this->parameters[$index]["table"];
			return null;
		}

		function hasParameterTableAlias($index) {
			if (isSet($this->parameters[$index]) && ($this->parameters[$index]["tableAlias"] != "")) return true;
			return false;
		}

		function getParameterTableAlias($index) {
			if (isSet($this->parameters[$index])) return $this->parameters[$index]["tableAlias"];
			return null;
		}


		/**
		* returns the type of the parameter with the given index
		*@param int $index the index of the item to get the type from
		*@return string the parameter type
		*/
		function getParameterType($index) {
			if (isSet($this->parameters[$index])) return $this->parameters[$index]["type"];
			return false;
		}

	}

	class ColumnSorting {

		var $privateVars;

		function ColumnSorting() {
			$this->privateVars = array(
				"sorting" => array()
			);
		}

		/**
		 * Sets if a column needs to be ordered.
		 *@param string $alias the column to sort
		 *@param bool $ascending <code>true</code> for ascending, <code>false</code> for descending
		 */
		function addColumnSort($alias, $ascending=true) {
			$this->privateVars['sorting'][] = array(
				'alias' => $alias,
				'ascending' => $ascending,
				'type' => 'column'
			);
		}

		/**
		 * Sets if a column needs to be ordered.
		 *@param string $alias the column to sort
		 *@param bool $ascending <code>true</code> for ascending, <code>false</code> for descending
		 */
		function addAliasSort($resultName, $ascending=true) {
			$this->privateVars['sorting'][] = array(
				'resultname' => $resultName,
				'ascending' => $ascending,
				'type' => 'alias'
			);
		}

		function addJoinColumnSort(&$joinTable, $alias, $ascending=true) {
			$this->privateVars['sorting'][] = array(
				'alias' => $alias,
				'table' => $joinTable,
				'ascending' => $ascending,
				'type' => 'joincolumn'
			);
		}

		/**
		 * Returns the number of sort settings in this set
		 *@return int the number of sort settings in this set
		 */
		function getSortCount() {
			return count($this->privateVars['sorting']);
		}

		/**
		 * Returns the data of the sort setting with the given index
		 *@param int $index the index of the sort setting to retrieve
		 *@param array An array containing data for sorting.
		 */
		function getSorting($index) {
			return $this->privateVars['sorting'][$index];
		}
	}


	/**
	 * This class is a container to store a list of mutations that
	 * must be executed on a data selection.
	 * eg. Queries like: UPDATE table SET column = column + 1 where column > 2;
	 *@author Matthijs Groen (matthijs at ivinity.nl)
	 *@version 1.0
	 */
	class DataMutation {

		var $privateVars;

		function DataMutation() {
			$this->privateVars['mutations']	= array();
			$this->privateVars['onlySet'] = false;
		}

		function hasOnlySet() {
			return $this->privateVars['onlySet'];
		}

		function isOnlySet($index) {
			return $this->privateVars['mutations'][$index]['onlySet'];
		}

		function getAlias($index) {
			return $this->privateVars['mutations'][$index]['alias'];
		}

		/**
		 * Adds the specified value to the column of the given alias
		 *@param string $alias the column alias to add the value to
		 *@param mixed $value the value to add
		 *@param boolean $onlySet if true, only the not null values will be affected.
		 */
		function addToColumn($alias, $value, $onlySet = false) {
			$this->privateVars['mutations'][] = array(
				'alias' => $alias,
				'value' => $value,
				'type' =>	'columnAdd',
				'onlySet' => $onlySet
			);
			if ($onlySet) {
				$this->privateVars['onlySet'] = true;
			}
		}

		/**
		 * Subtracts the specified value to the column of the given alias
		 *@param string $alias the column alias to subtract the value from
		 *@param mixed $value the value to subtract
		 *@param boolean $onlySet if true, only the not null values will be affected.
		 */
		function subtractFromColumn($alias, $value, $onlySet = false) {
			$this->privateVars['mutations'][] = array(
				'alias' => $alias,
				'value' => $value,
				'type' =>	'columnSub',
				'onlySet' => $onlySet
			);
			if ($onlySet) {
				$this->privateVars['onlySet'] = true;
			}
		}

		/**
		 * Column equals given value
		 *@param string $alias the column alias to subtract the value from
		 *@param mixed $value the value to subtract
		 *@param boolean $onlySet if true, only the not null values will be affected.
		 */
		function setEquals($alias, $value, $onlySet = false) {
			$this->privateVars['mutations'][] = array(
				'alias' => $alias,
				'value' => $value,
				'type' =>	'equals',
				'onlySet' => $onlySet
			);
			if ($onlySet) {
				$this->privateVars['onlySet'] = true;
			}
		}

		/**
		 * Column equals given value
		 *@param string $alias the column alias to subtract the value from
		 *@param mixed $value the value to subtract
		 *@param boolean $onlySet if true, only the not null values will be affected.
		 */
		function setNull($alias) {
			$this->privateVars['mutations'][] = array(
				'alias' => $alias,
				'type' =>	'null',
			);
		}

		/**
		 * Sets the max value for a column. All values in the column exceeding the max value
		 * will be set to the max value.
		 *@param string $alias the column alias to set to max
		 *@param mixed $value the value that defines the max value
		 *@param boolean $onlySet if true, only the not null values will be affected.
		 */
		function setColumnMax($alias, $value, $onlySet = false) {
			$this->privateVars['mutations'][] = array(
				'alias' => $alias,
				'value' => $value,
				'type' =>	'columnMax',
				'onlySet' => $onlySet
			);
			if ($onlySet) {
				$this->privateVars['onlySet'] = true;
			}
		}

		/**
		 * Sets the min value for a column. All values in the column less than the min value
		 * will be set to the min value.
		 *@param string $alias the column alias to set to min
		 *@param mixed $value the value that defines the min value
		 *@param boolean $onlySet if true, only the not null values will be affected.
		 */
		function setColumnMin($alias, $value, $onlySet = false) {
			$this->privateVars['mutations'][] = array(
				'alias' => $alias,
				'value' => $value,
				'type' =>	'columnMin',
				'onlySet' => $onlySet
			);
			if ($onlySet) {
				$this->privateVars['onlySet'] = true;
			}
		}

		/**
		 * Returns the number of mutations in this set
		 *@return int the number of mutations in this set
		 */
		function getMutationCount() {
			return count($this->privateVars['mutations']);
		}

		/**
		 * Returns the data of the mutation with the given index
		 *@param int $index the index of the mutation to retrieve
		 *@param array An array containing data for mutating data.
		 */
		function getMutation($index) {
			return $this->privateVars['mutations'][$index];
		}

		function mutate(&$dataRow) {
			$table = $dataRow->getTable();
			foreach ($this->privateVars['mutations'] as $mutation) {
				$alias = $mutation['alias'];
				if ($table->hasAlias($alias)) {
					switch($mutation['type']) {
						case 'equals':
							if (($mutation['onlySet'] && !$dataRow->isNull($alias)) || (!$mutation['onlySet']))
								$dataRow->setValue($alias, $mutation['value']);
							break;
						case 'null':
								$dataRow->setNull($alias);
							break;
						case 'columnAdd':
							if (($mutation['onlySet'] && !$dataRow->isNull($alias)) || (!$mutation['onlySet']))
								$dataRow->setValue($alias, $dataRow->getValue($alias) + $mutation['value']);
							break;
						case 'columnSub':
							if (($mutation['onlySet'] && !$dataRow->isNull($alias)) || (!$mutation['onlySet']))
								$dataRow->setValue($alias, $dataRow->getValue($alias) - $mutation['value']);
							break;
						case 'columnMax':
							if ($dataRow->isNull($alias)) {
								if (!$mutation['onlySet']) {
									$dataRow->setValue($alias, $mutation['value']);
								}
							} else {
								$value = $dataRow->getValue($alias);
								if (is_numeric($value)) {
									if ($value > $mutation['value']) $dataRow->setValue($alias, $mutation['value']);
								}
								if (is_Object($value) && (get_Class($value) == "datetime")) {
									if ($value->after($mutation['value'])) $dataRow->setValue($alias, $mutation['value']);
								}
							}
							break;
						case 'columnMin':
							if ($dataRow->isNull($alias)) {
								if (!$mutation['onlySet']) {
									$dataRow->setValue($alias, $mutation['value']);
								}
							} else {
								$value = $dataRow->getValue($alias);
								if (is_numeric($value)) {
									if ($value < $mutation['value']) $dataRow->setValue($alias, $mutation['value']);
								}
								if (is_Object($value) && (get_Class($value) == "datetime")) {
									if ($value->before($mutation['value'])) $dataRow->setValue($alias, $mutation['value']);
								}
							}
							break;
					}
				}
			}
		}

	}

	class MultiLanguageTable extends DataTable {

		function MultiLanguageTable($database) {
			$this->DataTable($database, "ivmultilanguage");

			$this->defineInt("ID", "ID", false);
			$this->setPrimaryKey("ID");
			$this->setAutoIncrement("ID");

			$this->defineText("table", "table", 50, false);
			$this->defineText("recordID", "recordID", 20, false);
			$this->defineText("language", "language", 4, false);
			$this->defineText("recordTexts", "recordTexts", 500, false);

			$this->defineIndex("language");
			$this->defineIndex("table");
			$this->defineIndex("recordID");
		}
	}


	class DatabaseCache {
		var $cache;

		function DatabaseCache() {
			$this->cache = array();
		}

		function setCache(&$row) {
			if(is_object($row)) {
				$table = $row->getTable();
				if(!isSet($this->cache[$table->getTableName()]))
					$this->cache[$table->getTableName()] = array();
				if(!isSet($this->cache[$table->getTableName()][$row->getValue($table->getPrimaryKey())]))
					$this->cache[$table->getTableName()][$row->getValue($table->getPrimaryKey())] = array();

				$this->cache[$table->getTableName()][$row->getValue($table->getPrimaryKey())] = $row;
				//$row->setValue("cellName", "fromCache");
			}
		}

		function hasCache($tableName,$rowID) {
			return is_object($this->getCache($tableName,$rowID));
		}

		function getCache($tableName,$rowID) {
			if(is_numeric($rowID)) {
				if(isSet($this->cache[$tableName]) && isSet($this->cache[$tableName][$rowID])) {
					$row = $this->cache[$tableName][$rowID];
					return $row;
				}
			}
			return null;
		}

		function deleteFromCache(&$row) {
			if(is_object($row)) {
				$table = $row->getTable();
				if(isSet($this->cache[$table->getTableName()])) {
					unSet($this->cache[$table->getTableName()][$row->getValue($table->getPrimaryKey())]);
				}
			}
		}
	}

	class DataRowCopyHelper {
		var $data = array();
		var $instancedData = array();

		function storeCopyInfo(&$original, &$rowCopy, $instance = "") {
			$table = $original->getTable();
			$key = $table->getPrimaryKey();
			if ($instance == "")
				$this->data[$table->getTableName().$original->getValue($key)] = $rowCopy->getValue($key);
			else {
				if (!isSet($this->instancedData[$instance])) $this->instancedData[$instance] = array();
				$this->instancedData[$instance][$table->getTableName().$original->getValue($key)] = $rowCopy->getValue($key);
			}
		}

		function getCopy(&$table, $originalID, $instance = "") {
			if ($instance == "") {
				if(isSet($this->data[$table->getTableName().$originalID])) {
					$newID = $this->data[$table->getTableName().$originalID];
					return $table->getRowByKey($newID);
				}
			} else {
				if (!isSet($this->instancedData[$instance])) return false;
				if(isSet($this->instancedData[$instance][$table->getTableName().$originalID])) {
					$newID = $this->instancedData[$instance][$table->getTableName().$originalID];
					return $table->getRowByKey($newID);
				}
			}
			return false;
		}

		function cleanup($instance = "") {
			if ($instance == "") {
				$this->data = array();
			} else unset($this->instancedData[$instance]);
		}
	}

	class DatabaseQuery {

	}

	class SelectQuery extends DatabaseQuery {

		var $table;
		var $functions;
		var $filter;
		var $sorting;

		function SelectQuery() {
			$this->filter = new DataFilter();
			$this->sorting = new ColumnSorting();
			$this->functions = new FunctionDescriptions();
		}

		function setTable(&$table) {
			$this->table = $table;
		}

		function setFunctions(&$functionDescriptions) {
			$this->functions = $functionDescriptions;
		}

		function setFilter(&$dataFilter) {
			$this->filter = $dataFilter;
		}

		function setSorting(&$sorting) {
			$this->sorting = $sorting;
		}

		function getTable() {
			return $this->table;
		}

		function getFilter() {
			return $this->filter;
		}

		function getFunctions() {
			return $this->functions;
		}

		function getSorting() {
			return $this->sorting;
		}

	}

	class UnionQuery extends DatabaseQuery {

		var $baseSelect;
		var $unions;
		var $order;

		function UnionQuery($select, $unionSelect, $unionType=union_type_all) {
			$this->unions = array();
			$this->baseSelect = $select;
			$this->unions[] = array("select" => $unionSelect, "type" => $unionType);
			$this->order = new ColumnSorting();
		}

		function addUnion($unionSelect, $unionType=union_type_all) {
			$this->unions[] = array("select" => $unionSelect, "type" => $unionType);
		}

		function getUnionCount() {
			return count($this->unions);
		}

		function setSorting(&$sorting) {
			$this->order = $sorting;
		}

		function getSorting() {
			return $this->order;
		}

		function getBaseSelect() {
			return $this->baseSelect;
		}

		function getUnionSelect($index) {
			return $this->unions[$index]["select"];
		}

		function getUnionType($index) {
			return $this->unions[$index]["type"];
		}

	}

	$GLOBALS['databaseCache'] = new DatabaseCache();
	$GLOBALS['dataRowCopyHelper'] = new DataRowCopyHelper();
?>
