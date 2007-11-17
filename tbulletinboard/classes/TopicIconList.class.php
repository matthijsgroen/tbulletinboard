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

	require_once($TBBclassDir."TopicIcon.bean.php");

	class TopicIconList {

		var $privateVars;

		function TopicIconList() {
			$this->privateVars = array();
			$this->privateVars['readIcons'] = false;
			$this->privateVars['icons'] = array();
			$this->privateVars['cacheID'] = array();
		}

		function addIcon($name, $fileName) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$topicIconTable = new TopicIconTable($database);
			$newIcon = $topicIconTable->addRow();
			$newIcon->setValue("name", $name);
			$newIcon->setValue("imgUrl", $fileName);
			$newIcon->store();
		}

		function getIconsInfo() {
			if ($this->privateVars['readIcons']) {
				return $this->privateVars['icons'];
			}
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$result = array();

			$topicIconTable = new TopicIconTable($database);
			$topicIconTable->selectAll();

			while ($iconData = $topicIconTable->getRow()) {
				$icon = array();
				$icon['name'] = $iconData->getValue('name');
				$icon['imgUrl'] = $TBBconfiguration->uploadOnlineDir . 'topicicons/' . $iconData->getValue('imgUrl');
				$icon['ID'] = $iconData->getValue('ID');
				$result[] = $icon;
				$this->privateVars['cacheID'][$iconData->getValue('ID')] = $icon;
			}
			$this->privateVars['readIcons'] = true;
			$this->privateVars['icons'] = $result;
			return $result;
		}

		function getIconInfo($id) {
			$this->getIconsInfo();
			if (isSet($this->privateVars['cacheID'][$id])) {
				return $this->privateVars['cacheID'][$id];
			}
			return false;
		}
	}
	$GLOBALS['TBBtopicIconList'] = new TopicIconList();

?>
