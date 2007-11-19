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
	require_once($libraryClassDir."Form.class.php");

	/**
	 * Component to put plain text (html allowed) in forms
	 */
	class FormRadioButton extends FormComponent {

		var $value;
		var $focus;
		var $enabled;
		var $name;
		var $selected;

		function FormRadioButton($title, $description, $name, $value, $selected=true, $focus="", $enabled=true) {
			$identifier = "RadioButton".$value;
			$identifier = str_replace("-", "_", $identifier);

			$this->FormComponent($title, $description, $identifier);
			$this->value = $value;
			$this->focus = $focus;
			$this->setDisabled(!$enabled);
			$this->enabled = $enabled;
			$this->selected = $selected;
			$this->name = $name;
			$this->rowClass = "radiobutton";
		}

		function isSelected() {
			$selected = $this->selected;
			if ($this->form->hasValue($this->name, $this->rowClass)) {
				$selected = ($this->form->getValue($this->name, $this->rowClass) == $this->value);
			}
			return $selected;
		}

		function setSelected($selected) {
			$this->selected = $selected;
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
				'<input class="radio" %svalue="%s"%s name="%s" type="radio" tabindex="%s" %s%s%s/>'."\n",
				(strlen($radioID) > 0) ? 'id="'.$radioID.'"' : "",
				$this->value,
				($this->isSelected()) ? " checked=\"checked\"" : "",
				$this->name,
				$this->form->getTabIndex(),
				(strlen($this->focus) > 0) ? " onfocus=\"".$this->focus."\" onclick=\"".$this->focus."\" " : "",
				//(!$this->enabled) ? "disabled=\"disabled\" " : ""
				($this->isDisabled()) ? " disabled=\"disabled\" " : "",
				' onchange="'.$onChangeString.'"'
			);
			$result .= '</span>'."\n";
			$this->form->increaseTabIndex();
			return $result;
		}

		function getJavascript() {
			return false;
		}

	}


?>
