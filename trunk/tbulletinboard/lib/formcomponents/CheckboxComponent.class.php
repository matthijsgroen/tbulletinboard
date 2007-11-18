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
	 * Component to put button (html allowed) in forms
	 */


	class FormCheckboxComponent extends FormContainer {

		var $value;
		var $checked;
		var $change;
		var $itemID;
		var $ignorePostData;

		function FormCheckboxComponent($title, $description, $name, $checked = false) {
			$this->FormContainer($title, $description, $name);

			$this->name = $name;
			$this->rowClass = "radiobutton";
			$this->value = $title;
			$this->checked = $checked;
			$this->itemID = uniqID("rgo");
		}

		function setComponent($component) {
			parent::setComponent($component);
		}

		function getInput() {
			$onChangeString = $this->onchange;
			if(is_object($this->form)) {
				$onChangeScript = new JavaScript();
				
				$functionName = "field".$this->form->id.$this->identifier."IsChanged";
				
				// If identifier contains [], remove added by Guido on 23-02-06
				$functionName = str_replace("[]","",$functionName);
				
				$onChangeScript->startFunction($functionName);
				if($this->onchange != "") $onChangeScript->addLine($this->onchange);
				$onChangeScript->addLine("form".$this->form->id."IsChanged();");
				$onChangeScript->endBlock();
				$this->attachScript($onChangeScript);
				$onChangeString = $functionName."();";
			}


			$result = '';
			//$itemID = uniqID('chkbx');
			//$itemID = "chkbx-".$this->name;

			$selectedValue = $this->checked;

			if ((!$this->ignorePostData) && ($this->form->hasValue($this->identifier, "checkboxes"))) {
				$selectedValue = $this->form->getValue($this->identifier, "checkboxes", null, $this->value);
			}

			$result .= sprintf(
				'<table><tr><td><input type="checkbox" name="%s"%s tabindex="%s" %s %s id="%s" value="%s" /></td><td>',
				$this->identifier,
				($selectedValue) ? ' checked="checked"' : "",
				$this->form->getTabIndex(),
				//(($this->disabled) ? 'disabled="disabled"' : ''),
				(($this->isDisabled()) ? 'disabled="disabled"' : ''),
				"onchange=\"".$onChangeString."\" ",
				$this->itemID,
				$this->value
			) ;
			$this->form->increaseTabIndex();
			if($this->hasComponents()) {
				$component = $this->getComponent(0);
				$component->onFocus("componentFocus".$this->itemID."();");
				$result .= " ".$component->getInput();
				//$this->form->increaseTabIndex();
			}
			$result .= "</td></tr></table>";

			if($this->hasComponents()) {
				$component = $this->getComponent(0);

				$script = new Javascript();
				$script->startFunction("componentFocus".$this->itemID, array());
				$script->addline("var form = document.getElementById('".$this->form->id."');");
				$script->addline("for (i=0;i<form.elements['".$this->name."'].length;i++) {");
				$script->addline("if (form.elements['".$this->name."'][i].value == '".$this->value."')");
				$script->addline("form.elements['".$this->name."'][i].checked = true;");
				$script->addline("}");
				$script->endBlock();
				$this->attachScript($script);
			}

			return $result;
		}

}

?>
