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
	importClass("util.Javascript");

	/**
	 * Component to put button (html allowed) in forms
	 */
	class FormCheckboxGroup extends FormContainer {

		var $showHideFunc;

		function FormCheckboxGroup($title, $description, $name, $buttons = array()) {
			$this->FormContainer($title, $description, $name);
			$this->setComponents($buttons);
			$this->rowClass = "radiogroup";
			$this->showHideFunc = uniqID("rbo");
		}

		function getOptFuncName() {
			return $this->showHideFunc;
		}

		function getInput() {
			$result = '';
			$result .= '<span class="radiogroup"><table class="rbgtable">'."\n";

			if($this->hasComponents()) {
				$script = new JavaScript();

				for($i = 0; $i < $this->getComponentCount(); $i++) {
					$component =& $this->components[$i];
					$itemID = uniqID("rgo");
					if ($component instanceof FormCheckbox) {

						$result .= sprintf('<tr><td class="rbutton">%s</td><td class="rlabel"><label for="%s">%s</label> <small class="optionComment">%s</small></td></tr>'."\n",
							$component->getInput($itemID),
							$itemID,
							$component->title,
							$component->description
						);
					} else $result .= $component->getInput($itemID) . "\n";
				}
			}
			$result .= '</table></span>'."\n";

			if($this->hasComponents()) {
				if ($script->hasLines()) $result .= $script->getScript();
			}
			return $result;
		}

	}
?>
