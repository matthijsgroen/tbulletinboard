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
	importClass("util.LibDateTime");
	importClass("util.Language");

	/**
	 * Component to a time input field in the form
	 */
	class FormTime extends FormComponent {

		var $privateVars;

		function FormTime($title, $description, $name, $required = false, $disabled = false, $prefix="", $postfix="", $onchange="") {
			$this->FormComponent($title, $description, $name);
			$this->privateVars['name'] = $name;
			$this->privateVars['hours'] = $name . '_hours';
			$this->privateVars['minutes'] = $name . '_minutes';
			//$this->privateVars['disabled'] = $disabled;
			$this->setDisabled($disabled);
			$this->privateVars['prefix'] = $prefix;
			$this->privateVars['postfix'] = $postfix;
			//$this->privateVars['onkeyup'] = $onkeyup;
			$this->onchange = $onchange;
			$this->rowClass = "timefield";
			$this->required = $required;
		}

		function getInput() {
			$onChangeString = $this->onchange;
			if(is_object($this->form)) {
				$onChangeScript = new JavaScript();
				$onChangeScript->startFunction("field".$this->form->id.$this->identifier."IsChanged");
				if($this->onchange != "") $onChangeScript->addLine($this->onchange);
				//$onChangeScript->addLine("return autoTab(this, 2, event);");
				$onChangeScript->addLine("form".$this->form->id."IsChanged();");
				$onChangeScript->endBlock();
				$this->attachScript($onChangeScript);
				$onChangeString = "field".$this->form->id.$this->identifier."IsChanged();";
			}

			$input = $this->privateVars['prefix'] . " ";
			$input .= "<span class=\"timeInput\">";
			$value = htmlConvert($this->getPartValue('hours'));
			//$input .= sprintf('<input type="text" onkeyup="'.$this->privateVars['onkeyup'].' return autoTab(this, 2, event); " name="%s" '. (($this->disabled) ? 'disabled="disabled"' : '') .' maxlength="2" size="2" value="%s" tabindex="%s" class="hourf" />:', $this->privateVars['hours'], $value, $this->form->getTabIndex());
			$input .= sprintf('<input type="text" onchange="'.$onChangeString.' return autoTab(this, 2, event);" onkeyup="'.$onChangeString.' return autoTab(this, 2, event);" name="%s" '. (($this->disabled) ? 'disabled="disabled"' : '') .' maxlength="2" size="2" value="%s" tabindex="%s" class="hourf" />:', $this->privateVars['hours'], $value, $this->form->getTabIndex());
			$this->form->increaseTabIndex();
			$value = htmlConvert($this->getPartValue('minutes'));
			$input .= sprintf('<input type="text" name="%s" onchange="'.$onChangeString.' return autoTab(this, 2, event);" onkeyup="'.$onChangeString.'" return autoTab(this, 2, event);'. (($this->disabled) ? 'disabled="disabled"' : '') .' maxlength="2" size="2" value="%s" tabindex="%s" class="minutef" />', $this->privateVars['minutes'], $value, $this->form->getTabIndex());
			$this->form->increaseTabIndex();
			$input .= "</span> <small>".ivMLGS("library", 22, "uu:mm")."</small>";
			$input .= " " . $this->privateVars['postfix'];
			return $input;
		}

		function notifyWriting() {
			$value = htmlConvert($this->getPartValue('hours'));
			if ($this->disabled) $this->form->addHiddenField($this->privateVars['hours'], $value);
			$value = htmlConvert($this->getPartValue('minutes'));
			if ($this->disabled) $this->form->addHiddenField($this->privateVars['minutes'], $value);
		}

		function getPartValue($timePart) {
			$partValue = $this->form->getValue($this->privateVars[$timePart], 'timefield');
			
			if (trim($partValue) == "") {
				// Check if the user has set the value using the fieldname and d-m-Y
				$value = $this->form->getValue($this->privateVars['name'], 'timefield');
				if (trim($value) == "") return "";
				$parts = explode(":", $value);
				switch($timePart) {
					case 'hours': $partNr = 0; break;
					case 'minutes': $partNr = 1; break;
				}
				if (count($parts) > $partNr) {
					$this->form->setValue($this->privateVars[$timePart], $parts[$partNr]);
					return $parts[$partNr];
				}
				return "";
			} else {
				return $partValue;
			}
		}

		function getValue() {
			$result = "";
			if ($this->hasPart('hours')) $result .= $this->getPartValue('hours');
			if ($this->hasPart('minutes')) $result .= "-".$this->getPartValue('minutes');
			return $result;
		}

		function hasPart($timePart) {
			$value = $this->getPartValue($timePart);
			return (trim($value) != "");
		}

		function hasValue($postData = null) {
			$value = $this->getPartValue('hours') . $this->getPartValue('minutes');
			return (trim($value) != "");
		}

		function getValueFormatted() {
			if (!$this->hasPart('hours')) return false; // required part
			$hours = $this->getPartValue('hours');
			if (!$this->hasPart('minutes')) return false; // required part
			$minutes = $this->getPartValue('minutes');
			if ((strLen($hours) != 2) && (strLen($hours) != 1)) return false; // 1 or 2 chars
			if ((strLen($minutes) != 2) && (strLen($minutes) != 1)) return false; // 1 or 2 chars
			if (!is_numeric($hours)) return false;
			if (!is_numeric($minutes)) return false;
			if (($hours < 0) || ($hours > 23)) return false;
			if (($minutes < 0) || ($minutes > 59)) return false;

			return array("hour" => $hours, "minute" => $minutes);
		}

		function getTimestamp() {
			if ($this->hasValidValue()) {
				$time = $this->getValueFormatted();
				return new LibDateTime($time['hour'], $time['minute'], 0);
			}
			return false;
		}

		function hasValidValue($postData = null) {
			if (!$this->hasPart('hours') && !$this->hasPart('minutes')) return true; // An empty value can also be valid
			return is_array($this->getValueFormatted());
		}


		function getErrorMessage() {
			return ivMLGS("library", 23, "Geen geldig tijd formaat! (uu:mm)");
		}


	}


?>
