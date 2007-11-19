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

	class DataOrderHelper {

		var $table;
		var $displayColumn;
		var $orderColumn;

		function DataOrderHelper(&$table, $displayColumn, $orderColumn) {
			$this->table =& $table;
			$this->displayColumn = $displayColumn;
			$this->orderColumn = $orderColumn;
		}

		function fillOrderSelecter(&$form, &$selectComponent, $excludeID = -1, $selectionFilter = false, $useIDs=true) {
			if ($selectionFilter === false) $selectionFilter = new DataFilter();

			$sorting = new ColumnSorting();
			$sorting->addColumnSort($this->orderColumn, true);
			$rowFilter = new DataFilter();
			if ($selectionFilter->getFilterCount() > 0)	$rowFilter->addDataFilter($selectionFilter);

			$selectComponent->addComponent(new FormOption("Bovenaan", "top"));
			$under = "top";
			$this->table->selectRows($rowFilter, $sorting);
			$keyColumn = $this->table->getPrimaryKey();
			if ($this->table->getSelectedRowCount() > 0) $selectComponent->addComponent(new FormOption("Onderaan", "bottom"));
			while ($row =& $this->table->getRow()) {
				if ($row->getValue($keyColumn) == $excludeID) {
					$under = $row->getValue($keyColumn);
					if ($useIDs == false) $under = $row->getValue($this->orderColumn);
				} else {
					$selectID = $row->getValue($keyColumn);
					if ($useIDs == false) $selectID = $row->getValue($this->orderColumn) + 1;
					$selectComponent->addComponent(new FormOption("Onder ".$row->getValue($this->displayColumn), $selectID, false));
				}
			}
			$form->addComponent($selectComponent);
			$form->setValue($selectComponent->identifier, $under);
		}

		function setNewOrder(&$newRecord, $orderValue, $selectionFilter = false, $needFix = true) {
			if ($selectionFilter === false) $selectionFilter = new DataFilter();

			if ($orderValue == "top") {
				$newRecord->setValue($this->orderColumn, 1);

				$rowFilter = new DataFilter();
				if ($selectionFilter->getFilterCount() > 0)
					$rowFilter->addDataFilter($selectionFilter);

				$mutations = new DataMutation();
				$mutations->addToColumn($this->orderColumn, 1);
				$this->table->executeDataMutations($mutations, $rowFilter);
				return;
			}
			if ($orderValue == "bottom") {
				$max = $this->table->countRows($selectionFilter);
				if ($newRecord->getValue($this->orderColumn) == $max) return;

				$sorting = new ColumnSorting();
				$sorting->addColumnSort($this->orderColumn, false);

				$rowFilter = new DataFilter();
				if ($selectionFilter->getFilterCount() > 0)
					$rowFilter->addDataFilter($selectionFilter);
				$keyColumn = $this->table->getPrimaryKey();

				if (($needFix) && ($newRecord->isInDatabase())) {
					$rowFilter->addEqualsNot($keyColumn, $newRecord->getValue($keyColumn));

					$fixOrderFilter = new DataFilter();
					if ($selectionFilter->getFilterCount() > 0)
						$fixOrderFilter->addDataFilter($selectionFilter);

					$mutations = new DataMutation();
					$mutations->subtractFromColumn($this->orderColumn, 1);
					$this->table->executeDataMutations($mutations, $fixOrderFilter);
				}

				$this->table->selectRows($rowFilter, $sorting);
				if ($lastRecord =& $this->table->getRow()) {
					$newRecord->setValue($this->orderColumn, $lastRecord->getValue($this->orderColumn) + 1);
				} else $newRecord->setValue($this->orderColumn, 1);
				return;
			}
			if (is_numeric($orderValue)) {
				//print "orderValue : ".$orderValue."<br/>\n";

				$rowFilter = new DataFilter();
				$rowFilter->addGreaterThanOrEquals($this->orderColumn, $orderValue);
				if ($selectionFilter->getFilterCount() > 0)
					$rowFilter->addDataFilter($selectionFilter);

				$mutations = new DataMutation();
				$mutations->addToColumn($this->orderColumn, 1);
				$this->table->executeDataMutations($mutations, $rowFilter);
				//$this->table->selectRows($rowFilter,new ColumnSorting());
				//print $this->table->getSelectionQuery();
				//while($row =& $this->table->getRow()) print $row->getValue('standtype');
				$newRecord->setValue($this->orderColumn, $orderValue);
				return;
			}
		}

		function removeOrder(&$removedRecord, $selectionFilter = false) {
			if ($selectionFilter === false) $selectionFilter = new DataFilter();

			$rowFilter = new DataFilter();
			$rowFilter->addGreaterThan($this->orderColumn, $removedRecord->getValue($this->orderColumn));
			if ($selectionFilter->getFilterCount() > 0)
				$rowFilter->addDataFilter($selectionFilter);

			$mutations = new DataMutation();
			$mutations->subtractFromColumn($this->orderColumn, 1);
			$this->table->executeDataMutations($mutations, $rowFilter);
		}

		function removeOrderFilter(&$removedRecord, $filterColumn) {
			$rowFilter = new DataFilter();
			$rowFilter->addGreaterThan($this->orderColumn, $removedRecord->getValue($this->orderColumn));

			if ($removedRecord->isNull($filterColumn)) {
				$rowFilter->addNull($filterColumn);
			} else {
				$filterValue = $removedRecord->getValue($filterColumn);
				$rowFilter->addEquals($filterColumn, $filterValue);
			}

			$mutations = new DataMutation();
			$mutations->subtractFromColumn($this->orderColumn, 1);
			$this->table->executeDataMutations($mutations, $rowFilter);
		}

		function editOrder(&$editRecord, $orderValue, $selectionFilter = false) {
			if ($editRecord->getValue($this->orderColumn) == $orderValue) return;

			$this->removeOrder($editRecord, $selectionFilter);
			$this->setNewOrder($editRecord, $orderValue, $selectionFilter, false);
			$editRecord->store();
		}

		function moveRecord($recordID, $relativePosition, $filterColumn) {
			//print "a";

			$record = $this->table->getRowByKey($recordID);
			if ($record === false) return;

			//print "b";

			$filter = new DataFilter();
			if ($filterColumn != "") {
				if($record->isNull($filterColumn)) {
					$filter->addNull($filterColumn);
				} else $filter->addEquals($filterColumn, $record->getValue($filterColumn));
			}

			//print "c";

			if (is_numeric($relativePosition)) {
				//print "d";
				$newOrder = $record->getValue($this->orderColumn) + $relativePosition;
				//print "newOrder :".$newOrder;
				if ($newOrder < 1) {
					$newOrder = "top";
					if ($record->getValue($this->orderColumn) == 1) return;
				}
				if ($relativePosition > 0) {
					$max = $this->table->countRows($filter);
					if ($newOrder > $max) $newOrder = "bottom";
				}
			} else if (is_string($relativePosition)) {
				if (($record->getValue($this->orderColumn) == 1) && ($relativePosition == "top")) return;
				$max = $this->table->countRows($filter);
				if (($record->getValue($this->orderColumn) == $max) && ($relativePosition == "bottom")) return;

				$newOrder = $relativePosition;
			}

			$this->editOrder($record, $newOrder, $filter);
		}

		function fixOrder($filterColumn, $filterValue, $startOffset = 1) {
			$filter = new DataFilter();
			if ($filterColumn != "") $filter->addEquals($filterColumn, $filterValue);

			$sorting = new ColumnSorting();
			$sorting->addColumnSort($this->orderColumn, true);

			$this->table->selectRows($filter, $sorting);
			$order = $startOffset;
			while ($row =& $this->table->getRow()) {
				$row->setValue($this->orderColumn, $order);
				$row->store();
				$order++;
			}
		}

	}

?>
