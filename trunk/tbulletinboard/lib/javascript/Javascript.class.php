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

	class Javascript {

		var $scriptLines;
		var $currentObject;
		var $activeFunctionList = array();
		var $activeFunctionInit = -1;
		var $tabLevel;

		function Javascript() {
			$this->clear();
		}

		function clear() {
			$this->scriptLines = array();
			$this->tabLevel = 0;
			$this->currentObject = null;
		}

		function hasLines() {
			return (count($this->scriptLines) > 0);
		}

		/**
		* 09-09-05, changeParams added by Guido
		**/
		function loadPage($url, $changeParams=array()) {
			if (count($changeParams) == 0) {
				$this->addLine(sprintf("document.location.href='%s';", $url));
			} else {
				$this->addLine("var refUrl = '".$url."';");
				foreach($changeParams as $paramName => $paramValue) {
					$this->addLine("refUrl = setParameterInURL(refUrl, '".$paramName."', '".urlencode($paramValue)."');");
				}
				$this->addLine("document.location.href = refUrl;");
			}
		}

		function closeWindow() {
			$this->addLine("window.open('','_parent','');");
			$this->addLine('window.close();');
		}

		function resizeWindow($width, $height) {
			$this->addLine('window.resizeTo('.$width.', '.$height.');');
		}

		function resizeWindowCenter($width, $height) {
			$this->resizeWindow($width, $height);
			$this->addLine('window.moveTo((window.screen.availWidth - '.$width.') / 2, (window.screen.availHeight - '.$height.') / 2);');
		}

		function loadOpenerFrame($url, $frames=array()) {
			if (count($frames) == 0)
				$this->addLine(sprintf("window.opener.location.href = '%s';", $url));
			else $this->addLine(sprintf("window.opener.top.frames['%s'].location.href = '%s';", implode("'].frames['", $frames), $url));
		}

		function loadFrame($url, $frames=array()) {
			if (count($frames) == 0) {
				$this->loadPage($url);
				return;
			}
			if ((count($frames) == 1) && ($frames[0] == 'top'))
				$this->addLine(sprintf("top.location.href = '%s';", implode("'].frames['", $frames), $url));
			else $this->addLine(sprintf("top.frames['%s'].location.href = '%s';", implode("'].frames['", $frames), $url));
		}

		function popupWindow($url, $width, $height, $name, $address = false, $leftPos = -1, $topPos = -1, $scrollbar=1, $resizable =1) {
			$line = sprintf("js_class_popupWindow('%s', %d, %d, '%s', %d, %d, %d, %d, %d);",
				$url, $width, $height, $name, ($address) ? 1 : 0, $leftPos, $topPos, $scrollbar, $resizable);
			$this->addLine($line);
			return $line;
		}

		function popupWindowJSUrl($url, $width, $height, $name, $address = false, $leftPos = -1, $topPos = -1, $scrollbar=1, $resizable =1) {
			$line = sprintf("js_class_popupWindow(%s, %d, %d, '%s', %d, %d, %d, %d, %d);",
				$url, $width, $height, $name, ($address) ? 1 : 0, $leftPos, $topPos, $scrollbar, $resizable);
			$this->addLine($line);
			return $line;
		}

		/**
		* 09-09-05, changeParams added by Guido
		**/
		function refreshFrame($frames=array(), $changeParams=array()) {
			if (count($frames) == 0) {
				if (count($changeParams) == 0) {
					$this->addLine('document.location.href = document.location.href;');
				} else {
					$this->addLine("var refUrl = document.location.href;");
					foreach($changeParams as $paramName => $paramValue) {
						$this->addLine("refUrl = setParameterInURL(refUrl, '".$paramName."', '".$paramValue."');");
					}
					$this->addLine("document.location.href = refUrl;");
				}
			} else if ((count($frames) == 1) && ($frames[0] == 'top')) {
				if (count($changeParams) == 0) {
					$this->addLine("top.document.location.href = top.document.location.href;");
				} else {
					$this->addLine("var refUrl = top.document.location.href;");
					foreach($changeParams as $paramName => $paramValue) {
						$this->addLine("refUrl = setParameterInURL(refUrl, '".$paramName."', '".$paramValue."');");
					}
					$this->addLine("top.document.location.href = refUrl;");
				}
			} else {
				if (count($changeParams) == 0) {
					$this->addLine(sprintf("top.frames['%s'].location.href = top.frames['%s'].location.href;", implode("'].frames['", $frames), implode("'].frames['", $frames)));
				} else {
					$frameString = implode("'].frames['", $frames);
					$this->addLine("var refUrl = top.frames['".$frameString."'].location.href;");
					foreach($changeParams as $paramName => $paramValue) {
						$this->addLine("refUrl = setParameterInURL(refUrl, '".$paramName."', '".$paramValue."');");
					}
					$this->addLine("top.frames['".$frameString."'].location.href = refUrl;");
				}
			}
		}

		function addXMLReader($loadName, $varName, $updateFunction, $extraParameters = array()) {
			$this->addLine("// global request and XML document objects");
			if ($varName != "") {
				$internVar = $varName;
			} else {
				$internVar = uniqID("req");
			}
			$this->addLine("var $internVar;");

			$params = array("url");
			$extraParams = "";
			if (count($extraParameters) > 0) {
				$params = array_merge($params, $extraParameters);
				$passTroughParams = implode(", ", $extraParameters);
				$declareParams = array();
				foreach ($extraParameters as $param) {
					$declareParams[] = uniqID("pa");
				}
				$extraParams = ", ".implode(", ", $declareParams);
				$this->addLine("var ".implode(", ", $declareParams).";");
			}

			$this->startFunction($loadName, $params);
			for($i = 0; $i < count($extraParameters); $i++) {
				$this->addLine($declareParams[$i]." = ".$extraParameters[$i].";");
			}
			$this->addLine("if (window.XMLHttpRequest) {");
			$this->addLine($internVar." = new XMLHttpRequest();");
			$this->addLine("} else if (window.ActiveXObject) {");
			$this->addLine($internVar." = new ActiveXObject(\"Microsoft.XMLHTTP\");");
			$this->addLine("}");
			$this->addLine($internVar.".onreadystatechange = processReqChange;");
			//$this->addLine($internVar.".onreadystatechange = 'processReqChange(".$passTroughParams.")';");
			$this->addLine($internVar.".open('GET', url, true);");
			$this->addLine($internVar.".send(null);");
			$this->endBlock();


			$this->startFunction("processReqChange", array());
			$this->addLine("// only if req shows \"loaded\"");
			$this->addLine("if (".$internVar.".readyState == 4) {");
			$this->addLine("// only if \"OK\"");
			$this->addLine("if (".$internVar.".status == 200) {");
			$this->addLine($updateFunction."(".$internVar.$extraParams.");");
			$this->addLine("} else {");
			$this->addLine("alertML(\"There was a problem retrieving the XML data:\\n\" + ".$internVar.".statusText);");
			$this->addLine("}");
			$this->addLine("}");
			$this->endBlock();
		}

		function addFormatNumber($functionName) {
			$this->startFunction($functionName, array("num", "decimalNum", "bolLeadingZero", "bolParens"));
			$this->addLine("/* IN - num:            the number to be formatted");
			$this->addLine("    decimalNum:     the number of decimals after the digit");
			$this->addLine("    bolLeadingZero: true / false to use leading zero");
			$this->addLine("    bolParens:      true / false to use parenthesis for - num");
			$this->addLine("   RETVAL - formatted number");
			$this->addLine("*/");
			$this->addLine("var tmpNum = num;");
			$this->addLine("// Return the right number of decimal places");
			$this->addLine("tmpNum *= Math.pow(10,decimalNum);");
			$this->addLine("tmpNum = Math.floor(tmpNum);");
			$this->addLine("tmpNum /= Math.pow(10,decimalNum);");
			$this->addLine("var tmpStr = new String(tmpNum);");

			$this->addLine("// See if we need to hack off a leading zero or not");
			$this->addLine("if (!bolLeadingZero && num < 1 && num > -1 && num !=0)");
			$this->addLine("if (num > 0) tmpStr = tmpStr.substring(1,tmpStr.length);");
			$this->addLine("else // Take out the minus sign out (start at 2)");
			$this->addLine("tmpStr = "-" + tmpStr.substring(2,tmpStr.length);");
			$this->addLine("// See if we need to put parenthesis around the number");
			$this->addLine("if (bolParens && num < 0)");
			$this->addLine("tmpStr = \"(\" + tmpStr.substring(1,tmpStr.length) + \")\";");

			$this->addLine("return tmpStr;");
			$this->endBlock();
		}

		function addEventAttacher($functionName) {
			$this->startFunction($functionName, array("elementObj", "eventName", "eventHandlerFunctionName"));
			$this->addLine("if (elementObj.addEventListener) { // Non-IE browsers");
			$this->addLine("elementObj.addEventListener(eventName, eventHandlerFunctionName, false);");
			$this->addLine("} else if (elementObj.attachEvent) { // IE 6+");
			$this->addLine("elementObj.attachEvent('on' + eventName, eventHandlerFunctionName);");
			$this->addLine("} else { // Older browsers");
			$this->addLine("var currentEventHandler = elementObj['on' + eventName];");
			$this->addLine("if (currentEventHandler == null) {");
			$this->addLine("elementObj['on' + eventName] = eventHandlerFunctionName;");
			$this->addLine("} else {");
			$this->addLine("elementObj['on' + eventName] = function(e) { currentEventHandler(e); eventHandlerFunctionName(e); }");
			$this->addLine("}");
			$this->addLine("}");
			$this->endBlock();
		}

		function addTrimFunction($functionName) {
			$this->startFunction($functionName, array("text"));
			$this->addLine("if(text.length < 1) {");
			$this->addLine("return \"\";");
			$this->addLine("}");
			$this->addLine("text = r".$functionName."(text);");
			$this->addLine("text = l".$functionName."(text);");
			$this->addLine("if(text==\"\"){");
			$this->addLine("return \"\";");
			$this->addLine("}");
			$this->addLine("else{");
			$this->addLine("return text;");
			$this->addLine("}");
			$this->endBlock();

			$this->startFunction("r".$functionName, array("text"));
			$this->addLine("var w_space = String.fromCharCode(32);");
			$this->addLine("var v_length = text.length;");
			$this->addLine("var strTemp = \"\";");
			$this->addLine("if(v_length < 0){");
			$this->addLine("return \"\";");
			$this->addLine("}");
			$this->addLine("var iTemp = v_length -1;");
			$this->addLine("while(iTemp > -1){");
			$this->addLine("if(text.charAt(iTemp) == w_space){");
			$this->addLine("}");
			$this->addLine("else{");
			$this->addLine("strTemp = text.substring(0,iTemp + 1);");
			$this->addLine("break;");
			$this->addLine("}");
			$this->addLine("iTemp = iTemp-1;");
			$this->addLine("} //End While");
			$this->addLine("return strTemp;");
			$this->endBlock();

			$this->startFunction("l".$functionName, array("text"));
			$this->addLine("var w_space = String.fromCharCode(32);");
			$this->addLine("if(v_length < 1){");
			$this->addLine("return\"\";");
			$this->addLine("}");
			$this->addLine("var v_length = text.length;");
			$this->addLine("var strTemp = \"\";");
			$this->addLine("var iTemp = 0;");
			$this->addLine("while(iTemp < v_length){");
			$this->addLine("if(text.charAt(iTemp) == w_space){");
			$this->addLine("}");
			$this->addLine("else{");
			$this->addLine("strTemp = text.substring(iTemp,v_length);");
			$this->addLine("break;");
			$this->addLine("}");
			$this->addLine("iTemp = iTemp + 1;");
			$this->addLine("} //End While");
			$this->addLine("return strTemp;");
			$this->endBlock();

		}

		function refreshOpenerFrame($frames=array(), $changeParams=array()) {
			/* Remarked by Guido 22-01-05, adding multiple frame support
			$this->refreshFrame("window.opener.document");*/
			if (count($frames) == 0) {
				if (count($changeParams) == 0)
					$this->addLine("window.opener.location.href = window.opener.location.href;");
				else {
					$this->addLine("var refUrl = window.opener.location.href;");
					foreach($changeParams as $paramName => $paramValue) {
						$this->addLine("refUrl = setParameterInURL(refUrl, '".$paramName."', '".$paramValue."');");
					}
					$this->addLine("window.opener.location.href = refUrl;");
				}
				return;
			}
			if ((count($frames) == 1) && ($frames[0] == 'top'))
				$this->addLine("window.opener.top.location.href = window.opener.top.location.href;");
			else $this->addLine(sprintf("window.opener.top.frames['%s'].location.href = window.opener.top.frames['%s'].location.href;", implode("'].frames['", $frames), implode("'].frames['", $frames)));
		}

		function itemSelected($varName, $displayName) {
			global $language;
			$this->addLine(sprintf("if (glob_%s == null) { alertML('".$language->getSentence("library", 0, "Selecteer een %s.")."'); return; }",
				$varName, $displayName));
		}

		function startFunction($name, $parameters = array()) {
			$this->addLine("");
			if ($this->currentObject == null) {
				$this->addLine(sprintf("function %s(%s) {", $name, implode(", ", $parameters)));
			} else {
				$this->activeFunctionList[] = $name;
				$this->addLine(sprintf("%s.%s = function(%s) {", $this->currentObject, $name, implode(", ", $parameters)));
			}
		}

		function endBlock() {
			$this->addLine('}');
		}

		function startObject($objectName, $parameters = array()) {
			$this->startFunction($objectName, $parameters);
			$this->currentObject = $objectName;
			$this->activeFunctionList = array();
			$this->activeFunctionInit = count($this->scriptLines);
			$this->addLine("{%functiondeclarations%}");
		}

		function endObject() {
			$initStr = "";
			foreach($this->activeFunctionList as $funcInit) {
				$initStr .= sprintf('this.%2$s = %1$s.%2$s;'."\n", $this->currentObject, $funcInit);
			}
			$this->scriptLines[$this->activeFunctionInit] = str_replace("{%functiondeclarations%}", $initStr, $this->scriptLines[$this->activeFunctionInit]);
			$this->scriptLines = explode("\n", implode("\n", $this->scriptLines));
			$this->currentObject = null;
			$this->addLine("");
		}

		/**
		* Adds try-statement, requires a endBlock or addCatch to end
		**/
		function addTry() {
			$this->addLine('try {');
		}

		/**
		* Adds catch-statement, is the ending of addTry
		*@param boolean, close if the catch block also has to be closed
		**/
		function addCatch($close=true) {
			$this->addLine('} catch(e) {');
			if($close) $this->endBlock();
		}

		/**
		* Adds confirm if-statement, requires a endBlock to end
		**/
		function addConfirm($question) {
			$this->addLine('if(confirmML("'.addSlashes($question).'")) {');
		}

		/**
		* Print the Frame
		**/
		function printFrame($frames=array()) {
			if(count($frames) == 0) $this->addLine("framePrint(new Array());");
			else $this->addLine("framePrint(new Array('".implode("','",$frames)."'));");
		}

		function alert($text) {
			$this->addLine('alertML("'.str_replace("\"", "\\\"", $text).'");');
		}

		function addLine($scriptLine) {
			$this->scriptLines[] = $scriptLine;
		}

		function toString() {
			if (count($this->scriptLines) == 0) return "";

			$result = '<script type="text/javascript"><!--'."\n";
			$result .= '// compatibility with xhtml <![CDATA['."\n";
			for ($i = 0; $i < count($this->scriptLines); $i++) {
				$scriptLine = $this->scriptLines[$i];

				if ((subStr($scriptLine, 0, 1) == "}") && ($this->tabLevel > 0)) $this->tabLevel--;
				$line = "";
				for ($j = 0; $j < $this->tabLevel; $j++) $line .= "\t";
				$line .= $scriptLine;
				if (subStr_count($scriptLine, "{") > 0) {
					$lastOpenBracket = strrpos($scriptLine, "{");
					$lastCloseBracket = strrpos($scriptLine, "}");
					if (($lastCloseBracket === false) || ($lastCloseBracket < $lastOpenBracket)) $this->tabLevel++;
				}

				$result .= "\t".$line."\n";
			}
			$result .= '//]]> -->'."\n";
			$result .= '</script>'."\n";
			return $result;
		}

		function getScript() {
			print $this->toString();
		}

		function mergeScript($script) {
			$this->addLine("");
			$this->scriptLines = array_merge($this->scriptLines, $script->scriptLines);
		}

		function searchReplace($search, $replace) {
			for ($i = 0; $i < count($this->scriptLines); $i++) {
				$this->scriptLines[$i] = str_replace($search, $replace, $this->scriptLines[$i]);
			}
		}

		function pause($milliSeconds) {
			$this->addLine("pause(".$milliSeconds.");");
		}

	}

?>
