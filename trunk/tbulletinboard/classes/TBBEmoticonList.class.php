<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
	 */
	global $ivLibDir;
	require_once($ivLibDir."EmoticonList.class.php");
	require_once($TBBclassDir."Emoticon.bean.php");

	class TBBemoticonList extends EmoticonList {

		var $privateVars;

		function TBBemoticonList() {
			$this->EmoticonList();
			$this->privateVars['emoticonsRead'] = false;
			$this->readEmoticonsInfo();
		}

		function readEmoticonsInfo($force = false) {
			if (($this->privateVars['emoticonsRead']) && (!$force)) {
				return $this->privateVars['emoticons'];
			}
			global $TBBconfiguration;
			$this->clearEmoticons();
			$database = $TBBconfiguration->getDatabase();
			$result = array();
			$emoticonTable = new EmoticonTable($database);

			$sorting = new ColumnSorting();
			$sorting->addColumnSort("order", true);
			
			$filter = new DataFilter();
			
			$emoticonTable->selectRows($filter, $sorting);
			/*
			$selectQuery = sprintf(
				"SELECT * FROM %semoticons",
				$TBBconfiguration->tablePrefix
			);
			$selectResult = $database->executeQuery($selectQuery);
			*/

			$parseOrder = array();
			while ($emoticonRow = $emoticonTable->getRow()) {
				//$emoticon = array();
				$id = $emoticonRow->getValue("ID");
				$name = $emoticonRow->getValue("name");
				$imgUrl = $TBBconfiguration->uploadOnlineDir.'emoticons/'.$emoticonRow->getValue("imgUrl");
				$codes = explode(" ", $emoticonRow->getValue("code"));
				$filename = $emoticonRow->getValue("imgUrl");
				$this->addEmoticon($name, $id, $imgUrl, $codes, $filename);
			}
			$this->privateVars['emoticonsRead'] = true;
			return false;
		}

		function addEmoticonToDB($name, $imgUrl, $code) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$emoticonTable = new EmoticonTable($database);
			$newEmoticon = $emoticonTable->addRow();
			$newEmoticon->setValue("name", $name);
			$newEmoticon->setValue("imgUrl", $imgUrl);
			$newEmoticon->setValue("code", $code);
			$newEmoticon->store();

			/*
			$insertQuery = sprintf(
				"INSERT INTO %semoticons(`name`, `imgUrl`, `code`) VALUES('%s', '%s', '%s')",
				$TBBconfiguration->tablePrefix,
				addSlashes($name),
				addSlashes($imgUrl),
				addSlashes($code)
			);
			$insertResult = $database->executeQuery($insertQuery);
			*/
			return $newEmoticon->getValue("ID");
		}

		function updateEmoticonInDB($id, $name, $imgUrl, $code) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$emoticonTable = new EmoticonTable($database);
			$newEmoticon = $emoticonTable->getRowByKey($id);
			$newEmoticon->setValue("name", $name);
			$newEmoticon->setValue("imgUrl", $imgUrl);
			$newEmoticon->setValue("code", $code);
			$newEmoticon->store();
			/*
			$insertQuery = sprintf(
				"REPLACE INTO %semoticons(`ID`, `name`, `imgUrl`, `code`) VALUES('%s', '%s', '%s', '%s')",
				$TBBconfiguration->tablePrefix,
				addSlashes($id),
				addSlashes($name),
				addSlashes($imgUrl),
				addSlashes($code)
			);
			$insertResult = $database->executeQuery($insertQuery);
			*/
			return $newEmoticon->getValue("ID");
		}

		function deleteEmoticon($id) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$emoticon = $this->getEmoticon($id);
			if ($emoticon == false) return false;
			unlink($emoticon['imgUrl']);
			$emoticonTable = new EmoticonTable($database);
			$newEmoticon = $emoticonTable->getRowByKey($id);
			$newEmoticon->delete();
			/*
			$deleteQuery = sprintf(
				"DELETE FROM %semoticons WHERE `ID`='%s' LIMIT 1",
				$TBBconfiguration->tablePrefix,
				addSlashes($id)
			);
			$database->executeQuery($deleteQuery);
			*/
			$this->removeEmoticon($id);
			return true;
		}

	}

	$GLOBALS['TBBemoticonList'] = new TBBemoticonList();

?>
