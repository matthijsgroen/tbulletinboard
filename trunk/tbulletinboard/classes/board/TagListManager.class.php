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

	importBean("board.TextParsing");
	importClass("util.TextTagList");
	/*
	require_once($TBBclassDir."TextParsing.bean.php");
	require_once($libraryClassDir."TextTagList.class.php");
	*/

	class TBBTag extends TextTag {

		var $privateVars;

		function TBBTag($starttag, $acceptParameters, $acceptAll, $endtag, $htmlcode, $endTagRequired, $inTags, $subTags) {
			$this->TextTag($starttag, $acceptParameters, $acceptAll, $endtag, $htmlcode, $endTagRequired, $inTags, $subTags);
		}

		function setSystem($system) { 
			$this->privateVars['system'] = $system;
		}

		function isSystem() {
			return $this->privateVars['system'];
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

			while ($tagInfo = $textParsing->getRow()) {
				$origin = $tagInfo->getValue("origin");
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
					case "parameter": $wordBreaks = TextTag::breakParameter(); break;
				}
				$tag = $this->addTBBTag($origin, $startTag, $acceptParameters, $acceptAll, $endtag, $htmlcode, 
					$endTagRequired, $inTags, $subTags, $active, $description, $example, $id, $wordBreaks);
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

		private function addTBBTag($origin, $starttag, $acceptParameters, $acceptAll,
				$endtag, $htmlcode, $endTagRequired, $inTags, $subTags, $active, $description, $example, $id, $wordBreaks) {
			if ($origin == "system") {
				importClass("board.plugin.ModulePlugin");
				global $TBBModuleManager;
				$plugin = $TBBModuleManager->getPluginByID($htmlcode);
				if (is_Object($plugin)) {
					$tbbTag = $plugin->getTag($starttag, $acceptParameters, $acceptAll, $endtag, $htmlcode, $endTagRequired, $inTags, $subTags);
					$tbbTag->setID($id);
					$tbbTag->setDescription($description);
					$tbbTag->setExample($example);
					$tbbTag->setActive($active);
					$tbbTag->setSystem(true);
					$tbbTag->setWordBreaks($wordBreaks);
					$this->privateVars['tbbTags'][] = $tbbTag;
					return $tbbTag;
				}
				return false;
			} else {			
				$tbbTag = new TBBTag($starttag, $acceptParameters, $acceptAll, $endtag, $htmlcode, $endTagRequired, $inTags, $subTags);
				$tbbTag->setID($id);
				$tbbTag->setDescription($description);
				$tbbTag->setExample($example);
				$tbbTag->setActive($active);
				$tbbTag->setSystem(false);
				$tbbTag->setWordBreaks($wordBreaks);
				$this->privateVars['tbbTags'][] = $tbbTag;
				return $tbbTag;
			}
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

		}

		function deleteTag($tagID) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$tagList = $this->getTBBtags(true);
			$index = $tagList->indexOf($tagID);

			$textParsingTable = new TextParsingTable($database);
			$textParsingTable->deleteRowByKey($tagID);
			//$tag->deleteRow();

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
