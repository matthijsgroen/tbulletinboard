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
	 * Component to put html with a template in forms
	 */
	class FormTemplateField extends FormComponent {

		var $privateVars;
		var $wide;

		/**
		 * Creates an TemplateField
		 *@param string $template
		 *@param string $text
		 */
		function FormTemplateField($template, $text, $name="Text", $title="", $description="", $wide=true) {
			$this->FormComponent($title, $description, $name);
			$this->privateVars = array(
				'name' => $name,
				'title' => $title,
				'description' => $description,
				'required' => false,
				'disabled' => false,
				'type' => "htmltemplate",
				'template' => $template,
				'text' => $text
			);
			$this->wide = $wide;
			$this->required = false;
			$this->rowClass = "textfield";
		}

		function getInput() {
			$varText = "";
			if(is_object($this->form)) $varText = $this->form->getValue($this->identifier, "text");

			$inputField = str_replace("%text%", $this->privateVars['text'], $this->privateVars['template']);
			$inputField = str_replace("%vartext%", $varText, $inputField);
			global $docRoot;
			$inputField = str_replace("%root%", $docRoot, $inputField);

			return $inputField;
		}

		function printComponent() {
			if ($this->wide) {
				$this->form->printComponentWide($this);
			} else {
				$this->form->printComponentStandard($this);
			}
		}

		function hasValue($postData = null) {
			return true;
		}

		function hasValidValue($postData = null) {
			return true;
		}

		function getErrorMessage() {
			return "";
		}

		function getValue() {
			return '';
		}

	}

?>
