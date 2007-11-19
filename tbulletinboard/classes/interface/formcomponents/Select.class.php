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
	importClass("interface.Form");
	importClass("util.Javascript");

	/**
	 * Component to put button (html allowed) in forms
	 */
	class FormSelect extends FormContainer {

		var $focus;
		//var $change; // replaced by onchange in FormComponent
		//var $enabled;
		var $rows;
		var $displayMode;
		var $value;
		var $prefix;
		var $postfix;
		var $multiple;
		var $itemID;
		var $minChoice;
		var $maxChoice;

		function FormSelect($title, $description, $name, $rows = 1, $change="", $focus="",
				$enabled=true, $prefix="", $postfix="", $multiple = false, $minChoice = 0, $maxChoice = 0) {

			$this->FormContainer($title, $description, $name);
			$this->onchange = $change;
			$this->focus = $focus;
			$this->multiple = $multiple;
			$this->minChoice = $minChoice;
			$this->maxChoice = $maxChoice;

			$this->disabled = !$enabled;
			$this->rows = $rows;
			$this->rowClass = "select";
			$this->displayMode = "default";
			$this->value = "";
			$this->prefix = $prefix;
			$this->postfix = $postfix;
			$this->itemID = "slct-".$name;
		}

		function setValue($value) {
			$this->value = $value;
		}

		function onFocus($jsCommand) {
			$this->focus = $jsCommand . " " . $this->focus;
		}

		function setSubSelectDisplayMode() {
			$this->displayMode = "subselect";
		}

		function getInput() {
			$result = "";
			$result .= $this->prefix . " ";
			switch($this->displayMode) {
				case 'subselect': $result .= $this->getSubSelectInput(); break;
				default: $result .= $this->getDefaultInput(); break;
			}
			$result .= " " . $this->postfix;
			return $result;
		}

		function hasValidValue($postData = null) {
			if ($this->multiple) {
				$optionsChoosen = array();
				if (isset($postData[$this->identifier])) $optionsChoosen = $postData[$this->identifier];

				if (($this->minChoice > 0) && (count($optionsChoosen) < $this->minChoice) && ($this->maxChoice == 0)) {
					$this->errorMessage = "Er dienen ";
					if (($this->maxChoice == 0) || ($this->maxChoice > $this->minChoice)) {
						$this->errorMessage .= "minstens ";
					}
					$this->errorMessage .= $this->minChoice . " opties gekozen te zijn";
					return false;
				}
				if (($this->maxChoice > 0) && (count($optionsChoosen) > $this->maxChoice) && ($this->minChoice == 0)) {
					$this->errorMessage = "Er dienen ";
					if (($this->minChoice == 0) || ($this->maxChoice > $this->minChoice)) {
						$this->errorMessage .= "maximaal ";
					}
					$this->errorMessage .= $this->maxChoice . " opties gekozen te zijn";
					return false;
				}
				if ((($this->maxChoice > 0) && (count($optionsChoosen) > $this->maxChoice)) ||
						(($this->minChoice > 0) && (count($optionsChoosen) < $this->minChoice))) {

					$this->errorMessage = "Er dienen ";
					if ($this->maxChoice - $this->minChoice > 0) {
						$this->errorMessage .= "tussen de " . $this->minChoice . " en de ";
					}
					$this->errorMessage .= $this->maxChoice . " opties gekozen te zijn";
					return false;
				}

			}

			for($i = 0; $i < count($this->components); $i++) {
				if (!$this->components[$i]->hasValidValue()) {
					$this->errorMessage = $this->components[$i]->getErrorMessage();
					return false;
				}
			}
			return true;
		}

		function getDefaultValue() {
			if($this->hasComponents()) {
				$component =& $this->components[0];
				return $component->getDefaultValue();
			}
		}

		function hasValue($postData = null) {
			if ($this->hasForm()) {
				return $this->form->hasValue($this->identifier, $this->rowClass);
			}
			if (isSet($postData[$this->identifier])) {
				return ($postData[$this->identifier] != "");
			}
			return false;
		}

		function getSubSelectInput() {
			$onChangeString = $this->onchange;
			if(is_object($this->form)) {
				$onChangeScript = new JavaScript();
				$onChangeScript->startFunction("field".$this->form->id.$this->identifier."IsChanged", array("element"));
				$onChangeScript->addLine($this->identifier."changeGroup(element);");
				$onChangeScript->addLine("form".$this->form->id."IsChanged();");
				$onChangeScript->endBlock();
				$this->attachScript($onChangeScript);
				$onChangeString = "field".$this->form->id.$this->identifier."IsChanged(this);";
			}

			$result = '';
			$selectedValue = $this->value;
			if($this->hasForm())
				$selectedValue = $this->form->getValue($this->identifier,'');
			//print "Value:".$selectedValue;

			$hasSelection = false;
			if($this->hasComponents()) {
				for($i = 0; $i < $this->getComponentCount(); $i++) {
					$component =& $this->components[$i];
					$component->setSelected($selectedValue);
					if ($component->isSelected()) $hasSelection = true;
				}
			}
			if (!$hasSelection) {
				$selectedValue = $this->getDefaultValue();
			}


			$result .= sprintf('<input type="hidden" name="%s" value="%s" />', $this->identifier,
				$selectedValue);


			$result .= sprintf(
				'<select class="formselect" name="%s" %s%s%s%s id="%s" size="%s" />'."\n",
				$this->identifier."mainSel",
				($this->hasForm()) ? 'tabindex="'.$this->form->getTabIndex().'" ' : "",
				"onchange=\"".$onChangeString."\" ",
				(strlen($this->focus) > 0) ? "onfocus=\"".$this->focus."\" onclick=\"".$this->focus."\"" : "",
				($this->disabled) ? "disabled=\"disabled\"" : "",
				$this->itemID,
				1
			);
			if ($this->hasForm()) $this->form->increaseTabIndex();

			$subListSelects = "";
			$groupIDs = array();


			if($this->hasComponents()) {
				for($i = 0; $i < $this->getComponentCount(); $i++) {
					$component =& $this->components[$i];
					if ((strLen($selectedValue) > 0) ||
						($this->hasForm() && ($this->form->hasValue($this->identifier,''))))
							$component->setSelected($selectedValue);
					if (get_class($component) == "formoptiongroup") {
						$result .= $component->getSubSelectOption();
						//print ("Selectie: ".$selectedValue."<br />\n");
						//print ($component->isSelected()) ? "Geselecteerd" : "Niet geselecteerd";

						$subListSelects .= sprintf('<%1$s%2$s id="id%4$s">%3$s</%1$s>',
							"div", (($component->isSelected() || (($selectedValue == "") && ($i == 0))) ? '' : ' style="display: none;"'),
							$component->getSubSelectList($this->rows, $this->identifier."changeValue(element);", $this->focus, !$this->disabled),
							$component->getSubName()
						);
						$groupIDs[] = $component->getSubName();
					}
				}
			}
			$result .= '</select>'."\n";

			$script = new Javascript();
			$script->startFunction($this->identifier."changeGroup", array("element"));
			$script->addLine('var selectedGroup = element.options[element.selectedIndex].value;');
			$script->addLine('var groupIDs = new Array("'.implode('", "', $groupIDs).'");');
			$script->addLine('for (var i = 0; i < groupIDs.length; i++) {');
			$script->addLine("var selectorDiv = document.getElementById('id'+groupIDs[i]);");
			$script->addLine("if (groupIDs[i] == selectedGroup) {");
			$script->addLine("selectorDiv.style.display = '';");
			$script->addLine("var selector = document.getElementById(groupIDs[i]);");
			$script->addLine('var selectedValue = selector.options[selector.selectedIndex].value;');
			$script->addLine("element.form.".$this->identifier.".value = selectedValue;");
			$script->addLine('} else {');
			$script->addLine("selectorDiv.style.display = 'none';");
			$script->addLine('}');
			$script->addLine('}');
			$script->endBlock();

			$script->startFunction($this->identifier."changeValue", array("element"));
			$script->addLine('var selectedValue = element.options[element.selectedIndex].value;');
			$script->addLine("element.form.".$this->identifier.".value = selectedValue;");
			$script->endBlock();

			//$result .= $script->getScript();
			$result .= $script->toString();
			$result .= $subListSelects;
			return $result;
		}

		function getDefaultInput() {
			$onChangeString = $this->onchange;
			if(is_object($this->form)) {
				$onChangeScript = new JavaScript();
				$onChangeScript->startFunction("field".$this->form->id.$this->identifier."IsChanged", array("element"));
				if($this->onchange != "") $onChangeScript->addLine($this->onchange);
				$onChangeScript->addLine("form".$this->form->id."IsChanged();");
				$onChangeScript->endBlock();
				$this->attachScript($onChangeScript);
				$onChangeString = "field".$this->form->id.$this->identifier."IsChanged(this);";
			}

			$result = '';
			$selectedValue = $this->value;
			if($this->hasForm())
				$selectedValue = $this->form->getValue($this->identifier,'');
			$change = str_replace("%value%", "this.options[this.selectedIndex].value", $onChangeString);

			$result .= sprintf(
				'<select class="formselect" name="%s%s" %s%s%s%s%s id="%s" size="%s">'."\n",
				$this->identifier,
				($this->multiple) ? "[]" : "",
				($this->hasForm()) ? 'tabindex="'.$this->form->getTabIndex().'" ' : "",
				"onchange=\"".$onChangeString."\" ",
				(strlen($this->focus) > 0) ? "onfocus=\"".$this->focus."\" onclick=\"".$this->focus."\" " : "",
				($this->disabled) ? "disabled=\"disabled\" " : "",
				($this->multiple) ? "multiple=\"multiple\" " : "",
				$this->itemID,
				$this->rows
			);
			if ($this->hasForm()) $this->form->increaseTabIndex();

			if($this->hasComponents()) {
				for($i = 0; $i < $this->getComponentCount(); $i++) {
					$component =& $this->components[$i];
					if ($this->multiple) {
						if ((count($selectedValue) > 0) ||
							($this->hasForm() && ($this->form->hasValue($this->identifier,''))))
								$component->setSelected($selectedValue);
					} else {
						if ((strLen($selectedValue) > 0) ||
							($this->hasForm() && ($this->form->hasValue($this->identifier,''))))
								$component->setSelected($selectedValue);
					}
					$result .= $component->getInput();
				}
			}
			$result .= '</select>'."\n";
			return $result;
		}
	}

	/**
	 * Component to put plain text (html allowed) in forms
	 */
	class FormOption extends FormComponent {

		var $value;
		var $selected;

		function FormOption($title, $value, $selected=false) {
			$this->FormComponent($title, "", "Option".$value);
			$this->value = $value;
			$this->selected = $selected;
			$this->rowClass = "option";
		}

		function setSelected($selected=true) {
			if (getType($selected) == "array") {
				$this->selected = in_array($this->value, $selected);
			} else if (getType($selected) == "boolean") {
				$this->selected = $selected;
			} else {
				$this->selected = ($this->value == $selected);
			}
		}

		function isSelected() {
			return $this->selected;
		}

		function getDefaultValue() {
			return $this->value;
		}

		function getInput() {
			$result = sprintf(
				'<option class="formoption" value="%s"%s>%s</option>'."\n",
				$this->value,
				($this->selected) ? " selected=\"selected\"" : "",
				convertTextCharacters($this->title)
			);
			return $result;
		}
	}

	/**
	 * Component to put plain text (html allowed) in forms
	 */
	class FormOptionGroup extends FormContainer {

		var $subName;
		var $value;

		function FormOptionGroup($title) {
			$this->FormContainer($title, "", "Option".$title);
			$this->rowClass = "option";
			$this->subName = uniqID("optgrp");
			$this->value = "";
		}

		function setSelected($selected=true) {
			if (getType($selected) == "boolean") return;
			$this->value = $selected;
			for($i = 0; $i < $this->getComponentCount(); $i++) {
				$component =& $this->components[$i];
				$component->setSelected($selected);
			}
		}

		function getSubName() {
			return $this->subName;
		}

		function getDefaultValue() {
			if($this->hasComponents()) {
				$component =& $this->components[0];
				return $component->getDefaultValue();
			}
		}

		function isSelected() {
			for($i = 0; $i < $this->getComponentCount(); $i++) {
				$component =& $this->components[$i];
				if ($component->isSelected()) return true;
			}
			return false;
		}

		function getInput() {
			$result = sprintf(
				'<optgroup class="formoption" label="%s">%s</option>'."\n",
				$this->title,
				$this->title
			);
			for($i = 0; $i < $this->getComponentCount(); $i++) {
				$component =& $this->components[$i];
				$result .= $component->getInput();
			}
			$result .= "</optgroup>";
			return $result;
		}

		function getSubSelectOption() {
			$result = sprintf(
				'<option class="formoption" value="%s"%s>%s</option>'."\n",
				$this->subName,
				($this->isSelected()) ? " selected=\"selected\"" : "",
				$this->title
			);
			return $result;
		}

		function getSubSelectList($rows = 1, $change, $focus, $enabled) {
			$formSelect = new FormSelect("", "", $this->subName, $rows, $change, $focus, $enabled);
			$formSelect->setValue($this->value);
			for($i = 0; $i < $this->getComponentCount(); $i++) {
				$component =& $this->components[$i];
				$formSelect->addComponent($component);
			}
			$formSelect->setForm($this->form);
			$result = $formSelect->getInput();
			if ($formSelect->hasAttachedScript()) {
				$dataScript =& $formSelect->getAttachedScript();
				$result .= $dataScript->toString();
			}

			return $result;
		}
	}

?>
