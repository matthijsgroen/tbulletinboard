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
	define("ivPasswordDefaultNoChange", "____________");

	/**
	 * Component to put plain text (html allowed) in forms
	 */
	class FormPasswordField extends FormComponent {

		var $privateVars;

		/**
		 * Creates an textField
		 *@param string $name name of the variable that will be submitted
		 *@param string $title name of the field for the user
		 *@param string $description short description containing the meaning of this field
		 *@param int $maxlength the maximum allowed number of characters in this field
		 *@param bool $required true if a value is required for this field. false otherwise.
		 *@param bool $disabled true if this field is disabled and no user input is allowed. false otherwise
		 *@param string $prefix the text before the input field
		 *@param string $postfix the text after the input field
		 */
		function FormPasswordField($name, $title, $description, $maxlength, $required = false, $disabled = false, $prefix = "", $postfix = "") {
			$this->FormComponent($title, $description, $name);
			$this->privateVars = array(
				'name' => $name,
				'title' => $title,
				'description' => $description,
				'maxlength' => $maxlength,
				'required' => $required,
				'disabled' => $disabled,
				'prefix' => $prefix,
				'postfix' => $postfix,
				'type' => "text"
			);
			$this->required = $required;
			$this->rowClass = "passwordfield";
		}

		function notifyWriting() {
			if(is_object($this->form)) {
				$this->form->addHiddenField("password_" . $this->privateVars["name"], "yes");
			}
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

			$showValue = false;
			if ($this->form->hasValue($this->privateVars['name'], $this->privateVars['type'])) {
				if ($this->form->getValue($this->privateVars['name'], $this->privateVars['type']) == "_") $showValue = true;
			}
			$inputField = sprintf(
				'%s <input type="password" name="%s" id="%s" maxlength="%s" %stabindex="%s" %s%s/> %s',
					$this->privateVars['prefix'],
					$this->privateVars['name'],
					$this->privateVars['name'],
					$this->privateVars['maxlength'],
					($showValue) ? 'value="'.ivPasswordDefaultNoChange.'" ' : "",
					$this->form->getTabIndex(),
					($this->privateVars['disabled']) ? 'disabled="disabled"': '',
					' onkeyup="'.$onChangeString.'" onchange="'.$onChangeString.'"',
					$this->privateVars['postfix']
			);
			$this->form->increaseTabIndex();
			return $inputField;
		}

		function hasValue($postData = null) {
			if (!$this->form->hasValue($this->privateVars['name'], $this->privateVars['type'])) return false;
			$value = $this->form->getValue($this->privateVars['name'], $this->privateVars['type']);
			return ($value !== "");
		}

		function hasValidValue($postData = null) {
			return true;
		}

		function getErrorMessage() {
			return "";
		}

		function getValue() {
			return $this->form->getValue($this->privateVars['name'], $this->privateVars['type']);
		}

	}

?>
