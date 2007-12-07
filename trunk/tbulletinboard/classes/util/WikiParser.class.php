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
	 * Author:		Marc Worrell
	 * Copyright:	(c) 2005-2007 Marc Worrell
	 * Description:	Parser for Wiki texts, translates to HTML
	 *
	 * http://wiki.ciaweb.net/yawiki/index.php?area=Text_Wiki&page=WikiRules
	 *
	 * Modified for use by TBB2 by Matthijs Groen
	 */

	class WikiParser {
	
		// Parameters:	$s			Wiki text to translate
		//				$options	(optional) translation options
		// Returns:		html version of parameter
		// Description:	parse the wiki text and generate a html version
		//
		//	this parser is a hand coded shift-reduce like parser.
		//	due to the nature of the wiki texts a recursive descent parser is not feasible.
		//
		//  the only option is 'target', set to 'line' to suppress a <p/> around the generated
		//	line.  this is useful for making html of headlines and titles.  as it is not too
		//  handy to have <p/> inside your <h1/>
		//
		public function parseWiki($s, $options = array()) {
			list($tk, $tk_s) = $this->tokenizeText($s, $options);

			$block		= array();		// lines for current block
			$line		= array();		// stacked tokens for current line
			$line_s		= array();		// stacked texts for current line

			$html		= '';			// generated html
			$i			= 0;
			$toc		= false;		// is there a toc or not?	
	
			do {
				$tok = $tk[$i];
				switch ($tok) {
				case 'br':
					$line[]   = 'html';
					$line_s[] = "<br/>\n";
					break;
				case 'html':
					list($_html, $block) = $this->handleLine($block, $line, $line_s);
					$html  .= $_html . $this->handleBlock($block);
			
					if ($this->allowHTML()) {
						$html  .= "\n<!--[html]-->" . $tk_s[$i] . "<!--[/html]-->\n";
					} else {
						$html .= '<p>' . nl2br(strip_tags($tk_s[$i])) . '</p>';
					}
					$line   = array();
					$line_s = array();
					$block  = array();
					break;
			
				case 'code':
					list($_html, $block) = $this->handleLine($block, $line, $line_s);
					$html  .= $_html . $this->handleBlock($block);
			
					$html  .= "\n<pre>\n" . $this->convertHTMLspecialChars($tk_s[$i]) . "</pre>\n";
			
					$line   = array();
					$line_s = array();
					$block  = array();
					break;

				case 'p':
				case 'end':
					list($_html, $block) = $this->handleLine($block, $line, $line_s);
					$html  .= $_html . "\n" . $this->handleBlock($block);
					$line   = array();
					$line_s = array();
					$block  = array();
					break;
		
				case 'newline':
					list($_html, $block) = $this->handleLine($block, $line, $line_s);
					$html  .= $_html;
					$line   = array();
					$line_s = array();
					break;
		
				case 'toc':
					$html  .= '<!--[[toc]]-->';
					$line   = array();
					$line_s = array();
					$toc	= true;
					break;
			
				case 'comment':
					if ($i == 0) {
						// Comment at the start of a line or in a block
						$html .= '<!-- '.$this->convertHTMLspecialChars(trim($tk_s[$i])).' -->';
					} else {
						// Comment in a line
						list($line, $line_s) = $this->handleTokenStack($line, $line_s, $tok, $tk_s[$i]);
					}
					break;

				case 'word':
				case ' ':
				default:
					list($line, $line_s) = $this->handleTokenStack($line, $line_s, $tok, $tk_s[$i]);
					break;
				}
				$i++;
			}
			while ($tok != 'end');
	
			// Merge <p/>'s over more than one line
			$html = preg_replace("|</p>\n<p>|", "<br/>\n", $html);
	
			if (!empty($options['target']) && $options['target'] == 'line') {
				// Strip the <p> tags... the user wants a single line.
				$html = trim(preg_replace('|</?p>|', ' ', $html));
			} else if ($toc) {
				$html = $this->getToc($html);
			}
	
			return trim($html);
		}

		private function convertHTMLspecialChars($text) {
			return $text;
		}

		// Parameters:	-
		// Returns:		false	no html allowed
		//				true 	html allowed
		// Description:	Check if the ACL allows html entry
		//
		public function allowHTML() {
			$allow = false;
			if (isset($GLOBALS['any_acl'])) {
				$allow = $GLOBALS['any_acl']->allowHtml();
			}
			return $allow;
		}

		// Parameters:	$uri		uri to be checked
		// Returns:		false	when uri not allowed
		//				uri		when allowed
		// Description:	Check if the ACL allows the given uri
		//
		public function filterUri($uri) {
			if (isset($GLOBALS['any_acl'])) {
				$uri = $GLOBALS['any_acl']->filterUri($uri);
			}
			return $uri;
		}

		// Parameters:	$uri		uri to be checked
		// Returns:		false	when uri not allowed
		//				uri		when allowed
		// Description:	Check if the ACL allows the given attrs.
		//				This function has a short whitelist of allowed attributes.
		//
		public function filterAttributes($attr) {
			$as = array();
			foreach ($attr as $a => $v) {
				switch ($a) {
				case 'id':
				case 'name':
				case 'align':
				case 'valign':
				case 'title':
				case 'width':
				case 'height':
				case 'rel':
				case 'alt':
				case 'class':
				case 'link':
				case 'caption':
					$as[$a] = $v;
					break;
				default:
					if (isset($GLOBALS['any_acl']) && $GLOBALS['any_acl']->allowHtml()) {
						$as[$a] = $v;
					}
					break;
				}
			}
			return $as;
		}

		// Function:	_wiki_reduce_block
		// Access:		INTERNAL
		// Parameters:	$block		the tokens in the block
		// Returns:		html fragment
		// Description:	Force the complete reduction of a block to html
		//
		private function handleBlock($block) {
			if (count($block) > 0) {
				list($html, $block) = $this->handleBlockStack($block, array('end'), array(''));
			} else {
				$html = '';
			}
			return $html;
		}

		// Parameters:	$block		the tokens in the block
		//				$line		line tokens
		//				$line_s		line strings
		// Returns:		array(html-fragment, block)
		// Description:	(Partially) reduces the block after encountering the given line
		//
		//				Checks for:
		//				- enumerated lists
		//				- tables
		//				- blockquote
		//
		//
		// Each block entry is as follows:
		//
		//			( class, depth, class-parms, line_tokens, line_strings )
		//
		// Where class is one of:
		//
		//			table, ul, ol, blockquote, dl
		//
		// Depth is valid for:
		//
		//			ul, ol, blockqoute
		//
		private function handleBlockStack($block, $line, $line_s) {
			if (!empty($line)) {
				if ($line[0] == '=' && @$line[1] == ' ') {
					$html  				 = $this->handleBlock($block);
					list($line, $line_s) = $this->merge($line, $line_s, 2, false, true);
					$html .= "\n<p><div style=\"text-align: center;\">" . $line_s[2] . "</div></p>\n";
			
					return array($html, array());
				}
			}

			$block_line = $this->getBlockTypeOfLine($line, $line_s);

			if ($block_line[0] == 'p' || $block_line[0] == 'end') {
				$html = $this->handleBlockLines($block);

				if ($block_line[0] == 'p') {
					list($line, $line_s) = $this->merge($line, $line_s, 0, false, true);
					if (!empty($line_s[0])) {
						$html .= "<p>" . $line_s[0] . "</p>\n";
					}
				}
		
				$block = array();
			} else {
				$block[] 	= $block_line;	
				$html    	= '';
			}
	
			return array($html, $block);
		}

		// Parameters:	$block		a complete block
		// Returns:		html
		// Description:	recursively reduces a block to html
		//				all line level reductions have been done
		//				what we get is a block of lines, each preparsed.
		//
		private function handleBlockLines(&$block) {
			if (empty($block)) {
				return '';
			}

			$len	= count($block);
			$class	= $block[0][0];
			$depth	= $block[0][1];

			// Collect all lines with the same class and depth
	
			$sub_block   = array();
			$sub_block[] = array_shift($block);

			if ($class == 'ol') {
				$alt_class = 'ul';
			} else if ($class == 'ul') {
				$alt_class = 'ol';
			} else {
				$alt_class = false;
			}
	
			while (!empty($block) && $block[0][1] >= $depth && ($block[0][0] == $class || $block[0][0] == $alt_class)) {
				if ($block[0][1] > $depth || $block[0][0] != $class) {
					// this is a nested block of the same kind
					// reduce this one separately and remember the html in the previous block line
					$html = $this->handleBlockLines($block);
			
					if (!empty($html)) {
						$sub_block[count($sub_block)-1][5] = $html;
					}
				} else {
					$sub_block[] = array_shift($block);
				}
			}

			// special handling for a table
			$td = 0;
			if ($class == 'table') {
				foreach ($sub_block as $sub) {
					$td = max($td, $sub[2]);
				}
			}
	
			// generate the html for the captured block
			$html = "<$class class=\"wikielement\">\n";
			$nr   = 0;
			foreach ($sub_block as $sub) {
				$pars	= $sub[2];
				$line	= $sub[3];
				$line_s	= $sub[4];
				$nested	= isset($sub[5]) ? $sub[5] : '';
				$nr++;
		
				switch ($class) {
				case 'ol':
				case 'ul':
					list($line, $line_s) = $this->merge($line, $line_s, 2, false, true);
					$html .= '<li>' . trim($line_s[2]) . $nested . "</li>\n";
					break;

				case 'table':
					// Generate a row
					$html .= $this->createTableRow($td, $line, $line_s, $pars);
					break;
		
				case 'blockquote':
					if ($nr == 1) {
						$html .= '<p>';
					}
					list($line, $line_s) = $this->merge($line, $line_s, 2, false, true);
					$html .= $line_s[2] . $nested;
					if ($nr != count($sub_block)) {
						$html .= '<br/>';
					} else {
						$html .= "</p>\n";
					}
					break;
		
				case 'dl':
					// $pars is the offset of the first ' ' of the ' : ' separating the dt from the dd
					list($line, $line_s) = $this->merge($line, $line_s, $pars+3, false, true);

					// the reduced html of the dd
					$dd = array_pop($line_s);
					array_pop($line);
			
					// op the ' ' ':' ' ';
					array_pop($line_s);
					array_pop($line);
					array_pop($line_s);
					array_pop($line);
					array_pop($line_s);
					array_pop($line);

					// Reduce the dt part
					list($line, $line_s) = $this->merge($line, $line_s, 2, false, true);
					$dt = array_pop($line_s);
			
					$html .= "  <dt>$dt</dt>\n  <dd>$dd</dd>\n";
					break;
				}
			}
			$html .= "</$class>\n\n";

			return $html;
		}

		// Parameters:	$table_cols	nr of tds
		// 				$line		tokens in line
		//				$line_s		text of tokens
		// Returns:		html for row
		// Description:	generates the html for a row
		//
		function createTableRow($table_cols, $line, $line_s) {
			$html	= "<tr>";
			$len	= count($line);
			$td		= array();

			$start	= 1;
			$colspan= 1;

			// Split the line in tds 
			for ($i=1;$i<$len;$i++) {
				if ($line[$i] == '||') {
					if ($line[$i-1] == '||' && $i+1 < $len) {
						$colspan++;
						$start++;
					} else {
						// A td from $start to $i-1
						if ($i - $start > 0) {
							$td[]	= array(array_slice($line,   $start, $i - $start),
												array_slice($line_s, $start, $i - $start),
												$colspan);
						} else {
							$td[]	= array(false, false, $colspan);
						}
						$start   = $i+1;
						$colspan = 1;
					}
				}
			}
	
			// Generate the html per td
			foreach ($td as $t) {
				$line    = $t[0];
				$line_s  = $t[1];
		
				if ($t[2] > 1) {
					$colspan = ' colspan="' . $t[2] . '" ';
				} else {
					$colspan = '';
				}
		
				if (!empty($line)) {
					$end = "</td>";
					switch ($line[0]) {
					case '>':
						$html .= "\n  <td style=\"text-align: right;\"$colspan>";
						$start = 1;
						break;
					case '<':
						$html .= "\n  <td style=\"text-align: left;\"$colspan>";
						$start = 1;
						break;
					case '=':
						$html .= "\n  <td style=\"text-align: center;\"$colspan>";
						$start = 1;
						break;
					case '~':
						$html .= "\n  <th$colspan>";
						$end   = "</th>";
						$start = 1;
						break;
					default:
						$html .= "\n  <td$colspan>";
						$start = 0;
						break;
					}

					list($line, $line_s) = $this->merge($line, $line_s, $start, false, true);
			
					$html .= trim($line_s[$start]) . $end;
				} else {
					$html .= "\n  <td$colspan></td>";
				}
			}
	
			$html .= "\n</tr>\n";
			return $html;
		}


		// Function:	_wiki_block_line
		// Access:		INTERNAL
		// Parameters:	$line		line tokens
		//				$line_s		line strings
		// Returns:		a block line entry
		// Description:	checks the line to see what kind of block line the line is
		//
		private function getBlockTypeOfLine($line, $line_s) {
			$len = count($line);
	
			if ($len >= 2) {
				// : term : definition
				if ($line[0] == ':' &&	$line[1] == ' ') {
					// Try to find (' ', ':' , ' ');
					$i    = 2;
					$offs = false;
					while ($i < $len - 2 && $offs === false) {
						if ($line[$i] == ':' && $line[$i-1] == ' ' && $line[$i+1] == ' ') {
							$offs = $i-1;
						}
						$i++;
					}
			
					if ($offs !== false) {
						return array('dl', 0, $offs, $line, $line_s);
					}
				}
		
				// || td || .. ||
				if ($line[0] == '||' && $line[$len-1] == '||') {
					// count the number of cols
					$cols = 0;
					for ($i = 0; $i<$len; $i++) {
						if ($line[$i] == '||') {
							$cols++;
						}
					}
					return array('table', 0, $cols-1, $line, $line_s);
				}
		
				// > block quoted text
				if ($line[0] == '>' && $line[1] == ' ') {
					return array('blockquote', strlen($line_s[0]), 0, $line, $line_s);
				}

				// * unordered list
				if ($line[0] == '*' && $line[1] == ' ') {
					return array('ul', 0, 0, $line, $line_s);
				}
				if ($line[0] == ' ' && $line[1] == '*' && $line[2] == ' ') {
					return array('ul', strlen($line_s[0]), 0, $line, $line_s);
				}

				// # ordered list
				if ($line[0] == '#' && $line[1] == ' ') {
					return array('ol', 0, 0, $line, $line_s);
				}
				if ($line[0] == ' ' && $line[1] == '#' && $len > 2 && $line[2] == ' ') {
					return array('ol', strlen($line_s[0]), 0, $line, $line_s);
				}
			}
	
			// Just another part of a paragraph
			if ($len > 0 && $line[0] == 'end') {
				return array('end', 0, 0, $line, $line_s);
			} else {
				return array('p', 0, 0, $line, $line_s);
			}
		}

		// Parameters:	$block		the tokens in the block
		// 				$line		the line stack
		//				$line_s		line texts
		// Returns:		html fragment
		//				modified block
		// Description:	Reduce the current line and append it to the current block.
		//				The reduction of a single line checks for:
		//				- non reduced :// or mailto: urls
		//				- non reduced wiki words
		//				- headers
		//				- blockquote levels
		//				- enumerated lists
		//				- table rows
		//
		private function handleLine($block, $line, $line_s) {
			// wiki words
			list($line, $line_s) = $this->replaceWikiWords($line, $line_s);
	
			if (count($line) == 1 && $line[0] == '-' && (strlen($line_s[0]) == 4 || strlen($line_s[0]) == 3)) {
				// horiz	\n----\n
				$html = $this->handleBlock($block);
				return array($html . "\n<hr />\n", array());
			}
	
			if (count($line) > 2 && $line[0] == '=' && $line[1] == ' ' && strlen($line_s[0]) <= 6) {
				//  \n====== headline 1..6 ======
				list($line, $line_s) = $this->merge($line, $line_s, 2, false, true);
				$html   = $this->handleBlock($block);
				$level  = strlen($line_s[0]);
				$title = substr($line_s[2], 0, -$level);
				$html  .= "\n<h$level>".trim($title)."</h$level>\n";
		
				return array($html, array());
			}
	
			return $this->handleBlockStack($block, $line, $line_s);
		}

		// Parameters:	$line
		//				$line_s
		//				$tok
		//				$tok_s
		// Returns:		the new line state
		// Description:	Shifts the given token on the stack and reduces the stack
		//				returning a new line state.
		//
		private function handleTokenStack($line, $line_s, $tok, $tok_s) {
			switch ($tok) {
			case "em":
			case "strong":
			case "sup":
			case "sub":
			case "strike":
			case '}}':
				//  "//"  or "**" or "^^" or {{ }}
				$offs = $this->searchTokenInStack($line, $this->handleInlineStart($tok));
				if ($offs !== false) {
					list($line, $line_s) = $this->merge($line, $line_s, $offs+1, false, true);
					array_pop($line);
					$text	  = array_pop($line_s);
					array_pop($line);
					array_pop($line_s);
					$line[]   = 'html';
					$line_s[] =  $this->handleInlineHtml($tok, $text);
				} else {
					$line[]   = $tok;
					$line_s[] = $tok_s; 
				}
				break;
		
			case '@@':
				// @@---minus+++revision@@
				$offs = $this->searchTokenInStack($line, '@@');
				if ($offs !== false) {
					list($line, $line_s) = $this->handleRevision($line, $line_s, $offs);
				} else {
					$line[]   = $tok;
					$line_s[] = $tok_s; 
				}
				break;

			case '##':
				// ##color|text##
				$offs = $this->searchTokenInStack($line, '##');
				if ($offs !== false) {
					list($line, $line_s) = $this->handleColoredText($line, $line_s, $offs);
				} else {
					$line[]   = $tok;
					$line_s[] = $tok_s; 
				}
				break;
			case ']':
				// [uri descr]
				$offs = $this->searchTokenInStack($line, '[');
				if ($offs !== false) {
					list($line, $line_s) = $this->handleLink($line, $line_s, $offs);
				} else {
					$line[]   = $tok;
					$line_s[] = $tok_s; 
				}
				break;
			case ']]':
				// [[# anchor-name]]
				// [[image iamge-pars]]
				$offs = $this->searchTokenInStack($line, '[[');
				if ($offs !== false && $line[$offs+1] == '#') {
					list($line, $line_s) = $this->handleAnchor($line, $line_s, $offs);
				} else if ($offs !== false && $line[$offs+1] == 'word' && $line_s[$offs+1] == 'image') {
					list($line, $line_s) = $this->handleImage($line, $line_s, $offs);
				} else {
					$line[]   = $tok;
					$line_s[] = $tok_s; 
				}
				break;
		
			case '))':
				// ((name|descr))
				$offs = $this->searchTokenInStack($line, '((');
				if ($offs !== false) {
					list($line, $line_s) = $this->handleFreeLink($line, $line_s, $offs);
				} else {
					$line[]   = $tok;
					$line_s[] = $tok_s; 
				}
				break;

			case 'comment':
				$line[]   = 'html';
				$line_s[] = '<!-- '. $this->convertHTMLspecialChars(trim($tok_s)) . ' -->';
				break;

			default:
				$line[]	  = $tok;
				$line_s[] = $tok_s;
				break;
			}
	
			return array($line, $line_s);
		}

		// helper for @@--- +++ @@ revision patterns
		private function handleRevision($line, $line_s, $offs) {
			// @@---minus+++revision@@
			$len  = count($line_s);
			$offs = $this->searchTokenInStack($line, '@@');
			if ($offs !== false && $offs < $len-1 && ($line_s[$offs+1] == '---' || $line_s[$offs+1] == '+++')) {
				if ($line_s[$offs+1] === '---') {
					$offs_del = $offs+1;			
					$offs_ins = $offs+2;
			
					// Try to find the '+++'
					while ($offs_ins < $len && $line_s[$offs_ins] != '+++') {
						$offs_ins++;
					}
				} else {
					$offs_del = false;
					$offs_ins = $offs+1;
				}
		
				if ($offs_ins < $len) {
					list($line, $line_s) = $this->merge($line, $line_s, $offs_ins+1, false, true);
					array_pop($line);
					$ins = array_pop($line_s);

					// Remove the '+++'
					array_pop($line);
					array_pop($line_s);
				} else {
					$ins = false;
				}
		
				if ($offs_del !== false) {
					list($line, $line_s) = $this->merge($line, $line_s, $offs_del+1, false, true);
					array_pop($line);
					$del = array_pop($line_s);

					// Remove the '---'
					array_pop($line);
					array_pop($line_s);
				} else {
					$del = false;
				}

				// Remove the '@@';
				array_pop($line);
				array_pop($line_s);
		
				if (!empty($del)) {
					$line[]   = 'html';
					$line_s[] =  $this->handleInlineHtml('del', $del);
				}
				if (!empty($ins)) {
					$line[]   = 'html';
					$line_s[] =  $this->handleInlineHtml('ins', $ins);
				}
			}
			return array($line, $line_s);
		}


		// helper for [[# anchor-name]]
		private function handleAnchor($line, $line_s, $offs) {
			// fetch the anchor name
			list($line, $line_s) = $this->merge($line, $line_s, $offs+2, -1, false, false);

			// pop the name
			array_pop($line);
			$name = array_pop($line_s);
	
			// pop the #
			array_pop($line);
			array_pop($line_s);

			$line[$offs]   = 'html';
			$line_s[$offs] = '<a name="' . $this->convertHTMLspecialChars(trim($name)) . '"></a>';
	
			return array($line, $line_s);
		}


		// helper for [[image path/to/image image-pars]]
		private function handleImage($line, $line_s, $offs) {
			// fetch the complete text
			list($line, $line_s) = $this->merge($line, $line_s, $offs+2, -1, false, false);

			// pop the image path and parameters
			array_pop($line);
			$text = trim(array_pop($line_s));

			// pop 'image'
			array_pop($line);
			array_pop($line_s);
	
			// Extract the interesting parts from the image description
			$pos = strpos($text, ' ');
	
			if ($pos === false) {
				$src  = $text;
				$attr = array();
			} else {
				$src  = substr($text, 0, $pos);
				$attr = $this->getAttrs(substr($text, $pos+1));
			}

			// Remove double quotes around the uri, some people do type them...
			if (strlen($src) >= 2 && $src{0} == '"' && $src{strlen($src)-1} == '"') {
				$src = substr($src, 1, -1);
			}

			// We have to postpone the image generation till 'showtime' because an image
			// typically refers to data that is dynamic.  So we just pack the image data
			// in a special tag and do an expand in smarty.

			if ((strpos($src, '://') !== false 
				||	strpos($src, '/') !== false
				||	preg_match('/^[a-zA-Z0-9_]+\.[a-z]{3}$/', $src))
				&&	(	empty($attr['link'])
					||	(	strpos($attr['link'], '://') !== false
					&&	strncasecmp($attr['link'], 'popup:', 6) != 0)))
			{
				if (!empty($attr['link'])) {
					// Remove double quotes around the uri, some people do type them...
					$link = $attr['link'];
					if (strlen($link) >= 2 && $link{0} == '"' && $link{strlen($link)-1} == '"') {
						$link = substr($link, 1, -1);
					}
	
					$pre  = '<a href="' . $this->convertHTMLspecialChars($this->filterUri($link)) . '" target="_blank">';
					$post = '</a>';
					unset($attr['link']);
				} else {
					$pre  = '';
					$post = '';
				}
		
				$html = $pre . '<img src="'. $this->convertHTMLspecialChars($src) . '" ';

				if (!isset($attr['alt'])) {
					$attr['alt'] = '';
				}

				$attr = $this->filterAttributes($attr);

				foreach ($attr as $label=>$value) {
					$html .= $this->convertHTMLspecialChars($label) . '="' . $this->convertHTMLspecialChars($value) .'" ';
				}
				$html .= '/>' . $post;
			} else {
				// Pack the attributes so that we can easily expand them again.
				$html  = '<!--[image ';
				$html .= $this->convertHTMLspecialChars($src);

				if (!empty($attr['link'])) {
					$attr['link'] = $this->filterUri($attr['link']);
				}
				$attr  = $this->filterAttributes($attr);

				foreach ($attr as $label=>$value) {
					$html .= ' ' . $this->convertHTMLspecialChars($label) . '="' . $this->convertHTMLspecialChars($value) .'"';
				}
				$html .= ']-->';
			}
	
			$line[$offs] 	= 'html';
			$line_s[$offs] 	= $html;
	
			return array($line, $line_s);
		}



		// helper for ##color| ## colored text
		private function handleColoredText($line, $line_s, $offs) {
			// Check for the optional description
			$space = $this->findTokenAfter($line, '|', $offs);
			if ($space != false) {
				// Fetch description of link
				list($line, $line_s) = $this->merge($line, $line_s, $space+1, -1, true);
				array_pop($line);
				$text = trim(array_pop($line_s));

				array_pop($line);
				array_pop($line_s);
			} else {
				$text = false;
			}

			// Merge all tokens for the color
			list($line, $line_s) = $this->merge($line, $line_s, $offs+1, -1, false);
			array_pop($line);
			$color = trim(array_pop($line_s));

			if ((strlen($color) == 3 || strlen($color) === 6)
				&&	preg_match('/^[0-9a-fA-F]+$/', $color))
			{
				$color = '#' . $color;
			}
	
			// pop the opening '##'
			array_pop($line);
			array_pop($line_s);
	
			// Create the span
			if (!empty($text)) {
				$line[]   = 'html';
				$line_s[] = "<span style=\"color: $color;\">$text</span>";
			}	
			return array($line, $line_s);
		}

		// helper for [uri descr]
		private function handleLink($line, $line_s, $offs) {
			// Keep a copy of line/line_s in case we don't find an uri
			$line0   = $line;
			$line_s0 = $line_s;

			// Check for the optional description
			$space = $this->findTokenAfter($line, ' ', $offs);
			if ($space != false) {
				// Fetch description of link
				list($line, $line_s) = $this->merge($line, $line_s, $space, -1, false);
				array_pop($line);
				$descr = trim(array_pop($line_s));
		
				// Try to fetch any optional attributes
				list($descr, $attrs) = $this->splitDescrAttrs($descr);
			} else {
				$descr = false;
				$attrs = false;
			}

			// Merge all tokens for the uri
			list($line, $line_s) = $this->merge($line, $line_s, $offs+1, -1, false, false);
			array_pop($line);
			$uri   = array_pop($line_s);

			// only accept this construct when the uri looks like an uri
			$colon = strpos($uri, ':');
			$dot   = strpos($uri, '.');
			$last  = strlen($uri) - 1;

			if (	strpos($uri, '/') !== false
				||	strpos($uri, '#') !== false
				||	($dot !== false && $dot < $last)
				||	($colon > 0 && $colon < $last))
			{
				// pop the opening '['
				array_pop($line);
				array_pop($line_s);
	
				// Create the link
				if (empty($descr)) {
					// Footnote
					//$html = '<sup>' . $this->createLink($uri, '*', '', $attrs) .'</sup>';
					
					// Fix by Matthijs Groen to keep tag parsing possible
					$line     = $line0;
					$line_s   = $line_s0;
		
					$line[]   = ']';
					$line_s[] = ']';
					return array($line, $line_s);
				} else {
					// Described link
					$html = $this->createLink($uri, $descr, '', $attrs);
				}
				$line[]   = 'html';
				$line_s[] = $html;
			} else {
				// No uri found, do not reduce the found [uri descr] construct
				$line     = $line0;
				$line_s   = $line_s0;
		
				$line[]   = ']';
				$line_s[] = ']';
			}
			return array($line, $line_s);
		}

		// helper for ((uri|descr))
		private function handleFreeLink($line, $line_s, $offs) {
			// Check for the optional description
			$anchor = false;
			$pipe   = $this->findTokenAfter($line, '|', $offs);
			if ($pipe != false) {
				$hash = $this->findTokenAfter($line, '#', $pipe, true);
				if ($hash !== false) {
					list($line, $line_s) = $this->merge($line, $line_s, $hash+1, -1, false, false);
					array_pop($line);
					$anchor = '#' . trim(array_pop($line_s));

					array_pop($line);
					array_pop($line_s);
				}
		
				// Fetch description of link
				list($line, $line_s) = $this->merge($line, $line_s, $pipe+1, -1, false);
				array_pop($line);
				$descr = trim(array_pop($line_s));

				list($descr, $attrs) = $this->splitDescrAttrs($descr);
		
				array_pop($line);
				array_pop($line_s);
			} else {
				$descr = false;
				$attrs = false;
			}

			// Merge all tokens for the uri (we will need unescaped text for this one)
			list($line, $line_s) = $this->merge($line, $line_s, $offs+1, -1, false, false);
			array_pop($line);
			$uri = array_pop($line_s);

			// pop the opening '['
			array_pop($line);
			array_pop($line_s);
	
			// Create the link
			$line[]   = 'html';
			$line_s[] = $this->createLink($uri, $descr, $anchor, $attrs);
	
			return array($line, $line_s);
		}

		// Parameters:	$stack		stack with tokens
		//				$tok		try to find this token
		//				$start		(optional) look below this offset
		// Returns:		offset in stack
		//				false when not found
		// Description:	try to locate the token the stack in the stack,
		//				starting to search on top
		//
		private function searchTokenInStack($stack, $tok, $start = false) {
			if ($start === false) {
				$start = count($stack) - 1;
			} else {
				$start--;
			}
	
			// Don't scan through tds...
			while ($start >= 0 
				&&	$stack[$start] != $tok 
				&&	($tok == '||' || $stack[$start] != '||'))
			{
				$start--;
			}
	
			if ($start < 0 || $stack[$start] != $tok) {
				$start = false;
			}
			return $start;
		}

		// Parameters:	$line		list of tokens
		// 				$tok		token to find
		// 				$offs		offset to start above
		//				$space		(optional) set to false to disallow whitespace
		// Returns:		false when not found
		//				offset otherwise
		// Description:	find the given token _after_ the given offset
		//
		private function findTokenAfter($line, $tok, $offset, $space = true) {
			$ct = count($line);
			while ($offset < $ct && $line[$offset] != $tok
				&&	($space	|| $line[$offset] != ' '))
			{
				$offset ++;
			}
	
			if ($offset == $ct || $line[$offset] != $tok) {
				return false;
			} else {
				return $offset;
			}
		}

		// Parameters:	$stack		the token stack
		//				$stack_s	the texts of the stack
		//				$depth		the offset to start the merge
		//				$count		number of tokens to merge (-1 for all)
		//				$replace	do some wikiword on uri replacements
		//				$escape		(optional) set to false to not escape html specialchars
		// Returns:		modified token stack
		// Description:	merges the given entries into one textual entry
		//				literal and word entries will be escaped with htmlspecialchars.
		//
		private function merge($stack, $stack_s, $offset, $count, $replace, $escape = true) {
			if ($count <= 0) {
				$len  = count($stack);
			} else {
				$len = min(count($stack), $offset+$count);
			}
	
			$text = '';
			for ($i=$offset; $i<$len; $i++) {
				if ($replace && $stack[$i] == 'wiki-word') {
					$text .= $this->createLink($stack_s[$i],'');
				} else if ($stack[$i] == 'html') {
					$text .= $stack_s[$i];
				} else if ($stack[$i] == 'literal') {
					$text .= '<!--[lit]-->' . $this->convertHTMLspecialChars($stack_s[$i]) . '<!--[/lit]-->';
				} else if ($replace && $stack[$i] == 'url') {
					@list($protocol, $address) = explode('://', $stack_s[$i]);
					$text .= '<a href="'.$this->convertHTMLspecialChars($stack_s[$i]).'" target="_blank">' . $this->convertHTMLspecialChars($stack_s[$i]) . "</a>"; 
				} else if ($replace && $stack[$i] == 'mailto') {
					// Add a marker to the mailto so that we can rebuild the wiki text
					$text .= '<!--[mailto]-->' 
							.  substr($this->convertHTMLspecialChars($stack_s[$i]), 7)
							. '<!--[/mailto]-->';
				} else if ($escape) {
					$text .= $this->convertHTMLspecialChars($stack_s[$i]);
				} else {
					$text .= $stack_s[$i];
				}
			}
	
			if ($len == count($stack)) {
				array_splice($stack,   $offset);
				array_splice($stack_s, $offset);
			} else {
				array_splice($stack,   $offset, $count);
				array_splice($stack_s, $offset, $count);
			}
	
			if ($escape) {
				$stack[] = 'html';
			} else {
				$stack[] = 'text';
			}
			$stack_s[] = $text;
			return array($stack, $stack_s);
		}


		// Parameters:	$uri			url, not escaped
		//				$descr			description, escaped
		//				$anchor			optional anchor ('#anchor')
		//				$attrs			attributes ( attr="value" )
		// Returns:		complete <a/> anchor tag
		// Description:	creates the anchor tag for the given uri and descr.
		//				when descr is empty then the anchor tag is generated from the uri.
		//
		private function createLink($uri, $descr, $anchor = '', $attrs = array()) {
			$uri = trim($uri);
			if (!empty($descr)) {
				$descr = trim($descr);
			}

			// Remove double quotes around the uri, some people do type them...
			if (strlen($uri) >= 2 && $uri{0} == '"' && $uri{strlen($uri)-1} == '"') {
				$uri = substr($uri, 1, -1);
			}
	
			$pre  = '';
			$post = '';

			if (!empty($attrs)) {
				$attrs = ' ' . implode(' ', $attrs);
			} else {
				$attrs = '';
			}
	
			// 1. Check if the uri is a complete one
			if (strncasecmp($uri, 'mailto:', 7) == 0) {
				// Add a marker to the mailto so that we can rebuild the wiki text
				$descr = trim($descr);
				if (!empty($descr)) {
					$descr = ' '.$descr;
				}
				$text   = '<!--[mailto]-->' 
						.  $this->convertHTMLspecialChars(substr($uri, 7)) . $this->convertHTMLspecialChars($descr)
						. '<!--[/mailto]-->';
		
				// Bail out!
				return $text;
			}
			else if (	strpos($uri, '/') === false  
					&&	!preg_match('/^[a-zA-Z0-9_\-]+\.[a-zA-Z]{2,4}/', $uri)
					&&	strncasecmp($uri, 'javascript:', 11) != 0)
			{
				// assume symbolic name
				if (empty($descr)) {
					// Bail Out: Make special runtime tag, we will need the title of the thing we are linking to...
					$pre  = '<!--[link ' . $this->convertHTMLspecialChars($uri) . $this->convertHTMLspecialChars($anchor) . $attrs . ']-->';
					$post = '<!--[/link]-->';
			
					$descr = $this->convertHTMLspecialChars($uri);
				}
		
				if (!empty($uri)) {
					$uri = "id.php/" . str_replace(' ', '%20', $uri);
				} else if (empty($anchor)) {
					$anchor = '#';
				}
			}
			else if (	!empty($uri)
				&&	strpos($uri, '://') === false
				&&	strncasecmp($uri, 'javascript:', 11) != 0
				&&	preg_match('/^[a-z]+(\.[a-z]+)(\.[a-z]+)+(\/.*)?$/', $uri))
			{
				// Make sure we have a protocol for our link, better for <a/> tags
				$uri = 'http://' . $uri;
			}
	
			// 2. Extract a description when we don't have one
			if (empty($descr) && strpos($uri, '://') !== false) {
				list($protocol, $col, $descr) = explode('://', $uri);
			}
	
			if (empty($descr)) {
				$descr = $uri;
			}
	
			if (isset($GLOBALS['any_acl'])) {
				$uri = $GLOBALS['any_acl']->filterUri($uri);
			}
			return $pre . '<a href="' . $this->convertHTMLspecialChars($uri) . $this->convertHTMLspecialChars($anchor) . '"' . $attrs . ' target="_blank">' . $descr . '</a>' . $post;
		}

		// Parameters:	$descr
		// Returns:		list($descr, $attrs)
		// Description:	splits any  attr="value" attributes from the given description
		//				returns the descr and the list of attributes
		//
		private function splitDescrAttrs($descr) {
			global $_attrs;

			$_attrs = array();
			$descr  = preg_replace_callback('/\s([a-zA-Z]+)=("|&quot;)(.*?)("|&quot;)/', array('self', 'collectAttributes'), ' '.$descr);
			return array(trim($descr), $_attrs);
		}


		// Helper function to collect all attributes from the descr
		public static function collectAttributes($match) {
			global $_attrs;
			global $any_acl;
	
			if (	$match[1] == 'target' 
				||	$match[1] == 'class'
				||	$any_acl->allowHtml())
			{
				$_attrs[] = $match[1] . '="' . $match[3] . '"';
				return '';
			} else {
				return $match[0];
			}
		}

		// Parameters:	$tok
		// Returns:		start token for $tok
		// Description:	returns the start token belonging to the inline token $tok
		//
		private function handleInlineStart($tok) {
			switch ($tok) {
			case '}}':
				return '{{';

			default:
				break;
			}
			return $tok;
		}

		// Parameters:	$tok
		//				$text
		// Returns:		html for text
		// Description:	surrounds text with the correct html tags for $tok
		//
		private function handleInlineHtml($tok, $text) {
			switch ($tok) {
			case '}}':
				$tag = 'tt';
				break;
			default:
				$tag = $tok;
				break;
			}	
			return "<$tag>$text</$tag>";
		}

		// Parameters:	$line
		//				$line_s
		//				$offset		(optional) start scanning at offset
		//				$end		(optional) stop at offset
		// Returns:		(line, line_s)
		// Description:	scans the line for WikiWords, when found then replaces them
		//				with HTML fragments for freelinks.
		//
		private function replaceWikiWords($line, $line_s, $offset = 0, $end = false) {
			if ($end === false) {
				$end = count($line);
			}
	
			for ($i = $offset; $i< $end; $i++) {
				if ($line[$i] == 'wiki-word') {
					$line[$i]   = 'html';
					$line_s[$i] = $this->createLink($line_s[$i], '');
				}
			}
	
			return array($line, $line_s);
		}

		// Parameters:	$text	the text containing 'attr="value"' pairs
		// Returns:		array with attr=>value pairs
		// Description:	parses the attributes of a tag
		//
		private function getAttrs($text) {
			$parts	= explode('="', trim($text));
			$last	= count($parts) - 1;
			$attrs	= array();
			$key	= false;
	
			foreach ($parts as $i => $val) {
				if ($i == 0) {
					$key = trim($val);
				} else {
					$pos 		 = strrpos($val, '"');
					$attrs[$key] = stripslashes(substr($val, 0, $pos));
					$key 		 = trim(substr($val, $pos+1));
				}
			}
			return $attrs;
		}

		// Parameters:	$html	html with a toc marker
		// Returns:		html with a table of contents
		// Description:	Inserts a table of contents into the html
		//
		private function getToc($html) {
			global $toc_nr;
			global $toc_base;
			global $toc;
	
			$pos = strpos($html, '<!--[[toc]]-->');
			if ($pos !== false) {
				$toc_base = abs(crc32(microtime(true).'-'.rand(0,100))); 
				$toc_nr   = 0;
		
				// 1. Find all <h[2-6]> tags for insertion in the table of contents, no h1 tags are inserted
				$html = preg_replace_callback('|(<h[2-6]>)(.*)(</h[2-6]>)|U', array('self', 'getTocAccum'), $html);

				// 2. Create the table of contents at the place of the toc tag
				$s = "<!--[toc]-->\n<ul class='wikitoc'>\n";
				foreach ($toc as $entry) {
					list($anchor, $level, $title) = $entry;
					$s .= "<li class='wikitoc$level'><a href='#$anchor'>$title</a></li>\n";
				}
				$s   .= "</ul>\n<!--[/toc]-->\n";
				$html = str_replace('<!--[[toc]]-->', $s, $html);
			}
			return $html;
		}


		public static function getTocAccum($ms) {
			global $toc_nr;
			global $toc_base;
			global $toc;
	
			$toc_nr++;
			$anchor = "$toc_base-$toc_nr";
			$toc[]  = array($anchor, $ms[1]{2}, $ms[2]);
			return $ms[1]."<a name='$anchor'></a>".$ms[2].$ms[3];
		}

		// Parameters:	$s			input string
		//				$options	tokenize options
		// Returns:		token stream
		// Description:	tokenizes the given wiki stream
		//
		//				options:
		//				- allow_wikiword		default false
		//				
		public function tokenizeText($s, $options = array()) {
			$s  		= $this->normalizeNewlines($s) . "\n\n";
			$i  		= 0;			// the offset of the scanner
			$line_offs	= 0;			// the token offset in the current line
			$len 		= strlen($s);	// the length of the input stream

			$tk			= array();		// the token list returned
			$tk_s		= array();		// token strings

			// Get the settings
			$allow_wikiword	= !empty($options['allow_wikiword']);
	
			// Translate the character stream into tokens, use the ending "\n" as a buffer.
			while ($i < $len-2) {
				$c		= $s{$i};
				$n_c	= $s{$i+1};
				$nn_c	= $s{$i+2};
		
				$line_offs++;

				switch ($c) {
				case "\n":
					if ($n_c == "\n") {
						while ($i < $len - 1 && $s{$i+1} == "\n") {
							$i++;
						}
						$tk[]   = "p";
						$tk_s[] = "\n\n";
					} else {
						$tk[]   = "newline";
						$tk_s[] = "\n";
					}
					$line_offs = 0;
					break;
		
				case ' ':
					if ($n_c == '_'	&& $nn_c == "\n") {
						$tk[]   = 'br';
						$tk_s[] = " _\n";
						$i   += 2;
					} else {
						$tok = ' ';
						while ($s{$i+1} == ' ') {
							$tok .= ' ';
							$i++;
						}
				
						if ($s{$i+1} == '_' &&	$s{$i+2} == "\n") {
							$tk[]   = 'br';
							$tk_s[] = " _\n";
							$i   += 2;
						} else {
							$tk[]   = ' ';
							$tk_s[] = $tok;
						}
					}
					break;
		
				case '`':
					if ($n_c == '`') {
						$j   = $i+2;
						$tok = '';
						while ($j < $len - 2 &&	($s{$j} != '`' || $s{$j+1} != '`')) {
							$tok .= $s{$j};
							$j++;
						}
						if ($s{$j} == '`' && $s{$j+1} == '`') {
							$tk[]   = 'literal';
							$tk_s[] = str_replace("\n", " ", $tok);
							$i      = $j+1;
						} else {
							$tk[]	= '`';
							$tk_s[] = '`';
						}
					} else {
						$tk[]	= '`';
						$tk_s[] = '`';
					}
					break;

				case '<':
					// Check for <html> on one line
					if ($line_offs == 1 &&	substr($s, $i, 7) == "<html>\n"
						&&	($end = strpos($s, "\n</html>\n", $i+5)) !== false)
					{
						$tk[]	= 'html';
						$tk_s[] = substr($s, $i+7, $end - ($i+6));
						$i		= $end + 8;
					}
					// Check for <code> on one line
					else if ($line_offs == 1
						&&	substr($s, $i, 7) == "<code>\n"
						&&	($end = strpos($s, "\n</code>\n", $i+5)) !== false)
					{
						$tk[]	= 'code';
						$tk_s[] = substr($s, $i+7, $end - ($i+6));
						$i		= $end + 8;
					}
					// Check for a <!-- ... --> block
					else if (	substr($s, $i, 4) == '<!--'
						&&	($end = strpos($s, '-->', $i+4)) !== false)
					{
						$tk[]	= 'comment';
						$tk_s[] = trim(substr($s, $i+4, $end - ($i+4)));
						$i		= $end + 2;
					} else {
						$tk[]	= '<';
						$tk_s[]	= '<';
					}
					break;
			
				case '/':
					if ($n_c == '/') {
						$tk[]	= "em";
						$tk_s[]	= "//";
						$i+=1;
					} else  {
						$tk[]   = $c;
						$tk_s[] = $c;
					}
					break;
				case '*':
					if ((count($tk) > 2) && ($tk[count($tk)-1] == " ") && in_array($tk[count($tk)-2], array("p", "newline"))) {
						$tk[]   = $c;
						$tk_s[] = $c;
					} else {			
						$tk[]	= "strong";
						$tk_s[]	= "*";
					}
					break;
				case ',':
					if ($n_c == ',') {
						$tk[]	= "sub";
						$tk_s[]	= ",,";
						$i+=1;
					} else  {
						$tk[]   = $c;
						$tk_s[] = $c;
					}
			
					break;
				case '_':
					$tk[]	= "em";
					$tk_s[]	= "_";
					break;
				case '~':
					if ($n_c == '~') {
						$tk[]	= "strike";
						$tk_s[]	= "~~";
						$i+=1;
					} else {
						$tk[]   = $c;
						$tk_s[] = $c;
					}
					break;
				case '^':
					$tk[]	= "sup";
					$tk_s[]	= "^";
					break;
				case '@':
				case '#':
				case '(':
				case ')':
				case '|':
				case '[':
				case ']':
				case '{':
				case '}':
					if ($c == '[' & $n_c == '[') {
						// check for block-level [[toc]]
						if (	$line_offs == 1
							&&	substr($s, $i, 8) == "[[toc]]\n")
						{
							$tk[]   = 'toc';
							$tk_s[] = '[[toc]]';
							$i     += 6;
						} else {
							$tk[]   = $c.$c;
							$tk_s[] = $c.$c;
							$i++;
						}
					} else if ($n_c == $c) {
						$tk[]   = $c.$c;
						$tk_s[] = $c.$c;
						$i++;
					} else {
						$tk[] 	= $c;
						$tk_s[]	= $c;
					}
					break;
		
				case '>':
					$tok = '>';
					while ($s{$i+1} == '>') {
						$tok .= '>';
						$i++;
					}
					$tk[]	= ">";
					$tk_s[]	= $tok;
					break;
			
				case '\'':
					if ($n_c == '\'' && $nn_c == '\'') {
						$tk[]	= "strong";
						$tk_s[]	= "'''";
						$i+=2;
					} else if ($n_c == '\'') {
						$tk[]	= "em";
						$tk_s[]	= "''";
						$i+=1;
					} else  {
						$tk[]   = $c;
						$tk_s[] = $c;
					}
					break;
			
				case ':':
					if ($n_c == '/' && $nn_c == '/') {
						$tk[]   = '://';
						$tk_s[] = '://';
						$i += 2;
					} else {
						$tk[]   = ':';
						$tk_s[] = ':';
					}
					break;
			
				default:
					$class	= $this->getCharacterClass($c);
					$tok	= $c;
					$j		= $i;
					while ($class == $this->getCharacterClass($s{$j+1}) && $j < $len - 2) {
						$j++;
						$tok .= $s{$j};
					}
			
					if ($class == 'word') {
						if (	(($tok == 'http' || $tok == 'https') && substr($s, $j+1, 3) == '://')
							||	($tok == 'mailto' && $s[$j+1] == ':'))
						{
							// http://  or   mailto: -- fetch till whitespace or one of "])|>"
							if ($tok == 'mailto') {
								$class = 'mailto';
							} else {
								$class = 'url';
							}
					
							while (strpos("\n\t |[](){}<>\"'", $s{$j+1}) === false) {
								$j++;
								$tok .= $s{$j};
							}
						} else if ($allow_wikiword
							&&	$c >= 'A' 
							&&	$c <= 'Z'
							&&	preg_match('/^[A-Z][a-z0-9_]+[A-Z][a-zA-Z0-9_]*$/', $tok))
						{
							$class = "wiki-word";
						}
					}
					$tk[]	= $class;
					$tk_s[]	= $tok;
			
					$i = $j;
					break;
				}
				$i++;
			}
	
			$tk[]   = 'end';
			$tk_s[] = '';
	
			return array($tk, $tk_s);
		}

		// Parameters:	$c		character
		// Returns:		the class of the character
		// Description:	classifies a character as to belong to a wiki token group
		//
		private function getCharacterClass($c) {
			switch ($c) {
			case '[':	return $c;
			case ']':	return $c;
			case '*':	return $c;
			case ':':	return $c;
			case '#':	return $c;
			case '\'':	return $c;
			case '/':	return $c;
			case '|':	return $c;
			case '+':	return $c;
			case '-':	return $c;
			case '@':	return $c;
			case ':':	return $c;
			case '^':	return $c;
			case '>':	return $c;
			case '<':	return $c;
			case ',':	return $c;
			case '_':	return $c;
			case '=':	return $c;
			case '"':	return $c;
			case '{':	return $c;
			case '}':	return $c;
			case '(':	return $c;
			case ')':	return $c;
			case '~':	return $c;
			case ' ':	
			case "\n":
						return 'ws';
			default:
				return 'word';
			}
		}

		// Parameters:	$s		input string
		// Returns:		string with normalized newlines
		// Description:	translates the newlines in the string to unix style
		//				concatenates lines ending with a '\'
		//
		public function normalizeNewlines($s) {
			$s = str_replace("\r\n", 	"\n", 	$s);
			$s = str_replace("\r", 		"\n", 	$s);
			$s = str_replace("\\\n", 	" ", 	$s);
			
			//$s = str_replace("\n\n\n", 	"\n\n", $s);
			$s = str_replace("\t",		"    ", $s);
			//$s = trim($s) . "\n\n";
			return $s;
		}
	
	}

?>
