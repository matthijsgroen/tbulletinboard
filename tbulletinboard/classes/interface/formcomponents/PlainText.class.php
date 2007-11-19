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
	class FormPlainText extends FormComponent {

		var $text;
		var $wide;

		function FormPlainText($title, $description, $text, $name="Text", $wide=false) {
			$this->FormComponent($title, $description, $name);
			$this->text = $text;
			$this->rowClass = "infotext";
			$this->wide = $wide;
		}

		function getInput() {
			$varText = $this->form->getValue($this->identifier, "text");

			$inputField = str_replace("%text%", $varText, $this->text);
			global $docRoot;
			$inputField = str_replace("%root%", $docRoot, $inputField);

			return "<span>".$inputField."</span>";
		}

		function printComponent() {
			if ($this->wide) {
				$this->form->printComponentWide($this);
			} else {
				$this->form->printComponentStandard($this);
			}
		}
	}


?>
