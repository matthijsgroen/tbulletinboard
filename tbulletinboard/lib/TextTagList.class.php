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

	class TextTag {

		var $privateVars;

		function TextTag($starttag, $acceptParameters, $acceptAll, $endtag, $htmlcode, $endTagRequired, $inTags, $subTags) {
			$this->privateVars = array(
				'startTag' => $starttag,
				'endTag' => $endtag,
				'htmlReplace' => $htmlcode,
				'endTagRequired' => $endTagRequired,
				'inTags' => $inTags,
				'subTags' => $subTags,
				'acceptParameters' => $acceptParameters,
				'acceptAll' => $acceptAll,
				'breakWords' => TextTag::breakAll(),
				'id' => uniqId('tag'),
				'example' => '',
				'description' => ''
			);
		}

		static function breakAll() { return 0; }
		static function breakText() { return 1; }
		static function breakParameter() { return 2; }
		static function breakNone() { return 3; }

		function setID($id) {
			$this->privateVars['id'] = $id;
		}

		function getID() {
			return $this->privateVars['id'];
		}

		function getWordBreaks() {
			return $this->privateVars['breakWords'];
		}

		function setWordBreaks($wordbreaks) {
			$this->privateVars['breakWords'] = $wordbreaks;
		}

		function getExample() {
			return $this->privateVars['example'];
		}

		function getDescription() {
			return $this->privateVars['description'];
		}

		function setExample($example) {
			$this->privateVars['example'] = $example;
		}

		function setDescription($description) {
			$this->privateVars['description'] = $description;
		}

		function getName() {
			return $this->privateVars['startTag'];
		}

		function isEndTag($text) {
			$endTags = $this->privateVars['endTag'];
			return in_array($text, $endTags);
		}

		function getHtmlReplace() {
			return $this->privateVars['htmlReplace'];
		}

		function endTagRequired() {
			return $this->privateVars['endTagRequired'];
		}

		function mayInTags($parentTags) {
			if (in_array('{all}', $this->privateVars['inTags'])) return true;
			//for ($i = 0; $i < count($parentTags); $i++) {
			if ((count($parentTags) > 0) && (in_array($parentTags[count($parentTags) -1], $this->privateVars['inTags']))) return true;
			//}
			return false;
		}

		function allowSubTag($subTag) {
			if (in_array('{all}', $this->privateVars['subTags'])) return true;
			if (in_array($subTag, $this->privateVars['subTags'])) return true;
			return false;
		}

		function allowParameter($parameter) {
			if ($this->privateVars['acceptAll']) return true;
			if (in_array($parameter, $this->privateVars['acceptParameters'])) return true;
			return false;
		}

		function allowAllParameters() {
			return ($this->privateVars['acceptAll']);
		}

		function getAcceptedParameters() {
			return $this->privateVars['acceptParameters'];
		}

		function getAcceptedEndTags() {
			return $this->privateVars['endTag'];
		}

		function getAllowedParents() {
			return $this->privateVars['inTags'];
		}

		function getAllowedChilds() {
			return $this->privateVars['subTags'];
		}
	}

	class TextTagList {

		var $privateVars;

		function TextTagList() {
			$this->privateVars['textTags'] = array();
		}

		function addTextTag($textTag) {
			$this->privateVars['textTags'][] = $textTag;
		}

		function getTagCount() {
			return count($this->privateVars['textTags']);
		}

		function getTag($index) {
			return $this->privateVars['textTags'][$index];
		}

		function indexOf($id) {
			for ($i = 0; $i < count($this->privateVars['textTags']); $i++) {
				$tag = $this->privateVars['textTags'][$i];
				if ($tag->getID() == $id) return $i;
			}
			return -1;
		}

		static function p_sortMethod(&$tagA, &$tagB) {
			$result = strCaseCmp($tagA->getName(), $tagB->getName());
			if ($result == 0) {
				$allowNoneA = (!$tagA->allowAllParameters() && (count($tagA->getAcceptedParameters()) == 0));
				$allowNoneB = (!$tagB->allowAllParameters() && (count($tagB->getAcceptedParameters()) == 0));
				if (($allowNoneA) && (!$allowNoneB)) return -1;
				if (($allowNoneB) && (!$allowNoneA)) return 1;
			}
			return $result;
		}

		function sort() {
			uSort($this->privateVars['textTags'], array('TextTagList', 'p_sortMethod'));
		}

	}


?>
