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

	/**
	 * Component to put button (html allowed) in forms
	 */
	class FormRadioButtonComponent extends FormContainer {

		var $name;
		var $focus;
		var $selected;
		var $value;
		var $itemID;

		function FormRadioButtonComponent($title, $description, $name, $selected = true) {
			$this->FormContainer($title, $description, "RadioButtonComponent");

			$this->name = $name;
			$this->selected = $selected;
			$this->rowClass = "radiobutton";
			$this->value = $title;
			$this->itemID = uniqID("rgo");

		}

		function setComponent($component) {
			parent::setComponent($component);
		}

		function getInput() {
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

			$result = '<tr><td>'."\n";
			$result .= sprintf(
				'<input class="radio" value="%s"%s name="%s" type="radio" tabindex="%s" %s%s%s/></td><td>'."\n",

				$this->value,
				($this->isSelected()) ? " checked=\"checked\"" : "",
				$this->name,
				$this->form->getTabIndex(),
				(strlen($this->focus) > 0) ? "onfocus=\"".$this->focus."\" onclick=\"".$this->focus."\" " : "",
				($this->isDisabled()) ? "disabled=\"disabled\" " : "",
				' onchange="'.$onChangeString.'"'
			);
			$this->form->increaseTabIndex();

			if($this->hasComponents()) {
				$component = $this->getComponent(0);
				$component->onFocus("componentFocus".$this->itemID."();");
				$result .= " ".$component->getInput();
				//$this->form->increaseTabIndex();
			}
			$result .= '</td></tr>'."\n";

			if($this->hasComponents()) {
				$component = $this->getComponent(0);

				$script = new Javascript();
				$script->startFunction("componentFocus".$this->itemID, array());
				$script->addline("var form = document.getElementById('".$this->form->id."');");
				$script->addline("for (i=0;i<form.".$this->name.".length;i++) {");
				$script->addline("if (form.".$this->name."[i].value == '".$this->value."')");
				$script->addline("form.".$this->name."[i].checked = true;");
				$script->addline("}");
				$script->endBlock();

				$this->attachScript($script);
			}

			return $result;

		}

		function isSelected() {
			$selected = $this->selected;
			if ($this->form->hasValue($this->name, $this->rowClass)) {
				$selected = ($this->form->getValue($this->name, $this->rowClass) == $this->value);
			}
			return $selected;
		}

	}
?>
