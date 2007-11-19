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
	 * the Table class is for quickly building html tables
	 *@author Matthijs Groen (matthijs at ivinity.nl)
	 *@version 1.2
	 */
	class Table {

		var $privateVars;
		var $cellSpacing;

		/**
		 * Constructs a table and set the properties to their
		 * default values
		 */
		function Table() {
			// Configuration variables
			$this->privateVars['header'] = array();
			$this->privateVars['headerAlias'] = array();
			$this->privateVars['headerClasses'] = array();
			$this->privateVars['headerColspan'] = array();
			$this->privateVars['showHeader'] = true;

			$this->privateVars['rows'] = array();
			$this->privateVars['rowClasses'] = array();
			$this->privateVars['rowColspan'] = array();

			$this->privateVars['tableClass'] = "table";
			$this->privateVars['tableID'] = "table";
			$this->privateVars['cellLimit'] = -1;

			$this->privateVars["openAll"] = false; // option to 'open' all groups

			// checkbox columns
			$this->privateVars['checkboxColumn'] = -1;
			$this->privateVars['hideColumns'] = array();
			$this->privateVars['checkboxColumnValues'] = array();
			$this->privateVars['checkExclude'] = -1;
			$this->privateVars['checkClick'] = "";
			$this->privateVars['checkClickColumns'] = array();

			$this->privateVars['allowSubGroups'] = -1;
			$this->privateVars['autoCollapse'] = false;
			$this->privateVars['collapseRows'] = array();
			$this->privateVars['collapseImages'] = array();
			$this->privateVars['alignment'] = array();
			$this->privateVars['onChangeScript'] = "";

			global $GLOBALS;
			if (!isSet($GLOBALS['ivTableSpace'])) {
				$this->cellSpacing = 1;
			} else {
				$this->cellSpacing = $GLOBALS['ivTableSpace'];
			}
			// Row selection
			$this->privateVars['rowSelect'] = false;
			$this->privateVars['selectedRow'] = false;
			$this->privateVars['rowSelectParameters'] = array();
			$this->privateVars['rowSelectCallback'] = "";
			$this->privateVars['selectedRowID'] = false;
			$this->privateVars['doubleClickAction'] = false;
			// Column selection
			$this->privateVars['cellSelect'] = false;
			$this->privateVars['selectedCell'] = false;
			$this->privateVars['cellSelectParameters'] = array();
			$this->privateVars['cellSelectCallback'] = "";
			$this->privateVars['cellSelectClickable'] = array();
			$this->privateVars['selectedCellID'] = false;

			// Datafill variables
			$this->privateVars['columns'] = 0;
			$this->privateVars['dataColumns'] = 0;
			$this->privateVars['currentGroup'] = false;
			$this->privateVars["currentGroupOpen"] = false;
			$this->privateVars['subGroups'] = array();
			$this->privateVars['openImage'] = "images/opentable.gif";
			$this->privateVars['closeImage'] = "images/closetable.gif";

			// Writing variables
			$this->privateVars['writingSubgroup'] = false;

			// Sorting
			$this->privateVars['sorting'] = '';
			$this->privateVars['sortingDir'] = false;
			$this->privateVars['enableSorting'] = true;

			// Fake Sorting (display only)
			$this->privateVars['showsort'] = false;
			$this->privateVars['showsortdir'] = true;
			$this->privateVars['showsorturl'] = '';
			$this->privateVars['showsortcolumns'] = array();

			$this->privateVars['ascentImage'] = '';
			$this->privateVars['descentImage'] = '';
		}

		/**
		 * Defines the images used for expanding and collapsing rows.
		 *@param string $openImage the image shown if the group is expanded
		 *@param string $closeImage the image shown if the group is collapsed
		 */
		function setOpenCloseImages($openImage, $closeImage) {
			$this->privateVars['openImage'] = $openImage;
			$this->privateVars['closeImage'] = $closeImage;
		}

		/**
		 * Hides the header of the table
		 */
		function hideHeader() {
			$this->privateVars['showHeader'] = false;
		}

		/**
		 * Marks the row with the given value at the given column as selected
		 *@param string $value
		 *@param int $column index of the column that must have the given value to be selected
		 */
		function selectRow($value, $column=0) {
			$this->privateVars['selectedRow'] = array('value' => $value, 'column' => $column);
		}

		/**
		 * Marks the row with the given value at the given column as selected
		 *@param string $value
		 *@param int $column index of the column that must have the given value to be selected
		 */
		function selectCell($value, $column, $cell) {
			$this->privateVars['selectedCell'] = array('value' => $value, 'column' => $column, 'cell' => $cell);
		}

		/**
		 * Returns the datarow with the given index rownr
		 *@param int $index the index of the row requested
		 *@return mixed Array with data if row is found, bool false otherwise
		 */
		function getDataRow($index) {
			$foundIndex = -1;
			$eot = false;
			$i = 0;
			while (($foundIndex < $index) && (!$eot)) {
				$row = $this->privateVars['rows'][$i];
				if ($row['type'] == 'data') $foundIndex++;
				$i++;
				if ($i >= $this->getRowCount()) $eot = true;
			}
			if ($index == $foundIndex) {
				return $row['cells'];
			}
		}

		function getHeaderRow() {
			$result = array();

			for ($i = 0; $i < $this->privateVars['columns']; $i++) {
					if (!in_array($i, $this->privateVars['hideColumns'])) {
						$result[] = $this->privateVars['headerAlias'][$i];
					}
			}

			return $result;
		}

		/**
		 * Mark that the column with given colNr is shown as a checkbox.
		 */
		function setCheckboxColumn($colNr) {
			$this->privateVars['checkboxColumn'] = $colNr;
		}

		function setCheckboxClick($functionName, $columnsInfo) {
			$this->privateVars['checkClick'] = $functionName;
			$this->privateVars['checkClickColumns'] = $columnsInfo;
		}

		/**
		 *@param array $values array containing the values that are checked.
		 */
		function setCheckedValues($values) {
			$this->privateVars['checkboxColumnValues'] = $values;
		}

		/**
		 *@param string $functionName name of the javascript to call on doubleclick
		 */
		function setRowDoubleClickFunction($functionName) {
			$this->privateVars['doubleClickAction'] = $functionName;
		}

		/**
		 * Make the rows in the table clickable.
		 *@param int $colNr the columndata of this column will be the parameter for the callback function
		 *@param String $callbackFunction name of a javascriptfunction that will be called after clicking on a row
		 *@param bool $hideColumn if true, the column given as $colNr will be hidden.
		 *@deprecated use setRowSelect instead.
		 */
		function setClickColumn($colNr, $callbackFunction, $hideColumn = false) {
			$this->setRowSelect(array($colNr), $callbackFunction);
			if ($hideColumn) {
				$this->hideColumn($colNr);
			}
		}

		/**
		 * Hides datacolumn with the given colNr
		 *@param string $colNr the nr of the datacolumn to hide from display
		 */
		function hideColumn($colNr) {
			$this->privateVars['hideColumns'][] = $colNr;
		}

		/**
		 * Make the rows in the table clickable.
		 *@param array $parameterColumns an array containing the indexes of the datacolumns
		 * to use as parameters for the callback function
		 *@param string $callbackFunction the name of the javascript function to execute when a
		 * row gets selected. Use an empty string to disable the callback function
		 */
		function setRowSelect($parameterColumns, $callbackFunction) {
			$this->privateVars['rowSelect'] = true;
			$this->privateVars['rowSelectParameters'] = $parameterColumns;
			$this->privateVars['rowSelectCallback'] = $callbackFunction;
		}

		/**
		 * Make the cells in the table clickable. This is usefull when using an cellLimit greater than
		 * the number of datacolumns
		 *@param array $parameterColumns an array containing the indexes of the datacolumns
		 * to use as parameters for the callback function
		 *@param string $callbackFunction the name of the javascript function to execute when a
		 * row gets selected. Use an empty string to disable the callback function
		 */
		function setCellSelect($parameterColumns, $clickableColumns, $callbackFunction) {
			$this->privateVars['cellSelect'] = true;
			$this->privateVars['cellSelectParameters'] = $parameterColumns;
			$this->privateVars['cellSelectCallback'] = $callbackFunction;
			$this->privateVars['cellSelectClickable'] = $clickableColumns;
		}


		/**
		 * Forces the table to have the given amount of cells on a row. If the amount is less than the number of
		 * datacolumns, the rows will be 'wrapped'. If the amount is higher than the amount of datacolumns,
		 * multiple datarows will be shown on one tablerow.
		 *@param int $forceAmount the number of cells the row should have.
		 */
		function setCellLimit($forceAmount) {
			$this->privateVars['cellLimit'] = $forceAmount;
			$this->clear();
		}

		/**
		 * Sets the CSS class for the table. Multiple classes can be given if names are separated
		 * with a space (just like in CSS self)
		 *@param string $className
		 */
		function setClass($className) {
			$this->privateVars['tableClass'] = $className;
		}

		/**
		* Sets the ID for the table
		*@param String ID name
		**/
		function setID($tableID) {
			$this->privateVars['tableID'] = $tableID;
		}

		function setAlignment() {
			if (func_num_args() == 1 && is_array(func_get_arg(0))) {
				$array_var = func_get_arg(0);
				$nrArgs = count($array_var);
			}	else {
				$nrArgs = func_num_args();
				$array_var = func_get_args();
			}
			$this->privateVars['alignment'] = $array_var;
		}

		/**
		 * Sets the headernames for the table. This also defines the numer of columns the table has.
		 *@param string $header,... the name of the header
		 */
		function setHeader() {
			if (func_num_args() == 1 && is_array(func_get_arg(0))) {
				$array_var = func_get_arg(0);
				$nrArgs = count($array_var);
			}	else {
				$nrArgs = func_num_args();
				$array_var = func_get_args();
			}
			$this->privateVars['columns'] = $nrArgs;
			$this->privateVars['dataColumns'] = $nrArgs;
			$this->privateVars['header'] = $array_var;
			$this->privateVars['headerAlias'] = $array_var;
			for ($i = 0; $i < $nrArgs; $i ++) {
				$this->privateVars['alignment'][] = "center";
			}
			$this->clear();
		}

		function setHeaderAliasses() {
			if (func_num_args() == 1 && is_array(func_get_arg(0))) {
				$headerArray = func_get_args();
				if (count($headerArray[0]) <> $this->privateVars['columns']) return false;
				$this->privateVars['headerAlias'] = $headerArray[0];
			}	else {
				$nrArgs = func_num_args();
				if ($nrArgs <> $this->privateVars['columns']) return false;
				$this->privateVars['headerAlias'] = func_get_args();
			}
		}

		function getHeaderIndex($alias) {
			return array_search($alias, $this->privateVars['headerAlias']);
		}

		/**
		 * Sets the CSS classes for the columnheaders of the table.
		 *@param string $headerCSSclass,... the CSS class of the columnheader
		 */
		function setHeaderClasses() {
			if (func_num_args() == 1 && is_array(func_get_arg(0))) {
				$headerArray = func_get_args();
				if (count($headerArray[0]) <> $this->privateVars['columns']) return false;
				$this->privateVars['headerClasses'] = $headerArray[0];
			}	else {
				$nrArgs = func_num_args();
				if ($nrArgs <> $this->privateVars['columns']) return false;
				$this->privateVars['headerClasses'] = func_get_args();
			}
		}

		/**
		 * Sets the colspans of the columnheaders of the table
		 *@param string $headerColspan,... the colspan of the columnheader
		 */
		function setHeaderColspan() {
			$nrArgs = func_num_args();
			if ($nrArgs <> $this->privateVars['columns']) return false;
			$this->privateVars['headerColspan'] = func_get_args();
			$dataCols = 0;
			for ($i = 0; $i < $this->privateVars['columns']; $i++) {
				$dataCols += func_get_arg($i);
			}
			$this->privateVars['dataColumns'] = $dataCols;
			$this->clear();
		}

		/**
		 * Sets the colspans of the column of the table
		 *@param string $rowColspan,... the colspan of the column
		 */
		function setRowColspan() {
			$nrArgs = func_num_args();
			$nrHeads = 0;
			for ($i = 0; $i < $nrArgs; $i++) {
				$nrHeads += func_get_arg($i);
			}
			if ($nrHeads == $this->privateVars['columns']) {
				$this->privateVars['dataColumns'] = $nrArgs;
				$this->privateVars['rowColspan'] = func_get_args();
			}
		}

		/**
		 * Sets the CSS style class of the cells of a row
		 *@param string $rowcellCSSclass,... the CSS class of the cells of the row
		 */
		function setRowClasses() {
			if (func_num_args() == 1 && is_array(func_get_arg(0))) {
				$array_var = func_get_arg(0);
				$nrArgs = count($array_var);
			}	else {
				$nrArgs = func_num_args();
				$array_var = func_get_args();
			}
			$dataColumns = $this->privateVars['dataColumns'];
			if ($this->privateVars['cellLimit'] > 0) {
				$force = $this->privateVars['cellLimit'];
				if (($force < $dataColumns) && (($nrArgs % $force) != 0))  return false;
				if (($force > $dataColumns) && (($force % $nrArgs) != 0))  return false;
			} else {
				if ($nrArgs <> $dataColumns) return false;
			}
			$this->privateVars['rowClasses'] = $array_var;
		}

		/**
		 * Enables subgroups that can expand and collide.
		 *@param string $uniqueColumn index of the datacolumn that has unique values
		 */
		function allowSubgroups($uniqueColumn, $autoCollapse = false) {
			$this->privateVars['allowSubGroups'] = $uniqueColumn;
			$this->privateVars['autoCollapse'] = $autoCollapse;
		}

		/**
		* Sets the 'openAll' variable, used when printing the table to 'open' all the groups
		*@param bool, openAll, override the close per row
		**/
		function openAllRows($openAll = true) {
			$this->privateVars["openAll"] = $openAll;
		}

		/**
		 * Adds a row of data into the table
		 *@param string $dataCell,... value of the datacells of the new row in the table
		 */
		function addRow() {
			if (func_num_args() == 1 && is_array(func_get_arg(0))) {
				$array_var = func_get_arg(0);
				$nrArgs = count($array_var);
			}	else {
				$nrArgs = func_num_args();
				$array_var = func_get_args();
			}

			$dataColumns = $this->privateVars['dataColumns'];
			if ($this->privateVars['cellLimit'] > 0) {
				$force = $this->privateVars['cellLimit'];
				if (($force < $dataColumns) && (($nrArgs % $force) != 0))  return false;
				if (($force > $dataColumns) && (($force % $nrArgs) != 0))  return false;
			} else {
				if ($nrArgs <> $dataColumns) return false;
			}
			$groupID = $this->privateVars['currentGroup'];
			$groupsUnique = $this->privateVars['allowSubGroups'];
			if ($groupID === false) $open = true;
			else {
				$open = $this->privateVars["currentGroupOpen"];
				$this->privateVars['subGroups'][$groupID][] = $array_var[$groupsUnique];
			}
			$this->privateVars['rows'][] = array('type' => 'data', 'cells' => $array_var, 'group' => $groupID, 'open' => $open);
		}

		function addTable(&$table) {
			if ($table->privateVars['dataColumns'] != $this->privateVars['dataColumns']) return false;
			$this->privateVars['rows'] = array_merge($this->privateVars['rows'], $table->privateVars['rows']);

			return true;
		}

		/**
		 * Adds a grouping column. This is a header that spans the entire width of the table
		 *@param string $data data that is in the header.
		 */
		function addGroup($data) {
			$this->privateVars['rows'][] = array('type' => 'group', 'name' => $data);
		}

		/**
		 * Starts a collidable group in the table.
		 *@param bool $open true if the group is expanded on default. False if collided
		 *@param string $dataCell,... value of the datacells of the new row in the table
		 */
		function startSubGroup($open) {
			if (func_num_args() == 2 && is_array(func_get_arg(1))) {
				$array_var = func_get_arg(1);
				$nrArgs = count($array_var);
			}	else {
				$nrArgs = func_num_args()-1;
				$array_var = array_slice(func_get_args(),1);
			}

			/* GUIDO: 23-09-2004: replaced by code above
			$nrArgs = func_num_args();
			*/

			if ($this->privateVars['allowSubGroups'] == -1) return false;
			if ($nrArgs <> $this->privateVars['dataColumns']) return false;

			$groupID = $array_var[0];
			/* GUIDO: 23-09-2004: replaced by code above
			$groupID = func_get_arg($this->privateVars['allowSubGroups'] + 1);*/

			$this->privateVars["currentGroup"] = $groupID;
			$this->privateVars["currentGroupOpen"] = $open;
			$this->privateVars['subGroups'][$groupID] = array();

			$cells = array();
			foreach($array_var AS $cellValue) $cells[] = $cellValue;

			/* GUIDO: 23-09-2004: replaced by code above
			for ($i = 1; $i < $nrArgs; $i++) {
				$cells[] = func_get_arg($i);
			}*/
			$this->privateVars['rows'][] = array('type' => 'expander', 'cells' => $cells, 'open' => $open);
		}

		/**
		 * Ends the subgroup and all new rows will be shown as default
		 */
		function endSubGroup() {
			$this->privateVars["currentGroup"] = false;
		}

		/**
		 * Returns the number of rows in the table
		 */
		function getRowCount() {
			return count($this->privateVars['rows']);
		}

		/**
		 * Clears all rows in the table. The configuration remains.
		 */
		function clear() {
			$this->privateVars['rows'] = array();
		}


		function setSortAscentImage($url) {
			$this->privateVars['ascentImage'] = $url;
		}

		function setSortDescentImage($url) {
			$this->privateVars['decentImage'] = $url;
		}

		function showSorting($columnIndex, $ascending, $sortingUrl, $columns) {
			$this->privateVars['showsort'] = $columnIndex;
			$this->privateVars['showsortdir'] = $ascending;
			$this->privateVars['showsorturl'] = $sortingUrl;
			$this->privateVars['showsortcolumns'] = $columns;
		}

		/**
		 * Prints the xhtml output of the table
		 */
		function showTable($enableSorting = false) {
			print $this->getTableString($enableSorting);
		}

		function sortTable($columnNR, $orderBool) {
			if ((getType($columnNR) == "string") && (!is_numeric($columnNR))) $columnNR = $this->getColumnNumber($columnNR);
			if(is_numeric($columnNR) && $columnNR >= 0) {
				if ($orderBool)
					$compare = create_function('$a,$b','
											if (isset($a["cells"]) && isset($b["cells"])) {
												if (is_numeric($b["cells"]['.$columnNR.']) && is_numeric($a["cells"]['.$columnNR.'])) {
													setType($b["cells"]['.$columnNR.'],"float");
													setType($a["cells"]['.$columnNR.'],"float");
													if($b["cells"]['.$columnNR.'] == $a["cells"]['.$columnNR.']) return 0;
													if($b["cells"]['.$columnNR.'] > $a["cells"]['.$columnNR.']) return 1;
													if($b["cells"]['.$columnNR.'] < $a["cells"]['.$columnNR.']) return -1;
												}
												else if (ereg ("([0-9]{1,2})-([0-9]{1,2})-([0-9]{4}) ([0-9]{2}):([0-9]{2})", substr($b["cells"]['.$columnNR.'],0,16), $regs) && ereg ("([0-9]{1,2})-([0-9]{1,2})-([0-9]{4}) ([0-9]{2}):([0-9]{2})", substr($a["cells"]['.$columnNR.'],0,16), $regs2)) {
   												$bTime = new DateTime($regs[4],$regs[5],0,$regs[2],$regs[1],$regs[3]);
   												$aTime = new DateTime($regs2[4],$regs2[5],0,$regs2[2],$regs2[1],$regs2[3]);
   												if($bTime->isEqual($aTime)) return 0;
   												if($bTime->after($aTime)) return 1;
   												if($bTime->before($aTime)) return -1;
	 											}
												else if (ereg ("([0-9]{1,2})-([0-9]{1,2})-([0-9]{4})", substr($b["cells"]['.$columnNR.'],0,10), $regs) && ereg ("([0-9]{1,2})-([0-9]{1,2})-([0-9]{4})", substr($a["cells"]['.$columnNR.'],0,10), $regs2)) {

   												$bTime = new DateTime(0,0,0,$regs[2],$regs[1],$regs[3]);
   												$aTime = new DateTime(0,0,0,$regs2[2],$regs2[1],$regs2[3]);
   												if($bTime->isEqual($aTime)) return 0;
   												if($bTime->after($aTime)) return 1;
   												if($bTime->before($aTime)) return -1;
	 											}
   											else
													return strcasecmp($b["cells"]['.$columnNR.'],$a["cells"]['.$columnNR.']);
											}
										');
				else
					$compare = create_function('$a,$b','
											if (isset($a["cells"]) && isset($b["cells"])) {
												if (is_numeric($b["cells"]['.$columnNR.']) && is_numeric($a["cells"]['.$columnNR.'])) {
													setType($b["cells"]['.$columnNR.'],"float");
													setType($a["cells"]['.$columnNR.'],"float");
													if($b["cells"]['.$columnNR.'] == $a["cells"]['.$columnNR.']) return 0;
													if($b["cells"]['.$columnNR.'] > $a["cells"]['.$columnNR.']) return -1;
													if($b["cells"]['.$columnNR.'] < $a["cells"]['.$columnNR.']) return 1;
												}
												else if (ereg ("([0-9]{1,2})-([0-9]{1,2})-([0-9]{4}) ([0-9]{2}):([0-9]{2})", substr($b["cells"]['.$columnNR.'],0,16), $regs) && ereg ("([0-9]{1,2})-([0-9]{1,2})-([0-9]{4}) ([0-9]{2}):([0-9]{2})", substr($a["cells"]['.$columnNR.'],0,16), $regs2)) {
   												$bTime = new DateTime($regs[4],$regs[5],0,$regs[2],$regs[1],$regs[3]);
   												$aTime = new DateTime($regs2[4],$regs2[5],0,$regs2[2],$regs2[1],$regs2[3]);
   												if($bTime->isEqual($aTime)) return 0;
   												if($bTime->after($aTime)) return -1;
   												if($bTime->before($aTime)) return 1;
	 											}
												else if (ereg ("([0-9]{1,2})-([0-9]{1,2})-([0-9]{4})", substr($b["cells"]['.$columnNR.'],0,10), $regs) && ereg ("([0-9]{1,2})-([0-9]{1,2})-([0-9]{4})", substr($a["cells"]['.$columnNR.'],0,10), $regs2)) {
   												$bTime = new DateTime(0,0,0,$regs[2],$regs[1],$regs[3]);
   												$aTime = new DateTime(0,0,0,$regs2[2],$regs2[1],$regs2[3]);
   												if($bTime->isEqual($aTime)) return 0;
   												if($bTime->after($aTime)) return -1;
   												if($bTime->before($aTime)) return 1;
	 											}
   											else
													return strcasecmp($a["cells"]['.$columnNR.'],$b["cells"]['.$columnNR.']);
											}
										');

				usort($this->privateVars['rows'],$compare);
			}
		}

		function getColumnNumber($ColumnName) {
			return array_search($ColumnName, $this->privateVars['header']);
		}

		/**
		 * return the xhtml output in form of a string
		 *@return string the xhtml output
		 */
		function getTableString($enableSorting = false) {
			$this->privateVars['enableSorting'] = $enableSorting;

			if($this->privateVars['enableSorting']) {
				if(isset($_GET['sort']) && isset($_GET['sortDir'])) {
					$this->privateVars['sortingDir'] = ($_GET['sortDir'] == 'asc') ? false : true;
					$this->privateVars['sorting'] = stripSlashes($_GET['sort']);
				}
				if(isset($_POST['sort']) && isset($_POST['sortDir'])) {
					$this->privateVars['sortingDir'] = ($_POST['sortDir'] == 'asc') ? false : true;
					$this->privateVars['sorting'] = $_POST['sort'];
				}
				if ($this->privateVars['sorting'] != '') {
					$this->sortTable($this->privateVars['sorting'],$this->privateVars['sortingDir']);
				}
			}

			$tblID = uniqID('tbljs');
			$n = "\n";
			$t = "\t";
			$resultString = "";
			$resultString .= $t.'<div class="center">'.$n;
			$resultString .= $t.'<table id="'.$this->privateVars['tableID'].'" class="'.$this->privateVars['tableClass'].'" cellspacing="'.$this->cellSpacing.'">'.$n;
			// Draw the header of the table
			if ($this->privateVars['showHeader']) $resultString .= $this->getHeaderString();

			$resultString .= $t.$t.'<tbody>'.$n;
			$inActiveGroup = false;

			$startCel = 0;
			$dataColumns = $this->privateVars['dataColumns'];
			$forcedWidth = ($this->privateVars['cellLimit'] == -1) ? $this->privateVars['dataColumns'] : $this->privateVars['cellLimit'];

			$rowNr = 0;

			for ($i = 0; $i < count($this->privateVars['rows']); $i++) {
				$row = $this->privateVars['rows'][$i];
				if ($row['type'] == 'data') $resultString .= $this->getDataRowString($row, $rowNr, $tblID, $startCel);
				else if ($row['type'] == 'expander') $resultString .= $this->getExpanderRowString($row, $rowNr, $tblID, $startCel);
				else if ($row['type'] == 'group') {
					$resultString .= $this->getGroupRowString($row, $i, $tblID, $startCel);
					$startCel = $startCel - $dataColumns; // Trick the mechanism to start new row
				}
				$startCel = ($startCel + $dataColumns) % $forcedWidth;
				if ($startCel == 0) $rowNr++;
			}
			$resultString .=  $t.$t.'</tbody>'.$n;
			$resultString .=  $t.'</table>'.$n;
			//if (($this->privateVars['rowSelect']) || ($this->privateVars['cellSelect'])) {
			$resultString .= $t.$t.'<script type="text/javascript"><!--'.$n;
			if ($this->privateVars['rowSelect'])
				$resultString .= $this->getRowSelectionJavascript($tblID, $this->privateVars['selectedRowID']);
			if ($this->privateVars['cellSelect'])
				$resultString .= $this->getCellSelectionJavascript($tblID, $this->privateVars['selectedCellID']);
			if ($this->privateVars['allowSubGroups'] != -1)
				$resultString .= $this->getGroupOpenJavascript($tblID);
			$resultString .= $t.$t.'// -->'.$n;
			$resultString .= $t.$t.'</script>'.$n;
			//}
			$resultString .=  $t.'</div>'.$n;
			return $resultString;
		}

		function getGroupOpenJavascript($tblID) {
			$n = "\n";
			$t = "\t";
			$resultString  = $t.$t.$t.'function '.$tblID.'openGroup(rowIDarray, imgID, openImage, closeImage) {'.$n;
			if ($this->privateVars['autoCollapse'] === true) {
				$resultString .= $t.$t.$t.$t."var allRowIDs = new Array('".implode("', '", $this->privateVars['collapseRows'])."');".$n;
				$resultString .= $t.$t.$t.$t."var allImgIDs = new Array('".implode("', '", $this->privateVars['collapseImages'])."');".$n;
				$resultString .= $t.$t.$t.$t."closeAll(allRowIDs, allImgIDs, closeImage, '".$tblID."');".$n;
			}
			$resultString .= $t.$t.$t.$t.'changeGroup(rowIDarray, imgID, openImage, closeImage);'.$n;
			$resultString .= $t.$t.$t.'}'.$n;
			return $resultString;
		}

		/**
		 * Returns the xhtml output for a javascript to select table rows
		 *@param string $tblID the uniqueID for the xhtml table
		 *@param string $selectedRow the identifier of the default selected row. Empty string if no row selected
		 *@return string the xhtml output of the javascript
		 */
		function getRowSelectionJavascript($tblID, $selectedRow) {
			$n = "\n";
			$t = "\t";
			$resultString = $t.$t.$t.'var '.$tblID.'selectedRow = \'';
			if ($selectedRow !== false)
				$resultString .= $tblID.$selectedRow;
			$resultString .= '\';'.$n.$n;
			$resultString .= $t.$t.$t.'function '.$tblID.'SelectRow(id, parameters) {'.$n;
			$resultString .= $t.$t.$t.$t.'if ('.$tblID.'selectedRow != \'\') {'.$n;
			$resultString .= $t.$t.$t.$t.$t.'// There was a record selected already!'.$n;
			$resultString .= $t.$t.$t.$t.$t.'var oldSelect = document.getElementById('.$tblID.'selectedRow);'.$n;
			$resultString .= $t.$t.$t.$t.$t.'removeCssClass(oldSelect, \'selectedRow\');'.$n;
			$resultString .= $t.$t.$t.$t.'}'.$n;
			$resultString .= $t.$t.$t.$t.'var rowID = \''.$tblID.'\' + id;'.$n;
			$resultString .= $t.$t.$t.$t.'var newSelect = document.getElementById(rowID);'.$n;
			$resultString .= $t.$t.$t.$t.'addCssClass(newSelect, \'selectedRow\');'.$n;
			$resultString .= $t.$t.$t.$t.''.$tblID.'selectedRow = rowID;'.$n;

			if ($this->privateVars['rowSelectCallback'] != "") {
				$parameters = "";
				if (count($this->privateVars['rowSelectParameters']) > 0) {
					$params = array();
					for ($par = 0; $par < count($this->privateVars['rowSelectParameters']); $par++) $params[] = $par;
					$parameters = "parameters[".implode("], parameters[", $params)."]";
				}
				$resultString .= $t.$t.$t.$t.''.$this->privateVars['rowSelectCallback'].'('.$parameters.');'.$n;
			}
			$resultString .= $t.$t.$t.'}'.$n;

			if ($this->privateVars['doubleClickAction'] !== false) {
				$resultString .= $n;
				$resultString .= $t.$t.$t.'function '.$tblID.'ExecuteRow(id, parameters) {'.$n;
				$resultString .= $t.$t.$t.$t.$tblID.'SelectRow(id, parameters);'.$n;
				$resultString .= $t.$t.$t.$t.$this->privateVars['doubleClickAction'].'();'.$n;
				$resultString .= $t.$t.$t.'}'.$n;
			}

			//$resultString .= $t.$t.$t.'function '.$tblID.'refreshSelectedRow(id) {'.$n;
			//$resultString .= $t.$t.$t.$t.'alertML(id);'.$n;
			//$resultString .= $t.$t.$t.$t.'var newSelect = document.getElementById(id);'.$n;
			//$resultString .= $t.$t.$t.$t.'addCssClass(newSelect, \'selectedRow\');'.$n;
			//$resultString .= $t.$t.$t.'}'.$n;
			return $resultString;
		}

		/**
		 * Returns the xhtml output for a javascript to select table rows
		 *@param string $tblID the uniqueID for the xhtml table
		 *@param string $selectedRow the identifier of the default selected row. Empty string if no row selected
		 *@return string the xhtml output of the javascript
		 */
		function getCellSelectionJavascript($tblID, $selectedCell) {
			$n = "\n";
			$t = "\t";
			$resultString = $t.$t.$t.'var '.$tblID.'selectedCell = \'';
			if ($selectedCell !== false)
				$resultString .= $tblID.$selectedCell;

			$parameters = "";
			if (count($this->privateVars['cellSelectParameters']) > 0) {
				$params = array();
				for ($par = 0; $par < count($this->privateVars['cellSelectParameters']); $par++) $params[] = $par;
				$parameters = "parameters[".implode("], parameters[", $params)."]";
				$previousParameters = '\'"+parameters['.implode(']+"\', \'"+parameters[', $params).']+"\'';
			}

			$resultString .= '\';'.$n.$n;
			$resultString .= $t.$t.$t.'function '.$tblID.'SelectCell(id, cellIndex, parameters) {'.$n;
			$resultString .= $t.$t.$t.$t.'if ('.$tblID.'selectedCell != \'\') {'.$n;
			$resultString .= $t.$t.$t.$t.$t.'// There was a record selected already!'.$n;
			$resultString .= $t.$t.$t.$t.$t.'var oldSelect = document.getElementById('.$tblID.'selectedCell);'.$n;
			$resultString .= $t.$t.$t.$t.$t.'removeCssClass(oldSelect, \'selectedCell\');'.$n;
			$resultString .= $t.$t.$t.$t.$t.'selectPreviousCell = '.$tblID.'selectedCell;'.$n;
			$resultString .= $t.$t.$t.$t.'} else {'.$n;
			$resultString .= $t.$t.$t.$t.$t.'selectPreviousCell = "";'.$n;
			$resultString .= $t.$t.$t.$t.'}'.$n;
			$resultString .= $t.$t.$t.$t.'var cellID = \''.$tblID.'\' + id;'.$n;
			$resultString .= $t.$t.$t.$t.'selectCurrentCell = cellID;'.$n;
			$resultString .= $t.$t.$t.$t.'var newSelect = document.getElementById(cellID);'.$n;
			$resultString .= $t.$t.$t.$t.'addCssClass(newSelect, \'selectedCell\');'.$n;
			$resultString .= $t.$t.$t.$t.''.$tblID.'selectedCell = cellID;'.$n;
			$resultString .= $t.$t.$t.$t.'cellVarName = "'.$tblID.'selectedCell";'.$n;

			if ($this->privateVars['cellSelectCallback'] != "") {
				$resultString .= $t.$t.$t.$t.$t.''.$this->privateVars['cellSelectCallback'].'(cellIndex, '.$parameters.');'.$n;
			}
			$resultString .= $t.$t.$t.'}'.$n;
			return $resultString;
		}

		/**
		 * Returns the xhtml output of the table header
		 *@return string the xhtml output of the table header
		 */
		function getHeaderString() {
			$n = "\n";
			$t = "\t";
			$resultString = $t.$t.'<thead>'.$n;
			$resultString .= $t.$t.$t.'<tr>'.$n;
			$checkboxAll = uniqId("cka");
			$dataColumnPos = 0;

			$forcedWidth = ($this->privateVars['cellLimit'] == -1) ? $this->privateVars['dataColumns'] : $this->privateVars['cellLimit'];
			if ($forcedWidth > $this->privateVars['dataColumns']) {
				$nrTimes = ($forcedWidth / $this->privateVars['dataColumns']);
			} else $nrTimes = 1;

			for ($repeat = 0; $repeat < $nrTimes; $repeat++) {
				for ($i = 0; $i < $this->privateVars['columns']; $i++) {
					if (!in_Array($dataColumnPos, $this->privateVars['hideColumns'])) {
						$headerClass = "";
						if (isSet($this->privateVars['headerClasses'][$i])) {
							$headerClass = $this->privateVars['headerClasses'][$i]." ";
						}
						$colSpan = 1;
						if (isSet($this->privateVars['headerColspan'][$i])) {
							$colSpan = $this->privateVars['headerColspan'][$i];
						}
						if ($colSpan > 1) {
							$dataColumnPos += ($colSpan -1);
						}
						$resultString .= $t.$t.$t.$t.'<th class="'.$headerClass.(($i % 2 == 1) ? "even" : "odd").'"'.(($colSpan > 1) ? ' colspan="'.$colSpan.'"' : "").'>';
						if ($i != $this->privateVars['checkboxColumn']) {
							$resultString .= $this->makeHeaderURL($i);
						} else {
							$resultString .= '<input type="checkbox" name="'.$checkboxAll.'" onchange="'.$this->privateVars['onChangeScript'].'" onclick="javascript:check(this.form[\''.$this->privateVars['header'][$i].'[]\'], this.form[\''.$checkboxAll.'\'])" />';
						}
						$resultString .= '</th>'.$n;
					}
					$dataColumnPos ++;
				}
				$dataColumnPos = 0;
			}

			$resultString .= $t.$t.$t.'</tr>'.$n;
			$resultString .= $t.$t.'</thead>'.$n;
			return $resultString;
		}

		function makeHeaderUrl($i) {
			$header = $this->privateVars['header'][$i];
			$showSorting = false;
			if ($this->privateVars['enableSorting']) {
				$showSorting = true;
				$sortUrl = rewriteUrlParameters($_SERVER['REQUEST_URI'], array('sort'=>'%1$s', 'sortDir'=>'%2$s'));
				$sortingCol = $this->privateVars['sorting'];
				$sortingDir = $this->privateVars['sortingDir'];
				$sortingCols = array();
				for ($h = 0; $h < count($this->privateVars['header']); $h++) $sortingCols[] = $h;
			}
			if ($this->privateVars['showsort'] !== false) {
				$showSorting = true;
				$sortUrl = $this->privateVars['showsorturl'];
				$sortingCol = $this->privateVars['showsort'];
				$sortingDir = !$this->privateVars['showsortdir'];
				$sortingCols = $this->privateVars['showsortcolumns'];
			}

			if ($showSorting) {
				$selected = ($i == $sortingCol);
				if ($selected) {
						$ascentString = 'v';
						if ($this->privateVars['ascentImage'] == '') {
							global $globalAscentImage; if ($globalAscentImage != '') $ascentString = '<img src="'.$globalAscentImage.'" border="0" />';
						}	else { $ascentString = '<img src="'.$this->privateVars["ascentImage"].'" border="0" />';	}
						$descentString = '^';
						if ($this->privateVars['descentImage'] == '') {
							global $globalDescentImage; if ($globalDescentImage != '') $descentString = '<img src="'.$globalDescentImage.'" border="0" />';
						} else { $descentString = '<img src="'.$this->privateVars["descentImage"].'" border="0" />'; }


						$header = sprintf('<a href="%s">%s</a> %s',
							sprintf($sortUrl, (!$sortingDir) ? $i : -1, (!$sortingDir) ? 'desc' : 'asc'), $header,
							(!$sortingDir) ? $ascentString : $descentString);
				} else if (in_array($i, $sortingCols)) {
					$linkUrl = sprintf($sortUrl, $i, 'asc');
					$header = sprintf('<a href="%s">%s</a>', $linkUrl, $header);
				}
			}
			return $header;
		}

		/**
		 * Returns the xhtml output of a grouprow
		 *@param string $row the data of the row
		 *@param string $rowNr the index of the row in the table
		 *@param string $tblID the uniqueID for the xhtml table
		 *@param int $startCell the starting cell within the row
		 *@return string the xhtml output of a grouprow
		 */
		function getGroupRowString($row, $rowNr, $tblID, $startCell) {
			$n = "\n";
			$t = "\t";
			$forcedWidth = ($this->privateVars['cellLimit'] == -1) ? $this->privateVars['dataColumns'] : $this->privateVars['cellLimit'];
			$columnWidth = $this->privateVars['dataColumns'];
			for ($i = 0; $i < $this->privateVars['dataColumns']; $i++) {
				if (isSet($this->privateVars['rowColspan'][$i])) {
					$columnWidth += $this->privateVars['rowColspan'][$i] -1;
				}
			}

			if (($forcedWidth == $columnWidth) && ($this->privateVars['cellLimit'] == -1)) {
				$forcedWidth -= count($this->privateVars['hideColumns']);
			} else if ($this->privateVars['cellLimit'] > $this->privateVars['dataColumns']) {
				$times = ($this->privateVars['cellLimit'] / $this->privateVars['dataColumns']);
				$forcedWidth -= (count($this->privateVars['hideColumns']) * $times);
			}

			$resultString =  $t.$t.$t.'<tr>'.$n;
			$resultString .=  $t.$t.$t.$t.'<th class="rowgroup" colspan="'.$forcedWidth.'">'.$row['name'].'</th>'.$n;
			$resultString .=  $t.$t.$t.'</tr>'.$n;
			return $resultString;
		}

		/**
		 * Returns the xhtml output of a datarow
		 *@param string $row the data of the row
		 *@param string $rowNr the index of the row in the table
		 *@param string $tblID the uniqueID for the xhtml table
		 *@param int $startCell the starting cell within the row
		 *@return string the xhtml output of a datarow
		 */
		function getDataRowString($row, $rowNr, $tblID, $startCell) {
			$n = "\n";
			$t = "\t";
			$resultString = "";
			$checkboxValues = $this->privateVars['checkboxColumnValues'];

			$forcedWidth = ($this->privateVars['cellLimit'] == -1) ? $this->privateVars['dataColumns'] : $this->privateVars['cellLimit'];
			$rowClick = "";
			$selected = "";
			$subName = "";
			$rowID = uniqID('tdr');
			if ($this->privateVars['allowSubGroups'] > -1) {
				$rowID = $row['cells'][$this->privateVars['allowSubGroups']];
			}
			if ($startCell == 0) {
				if ($this->privateVars['rowSelect']) {
					$columnValues = array();
					for ($par = 0; $par < count($this->privateVars['rowSelectParameters']); $par++) {
						$columnIndex = $this->privateVars['rowSelectParameters'][$par];
						$columnValues[] = htmlConvert(addSlashes($row['cells'][$columnIndex]));
					}
					$rowClick = " onclick=\"". $tblID . "SelectRow('". $rowID ."', new Array('".implode("', '", $columnValues)."'))\"";
					if ($this->privateVars['doubleClickAction'] !== false) {
						$rowClick .= " ondblclick=\"". $tblID . "ExecuteRow('". $rowID ."', new Array('".implode("', '", $columnValues)."'))\"";
					}
				}
				if ($this->privateVars['writingSubgroup'] != false) {
					$this->privateVars['collapseRows'][] = $rowID;
					if (in_Array($rowID, $this->privateVars['subGroups'][$this->privateVars['writingSubgroup']])) $subName = "sub";
					else $this->privateVars['writingSubgroup'] = false;
				}
				// check if this row is selected
				if ($this->privateVars['selectedRow'] !== false) {
					$selectionInfo = $this->privateVars['selectedRow'];
					if ($row['cells'][$selectionInfo['column']] == $selectionInfo['value']) {
						$selected = " selectedRow";
						$this->privateVars['selectedRowID'] = $rowID;
					}
				}

				$openRow = $row['open'];
				if($this->privateVars["openAll"]) $openRow = $this->privateVars["openAll"];
				$resultString .=  $t.$t.$t.'<tr id="'.$tblID.$rowID.'" class="'.$subName.(($rowNr % 2 == 0) ? "even" : "odd").'Row'.$selected.((!$openRow)? " closeGroup" : "").'"'.$rowClick.'>'.$n;
			}
			$maxWidth = ($forcedWidth > count($row['cells'])) ? count($row['cells']) : $forcedWidth;

			$cellClickArray = array();
			if ($this->privateVars['cellSelect']) {
				$cellClickArray = $this->privateVars['cellSelectClickable'];
			}
			$cellPosition = $startCell - (($startCell / count($row['cells'])) * count($this->privateVars['hideColumns']));

			for ($k = 0; $k < count($row['cells']); ) {
				for ($j = 0; $j < $maxWidth; $j++) {
					$cellClass = "";
					$selectedCell = "";
					if (isSet($this->privateVars['rowClasses'][$k])) {
						$cellClass = $this->privateVars['rowClasses'][$k]." ";
					}
					if (!in_Array($k, $this->privateVars['hideColumns'])) {
						$cellClick = "";
						if (in_Array($k, $cellClickArray)) {
							$columnValues = array();
							for ($par = 0; $par < count($this->privateVars['cellSelectParameters']); $par++) {
								$columnIndex = $this->privateVars['cellSelectParameters'][$par];
								$columnValues[] = htmlConvert(addSlashes($row['cells'][$columnIndex]));
							}
							$cellClick = " onclick=\"". $tblID . "SelectCell('". $rowID.$k ."', ".$k.", new Array('".implode("', '", $columnValues)."'))\" id=\"".$tblID.$rowID.$k."\"";

							if ($this->privateVars['selectedCell'] !== false) {
								$selectionInfo = $this->privateVars['selectedCell'];
								if ($row['cells'][$selectionInfo['column']] == $selectionInfo['value']) {
									$selectedCell = "selectedCell ";
									$this->privateVars['selectedCellID'] = $rowID.$k;
								}
							}
						}

						$colSpan = 1;
						if (isSet($this->privateVars['rowColspan'][$k])) {
							$colSpan = $this->privateVars['rowColspan'][$k];
						}
						$sortingClass = "";
						if (($this->privateVars['enableSorting']) && ($this->privateVars['sorting'] == $k)) $sortingClass = "Sorted";
						if (($this->privateVars['showsort'] !== false) && ($this->privateVars['showsort'] == $k)) $sortingClass = "Sorted";

						if (isSet($this->privateVars['alignment'][$k])) {
							$cellAlignment = $this->privateVars['alignment'][$k];
						} else { $cellAlignment = "center"; }

						$resultString .=  $t.$t.$t.$t.'<td class="'.$selectedCell.$cellClass.(($cellPosition % 2 == 1) ? "even" : "odd").$sortingClass.'Cell"'.(($colSpan > 1) ? ' colspan="'.$colSpan.'"' : "").$cellClick.' align="'.$cellAlignment.'">';
						if ($k != $this->privateVars['checkboxColumn']) {
							$resultString .=  $row['cells'][$k];
						} else {
							$checked = "";
							$boxName = $this->privateVars['header'][$j];
							$value = $row['cells'][$k];
							if (isSet($_POST[$boxName]) && in_Array($value, $_POST[$boxName])) {
								$checked = 'checked="checked" ';
							} else if ((in_Array($value, $checkboxValues)) && (!isSet($_POST[$boxName]))) {
								$checked = 'checked="checked" ';
							}
							if ($value == $this->privateVars['checkExclude'])
								$resultString .= '';
							else {
								$onClick = "";
								if ($this->privateVars['checkClick'] != "") {
									$columnValues = array();
									for ($par = 0; $par < count($this->privateVars['checkClickColumns']); $par++) {
										$columnIndex = $this->privateVars['checkClickColumns'][$par];
										$columnValues[] = htmlConvert(addSlashes($row['cells'][$columnIndex]));
									}
									$rowClick = " onclick=\"". $tblID . "SelectRow('". $rowID ."', new Array('".implode("', '", $columnValues)."'))\"";

									$onClick = sprintf('onclick="%s(this.checked, \'%s\')" ', $this->privateVars['checkClick'], implode("', '", $columnValues));
								}
								$resultString .= sprintf('<input type="checkbox" name="%s[]" value="%s" %s %s onchange="%s"/>', $boxName, $value, $checked, $onClick, $this->privateVars['onChangeScript']);
							}
						}
						$resultString .= '</td>'.$n;
						$cellPosition++;
					}
					$k++;
				}
				if ($k < count($row['cells']) - 1) {
					$resultString .=  $t.$t.$t.'</tr>'.$n;
					$resultString .=  $t.$t.$t.'<tr class="'.$subName.(($rowNr % 2 == 0) ? "even" : "odd").'Row'.$selected.((!$row['open'])? " closeGroup" : "").'"'.$rowClick.'>'.$n;
				}
			}
			if (($startCell + count($row['cells'])) % $forcedWidth == 0) {
				$resultString .= $t.$t.$t.'</tr>'.$n;
			}
			return $resultString;
		}

		/**
		 * Returns the xhtml output of a expanderrow
		 *@param string $row the data of the row
		 *@param string $rowNr the index of the row in the table
		 *@param string $tblID the uniqueID for the xhtml table
		 *@param int $startCell the starting cell within the row
		 *@return string the xhtml output of a expanderrow
		 */
		function getExpanderRowString($row, $rowNr, $tblID, $startCell) {
			$n = "\n";
			$t = "\t";
			$resultString = "";
			$checkboxValues = $this->privateVars['checkboxColumnValues'];
			// Handling of row data if cellLimit is on
			$forcedWidth = ($this->privateVars['cellLimit'] == -1) ? $this->privateVars['dataColumns'] : $this->privateVars['cellLimit'];
			$handledGroupTag = false;
			$rowClick = "";
			$selected = "";
			$expandID = $row['cells'][$this->privateVars['allowSubGroups']];
			if ($this->privateVars['rowSelect']) {
				$columnValues = array();
				for ($par = 0; $par < count($this->privateVars['rowSelectParameters']); $par++) {
					$columnIndex = $this->privateVars['rowSelectParameters'][$par];
					$columnValues[] = htmlConvert($row['cells'][$columnIndex]);
				}
				$rowClick = " onclick=\"". $tblID . "SelectRow('". $expandID ."', new Array('".implode("', '", $columnValues)."'))\" id=\"".$tblID.$expandID."\"";


				// check if this row is selected
				if ($this->privateVars['selectedRow'] !== false) {
					$selectionInfo = $this->privateVars['selectedRow'];
					if ($row['cells'][$selectionInfo['column']] == $selectionInfo['value']) {
						$selected = " selectedRow";
						$this->privateVars['selectedRowID'] = $expandID;
					}
				}
			}
			$cellPosition = $startCell - (($startCell / count($row['cells'])) * count($this->privateVars['hideColumns']));

			$resultString .=  $t.$t.$t.'<tr class="'.(($rowNr % 2 == 0) ? "even" : "odd").'Row'.$selected.'" '.$rowClick.' id="'.$tblID.$expandID.'">'.$n;
			for ($k = 0; $k < count($row['cells']); ) {
				for ($j = 0; $j < $forcedWidth; $j++) {
					$cellClass = "";
					if (isSet($this->privateVars['rowClasses'][$k])) {
						$cellClass = $this->privateVars['rowClasses'][$k]." ";
					}
					if (!in_Array($k, $this->privateVars['hideColumns'])) {
						$colSpan = 1;
						if (isSet($this->privateVars['rowColspan'][$k])) {
							$colSpan = $this->privateVars['rowColspan'][$k];
						}
						$resultString .=  $t.$t.$t.$t.'<td class="'.$cellClass.(($cellPosition % 2 == 1) ? "even" : "odd").'Cell"'.(($colSpan > 1) ? ' colspan="'.$colSpan.'"' : "").'>';

						if (!$handledGroupTag) {
							$groupID = $row["cells"][$this->privateVars['allowSubGroups']];
							$this->privateVars['writingSubgroup'] = $groupID;
							$rowIDs = implode("', '".$tblID, $this->privateVars['subGroups'][$groupID]);
							$imgUrl = ($row['open']) ? $this->privateVars['openImage'] : $this->privateVars['closeImage'];
							$resultString .= '<a href="javascript:'.$tblID.'openGroup(new Array(\''.$tblID.$rowIDs.'\'), \''.$tblID.$expandID.'img\', \''.$this->privateVars['openImage'].'\', \''.$this->privateVars['closeImage'].'\')"><img id="'.$tblID.$expandID.'img" src="'.$imgUrl.'" class="tableOpenImage" border="0" /></a> ';
							$this->privateVars['collapseImages'][] = $expandID;
							$handledGroupTag = true;
						}
						if ($k != $this->privateVars['checkboxColumn']) {
							$resultString .=  $row['cells'][$k];
						} else {
							$checked = "";
							$boxName = $this->privateVars['header'][$j];
							$value = $row['cells'][$k];
							if (isSet($_POST[$boxName]) && in_Array($value, $_POST[$boxName])) {
								$checked = 'checked="checked" ';
							} else if ((in_Array($value, $checkboxValues)) && (!isSet($_POST[$boxName]))) {
								$checked = 'checked="checked" ';
							}
							$resultString .= '<input type="checkbox" name="'.$boxName.'[]" value="'.$value.'" '.$checked.' onchange="'.$this->privateVars['onChangeScript'].'"/>';
						}
						$resultString .= '</td>'.$n;
						$cellPosition++;
					}
					$k++;
				}
				if ($k < count($row['cells']) - 1) {
					$resultString .=  $t.$t.$t.'</tr>'.$n;
					$resultString .=  $t.$t.$t.'<tr class="'.(($rowNr % 2 == 0) ? "even" : "odd").'Row" '.$rowClick.'>'.$n;
				}
			}
			$resultString .=  $t.$t.$t.'</tr>'.$n;
			return $resultString;
		}

		/**
		* Gets the serialized data of this table
		*@return String, serialized data
		**/
		function getSerializedData() {
			return serialize($this->privateVars);
		}

		/**
		* Sets the data of this data
		*@param String data, serialized data
		**/
		function setSerializedData($data) {
			$this->privateVars = unserialize($data);
		}

		/**
		* Sets the javascript line that must be executed if a checkbox is checked (Table as FormComponent)
		**/
		function setOnChange($script) {
			$this->privateVars['onChangeScript'] = $script;
		}

	}

?>
