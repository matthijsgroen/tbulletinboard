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
	 * Import the FormComponent superclass
	 */
	global $libraryClassDir;
	require_once($libraryClassDir."Form.class.php");
	require_once($libraryClassDir."javascript/Javascript.class.php");
	require_once($libraryClassDir."formcomponents/RadioButton.class.php");

	/**
	 * Component to put plain text (html allowed) in forms
	 */
	class FormRadioViewHideButton extends FormRadioButton {

		var $showMarkings;
		var $hideMarkings;
		var $showHideFunc;
		var $uniqueID;

		function FormRadioViewHideButton($title, $description, $name, $value, $selected=true, $focus="", $enabled=true, $showMarkings = array(), $hideMarkings = array()) {
			$this->FormRadioButton($title, $description, $name, $value, $selected, $focus, $enabled);
			$this->showMarkings = $showMarkings;
			$this->hideMarkings = $hideMarkings;
			$this->showHideFunc = uniqID("rbo");
			$this->uniqueID = uniqID("radf");
		}

		function setShowMarkings($markings) {
			$this->showMarkings = $markings;
		}

		function setHideMarkings($markings) {
			$this->hideMarkings = $markings;
		}

		function getHideMarkings() {
			return $this->hideMarkings;
		}

		function notifyAdding(&$container) {
			$this->showHideFunc = $container->getOptFuncName();
		}

		function getInput($radioID="") {
			$onChangeString = $this->onchange;
			if(is_object($this->form)) {
				$onChangeScript = new JavaScript();
				$onChangeScript->startFunction("field".$this->form->id.$this->identifier."IsChanged");
				if($this->onchange != "") $onChangeScript->addLine($this->onchange);
				$onChangeScript->addLine("form".$this->form->id."IsChanged();");
				$onChangeScript->endBlock();
				$this->attachScript($onChangeScript);
				$onChangeString = "field".$this->form->id.$this->identifier."IsChanged();";
			}
			
			$result = '<span class="radiobutton">'."\n";
			$result .= sprintf(
				'<input class="radio" %svalue="%s"%s name="%s" type="radio" tabindex="%s" %s %s %s/>'."\n",
				(strlen($radioID) > 0) ? 'id="'.$radioID.'"' : "",
				$this->value,
				($this->isSelected()) ? " checked=\"checked\"" : "",
				$this->name,
				$this->form->getTabIndex(),
				"onfocus=\"".$this->uniqueID."FocusClick()\" onclick=\"".$this->uniqueID."FocusClick()\" ",
				//(!$this->enabled) ? "disabled=\"disabled\" " : ""
				($this->isDisabled()) ? "disabled=\"disabled\" " : "",
				'onchange="'.$onChangeString.'"'
			);
			//	(strlen($this->focus) > 0) ? "onfocus=\"".$this->focus."\" onclick=\"".$this->focus."\" " :
			$result .= '</span>'."\n";
			$this->form->increaseTabIndex();

			$script = new Javascript();
			$script->startFunction($this->uniqueID."FocusClick", array());
			if (strLen($this->focus) > 0) $script->addLine($this->focus);
			$script->addLine($this->showHideFunc."();");
			$script->endBlock();

			$this->attachScript($script);

			return $result;
		}

		function getViewHideScript() {
			$script = new Javascript();

			$script->addLine("if (checkValue == '".$this->value."') {");
			if (isSet($this->hideMarkings)) {
				for ($x = 0; $x < count($this->hideMarkings); $x++) {
					$hideName = $this->hideMarkings[$x];
					$nrRows = $this->form->getMarkCount($hideName);
					$hideNames = array();
					for ($y = 0; $y < $nrRows; $y++) {
						$hideNames[] = 'row'.$hideName.$y;
					}
					$script->addLine("var hideElements = new Array('".implode("', '", $hideNames)."');");
					$script->addLine("for (var i = 0; i < hideElements.length; i++) {");
					$script->addLine("var item = document.getElementById(hideElements[i]);");
					$script->addLine("if (item != null) item.style.display = 'none';");
					$script->addLine("}");
				}
			}
			if (isSet($this->showMarkings)) {
				for ($x = 0; $x < count($this->showMarkings); $x++) {
					$showName = $this->showMarkings[$x];
					$nrRows = $this->form->getMarkCount($showName);
					$showNames = array();
					for ($y = 0; $y < $nrRows; $y++) {
						$showNames[] = 'row'.$showName.$y;
					}
					$script->addLine("var showElements = new Array('".implode("', '", $showNames)."');");
					$script->addLine("for (var i = 0; i < showElements.length; i++) {");
					$script->addLine("var item = document.getElementById(showElements[i]);");
					$script->addLine("if (item != null) item.style.display = '';");
					$script->addLine("}");
				}
			}
			$script->endBlock();

			return $script;
		}

	}

?>
