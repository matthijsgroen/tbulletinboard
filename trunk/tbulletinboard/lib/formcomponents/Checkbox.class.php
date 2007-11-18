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


	class FormCheckbox extends FormComponent {

		var $caption;
		var $value;
		var $checked;
		var $checkDesc;
		var $onchange;
		var $itemID;
		var $ignorePostData;
		var $inGroup;

		function FormCheckbox($caption, $title, $description, $name, $value, $checked = false, $checkDesc = "", $change="", $ignorePostData = false) {
			$this->FormComponent($title, $description, $name);
			$this->checkDesc = $checkDesc;
			$this->caption = $caption;
			$this->value = $value;
			$this->checked = $checked;
			$this->onchange = $change;
			$this->rowClass = "checkbox";
			$this->inGroup = false;

			//$this->itemID = "chkbx-".$name; CHANGED BY Urvin 16-11-05 -> components needs to have unique id's for javascripting

			$this->itemID = uniqID("rgo");
			$this->ignorePostData = $ignorePostData;
		}

		function getInput($radioID="") {
			$onChangeString = $this->onchange;
			if(is_object($this->form)) {
				$onChangeScript = new JavaScript();

				$functionName = "field".$this->form->id.$this->identifier."IsChanged";

				// If identifier contains [], remove added by Guido on 23-02-06
				$functionName = str_replace("[]","",$functionName);

				$onChangeScript->startFunction($functionName, array("element"));
				if($this->onchange != "") $onChangeScript->addLine($this->onchange);
				$onChangeScript->addLine("form".$this->form->id."IsChanged();");
				$onChangeScript->endBlock();
				$this->attachScript($onChangeScript);
				$onChangeString = $functionName."(this);";
			}

			$result = '';
			//$itemID = uniqID('chkbx');
			//$itemID = "chkbx-".$this->name;

			$selectedValue = $this->checked;

			if ((!$this->ignorePostData) && ($this->form->hasValue($this->identifier, "checkboxes"))) {
				$selectedValue = $this->form->getValue($this->identifier, "checkboxes",null,$this->value);
			}
			
			if (!$this->inGroup) {
				$result .= sprintf(
					'<table><tr><td><input type="checkbox" name="%s"%s tabindex="%s" %s %s id="%s" value="%s" /></td><td>',
					$this->identifier,
					($selectedValue) ? ' checked="checked"' : "",
					$this->form->getTabIndex(),
					//(($this->disabled) ? 'disabled="disabled"' : ''),
					(($this->isDisabled()) ? 'disabled="disabled"' : ''),
					/*"onchange=\"".$onChangeString."\" "."onclick=\"".$onChangeString."\" ",*/ //20-06-06 Onchanges is not working together with viewhider
					"onclick=\"".$onChangeString."\" ",
					$this->itemID,
					$this->value
				) . ' <label for="'.$this->itemID.'">'.$this->caption.'</label>'
					. ' <small class="optionComment">'.$this->checkDesc.'</small></td></tr></table>'."\n";
			} else {
				$result .= sprintf(
					'<input type="checkbox" name="%s"%s tabindex="%s" %s %s id="%s" value="%s" />',
					$this->identifier,
					($selectedValue) ? ' checked="checked"' : "",
					$this->form->getTabIndex(),
					//(($this->disabled) ? 'disabled="disabled"' : ''),
					(($this->isDisabled()) ? 'disabled="disabled"' : ''),
					/*"onchange=\"".$onChangeString."\" "."onclick=\"".$onChangeString."\" ",*/ //20-06-06 Onchanges is not working together with viewhider
					"onclick=\"".$onChangeString."\" ",
					$radioID,
					$this->value
				);
			}
			
			$this->form->increaseTabIndex();
			return $result;
		}

		function notifyAdding(&$container) {
			if ($container instanceof FormContainer) $this->inGroup = true;
		}

	}

?>
