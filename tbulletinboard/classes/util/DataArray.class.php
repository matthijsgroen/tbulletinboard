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

	class DataItem {

		var $privateVars;
		var $object;

		function DataItem() {
			$this->privateVars = array();
		}

		function setProperty($name, $value) {
			$this->privateVars[$name]	= $value;
		}

		function hasProperty($name) {
			return isSet($this->privateVars[$name]);
		}

		function getProperty($name) {
			if (!isSet($this->privateVars[$name])) return false;
			return $this->privateVars[$name];
		}

		function getHashList() { return $this->privateVars; }

		function getDebugInfo() {
			$result = "".count($this->privateVars) . " velden<br />\n";
			foreach($this->privateVars as $key => $value) {
				$result .= $key . " (".getType($value). "): ".$value."<br />\n";
			}
			return $result;
		}

		function mergeDataItem(&$otherItem) {
			foreach($this->otherItem->privateVars as $key => $value) {
				if (!isSet($this->privateVars[$key]))
					$this->privateVars[$key] = $value;
			}
		}

		function getSerializedData() {
			return serialize($this->privateVars);
		}

		function unSerializeData($serializedData) {
			$this->privateVars = unserialize($serializedData);
		}

	}

	class DataArray extends DataItem {

		function DataArray() {
			$this->DataItem();
		}

		function initialize() {
		}

		function getCount() {
			return 0;
		}

		function getItem($index) {
			return false;
		}
	}

	class DataItemStack {

		var $stack;
		var $stackCount;

		function DataItemStack() {
			$this->stack = array();
			$this->stackCount = 0;
		}

		function push(&$dataItem, $index, $optional=array()) {
			$this->stack["item".$this->stackCount] = array("item" => $dataItem, "index" => $index, "optional" => $optional);
			$this->stackCount++;
		}

		function hasItems() {
			return $this->stackCount > 0;
		}

		function pop() {
			if ($this->stackCount < -1) return false;
			$this->stackCount--;
			return $this->stack["item".$this->stackCount];
		}

	}

?>
