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
	 * Import data objects functionality
	 */
	importClass("orm.DataObjects");

	/**
	 * Database is a class to create a database connection and execute queries
	 */
	class Database {

		/**
		 * Private vars for this object
		 *@var Array $privateVars
		 */
		var $privateVars;

		/**
		 * Instantiates a Database object
		 *@param string $host the url of the host
		 *@param string $database the name of the database to be selected
		 *@param string $user the username of the user
		 *@param string $password the password of the user
		 */
		function Database($host, $database, $user, $password, $port) {
			$this->privateVars = array();
			$this->privateVars['host'] = $host;
			$this->privateVars['database'] = $database;
			$this->privateVars['user'] = $user;
			$this->privateVars['password'] = $password;
			$this->privateVars['port'] = $port;
			$this->privateVars['connected'] = false;
			$this->privateVars['tablePrefix'] = '';
			$this->privateVars['defaultLanguage'] = 'nl';
			$GLOBALS['queriesExecuted'] = 0;
		}

		function setDefaultLanguage($language) {
			$this->privateVars['defaultLanguage'] = $language;
		}

		function getDefaultLanguage() {
			return $this->privateVars['defaultLanguage'];
		}

		function getNrQueries() {
			return $GLOBALS['queriesExecuted'];
		}

		/**
		 * Connect to the database
		 *@return bool whether the connection was succesfull
		 */
		function connect() {
		}

		/**
		 * Checks whether there is an active connection
		 *@return bool whether there is a connection
		 */
		function isConnected() {
			return $this->privateVars['connected'];
		}

		/**
		 * Disconnects to the database, if there is a connection
		 *@return bool whether the disconnection was succesfull
		 */
		function disconnect() {
		}

		/**
		 * Executes a query and returns the resultset
		 *@param string $query the sql-query
		 *@return Resultset the resultset, or FALSE if an error occured
		 */
		function executeQuery($query) {
		}

		/**
		 * Insert the given rowdata into the given table using the tablemetadata
		 *@param DataTable $table the table metadata
		 *@param DataRow $row Rowdata
		 *@return Resultset the resultset, or FALSE if an error occured
		 */
		function insertRow(&$table, &$row) {
			return false;
		}

		function getInsertQuery(&$table, &$row) {
			return "";
		}

		/**
		 * Update the given rowdata into the given table using the tablemetadata
		 *@param DataTable $table the table metadata
		 *@param DataRow $row Rowdata
		 *@return Resultset the resultset, or FALSE if an error occured
		 */
		function updateRow(&$table, &$row) {
			return false;
		}

		/**
		 * Selects rows from a table using a filter, and specified column sorting
		 *@param DataTable $table the table to select rows from
		 *@param DataFilter $filter a list of requirements the data must meet
		 *@param ColumnSorting $columnSorting a list of sort settings
		 *@return Resultset the resultset, or FALSE if an error occured
		 */
		function selectRows(&$table, &$filter, &$columnSorting) {
			return false;
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
			return false;
		}

		/**
		 * Removes rows from the given table using the given datafilter.
		 * all rows that pass the filter will be removed.
		 *@param DataTable $table the table to delete rows from
		 *@param DataFilter $filter a list of requirements the data must meet
		 *@return Resultset the resultset, or FALSE if an error occured
		 */
		function deleteRows(&$table, &$filter) {
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
		function executeTableFunctions(&$table, &$functionDescriptions, &$filter ,&$columnSorting, $debug=false) {
			return false;
		}

		/**
		 * Executes data mutations on this table with the data that meet the given requirements.
		 *@param DataTable $table the table wich data to use
		 *@param DataMutation $mutationDescriptions the mutations to execute
		 *@param DataFilter $filter a list of requirements the data must meet
		 *@param bool true for debug print of the query and no execution, true for execution and no output
		 */
		function executeTableMutations(&$table, &$mutationDescriptions, &$filter, $debug=false) {
			return false;
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
			return false;
		}

		function setTablePrefix($prefix) {
			$this->privateVars['tablePrefix'] = $prefix;
		}

		function getTablePrefix() {
			return $this->privateVars['tablePrefix'];
		}

		function createTable(&$table) {
		}

		function tableExists(&$table) {
			return false;
		}

		function synchronizeTableStructure(&$table) {
		}

		function renameTableColumn(&$table, $oldName, $alias) {
		}

		function copyTable(&$table, $newName) {
		}

		function dropTable(&$table, $confirmationCode) {
		}

	}
?>
