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
	class FormComponentBar extends FormContainer {
		function FormComponentBar($title, $description, $name, $components = array()) {
			$this->FormContainer($title, $description, $name);
			$this->setComponents($components);
			$this->rowClass = "componentbar";
		}

		function getInput() {
			$result = '';
			$result .= '<span class="componentbar">'."\n";

			if($this->hasComponents()) {
				for($i = 0; $i < $this->getComponentCount(); $i++) {
					$result .= $this->components[$i]->getInput();
				}
			}
			$result .= '</span>'."\n";
			return $result;
		}
	}
?>
