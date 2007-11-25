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

	importClass("util.Javascript");

	/**
	 * FormFieldGroup is a standard superclass for all FormFieldGroups.
	 * A FormFieldGroup can contain various new formfields that can be used
	 * in a form. See FormFields.class.php for a FormFieldGroup with the
	 * most commonly used formfields
	 */
	class FormFieldGroup {

		function FormFieldGroup() {
		}

		function hasFieldType($type) {
		}

		function writeField(&$form, $type, $fieldData) {
		}

		/**
		 * This function gets called when the form will be written in HTML.
		 */
		function onFormOutput(&$form) {
		}
	}

	/**
	 * Superclass for all form components
	 */
	class FormComponent {

		/**
		 * The form this component resides in
		 *@var Form $form
		 */
		var $form;

		/**
		 * Title of the component (display for users)
		 *@var string $title
		 */
		var $title;
		var $errorDisplayName;

		/**
		 * Description of the component (display for users)
		 *@var string $description
		 */
		var $description;

		/**
		 * Marking of the group this component is in
		 *@var string $title
		 */
		var $markGroup;

		/**
		 * Index of the component within the marking. This is to create an unique ID
		 *@var int $markIndex
		 */
		var $markIndex;

		/**
		 * Index of this component within the form.
		 *@var int $componentIndex
		 */
		var $componentIndex;

		/**
		 * The class of the row of the formcomponent
		 *@var string $rowClass
		 */
		var $rowClass;

		/**
		 * States if a value is required for this field
		 *@var bool $required
		 */
		var $required;

		/**
		 * Internal identifier whereby this component can be referenced
		 *@var string $identifier
		 */
		var $identifier;

		/**
		 * State if this component is disabled
		 */
		var $disabled;

		/**
		 * State if this component is a visible component
		 */
		var $visible;

		var $onchange;
		var $onfocus;

		/**
		 * Users can attach scripts to components (for onchange events, etc)
		 */
		var $attachedScript;

		function FormComponent($title, $description, $identifier) {
			$this->title = $title;
			$this->errorDisplayName = $title;
			$this->description = $description;
			$this->identifier = $identifier;
			$this->setDisabled(false);
			$this->setVisible(true);
			$this->form = null;
			$this->attachedScript = null;
		}

		function setForm(&$form) {
			$this->form =& $form;
		}

		function hasForm() {
			return is_Object($this->form);
		}

		function setErrorDisplayName($name) {
			$this->errorDisplayName = $name;
		}

		function &getForm() {
			return $this->form;
		}

		function getInput() {
			$result = '';
			$result .= '<div class="componentgroup">'."\n";

			if($this->hasComponents()) {
				for($i = 0; $i < $this->getComponentCount(); $i++) {
					$result .= $this->components[$i]->getInput();
				}
			}
			$result .= '</div>'."\n";
			return $result;
		}

		function getExtra() {
		}

		function printComponent() {
			if ($this->visible) {
				$this->form->printComponentStandard($this);
			} else {
				$this->form->printComponentHidden($this);
			}
		}

		function onChange($jsCommand) {
			$this->onchange = $jsCommand;
		}

		function onFocus($jsCommand)  {
			$this->onfocus = $jsCommand;
		}

		function hasValue($postData = null) {
			return false;
		}

		function hasValidValue($postData = null) {
			return true;
		}

		function getErrorMessage() {
			return "";
		}

		function getValue() {
			return "";
		}

		function setDisabled($disabled) {
			$this->disabled = $disabled;
		}

		function isDisabled() {
			return $this->disabled;
		}

		function setVisible($visible) {
			$this->visible = $visible;
		}

		function setRequired($required) {
			$this->required = $required;
		}

		function isRequired() {
			return $this->required;
		}

		function notifyAdding(&$container) {
		}

		function notifyWriting() {
		}

		function getOnSubmitScript(&$script) {
		}

		function attachScript(&$script) {
			if (is_Object($this->attachedScript)) {
				$this->attachedScript->mergeScript($script);
			} else {
				$this->attachedScript =& $script;
			}
		}

		function hasAttachedScript() {
			return is_Object($this->attachedScript);
		}

		function getAttachedScript() {
			return $this->attachedScript;
		}

	}

	/**
	 * Superclass for all containers
	 */
	class FormContainer extends FormComponent {
		/**
		 * Array with all the FormComponents
		 **/
		var $components;
		var $errorMessage;

		/**
		 * Creates a new container
		 **/
		function FormContainer($title, $description,$identifier) {
			$this->FormComponent($title, $description,$identifier);
			$this->components = array();
			$this->errorMessage = "";
		}

		/**
		 * Sets this form and the forms of al the components
		 **/
		function setForm(&$form) {
			$this->form =& $form;
			if($this->hasComponents()) {
				for($i = 0; $i < $this->getComponentCount(); $i++) {
					$this->components[$i]->setForm($form);
				}
			}
		}

		/**
		 * Sets this component disabled status and all the components
		 **/
		function setDisabled($disabled) {
			$this->disabled =& $disabled;
			if($this->hasComponents()) {
				for($i = 0; $i < $this->getComponentCount(); $i++) {
					$this->components[$i]->setDisabled($disabled);
				}
			}
		}

		/**
		 * Add a component
		 **/
		function addComponent(&$component) {
			$component->setForm($this->form);
			$component->notifyAdding($this);
			$this->components[] =& $component;
		}


		/**
		 * Adds an array of components
		 **/
		function addComponents(&$components) {
			for($i = 0; $i < count($components); $i++) {
				$this->addComponent($components[$i]);
			}
		}

		/**
		 * Removes all components and add a component
		 **/
		function setComponent(&$component) {
			$this->removeAllComponents();
			$this->addComponent($component);
		}

		/**
		 * Removes all components and adds an array of components
		 **/
		function setComponents(&$components) {
			$this->removeAllComponents();
			$this->addComponents($components);
		}

		/**
		 * Removes all the components
		 **/
		function removeAllComponents() {
			if(isSet($this->components)) unSet($this->components);
			$this->components = array();
		}

		/**
		 * Checks if there is a component in this container
		 **/
		function hasComponents() {
			if($this->getComponentCount() > 0) return true;
			return false;
		}

		/**
		 * Gets the component count of this container
		 **/
		function getComponentCount() {
			if(isSet($this->components)) return count($this->components);
			return -1;
		}

		function getComponent($index) {
			return $this->components[$index];
		}

		function getComponents() {
			return $this->components;
		}

		function hasValidValue($postData = null) {
			for($i = 0; $i < count($this->components); $i++) {
				if (!$this->components[$i]->hasValidValue($postData)) {
					$this->errorMessage = $this->components[$i]->getErrorMessage();
					return false;
				}
			}
			return true;
		}

		function getErrorMessage() {
			return $this->errorMessage;
		}

		function getOnSubmitScript(&$script) {
			for($i = 0; $i < count($this->components); $i++) {
				$this->components[$i]->getOnSubmitScript($script);
			}
		}

		function hasAttachedScript() {
			$selfAttached = is_Object($this->attachedScript);
			if ($selfAttached) return true;
			for($i = 0; $i < count($this->components); $i++) {
				if ($this->components[$i]->hasAttachedScript()) return true;
			}
		}

		function getAttachedScript() {
			$script = new Javascript();
			if (is_Object($this->attachedScript)) {
				$script->mergeScript($this->attachedScript);
			}
			for($i = 0; $i < count($this->components); $i++) {
				if ($this->components[$i]->hasAttachedScript()) {
					$script->mergeScript($this->components[$i]->getAttachedScript());
				}
			}
			return $script;
		}

	}

	/**
	 * Form is a class that builds te list of formfields and displays them.
	 */
	class Form {

		var $id;
		var $action;
		var $method;
		var $target;
		var $privateVars;
		var $encType;
		var $activeMarking;
		var $ignorePostData;
		var $formSkin = true; // false for menuSkin
		// skin variables
		var $activeFormLayout = null;
		var $formStyleCounts;

		/**
		 * Instances a new form
		 *@param string $id identifier of the form.
		 *@param string $action action target of the form
		 */
		function Form($id, $action, $target="") {
			$this->method = "post";
			$this->target = $target;
			$this->id = $id;
			$this->action = $action;
			$this->encType = "application/x-www-form-urlencoded";
			$this->privateVars['fields'] = array();
			$this->privateVars['hidden-fields'] = array();
			$this->privateVars['values'] = array();
			$this->privateVars['onsubmit'] = array();
			$this->privateVars['onchange'] = array();
			$this->privateVars['fieldgroups'] = array();
			$this->privateVars['markGroups'] = array();
			$this->privateVars['markHidden'] = array();
			$this->privateVars['tabIndex'] = 0;
			$this->formStyleCounts = array();

			$this->addHiddenField('formName', $this->id);
			$this->addHiddenField('submitValue', "");
			$this->setIgnorePostData(false);
		}

		function changeName($newName) {
			$this->id = $newName;
			for ($i = 0; $i < count($this->privateVars['hidden-fields']); $i++) {
				if ($this->privateVars['hidden-fields'][$i]['name'] == "formName") {
					$this->privateVars['hidden-fields'][$i]["value"] = $newName;
				}
			}
		}

		/**
		* Sets the method of the form, use "post" or "get"
		*@param String $method the method of the form
		*
		**/
		function setMethod($method) {
			$this->method = $method;
		}

		/*****
		 ** Marking items is for the functionality to hide and show a group of fields, eg. triggered by a radiobutton value.
		 ***/
		/**
		 * Returns the number of items marked for the given name
		 */
		function getMarkCount($markName) {
			if (isSet($this->privateVars['markGroups'][$markName])) {
				return $this->privateVars['markGroups'][$markName];
			}
			return 0;
		}

		/**
		 * Hide the items marked with name
		 */
		function hideMarking($name) {
			$this->privateVars['markHidden'][$name] = true;
		}

		/**
		 * Set marking to a name. The items that will added will be marked with the given name
		 */
		function startMarking($marking) {
			$this->activeMarking = $marking;
		}

		/**
		 * Stop marking items
		 */
		function endMarking() {
			unSet($this->activeMarking);
		}

		/**
		 * Sets a boolean sothat de posted data of the form is ignored
		 */
		function setIgnorePostData($ignorePostData) {
			$this->ignorePostData = $ignorePostData;
		}

		/**
		 * Add an object that can add and render formfields
		 */
		function addFieldGroup(&$fieldGroup) {
			$this->privateVars['fieldgroups'][] =& $fieldGroup;
		}

		/**
		 * Gives an extra char to formelements when they are required
		 */
		function showRequired($bool, $showChar = '', $class = '') {
			if($bool) {
				$this->privateVars['required']['bool'] = true;
				$this->privateVars['required']['char'] = $showChar;
				$this->privateVars['required']['class'] = $class;
			}
			else {
				$this->privateVars['required']['char'] = '';
				$this->privateVars['required']['bool'] = false;
			}
		}

		/**
		 * Add a part of javascript code that gets triggered if the form will submit.
		 *
		 * @param String $code a function call that returns a boolean
		 */
		function addOnSubmit($code) {
			$this->privateVars['onsubmit'][] = $code;
		}

		/**
		* Add a javascript that gets triggered if the form is changed
		**/
		function addOnChange($script) {
			$this->privateVars['onchange'][] = $script;
		}


		function addHiddenField($name, $value) {
			$this->privateVars['hidden-fields'][] = array('name' => $name, 'value' => $value);
		}

		function setFocus($fieldName, $fieldType = null) {
			$this->privateVars['focusname'] = $fieldName;
			$this->privateVars['focustype'] = $fieldType;
		}

		function selectValue($fieldName) {
			$this->privateVars['selectname'] = $fieldName;
		}


		function getFormInitScript() {
			importClass("util.Javascript");
			$hasItems = false;

			$selectString = "";
			if (isset($this->privateVars['selectname']) && $this->privateVars['selectname'] != null) {
				$postFix = '';
				$selectname = $this->privateVars['selectname'];
				if(!is_numeric($selectname)) {
					$selectString = 'document.forms[\''.$this->id.'\'].'.$selectname.$postFix.'.select();';
					$hasItems = true;
				}
			}

			$focusString = "";
			if (isset($this->privateVars['focusname']) && $this->privateVars['focusname'] != null) {
				$postFix = '';
				if ($this->privateVars['focustype'] != null) {
					if ($this->privateVars['focustype'] == 'datefield') $postFix = '_day';
					if ($this->privateVars['focustype'] == 'timefield') $postFix = '_hours';
				}
				$focusname = $this->privateVars['focusname'];

				if(!is_numeric($focusname)) {
					$focusString = 'document.forms[\''.$this->id.'\'].'.$focusname.$postFix.'.focus();';
					$hasItems = true;
				}
			}

			$script = new Javascript();
			if ($hasItems) {
				$script->addEventAttacher("attach".$this->id."init");
				$script->startFunction("init".$this->id."form", array());
				$script->addLine($selectString);
				$script->addLine($focusString);
				$script->endBlock();
				$script->addLine("attach".$this->id."init(window, 'load', init".$this->id."form);");
			}
			return $script;
		}


		function addField($fieldData) {
			$defaults = array();
			// If marking items is on, mark the new items.
			if (isSet($this->activeMarking)) {
				$nr = 0; // Fetch the index of the group
				if (isSet($this->privateVars['markGroups'][$this->activeMarking])) {
					$nr = $this->privateVars['markGroups'][$this->activeMarking];
				}
				$this->privateVars['markGroups'][$this->activeMarking] = $nr+1;
				$defaults['idMark'] = $this->activeMarking;
				$defaults['markIndex'] = $nr;
			}
			$fieldData = array_merge($defaults, $fieldData);
			$this->privateVars['fields'][] = $fieldData;
		}

		function addComponent(&$formComponent) {
			if (isSet($this->activeMarking)) {
				$nr = 0; // Fetch the index of the group
				if (isSet($this->privateVars['markGroups'][$this->activeMarking])) {
					$nr = $this->privateVars['markGroups'][$this->activeMarking];
				}
				$this->privateVars['markGroups'][$this->activeMarking] = $nr+1;
				$formComponent->markGroup = $this->activeMarking;
				$formComponent->markIndex = $nr;
			}
			$formComponent->setForm($this);
			$this->privateVars['fields'][] =& $formComponent;
		}

		/**
		 * Remove a component
		 **/
		function removeComponent($fieldName) {
			$newComponentList = array();
			for($i = 0; $i < count($this->privateVars['fields']); $i++) {
				$component =& $this->privateVars['fields'][$i];
				if($component->identifier != $fieldName) {
					$newComponentList[] =& $component;
				} else { //check if component has an activeMarking
					//TODO update marking + index of other components;
					//if(isset($this->privateVars['markGroups'][$component->markGroup]))
					//	$this->privateVars['markGroups'][$component->markGroup]--;
				}
			}
			$this->privateVars['fields'] =& $newComponentList;
			unset($newComponentList);
		}

		function writeFormStart() {
			$this->notifyBeforeWriting();
			
			$target = "";
			if ($this->target != "") $target=' target="'.$this->target.'"';
			?>
			<form id="<?=$this->id ?>" name="<?=$this->id ?>" method="<?=$this->method ?>"<?=$target ?>
				action="<?=$this->action ?>" enctype="<?=$this->encType ?>" onsubmit="return form<?=$this->id ?>OnSubmit(this)" accept-charset="UTF-8">
				<div class="hidden">
				<?php
					for ($i = 0; $i < count($this->privateVars['hidden-fields']); $i++) {
						$field = $this->privateVars['hidden-fields'][$i];
						?>
						<input type="hidden" name="<?=$field['name']; ?>" id="<?=$field['name']; ?>" value="<?=$field['value']; ?>" />
						<?php
					}
				?>
				</div>
			<?php
		}
		
		function writeFormEnd() {
			$this->writeFormJavascript();
		}

		function writeFormTable($startTab = 1, $menuClass="", $width="", $style="") {
			global $formSkin;
			$this->privateVars['tabIndex'] = $startTab;
			?>
			<div class="form">
					<?php
					$this->writeFormStart();
					if($formSkin) {
						?>
						<table style="<?=$style; ?><?=($width != "") ? ' width: '.$width.';' : ""; ?>">
							<tbody class="formBody">
							<?php
								$index = 0;
								for ($i = 0; $i < count($this->privateVars['fields']); $i++) {
									if (!is_Object($this->privateVars['fields'][$i])) {
										$field =& $this->privateVars['fields'][$i];
										$field['form-index'] = $index;
										$handled = false;
										for ($j = 0; $j < count($this->privateVars['fieldgroups']); $j++) {
											$fieldGroup =& $this->privateVars['fieldgroups'][$j];
											if ($fieldGroup->hasFieldType($field['type']))  {
												$fieldGroup->writeField($this, $field['type'], $field);
												$handled = true;
												break;
											}
										}
										if ($handled) $index++;
									} else {
										$field =& $this->privateVars['fields'][$i];
										$field->componentIndex = $index;
										$field->printComponent();
										if ($field->visible) $index++;
									}
								}
							?>
							</tbody>
						</table>
						<?php
					} else {
						?>
						<div class="<?=$menuClass?>">
							<div class="menu"><span class="menuspc">&nbsp;</span>
							<?php
								$index = 0;
								for ($i = 0; $i < count($this->privateVars['fields']); $i++) {
									if (!is_Object($this->privateVars['fields'][$i])) {
										$field = $this->privateVars['fields'][$i];
										$field['form-index'] = $index;
										$handled = false;
										for ($j = 0; $j < count($this->privateVars['fieldgroups']); $j++) {
											$fieldGroup =& $this->privateVars['fieldgroups'][$j];
											if ($fieldGroup->hasFieldType($field['type']))  {
												$fieldGroup->writeField($this, $field['type'], $field);
												$handled = true;
											}
										}
										if ($handled) $index++;
									} else {
										$field =& $this->privateVars['fields'][$i];
										$field->componentIndex = $index;
										$field->printComponent();
										$index++;
									}

									if($i < count($this->privateVars['fields'])-1) {
										?><span class="optiondivider">|</span><?
									}
								}
							?>
							</div>
						</div>
						<?php
					}
					?>
				</form>
			</div>
			<?php
			$this->writeFormJavascript();
		}

		function writeFormJavascript() {
			$onSubmitScript = new Javascript();
			$onSubmitScript->startFunction("form".$this->id."OnSubmit", array("thisForm"));

			$onSubmitScript->addLine("var result = true;");
			if (count($this->privateVars['onsubmit']) > 0) {
				$onSubmitScript->addLine('result = ('.implode(" && ", $this->privateVars['onsubmit']).');');
			}
			// the piece where other components disable :-)
			$onSubmitScript->addLine("if (result) {");
			for ($i = 0; $i < count($this->privateVars['fields']); $i++) {
				if (is_Object($this->privateVars['fields'][$i])) {
					$field =& $this->privateVars['fields'][$i];
					$field->getOnSubmitScript($onSubmitScript);
				}
			}
			$onSubmitScript->addLine("}");
			$onSubmitScript->addLine("return result;");
			$onSubmitScript->endBlock();
			//print $onSubmitScript->toString();

			$initScript = $this->getFormInitScript();
			//print $initScript->toString();

			$onChangeScript = new JavaScript();
			$onChangeScript->addLine("var form".$this->id."IsChangedBool = false;");
			$onChangeScript->startFunction("form".$this->id."IsChanged");
			$onChangeScript->addLine("form".$this->id."IsChangedBool = true;");
			$onChangeScript->addLine("onForm".$this->id."Change();");
			$onChangeScript->endBlock();

			$onChangeScript->startFunction("onForm".$this->id."Change");
			foreach($this->privateVars["onchange"] AS $onChangeSubScript) {
				$onChangeScript->mergeScript($onChangeSubScript);
			}
			$onChangeScript->endBlock();

			$onChangeScript->startFunction("isForm".$this->id."Changed");
			$onChangeScript->addLine("return form".$this->id."IsChangedBool;");
			$onChangeScript->endBlock();

			$formScript = new Javascript();
			$formScript->mergeScript($onSubmitScript);
			$formScript->mergeScript($initScript);
			$formScript->mergeScript($onChangeScript);


			for ($i = 0; $i < count($this->privateVars['fields']); $i++) {
				if (is_object($this->privateVars['fields'][$i])) {
					$field = $this->privateVars['fields'][$i];
					if ($field->hasAttachedScript()) {
						$formScript->mergeScript($field->getAttachedScript());
					}
				}
			}
			print $formScript->toString();
		}

		function getFormContents($startTab = 1, $width="", $style="") {
			ob_start();
			$this->writeForm($startTab, $width, $style);
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		}

		function writeForm($startTab = 1, $width="", $style="") {
			global $formSkin;
			$formSkin = true;
			for ($i = 0; $i < count($this->privateVars['fieldgroups']); $i++) {
				$this->privateVars['fieldgroups'][$i]->onFormOutput($this);
			}

			if (isSet($GLOBALS["ivFormLayoutSupplier"])) {
				$formLayout = $GLOBALS["ivFormLayoutSupplier"]->getLayout($this->id);
				if (is_Object($formLayout)) {
					$this->writeFormSkinned($formLayout, $startTab);
					return;
				}
			}
			$this->writeFormTable($startTab, "", $width, $style);
		}

		function writeFormSkinned($formLayout, $startTab) {
			$this->privateVars['tabIndex'] = $startTab;
			ob_start();
			$this->writeFormStart();
			$formStart = ob_get_contents();
			ob_end_clean();
			$formEnd = "</form>";

			$this->activeFormLayout = $formLayout;

			ob_start();
			$index = 0;
			$hasRequiredFields = false;
			for ($i = 0; $i < count($this->privateVars['fields']); $i++) {
				if (!is_Object($this->privateVars['fields'][$i])) {
					$field =& $this->privateVars['fields'][$i];
					$field['form-index'] = $index;
					$handled = false;
					for ($j = 0; $j < count($this->privateVars['fieldgroups']); $j++) {
						$fieldGroup =& $this->privateVars['fieldgroups'][$j];
						if ($fieldGroup->hasFieldType($field['type']))  {
							$fieldGroup->writeField($this, $field['type'], $field);
							$handled = true;
							break;
						}
					}
					if ($handled) $index++;
				} else {
					$field =& $this->privateVars['fields'][$i];
					$field->componentIndex = $index;
					if ($field->isRequired()) $hasRequiredFields = true;
					$field->printComponent();
					if ($field->visible) $index++;
				}
			}
			$fields = ob_get_contents();
			ob_end_clean();

			$required = ($hasRequiredFields) ? $this->activeFormLayout->getRequiredLine() : "";

			$borderSkin = $formLayout->getBorder();
			$result = str_replace("%formstart%", $formStart, $borderSkin);
			$result = str_replace("%formend%", $formEnd, $result);
			$result = str_replace("%fields%", $fields, $result);
			$result = str_replace("%required%", $required, $result);
			print $result;
			$this->writeFormJavascript();
		}

		function notifyBeforeWriting() {
			for ($i = 0; $i < count($this->privateVars['fields']); $i++) {
				if (is_Object($this->privateVars['fields'][$i])) {
					$field = $this->privateVars['fields'][$i];
					$field->notifyWriting();
				}
			}
		}

		/**
		* Writes the form as a Menu
		*@param int $startTab, the tabindex
		*@param string $menuClass the CSS class of the menu
		**/
		function writeMenu($startTab = 1, $menuClass="") {
			global $formSkin;
			$formSkin = false;
			for ($i = 0; $i < count($this->privateVars['fieldgroups']); $i++) {
				$this->privateVars['fieldgroups'][$i]->onFormOutput($this);
			}
			$this->writeFormTable($startTab,$menuClass);
		}

		function printInputField($field, $extra, $inputField, $rowClass) {
			global $formSkin;
			if($formSkin) {
				?>
				<tr class="<?=$rowClass; ?> <?=($field['form-index'] % 2 == 0) ? 'fr1' : 'fr2'; ?>"<?=isSet($field['idMark']) ? " id=\"row".$field["idMark"].$field["markIndex"]."\"" : "" ?><?=(isSet($field['idMark']) && isSet($this->privateVars['markHidden'][$field['idMark']])) ? " style=\"display: none;\"" : "" ?>>
					<td class="fname">
						<div class="flabel"><span class="fieldtitle"><?=((strLen($field['title']) > 0) ? $field['title'].':' : '')  ?><br /></span></div>
						<div class="fdesc"><small class="fielddesc"><?=$field['description'] ?></small></div>
						<?=$extra; ?>
					</td>
					<td class="finput" <?= !(isset($this->privateVars['required']) && $this->privateVars['required']['bool'] && isset($field['required']) && $field['required']) ? "colspan=\"2\"" : "" ?>>
						<?=$inputField  ?>
					</td>
					<?php
					if ((isset($this->privateVars['required']) && $this->privateVars['required']['bool'] && isset($field['required']) && $field['required'])) {
					?>
						<td class="frequired">
							<?=  ((isset($this->privateVars['required']) && $this->privateVars['required']['bool'] && isset($field['required']) && $field['required']) ? "<span class=\"".$this->privateVars['required']['class']."\">".$this->privateVars['required']['char'] : "&nbsp;") ?>
						</td>
					<?php
					}
					?>
				</tr>
				<?php
			} else {
				print "<!--".$inputField."-->";
				?>

				<span class="menuitem">
					<?=$inputField;?>
				</span>
				<?php
			}
		}

		function printComponentHidden(&$formComponent) {
			print $formComponent->getInput();
		}

		function printSkinnedFormComponent(&$formComponent) {
			$total = count($this->privateVars['fields']);
			$formType = $formComponent->rowClass;
			$typeOrder = 1;
			if (isSet($this->formStyleCounts[$formType])) {
				$this->formStyleCounts[$formType] += 1;
				$typeOrder = $this->formStyleCounts[$formType];
			} else {
				$this->formStyleCounts[$formType] = 1;
			}
			$index = $formComponent->componentIndex + 1;

			$layout = $this->activeFormLayout->getFieldSkin($index, $total,
				$formType, $typeOrder);
			if ($layout !== false) {

				$fieldID = ($formComponent->markGroup != "") ? " id=\"row".$formComponent->markGroup.$formComponent->markIndex."\"" : "";
				$fieldID .= (($formComponent->markGroup != "") && isSet($this->privateVars['markHidden'][$formComponent->markGroup])) ? " style=\"display: none;\"" : "";

				$title = ((strLen($formComponent->title) > 0) ? convertTextCharacters($formComponent->title) : '');
				$description = convertTextCharacters($formComponent->description);
				$extra = $formComponent->getExtra();
				$field = $formComponent->getInput();
				$required = $formComponent->isRequired();
				$requiredMark = ($required) ? $this->activeFormLayout->getRequiredMark() : "";

				$layout = str_Replace("%uniqueID%", uniqID("sff"), $layout);

				$layout = str_Replace("%fieldID%", $fieldID, $layout);
				$layout = str_Replace("%title%", $title, $layout);
				$layout = str_Replace("%description%", $description, $layout);
				$layout = str_Replace("%extra%", $extra, $layout);
				$layout = str_Replace("%field%", $field, $layout);
				$layout = str_Replace("%required%", $requiredMark, $layout);

				print $layout;

			} else {
				print "Skin niet gevonden!";
			}
		}

		function printComponentStandard(&$formComponent) {
			if (is_Object($this->activeFormLayout)) {
				$this->printSkinnedFormComponent($formComponent);
				return;
			}
			global $formSkin;
			if($formSkin) {
				?>
					<tr class="<?=$formComponent->rowClass; ?> <?=($formComponent->componentIndex % 2 == 0) ? 'fr1' : 'fr2'; ?>"<?=($formComponent->markGroup != "") ? " id=\"row".$formComponent->markGroup.$formComponent->markIndex."\"" : "" ?><?=(($formComponent->markGroup != "") && isSet($this->privateVars['markHidden'][$formComponent->markGroup])) ? " style=\"display: none;\"" : "" ?>>
						<td class="fname">
							<div class="flabel"><span class="fieldtitle"><?=((strLen($formComponent->title) > 0) ? convertTextCharacters($formComponent->title).':' : '') ?><br /></span></div>
							<div class="fdesc"><small class="fielddesc"><?=convertTextCharacters($formComponent->description) ?></small></div>
							<?= $formComponent->getExtra(); ?>
						</td>
						<td class="finput" <?= !(isset($this->privateVars['required']) && $this->privateVars['required']['bool'] && $formComponent->required) ? "colspan=\"2\"" : "" ?>>
							<?= $formComponent->getInput() ?>
						</td>
						<?php
						if((isset($this->privateVars['required']) && $this->privateVars['required']['bool'] && $formComponent->required)) {
						?>
							<td class="frequired">
								<?= ((isset($this->privateVars['required']) && $this->privateVars['required']['bool'] && $formComponent->required) ? "<span class=\"".$this->privateVars['required']['class']."\">".$this->privateVars['required']['char'] ."</span>" : "&nbsp;") ?>
							</td>
						<?php
						}
						?>
					</tr>
				<?php
			} else {
				?>
				<span class="menuitem">
					<?= $formComponent->getInput(); ?>
				</span>
				<?php
			}
		}

		//
		function printComponentWide(&$formComponent) {
			global $formSkin;
			if($formSkin) {
				?>
					<tr class="<?=$formComponent->rowClass; ?> <?=($formComponent->componentIndex % 2 == 0) ? 'fr1' : 'fr2'; ?>"<?=($formComponent->markGroup != "") ? " id=\"row".$formComponent->markGroup.$formComponent->markIndex."\"" : "" ?><?=(($formComponent->markGroup != "") && isSet($this->privateVars['markHidden'][$formComponent->markGroup])) ? " style=\"display: none;\"" : "" ?>>
						<td class="fwide" <?= !(isset($this->privateVars['required']) && $this->privateVars['required']['bool'] && $formComponent->required) ? "colspan=3" : "colspan=\"2\"" ?>>
							<?= $formComponent->getInput() ?>
						</td>
						<?php
						if((isset($this->privateVars['required']) && $this->privateVars['required']['bool'] && $formComponent->required)) {
						?>
							<td class="frequired">
								<?= ((isset($this->privateVars['required']) && $this->privateVars['required']['bool'] && $formComponent->required) ? "<span class=\"".$this->privateVars['required']['class']."\">".$this->privateVars['required']['char'] ."</span>" : "&nbsp;") ?>
							</td>
						<?php
						}
						?>
					</tr>
				<?php
			} else {
				?>
				<span class="menuitem">
					<?= $formComponent->getInput(); ?>
				</span>
				<?php
			}
		}
		//

		function getEmptyPostData() {
			$postData = array();
			$postData['formName'] = $this->id;
			$postData['checkSubmit'] = $this->id."-check";
			return $postData;
		}

		function hasValue($inputName, $inputType, $postData = null) {
			if ($postData == null) $postData = $_POST;
			if ($inputType == 'checkboxes') {
				if(!$this->ignorePostData) {
					if (isSet($postData['formName']) && ($postData['formName'] == $this->id)) return true;
				}
			}
			$hasValue = false;
			if(!$this->ignorePostData) {
				$hasValue = ((isSet($postData['formName'])) && ($postData['formName'] == $this->id) && isSet($postData[$inputName])) ? true : false;
			}
			if (!$hasValue) {
				$hasValue = isSet($this->privateVars['values'][$inputName]);
			}
			return $hasValue;
		}

		function getValue($inputName, $inputType, $postData = null, $expectedValue = null) {
			if ($postData == null) $postData = $_POST;

			if ($inputType == 'checkboxes') {
				if(!$this->ignorePostData) {
					if (isSet($postData['formName']) && ($postData['formName'] == $this->id)) {
						if(strPos($inputName,"[]") !== false) {
							$checkboxPostDataName = subStr($inputName,0,strLen($inputName)-2);
							if(isSet($postData[$checkboxPostDataName])) {
								$checkboxPostData = $postData[$checkboxPostDataName];
								if($expectedValue != null) return (array_search($expectedValue,$checkboxPostData) !== false);
							}
							return false;
						} else return isSet($postData[$inputName]);
					}
				}
				if (isSet($this->privateVars['values'][$inputName])) {
					return ($this->privateVars['values'][$inputName]);
				}
			} else if($inputType == "float") {
				if(!$this->ignorePostData) {
					if(isSet($postData[$inputName])) {
						return str_replace(",",".",$postData[$inputName]);
					}
				}
				if (isSet($this->privateVars['values'][$inputName])) {
					return str_replace(",",".",$this->privateVars['values'][$inputName]);
				}
			}

			if(!$this->ignorePostData) {
				if (isSet($postData[$inputName])) {
					// when slashes seem to appear, check the magic_quotes_gpc setting.
					// this should be turned off (php.ini)
					return $postData[$inputName];
				}
			}

			if (isSet($this->privateVars['values'][$inputName])) {
				return $this->privateVars['values'][$inputName];
			}
			return "";
		}

		function setValue($varName, $value) {
			$this->privateVars['values'][$varName] = $value;
		}

		function getTabIndex() {
			return $this->privateVars['tabIndex'];
		}

		function increaseTabIndex() {
			$this->privateVars['tabIndex']++;
		}

		/**
		 * Checks the validity of the form fields
		 *
		 * @param Messages $feedback
		 * @param array $values
		 * @return boolean
		 */
		function checkPostedFields(&$feedback, $values = null) {
			if ($values == null) $values = $_POST;
			importClass("util.Language");

			$noValueError = ivMLGS("library", 16, "Veld `%s` is verplicht maar niet ingevuld!");
			//This code makes a list of all markings that are REALLY hidden when posting the form
			$hideFields = array();
			for ($i = 0; $i < count($this->privateVars['fields']); $i++) {
				$field =& $this->privateVars['fields'][$i];
				if (is_Object($field) && (strToLower(get_Class($field)) == "formradiogroup")) {
					for ($j = 0; $j < $field->getComponentCount(); $j++) {
						$radioOption = $field->getComponent($j);
						if (strToLower(get_Class($radioOption)) == "formradioviewhidebutton") {
							if($radioOption->isSelected())
								$hideFields = array_merge($hideFields, $radioOption->getHideMarkings());
						}
					}
				} elseif (!is_Object($field) && isset($field['type']) && $field['type'] == 'radio') {
					$value = $this->getValue($field['name'], $field['type'], $values);
					if(isset($field['options'])) {
						for ($var = 0; $var < count($field['options']); $var++) {
							if ($field['options'][$var]['value'] == $value) {
								if(isset($field['options'][$var]['hide'])) {
									$hideFields = array_merge($hideFields, $field['options'][$var]['hide']);
								}
							}
						}
					}
				}
			}
			//endtemp
			for ($i = 0; $i < count($this->privateVars['fields']); $i++) {
				$field =& $this->privateVars['fields'][$i];
				if ((!is_Object($field)) && isSet($field['required']) && ($field['required'] == true) && isSet($field['name']) && (!isSet($field['idMark']) || !in_array($field['idMark'],$hideFields))) {
					$title = htmlConvert($field['title']);
					if ($this->hasValue($field['name'], $field['type'], $values)) {
						$value = $this->getValue($field['name'], $field['type'], $values);
						if (strLen(trim($value)) == 0) {
							$this->setFocus($field['name'], null);
							$feedback->addMessage(sprintf($noValueError, $title));
							return false;
						}
					} else {

						$feedback->addMessage(sprintf($noValueError, $title));
						$this->setFocus($field['name'], null);
						return false;
					}

				}
				if (is_Object($field)) {
					$title = htmlConvert($field->errorDisplayName);
					$title = str_replace("\n", "\\n", $title);
					$title = str_replace("\r", "", $title);
					if (!$field->hasValidValue($values) && !(in_array($field->markGroup, $hideFields))) {

						$feedback->addMessage(sprintf(ivMLGS("library", 17, "Fout bij %1\$s: %2\$s"), $title, $field->getErrorMessage()));
						$this->setFocus($field->identifier, $field->rowClass);
						return false;
					}
					if ( !(in_array($field->markGroup, $hideFields)) && ($field->required) && (!$field->hasValue($values))) {
						$feedback->addMessage(sprintf($noValueError, $title));
						$this->setFocus($field->identifier, $field->rowClass);
						return false;
					}
				}
			}
			return true;
		}

		function getComponentByIdentifier($identifier) {
			for ($i = 0; $i < count($this->privateVars['fields']); $i++) {
				$field =& $this->privateVars['fields'][$i];
				if (is_Object($field)) {
					if ($field->identifier == $identifier) return $field;
				}
			}
			return false;
		}

	}

	function includeFormComponents() {
		$arg_list = func_get_args();
		for ($i = 0; $i < count($arg_list); $i++) {
			importClass("interface.formcomponents.".$arg_list[$i]);
		    //require_once($libraryClassDir . "formcomponents/" . $arg_list[$i] . ".class.php");
		}
	}
?>
