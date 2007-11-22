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

	importClass("interface.Table");
	importBean("board.SearchCache");
	require_once($TBBclassDir."tbblib.php");
	
	/*
	require_once($libraryClassDir."Table.class.php");
	require_once($TBBclassDir."SearchCache.bean.php");
	*/

	class SearchResult {

		var $privateVars;

		function SearchResult() {
			$this->privateVars = array();
			$this->privateVars['columnNames'] = array();
			$this->privateVars['columnTypes'] = array();
			$this->privateVars['columnSorting'] = array();

			$this->privateVars['rows'] = array();
			$this->privateVars['searchSubject'] = array("resultaat", "resultaten");

			$this->privateVars['sortCache'] = array();

			$this->privateVars['cacheID'] = false;
		}

		function cacheInDatabase() {
			global $TBBconfiguration;
			global $TBBsession;

			$database = $TBBconfiguration->getDatabase();
			$searchCacheTable = new SearchCacheTable($database);
			if ($this->privateVars['cacheID'] === false) $cacheRow = $searchCacheTable->addRow();
			else $cacheRow = $searchCacheTable->getRowByKey($this->privateVars['cacheID']);

			$cacheRow->setValue("sessionID", $TBBsession->getValue("tbbSessID"));
			$cacheRow->setValue("date", new LibDateTime());
			$cache = array();
			$cache['columnNames'] = $this->privateVars['columnNames'];
			$cache['columnTypes'] = $this->privateVars['columnTypes'];
			$cache['columnSorting'] = $this->privateVars['columnSorting'];
			$cache['rows'] = $this->privateVars['rows'];
			$cache['searchSubject'] = $this->privateVars['searchSubject'];
			$cache['sortCache'] = $this->privateVars['sortCache'];

			$storeCache = serialize($cache);
			$cacheRow->setValue("searchCache", $storeCache);
			$cacheRow->store();
			$this->privateVars['cacheID'] = $cacheRow->getValue("ID");

			// Delete the searchresults of 24 hours ago
			$cleanFilter = new DataFilter();
			$cleanDate = new LibDateTime();
			$cleanDate->sub(ivHour, 4);
			$cleanFilter->addLessThan("date", $cleanDate);
			$searchCacheTable->deleteRows($cleanFilter);
			return $cacheRow->getValue("ID");
		}

		function getCachedResult($cacheID) {
			global $TBBconfiguration;
			global $TBBsession;

			$database = $TBBconfiguration->getDatabase();
			$searchCacheTable = new SearchCacheTable($database);
			$cacheRow = $searchCacheTable->getRowByKey($cacheID);
			if (!is_Object($cacheRow)) return false;
			if ($cacheRow->getValue("sessionID") !== $TBBsession->getValue("tbbSessID")) return false;
			$cache = unserialize($cacheRow->getValue("searchCache"));

			$this->privateVars['columnNames'] = $cache['columnNames'];
			$this->privateVars['columnTypes'] = $cache['columnTypes'];
			$this->privateVars['columnSorting'] = $cache['columnSorting'];
			$this->privateVars['rows'] = $cache['rows'];
			$this->privateVars['searchSubject'] = $cache['searchSubject'];
			$this->privateVars['sortCache'] = $cache['sortCache'];
			$this->privateVars['cacheID'] = $cacheRow->getValue("ID");
			return true;
		}

		function setSearchSubject($oneResult, $multipleResults) {
			$this->privateVars['searchSubject'] = func_get_args();
		}

		function getSearchSubject($oneResult) {
			if ($oneResult) return $this->privateVars['searchSubject'][0];
			return $this->privateVars['searchSubject'][1];
		}

		function defineColumnNames() {
			$this->privateVars['columnNames'] = func_get_args();
			$nrArgs = func_num_args();
			$this->privateVars['columnTypes'] = array_pad(array(), $nrArgs, "text");
		}

		function defineColumnTypes() {
			if (count($this->privateVars['columnNames']) == func_num_args())
				$this->privateVars['columnTypes'] = func_get_args();
		}

		function defineSortColumns() {
			$this->privateVars['columnSorting'] = func_get_args();
		}

		function addResultRow($relevance) {
			if (func_num_args() != ((count($this->privateVars['columnTypes']) * 2) + 1)) return false;
			$row = array();
			for ($i = 0; $i < func_num_args(); $i++) {
				$cellData = func_get_arg($i);
				if ((($i - 1) % 2) == 0) {
					$columnIndex = (($i -1) / 2);
					if ($this->privateVars['columnTypes'][$columnIndex] == "date")
						$cellData = $cellData->getTimeStamp();
					/*
					if ($this->privateVars['columnTypes'][$columnIndex] == "time")
						$cellData = (($cellData->get(LibDateTime::hour()) * 60) + $cellData->get(LibDateTime::minute()));
					*/
				}
				$row['cell'.$i] = $cellData;
			}
			$this->privateVars['rows'][] = $row;
		}

		function getResultCount() {
			return count($this->privateVars['rows']);
		}

		function getSortedResults($sortColumn, $sortMode) {
			if ($sortMode == 'none') {
				$sortCol = 0;
				$sortMode = 'desc';
			} else {
				$sortCol = 1 + ($sortColumn * 2);
			}
			$sortType = SORT_ASC;
			$sortDataType = SORT_REGULAR;
			if ($sortMode == 'desc') $sortType = SORT_DESC;

			if (isSet($this->privateVars['sortCache'][$sortMode.$sortColumn])) {
				return $this->privateVars['sortCace'][$sortMode.$sortColumn];
			}
			if ($sortCol == 0) $sortDataType = SORT_NUMERIC;
			$sortedResult = $this->privateVars['rows'];
			$sortedResult = array_csort($sortedResult, "cell".$sortCol, $sortType, $sortDataType);
			$this->privateVars['sortCace'][$sortMode.$sortColumn] = $sortedResult;

			$this->cacheInDatabase();
			return $sortedResult;
		}

		function getResultTable($urlParams, $startRow, $rowLimit, $sortColumn, $sortMode) {
			global $TBBconfiguration;

			$validSorts = array("asc", "desc", "none");
			if (!in_Array($sortMode, $validSorts)) $sortMode = 'none';

			$resultTable = new Table();
			$headerNames = array();
			$sortable = $this->privateVars['columnSorting'];
			for ($i = 0; $i < count($this->privateVars['columnNames']); $i++) {
				// ToDo: Fix sorting here
				$headerName = $this->privateVars['columnNames'][$i];
				if (in_array($i, $sortable)) {
					$params = "";
					if (count($urlParams) > 0) $params = "&amp;".implode("&amp;", $urlParams);
					if (($i == $sortColumn) && ($sortMode == 'asc')) {
						$headerName = sprintf('<a href="?sortColumn=%s&amp;sortType=desc%s">%s</a> <img src="%s" alt="%s" />',
							$i, $params, $headerName, $TBBconfiguration->imageOnlineDir . "arrow_down.gif", "oplopend"
						);
					} else
					if (($i == $sortColumn) && ($sortMode == 'desc')) {
						$headerName = sprintf('<a href="?sortColumn=%s&amp;sortType=none%s">%s</a> <img src="%s" alt="%s" />',
							$i, $params, $headerName, $TBBconfiguration->imageOnlineDir . "arrow_up.gif", "aflopend"
						);
					} else {
						$headerName = sprintf('<a href="?sortColumn=%s&amp;sortType=asc%s">%s</a>',
							$i, $params, $headerName
						);
					}
				}
				$headerNames[] = $headerName;
			}
			$resultTable->setHeader($headerNames);

			$rowClasses = array();
			for ($i = 0; $i < count($this->privateVars['columnTypes']); $i++)
				$rowClasses[] = "searchResult".$this->privateVars['columnTypes'][$i];
			$resultTable->setRowClasses($rowClasses);

			$sortedRows = $this->getSortedResults($sortColumn, $sortMode);
			$nrResults = $this->getResultCount();
			$ending = $startRow + $rowLimit;
			if ($ending > $nrResults) $ending = $nrResults;
			for ($i = $startRow; $i < $ending; $i++) {
				$row = $sortedRows[$i];
				$tableRow = array();
				for ($j = 0; $j < count($this->privateVars['columnNames']); $j++) {
					$tableRow[] = $row['cell'.(2 + ($j * 2))];
				}
				$resultTable->addRow($tableRow);
			}

			return $resultTable;
		}

	}

?>
