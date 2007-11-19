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
	require_once($libraryClassDir."formcomponents/Select.class.php");

	class FormValueChoiceList extends FormContainer {

		var $privateVars;

		/**
		 * Creates an textField
		 *@param string $name name of the variable that will be submitted
		 *@param string $title name of the field for the user
		 *@param string $description short description containing the meaning of this field
		 *@param int $maxlength the maximum allowed number of characters in this field
		 *@param bool $required true if a value is required for this field. false otherwise.
		 *@param bool $disabled true if this field is disabled and no user input is allowed. false otherwise
		 *@param string $prefix the text before the input field
		 *@param string $postfix the text after the input field
		 */
		function FormValueChoiceList($name, $title, $description, $rows = 10, $separator = ',', $required = false, $disabled = false) {
			$this->FormComponent($title, $description, $name);
			$this->privateVars = array(
				'name' => $name,
				'title' => $title,
				'description' => $description,
				'required' => $required,
				'type' => "valuechoicelist",
				'separator' => $separator,
				'rows' => $rows
			);
			$this->disabled = $disabled;
			$this->required = $required;
			$this->rowClass = "valuechoicelist";
		}

		function getInput() {
			$leftListName = $this->identifier."LeftList";
			$rightListName = $this->identifier."RightList";

			$selectedArray = null;
			if ($this->form->hasValue($this->privateVars['name'], $this->privateVars['type'])) {
				$value = $this->form->getValue($this->privateVars['name'], $this->privateVars['type']);
				if(is_array($value)) $selectedArray = $value;
				else $selectedArray = explode($this->privateVars["separator"],$value); // backward compatibility, may be deleted after testing
			}

			$script = new JavaScript();
			$script->addLine("var ".$leftListName." = document.getElementById('".$leftListName."');");
			$script->addLine("var ".$rightListName." = document.getElementById('".$rightListName."');");
			$script->addLine("var separator = '".$this->privateVars["separator"]."';");

			$script->startFunction($this->identifier."moveItemToRight");
			$script->addLine("leftLen = ".$leftListName.".length;");
			$script->addLine("for ( i=0; i < leftLen ; i++){");
			$script->addLine("if (".$leftListName.".options[i].selected == true ) {");
			$script->addLine("rightLen = ".$rightListName.".length;");
			$script->addLine("".$rightListName.".options[rightLen]= new Option(".$leftListName.".options[i].text,".$leftListName.".options[i].value);");
			$script->addLine("}");
			$script->addLine("}");
			//$script->addLine("resetSelected();");
			$script->addLine("for ( i = (leftLen -1); i >= 0; i--){");
			$script->addLine("if (".$leftListName.".options[i].selected == true) {");
			$script->addLine("".$leftListName.".options[i] = null;");
			$script->addLine("}");
			$script->addLine("}");
			//$script->addLine("alertML('Resultaat : '+getValue());");
			$script->endBlock();

			$script->startFunction($this->identifier."moveItemToLeft");
			$script->addLine("rightLen = ".$rightListName.".length;");

			$script->addLine("for ( i=0; i<rightLen ; i++){");
			$script->addLine("if (".$rightListName.".options[i].selected == true ) {");
			$script->addLine("leftLength = ".$leftListName.".length;");
			$script->addLine("".$leftListName.".options[leftLength]= new Option(".$rightListName.".options[i].text,".$rightListName.".options[i].value);");
			$script->addLine("}");
			$script->addLine("}");
			//$script->addLine("resetSelected();");
			$script->addLine("for ( i=(rightLen-1); i>=0; i--) {");
			$script->addLine("if (".$rightListName.".options[i].selected == true ) {");
			$script->addLine("".$rightListName.".options[i] = null;");
			$script->addLine("}");
			$script->addLine("}");
			//$script->addLine("alertML('Resultaat : '+getValue());");
			$script->endBlock();

			$script->startFunction($this->identifier."moveAllItemsToRight");
			$script->addLine("leftLen = ".$leftListName.".length ;");
			$script->addLine("for ( i=0; i < leftLen ; i++) ".$leftListName.".options[i].selected = true;");
			$script->addLine($this->identifier."moveItemToRight();");
			$script->endBlock();

			$script->startFunction($this->identifier."moveAllItemsToLeft");
			$script->addLine("rightLen = ".$rightListName.".length ;");
			$script->addLine("for ( i=0; i < rightLen ; i++) ".$rightListName.".options[i].selected = true;");
			$script->addLine($this->identifier."moveItemToLeft();");
			$script->endBlock();

			$script->startFunction($this->identifier."resetSelected");
			$script->addLine("rightLen = ".$rightListName.".length ;");
			$script->addLine("for ( i=0; i < rightLen ; i++) ".$rightListName.".options[i].selected = false;");
			$script->addLine("leftLen = ".$leftListName.".length ;");
			$script->addLine("for ( i=0; i < leftLen ; i++) ".$leftListName.".options[i].selected = false;");
			$script->endBlock();

			$script->startFunction($this->identifier."saveValue");
			$script->addLine("var result = '';");
			$script->addLine("leftLen = ".$leftListName.".length ;");
			$script->addLine("for ( i=0; i < leftLen ; i++) result += ".$leftListName.".options[i].value+separator;");
			$script->addLine("result = result.substring(0,result.length-1);");
			$script->addLine("var form = document.getElementById('".$this->form->id."');");
			$script->addLine("form.".$this->identifier.".value = result;");
			$script->endBlock();

			$this->attachScript($script);

			$result = '<input type="hidden" name="'.$this->identifier.'" id="'.$this->identifier.'" value="" />';
			$result .= "<table><tr><td><center><strong>selectie</strong></center>";

			$handledArray = array();

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

			$result .= sprintf(
				'<select class="formselect" name="%s" %s%s id="%s" size="%s" multiple="multiple" %s>'."\n",
				$leftListName,
				($this->hasForm()) ? 'tabindex="'.$this->form->getTabIndex().'" ' : "",
				/*(strlen($this->focus) > 0) ? "onfocus=\"".$this->focus."\" onclick=\"".$this->focus."\" " : "",*/
				//(!$this->enabled) ? "disabled=\"disabled\" " : "",
				($this->disabled) ? "disabled=\"disabled\" " : "",
				$leftListName,
				$this->privateVars['rows'],
				'onchange = "'.$onChangeString.'"'
			);
			if ($this->hasForm()) $this->form->increaseTabIndex();
			if($this->hasComponents()) {
				for($i = 0; $i < $this->getComponentCount(); $i++) {
					$component =& $this->components[$i];

					$add = false;
					if($selectedArray != null) { // we have a selection
						if(in_array($component->getDefaultValue(),$selectedArray) && !in_array($component->getDefaultValue(),$handledArray)) $add = true;
					} else {
						if($component->isSelected() && !in_array($component->getDefaultValue(),$handledArray)) $add = true;
					}

					if($add) {
						$component->setSelected(false);
						$result .= $component->getInput();
						$handledArray[] = $component->getDefaultValue();
					}
				}
			}
			$result .= '</select>'."\n";



			$result .= "</td><td><table><tr><td></td></tr><tr><td>"."\n";

			$result .= '<button name="'.$this->identifier.'moveAllLeftButton" 	type="button" tabindex="'.$this->form->getTabIndex().'" onClick="'.$this->identifier.'moveAllItemsToLeft()"> &laquo; </button><br/>'."\n";
			$this->form->increaseTabIndex();
			$result .= '<button name="'.$this->identifier.'moveLeftButton" 			type="button" tabindex="'.$this->form->getTabIndex().'" onClick="'.$this->identifier.'moveItemToLeft()"> &lt; </button><br/>'."\n";
			$this->form->increaseTabIndex();
			$result .= '<button name="'.$this->identifier.'moveRightButton" 		type="button" tabindex="'.$this->form->getTabIndex().'" onClick="'.$this->identifier.'moveItemToRight()"> &gt; </button><br/>'."\n";
			$this->form->increaseTabIndex();
			$result .= '<button name="'.$this->identifier.'moveAllRightButton" 	type="button" tabindex="'.$this->form->getTabIndex().'" onClick="'.$this->identifier.'moveAllItemsToRight()"> &raquo; </button><br/>'."\n";
			$this->form->increaseTabIndex();

			$result .= "</td></tr><tr><td></td></tr></table></td><td><center>opties</center>"."\n";

			$result .= sprintf(
				'<select class="formselect" name="%s" %s%s id="%s" size="%s" multiple="multiple" %s>'."\n",
				$rightListName,
				($this->hasForm()) ? 'tabindex="'.$this->form->getTabIndex().'" ' : "",
				/*(strlen($this->focus) > 0) ? "onfocus=\"".$this->focus."\" onclick=\"".$this->focus."\" " : "",*/
				//(!$this->enabled) ? "disabled=\"disabled\" " : "",
				($this->disabled) ? "disabled=\"disabled\" " : "",
				$rightListName,
				$this->privateVars['rows'],
				'onchange = "'.$onChangeString.'"'				
			);
			if ($this->hasForm()) $this->form->increaseTabIndex();
			if($this->hasComponents()) {
				for($i = 0; $i < $this->getComponentCount(); $i++) {
					$component =& $this->components[$i];

					$add = false;
					if($selectedArray != null) { // we have a selection
						if(!in_array($component->getDefaultValue(),$selectedArray) && !in_array($component->getDefaultValue(),$handledArray)) $add = true;
					} else {
						if(!$component->isSelected() && !in_array($component->getDefaultValue(),$handledArray)) $add = true;
					}

					if($add) {
						$component->setSelected(false);
						$result .= $component->getInput();
						$handledArray[] = $component->getDefaultValue();
					}
				}
			}
			$result .= '</select>'."\n";

			$result .= "</td></tr></table>"."\n";

			return $result;
		}

		function getOnSubmitScript(&$script) {
			$script->addLine($this->identifier."saveValue();");
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
			$result = array();
			$stringValue = $this->form->getValue($this->identifier, $this->privateVars['type'], $postData);
			//$stringValue = $postValue[$this->identifier];
			if(strLen($stringValue) > 0) $result = explode($this->privateVars["separator"],$stringValue);
			return $result;
		}
	}

?>
