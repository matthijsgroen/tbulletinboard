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
	require_once($libraryClassDir."formcomponents/Button.class.php");

	/**
	 * Component to put button (html allowed) in forms
	 */
	class FormSubmit extends FormButton {

		var $randomName;

		function FormSubmit($caption, $title, $description, $name, $onclick = "") {
			$this->FormButton($caption, $title, $description, $name, $onclick);
			$this->randomName = $this->identifier;
		}

		function getInput() {
			$result = '';
			$result .= '<span class="button">'."\n";
			$onClick = ((trim($this->privateVars["onclick"]) != "") ? $this->privateVars["onclick"]."; " : "") ."this.form.submitValue.value='".$this->identifier."'";

			$result .= '<button type="submit" onclick="'.$onClick.
				'" tabindex="'.$this->form->getTabIndex().'" '.(($this->disabled) ? 'disabled="disabled"' : '').
				' title="'.$this->description.'" name="'.$this->identifier.'" value="'.$this->caption.'">'.
					$this->caption.'</button>'."\n";
			$result .= '</span>'."\n";
			$this->form->increaseTabIndex();
			return $result;
		}

		function getOnSubmitScript(&$script) {
			$script->addLine("thisForm.".$this->identifier.".disabled = true;");
		}
	}

?>
