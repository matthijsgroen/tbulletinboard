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
	 * Component to put plain text (html allowed) in forms
	 */
	class FormFileUpload extends FormComponent {

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
		function FormFileUpload($name, $title, $filetypes, $maxKbSize, $maxX = 0, $maxY = 0, $required=false) {
			$description = sprintf('%s: %skB', ivMLGS("library", 2, "Maximum size"), $maxKbSize);
			if ($filetypes != "") {
				$description .= sprintf('<br />%s: %s', ivMLGS("library", 3, "Format"), $filetypes);
			}
			if ($maxY > 0 || $maxX > 0) {
				$description .= 
					'<br /><abbr title="'.ivMLGS("library", 4, "Maximum").'">'.ivMLGS("library", 5, "Max.").'</abbr>'.
					' <abbr title="'.ivMLGS("library", 6, "resolution").'">'.ivMLGS("library", 7, "res.").'</abbr>: '.$maxX.'x'.$maxY;
			}
		
			$this->FormComponent($title, $description, $name);
			$this->privateVars = array(
				'filetypes' => $filetypes,
				'maxKbSize' => $maxKbSize,
				'maxX' => $maxX,
				'maxY' => $maxY
			);
			$this->required = $required;
			$this->rowClass = "fileupload";
		}

		function setForm(&$form) {
			$this->form =& $form;
			$form->addHiddenField("MAX_FILE_SIZE", $this->privateVars['maxKbSize'] * 1024);
			$form->encType = "multipart/form-data";
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

			$inputField = sprintf(
				'<input type="file" name="%s" id="%s" tabindex="%s" onchange="%s"/>',
					$this->identifier,
					$this->identifier,
					$this->form->getTabIndex(),
					$onChangeString
			);
			$this->form->increaseTabIndex();
			return $inputField;
		}

		function hasValue() {
			if (!$this->form->hasValue($this->privateVars['name'], $this->privateVars['type'])) return false;
			$value = $this->form->getValue($this->privateVars['name'], $this->privateVars['type']);
			return ($value !== "");
		}

		function hasValidValue() {
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
