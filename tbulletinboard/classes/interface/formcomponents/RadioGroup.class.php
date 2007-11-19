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
	class FormRadioGroup extends FormContainer {

		var $showHideFunc;

		function FormRadioGroup($title, $description, $name, $buttons = array()) {
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

			$viewHideScript = new Javascript();
			$viewHideScript->startFunction($this->getOptFuncName(), array());
			$viewHideScript->addLine('var field = document.'.$this->form->id.'.'.$this->identifier.';');
			$viewHideScript->addLine("var checkValue = '';");
			$viewHideScript->addLine('for (i = 0; i < field.length; i++) {');
			$viewHideScript->addLine('if (field[i].checked == true) checkValue = field[i].value;');
			$viewHideScript->endBlock();


			$viewHideComponents = 0;
			if($this->hasComponents()) {
				$script = new JavaScript();

				for($i = 0; $i < $this->getComponentCount(); $i++) {
					$component =& $this->components[$i];
					$itemID = uniqID("rgo");
					if ($component instanceof FormRadioButton) {

						$result .= sprintf('<tr><td class="rbutton">%s</td><td class="rlabel"><label for="%s">%s</label> <small class="optionComment">%s</small></td></tr>'."\n",
							$component->getInput($itemID),
							$itemID,
							$component->title,
							$component->description
						);
					} else $result .= $component->getInput($itemID) . "\n";


					if ($component instanceof FormRadioViewHideButton) {
						$vhScript = $component->getViewHideScript();
						$viewHideScript->mergeScript($vhScript);
						$viewHideComponents++;
					}


				}
			}
			$result .= '</table></span>'."\n";

			if ($viewHideComponents > 0) {
				$viewHideScript->endBlock();
				$script->mergeScript($viewHideScript);
			}


			if($this->hasComponents()) {
				if ($script->hasLines()) $result .= $script->getScript();
			}
			return $result;
		}

		function getSelectedRadioButton() {
			$selectedValue = false;
			if ($this->form->hasValue($this->identifier, "radiogroup")) {
				$selectedValue = $this->form->getValue($this->identifier, "radiogroup");
			}
			for($i = 0; $i < $this->getComponentCount(); $i++) {
				$component =& $this->components[$i];
				if (($component->selected == true) && ($selectedValue === false)) return $component;
				if (($component->value == $selectedValue) && ($selectedValue !== false)) return $component;
			}
			return false;
		}

		function notifyWriting() {
			$radioButton = $this->getSelectedRadioButton();
			if (!is_Object($radioButton)) return false;
			if (get_Class($radioButton) == "formradioviewhidebutton") {
				for ($j = 0; $j < count($radioButton->hideMarkings); $j++) {
					$hideMarking = $radioButton->hideMarkings[$j];
					if (!in_array($hideMarking, $radioButton->showMarkings))
						$this->form->hideMarking($hideMarking);
				}

			}

		}
	}
?>
