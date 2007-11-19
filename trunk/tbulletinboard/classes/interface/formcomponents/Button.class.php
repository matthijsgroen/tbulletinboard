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
	class FormButton extends FormComponent {

		var $privateVars;
		var $caption;

		function FormButton($caption, $title, $description, $name,$onclick) {
			$this->FormComponent($title, $description,$name);
			$this->caption = $caption;
			$this->privateVars = array();
			$this->privateVars["onclick"] = $onclick;
			$this->rowClass = "button";
		}

		function getInput() {
			$result = '';
			$result .= '<span class="button">'."\n";
			$result .= '<button type="button" name="'.$this->identifier.'" onclick="'.$this->privateVars["onclick"].'" tabindex="'.$this->form->getTabIndex().'" '.(($this->disabled) ? 'disabled="disabled"' : '').' title="'.$this->description.'" >'.$this->caption.'</button>'."\n";
			$result .= '</span>'."\n";
			$this->form->increaseTabIndex();
			return $result;
		}
	}
?>
