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
	importBean("updater.UpdateHistory");


	class ModuleUpdater {

		private $moduleName;
		private $patchFolder;
		private $patchList = array();
		private $patchesListed = false;

		private $newPatchList = array();
		private $newPatchesListed = false;
		private $database = null;
		
		private $lastPatchExecuted = null;
		private $lastPatchDate = null;
		private $errorMessage = "";

		function ModuleUpdater($moduleName, $patchFolder, &$database) {
			$this->moduleName = $moduleName;
			$this->patchFolder = $patchFolder;
			$this->database = $database;
		}
	
		function getTotalPatchCount() {
			$this->indexPatches();
			return count($this->patchList);
		}
		
		private function indexPatches() {
			if ($this->patchesListed) return;
			if (is_dir($this->patchFolder)) {
				if ($dh = opendir($this->patchFolder)) {
					while (($file = readdir($dh)) !== false) {
						if(fnmatch("ptch*.php", $file)) {
							$time = substr($file, 4, 10);
							$this->patchList[] = array("time" => $time, "name" => $file, "path" => $this->patchFolder . $file, "folder" => $this->patchFolder);
						}
					}
					closedir($dh);
				}
			}
			// Obtain a list of columns
			foreach ($this->patchList as $key => $row) {
			   $patchtime[$key]  = $row['time'];
			   $patchname[$key] = $row['name'];
			}

			// Sort the data with volume descending, edition ascending
			// Add $patchList as the last parameter, to sort by the common key
			array_multisort($patchtime, SORT_ASC, $patchname, SORT_ASC, $this->patchList);
			$this->patchesListed = true;
		}
		
		function getNewPatchCount() {
			$this->createNewPatchList();
			return count($this->newPatchList);
		}
		
		private function createNewPatchList() {
			if ($this->newPatchesListed) return;
			$this->indexPatches();
			$historyTable = new UpdateHistoryTable($this->database);
			$lastUpdate = null;
			if ($historyTable->tableExists()) {
				$filter = new DataFilter();
				$filter->addEquals("module", $this->moduleName);
				$filter->setLimit(1);
				$sorting = new ColumnSorting();
				$sorting->addColumnSort("patchDate", false);
				$historyTable->selectRows($filter, $sorting);
				if ($lastRecord = $historyTable->getRow()) {
					$lastUpdate = $lastRecord->getValue("patchDate");
					$this->lastPatchDate = $lastRecord->getValue("patchDate");
					$this->lastPatchExecuted = $lastRecord->getValue("executeDate");
				}				
			}
			$patchTime = new LibDateTime();
			foreach($this->patchList as $patchInfo) {
				if ($lastUpdate === null) {
					$this->newPatchList[] = $patchInfo;
				}	else {
					$patchTime->initializeByLinuxTimestamp($patchInfo['time']);
					if ($patchTime->after($lastUpdate)) {
						$this->newPatchList[] = $patchInfo;
					}
				}
			}
			$this->newPatchesListed = true;			
		}

		function executePatches() {
			$this->createNewPatchList();
			foreach($this->newPatchList as $patchInfo) {
				$this->executePatch($patchInfo);
			}		
		}
		
		private function executePatch($patchInfo) {
			ob_start();
			include($patchInfo['path']);
			$queries = ob_get_contents()."\n";
			ob_end_clean();

			$correctMeta = true;
			if (!isSet($patchName)) $correctMeta = false;
			if (!isSet($patchFunc)) $correctMeta = false;
			if (!isSet($patchAuthor)) $correctMeta = false;

			if (!$correctMeta) {
				$this->errorMessage = "no valid patch metadata";
				return false;
			}
			if (strLen(trim($queries)) > 0) {
				$lines = explode("\n", $queries);
				$queryList = array();
				$inString = false;
				$lastQuery = "";
				foreach($lines as $line) {
					$trimLine = trim($line);
					$quotCount = substr_count($trimLine, "'");
					$remQuotCount = substr_count($trimLine, "\'");
					if ((($quotCount - $remQuotCount) % 2) == 1) {
						$inString = !$inString;
					}
					if (subStr($trimLine, 0, 1) != '#') {
						$lastQuery .= $line . "\n";
						if ((subStr($trimLine, -1) == ';') && (!$inString)) {
							$queryList[] = ' '.substr($lastQuery,0,-1);
							$lastQuery = "";
						}
					}
				}

				printf("Q(%s) \n", count($queryList));
				//print htmlSpecialChars($queryList[1]);
				//return false;
				$i = 1;
				foreach($queryList as $query) {
					$result = $this->database->executeQuery($query);
					if (!$result) {
						$this->errorMessage('Invalid query: '.$query.'');
						return false;
					}
					$i++;
				}
			}
			
			$patchTime = new LibDateTime();
			$patchTime->initializeByLinuxTimestamp($patchInfo['time']);

			$historyTable = new UpdateHistoryTable($this->database);
			if ($historyTable->tableExists()) {
				$newLog = $historyTable->addRow();
				$newLog->setValue("module", $this->moduleName);
				$newLog->setValue("name", $patchName);
				$newLog->setValue("author", $patchAuthor);
				$newLog->setValue("patchDate", $patchTime);
				$newLog->setValue("executeDate", new LibDateTime());
				$newLog->store();
			}
			return true;
		}
		
		function getErrorMessage() {
			return $this->errorMessage;
		}
	}

?>
