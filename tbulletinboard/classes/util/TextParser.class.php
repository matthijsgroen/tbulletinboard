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
	 * A node in the text parsetree.
	 *
	 * This node will automatically parse any text that will be set into subnodes. This Node is also
	 * the supernode for text and tag nodes. This class will be used in the TextParser class. There is
	 * no need to create an instance.
	 */
	class ParseNode {

		/**
		 * An array containing all subnodes
		 *@var array $content
		 */
		var $content;

		/**
		 * an reference to the textparser for configuration settings.
		 *@var TextParser $textParser
		 */
		var $textParser;

		/**
		 * an array containing the names of the parentnodes in sequential order. eg. the root node first
		 *@var array $parentNodes
		 */
		var $parentNodes;

		/**
		 * Creates a parsenode. This will be done automatically when the TextParser parses the text.
		 *@param TextParser $textParser a reference to the textparser that owns the tree of nodes
		 *@param array $parentNodes an array containing the names of the parentnodes in sequential order. eg. the root node first
		 */
		function ParseNode(&$textParser, $parentNodes) {
			$this->textParser = $textParser;
			$this->content = array();
			$this->parentNodes = $parentNodes;
		}

		/**
		 * Sets the text into this node. The text will automatically be parsed an subnodes will be created.
		 *@param string $text the text to parse
		 */
		function setText($text) {
			$tagStart = $this->textParser->findStartTag($text, 0, false, $this->parentNodes);

			while ($tagStart != false) {
				$tag = $tagStart['tag'];

				$tagEnd = $this->textParser->findEndTag($text, $tagStart['endPos'], $tag);
				if ($tagEnd != false) {
					// put the text before the tag into the content
					$beforeText = subStr($text, 0, $tagStart['startPos']);
					if (strLen($beforeText) > 0) {
						$this->content[] = new TextNode($this->textParser, $this->parentNodes, $beforeText);
					}

					// handle the tag
					$tagText = subStr($text, $tagStart['endPos'], ($tagEnd['startPos'] - $tagStart['endPos']));
					$tagStart2 = $this->textParser->findStartTag($tagText, 0, $tag, $this->parentNodes);
					$tagParentNode = $this->parentNodes;
					$tagParentNode[] = $tagStart['name'];

					if (!$tag->endTagrequired()) {
						while ($tagStart2 != false) {
							$text2 = subStr($tagText, 0, $tagStart2['startPos']);


							$tagNode = new TagNode($this->textParser, $tagParentNode, $tag, $tagStart['parameterValue']);
							$tagNode->setText($text2);

							$this->content[] = $tagNode;

							$tagText = subStr($tagText, $tagStart2['endPos']);
							$tagStart = $tagStart2;
							$tagStart2 = $this->textParser->findStartTag($tagText, 0, $tag, $this->parentNodes);
						}
					} else {
						// test for recursive tags
						/*
							1. check if this text contains another starttag
							//		function findStartTag($text, $start, $searchTag, $parentTags) {
							2. search for another end tag
							// 		function findEndTag($text, $start, $tag) {
						*/
						while ($tagStart2 != false) {
							$insidePos = $tagStart2['endPos'];

							// grab an extra end tag
							$newTagEnd = $this->textParser->findEndTag($text, $tagEnd['endPos'], $tag);
							if ($newTagEnd != false) {
								$tagEnd = $newTagEnd;

								$tagStart2 = $this->textParser->findStartTag($tagText, $insidePos, $tag, $this->parentNodes);
							} else {
								$tagStart2 = false;
							}
						}
						$tagText = subStr($text, $tagStart['endPos'], ($tagEnd['startPos'] - $tagStart['endPos']));
					}
					$restNode = new TagNode($this->textParser, $tagParentNode, $tag, $tagStart['parameterValue']);
					$restNode->setText($tagText);
					$this->content[] = $restNode;

					// Knip de tag van de rest af
					$text = subStr($text, $tagEnd['endPos']);
				} else {
					$afterText = substr($text, 0, $tagStart['endPos']);
					if (strLen($afterText) > 0) {
						$this->content[] = new TextNode($this->textParser, $this->parentNodes, $afterText);
					}
					$text = subStr($text, $tagStart['endPos']);
				}
				$tagStart = $this->textParser->findStartTag($text, 0, false, $this->parentNodes);
			}

			$this->content[] = new TextNode($this->textParser, $this->parentNodes, $text);
		}

		/**
		 * Returns the text from this node.
		 *@param bool $raw if false, the text will be returned parsed, otherwise it will be returned raw
		 */
		function getText($raw, $breakWords, $highlights = array()) {
			$result = '';
			for ($i = 0; $i < count($this->content); $i++) {
				$node = $this->content[$i];
				$result .= $node->getText($raw, $breakWords, $highlights);
			}
			return $result;
		}
	}

	/**
	 * A ParseNode in the text that functions as a special tag. This is usefull for special text formating in eg. forums.
	 */
	class TagNode extends ParseNode {

		/**
		 * The tag definition of this tag.
		 *@var TextTag $tag
		 */
		var $tag;

		/**
		 * The parameter value of the tag (example: [color=red]hello[/color]) the parameter will be "red"
		 *@var string $parameter
		 */
		var $parameter;

		/**
		 * Creates an instance of the tagnode. This process is automatically done by the TextParser
		 *@param TextParser $textParser a reference to the textparser object for reading configuration options
		 *@param array $parentNodes the parentnodes in sequential order (see comment ParseNode)
		 *@param TextTag $tag the definition of the texttag that handles this node.
		 *@param string $parameter the value of the parameter given to the tag
		 */
		function TagNode(&$textParser, $parentNodes, &$tag, $parameter) {
			$this->ParseNode(&$textParser, $parentNodes);
			$this->tag = $tag;
			$this->parameter = $parameter;
		}

		/**
		 * Parses the text and parameter contents to the HTML style defined in the TextTag of this object.
		 *@param bool $raw when <code>true</code> plain text will be returned, when <code>false</code> the html replacement will be returned
		 */
		function getText($raw, $breakWords, $highlights=array()) {
			//print count($highlights)." highlights!\n";
			$result = '';
			$parseTag = !$raw;
			// Check if this tag contains tags that are not allowed inside this tag
			for ($i = 0; $i < count($this->content); $i++) {
				$node = $this->content[$i];
				if (get_Class($node) == 'tagnode') {
					if (!$this->tag->allowSubTag($node->tag->getName())) $parseTag = false;
				}
				if ((get_Class($node) == 'textnode') && (!$node->isEmpty())) {
					if (!$this->tag->allowSubTag('{text}')) $parseTag = false;
				}
			}
			$breakSetting = $this->tag->getWordBreaks();
			$acceptText = $this->tag->allowSubTag('{text}');
			for ($i = 0; $i < count($this->content); $i++) {
				$node = $this->content[$i];
				$text = $node->getText(!$parseTag, ($breakSetting == TextTag::breakAll()) || ($breakSetting == TextTag::breakText()), $highlights);
				if ((get_Class($node) == 'textnode') && (!$acceptText)) {
					$text = str_replace("\n", " ", $text);
					$text = str_replace("<br />", " ", $text);
				}
				$result .= $text;
			}
			if ($parseTag == true) {
				$htmlResult = $this->tag->getHtmlReplace();
				$htmlResult = str_Replace('{text}', $result, $htmlResult);
				$htmlResult = str_Replace('{parameter}', ($breakSetting == TextTag::breakAll()) || ($breakSetting == TextTag::breakParameter()) ? $this->textParser->breakLongWords($this->parameter) : $this->parameter, $htmlResult);
			} else {
				if (strlen($this->parameter) > 0)
					$htmlResult = $this->textParser->privateVars['tagStart1'].$this->tag->getName().'='.$this->parameter.$this->textParser->privateVars['tagStart2'].$result.$this->textParser->privateVars['tagEnd1'].$this->tag->getName().$this->textParser->privateVars['tagEnd2'];
				else
					$htmlResult = $this->textParser->privateVars['tagStart1'].$this->tag->getName().$this->textParser->privateVars['tagStart2'].$result.$this->textParser->privateVars['tagEnd1'].$this->tag->getName().$this->textParser->privateVars['tagEnd2'];
			}
			return $htmlResult;
		}
	}

	/**
	 * A ParseNode that represents plain text.
	 */
	class TextNode extends ParseNode {

		/**
		 * The plain text of this node
		 *@var string $text
		 */
		var $text;

		/**
		 * Creates an instance of TextNode.
		 *@param TextParser $textParser a reference to the textparser object for reading configuration options
		 *@param array $parentNodes the parentnodes in sequential order (see comment ParseNode)
		 *@param string $text the plain text for this node
		 */
		function TextNode(&$textParser, $parentNodes, $text) {
			$this->ParseNode(&$textParser, $parentNodes);
			$this->text = $text;
		}

		/**
		 * Sets the plain text for this node. The TextNode does <b>not</b> parse the given text.
		 *@param string $text the plain text to set
		 */
		function setText($text) {
			$this->text = $text;
		}

		/**
		 * Retrieves the raw text
		 *@param bool $raw does not apply to this class. It is always raw.
		 *@return string the raw text
		 */
		function getText($raw, $breakWords, $highlights = array()) {
			return ($breakWords) ? $this->textParser->breakLongWords($this->text, -1, $highlights) : $this->text;
		}

		/**
		 * Returns if the contents of the textnode is empty. Empty means that there are no visible characters in the text.
		 *@return bool true if the contents of the rawtext has no visible characters, false otherwise
		 */
		function isEmpty() {
			$text = $this->getText(false, false, array());
			$text = str_replace("\n", " ", $text);
			$text = str_replace("<br />", " ", $text);
			return (strLen(trim($text)) == 0) ? true : false;
		}
	}

	/**
	 * Creates an textparser to parse emoticons, tags, break long words and converts text to html format.
	 *@version 1.1
	 */
	class TextParser {

		/**
		 * The maximum allowed length of a word.
		 *@var int $maxWordLength
		 */
		var $maxWordLength;
		/**
		 * Container for private vars
		 * Variables declared in privateVars:
		 * - tagStart1 (string)
		 * - tagStart2 (string)
		 * - tagEnd1 (string)
		 * - tagEnd2 (string)
		 * - tagList (TextTagList)
		 *
		 *@var array $privateVars
		 */
		var $privateVars;

		/**
		 * Instantiates a new textparser with default options
		 */
		function TextParser() {
			$this->privateVars = array();
			// Default tag format: [name=parameter]content[/name]
			$this->privateVars['tagStart1'] = "[";
			$this->privateVars['tagStart2'] = "]";
			$this->privateVars['tagEnd1'] = "[/";
			$this->privateVars['tagEnd2'] = "]";

			$this->maxWordLength = 75;
			$this->privateVars['tagList'] = null;
		}

		function parseMessageText($text, $emoticonList, $tbbTagList, $highlights=array()) {
			$result = htmlConvert($text);
			if (!is_Object($tbbTagList)) $result = $this->breakLongWords($result, -1, $highlights);

			$result = str_replace("\n", "<br />\n", $result);
			if (is_Object($tbbTagList)) $result = $this->parseTextTags($result, $tbbTagList, $highlights);

			if (is_Object($emoticonList)) $result = $this->parseEmoticons($result, $emoticonList);
			return $result;
		}

		function breakLongWords($text, $length=-1, $highlights=array()) {
			if ($length == -1) $length = $this->maxWordLength;

			$rows = explode("\n", $text);
			for ($i = 0; $i < count($rows); $i++) {
				$row = $rows[$i];
				$words = explode(' ', $row);
				for ($j = 0; $j < count($words); $j++) {
					$word = $words[$j];
					$tags = explode($this->privateVars['tagStart1'], $word);
					for ($k = 0; $k < count($tags); $k++) {
						$tag = $tags[$k];

						if (strLen($tag) > $length) {
							// Cut the string in pieces!
							$sentence = "";
							while (strLen($tag) > $length) {
								$sentence .= subStr($tag, 0, $length) . "\n";
								$tag = subStr($tag, $length);
							}
							if (strLen($tag) > 1)
								$sentence .= $tag;
							$tags[$k] = $sentence;
						}
						// highlight words
						for ($hl = 0; $hl < count($highlights); $hl++) {
							if (strCaseCmp($tag, $highlights[$hl]) == 0) {
								$tags[$k] = sprintf('<span class="highlight%s">%s</span>', $hl, $tag);
							} else {
								$upWord = strToUpper($tag);
								$foundPos = strPos($upWord, strToUpper($highlights[$hl]));
								if ($foundPos !== false) {
									$word = subStr($tag, $foundPos, strLen($highlights[$hl]));
									$tags[$k] = str_Replace($word, sprintf('<span class="highlight%s">%s</span>', $hl, $word), $tag);
								}
							}
						}
					}
					$words[$j] = implode($this->privateVars['tagStart1'], $tags);
				}
				$rows[$i] = implode(' ', $words);
			}
			$result = implode("\n", $rows);
			return $result;
		}

		/**
		 * Places the emoticons from the given emoticonlist in the text
		 *@param string $text the text to place the emoticons in
		 *@param EmoticonList $emoticonList the list with emoticon definitions
		 *@return string the text in HTML form with emoticons images
		 */
		function parseEmoticons($text, &$emoticonList) {
			$result = $text;
			$emoticons = $emoticonList->getEmoticonsParseOrder();
			for ($i = 0; $i < count($emoticons); $i++) {
				$emoticon = $emoticons[$i];
				$result = str_Replace(htmlConvert($emoticon['code']), '<img src="'.$emoticon['imgUrl'].'" alt="'.$emoticon['code'].'" title="'.$emoticon['name'].'" />', $result);
			}
			return $result;
		}

		/**
		 * Parses the text for tags.
		 *@param string $text the text to parse for tags
		 *@param TextTagList $tagList the list containing the tag definitions
		 */
		function parseTextTags($text, $tagList, $highlights=array()) {
			$this->privateVars['tagList'] = $tagList;

			$textNode = new ParseNode($this, array());
			$textNode->setText($text);
			//print count($highlights)." highlights!\n";

			$result = $textNode->getText(false, true, $highlights);
			return $result;
		}

		/**
		 * Returns an array with taginformation of tags with the given name
		 *@param string $tagName name of the tag to get the definitions of
		 *@return array An array of TextTag objects where the tags have the name $tagName
		 */
		function getTextTags($tagName) {
			if (!is_Object($this->privateVars['tagList'])) return false;
			$tagList = $this->privateVars['tagList'];
			$result = array();

			for ($i = 0; $i < $tagList->getTagCount(); $i++) {
				$TBBtag = $tagList->getTag($i);
				if ($tagName == $TBBtag->getName()) $result[] = $TBBtag;
			}
			return $result;
		}

		function findEndTag($text, $start, $tag) {
			$startPos = strPos($text, $this->privateVars['tagEnd1'], $start);
			while ($startPos !== false) {
				$endPos = strPos($text, $this->privateVars['tagEnd2'], $startPos);
				if ($endPos === false) return false;
				$nextPos = strPos($text, $this->privateVars['tagEnd1'], $startPos+1);
				if (($nextPos === false) || ($endPos < $nextPos)) {
					$tagName = subStr($text, $startPos + 2, ($endPos - $startPos)-2);
					if ($tag->isEndTag($tagName)) {
						$result = array(
							'type' => 'tagStart',
							'name' => $tagName,
							'startPos' => $startPos,
							'endPos' => ($endPos + 1),
							'tag' => $tag
						);
						return $result;
					}
				}
				$startPos = $nextPos;
			}
			if (!$tag->endTagRequired()) {
				$result = array(
					'name' => "",
					'startPos' => strLen($text),
					'endPos' => strLen($text),
					'tag' => $tag
				);
				return $result;
			}
			return false;
		}

		function findStartTag($text, $start, $searchTag, $parentTags) {
			$startPos = strPos($text, $this->privateVars['tagStart1'], $start);
			while ($startPos !== false) {
				$endPos = strPos($text, $this->privateVars['tagStart2'], $startPos);
				if ($endPos === false) return false;
				$nextPos = strPos($text, $this->privateVars['tagStart1'], $startPos+1);
				if (($nextPos === false) || ($endPos < $nextPos)) {
					$tagName = subStr($text, $startPos + 1, ($endPos - $startPos)-1);
					$hasParameter = false;
					$parameter = "";
					$paramPos = strPos($tagName, '=');
					if ($paramPos !== false) {
						$hasParameter = true;
						$parameter = subStr($tagName, ($paramPos+1));
						$tagName = subStr($tagName, 0, $paramPos);
					}
					if (is_object($searchTag)) {
						if ($searchTag->getName() == $tagName) {
							$valid = true;
							if ($hasParameter) {
								$valid = $searchTag->allowParameter($parameter);
							}
							if ($valid) {
								$result = array(
									'name' => $tagName,
									'startPos' => $startPos,
									'endPos' => ($endPos + 1),
									'hasParameter' => $hasParameter,
									'parameterValue' => $parameter,
									'tag' => $searchTag
								);
								return $result;
							}
						}
					} else {
						$tags = $this->getTextTags($tagName);

						for ($i = 0; $i < count($tags); $i++) {
							$tag = $tags[$i];
							if (is_object($tag) && ($tag->mayInTags($parentTags))) {
								$valid = true;
								if ($hasParameter) {
									$valid = $tag->allowParameter($parameter);
								}
								if ($valid) {
									$result = array(
										'name' => $tagName,
										'startPos' => $startPos,
										'endPos' => ($endPos + 1),
										'hasParameter' => $hasParameter,
										'parameterValue' => $parameter,
										'tag' => $tag
									);
									return $result;
								}
							}
						}
					}
				}
				$startPos = $nextPos;
			}
			return false;
		}
	}

?>
