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

	define('iv_paletteloader', "iv_paletteloader");
	$GLOBALS[iv_paletteloader] = "";
	
	/**
	 * Component to put plain text (html allowed) in forms
	 */
	class FormColorField extends FormComponent {

		var $privateVars;

		/**
		 * Creates an colorField
		 *@param string $name name of the variable that will be submitted
		 *@param string $title name of the field for the user
		 *@param string $description short description containing the meaning of this field
		 *@param int $maxlength the maximum allowed number of characters in this field
		 *@param bool $required true if a value is required for this field. false otherwise.
		 *@param bool $disabled true if this field is disabled and no user input is allowed. false otherwise
		 *@param string $prefix the text before the input field
		 *@param string $postfix the text after the input field
		 */
		function FormColorField($name, $title, $description, $disabled = false) {
			$this->FormComponent($title, $description, $name);
			$this->privateVars = array(
				'name' => $name,
				'title' => $title,
				'description' => $description,
				'disabled' => $disabled,
				'type' => "color"
			);
			$this->rowClass = "colorfield";
		}

		function setPaletteLoader($loaderFile) {
			$GLOBALS[iv_paletteloader] = $loaderFile;
		}

		function getInput() {
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
			
			global $docRoot;
			$inputField = '<a href="javascript:openColorpicker'.$this->privateVars['name'].'()" '.
				'id="colorChoice'.$this->privateVars['name'].'" '.
				'style="display: block; width: 40px; height: 20px; border: 1px solid black; ';
			if ($this->form->hasValue($this->privateVars['name'], $this->privateVars['type']))
				$inputField .= 'background-color: #'.$this->form->getValue($this->privateVars['name'], $this->privateVars['type']).';';
			$inputField .= '"></a>';
			$inputField .= sprintf('<input type="hidden" name="%s" value="%s" />',
				$this->privateVars['name'], $this->form->getValue($this->privateVars['name'], $this->privateVars['type']));
			
			$paletteLoader = "";
			if (strlen($GLOBALS[iv_paletteloader]) > 0) {
				$paletteLoader = "&paletteLoader=".$GLOBALS[iv_paletteloader];
			}			
			$script = new Javascript();
			$script->startFunction("openColorpicker".$this->privateVars['name']);
			$script->addLine("var sizeX = 300; var sizeY = 300; var top = 100; var left = 100;");
			$script->addLine('var params = \'toolbar=0, location=0, directories=0, status=0, menubar=0, scrollbars=1, left=\'+left+\', top=\'+top+\', screenX=\'+left+\', screenY=\'+top+\', resizable=1, width=\'+sizeX+\', height=\'+sizeY;');
			$script->addLine("var picker = window.open('".$docRoot."lib/popups/colorpicker.php?selectedColor=".
				$this->form->getValue($this->privateVars['name'], $this->privateVars['type']).
				"&callback=".$this->privateVars['name'].$paletteLoader."', 'colorpicker', params);");
			$script->endBlock();			
			
			$script->startFunction("colorpickerCallback".$this->privateVars['name'], array("code"));
			$script->addLine("var colorBox = document.getElementById('colorChoice".$this->privateVars['name']."');");
			$script->addLine("if (code != '') {");
			$script->addLine("colorBox.style.backgroundColor = '#'+code;");
			$script->addLine("} else {");
			$script->addLine("colorBox.style.backgroundColor = '';");
			$script->addLine("}");
			$script->addLine("document.forms['".$this->form->id."'].".$this->privateVars['name'].".value = code;");
			$script->addLine($onChangeString);
			$script->endBlock();
			$inputField .= $script->toString();
			
			return $inputField;
		}

	}
	
	class ColorPaletteSet {
	
		var $palettes;
	
		function ColorPaletteSet() {
			$this->palettes = array();		
		}
	
		function addColorPalette(&$palette) {
			$this->palettes[] = $palette;
		}
		
		function getPaletteCount() {
			return count($this->palettes);
		}
		
		function getPalette($index) {
			return $this->palettes[$index];
		}
	}
	
	class ColorPalette {
	
		var $colors;
		var $name;
		
		function ColorPalette($name) {
			$this->name = $name;
			$this->colors = array();
		}
		
		function addColor($name, $color) {
			$this->colors[] = array("name" => $name, "color" => $color);
		}
	
	}

?>
