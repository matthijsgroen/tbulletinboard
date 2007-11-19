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

	class FormValueInputList extends FormComponent {

		var $privateVars;

		/**
		 * Creates an textField
		 *@param string $name name of the variable that will be submitted
		 *@param string $title name of the field for the user
		 *@param string $description short description containing the meaning of this field
		 *@param string $seperator, the seperator of the items
		 *@param string $valueSeparator, the seperator of the key and value, if empty there is only one input field
		 *@param string $varName, the name of the variable (key) the text after the input field
		 *@param string $valueName, the name of the value (value)
		 *@param bool $required true if a value is required for this field. false otherwise.
		 *@param bool $disabled true if this field is disabled and no user input is allowed. false otherwise
		 */
		function FormValueInputList($name, $title, $description, $separator = ',', $valueSeparator = '=', $varName = "", $valueName = "",	$required = false, $disabled = false) {
			$this->FormComponent($title, $description, $name);
			$this->privateVars = array(
				'name' => $name,
				'title' => $title,
				'description' => $description,
				'required' => $required,
				'type' => "valueinputlist",
				'separator' => $separator,
				'valueSeparator' => $valueSeparator,
				'varName' => $varName,
				'valueName' => $valueName,
			);
			$this->disabled = $disabled;
			$this->required = $required;
			$this->rowClass = "valueinputlist";
		}

		function getInput() {
			$addFieldName = uniqId('af');
			$addFieldName2 = uniqId('af2');
			$addButtonName = uniqId('ab');
			$delButtonName = uniqId('db');
			$listName = uniqId('lo');
			$addFunction = uniqId('ja');
			$delFunction = uniqId('jd');
			$updateFunction = uniqId('ju');
			$editFunction = uniqId('je');
			$editIndexVarName = uniqId('ji');

			$doubleField = ($this->privateVars['valueSeparator'] != "");

			$value = "";
			if ($this->form->hasValue($this->privateVars['name'], $this->privateVars['type']))
				$value = $this->form->getValue($this->privateVars['name'], $this->privateVars['type']);

			$extra = "";
			$fieldStr = "";
			$enter = "\n";
			$tab = "\t";
			// script
			//$fieldStr .= '<script type="text/javascript" src="javascript/form.js"></script>'.$enter;
			$fieldScript = new Javascript();

			$field1name = "waarde"; if ($this->privateVars['varName'] != "") $field1name = $this->privateVars['varName'];
			$field2name = "waarde"; if ($this->privateVars['valueName'] != "") $field2name = $this->privateVars['valueName'];

			$fieldScript->addLine('var '.$editIndexVarName.' = -1;');

			// Add function
			$fieldScript->startFunction($addFunction);
			$fieldScript->addLine("var addValue = trim(document.".$this->form->id.".".$addFieldName.".value);");
			$fieldScript->addLine("if (addValue.length == 0) {");
			$fieldScript->addLine("alertML('Er is geen ".$field1name." gegeven!');");
			$fieldScript->addLine("return;");
			$fieldScript->addLine("}");
			$fieldScript->addLine("addValue2 = addValue;");
			if ($doubleField) {
				$fieldScript->addLine("addValue2 = trim(document.".$this->form->id.".".$addFieldName2.".value);");
				$fieldScript->addLine("if (addValue2.length == 0) {");
				$fieldScript->addLine("alertML('Er is geen ".$field2name." gegeven!');");
				$fieldScript->addLine("return;");
				$fieldScript->addLine("}");
			}
			$fieldScript->addLine('var list = document.'.$this->form->id.'.'.$listName.';');
			$fieldScript->addLine("var option = new Option(addValue, addValue2);");
			$fieldScript->addLine("var index = list.length;");
			$fieldScript->addLine("if(this.".$editIndexVarName." > -1) {");
			$fieldScript->addLine("list.options[this.".$editIndexVarName."] = option;");
			$fieldScript->addLine("this.".$editIndexVarName." = -1");
			$fieldScript->addLine("} else {");
			$fieldScript->addLine("list.options[index] = option;");
			$fieldScript->addLine("}");
			$fieldScript->addLine("document.".$this->form->id.".".$addFieldName.".value = '';");
			if($doubleField) $fieldScript->addLine("document.".$this->form->id.".".$addFieldName2.".value = '';");
			$fieldScript->addLine("var stringValue = \"\";");
			$fieldScript->addLine("for (var i = 0; i < list.length; i++) {");
			$fieldScript->addLine("var item = list.options[i].text;");
			$fieldScript->addLine("stringValue = stringValue + item;");
			if ($doubleField) {
				$fieldScript->addLine("var item2 = list.options[i].value;");
				$fieldScript->addLine("stringValue = stringValue + '".$this->privateVars['valueSeparator']."' + item2;");
			}
			$fieldScript->addLine("if (i < list.length-1) stringValue = stringValue + '".$this->privateVars['separator']."';");
			$fieldScript->addLine("}");
			$fieldScript->addLine("document.".$this->form->id.".".$this->privateVars['name'].".value = stringValue;");
			$fieldScript->endBlock();

			// Delete function
			$fieldScript->startFunction($delFunction);
			$fieldScript->addLine('var list = document.'.$this->form->id.'.'.$listName.';');
			$fieldScript->addLine('deleteSelected(list);');
			$fieldScript->addLine("var stringValue = \"\";");
			$fieldScript->addLine("for (var i = 0; i < list.length; i++) {");
			$fieldScript->addLine("var item = list.options[i].text;");
			$fieldScript->addLine("stringValue = stringValue + item;");
			if ($doubleField) {
				$fieldScript->addLine("var item2 = list.options[i].value;");
				$fieldScript->addLine("stringValue = stringValue + '".$this->privateVars['valueSeparator']."' + item2;");
			}
			$fieldScript->addLine("if (i < list.length-1) stringValue = stringValue + '".$this->privateVars['separator']."';");
			$fieldScript->addLine("}");
			$fieldScript->addLine("document.".$this->form->id.".".$this->privateVars['name'].".value = stringValue;");
			$fieldScript->endBlock();

			// Edit function
			$fieldScript->startFunction($editFunction);
			$fieldScript->addLine('var list = document.'.$this->form->id.'.'.$listName.';');

			// Set saved values
			$fieldScript->addLine("document.".$this->form->id.".".$addFieldName.".value = list.options[list.selectedIndex].text;");
			if($doubleField) $fieldScript->addLine("document.".$this->form->id.".".$addFieldName2.".value = list.options[list.selectedIndex].value;");
			// Set selectedIndex
			$fieldScript->addLine('this.'.$editIndexVarName.' = list.selectedIndex;');
			$fieldScript->endBlock();

			$fieldStr .= $fieldScript->toString();

			// field
			if ($this->privateVars['varName'] != "") {
				$fieldStr .= $this->privateVars['varName'] . ":<br />\n";
			}
			$fieldStr .= sprintf('<input name="%s" type="text" class="addvalue" tabindex="%s" /><br />'."\n",
				$addFieldName,	$this->form->getTabIndex());
			$this->form->increaseTabIndex();
			if ($doubleField)  {
				if ($this->privateVars['varName'] != "") {
					$fieldStr .= $this->privateVars['valueName'] . ":<br />\n";
				}
				$fieldStr .= sprintf('<input name="%s" type="text" class="addvalue" tabindex="%s" /><br />'."\n",
					$addFieldName2,	$this->form->getTabIndex());
				$this->form->increaseTabIndex();
			}
			$fieldStr .= sprintf('<button name="%s" onclick="%s()" type="button" tabindex="%s">Toevoegen</button>'."\n", $addButtonName, $addFunction, $this->form->getTabIndex());
			$this->form->increaseTabIndex();
			$fieldStr .= sprintf('<button name="%s" onclick="%s()" type="button" tabindex="%s">Verwijderen</button><br />'."\n", $delButtonName, $delFunction, $this->form->getTabIndex());
			$this->form->increaseTabIndex();
			$optionValues = "";

			$options = explode($this->privateVars['separator'], $value);
			for ($i = 0; $i < count($options); $i++) {
				$option = $options[$i];
				if (strLen(trim($option)) > 0) {
					$name = $option;
					$itmvalue = $option;
					if ($doubleField) {
						list($name, $itmvalue) = explode($this->privateVars['valueSeparator'], $option);
					}

					$optionValues .= sprintf(
						'<option value="%1$s">%2$s</option>'."\n",
						htmlConvert($itmvalue), htmlConvert($name)
					);
				}
			}

			$onChangeString = $this->onchange;
			if(is_object($this->form)) {
				$onChangeScript = new JavaScript();
				$onChangeScript->startFunction("field".$this->form->id.$this->identifier."IsChanged");
				if($this->onchange != "") $onChangeScript->addLine($this->onchange);
				$onChangeScript->addLine("form".$this->form->id."IsChanged();");
				$onChangeScript->endBlock();
				$this->attachScript($onChangeScript);
				$onChangeString = "field".$this->form->id.$this->identifier."IsChanged();";
			}

			$fieldStr .= sprintf('<select name="%s" multiple="multiple" size="3" class="multivalue" tabindex="%s" ondblclick="%s()" onchange="%s">'."\n".'%s</select>'."\n",
				$listName,$this->form->getTabIndex(),$editFunction,$onChangeString,$optionValues
			);
			$this->form->increaseTabIndex();
			$fieldStr .= sprintf('<input type="hidden" name="%s" value="%s" />', $this->privateVars['name'], htmlConvert($value));


			$this->form->increaseTabIndex();
			return $fieldStr;
		}

		function hasValue($postData = null) {
			if (!$this->form->hasValue($this->privateVars['name'], $this->privateVars['type'], $postData)) return false;
			$value = $this->form->getValue($this->privateVars['name'], $this->privateVars['type'], $postData);
			return ($value !== "");
		}

		function hasValidValue() {
			return true;
		}

		function getErrorMessage() {
			return "";
		}

		function getValue($postData = null) {
			return $this->form->getValue($this->privateVars['name'], $this->privateVars['type'], $postData);
		}

		function setValueArray(&$valueArray) {
			$valueString = "";
			foreach($valueArray AS $optionID => $optionValue) {
				if(strLen($valueString) > 0) $valueString .= $this->privateVars['separator'];
				$valueString .= $optionValue.$this->privateVars['valueSeparator'].$optionID;
			}
			$this->form->setValue($this->privateVars['name'], $valueString);
		}

	}

?>
