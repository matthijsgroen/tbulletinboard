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
	global $libraryClassDir;
	require_once($libraryClassDir."Form.class.php");
	require_once($libraryClassDir."javascript/Javascript.class.php");

	/**
	 * Component to put button (html allowed) in forms
	 */
	class FormViewHider extends FormContainer {
		
		var $showHideFunc;
		var $value;
		
		function FormViewHider($title, $description, $name, $value) {
			$this->FormContainer($title, $description, $name);
			$this->setVisible(false);
			$this->value = $value;
			$this->showHideFunc = $title;
		}
		
		function getOptFuncName() {
			return $this->showHideFunc;
		}
		
		function getInput() {
			$value = $this->value;
			if ($this->form->hasValue($this->identifier, "viewhider"))
				$value = $this->form->getValue($this->identifier, "viewhider");
			
			$result = sprintf('<input type="hidden" name="%s" value="%s" />', $this->identifier, $value);
			
			$viewHideScript = new Javascript();
			$viewHideScript->startFunction($this->getOptFuncName(), array("checkValue"));
			$viewHideScript->addLine("document.forms['".$this->form->id."'].".$this->identifier.".value=checkValue;");

			$viewHideComponents = 0;
			if($this->hasComponents()) {
				$script = new JavaScript();
				for($i = 0; $i < $this->getComponentCount(); $i++) {
					$component =& $this->components[$i];
					if (get_class($component) == "formviewhide") {
						$vhScript = $component->getViewHideScript();
						$viewHideScript->mergeScript($vhScript);
						$viewHideComponents++;
					}
				}
			}

			if ($viewHideComponents > 0) {
				$viewHideScript->endBlock();
				$script->mergeScript($viewHideScript);			
			}			
			if($this->hasComponents()) {
				if ($script->hasLines()) $result .= $script->getScript();
			}
			return $result;
		}
		
		function notifyWriting() {
			$value = $this->value;
			if ($this->form->hasValue($this->identifier, "viewhider"))
				$value = $this->form->getValue($this->identifier, "viewhider");
			
			for ($i = 0; $i < $this->getComponentCount(); $i++) {
				$component =& $this->getComponent($i);
				if ($component->value == $value) {
					for ($j = 0; $j < count($component->hideMarkings); $j++) {
						$hideMarking = $component->hideMarkings[$j];
						if (!in_array($hideMarking, $component->showMarkings))
							$this->form->hideMarking($hideMarking);
					}
				}
			}
		}
	}


	class FormViewHide extends FormComponent {
	
		var $showMarkings;
		var $hideMarkings;
		var $showHideFunc;
		var $uniqueID;
		var $value;
	
		function FormViewHide($name, $value, $showMarkings = array(), $hideMarkings = array()) {
			$this->FormComponent("", "", "ViewHide".$value);
			$this->value = $value;
			$this->showMarkings = $showMarkings;
			$this->hideMarkings = $hideMarkings;
			$this->showHideFunc = uniqID("rbo");
			$this->uniqueID = uniqID("radf");
		}
		
		function setShowMarkings($markings) {
			$this->showMarkings = $markings;
		}
		
		function setHideMarkings($markings) {
			$this->hideMarkings = $markings;
		}
		
		function getHideMarkings() {
			return $this->hideMarkings;
		}
		
		function notifyAdding(&$container) {
			$this->showHideFunc = $container->getOptFuncName();
		}

		function getInput($radioID="") {
			return "";
		}

		function getViewHideScript() {
			$script = new Javascript();

			$script->addLine("if (checkValue == '".$this->value."') {");
			if (isSet($this->hideMarkings)) {
				for ($x = 0; $x < count($this->hideMarkings); $x++) {
					$hideName = $this->hideMarkings[$x];
					$nrRows = $this->form->getMarkCount($hideName);
					$hideNames = array();
					for ($y = 0; $y < $nrRows; $y++) {
						$hideNames[] = 'row'.$hideName.$y;
					}
					$script->addLine("var hideElements = new Array('".implode("', '", $hideNames)."');");
					$script->addLine("for (var i = 0; i < hideElements.length; i++) {");
					$script->addLine("var item = document.getElementById(hideElements[i]);");
					$script->addLine("if (item != null) item.style.display = 'none';");
					$script->addLine("}");
				}
			}
			if (isSet($this->showMarkings)) {
				for ($x = 0; $x < count($this->showMarkings); $x++) {
					$showName = $this->showMarkings[$x];
					$nrRows = $this->form->getMarkCount($showName);
					$showNames = array();
					for ($y = 0; $y < $nrRows; $y++) {
						$showNames[] = 'row'.$showName.$y;
					}
					$script->addLine("var showElements = new Array('".implode("', '", $showNames)."');");
					$script->addLine("for (var i = 0; i < showElements.length; i++) {");
					$script->addLine("var item = document.getElementById(showElements[i]);");
					$script->addLine("if (item != null) item.style.display = '';");
					$script->addLine("}");
				}
			}
			$script->endBlock();
			
			return $script;
		}
	
	}

?>
