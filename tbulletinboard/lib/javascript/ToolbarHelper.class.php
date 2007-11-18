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


	global $libraryClassDir;
	require_once($libraryClassDir . "javascript/Javascript.class.php");
	require_once($libraryClassDir . "Language.class.php");

	/**
	 * Generates javascripts for toolbars
	 */
	class ToolbarHelper {

		var $selectFunction;
		var $itemName;
		var $itemProperties;
		var $script;
		var $constants;
		var $constantValues;

		/**
		 * Creates an helper instance to create redirect urls for a toolbar button
		 *@param string name of the function that gets the selected item ID
		 *@param string name of the item selected eg. "item"
		 */
		function ToolbarHelper($selectFunction, $itemName) {
			$this->selectFunction = $selectFunction;
			$this->itemName = $itemName;
			$this->itemProperties = array();
			$this->script = new Javascript();
			$this->constants = array();
			$this->constantValues = array();
		}

		function setRecordProperties() {
			$this->itemProperties = func_get_args();
		}

		function defineConstants($name, $values) {
			$this->constants[$name] = $values;
		}

		function addConstantValues($name, $value, $values) {
			$this->constantValues[$name][] = array("value" => $value, "consts" => $values);
		}

		function addRecordPopup($funcName, $popupUrl, $width, $height, $scrollbars=false, $conditions="", $noConditionMessage="", $mustHaveSelected=true) {
			global $language;
			$this->script->startFunction($funcName, array());
			if ((count($this->itemProperties) > 0) && ($mustHaveSelected))
				$this->script->addLine(sprintf("if (glob_%s == null) { alertML('".$language->getSentence("library", 0, "Selecteer een %s.")."'); return; }",
					$this->itemProperties[0], $this->itemName));
			if (strLen($conditions) > 0)
				$this->script->addLine(sprintf("if (%s) {", $this->fixParameters($conditions)));
			$this->script->popupWindow($this->fixParameters($popupUrl, "'+%s+'"), $width, $height, $funcName);
			if (strLen($conditions) > 0) {
				if (strLen($noConditionMessage) > 0) {
					$this->script->addLine("} else {");
					$this->script->alert($this->fixParameters($noConditionMessage, '"+%s+"'));
				}
				$this->script->endBlock();
			}
			$this->script->endBlock();
		}

		function addPopup($funcName, $popupUrl, $width, $height, $scrollbars=false, $conditions="", $noConditionMessage="") {
			$this->addRecordPopup($funcName, $popupUrl, $width, $height, $scrollbars, $conditions, $noConditionMessage, false);
		}

		function addRecordRedirect($funcName, $redirectUrl, $conditions="", $frame=array(), $noConditionMessage="") {
			global $language;
			$this->script->startFunction($funcName, array());
			if (count($this->itemProperties) > 0)
				$this->script->addLine(sprintf("if (glob_%s == null) { alertML('".$language->getSentence("library", 0, "Selecteer een %s.")."'); return; }",
					$this->itemProperties[0],	$this->itemName));
			if (strLen($conditions) > 0)
				$this->script->addLine(sprintf("if (%s) {", $this->fixParameters($conditions)));
			$url = $this->fixParameters($redirectUrl, "'+%s+'");
			$this->script->addLine("var openUrl = htmlToText('".$url."');");
			$this->script->addLine("var isPopup = openUrl.indexOf('js_class_popupWindow');");
			$this->script->addLine("if (isPopup == 0) {");
			$this->script->addLine("eval(openUrl);");
			$this->script->addLine("} else");
			$this->script->loadFrame($url, $frame);

			if (strLen($conditions) > 0) {
				if ($noConditionMessage != "") {
					$this->script->addLine("} else {");
					$this->script->alert($this->fixParameters($noConditionMessage, '"+%s+"'));
				}
				$this->script->endBlock();
			}
			$this->script->endBlock();
		}

		function addRedirect($funcName, $redirectUrl, $frame=array()) {
			$this->script->startFunction($funcName, array());
			$url = $this->fixParameters($redirectUrl, "'+%s+'");
			$this->script->loadFrame($url, $frame);
			$this->script->endBlock();
		}

		function addRecordDelete($funcName, $itemName, $openUrl, $frame = array(), $conditions="", $noConditionMessage="", $extraWarning="") {
			global $language;
			$this->addRecordActionConfirm($funcName, $itemName, $language->getSentence("library", 13, "verwijderen"), $openUrl, $frame, $conditions, $noConditionMessage, $extraWarning);
		}

		function addRecordActionConfirm($funcName, $itemName, $action, $openUrl, $frame = array(), $conditions="", $noConditionMessage="", $extraWarning="") {
			global $language;
			$this->script->startFunction($funcName, array());
			if (count($this->itemProperties) > 0)
				$this->script->addLine(sprintf("if (glob_%s == null) { alertML('".$language->getSentence("library", 0, "Selecteer een %s.")."'); return; }",
					$this->itemProperties[0],	$this->itemName));
			if (strLen($conditions) > 0)
				$this->script->addLine(sprintf("if (%s) {", $this->fixParameters($conditions)));
			$this->script->addLine("if (confirmML('".sprintf($language->getSentence("library", 1, "Weet u zeker dat u %2\$s wilt %1\$s?\\n%3\$s"),
				$this->fixParameters(addSlashes($action), "'+%s+'"),
				$this->fixParameters(addSlashes($itemName), "'+%s+'"),
				$this->fixParameters(addSlashes($extraWarning), "'+%s+'"))."')) {");
			$this->script->loadFrame($this->fixParameters($openUrl, "'+%s+'"), $frame);
			$this->script->endBlock();
			if (strLen($conditions) > 0) {
				if ($noConditionMessage != "") {
					$this->script->addLine("} else {");
					$this->script->alert($this->fixParameters($noConditionMessage, '"+%s+"'));
				}
				$this->script->endBlock();
			}
			$this->script->endBlock();
		}

		function addScriptRowSelectCheck(&$script) {
			global $language;
			if (count($this->itemProperties) > 0)
				$script->addLine(sprintf("if (glob_%s == null) { alertML('".$language->getSentence("library", 0, "Selecteer een %s.")."'); return; }",
				$this->itemProperties[0],	$this->itemName));
		}

		function addScriptLine(&$script, $line) {
			$script->addLine($this->fixParameters($line));
		}

		function addRecordActionConfirmPopup($funcName, $itemName, $action, $openUrl, $width, $height, $scrollbars=false) {
			global $language;
			$this->script->startFunction($funcName, array());
			if (count($this->itemProperties) > 0)
				$this->script->addLine(sprintf("if (glob_%s == null) { alertML('".$language->getSentence("library", 0, "Selecteer een %s.")."'); return; }",
					$this->itemProperties[0],	$this->itemName));
			$this->script->addLine("if (confirmML('".sprintf($language->getSentence("library", 1, "Weet u zeker dat u %s wilt %s?"),
				$this->fixParameters(addSlashes($itemName), "'+%s+'"), $action)."')) {");

			$this->script->popupWindow($this->fixParameters($openUrl, "'+%s+'"), $width, $height, $funcName);
			//$this->script->loadFrame($this->fixParameters($openUrl, "'+%s+'"), $frame);
			$this->script->endBlock();
			$this->script->endBlock();
		}


		function getJavascript($selectFunction = true) {
			// write the 'selectRecord' function
			if ($selectFunction) {
				reset($this->itemProperties);
				foreach($this->itemProperties as $property) $this->script->addLine(sprintf("var glob_%s = null;", $property));
				reset($this->constants);
				foreach($this->constants as $constant => $fields) {
					foreach($fields as $variable => $defaultValue) {
 						$this->script->addLine(sprintf("var glob_%s = null;", $variable));
					}
				}
				$this->script->startFunction($this->selectFunction, $this->itemProperties);
				reset($this->itemProperties);
				foreach($this->itemProperties as $property) $this->script->addLine(sprintf("glob_%1\$s = %1\$s;", $property));

				foreach($this->constants as $constant => $fields) {
					$this->script->addLine("switch (".$constant.") {");

					foreach($this->constantValues[$constant] as $constValues) {
						$this->script->addLine("case '".$constValues['value']."':");
						foreach($constValues['consts'] as $variable => $value) {
	 						$this->script->addLine(sprintf("glob_%s = %s;", $variable, $this->fixParameters($value)));
						}
						$this->script->addLine("break;");
					}

					$this->script->addLine("default:");
					foreach($fields as $variable => $defaultValue) {
 						$this->script->addLine(sprintf("glob_%s = %s;", $variable, $this->fixParameters($defaultValue)));
					}
					$this->script->addLine("break;");
					$this->script->addLine("}");
				}

				$this->script->endBlock();
			}
			return $this->script->toString();
		}

		function fixParameters($text, $encapsulate="%s") {
			reset($this->itemProperties);
			while (list($index, $property) = each($this->itemProperties)) {
				$text = str_replace("%".$property."%", sprintf($encapsulate, "glob_".$property), $text);
			}
			foreach($this->constants as $constant => $fields) {
				foreach($fields as $variable => $defaultValue) {
					$text = str_replace("%".$variable."%", sprintf($encapsulate, "glob_".$variable), $text);
				}
			}

			return $text;
		}
	}

?>
