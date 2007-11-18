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
	 * The superclass Database
	 */
	require_once($libraryClassDir."Language.class.php");
	require_once($libraryClassDir."Database.class.php");
	require_once($libraryClassDir."MySQLResultset.class.php");
	require_once($libraryClassDir."LibDateTime.class.php");

	$GLOBALS['queriesExecuted'] = 0;
	/**
	 * MySQLDatabase is a class to create a MySQLdatabase connection and execute queries
	 */
	class MySQLDatabase extends Database {

		/**
		 * Instantiates a Database object
		 *@param string $host the url of the host
		 *@param string $database the name of the database to be selected
		 *@param string $user the username of the user
		 *@param string $password the password of the user
		 */
		function MySQLDatabase($host, $database, $user, $password, $port=3306) {
			$this->Database($host, $database, $user, $password, $port);
			$this->privateVars["version3"] = false;
			$this->privateVars["version"] = 4;
			$this->privateVars["textEncoding"] = false;
		}

		function setVersion3() { $this->privateVars["version3"] = true; $this->privateVars["version"] = 3;}
		function isVersion3() {	return $this->privateVars["version3"]; }

		function setVersion($version) { $this->privateVars["version"] = $version; }
		function getVersion() { return $this->privateVars["version"]; }

		function setTextEncoding($textEncoding) {
			$this->privateVars["textEncoding"] = $textEncoding;
		}

		/**
		 * Connect to an MySQL database
		 *@return bool whether the connection was succesfull
		 */
		function connect() {
			$host = $this->privateVars['host'];
			if ($this->privateVars['port'] != 3306) $host .= ":" . $this->privateVars['port'];
			$mysql_link = mysql_connect($host,	$this->privateVars['user'], $this->privateVars['password'], true);
			if($mysql_link && mysql_select_db($this->privateVars['database'], $mysql_link)) {
				$this->privateVars['link'] = $mysql_link;
				$this->privateVars['connected'] = true;
				if($this->getVersion() >= 5) {
					//mysql_query('SET character_set_results="utf8"',$mysql_link);
				}
				if($this->getVersion() >= 4) {
					//mysql_query("SET NAMES 'utf8'",$mysql_link);
					//mysql_query("CHARACTER SET utf8 COLLATE utf8_general_ci;",$mysql_link);
				}
				return ($mysql_link);
			}
			return $this->privateVars['connected'];
		}


		/**
		 * Disconnects to the MySQL database, if there is a connection
		 *@return bool whether the disconnection was succesfull
		 */
		function disconnect() {
			if($this->isConnected()) {
				$this->privateVars['link'] = null;
				$this->privateVars['connected'] = false;
			}
			return !$this->privateVars['connected'];
		}

		/**
		 * Executes a query and returns the MySQLResultset
		 *@param string $query the sql-query
		 *@return MySQLResultset the resultset, FALSE will be returned if an error occured
		 */
		function executeQuery($query) {
			//print $query;
			if($this->isConnected()) {
				if (is_Object($query) && is_a($query, "databasequery")) {
					$sqlQuery = $this->buildQuery($query);
					//print "<B>Nieuwe Query: ".$sqlQuery."</B><BR />\n";
					//return false;
				}	else {
					$sqlQuery = $query;
				}

				$result = @mysql_query($sqlQuery, $this->privateVars['link']);
				$GLOBALS['queriesExecuted']++;
				if($result) {
					global $language;
					$resultset = new MySQLResultset($result, $this, $sqlQuery);
					//$resultset->setLanguage($language->getDictionary());
					$resultset->setInsertID(mysql_insert_ID($this->privateVars['link']));
					return $resultset;
				} else {
					print "(<b>".$sqlQuery."</b>) - ";
					trigger_error("Query niet gelukt! (<b>".mysql_error($this->privateVars['link'])."</b>)", E_USER_ERROR);
				}
			}
			return false;
		}

		function buildQuery($query) {
			$queryType = get_class($query);
			switch($queryType) {
				case "selectquery":
					$table = $query->getTable();
					$functions = $query->getFunctions();
					$filter = $query->getFilter();
					$sorting = $query->getSorting();
					$result = $this->getSelectQuery($table, $functions, $filter, $sorting);
					return $result;
				case "unionquery":
					$baseQuery = $query->getBaseSelect();
					$orderTable = $baseQuery->getTable();

					$result = "( " . $this->buildQuery($baseQuery);
					for ($i = 0; $i < $query->getUnionCount(); $i++) {
						$type = $query->getUnionType($i);
						$result .= ") UNION ";
						switch($type) {
							case union_type_all: $result .= " ALL "; break;
							case union_type_distinct: $result .= " DISTINCT "; break;
						}
						$result .= "( ";
						$unionQuery = $query->getUnionSelect($i);
						$result .= $this->buildQuery($unionQuery);
					}
					$result .= " ) ";
					$columnSorting = $query->getSorting();
					// ORDER BY CLAUSE
					if ($columnSorting->getSortCount()) {
						$orderClause = $this->buildOrderClause($orderTable, $columnSorting);
						$result .= $orderClause;
					}
					return $result;
			}

		}

		/**
		 * Insert the given rowdata into the given table using the tablemetadata
		 *@param DataTable $table the table metadata
		 *@param DataRow $row Rowdata
		 *@return Resultset the resultset
		 *@return MySQLResultset the resultset, FALSE will be returned if an error occured
		 */
		function insertRow(&$table, &$row) {
			$insertQuery = $this->getInsertQuery($table, $row);
			$insResult = $this->executeQuery($insertQuery);
			// compare languages.
			if (is_Object($insResult)) {
				global $language;
				$primaryKey = $insResult->getInsertID();
				if ($table->isMultiLanguage()) {
					$langs = $row->getSupportedLanguages();
					foreach($langs as $langCode) {
						if (($langCode != $this->getDefaultLanguage()) && ($row->isLanguageDataChanged($langCode))) {
							$langData = $row->getLanguageData($langCode);
							if ($langData !== false) {
								$mlTable = new MultiLanguageTable($this);
								$newMlRow = $mlTable->addRow();
								$newMlRow->setValue("table", $table->getTableName());
								$newMlRow->setValue("recordID", $primaryKey);
								$newMlRow->setValue("language", $langCode);
								$newMlRow->setValue("recordTexts", serialize( $langData ));
								$newMlRow->store();
							}
						}
					}
				}
			}
			return $insResult;
		}

		function getInsertQuery(&$table, &$row) {
			$columns = array();
			$values = array();
			$columnCount = $table->getColumnCount();
			//print $table->getPrimaryKey();
			for ($i = 0; $i < $columnCount; $i++) {
				$alias = $table->getAlias($i);
				if ((!$row->isNull($alias)) || (!$table->allowNull($alias))) {
					if ($alias != $table->getPrimaryKey()) {
						$columns[] = $table->getColumnName($alias);
						$values[] = $this->getMySQLValue($table, $row, $alias);
					} else if (!$row->isNull($alias)) {
						$columns[] = $table->getColumnName($alias);
						$values[] = $this->getMySQLValue($table, $row, $alias);
					}
				}
			}
			$blobCount = $table->getBlobColumnCount();
			for ($i = 0; $i < $blobCount; $i++) {
				$alias = $table->getBlobAlias($i);
				if ((!$row->isNull($alias)) || (!$table->allowNull($alias))) {
					if ($alias != $table->getPrimaryKey()) {
						$columns[] = $table->getColumnName($alias);
						$values[] = $this->getMySQLValue($table, $row, $alias);
					}
				}
			}

			$insertQuery = sprintf("INSERT INTO `%s` (`%s`) VALUES (%s)",
				$table->getTableName(),
				implode("`, `", $columns),
				implode(", ", $values));
			if (count($columns) == 0) $insertQuery = sprintf("INSERT INTO `%s` () VALUES ()", $table->getTableName());
			//print($insertQuery);
			return $insertQuery;
		}

		/**
		 * Update the given rowdata into the given table using the tablemetadata
		 *@param DataTable $table the table metadata
		 *@param DataRow $row Rowdata
		 *@return MySQLResultset the resultset, FALSE will be returned if an error occured
		 */
		function updateRow(&$table, &$row) {
			$updates = array();
			$multiUpdates = array();
			$writeMulti = $table->isMultiLanguage();
			$columnCount = $table->getColumnCount();

			for ($i = 0; $i < $columnCount; $i++) {
				$alias = $table->getAlias($i);
				$column = $table->getColumnName($alias);
				$columnType = $table->getColumnType($alias);
				if ($row->isChanged($alias)) {
					$column = $table->getColumnName($alias);
					$value = $this->getMySQLValue($table, $row, $alias, true);
					$updates[] = sprintf("`%s` = %s", $column, $value);
				}
			}
			$blobCount = $table->getBlobColumnCount();
			for ($i = 0; $i < $blobCount; $i++) {
				$alias = $table->getBlobAlias($i);
				if ($row->isChanged($alias)) {
					$column = $table->getColumnName($alias);
					$value = $this->getMySQLValue($table, $row, $alias, true);
					$updates[] = sprintf("`%s` = %s", $column, $value);
				}
			}
			$primaryKey = $this->getMySQLValue($table, $row, $table->getPrimaryKey());
			$updateQuery = sprintf("UPDATE `%s` SET %s WHERE `%s`=%s",
				$table->getTableName(),
				implode(", ", $updates),
				$table->getColumnName($table->getPrimaryKey()),
				$primaryKey);
			//print($updateQuery);

			$multiWritten = false;
			if ($writeMulti) {
				$langCodes = $row->getSupportedLanguages();
				foreach($langCodes as $langCode) {
					if (($langCode != $this->getDefaultLanguage()) && ($row->isLanguageDataChanged($langCode))) {

						$multiWritten = true;
						$langData = $row->getLanguageData($langCode);
						if ($langData !== false) {
							$mlTable = new MultiLanguageTable($this);

							$oldID = $row->getLanguageStorageID($langCode);
							$hasRow = false;
							if ($oldID !== false) {
								$newMlRow = $mlTable->getRowByKey($oldID);
								if (is_Object($newMlRow)) $hasRow = true;
							}
							if (!$hasRow) {
								$newMlRow = $mlTable->addRow();
							}
							$newMlRow->setValue("table", $table->getTableName());
							$newMlRow->setValue("recordID", $row->getValue($table->getPrimaryKey()));
							$newMlRow->setValue("language", $langCode);
							$newMlRow->setValue("recordTexts", serialize( $langData ));
							$newMlRow->store();
						}
					}
				}

				/*
				$deleteQuery = sprintf("DELETE FROM `ivmultilanguage` WHERE `language`='%s' AND `table`='%s' AND ".
					"`recordID`=%s", $rowLanguage, $table->getTableName(), $primaryKey);
				$this->executeQuery($deleteQuery);
				$insertQuery = sprintf("INSERT INTO `ivmultilanguage`(`language`,`table`,`recordID`,`recordTexts`) ".
					"VALUES('%s', '%s', %s, '%s')", $rowLanguage, $table->getTableName(), $primaryKey,
					addSlashes(serialize($multiUpdates)));
				$this->executeQuery($insertQuery);
				*/
			}
			//print $updateQuery;
			if (count($updates) == 0) return $multiWritten;
			return $this->executeQuery($updateQuery);
		}

		/**
		 * Selects rows from a table using a filter, and specified column sorting
		 *@param DataTable $table the table to select rows from
		 *@param DataFilter $filter a list of requirements the data must meet
		 *@param ColumnSorting $columnSorting a list of sort settings
		 *@return Resultset the resultset, or FALSE if an error occured
		 */
		function selectRows(&$table, &$filter, &$columnSorting) {
			//$tableList = $filter->getJoinedTableNames();
			//if (!in_Array($table->getTableName(), $tableList)) $tableList[] = $table->getTableName();
			$selectQuery = sprintf("SELECT %s FROM `%s`",
				$this->getAllFields($table),
				$table->getTableName()
				//implode("`, `", $tableList)
				);
			$joinClause = $this->buildJoins($table, $filter);
			$selectQuery .= $joinClause;

			if ($filter->getFilterCount() > 0) {
				$whereClause = $this->buildWhereClause($table, $filter);
				$selectQuery .= $whereClause;
			}
			if ($columnSorting->getSortCount()) {
				$orderClause = $this->buildOrderClause($table, $columnSorting);
				$selectQuery .= $orderClause;
			}
			$selectQuery .= sprintf(" LIMIT %s, %s", $filter->getOffset(), $filter->getLimit());
			//print ($selectQuery);
			$result = $this->executeQuery($selectQuery);
			if (is_object($result)) {
				$result->setTable($table);
			}
			return $result;
		}

		/**
		 * Selects a row with the given key
		 *@param DataTable $table the table to select the from
		 *@param mixed $key the key value. The key is a value from the type of
		 * the keycolumn of the table. eg. If the primary key is a date, a timestamp
		 * should be used
		 *@return Resultset the resultset, or FALSE if an error occured
		 */
		function selectRow(&$table, $key) {
			$selectQuery = sprintf("SELECT %s FROM `%s`%s WHERE `%s`.`%s`='%s'",
				$this->getAllFields($table),
				$table->getTableName(),
				$this->buildJoins($table, new DataFilter()), // for multilanguage records
				$table->getTableName(),
				$table->getColumnName($table->getPrimaryKey()),
				addSlashes($key));

			$result = $this->executeQuery($selectQuery);
			if (is_object($result)) {
				$result->setTable($table);
			}
			return $result;
		}

		/**
		 * Removes rows from the given table using the given datafilter.
		 * all rows that pass the filter will be removed.
		 *@param DataTable $table the table to delete rows from
		 *@param DataFilter $filter a list of requirements the data must meet
		 *@return Resultset the resultset, or FALSE if an error occured
		 */
		function deleteRows(&$table, &$filter) {
			// Only supported starting from MySQL 4.0.0
			if ($this->privateVars['version3'] == false) {
				$tableList = $filter->getJoinedTableNames();
				if (!in_Array($table->getTableName(), $tableList)) $tableList[] = $table->getTableName();
				$deleteQuery = sprintf("DELETE `%s` FROM `%s`",
					$table->getTableName(),
					implode("`, `", $tableList));
				if ($filter->getFilterCount() > 0) {
					$whereClause = $this->buildWhereClause($table, $filter);
					$deleteQuery .= $whereClause;
				} else {
					$deleteQuery = sprintf("TRUNCATE TABLE `%s`",
						$table->getTableName());
				}
				$result = $this->executeQuery($deleteQuery);
				if (is_object($result)) {
					$result->setTable($table);
				}
				return $result;
			}

			if ($filter->hasJoins() == false) {
				// -- this way is also supported by version 3.23
				$deleteQuery = sprintf("DELETE FROM `%s`",
					$table->getTableName()
					);
				if ($filter->getFilterCount() > 0) {
					$whereClause = $this->buildWhereClause($table, $filter);
					$deleteQuery .= $whereClause;
				} else {
					$deleteQuery = sprintf("TRUNCATE TABLE `%s`",
						$table->getTableName());
				}
				$result = $this->executeQuery($deleteQuery);
				if (is_object($result)) {
					$result->setTable($table);
				}
				return $result;
			}
			// -- this causes slow deletes under 3.23
			$table->selectRows($filter, new ColumnSorting());
			while ($row = $table->getRow()) {
				$row->delete();
			}
			return false;
		}

		/**
		 * Executes data functions on a table on the data with the given requirements
		 *@param DataTable $table the table wich data to use
		 *@param FunctionDescriptions $functionDescriptions the functions to execute
		 *@param DataFilter $filter a list of requirements the data must meet
		 *@param ColumnSorting $columnSorting a list of sort settings
		 *@return Resultset the resultset, or FALSE if an error occured
		 */
		function executeTableFunctions(&$table, &$functionDescriptions, &$filter, &$columnSorting, $debug = false) {
			$selectQuery = $this->getSelectQuery($table, $functionDescriptions, $filter, $columnSorting, $debug);
			if ($selectQuery === false) return false;
			$result = $this->executeQuery($selectQuery);
			if (is_object($result)) {
				$result->setTable($table);
			}
			return $result;
		}

		function getSelectQuery(&$table, &$functionDescriptions, &$filter, &$columnSorting, $debug = false) {
			//$tableList = $filter->getJoinedTableNames();
			//if (!in_Array($table->getTableName(), $tableList)) $tableList[] = $table->getTableName();
			$functionClause = $this->buildFunctionClause($table, $functionDescriptions);
			if ($functionClause === false) return false;

			$selectQuery = sprintf("SELECT %s FROM `%s`",
				$functionClause,
				$table->getTableName()
				);
			$joinClause = $this->buildJoins($table, $filter);
			$selectQuery .= $joinClause;

			// WHERE CLAUSE
			if ($filter->getWhereCount() > 0) {
				$whereClause = $this->buildWhereClause($table, $filter);
				$selectQuery .= $whereClause;
			}

			// GROUP BY CLAUSE
			$selectQuery .= $this->buildGroupByClause($table, $functionDescriptions);

			// ORDER BY CLAUSE
			if ($columnSorting->getSortCount()) {
				$orderClause = $this->buildOrderClause($table, $columnSorting);
				$selectQuery .= $orderClause;
			}

			// LIMIT
			if (($filter->getOffset() != 0) || ($filter->getLimit() != max_limit))
				$selectQuery .= sprintf(" LIMIT %s, %s", $filter->getOffset(), $filter->getLimit());

			if ($debug) { print $selectQuery; return false; }
			return $selectQuery;
		}


		/**
		 * Executes data mutations on this table with the data that meet the given requirements.
		 *@param DataTable $table the table wich data to use
		 *@param DataMutation $mutationDescriptions the mutations to execute
		 *@param DataFilter $filter a list of requirements the data must meet
		 *@param bool true for debug print of the query and no execution, true for execution and no output
		 */
		function executeTableMutations(&$table, &$mutationDescriptions, &$filter, $debug=true) {
			if (!$mutationDescriptions->hasOnlySet()) {
				$updateQuery = sprintf("UPDATE `%s`",	$table->getTableName());
				if ($mutationDescriptions->getMutationCount() > 0) {
					$updateClause = $this->buildUpdateClause($table, $mutationDescriptions);
					$updateQuery .= $updateClause;
				}
				if ($filter->getFilterCount() > 0) {
					$whereClause = $this->buildWhereClause($table, $filter);
					$updateQuery .= $whereClause;
				}
				$updateQuery .= sprintf(" LIMIT %s", $filter->getLimit());

				if ($debug) { print $updateQuery; return false; }
				else return $this->executeQuery($updateQuery);
			} else {
				// Handle all mutations one by one,
				// And add an "column IS NOT NULL" if the setOnly is true
				$nrMutations = $mutationDescriptions->getMutationCount();
				if ($nrMutations > 0) {
					for ($i = 0; $i < $nrMutations; $i++) {
						$updateQuery = sprintf("UPDATE `%s`",	$table->getTableName());

						$updateClause = $this->buildUpdateClause($table, $mutationDescriptions, $i);
						$updateQuery .= $updateClause;

						if ($filter->getFilterCount() > 0) {
							if ($mutationDescriptions->isOnlySet($i)) {
								$excludefilter = new DataFilter();
								$excludefilter->addNotNull($mutationDescriptions->getAlias($i));
								$excludefilter->addDataFilter($filter);
								$whereClause = $this->buildWhereClause($table, $excludefilter);
							} else
								$whereClause = $this->buildWhereClause($table, $filter);

							$updateQuery .= $whereClause;
						}
						$updateQuery .= sprintf(" LIMIT %s", $filter->getLimit());

						if ($debug) { print $updateQuery."<br />\n";  }
						else $this->executeQuery($updateQuery);
					}
					if ($debug) return false;
				}
			}
		}

		/**
		 * Executes a query with a join on multiple tables and get all the columns of multiple tables
		 *@param array $tables an array Containing DataTables to extract. The first table is the
		 *  main table where the filter will be executed on
		 *@param DataFilter $filter a list of requirements the data must meet
		 *@param ColumnSorting $columnSorting a list of sort settings
		 *@return Resultset the resultset, or FALSE if an error occured
		 */
		function selectMultiTableRows($tables, &$filter, &$columnSorting) {
			$tableList = $filter->getJoinedTableNames();
			$selectList = array();
			for ($i = 0; $i < count($tables); $i++) {
				$table = $tables[$i];
				$tableList[] = $table->getTableName();
				$selectList[] = $this->getAllFields($table);
			}
			$tableList = array_unique($tableList);
			$selectQuery = sprintf("SELECT %s FROM `%s`",
				implode(", ", $selectList),
				$tables[0]->getTableName()
				);
			$joinClause = $this->buildJoins($tables[0], $filter);
			$selectQuery .= $joinClause;

			if ($filter->getFilterCount() > 0) {
				$whereClause = $this->buildWhereClause($tables[0], $filter);
				$selectQuery .= $whereClause;
			}
			if ($columnSorting->getSortCount()) {
				$orderClause = $this->buildOrderClause($tables[0], $columnSorting);
				$selectQuery .= $orderClause;
			}
			$selectQuery .= sprintf(" LIMIT %s, %s", $filter->getOffset(), $filter->getLimit());
			//print ($selectQuery);
			$result = $this->executeQuery($selectQuery);
			if (is_object($result)) {
				$result->setTables($tables);
			}
			return $result;
		}

		/**
		 * Gets the value of a column with the given alias
		 * from the table with the given row.
		 *@param DataTable $table the table where the row belongs
		 * to. This is used to get the MetaData info of the specified column
		 *@param DataRow $row the row containing the data where the data of the
		 * column with the given alias needs to be extracted
		 *@param string $alias the aliasname of the column to get the data form in
		 * MySQL Query string form
		 *@return string the value of the column of the given row in MySQL Query string form
		 */
		function getMySQLValue(&$table, &$row, $alias, $storeValue = false) {
			//$type = $table->getColumnType($alias);
			//print "ta";
			if ($row->isNull($alias)) {
				if ((!$table->allowNull($alias)) && ($table->hasDefaultValue($alias))) {
					$value = $table->getDefaultValue($alias);
					return $this->valueToString($table, $alias, $value, $storeValue);
				}
				return "NULL";
			}
			//print "da - ";
			$value = $row->getValue($alias);
			return $this->valueToString($table, $alias, $value, $storeValue);
		}

		/**
		 * Converts the given value to its MySQL Query string form,
		 * handling the value like the given type
		 *@access private
		 *@param string $type the datatype of the value
		 *@param string $value the value to convert to string form
		 *@return string the value in Query string form
		 */
		function valueToString(&$table, $alias, $value, $storeValue = false) {
			$type = $table->getColumnType($alias);
			switch($type) {
				case 'date':
					if (!is_Object($value)) {
						$varType = getType($value);
						$varValue = "unknown";
						switch($varType) {
							case "string": $varValue = '"'.$value.'"';
						}
						trigger_error("DateTime geen object for '".$table->getTableName()."'->'".$alias."'! <b>(</b>".
							$varType.": ".$varValue."<b>)</b>", E_USER_ERROR);
					}

					$value = $value->toString("Y-m-d H:i:s");
					/* MySQL datetime format
						see: http://www.mysql.com/doc/en/DATETIME.html
					 */
					break;
				case 'time': $value = $value->toString("H:i:s");
					/* MySQL datetime format
						see: http://www.mysql.com/doc/en/DATETIME.html
					 */
					break;
				case 'bool': $value = ($value) ? "yes" : "no"; break;
				case 'enum': $value = $table->getEnumStringValue($alias, $value); break;
			}
			if (is_object($value) || is_array($value)) {
				//print ("Object! (MySQLDatabase, ".__LINE__.") :".get_class($value))."<br />\n";
				return "''";
			} else {
				$value = "'".addSlashes($value)."'";
				if ($this->privateVars["textEncoding"] !== false) {
					$value = "_".$this->privateVars["textEncoding"].$value."";
				}

				return $value;
			}
		}

		/**
		 * builds the where clause part of the query
		 * using the given table and data specifications
		 *@access private
		 *@param DataTable $table the table to get column meta data from
		 *@param DataFilter $filter the list containing data requirements
		 *@return string the where clause of the query
		 */
		function buildWhereClause(&$table, &$filter, $tableAlias='') {
			return " WHERE " . $this->parseDataFilter($table, $filter, $tableAlias);
		}

		function parseDataFilter(&$table, &$filter, $useTableName = "") {
			$filterResult = "";
			$tableName = "`".$table->getTableName()."`";
			if ($useTableName != "") $tableName = $useTableName;
			$nrFilters = $filter->getFilterCount();

			$results = array();

			$glue = "";
			switch($filter->getMode()) {
				case 'and': $glue = ' AND '; break;
				case 'or': $glue = ' OR '; break;
			}

			for ($i = 0; $i < $nrFilters; $i++) {
				$requirement = $filter->getFilter($i);
				// Parse each requirement differently
				$filterResult = "";
				switch($requirement['type']) {
					case 'equals':
						$alias = $requirement['alias'];
						$value = $requirement['value'];
						if ($table->hasField($alias)) {
							$filterResult .= sprintf(
								"%s.`%s` = %s",
								$tableName,
								$table->getColumnName($alias),
								$this->valueToString($table, $alias, $value)
							);
						}
						break;
					case 'equalsNot':
						$alias = $requirement['alias'];
						$value = $requirement['value'];
						if ($table->hasField($alias)) {
							$filterResult .= sprintf(
								"%s.`%s` <> %s",
								$tableName,
								$table->getColumnName($alias),
								$this->valueToString($table, $alias, $value)
							);
						}
						break;
					case 'greaterThan':
						$alias = $requirement['alias'];
						$value = $requirement['value'];
						if ($table->hasField($alias)) {
							$filterResult .= sprintf(
								"%s.`%s` > %s",
								$tableName,
								$table->getColumnName($alias),
								$this->valueToString($table, $alias, $value)
							);
						}
						break;
					case 'smallerThan':
						$alias = $requirement['alias'];
						$value = $requirement['value'];
						if ($table->hasField($alias)) {
							$filterResult .= sprintf(
								"%s.`%s` < %s",
								$tableName,
								$table->getColumnName($alias),
								$this->valueToString($table, $alias, $value)
							);
						}
						break;
					case 'greaterEqualThan':
						$alias = $requirement['alias'];
						$value = $requirement['value'];
						if ($table->hasField($alias)) {
							$filterResult .= sprintf(
								"%s.`%s` >= %s",
								$tableName,
								$table->getColumnName($alias),
								$this->valueToString($table, $alias, $value)
							);
						}
						break;
					case 'smallerEqualThan':
						$alias = $requirement['alias'];
						$value = $requirement['value'];
						if ($table->hasField($alias)) {
							$filterResult .= sprintf(
								"%s.`%s` <= %s",
								$tableName,
								$table->getColumnName($alias),
								$this->valueToString($table, $alias, $value)
							);
						}
						break;
					case 'like':
						$alias = $requirement['alias'];
						$value = $requirement['value'];
						if ($table->hasField($alias)) {
							$filterResult .= sprintf(
								"%s.`%s` LIKE %s",
								$tableName,
								$table->getColumnName($alias),
								$this->valueToString($table, $alias, $value)
							);
						}
						break;
					case 'filter':
						$subFilter = $requirement['filter'];
						$filterResult .= '('.$this->parseDataFilter($table, $subFilter, $useTableName).')';
						break;
					case 'null':
						$alias = $requirement['alias'];
						if ($table->hasField($alias)) {
							$filterResult .= sprintf(
								"%s.`%s` IS NULL",
								$tableName,
								$table->getColumnName($alias)
							);
						}
						break;
					case 'not-null':
						$alias = $requirement['alias'];
						if ($table->hasField($alias)) {
							$filterResult .= sprintf(
								"%s.`%s` IS NOT NULL",
								$tableName,
								$table->getColumnName($alias)
							);
						}
						break;
					case 'joinFilter':
						$alias = $requirement['alias'];
						$joinAlias = $requirement['joinAlias'];
						$joinTable = $requirement['joinTable'];
						$joinFilter = $requirement['filter'];
						$joinType = $requirement['joinType'];
						$joinTableAlias = $requirement['joinTableAlias'];

						$joinTableName = "`".$joinTable->getTableName()."`";
						if ($joinTableAlias != "") $joinTableName = $joinTableAlias;


						if (($table->hasField($alias)) && ($joinTable->hasField($joinAlias))) {
							if ($joinType == 'left') {
								$filterResult .= sprintf(
								"%s.`%s` = %s.`%s`%s",
								$tableName,
								$table->getColumnName($alias),
								$joinTableName,
								$joinTable->getColumnName($joinAlias),
								($joinFilter->getFilterCount() > 0) ?
									 " AND (". $this->parseDataFilter($joinTable, $joinFilter, $joinTableAlias) . ")" :
									 ""
								);
							} else {
								if ($joinFilter->getFilterCount() > 0)
									 $filterResult .= "(" . $this->parseDataFilter($joinTable, $joinFilter, $joinTableAlias) . ")";
							}
						}
						break;
					case 'filterJoinFilter':
						$alias = $requirement['alias'];
						$joinAlias = $requirement['joinAlias'];
						$joinTable = $requirement['joinTable'];
						$joinFilter = $requirement['filter'];
						$joinType = $requirement['joinType'];
						$joinTableAlias = $requirement['joinTableAlias'];

						if (($table->hasField($alias)) && ($joinTable->hasField($joinAlias))) {
							if ($joinType == 'left') {
								$filterResult .= sprintf(
								"%s",
								($joinFilter->getFilterCount() > 0) ? (
									 "(". $this->parseDataFilter($joinTable, $joinFilter, $joinTableAlias) . ")"
									 ) :
									 ""
								);
							} else {
								if ($joinFilter->getFilterCount() > 0)
									 $filterResult .= "(" . $this->parseDataFilter($joinTable, $joinFilter, $joinTableAlias) . ")";
								else $filterResult .= "";
							}
						}
						break;
					case 'columnEquals':
						$alias = $requirement['alias'];
						$joinAlias = $requirement['joinAlias'];
						$joinTable = $requirement['joinTable'];
						if ($joinTable == null) $joinTable = $table;
						if (($table->hasField($alias)) && ($joinTable->hasField($joinAlias))) {
							$filterResult .= sprintf(
								"%s.`%s` = `%s`.`%s`",
								$tableName,
								$table->getColumnName($alias),
								$joinTable->getTableName(),
								$joinTable->getColumnName($joinAlias)
							);
						}
						break;
					case 'columnEqualsNot':
						$alias = $requirement['alias'];
						$joinAlias = $requirement['joinAlias'];
						$joinTable = $requirement['joinTable'];
						if ($joinTable == null) $joinTable = $table;
						if (($table->hasField($alias)) && ($joinTable->hasField($joinAlias))) {
							$filterResult .= sprintf(
								"%s.`%s` <> `%s`.`%s`",
								$tableName,
								$table->getColumnName($alias),
								$joinTable->getTableName(),
								$joinTable->getColumnName($joinAlias)
							);
						}
						break;
					case 'columnLessThan':
						$alias = $requirement['alias'];
						$joinAlias = $requirement['joinAlias'];
						$joinTable = $requirement['joinTable'];
						if ($joinTable == null) $joinTable = $table;
						if (($table->hasField($alias)) && ($joinTable->hasField($joinAlias))) {
							$filterResult .= sprintf(
								"%s.`%s` < `%s`.`%s`",
								$tableName,
								$table->getColumnName($alias),
								$joinTable->getTableName(),
								$joinTable->getColumnName($joinAlias)
							);
						}
						break;
					case 'columnLessThanOrEquals':
						$alias = $requirement['alias'];
						$joinAlias = $requirement['joinAlias'];
						$joinTable = $requirement['joinTable'];
						if ($joinTable == null) $joinTable = $table;
						if (($table->hasField($alias)) && ($joinTable->hasField($joinAlias))) {
							$filterResult .= sprintf(
								"%s.`%s` <= `%s`.`%s`",
								$tableName,
								$table->getColumnName($alias),
								$joinTable->getTableName(),
								$joinTable->getColumnName($joinAlias)
							);
						}
						break;
					case 'columnGreaterThan':
						$alias = $requirement['alias'];
						$joinAlias = $requirement['joinAlias'];
						$joinTable = $requirement['joinTable'];
						if ($joinTable == null) $joinTable = $table;
						if (($table->hasField($alias)) && ($joinTable->hasField($joinAlias))) {
							$filterResult .= sprintf(
								"%s.`%s` > `%s`.`%s`",
								$tableName,
								$table->getColumnName($alias),
								$joinTable->getTableName(),
								$joinTable->getColumnName($joinAlias)
							);
						}
						break;
					case 'columnGreaterThanOrEquals':
						$alias = $requirement['alias'];
						$joinAlias = $requirement['joinAlias'];
						$joinTable = $requirement['joinTable'];
						if ($joinTable == null) $joinTable = $table;
						if (($table->hasField($alias)) && ($joinTable->hasField($joinAlias))) {
							$filterResult .= sprintf(
								"%s.`%s` >= `%s`.`%s`",
								$tableName,
								$table->getColumnName($alias),
								$joinTable->getTableName(),
								$joinTable->getColumnName($joinAlias)
							);
						}
						break;
					case 'textRelevance':
						$text = $requirement['text'];
						$searchType = $requirement['searchType'];
						$filterResult .= $this->getTextSearchPart($table, $text, $searchType);
						break;
					case 'functionEqualsValue':
						$function = $requirement['function'];
						$alias = $requirement['alias'];
						$joinTable = $requirement['table'];

						$functionPart = $this->buildFunction($function, $joinTable);

						if ($joinTable->hasField($alias)) {
							$filterResult .= sprintf(
								"%s = `%s`.`%s`",
								$functionPart['code'],
								$joinTable->getTableName(),
								$joinTable->getColumnName($alias)
							);
						}
						break;
				}
				if (strLen(trim($filterResult)) > 0) {
					$results[] = $filterResult;
				}
			}
			return implode($glue, $results);
		}

		/**
		 * builds a functionclause of the query
		 * using the given table and function specifications
		 *@access private
		 *@param DataTable $table the table to get column meta data from
		 *@param DataFilter $filter the list containing data requirements
		 *@return string the function clause of the query
		 */
		function buildFunctionClause(&$table, &$functionDescriptions) {
			$functionClause = "";
			$nrFunctions = $functionDescriptions->getFunctionCount();
			for ($i = 0; $i < $nrFunctions; $i++) {
				$functionData = $functionDescriptions->getFunction($i);
				switch($functionData['type']) {
					case 'count': // Count all the rows
						$alias = $functionData['alias'];
						$resultName = $functionData['resultname'];
						if ($table->hasField($alias)) {
							$functionClause .= sprintf('COUNT(`%s`.`%s`) AS `%s`',
								$table->getTableName(),
								$table->getColumnName($alias),
								$resultName
								);
						}
						break;
					case 'countdistinct': // Count all the rows distinct
						$alias = $functionData['alias'];
						$resultName = $functionData['resultname'];
						if ($table->hasField($alias)) {
							$functionClause .= sprintf('COUNT(DISTINCT `%s`.`%s`) AS `%s`',
								$table->getTableName(),
								$table->getColumnName($alias),
								$resultName
								);
						}
						break;
					case 'joincount': // Count all the rows
						$alias = $functionData['alias'];
						$resultName = $functionData['resultname'];
						$joinTable = $functionData['joinTable'];
						if ($joinTable->hasField($alias)) {
							$functionClause .= sprintf('COUNT(`%s`.`%s`) AS `%s`',
								$joinTable->getTableName(),
								$joinTable->getColumnName($alias),
								$resultName
								);
						}
						break;
					case 'sum': // Sum all the rows
						$alias = $functionData['alias'];
						$resultName = $functionData['resultname'];
						if ($table->hasField($alias)) {
							$functionClause .= sprintf('SUM(`%s`.`%s`) AS `%s`',
								$table->getTableName(),
								$table->getColumnName($alias),
								$resultName
								);
						}
						break;
					case 'distinct': // return distinct values
						$alias = $functionData['alias'];
						$resultName = $functionData['resultname'];
						if ($table->hasField($alias)) {
							$functionClause .= sprintf('DISTINCT `%s`.`%s` AS `%s`',
								$table->getTableName(),
								$table->getColumnName($alias),
								$resultName
								);
						}
						break;
					case 'joindistinct': // Count all the rows
						$alias = $functionData['alias'];
						$resultName = $functionData['resultname'];
						$joinTable = $functionData['joinTable'];
						if ($joinTable->hasField($alias)) {
							$functionClause .= sprintf('DISTINCT `%s`.`%s` AS `%s`',
								$joinTable->getTableName(),
								$joinTable->getColumnName($alias),
								$resultName
								);
						}
						break;
					case 'avg': // Average of column
						$alias = $functionData['alias'];
						$resultName = $functionData['resultname'];
						if ($table->hasField($alias)) {
							$functionClause .= sprintf('AVG(`%s`.`%s`) AS `%s`',
								$table->getTableName(),
								$table->getColumnName($alias),
								$resultName
								);
						}
						break;
					case 'min': // Sum all the rows
						$alias = $functionData['alias'];
						$resultName = $functionData['resultname'];
						if ($table->hasField($alias)) {
							$functionClause .= sprintf('MIN(`%s`.`%s`) AS `%s`',
								$table->getTableName(),
								$table->getColumnName($alias),
								$resultName
								);
						}
						break;
					case 'max': // Sum all the rows
						$alias = $functionData['alias'];
						$resultName = $functionData['resultname'];
						if ($table->hasField($alias)) {
							$functionClause .= sprintf('MAX(`%s`.`%s`) AS `%s`',
								$table->getTableName(),
								$table->getColumnName($alias),
								$resultName
								);
						}
						break;
					case 'normal': // add the column
						$alias = $functionData['alias'];
						$resultName = $functionData['resultname'];
						if ($table->hasField($alias)) {
							$functionClause .= sprintf('`%s`.`%s` AS `%s`',
								$table->getTableName(),
								$table->getColumnName($alias),
								$resultName
								);
						}
						break;
					case 'normalJoin': // add the column
						$joinTable = $functionData['table'];
						$alias = $functionData['alias'];
						$resultName = $functionData['resultname'];
						if ($joinTable->hasField($alias)) {
							$functionClause .= sprintf('`%s`.`%s` AS `%s`',
								$joinTable->getTableName(),
								$joinTable->getColumnName($alias),
								$resultName
								);
						}
						break;
					case 'normalAliasJoin': // add the column
						$joinTable = $functionData['table'];
						$alias = $functionData['alias'];
						$resultName = $functionData['resultname'];
						if ($joinTable->hasField($alias)) {
							$functionClause .= sprintf('%s.`%s` AS `%s`',
								$functionData['tableAlias'],
								$joinTable->getColumnName($alias),
								$resultName
								);
						}
						break;
					case 'distinctAliasJoin': // add the column
						$joinTable = $functionData['table'];
						$alias = $functionData['alias'];
						$resultName = $functionData['resultname'];
						if ($joinTable->hasField($alias)) {
							$functionClause .= sprintf('DISTINCT %s.`%s` AS `%s`',
								$functionData['tableAlias'],
								$joinTable->getColumnName($alias),
								$resultName
								);
						}
						break;
					case 'all': // add the column
						$selectTable = $functionData['table'];
						$functionClause .= sprintf('`%s`.*',
							$selectTable->getTableName()
							);
						break;
					case 'function': // sub function
						$functionObj = $functionData['function'];
						$resultName = $functionData['resultname'];
						$funcCode = $this->buildFunction($functionObj, $table);

						if ($funcCode === false) return false; // invalid functions
						$functionClause .= sprintf('%s AS `%s`',
							$funcCode['code'],
							$resultName
							);
						break;
					case 'textrelevance': // textsearch
						/* http://dev.mysql.com/doc/refman/4.1/en/fulltext-search.html

							MATCH (col1,col2,...) AGAINST (expr [search_modifier])
							search_modifier: { IN BOOLEAN MODE | WITH QUERY EXPANSION }
						*/
						$resultName = $functionData['resultname'];
						$searchString = $this->getTextSearchPart($table, $functionData['text'], $functionData['searchType']);
						$functionClause .= sprintf('%s AS `%s`', $searchString, $resultName);

					/*
						'text' => $text,
						'searchType' => $searchType,
						'resultname' => $resultName,
						'type' => 'textrelevance'

					*/
				}
				if ($i < $nrFunctions -1) {
					$functionClause .= ', ';
				}
			}

			if(count($functionDescriptions->privateVars['groups']) > 0) {
				if ($nrFunctions > 0) $functionClause .= ", ";
				for($i = 0; $i < count($functionDescriptions->privateVars['groups']); $i++) {
					if($i != 0) $functionClause .= ", ";
					$functionClause .= "`".$table->getTableName()."`.`".$table->getColumnName($functionDescriptions->privateVars['groups'][$i])."`";
				}
			}
			return $functionClause;
		}

		function buildFunction(&$functionData, &$table) {
			$funcName = strToLower($functionData->getName());
			$argCount = $functionData->getParameterCount();

			$knownFunctions = array();
			$knownFunctions[] = array(
				"name" => "minus",
				"args" => array("int", "int"),
				"code" => "%s - %s",
				"result" => "int");
			$knownFunctions[] = array(
				"name" => "absolute",
				"args" => array("int"),
				"code" => "ABS(%s)",
				"result" => "int");
			$knownFunctions[] = array(
				"name" => "substring",
				"args" => array("string", "int"),
				"code" => "SUBSTRING(%s, %s)",
				"result" => "string");
			$knownFunctions[] = array(
				"name" => "substring",
				"args" => array("string", "int", "int"),
				"code" => "SUBSTRING(%s, %s, %s)",
				"result" => "string");
			$knownFunctions[] = array(
				"name" => "concat",
				"args" => array("%string"),
				"code" => "CONCAT(%s)",
				"result" => "string");

			for($i = 0; $i < count($knownFunctions); $i ++ ) {
				$funcDesc = $knownFunctions[$i];
				$hasDynArgs = in_array('%string', $funcDesc['args']);

				if ($funcDesc["name"] == $funcName) {
					if (count($funcDesc["args"]) == $argCount) {
						//print $funcName;
						$result = $this->p_getFunctionCode($funcDesc, $functionData, $table);
						//print_r($result);
						if (is_array($result)) return $result;
					} else if (count($funcDesc["args"]) < $argCount && $hasDynArgs) {
						$argStrings = array();
						//print $argCount . "<br />";

						for ($p = 0; $p < $argCount; $p ++) {
							$value = false;
							$paramType = false;
							$type = $functionData->getParameterType($p);
							switch($type) {
								case "string":
									$argStrings[] = "'".addSlashes($functionData->getParameterValue($p))."'";
									break;
								case "column":
									$alias = $functionData->getParameterValue($p);
									$checkTable = $table;
									$otherTable = $functionData->getParameterTable($p);
									if ($otherTable !== null) $checkTable = $otherTable;

									if (!$checkTable->hasField($alias)) return false;
									if ($functionData->hasParameterTableAlias($p)) {
										$argStrings[] = $functionData->getParameterTableAlias($p).".`".$checkTable->getColumnName($alias)."`";
									} else
										$argStrings[] = "`".$checkTable->getTableName()."`.`".$checkTable->getColumnName($alias)."`";
									$paramType = "column";
									break;
							}
						}
						//print_r($argStrings);


						$result = array();
						$result["type"] = $funcDesc['result'];
						$code = "\$result[\"code\"] = sprintf(\"".$funcDesc["code"]."\"";
						$params = implode(", ", $argStrings);
						//foreach($argStrings as $argStr)
						$code .= ", \"".$params."\"";
						$code .= ");";
						eval($code);
						return $result;

					}
				}
			}
			return false;
		}

		function p_getFunctionCode($funcDesc, &$functionData, &$table) {
			$argStrings = array();
			$argTypes = array();
			for ($p = 0; $p < count($funcDesc['args']); $p ++) {
				$value = false;
				$paramType = false;
				$type = $functionData->getParameterType($p);
				switch($type) {
					case "string":
						$value = "\"".addSlashes($functionData->getParameterValue($p))."\"";
						$paramType = "string";
						break;
					case "int":
						$value = $functionData->getParameterValue($p);
						$paramType = "int";
						break;
					case "datetime":
						$datevalue = $functionData->getParameterValue($p);
						$value = $datevalue->toString("Y-m-d H:i:s");
						/* MySQL datetime format
							see: http://www.mysql.com/doc/en/DATETIME.html
						 */
						$paramType = "date";
						break;
					case "float":
						$value = $functionData->getParameterValue($p);
						$paramType = "float";
						break;
					case "column":
						$alias = $functionData->getParameterValue($p);
						$checkTable = $table;
						$otherTable = $functionData->getParameterTable($p);
						if ($otherTable !== null) $checkTable = $otherTable;

						if (!$checkTable->hasField($alias)) return false;
						if ($functionData->hasParameterTableAlias($p)) {
							$value = $functionData->getParameterTableAlias($p).".`".$checkTable->getColumnName($alias)."`";
						} else
							$value = "`".$checkTable->getTableName()."`.`".$checkTable->getColumnName($alias)."`";
						$paramType = "column";
						break;
					case "function":
						$funcData = $functionData->getParameterValue($p);
						$result = $this->buildFunction($funcData, $table);
						if ($result === false) return false;
						$value = $result['code'];
						$paramType = "mixed"; //$result['type'];
						break;
				}
				if ($value === false) return false;
				if ($paramType != $funcDesc["args"][$p]) {
					if (!(($paramType == "column") || ($paramType == "mixed"))) {
						//print $functionData->getName()." invalidParameter(".$paramType." != ".$funcDesc["args"][$p].") ";
						return false; // invalid parameter
					}
				}
				$argStrings[] = $value;
				$argTypes[] = $paramType;
			}
			$result = array();
			$result["type"] = $funcDesc['result'];
			$code = "\$result[\"code\"] = sprintf(\"".$funcDesc["code"]."\"";
			foreach($argStrings as $argStr) $code .= ", \"".$argStr."\"";
			$code .= ");";
			eval($code);
			return $result;
		}

		function getTextSearchPart(&$table, $searchExpression, $searchType) {
			$textAliasses = $table->getIndexedColumns("text");
			$textColumns = array();
			foreach($textAliasses as $columnAlias) {
				$textColumns[] = $table->getColumnName($columnAlias);
			}
			$expression = "'".addSlashes($searchExpression)."'";
			switch($searchType) {
				case db_function_search_any:
					$expression = "'".addSlashes($searchExpression)."'"; //  WITH QUERY EXPANSION
					break;
				case db_function_search_all:
					$words = explode(" ", $searchExpression);
					$newExpression = implode(" +", $words);
					$expression = "'".addSlashes($newExpression)."' IN BOOLEAN MODE";
					break;
				case db_function_search_exact:
					$expression = "'\"".addSlashes($searchExpression)."\"' IN BOOLEAN MODE";
					break;
				case db_function_search_expression:
					$expression = "'".addSlashes($searchExpression)."' IN BOOLEAN MODE";
					break;
			}
			return sprintf('MATCH(`%s`) AGAINST (%s)', implode("`, `", $textColumns), $expression);
		}


		/**
		 * builds the order clause part of the query
		 * using the given table and sorting specifications
		 *@access private
		 *@param DataTable $table the table to get column meta data from
		 *@param ColumnSorting $columnSorting the list containing the sort settings
		 *@return string the order clause of the query
		 */
		function buildOrderClause(&$table, &$columnSorting) {
			$orderClause = " ORDER BY ";
			$nrSorts = $columnSorting->getSortCount();
			for ($i = 0; $i < $nrSorts; $i++) {
				$sortSetting = $columnSorting->getSorting($i);
				switch($sortSetting['type']) {
					case 'column':
						if(strLen($table->getColumnName($sortSetting['alias'])) > 0) { // added 01-06-05 by Guido, dynamic names weren't supported
							$orderClause .= sprintf(
								"`%s`.`%s` %s",
								$table->getTableName(),
								$table->getColumnName($sortSetting['alias']),
								($sortSetting['ascending']) ? "ASC" : "DESC"
							);
						} else {
							$orderClause .= sprintf(
								"`%s` %s",
								$sortSetting['alias'],
								($sortSetting['ascending']) ? "ASC" : "DESC"
							);
						}
					break;
					case 'joincolumn':
						$joinTable = $sortSetting['table'];
						if(strLen($joinTable->getColumnName($sortSetting['alias'])) > 0) { // added 01-06-05 by Guido, dynamic names weren't supported
							$orderClause .= sprintf(
								"`%s`.`%s` %s",
								$joinTable->getTableName(),
								$joinTable->getColumnName($sortSetting['alias']),
								($sortSetting['ascending']) ? "ASC" : "DESC"
							);
						} else {
							$orderClause .= sprintf(
								"`%s` %s",
								$sortSetting['alias'],
								($sortSetting['ascending']) ? "ASC" : "DESC"
							);
						}
					break;
					case 'alias':
						$orderClause .= sprintf(
							"`%s` %s",
							$sortSetting['resultname'],
							($sortSetting['ascending']) ? "ASC" : "DESC"
						);
					break;
				}

				if ($i < $nrSorts -1) {
					$orderClause .= ', ';
				}
			}
			return $orderClause;
		}

		/**
		* Builds the group by cluase part of the query
		 *@access private
		 *@param DataTable $table the table to get column meta data from
		 *@param FunctionDescription $functionDescriptions the object containing the groupby array
		 *@return string the groupby clause of the query
		**/
		function buildGroupByClause(&$table, &$functionDescriptions) {
			$groupByClause = "";

			if(count($functionDescriptions->privateVars['groups']) > 0) {
				$groupByClause .= " GROUP BY ";
				for($i = 0; $i < count($functionDescriptions->privateVars['groups']); $i++) {
					if($i != 0) $groupByClause .= ", ";
					$groupByClause .= sprintf("`%s`.%s", $table->getTableName(), $table->getColumnName($functionDescriptions->privateVars['groups'][$i]));
				}
			}
			return $groupByClause;
		}

		function getBlob(&$table, $alias, $primkey) {
			$columnName = $table->getColumnName($alias);
			$selectQuery = sprintf("SELECT %s FROM `%s` WHERE `%s`='%s'",
							$columnName,
							$table->getTableName(),
							$table->getColumnName($table->getPrimaryKey()),
							addSlashes($primkey));

			$result = @mysql_query($selectQuery,$this->privateVars['link']);
			if($result) {
				$data = mysql_fetch_row($result);
				return $data[0];
			}

		}

		/**
		 * Return all names of the fields in the given table for select statements.
		 */
		function getAllFields(&$table) {
			$result = '';
			$tableName = $table->getTableName();
			$nr = $table->getColumnCount();
			for ($i = 0; $i < $nr; $i++) {
				$alias = $table->getAlias($i);
				$columnName = $table->getColumnName($alias);
				$columnType = $table->getColumnType($alias);
				switch($columnType) {
					case "text":
						if ($this->privateVars["textEncoding"] !== false) {
							//$result .= sprintf('CONVERT(`%s`.`%s` USING utf8)', $tableName, $columnName);
							$result .= sprintf('`%s`.`%s`', $tableName, $columnName);
						} else $result .= sprintf('`%s`.`%s`', $tableName, $columnName);
						break;
					default: $result .= sprintf('`%s`.`%s`', $tableName, $columnName); break;
				}
				if ($i < $nr -1) $result .= ', ';
			}
			/*
			if ($table->hasMultiLanguageFields()) {
				$result .= ", `".$table->getTableName()."lang`.`recordTexts`";
			}
			*/
			return $result;
		}

		/**
		 * builds the update clause part of the query
		 * using the given table and data specifications
		 *@access private
		 *@param DataTable $table the table to get column meta data from
		 *@param DataMutation $mutations the list containing data mutations
		 *@return string the update clause of the query
		 */
		function buildUpdateClause(&$table, &$mutations, $forceIndex = false) {
			$updateClause = " SET ";
			if ($forceIndex !== false) {
				$mutationData = $mutations->getMutation($forceIndex);
				$updateClause .= $this->getUpdateString($table, $mutationData);
				return $updateClause;
			}
			$nrMutations = $mutations->getMutationCount();
			for ($i = 0; $i < $nrMutations; $i++) {
				$mutationData = $mutations->getMutation($i);
				$updateClause .= $this->getUpdateString($table, $mutationData);
				if ($i < $nrMutations -1) {
					$updateClause .= ', ';
				}
			}
			return $updateClause;
		}

		function getUpdateString(&$table, $mutationData) {
			$alias = $mutationData['alias'];
			if (isSet($mutationData['value'])) $value = $mutationData['value'];
			else  $value = null;
			switch($mutationData['type']) {
				case 'columnSub': // Subtract a value from a column
					if ($table->hasField($alias)) {
						return sprintf('`%1$s`.`%2$s` = `%1$s`.`%2$s` - %3$s',
							$table->getTableName(),
							$table->getColumnName($alias),
							$this->valueToString($table, $alias, $value, true)
							);
					}
					break;
				case 'columnAdd': // Add a value to a column
					if ($table->hasField($alias)) {
						return sprintf('`%1$s`.`%2$s` = `%1$s`.`%2$s` + %3$s',
							$table->getTableName(),
							$table->getColumnName($alias),
							$this->valueToString($table, $alias, $value, true)
							);
					}
					break;

				case 'columnMax':
					if ($table->hasField($alias)) {
						$updateString = '`%1$s`.`%2$s` = IF((`%1$s`.`%2$s` > %3$s) OR (`%1$s`.`%2$s` IS NULL), %3$s, `%1$s`.`%2$s`)';
						return sprintf($updateString,
							$table->getTableName(),
							$table->getColumnName($alias),
							$this->valueToString($table, $alias, $value, true)
							);
					}
					break;
				case 'columnMin':
					if ($table->hasField($alias)) {
						$updateString = '`%1$s`.`%2$s` = IF((`%1$s`.`%2$s` < %3$s) OR (`%1$s`.`%2$s` IS NULL), %3$s, `%1$s`.`%2$s`)';
						return sprintf($updateString,
							$table->getTableName(),
							$table->getColumnName($alias),
							$this->valueToString($table, $alias, $value, true)
							);
					}
					break;
				case 'null': // Set value to NULL
					if ($table->hasField($alias)) {
						return sprintf('`%1$s`.`%2$s` = NULL',
							$table->getTableName(),
							$table->getColumnName($alias)
							);
					}
					break;
				case 'equals': // Set value to a value
					if ($table->hasField($alias)) {
						return sprintf('`%1$s`.`%2$s` = %3$s',
							$table->getTableName(),
							$table->getColumnName($alias),
							$this->valueToString($table, $alias, $value, true)
							);
					}
					break;
			}
			return "";
		}


		function buildJoins(&$table, &$filter, $root = true, $tableAlias="") {
			$joinClause = "";
			$nrFilters = $filter->getFilterCount();
			//$addMultiLanguage = false;
			$tableName = '`'.$table->getTableName().'`';
			if ($tableAlias != '') $tableName = $tableAlias;


			for ($i = 0; $i < $nrFilters; $i++) {
				$requirement = $filter->getFilter($i);
				if ($requirement['type'] == 'joinFilter') {
					//$addMultiLanguage = true;
					$alias = $requirement['alias'];
					$joinAlias = $requirement['joinAlias'];
					$joinTable = $requirement['joinTable'];
					$joinFilter = $requirement['filter'];
					$joinType = $requirement['joinType'];
					$joinView = $requirement['joinView'];

					$useJoinTableNameFilter = false;
					$joinTableAlias = $requirement['joinTableAlias'];
					if ($joinTableAlias != "") {
						$useJoinTableNameFilter = true;
					};

					$joinClause .= sprintf(
						" %s %s JOIN `%s`%s ON %s.`%s` = %s.`%s`",
						($joinType == 'left') ? "LEFT" : "RIGHT",
						($joinView == 'inner') ? "" : "OUTER",
						$joinTable->getTableName(),
						($useJoinTableNameFilter) ? " AS ".$joinTableAlias : "",
						($useJoinTableNameFilter) ? $joinTableAlias : "`".$joinTable->getTableName()."`",
						$joinTable->getColumnName($joinAlias),
						$tableName,
						$table->getColumnName($alias)
					);
					$joinClause .= $this->buildJoins($joinTable, $joinFilter, false, ($useJoinTableNameFilter) ? $joinTableAlias : "");
				} else
				if ($requirement['type'] == 'filter') {
					$joinFilter = $requirement['filter'];
					$joinClause .= $this->buildJoins($table, $joinFilter, false);
				} else
				if ($requirement['type'] == 'filterJoinFilter') {
					//$addMultiLanguage = true;
					$alias = $requirement['alias'];
					$joinAlias = $requirement['joinAlias'];
					$joinTable = $requirement['joinTable'];
					$joinFilter = $requirement['filter'];
					$joinFilter2 = $requirement['joinFilter'];
					$joinType = $requirement['joinType'];
					$joinView = $requirement['joinView'];
					if ($requirement['joinFilterForOriginal']) {
						$joinFilterTable = $table;
					} else {
						$joinFilterTable = $joinTable;
					}
					$useJoinTableNameFilter = false;
					$joinTableAlias = $requirement['joinTableAlias'];
					if ($joinTableAlias != "") {
						$useJoinTableNameFilter = true;
					};

					$joinClause .= sprintf(
						" %s %s JOIN `%s`%s ON %s.`%s` = %s.`%s`%s",
						($joinType == 'left') ? "LEFT" : "RIGHT",
						($joinView == 'inner') ? "" : "OUTER",
						$joinTable->getTableName(),
						($useJoinTableNameFilter) ? " AS ".$joinTableAlias : "",
						($useJoinTableNameFilter) ? $joinTableAlias : "`".$joinTable->getTableName()."`",
						$joinTable->getColumnName($joinAlias),
						$tableName,
						$table->getColumnName($alias),
						($joinFilter2->getFilterCount() > 0) ? " AND (" . $this->parseDataFilter($joinFilterTable, $joinFilter2, $joinTableAlias) . ")" : ""
					);
					$joinClause .= $this->buildJoins($joinTable, $joinFilter, false, ($useJoinTableNameFilter) ? $joinTableAlias : "");
				}
			}
			/*
			if ($table->hasMultiLanguageFields() && ($addMultiLanguage || $root)) {
				global $language;
				$joinClause .= sprintf(
					" LEFT JOIN `%s` AS `%s` ON (`%s`.`%s` = `%s`.`%s`) AND (`%s`.`%s`='%s') AND (`%s`.`%s`='%s')",
					"ivmultilanguage",
					$table->getTableName() . "lang",
					$table->getTableName() . "lang",
					"recordID",
					$table->getTableName(),
					$table->getColumnName($table->getPrimaryKey()),
					$table->getTableName() . "lang", "language", $language->getDictionary(),
					$table->getTableName() . "lang", "table",	$table->getTableName()
				);
			}
			*/
			return $joinClause;
		}

		function createTable(&$table) {
			$createQuery = sprintf("CREATE TABLE IF NOT EXISTS `%s` (", $table->getTableName());
			$columns = array();
			$indexes = array();
			for ($i = 0; $i < $table->getColumnCount(); $i++) {
				$alias = $table->getAlias($i);
				$columns[] = $this->getColumnCreate($table, $alias);
				if ($table->hasIndex($alias)) {
					$indexes[] = $table->getColumnName($alias);
				}
			}
			$createQuery .= implode(", ", $columns);
			if ($table->getPrimaryKey() !== null) {
				$prim = $table->getPrimaryKey();
				$createQuery .= ", PRIMARY KEY(`".$table->getColumnName($prim)."`)";
			}
			if (count($indexes) > 0) {
				$createQuery .= ", INDEX(`".implode("`, `", $indexes)."`)";
			}
			$createQuery .= ")";
			$this->executeQuery($createQuery);
		}

		function getColumnCreate(&$table, $alias) {
			$type = $table->getColumnType($alias);
			switch($type) {
				case "multiLanguageInt":
				case "int":// `reviewType` BIGINT UNSIGNED NOT NULL ,
					return sprintf("`%s` BIGINT UNSIGNED%s%s",
						$table->getColumnName($alias),
						($table->allowNull($alias)) ? "" : " NOT NULL",
						($table->isAutoIncrement($alias)) ? " AUTO_INCREMENT" : ""
					);
				case "float":// `reviewType` FLOAT NOT NULL ,
					return sprintf("`%s` FLOAT%s%s",
						$table->getColumnName($alias),
						($table->allowNull($alias)) ? "" : " NOT NULL",
						($table->isAutoIncrement($alias)) ? " AUTO_INCREMENT" : ""
					);
				case "multiLanguageText":
				case "text":
					$length = $table->getColumnLength($alias);
					if ($length < 256) {// `name` VARCHAR( 20 ) NOT NULL ,
						return sprintf("`%s` VARCHAR(%s)%s%s",
							$table->getColumnName($alias),
							$length,
							($table->allowNull($alias)) ? "" : " NOT NULL",
							($table->hasDefaultValue($alias)) ? " DEFAULT ".$this->valueToString($table, $alias, $table->getDefaultValue($alias)) : ""
						);
					} else {// `message` text NOT NULL,
						return sprintf("`%s` TEXT%s",
							$table->getColumnName($alias),
							($table->allowNull($alias)) ? "" : " NOT NULL"
						);
					}
				case "bool":// `signature` enum('yes','no') NOT NULL default 'yes',
				case "enum":// `type` ENUM( 'text', 'number', 'select' ) NOT NULL ,
					$values = $table->getEnumDatabaseStrings($alias);
					return sprintf("`%s` ENUM('%s')%s%s",
						$table->getColumnName($alias),
						implode("','", $values),
						($table->allowNull($alias)) ? "" : " NOT NULL",
							($table->hasDefaultValue($alias)) ? " DEFAULT ".$this->valueToString($table, $alias, $table->getDefaultValue($alias)) : ""
					);
				case "date":// `lastChange` datetime default NULL,
					return sprintf("`%s` DATETIME%s%s",
						$table->getColumnName($alias),
						($table->allowNull($alias)) ? "" : " NOT NULL",
						($table->hasDefaultValue($alias)) ? " DEFAULT ".$this->valueToString($table, $alias, $table->getDefaultValue($alias)) : ""
					);
				case "time":// `lastChange` time default NULL,
					return sprintf("`%s` TIME%s%s",
						$table->getColumnName($alias),
						($table->allowNull($alias)) ? "" : " NOT NULL",
						($table->hasDefaultValue($alias)) ? " DEFAULT ".$this->valueToString($table, $alias, $table->getDefaultValue($alias)) : ""
					);
				default: return;
			}
		}

		function tableExists(&$table) {
			 $exists = @mysql_query(sprintf("SHOW TABLES FROM `%s` LIKE '%s'",
			 	$this->privateVars['database'], $table->getTableName()), $this->privateVars['link']);
		   return @mysql_num_rows($exists) == 1;
		}

		function synchronizeTableStructure(&$table) {
			$result = @mysql_query(sprintf("SHOW COLUMNS FROM `%s`", $table->getTableName()), $this->privateVars['link']);
			if (!$result) return false;
			if (mysql_num_rows($result) > 0) {
				$passedColumns = array();
				while ($row = mysql_fetch_assoc($result)) {
					//print_r($row);
					$columnName = $row['Field'];
					if ($columnAlias = $table->getAliasForName($columnName)) {
						$passedColumns[] = $columnAlias;
						//print "kolom hebben we! (".$columnAlias.")<br />";

					} else {
						//print "kolom is niet in definitie.. mik weg! (".$columnName.")<br />";
						//ALTER TABLE `z_list18` DROP `fg` ;
						$query = sprintf("ALTER TABLE `%s` DROP `%s`;", $table->getTableName(), $columnName);
						mysql_query($query, $this->privateVars['link']);
					}
				}
				for ($i = 0; $i < $table->getColumnCount(); $i++) {
					$columnAlias = $table->getAlias($i);
					if (!in_array($columnAlias, $passedColumns)) {
						//print "kolom is niet in database.. voeg toe! (".$columnAlias.")<br />";
						$query = sprintf("ALTER TABLE `%s` ADD %s;", $table->getTableName(), $this->getColumnCreate($table, $columnAlias));
						$result = mysql_query($query, $this->privateVars['link']);
						if (!$result) mysql_error($this->privateVars['link']);

					}
				}
			}

		}

		function renameTableColumn(&$table, $oldName, $alias) {
			$query = sprintf("ALTER TABLE `%s` CHANGE `%s` %s;", $table->getTableName(), $oldName, $this->getColumnCreate($table, $alias));
			$result = mysql_query($query, $this->privateVars['link']);
			if (!$result) mysql_error($this->privateVars['link']);

			//ALTER TABLE `z_list18` CHANGE `c_birthdate` `c_birthdatum` DATETIME NULL DEFAULT NULL
		}

		function copyTable(&$table, $newName) {
			$query = sprintf("CREATE TABLE IF NOT EXISTS `%s` LIKE `%s`", $newName, $table->getTableName());
			$result = mysql_query($query, $this->privateVars['link']);
			if (!$result) mysql_error($this->privateVars['link']);

			$query = sprintf("INSERT INTO `%s` SELECT * FROM `%s`", $newName, $table->getTableName());
			$result = mysql_query($query, $this->privateVars['link']);
			if (!$result) mysql_error($this->privateVars['link']);
		}

		function dropTable(&$table, $confirmationCode) {
			if ($confirmationCode === "tabledropconfirm") {
				$query = sprintf("DROP TABLE `%s`", $table->getTableName());
				$result = mysql_query($query, $this->privateVars['link']);
			}
		}

		function convertCharacterEncoding($charset, $collation) {
			// $charset = 'utf8';
			// $collation = 'utf8_general_ci';

			$databaseName = $this->privateVars['database'];
			mysql_query(sprintf('ALTER DATABASE `%s` DEFAULT CHARACTER SET %s COLLATE %s', $databaseName, $charset, $collation), $this->privateVars['link']);
			$tableResult = mysql_query(sprintf('SHOW TABLES FROM `%s`', $databaseName), $this->privateVars['link']);
			while ($tableName = mysql_fetch_row($tableResult)) {
				mysql_query(sprintf('ALTER TABLE `%s` DEFAULT CHARACTER SET %s COLLATE %s', $tableName[0], $charset, $collation), $this->privateVars['link']);
				$columnResult = mysql_query(sprintf('SHOW FULL COLUMNS FROM `%s`', $tableName[0]), $this->privateVars['link']);
				$textColumns = array();
				$changes = array();
				while($columnInfo = mysql_fetch_assoc($columnResult)) {
					if ($columnInfo['Collation'] != 'NULL') {
						$textColumns[] = $columnInfo['Field'];
						//
						if ($columnInfo['Null'] == "YES") {
							$defaultValue = ($columnInfo['Default'] == "NULL") ? "" : "DEFAULT '".$columnInfo['Default']."'";
						} else {
							$defaultValue = ($columnInfo['Default'] == "NULL") ? "DEFAULT NULL" :
								(($columnInfo['Default'] == "") ? "" : "DEFAULT '".$columnInfo['Default']."'");
						}

						$changes[] = sprintf('CHANGE `%1$s` `%1$s` %2$s CHARACTER SET %3$s COLLATE %4$s %5$s %6$s',
							$columnInfo['Field'],
							$columnInfo['Type'],
							$charset, $collation,
							($columnInfo['Null'] != "YES") ? "NOT NULL": "",
							$defaultValue
						);

						/*
						ALTER TABLE `clipboard`

						CHANGE `itemID` `itemID` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ,
						CHANGE `actionType` `actionType` VARCHAR( 20 ) CHARACTER SET ucs2 COLLATE ucs2_general_ci NOT NULL

						//Type = varchar(20)
						//Null = YES
						//Default =
						// ALTER TABLE `doc_cell` CHANGE `orientation` `orientation` ENUM( 'vertical', 'horizontal' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'vertical'
						*/
					}

				}
				if (count($textColumns) > 0) {
					$alterTableQuery = sprintf('ALTER TABLE `%s` %s', $tableName[0], implode(", ", $changes));
					//print $tableName[0] . ": (".implode(", ", $textColumns).") <br />\n";
					//print $alterTableQuery . "<br />\n";
					mysql_query($alterTableQuery, $this->privateVars['link']);
				}
			}

			/*
			function tableExists(&$table) {
				 $exists = mysql_query(sprintf("SHOW TABLES FROM `%s` LIKE '%s'",
					$this->privateVars['database'], $table->getTableName()), $this->privateVars['link']);
				 return mysql_num_rows($exists) == 1;
			*/
		}

	}
?>
