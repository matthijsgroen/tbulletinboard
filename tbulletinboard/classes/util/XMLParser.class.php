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

/**
 * XMLPARSER.CLASS.PHP
 *
 * Made On: 1 januari 2002
 * Made By: Matthijs
 *
 * Changes:
 *
 * Date: 1 januari 2002
 * By: Matthijs
 * Reason:
 *  Skin.class has been separated.. this is the parsing part :-)
 *
 * Date: 11 januari 2002
 * By: Frans
 * Reason: Bugfix in getTagDimensions
 *
 */

/**
 * Class XMLParser
 *
 */
class XMLParser {

	var $privateVars;

	function XMLParser($text) {
		$this->privateVars = array(
			'text' => $text,
			'position' => 0
		);
	}

	/**
	 * Function: getTagAttributes
	 * Input: text: Text to search through, tag: Tag to read the attributes from.
	 * Output: an associative array with key - value pairs
	 * Description: reads the attributes of an tag and returns them as an array
	 *
	 */
	function getTagAttributes($tag) {
		$text = $this->privateVars['text'];
		$tagDimensions = $this->getTagDimensions($tag);
		$result = Array();
		$tagStartPos = $tagDimensions["start"] + strLen($tag) + 1;
		$tagEndPos = $tagDimensions["end"]-1;

		$attributes = subStr($text, $tagStartPos, $tagEndPos - $tagStartPos);


		$attributes = eregi_Replace("[[:space:]]([^=]*)=\"([^\"]*)\"", "<key>\\1</key><value>\\2</value>", $attributes);
		$attXML = new XMLParser($attributes);
		while($attXML->containsTag("key")) {
			$key = $attXML->getTagContent("key");
			$value = $attXML->getTagContent("value");
			$result[$key->getText()] = $value->getText();
		}
		return $result;
	}

	/**
	 * Function: replaceTagFromText
	 * Input: text: Text to search through, replacement: Text to place over tag, tag: Tag to replace
	 * Output: the new text with the tag replaced
	 * Description: Replaces the first tag by the replacement text and returnes the new text.
	 *
	 */
	function replaceTag($tag, $replacement) {
		$text = $this->privateVars['text'];
		$tagDimensions = $this->getTagDimensions($tag);
		$result = subStr($text, 0, $tagDimensions["start"]) . $replacement . subStr($text, $tagDimensions["end"]);
		$this->privateVars['text'] = $result;
	}

	/**
	 * Function: getTagContentFromText
	 * Input: text: Text to search through, tag: Tag to find
	 * Output: content of the first tag found
	 * Description: finds the first tag and returns the contents
	 *
	 */
	function getTagContent($tag) {
		$text = $this->privateVars['text'];
		$tagDimensions = $this->getTagDimensions($tag);
		$result = subStr($text, $tagDimensions["contentStart"], $tagDimensions["contentEnd"]-$tagDimensions["contentStart"]);
		$this->privateVars['position'] = $tagDimensions["end"];
		return new XMLParser($result);
	}

	/**
	 * Function: textContainsTag
	 * Input: text: Text to search through, tag: Tag to find
	 * Output: true, false
	 * Description: returns true if tag was found, false otherwise
	 *
	 */
	function containsTag($tag) {
		$text = $this->privateVars['text'];
		$tagDimensions = $this->getTagDimensions($tag);
		if ($tagDimensions["start"] == -1) return false; else return true;
	}

	/**
	 * Function: getTagDimensions
	 * Input: $text, $tag
	 * Output: associative array with the fields "start", "end", "contentStart" and "contentEnd"
	 * Description: returns an array with the outer and inner boundaries
	 *
	 */
	function getTagDimensions($tag) {
		$text = $this->privateVars['text'];

		$tagStartPos = strPos($text, "<".$tag." ", $this->privateVars['position']);
		if ($tagStartPos === false) $tagStartPos = strPos($text, "<".$tag.">", $this->privateVars['position']);
		if ($tagStartPos === false) $tagStartPos = strPos($text, "<".$tag."/>", $this->privateVars['position']);
		if ($tagStartPos === false) return Array("start" => -1, "end" => -1, "contentStart" => -1, "contentEnd" => -1);
		$realTagStartPos = $tagStartPos;
		$tagStartPos = strPos($text, ">", $realTagStartPos) + 1;

		if (strPos($text, "/>", $realTagStartPos) == $tagStartPos-2)
			return Array("start" => $realTagStartPos, "end" => $tagStartPos, "contentStart" => -1, "contentEnd" => -1);
		$tagEndPos = strPos($text, "</".$tag.">", $realTagStartPos);	//bugfix hier: realTagStartPos als offset erbij gezet, want anders retourneerde die een positie van de eind-tag die voor de begin-tag ligt . . .
		if (($tagStartPos > strLen($tag)) && ($tagEndPos > $tagStartPos))
			return Array("start" => $realTagStartPos, "end" => $tagEndPos+strLen($tag)+3, "contentStart" => $tagStartPos, "contentEnd" => $tagEndPos);
		return Array("start" => -1, "end" => -1, "contentStart" => -1, "contentEnd" => -1);
	}

	function getText() {
		return $this->privateVars['text'];
	}

}
?>
