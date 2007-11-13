<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
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
