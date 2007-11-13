<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
	 */

	require_once($TBBclassDir."TextParsing.bean.php");
	require_once($ivLibDir."TextTagList.class.php");

	class TBBTag extends TextTag {

		var $privateVars;

		function TBBTag($starttag, $acceptParameters, $acceptAll, $endtag, $htmlcode, $endTagRequired, $inTags, $subTags) {
			$this->TextTag($starttag, $acceptParameters, $acceptAll, $endtag, $htmlcode, $endTagRequired, $inTags, $subTags);
		}

		function setActive($active) {
			$this->privateVars['active'] = $active;
		}

		function isActive() {
			return $this->privateVars['active'];
		}

	}

	class TagListManager {

		var $privateVars;

		function TagListManager() {
			$this->privateVars['tbbTags'] = array();
			$this->privateVars['tagsRead'] = false;
			$this->readTags();
		}

		function readTags() {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			if ($this->privateVars['tagsRead']) return true;
			$textParsing = new TextParsingTable($database);
			$textParsing->selectAll();
			/*
			$selectQuery = sprintf(
				"SELECT * FROM %stextparsing",
				$TBBconfiguration->tablePrefix
			);
			$selectResult = $database->executeQuery($selectQuery);
			*/
			while ($tagInfo = $textParsing->getRow()) {
				$startTag = $tagInfo->getValue("startName");
				$acceptParameters = explode(' ', trim($tagInfo->getValue("acceptedParameters")));
				if ((count($acceptParameters) == 1) && ($acceptParameters[0] == "")) $acceptParameters = array();
				$acceptAll = $tagInfo->getValue("acceptAll");
				$endtag = explode(' ', $tagInfo->getValue("endTags"));
				$htmlcode = $tagInfo->getValue("htmlReplace");
				$endTagRequired = $tagInfo->getValue("endTagRequired");
				$inTags = explode(',', $tagInfo->getValue("allowParents"));
				$subTags = explode(',', $tagInfo->getValue("allowChilds"));
				$active = $tagInfo->getValue("active");
				$example = $tagInfo->getValue("example");
				$description = $tagInfo->getValue("description");
				$id = $tagInfo->getValue("ID");
				$wordBreaks = TextTag::breakNone();
				switch ($tagInfo->getValue("wordBreaks")) {
					case "all": $wordBreaks = TextTag::breakAll(); break;
					case "text": $wordBreaks = TextTag::breakText(); break;
					case "parameter": $wordBreaks = TextTag::breakParam(); break;
				}
				$tag = $this->addTBBTag($startTag, $acceptParameters, $acceptAll, $endtag, $htmlcode, $endTagRequired, $inTags, $subTags, $active, $description, $example, $id, $wordBreaks);
			}
			$this->privateVars['tagsRead'] = true;
			return true;
		}

		function getTBBtags($ignoreActive = false) {
			$this->readTags();
			$tagList = new TextTagList();
			for ($i = 0; $i < count($this->privateVars['tbbTags']); $i++) {
				$tag = $this->privateVars['tbbTags'][$i];
				if ($tag->isActive()) {
					$tagList->addTextTag($tag);
				} else {
					if ($ignoreActive) {
						$tagList->addTextTag($tag);
					}
				}
			}
			$tagList->sort();
			return $tagList;
		}

		function addTBBTag($starttag, $acceptParameters, $acceptAll,
				$endtag, $htmlcode, $endTagRequired, $inTags, $subTags, $active, $description, $example, $id, $wordBreaks) {
			$tbbTag = new TBBTag($starttag, $acceptParameters, $acceptAll, $endtag, $htmlcode, $endTagRequired, $inTags, $subTags);
			$tbbTag->setID($id);
			$tbbTag->setDescription($description);
			$tbbTag->setExample($example);
			$tbbTag->setActive($active);
			$tbbTag->setWordBreaks($wordBreaks);
			$this->privateVars['tbbTags'][] = $tbbTag;
			return $tbbTag;
		}

		function addTBBTagToDB($starttag, $acceptParameters, $acceptAll, $endtag, $htmlcode, $endTagRequired, $inTags, $subTags, $description, $example, $wordBreak) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$wb = "none";
			if (TextTag::breakAll() == $wordBreak) $wb = "all";
			if (TextTag::breakText() == $wordBreak) $wb = "text";
			if (TextTag::breakParameter() == $wordBreak) $wb = "parameter";

			$textParsingTable = new TextParsingTable($database);
			$newTag = $textParsingTable->addRow();
			$newTag->setValue("startName", $starttag);
			$newTag->setValue("acceptAll", $acceptAll);
			$newTag->setValue("acceptedParameters", $acceptParameters);
			$newTag->setValue("endTags", $endtag);
			$newTag->setValue("endTagRequired", $endTagRequired);
			$newTag->setValue("htmlReplace", $htmlcode);
			$newTag->setValue("allowParents", $inTags);
			$newTag->setValue("allowChilds", $subTags);
			$newTag->setValue("description", $description);
			$newTag->setValue("example", $example);
			$newTag->setValue("wordBreaks", $wb);
			$newTag->store();

			/*
			$insertQuery = sprintf(
				"INSERT INTO %stextparsing (`startName`, `acceptAll`, `acceptedParameters`, `endTags`, `endTagRequired`, `htmlReplace`, `allowParents`, `allowChilds`, `description`, `example`, `wordBreaks`) ".
				"VALUES('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
				$TBBconfiguration->tablePrefix,
				addSlashes($starttag),
				($acceptAll) ? "yes" : "no",
				addSlashes($acceptParameters),
				addSlashes($endtag),
				($endTagRequired) ? "yes" : "no",
				addSlashes($htmlcode),
				addSlashes($inTags),
				addSlashes($subTags),
				addSlashes($description),
				addSlashes($example),
				addSlashes($wb)
			);
			$database->executeQuery($insertQuery);
			*/
		}

		function editTBBtagInDB($id, $starttag, $acceptParameters, $acceptAll, $endtag, $htmlcode, $endTagRequired, $inTags, $subTags, $description, $example, $wordBreak) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$wb = "none";
			if (TextTag::breakAll() == $wordBreak) $wb = "all";
			if (TextTag::breakText() == $wordBreak) $wb = "text";
			if (TextTag::breakParameter() == $wordBreak) $wb = "parameter";

			$textParsingTable = new TextParsingTable($database);
			$newTag = $textParsingTable->getRowByKey($id);
			$newTag->setValue("startName", $starttag);
			$newTag->setValue("acceptAll", $acceptAll);
			$newTag->setValue("acceptedParameters", $acceptParameters);
			$newTag->setValue("endTags", $endtag);
			$newTag->setValue("endTagRequired", $endTagRequired);
			$newTag->setValue("htmlReplace", $htmlcode);
			$newTag->setValue("allowParents", $inTags);
			$newTag->setValue("allowChilds", $subTags);
			$newTag->setValue("description", $description);
			$newTag->setValue("example", $example);
			$newTag->setValue("wordBreaks", $wb);
			$newTag->store();

			/*
			$insertQuery = sprintf(
				"REPLACE INTO %stextparsing (`ID`, `startName`, `acceptAll`, `acceptedParameters`, `endTags`, `endTagRequired`, `htmlReplace`, `allowParents`, `allowChilds`, `description`, `example`, `wordBreaks`) ".
				"VALUES('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
				$TBBconfiguration->tablePrefix,
				addSlashes($id),
				addSlashes($starttag),
				($acceptAll) ? "yes" : "no",
				addSlashes($acceptParameters),
				addSlashes($endtag),
				($endTagRequired) ? "yes" : "no",
				addSlashes($htmlcode),
				addSlashes($inTags),
				addSlashes($subTags),
				addSlashes($description),
				addSlashes($example),
				addSlashes($wb)
			);
			$database->executeQuery($insertQuery);
			*/
		}

		function deleteTag($tagID) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$tagList = $this->getTBBtags(true);
			$index = $tagList->indexOf($tagID);

			$textParsingTable = new TextParsingTable($database);
			$textParsingTable->deleteRowByKey($tagID);
			//$tag->deleteRow();

			/*
			$deleteQuery = sprintf(
				"DELETE FROM %stextparsing WHERE `ID`='%s' LIMIT 1",
				$TBBconfiguration->tablePrefix,
				addSlashes($tagID)
				);
			$deleteResult = $database->executeQuery($deleteQuery);
			$result = ($deleteResult->getNumAffectedRows() == 1);
			$deleteQuery = sprintf(
				"DELETE FROM %sboardtags WHERE `tagID`='%s'",
				$TBBconfiguration->tablePrefix,
				addSlashes($tagID)
				);
			$database->executeQuery($deleteQuery);
			*/
			unSet($this->privateVars['tbbTags'][$index]);
			//return $result;
		}

		function setTagActive($tagID, $active) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$textParsingTable = new TextParsingTable($database);
			$tag = $textParsingTable->getRowByKey($tagID);
			$tag->setValue("active", $active);
			$tag->store();
			/*
			$updateQuery = sprintf(
				"UPDATE %stextparsing SET `active`='%s' WHERE `ID`='%s'",
				$TBBconfiguration->tablePrefix,
				($active) ? "yes" : "no",
				addSlashes($tagID)
				);
			$updateResult = $database->executeQuery($updateQuery);
			*/
			for ($i = 0; $i < count($this->privateVars['tbbTags']); $i++) {
				$tag = $this->privateVars['tbbTags'][$i];
				if ($tag->getID() == $tagID) {
					$tag->setActive($active);
				}
			}
			return true;;
		}

		/**
		 * Returns a list of tags with the given ID's
		 *@param array $ids an array containing a the id's of the wanted tags
		 *@return TextTagList a list with the selected tags.
		 */
		function getTagList($ids) {
			$tagList = new TextTagList();
			for ($i = 0; $i < count($ids); $i++) {
				for ($j = 0; $j < count($this->privateVars["tbbTags"]); $j++) {
					$tag = $this->privateVars["tbbTags"][$j];
					if ($tag->getID() == $ids[$i]) {
						$tagList->addTextTag($tag);
					}
				}
			}
			$tagList->sort();
			return $tagList;
		}
	}

	$GLOBALS['TBBtagListManager'] = new TagListManager();

?>
