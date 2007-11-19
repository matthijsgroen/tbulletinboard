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
	importClass("interface.formcomponents.TextField");

	/**
	 * Component to put plain text (html allowed) in forms
	 */
	class FormFloatField extends FormTextField {

		var $privateVars;

		/**
		 * Creates an numberField
		 *@param string $name name of the variable that will be submitted
		 *@param string $title name of the field for the user
		 *@param string $description short description containing the meaning of this field
		 *@param int $maxlength the maximum allowed number of characters in this field
		 *@param bool $required true if a value is required for this field. false otherwise.
		 *@param bool $disabled true if this field is disabled and no user input is allowed. false otherwise
		 *@param string $prefix the text before the input field
		 *@param string $postfix the text after the input field
		 */
		function FormFloatField($name, $title, $description, $maxlength, $required = false, $disabled = false, $prefix = "", $postfix = "") {
			$this->FormTextField($name, $title, $description, $maxlength, $required, $disabled, $prefix, $postfix);
			$this->privateVars['type'] = "float";
			$this->rowClass = "numberfield";
		}

		function hasValidValue($postData = null) {
			if (!$this->hasValue()) return true;
			$s = $this->form->getValue($this->privateVars['name'], $this->privateVars['type']);
			$s = str_replace (",", ".", $s); // convert , -> .
			return is_Numeric($s); // staat een . ook toe
			//return preg_match('/^-?\d+$/', $s); // doet ook negatieve waarden
			//return preg_match('/^\d+$/', $s);
		}

		function getErrorMessage() {
			return ivMLGS("library", 20, "Geen geldig kommagetal");
		}

	}

?>
