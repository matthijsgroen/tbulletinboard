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
	 * Import Time and Date calculations class
	 */
	require_once($libraryClassDir."LibDateTime.class.php");
	/**
	 * Import for html conversion functionality
	 */
	require_once($libraryClassDir."library.php");
	require_once($libraryClassDir."Language.class.php");

	/**
	 * Component to put a date inputfield in the form
	 */
	class FormDate extends FormComponent {

		var $privateVars;
		var $centuryBounds = 30;
		var $errorMessage;
		var $postData;

		/**
		 * Constructor of FormDate component
		 *
		 * @param String $title the title of the field
		 * @param String $description the description of the field
		 * @param String $name the name of the field
		 * @param boolean $required if this field is required
		 * @param boolean $disabled if this field is disabled
		 * @param String $prefix a text before the field
		 * @param String $postfix a text after the field
		 * @param boolean $excludeYear if the year input must be hidden
		 * @param String $onchange on change javascript action
		 * @param mixed $postData where the object must get the posted data (example: $_POST or $_GET) default $_POST
		 * @return FormDate
		 */
		function FormDate($title, $description, $name, $required = false, $disabled = false, $prefix="", $postfix="", $excludeYear=false, $onchange="", $postData = null) {
			$this->FormComponent($title, $description, $name);
			$this->privateVars['name'] = $name;
			$this->privateVars['day'] = $name . '_day';
			$this->privateVars['month'] = $name . '_month';
			$this->privateVars['year'] = $name . '_year';
			$this->privateVars['disabled'] = $disabled;
			$this->privateVars['prefix'] = $prefix;
			$this->privateVars['postfix'] = $postfix;
			$this->privateVars['excludeYear'] = $excludeYear;
			//$this->privateVars['onchange'] = $onchange;
			$this->onchange = $onchange;
			$this->privateVars['onfocus'] = "";
			$this->rowClass = "datefield";
			$this->required = $required;
			$this->errorMessage = "";
			$this->postData = $postData;
		}

		function onFocus($jsCommand) {
			$this->privateVars['onfocus'] = $jsCommand;
		}

		function getInput() {
			$onChangeString = $this->onchange;
			if(is_object($this->form)) {
				$onChangeScript = new JavaScript();
				$onChangeScript->startFunction("field".$this->form->id.$this->identifier."IsChanged");
				if($this->onchange != "") $onChangeScript->addLine($this->onchange);
				//$onChangeScript->addLine("return autoTab(this, 2, event)");
				$onChangeScript->addLine("form".$this->form->id."IsChanged();");
				$onChangeScript->endBlock();
				$this->attachScript($onChangeScript);
				$onChangeString = "field".$this->form->id.$this->identifier."IsChanged();";
			}

			$input = $this->privateVars['prefix'] . " ";

			$input .= "<span class=\"dateInput\">";
			$value = htmlConvert($this->getPartValue('day'));
			$input  .= sprintf('<input type="text" onfocus="'.$this->privateVars['onfocus'].'" onchange="'.$onChangeString.' return autoTab(this, 2, event);" onkeyup="'.$onChangeString.' return autoTab(this, 2, event);" name="%s" '. (($this->privateVars["disabled"]) ? 'disabled="disabled"' : '') .' maxlength="2" size="2" value="%s" tabindex="%s" class="dayf" />-', $this->privateVars['day'], $value, $this->form->getTabIndex());

				$this->form->increaseTabIndex();
			$value = htmlConvert($this->getPartValue('month'));
			$input .= sprintf('<input type="text" onfocus="'.$this->privateVars['onfocus'].'" onchange="'.$onChangeString.' return autoTab(this, 2, event);" onkeyup="'.$onChangeString.' return autoTab(this, 2, event);" name="%s" '. (($this->privateVars["disabled"]) ? 'disabled="disabled"' : '') .' maxlength="2" size="2" value="%s" tabindex="%s" class="monthf" />', $this->privateVars['month'], $value, $this->form->getTabIndex());
			$this->form->increaseTabIndex();

			global $language;

			if(!$this->privateVars['excludeYear']) {
				$input .= '-'; // dividing char between month and year
				$value = htmlConvert($this->getPartValue('year'));
				$input .= sprintf('<input type="text" name="%s" '. (($this->privateVars["disabled"]) ? 'disabled="disabled"' : '') .' maxlength="4" size="4" value="%s" onchange="'.$onChangeString.'" onfocus="'.$this->privateVars['onfocus'].'" tabindex="%s" class="yearf" />', $this->privateVars['year'], $value, $this->form->getTabIndex());
							$this->form->increaseTabIndex();
				$input .= "</span> <small>".ivMLGS("library", 14, "dd-mm-jjjj")."</small>";
			} else $input .= "</span> <small>".ivMLGS("library", 15, "dd-mm")."</small>";

			$input .= " " . $this->privateVars['postfix'];
			return $input;
		}

		function getPartValue($datePart) {
			if($this->postData == null) $this->postData = $_POST;

			if (!is_Object($this->form)) {
				if(isSet($this->postData[$this->privateVars[$datePart]])) $partValue = $this->postData[$this->privateVars[$datePart]];
			} else {
				$hasPosted = $this->form->hasValue($this->privateVars[$datePart], 'datefield',$this->postData);
				$partValue = $this->form->getValue($this->privateVars[$datePart], 'datefield',$this->postData);
			}

			if ((trim($partValue) == "") && (!$hasPosted)) {
				// Check if the user has set the value using the fieldname and d-m-Y
				$value = $this->form->getValue($this->privateVars['name'], 'datefield',$this->postData);
				if (trim($value) == "") return "";
				$parts = explode("-", $value);
				switch($datePart) {
					case 'day': $partNr = 0; break;
					case 'month': $partNr = 1; break;
					case 'year': $partNr = 2; break;
				}
				if (count($parts) > $partNr) {
					$this->form->setValue($this->privateVars[$datePart], $parts[$partNr],$this->postData);
					return $parts[$partNr];
				}
				return "";
			} else {
				return $partValue;
			}
		}

		function hasPart($datePart) {
			$value = $this->getPartValue($datePart);
			return (trim($value) != "");
		}

		function hasValue($postData = null) {
			$value = $this->getPartValue('day') . $this->getPartValue('month') . $this->getPartValue('year');
			return (trim($value) != "");
		}

		function getValue() {
			$result = "";
			if ($this->hasPart('day')) $result .= $this->getPartValue('day');
			if ($this->hasPart('month')) $result .= "-".$this->getPartValue('month');
			if ($this->hasPart('year')) $result .= "-".$this->getPartValue('year');
			return $result;
		}

		function getValueFormatted() {
			if (!$this->hasPart('day')) return false; // required part
			$days = $this->getPartValue('day');
			if (!$this->hasPart('month')) return false; // required part
			$months = $this->getPartValue('month');
			if ($this->hasPart('year')) {
				$years = $this->getPartValue('year');
			} else {
				if($this->privateVars['excludeYear']) $dateTime = new LibDateTime(0,0,0,1,1,2004); // GET LEAP YEAR
				else $dateTime = new LibDateTime();
				$years = $dateTime->get(LibDateTime::year());
			}
			if ((strLen($days) != 2) && (strLen($days) != 1)) return false; // 1 or 2 chars
			if ((strLen($months) != 2) && (strLen($months) != 1)) return false; // 1 or 2 chars
			if ((strLen($years) != 4) && (strLen($years) != 2)) return false; // 2 or 4 chars
			if (!is_numeric($days)) return false;
			if (!is_numeric($months)) return false;
			if (!is_numeric($years)) return false;
			// fix the 03 to 2003 etc.
			if (strLen($years) == 2) {
				$dateTime = new LibDateTime();
				$border = ($this->centuryBounds) + $dateTime->toString("y"); // (30 + 03) = 33
				$century = subStr($dateTime->toString("Y"), 0, 2); // 20 (2003)
				if ($years > $border) $century--;
				$years = ($century * 100) + $years;
			}
			if (!checkDate($months, $days, $years)) return false;
			return array("day" => $days, "month" => $months, "year" => $years);
		}

		function getTimestamp() {
			if ($this->hasValidValue()) {
				$date = $this->getValueFormatted();
				return new LibDateTime(0, 0, 0, $date['month'], $date['day'], $date['year']);
			}
			return false;
		}

		function hasValidValue($postData = null) {
			if (!$this->hasPart('day') && !$this->hasPart('month') && !$this->hasPart('year')) return true; // An empty value can also be valid
			$result = $this->getValueFormatted();
			if (!is_array($result)) {
				$this->errorMessage = ivMLGS("library", 18, "Geen geldig datum formaat! (dd-mm-jjjj)");
				return false;
			}
			/*
			if ($result['year'] <= 1970) {
				$this->errorMessage = "Jaar moet tussen 1970 en 2037 liggen!";
				return false;
			}
			if ($result['year'] > 2037) {
				$this->errorMessage = "Jaar moet tussen 1970 en 2037 liggen!";
				return false;
			}
			*/
			return true;
		}

		function getErrorMessage() {
			return $this->errorMessage;
		}

	}


?>
