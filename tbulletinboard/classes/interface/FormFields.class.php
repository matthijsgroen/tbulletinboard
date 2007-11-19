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
	 * Import functionality to convert text to html.
	 */
	importClass("util.Language");
	importClass("interface.Form");

	/**
	 * StandardFormFields is a class containing the most usefull and
	 * generic FormClasses that can be used in almost every project.
	 */
	class StandardFormFields extends FormFieldGroup {

		var $activeForm;

		function StandardFormFields() {
		}

		function hasFieldType($type) {
			switch($type) {
				case 'catselect':
				case 'checkboxes':
				case 'checkviewhide':
				case 'password':
				case 'radio':
				case 'select':
				case 'submit':
				case 'text':
				case 'textfield':
				case 'title':
				case 'upload':
				case 'multifield':
				case 'textblockfield':
				case 'timefield':
				case 'datefield':
				case 'multiselect':
				case 'htmlfield':
					return true;
				default:
					return false;
			}
		}

		function writeField(&$form, $type, $fieldData) {
			switch($type) {
				case 'catselect': $this->writeCatSelect($form, $fieldData); break;
				case 'checkboxes': $this->writeCheckboxes($form, $fieldData); break;
				case 'checkviewhide' : $this->writeCheckViewHide($form, $fieldData); break;
				case 'password': $this->writeTextfield($form, $fieldData); break;
				case 'radio': $this->writeRadio($form, $fieldData); break;
				case 'select': $this->writeSelect($form, $fieldData); break;
				case 'submit': $this->writeSubmit($form, $fieldData); break;
				case 'text': $this->writeText($form, $fieldData); break;
				case 'textfield': $this->writeTextfield($form, $fieldData); break;
				case 'title': $this->writeTitle($form, $fieldData); break;
				case 'upload': $this->writeUploadfield($form, $fieldData); break;
				case 'multifield': $this->writeMultifield($form, $fieldData); break;
				case 'textblockfield': $this->writeTextblockfield($form, $fieldData); break;
				case 'timefield': $this->writeTimefield($form, $fieldData); break;
				case 'datefield': $this->writeDatefield($form, $fieldData); break;
				case 'multiselect': $this->writeMultiSelect($form, $fieldData); break;
				case 'htmlfield': $this->writeHTMLfield($form, $fieldData); break;

			}
		}

		/**
		 * Adds the field data to the active form. This method allows us to eventually insert default data
		 */
		function p_addField($data = array()) {
			$defaults = array();
			$data = array_merge($defaults, $data);
			$this->activeForm->addField($data);
		}

		function startGroup($title) {
			$this->p_addField(array('type' => 'title', 'title' => $title));
		}

		function endGroup() {
			$this->p_addField(array('type' => 'endgroup'));
		}

		function addHTMLfield($name) {
			$this->p_addField(array('type' => 'htmlfield', 'name' => $name));
		}

		function addTextfield($name, $title, $description, $maxlength, $required=false, $disabled=false) {
			$this->p_addField(array('type' => 'textfield', 'title' => $title, 'name' => $name,
				'description' => $description, 'maxlength' => $maxlength, 'htmltype' => 'text', 'required' => $required, 'disabled' => $disabled));
		}

		function addPasswordfield($name, $title, $description, $maxlength, $required=false, $disabled=false, $showValue=false) {
			$this->p_addField(array('type' => 'password', 'title' => $title, 'name' => $name,
				'description' => $description, 'maxlength' => $maxlength, 'htmltype' => 'password', 'required' => $required, 'disabled' => $disabled, 'showvalue' => $showValue));
		}

		/**
		 * Create a selection field with a list of options
		 *@param string $name the name of the variable to store the data in
		 *@param string $title the title of the field to show the user
		 *@param string $description extra text for examples or extra help
		 *@param array $options a list of options. The key of the array is the value, and the value is the caption
		 *@param string $defaultOption the value of the selected option
		 *@param bool $required true if the field requires a selection, false otherwise
		 */
		function addSelect($name, $title, $description, $options, $defaultOption, $required=false, $disabled=false, $onChange="") {
			$this->p_addField(array('type' => 'select', 'title' => $title, 'name' => $name,
				'description' => $description, 'options' => $options, 'default' => $defaultOption, 'required' => $required, 'disabled' => $disabled, 'onchange' => $onChange));
		}

		/**
		 * Creates a radio field with different text options
		 *@param string $name the name of the variable to store the data in
		 *@param string $title the title of the field to show the user
		 *@param string $description extra text for examples or extra help
		 *@param array $options a list of options. The options is an array, containing a subarray for each item.
		 *  this subarray has the following keys set: value, caption and description
		 *@param string $defaultOption the value of the selected option
		 *@param bool $required true if the field requires a selection, false otherwise
		 */
		function addRadio($name, $title, $description, $options, $defaultOption, $required=false) {
			$this->p_addField(array('type' => 'radio', 'title' => $title, 'name' => $name,
				'description' => $description, 'options' => $options, 'default' => $defaultOption, 'required' => $required,
				'showhide' => false));
		}

		/**
		 * Creates a radio field with different text options, and allow other form fields to hide/show depending
		 * on the selected item
		 *@param string $name the name of the variable to store the data in
		 *@param string $title the title of the field to show the user
		 *@param string $description extra text for examples or extra help
		 *@param array $options a list of options. The options is an array, containing a subarray for each item.
		 *  this subarray has the following keys set: value, caption, description, show and hide
		 *  where show and hide also are arrays containing the names of the groups to show/hide
		 *@param string $defaultoption the value of the selected option
		 *@param bool $required true if the field requires a selection, false otherwise
		 */
		function addRadioViewHide($name, $title, $description, $options, $defaultOption, $required=false) {
			$this->p_addField(array('type' => 'radio', 'title' => $title, 'name' => $name,
				'description' => $description, 'options' => $options, 'default' => $defaultOption, 'required' => $required,
				'showhide' => true));
		}

		function addCheckViewHide($name, $title, $description, $options, $checked=false, $required=false) {
			$this->p_addField(array('type' => 'checkviewhide', 'title' => $title, 'name' => $name,
				'description' => $description, 'options' => $options, 'checked' => $checked, 'required' => $required));
		}

		function addMultifield($name, $title, $description, $separator=" ", $required=false) {
			$this->p_addField(array(
				'type' => 'multifield',
				'title' => $title,
				'description' => $description,
				'name' => $name,
				'required' => $required,
				'separator' => $separator
			));
		}

		/**
		 * Adds a MultiSelect field. A MultiSelect field is 2 select boxes, where the options of box 1 can be transfered to box 2.
		 * The Items in Box 2 are the selected options, and will be send as POST data using a hiddenfield with the option values separated
		 * with the specified separator.
		 *@param string $name name of the hiddenfield that sends the selected item values
		 *@param string $title title to display for this fiels
		 *@param string $description fielddescription, containing a help or hint text
		 *@param array $options An array containing the options to choose. The array index is the value, the array value is the caption.
		 *@param string $separator the separator to place between the chosen option values
		 *@param bool $required true if minimal one options has to be chosen, <code>false</code> otherwise.
		 */
		function addMultiSelect($name, $title, $description, $options, $separator=" ", $required=false) {
			$this->p_addField(array(
				'type' => 'multiselect',
				'title' => $title,
				'description' => $description,
				'name' => $name,
				'options' => $options,
				'required' => $required,
				'separator' => $separator
			));
		}

		function addText($title, $description, $value, $required=false) {
			$this->p_addField(array('type' => 'text', 'title' => $title, 'description' => $description, 'value' => $value, 'required' => $required));
		}

		function addTextBlockField($name, $title, $description, $rows, $required=false) {
			$this->p_addField(array('type' => 'textblockfield', 'title' => $title, 'name' => $name,
				'description' => $description, 'rows' => $rows, 'required' => $required));
		}

		function addSubmit($caption, $showBack, $disabled = false) {
			$backLevel = 1;
			if (isSet($_POST['formBackLevel'])) {
				$backLevel = htmlConvert($_POST['formBackLevel']);
			}
			$this->activeForm->addHiddenField('formBackLevel', $backLevel+1);

			$this->p_addField(array('type' => 'submit', 'caption' => $caption, 'showBack' => $showBack,
				'backLevel' => $backLevel, 'disabled' => $disabled));
		}


		/**
		 * Adds a set of checkboxes to the form
		 *@param string $title the title of the field
		 *@param string $description the description of the field
		 *@param array $options an array with options. Every option is an associative array
		 * Associative variables
		 * - value (string)
		 * - caption (string)
		 * - description (string)
		 * - name (string)
		 * - checked (boolean)
		 * - onclick (string)
		 */
		function addCheckboxes($title, $description, $options) {
			$this->p_addField(array('type' => 'checkboxes', 'title' => $title, 'description' => $description, 'options' => $options));
		}

		function addUploadField($name, $title, $description, $required=false) {
			$this->p_addField(array('type' => 'upload', 'name' => $name, 'title' => $title, 'description' => $description, 'required' => $required));
			$this->activeForm->encType = "multipart/form-data";
		}

		function addImageUploadField($name, $title, $filetypes, $maxKbSize, $maxX = 0, $maxY = 0, $required=false) {
			$description = sprintf('%s: %skB<br />%s: %s', ivMLGS("library", 2, "Maximum size"), $maxKbSize, ivMLGS("library", 3, "Format"), $filetypes);

			if ($maxY > 0 || $maxX > 0) {
				$description .=
					'<br /><abbr title="'.ivMLGS("library", 4, "Maximum").'">'.ivMLGS("library", 5, "Max.").'</abbr>'.
					' <abbr title="'.ivMLGS("library", 6, "resolution").'">'.ivMLGS("library", 7, "res.").'</abbr>: '.$maxX.'x'.$maxY;
			}

			$this->activeForm->addHiddenField("MAX_FILE_SIZE", ($maxKbSize * 1024));
			$this->p_addField(array('type' => 'upload', 'name' => $name, 'title' => $title, 'description' => $description, 'required' => $required));
			$this->activeForm->encType = "multipart/form-data";
		}

		function addCategorySelect($name, $title, $description, $options, $defaultOption, $required=false) {
			$this->p_addField(array('type' => 'catselect', 'title' => $title, 'name' => $name,
				'description' => $description, 'options' => $options, 'default' => $defaultOption, 'required' => $required));
		}

		function addTimefield($name, $title, $description, $required=false) {
			$this->p_addField(array('type' => 'timefield', 'title' => $title, 'name' => $name,
				'description' => $description, 'required' => $required));
		}

		function addDatefield($name, $title, $description, $required=false) {
			$this->p_addField(array('type' => 'datefield', 'title' => $title, 'name' => $name,
				'description' => $description, 'required' => $required));
		}

		function writeTitle(&$form, $field) {
?>
						<tr<?=isSet($field['idMark']) ? " id=\"row".$field["idMark"].$field["markIndex"]."\"" : "" ?><?=(isSet($field['idMark']) && isSet($form->privateVars['markHidden'][$field['idMark']])) ? " style=\"display: none;\"" : "" ?>>
							<th colspan="3" class="formtitle"><?=$field['title'] ?></th>
						</tr>
<?php
		}

		function writeHTMLfield(&$form, $field) {
	?>
		<tr<?=isSet($field['idMark']) ? " id=\"row".$field["idMark"].$field["markIndex"]."\"" : "" ?><?=(isSet($field['idMark']) && isSet($form->privateVars['markHidden'][$field['idMark']])) ? " style=\"display: none;\"" : "" ?>>
			<th colspan="3" class="htmlfield"><?=$form->getValue($field['name'], $field['type'])?></th>
		</tr>
	<?php
		}

		function writeTextfield(&$form, $field) {
			$extra = "";
			$rowClass = 'ft-'.$field['htmltype'];
			$inputField = sprintf(
				'<input type="%s" name="%s" id="%s" maxlength="%s" %stabindex="%s" %s/>',
				$field['htmltype'],
				$field['name'],
				$field['name'],
				$field['maxlength'],
				((($field['htmltype'] != 'password') || (isSet($field['showvalue']) && $field['showvalue'])) && ($form->hasValue($field['name'], $field['type']))) ?
					'value="'.htmlConvert($form->getValue($field['name'], $field['type'])).'" ' : '',
				$form->getTabIndex(),
				($field['disabled']) ? 'disabled="disabled"': ''
			);
			$form->printInputField($field, $extra, $inputField, $rowClass);
			$form->increaseTabIndex();
		}

		function writeSelect(&$form, $field) {
			$selectedValue = $field['default'];

			if ($form->hasValue($field['name'], $field['type'])) {
				$selectedValue = $form->getValue($field['name'], $field['type']);
			}
			$extra = "";
			$rowClass = "ft-select";
			$optionsStr = '';
			reset($field['options']);
			while (list($value, $caption) = each($field['options'])) {
				$optionsStr .= sprintf(
					'<option value="%s"%s>%s</option>' . "\n",
					$value,
					($value == $selectedValue) ? " selected=\"selected\"" : "",
					$caption
				);
			}
			$inputField = sprintf(
				'<select name="%s" id="%s" tabindex="%s" onchange="%s" %s>%s</select>',
				$field['name'],
				$field['name'],
				$form->getTabIndex(),
				$field['onchange'],
				($field['disabled']) ? 'disabled="disabled"' : '',
				$optionsStr
			);
			$form->printInputField($field, $extra, $inputField, $rowClass);
			$form->increaseTabIndex();
		}

		function writeRadio(&$form, $field) {
			$selectedValue = $field['default'];

			if ($form->hasValue($field['name'], $field['type'])) {
				$selectedValue = $form->getValue($field['name'], $field['type']);
			}
			$fieldStr = ""; $jScript = "";
			$enter = "\n";
			$tab = "\t";
			$optFunc = uniqID("rbo");
			$extra = "";
			$rowClass = "ft-radio";
			$radioFields = "";
			for ($i = 0; $i < count($field['options']); $i++) {
				$option = $field['options'][$i];
				$radioFields .= sprintf(
					'<input class="radio" id="%s" value="%s"%s name="%s" type="radio" tabindex="%s" %s/>'."\n",
					$field['name'].$i,
					$option['value'],
					($option['value'] == $selectedValue) ? " checked=\"checked\"" : "",
					$field['name'],
					$form->getTabIndex(),
					(isSet($option["show"]) || isSet($option["hide"])) ? "onfocus=\"".$optFunc."()\" onclick=\"".$optFunc."()\" " : ""
				);
				if (isSet($option["show"]) || isSet($option["hide"])) {
					$jScript .= $tab."if (checkValue == '".$option["value"]."') {".$enter;
					//$jScript .= $tab.$tab."alertML('Waarde is ".$option["value"]."');".$enter;
					if (isSet($option["hide"])) {
						for ($x = 0; $x < count($option["hide"]); $x++) {
							$hideName = $option["hide"][$x];
							$nrRows = $form->getMarkCount($hideName);
							for ($y = 0; $y < $nrRows; $y++) {
								$jScript .= $tab.$tab."var item = document.getElementById('row".$hideName.$y."');".$enter;
								$jScript .= $tab.$tab."if (item != null) item.style.display = 'none';".$enter;
							}
						}
					}
					if (isSet($option["show"])) {
						for ($x = 0; $x < count($option["show"]); $x++) {
							$hideName = $option["show"][$x];
							$nrRows = $form->getMarkCount($hideName);
							for ($y = 0; $y < $nrRows; $y++) {
								$jScript .= $tab.$tab."var item = document.getElementById('row".$hideName.$y."');".$enter;
								$jScript .= $tab.$tab."if (item != null) item.style.display = '';".$enter;
							}
						}
					}
					$jScript .= $tab."}".$enter;
					$jScript .= "".$enter;
					$jScript .= $enter;
				}
				$radioFields .= sprintf(
					'<label for="%s">%s</label>'."\n",
					$field['name'].$i,
					$option['caption']
				);
				$radioFields .= sprintf(
					'<small class="optionComment">%s</small><br />'."\n",
					$option['description']
				);
				$form->increaseTabIndex();
			}

			if (isSet($field["showhide"]) && ($field["showhide"] == true)) {
				// script
				$fieldStr = '<script type="text/javascript"><!--'.$enter;
				$fieldStr .= 'function '.$optFunc.'() {'.$enter;
				$fieldStr .= $tab.'var field = document.'.$form->id.'.'.$field["name"].';'.$enter;
				$fieldStr .= $tab.'var checkValue = \'\';'.$enter;
				$fieldStr .= $tab.'for (i = 0; i < field.length; i++) { if (field[i].checked == true) checkValue = field[i].value; }'.$enter;
				//$fieldStr .= $tab.'alertML(\'Optie veranderd! \'+checkValue);'.$enter;
				$fieldStr .= $jScript;
				$fieldStr .= '}'.$enter;
				$fieldStr .= '// -->'.$enter;
				$fieldStr .= '</script>'.$enter;
			}


			$form->printInputField($field, $extra, $fieldStr . $radioFields, $rowClass);
		}

		function writeMultifield(&$form, $field) {
			$addFieldName = uniqId('af');
			$addButtonName = uniqId('ab');
			$delButtonName = uniqId('db');
			$listName = uniqId('lo');
			$addFunction = uniqId('ja');
			$delFunction = uniqId('jd');
			$updateFunction = uniqId('ju');

			$value = "";
			if ($form->hasValue($field['name'], $field['type'])) $value = $form->getValue($field['name'], $field['type']);

			$extra = "";
			$fieldStr = "";
			$enter = "\n";
			$tab = "\t";
			// script
			$fieldStr .= '<script type="text/javascript" src="javascript/form.js"></script>'.$enter;
			$fieldStr .= '<script type="text/javascript"><!--'.$enter;
			// add function
			$fieldStr .= 'function '.$addFunction.'() {'.$enter;
			$fieldStr .= $tab."var addValue = trim(document.".$form->id.".".$addFieldName.".value);".$enter;
			$fieldStr .= $tab."if (addValue.length == 0) {".$enter;
			$fieldStr .= $tab.$tab."alertML('Er is geen waarde gegeven!');".$enter;
			$fieldStr .= $tab.$tab."return;".$enter;
			$fieldStr .= $tab."}".$enter;
			$fieldStr .= $tab."".$enter;
			// Add the option
			$fieldStr .= $tab.'var list = document.'.$form->id.'.'.$listName.';'.$enter;
			$fieldStr .= $tab."var option = new Option(addValue, addValue);".$enter;
			$fieldStr .= $tab."var index = list.length;".$enter;
			$fieldStr .= $tab."list.options[index] = option;".$enter;
			$fieldStr .= $tab."document.".$form->id.".".$addFieldName.".value = '';".$enter;
			$fieldStr .= "".$enter;
			$fieldStr .= $tab."document.".$form->id.".".$field['name'].".value = implode('".$field['separator']."', list);";
			$fieldStr .= '}'.$enter;
			$fieldStr .= "".$enter;

			// del function
			$fieldStr .= 'function '.$delFunction.'() {'.$enter;
			$fieldStr .= $tab.'var list = document.'.$form->id.'.'.$listName.';'.$enter;
			$fieldStr .= $tab.'deleteSelected(list);';
			$fieldStr .= $tab."document.".$form->id.".".$field['name'].".value = implode('".$field['separator']."', list);";
			$fieldStr .= '}'.$enter;
			$fieldStr .= "".$enter;

			$fieldStr .= '// -->'.$enter;
			$fieldStr .= '</script>'.$enter;

			// field
			$fieldStr .= sprintf('<input name="%s" type="text" class="addvalue" tabindex="%s" /><br />'."\n", $addFieldName,	$form->getTabIndex());
			$form->increaseTabIndex();
			$fieldStr .= sprintf('<button name="%s" onclick="%s()" type="button" tabindex="%s">Toevoegen</button>'."\n", $addButtonName, $addFunction, $form->getTabIndex());
			$form->increaseTabIndex();
			$fieldStr .= sprintf('<button name="%s" onclick="%s()" type="button" tabindex="%s">Verwijderen</button><br />'."\n", $delButtonName, $delFunction, $form->getTabIndex());
			$form->increaseTabIndex();
			$optionValues = "";
			$options = explode($field['separator'], $value);
			for ($i = 0; $i < count($options); $i++) {
				$option = $options[$i];
				if (strLen(trim($option)) > 0)
					$optionValues .= sprintf(
						'<option value="%1$s">%1$s</option>'."\n",
						htmlConvert($option)
					);
			}
			$fieldStr .= sprintf('<select name="%s" multiple="multiple" size="3" class="multivalue" tabindex="%s">'."\n".'%s</select>'."\n",
				$listName,$form->getTabIndex(),
				$optionValues
			);
			$form->increaseTabIndex();
			$fieldStr .= sprintf('<input type="hidden" name="%s" value="%s" />', $field['name'], htmlConvert($value));

			$form->printInputField($field, $extra, $fieldStr, 'ft-multifield');
		}

		function writeMultiSelect(&$form, $field) {

			$addFieldName = uniqId('af');
			$addButtonName = uniqId('ab');
			$delButtonName = uniqId('db');

			$list1Name = uniqId('so');
			$list2Name = uniqId('lo');
			$addFunction = uniqId('ja');
			$delFunction = uniqId('jd');
			$updateFunction = uniqId('ju');

			$value = "";
			if ($form->hasValue($field['name'], $field['type'])) $value = $form->getValue($field['name'], $field['type']);

			$extra = "";
			$fieldStr = "";
			$enter = "\n";
			$tab = "\t";
			// script
			$fieldStr .= '<script type="text/javascript" src="javascript/form.js"></script>'.$enter;
			$fieldStr .= '<script type="text/javascript"><!--'.$enter;
			// add function
			$fieldStr .= 'function '.$addFunction.'() {'.$enter;
			$fieldStr .= $tab."var list1 = document.".$form->id.".".$list1Name.";".$enter;
			$fieldStr .= $tab."var list2 = document.".$form->id.".".$list2Name.";".$enter;
			$fieldStr .= $tab."moveSelected(list2, list1);".$enter;
			$fieldStr .= $tab."document.".$form->id.".".$field['name'].".value = implode('".$field['separator']."', list1);";
			$fieldStr .= '}'.$enter;
			$fieldStr .= "".$enter;

			// add function
			$fieldStr .= 'function '.$delFunction.'() {'.$enter;
			$fieldStr .= $tab."var list1 = document.".$form->id.".".$list1Name.";".$enter;
			$fieldStr .= $tab."var list2 = document.".$form->id.".".$list2Name.";".$enter;
			$fieldStr .= $tab."moveSelected(list1, list2);".$enter;
			$fieldStr .= $tab."document.".$form->id.".".$field['name'].".value = implode('".$field['separator']."', list1);";
			$fieldStr .= '}'.$enter;
			$fieldStr .= "".$enter;
			$fieldStr .= '// -->'.$enter;
			$fieldStr .= '</script>'.$enter;

			$selectedOptionValues = "";
			$optionValues = "";

			$selectedOptions = explode($field['separator'], $value);

			reset($field['options']);
			while (list($optionValue, $caption) = each($field['options'])) {
				if (in_Array($optionValue, $selectedOptions)) {
					$selectedOptionValues .= sPrintF(
						'<option value="%s">%s</option>'."\n",
						$optionValue,
						htmlConvert($caption)
					);
				} else {
					$optionValues .= sPrintF(
						'<option value="%s">%s</option>'."\n",
						$optionValue,
						htmlConvert($caption)
					);
				}
			}

			// field
			$fieldStr .= sprintf('<select name="%s" multiple="multiple" size="5" class="multivalue" tabindex="%s">'."\n".'%s</select><br />'."\n",
				$list1Name,$form->getTabIndex(),
				$selectedOptionValues
			);
			$form->increaseTabIndex();
			$fieldStr .= sprintf('<button name="%s" onclick="%s()" type="button" tabindex="%s">&uarr; Toevoegen</button>'."\n", $addButtonName, $addFunction, $form->getTabIndex());
			$form->increaseTabIndex();
			$fieldStr .= sprintf('<button name="%s" onclick="%s()" type="button" tabindex="%s">&darr; Verwijderen</button><br />'."\n", $delButtonName, $delFunction, $form->getTabIndex());
			$form->increaseTabIndex();
			$fieldStr .= sprintf('<select name="%s" multiple="multiple" size="5" class="multivalue" tabindex="%s">'."\n".'%s</select>'."\n",
				$list2Name,$form->getTabIndex(),
				$optionValues
			);
			$form->increaseTabIndex();
			$fieldStr .= sprintf('<input type="hidden" name="%s" value="%s" />', $field['name'], htmlConvert($value));

			$form->printInputField($field, $extra, $fieldStr, 'ft-multifield');
		}

		function writeText(&$form, $field) {
			$extra = "";
			$rowClass = "ft-text";
			$form->printInputField($field, $extra, $field['value'], $rowClass);
		}

		function writeTextblockfield(&$form, $field) {
			$extra = "";
			$rowClass = 'ft-textarea';
			$inputField = sprintf(
				'<textarea name="%s" id="%s" tabindex="%s" rows="%s">%s</textarea>',
				$field['name'],
				$field['name'],
				$form->getTabIndex(),
				$field['rows'],
				$form->getValue($field['name'], $field['type'])
			);
			$form->printInputField($field, $extra, $inputField, $rowClass);
			$form->increaseTabIndex();
		}

		function writeSubmit(&$form, $field) {
?>
						<tr class="ft-submit <?php if ($field['form-index'] % 2 == 0) print('fr1'); else print('fr2'); ?>">
							<td class="fback">
								<?php if ($field['showBack']) print('<a href="#" onclick="history.go(-'.$field['backLevel'].')" class="backLink">&laquo; ga terug</a>'); ?>
							</td>
							<td class="finput" colspan="2">
								<button type="submit" name="submitbutton" tabindex="<?=$form->getTabIndex(); ?>" <?=($field['disabled']) ? 'disabled="disabled"' : '' ?>><?=$field['caption'] ?></button>
							</td>
						</tr>
<?php
			$form->increaseTabIndex();
		}

		function writeCheckboxes(&$form, $field) {
			$extra = '<input type="hidden" name="checkSubmit" value="'.$form->id.'-check" />';
			$rowClass = "ft-checkbox";
			$radioFields = "";
			for ($i = 0; $i < count($field['options']); $i++) {
				$option = $field['options'][$i];

				$selectedValue = $option['checked'];

				if ($form->hasValue($option['name'], $field['type'])) {
					$selectedValue = $form->getValue($option['name'], $field['type']);
				}
				$radioFields .= sprintf(
					'<input class="checkbox" id="%s" value="%s"%s name="%s" type="checkbox" tabindex="%s" %s %s />'."\n",
					$option['name'],
					$option['value'],
					($selectedValue) ? " checked=\"checked\"" : "",
					$option['name'],
					$form->getTabIndex(),
					(isSet($option['disabled']) ? (($option['disabled']) ? ' disabled="disabled"' : '') : ''),
					(isSet($option['onclick']) ? (($option['onclick']) ? ' onclick="'.$option['onclick'].'"' : '') : '')
				);
				$radioFields .= sprintf(
					'<label for="%s">%s</label>'."\n",
					$option['name'],
					$option['caption']
				);
				$radioFields .= sprintf(
					'<small class="optionComment">%s</small><br />'."\n",
					$option['description']
				);
				$form->increaseTabIndex();
			}
			$form->printInputField($field, $extra, $radioFields, $rowClass);
		}

		function writeCheckViewHide(&$form, $field) {
			$extra = '<input type="hidden" name="checkSubmit" value="'.$form->id.'-check" />';
			$rowClass = "ft-checkbox";
			$optFunc = uniqID("rbo");
			$checkField = "";
			$options = $field['options'];

			$selectedValue = $field['checked'];

			if ($form->hasValue($field['name'], $field['type'])) {
				$selectedValue = $form->getValue($field['name'], $field['type']);
			}

			$checkField .= sprintf(
				'<input class="checkbox" id="%s" value="%s"%s name="%s" type="checkbox" tabindex="%s" %s %s %s/>'."\n",
				$field['name'],
				'checked',
				($selectedValue) ? " checked=\"checked\"" : "",
				$field['name'],
				$form->getTabIndex(),
				(isSet($field['disabled']) ? (($field['disabled']) ? ' disabled="disabled"' : '') : ''),
				(isSet($field['onclick']) ? (($field['onclick']) ? ' onclick="'.$field['onclick'].'"' : '') : ''),
				(isSet($options[0]["show"]) || isSet($options[0]["hide"]) || isSet($options[1]["show"]) || isSet($options[1]["hide"])) ? "onfocus=\"".$optFunc."()\" onclick=\"".$optFunc."()\" " : ""
			);
			$checkField .= sprintf(
				'<small class="optionComment">%s</small><br />'."\n",
				$field['description']
			);
			$form->increaseTabIndex();

			$fieldStr = "";
			$jScript0 = "";
			$jScript1 = "";
			$enter = "\n";
			$tab = "\t";

			if (isSet($options[0])) {
				if (isSet($options[0]["hide"])) {
					for ($x = 0; $x < count($options[0]["hide"]); $x++) {
						$hideName = $options[0]["hide"][$x];
						$nrRows = $form->getMarkCount($hideName);
						for ($y = 0; $y < $nrRows; $y++) {
							$jScript0 .= $tab.$tab."var item = document.getElementById('row".$hideName.$y."');".$enter;
							$jScript0 .= $tab.$tab."if (item != null) item.style.display = 'none';".$enter;
						}
					}
				}

				if (isSet($options[0]["show"])) {
					for ($x = 0; $x < count($options[0]["show"]); $x++) {
						$hideName = $options[0]["show"][$x];
						$nrRows = $form->getMarkCount($hideName);
						for ($y = 0; $y < $nrRows; $y++) {
							$jScript0 .= $tab.$tab."var item = document.getElementById('row".$hideName.$y."');".$enter;
							$jScript0 .= $tab.$tab."if (item != null) item.style.display = '';".$enter;
						}
					}
				}
			}

			if (isSet($options[1])) {
				if (isSet($options[1]["hide"])) {
					for ($x = 0; $x < count($options[1]["hide"]); $x++) {
						$hideName = $options[1]["hide"][$x];
						$nrRows = $form->getMarkCount($hideName);
						for ($y = 0; $y < $nrRows; $y++) {
							$jScript1 .= $tab.$tab."var item = document.getElementById('row".$hideName.$y."');".$enter;
							$jScript1 .= $tab.$tab."if (item != null) item.style.display = 'none';".$enter;
						}
					}
				}

				if (isSet($options[1]["show"])) {
					for ($x = 0; $x < count($options[1]["show"]); $x++) {
						$hideName = $options[1]["show"][$x];
						$nrRows = $form->getMarkCount($hideName);
						for ($y = 0; $y < $nrRows; $y++) {
							$jScript1 .= $tab.$tab."var item = document.getElementById('row".$hideName.$y."');".$enter;
							$jScript1 .= $tab.$tab."if (item != null) item.style.display = '';".$enter;
						}
					}
				}
			}

			// script
			$fieldStr = '<script type="text/javascript"><!--'.$enter;
			$fieldStr .= 'function '.$optFunc.'() {'.$enter;
			$fieldStr .= $tab.'var checked = document.'.$form->id.'.'.$field["name"].'.checked;'.$enter;
			$fieldStr .= $tab.'if(checked) {'.$enter;
			$fieldStr .= $jScript0;
			$fieldStr .= $tab.'} else {'.$enter;
			$fieldStr .= $jScript1;
			$fieldStr .= $tab.'}'.$enter;
			$fieldStr .= '}'.$enter;
			$fieldStr .= '// -->'.$enter;
			$fieldStr .= '</script>'.$enter;

			$form->printInputField($field, $extra, $fieldStr . $checkField, $rowClass);
		}

		function writeUploadfield(&$form, $field) {
			$extra = "";
			$rowClass = "ft-file";
			$inputField = sprintf(
				'<input type="file" name="%s" id="%s" tabindex="%s" />',
				$field['name'],
				$field['name'],
				$form->getTabIndex()
			);

			$form->printInputField($field, $extra, $inputField, $rowClass);
			$form->increaseTabIndex();
		}

		function writeCatSelect(&$form, $field) {
			$selectedValue = $field['default'];

			if ($form->hasValue($field['name'], $field['type'])) {
				$selectedValue = $form->getValue($field['name'], $field['type']);
			}
			$extra = "";
			$rowClass = "ft-select";
			$optionsStr = '';
			reset($field['options']);
			while (list($caption, $group) = each($field['options'])) {
				$optionsStr .= '<optgroup label="'.htmlConvert($caption).'">'."\n";
				while (list($value, $name) = each($group)) {
					$optionsStr .= sprintf(
						'<option value="%s"%s>%s</option>'."\n",
						$value,
						($value == $selectedValue) ? " selected=\"selected\"" : "",
						$name
					);
				}
				$optionsStr .= '</optgroup>'."\n";
			}

			$inputField = sprintf(
				'<select name="%s" id="%s" tabindex="%s">%s</select>',
				$field['name'],
				$field['name'],
				$form->getTabIndex(),
				$optionsStr
			);
			$form->printInputField($field, $extra, $inputField, $rowClass);
			$form->increaseTabIndex();
		}

		function writeTimefield(&$form, $field) {
			$extra = "";
			$rowClass = 'ft-time';
			$inputField = sprintf(
				'<input type="text" name="%s" id="%s" maxlength="2" size="2" %stabindex="%s" />',
				$field['name'].'_h',
				$field['name'].'_h',
				(($form->hasValue($field['name'].'_h', $field['type']))) ?
					'value="'.htmlConvert($form->getValue($field['name'].'_h', $field['type'])).'" ' : '',
				$form->getTabIndex()
			);
			$form->increaseTabIndex();
			$inputField .= ' : ';
			$inputField .= sprintf(
				'<input type="text" name="%s" id="%s" maxlength="2" size="2" %stabindex="%s" />',
				$field['name'].'_m',
				$field['name'].'_m',
				(($form->hasValue($field['name'].'_m', $field['type']))) ?
					'value="'.htmlConvert($form->getValue($field['name'].'_m', $field['type'])).'" ' : '',
				$form->getTabIndex()
			);
			$form->increaseTabIndex();

			$form->printInputField($field, $extra, $inputField, $rowClass);
		}

		function writeDatefield(&$form, $field) {
			$extra = "";
			$rowClass = 'ft-date';
			$inputField = sprintf(
				'<input type="text" name="%s" id="%s" maxlength="2" size="2" %stabindex="%s" />',
				$field['name'].'_d',
				$field['name'].'_d',
				(($form->hasValue($field['name'].'_d', $field['type']))) ?
					'value="'.htmlConvert($form->getValue($field['name'].'_d', $field['type'])).'" ' : '',
				$form->getTabIndex()
			);
			$form->increaseTabIndex();
			$inputField .= ' - ';
			$inputField .= sprintf(
				'<input type="text" name="%s" id="%s" maxlength="2" size="2" %stabindex="%s" />',
				$field['name'].'_m',
				$field['name'].'_m',
				(($form->hasValue($field['name'].'_m', $field['type']))) ?
					'value="'.htmlConvert($form->getValue($field['name'].'_m', $field['type'])).'" ' : '',
				$form->getTabIndex()
			);
			$form->increaseTabIndex();
			$inputField .= ' - ';
			$inputField .= sprintf(
				'<input type="text" name="%s" id="%s" maxlength="4" size="4" %stabindex="%s" />',
				$field['name'].'_y',
				$field['name'].'_y',
				(($form->hasValue($field['name'].'_y', $field['type']))) ?
					'value="'.htmlConvert($form->getValue($field['name'].'_y', $field['type'])).'" ' : '',
				$form->getTabIndex()
			);
			$form->increaseTabIndex();

			$form->printInputField($field, $extra, $inputField, $rowClass);
		}

		function onFormOutput(&$form) {
			/**
			 * Check all formfields. If there are radio fields that will hide/show fields, check the value of the field
			 * And hide the fields that needs to be hidden for that option.
			 */
			for ($f = 0; $f < count($form->privateVars['fields']); $f++) {
				$field = $form->privateVars['fields'][$f];
				
				if (is_array($field)) {				
					if (($field['type'] == 'radio') && ($field['showhide'])) {
						$selectedValue = $field['default'];
						if ($form->hasValue($field['name'], $field['type'])) {
							$selectedValue = $form->getValue($field['name'], $field['type']);
						}
						for ($i = 0; $i < count($field['options']); $i++) {
							$option = $field['options'][$i];
							if (($option['value'] == $selectedValue) && (isSet($option['hide']))) {
								for ($j = 0; $j < count($option['hide']); $j++) {
									if (!isset($option['show']) || (isset($option['show']) && !in_array($option['hide'][$j],$option['show'])))
										$form->hideMarking($option['hide'][$j]);
								}
							}
						}
					} else if($field['type'] == 'checkviewhide') {
						$checked = $field['checked'];
						if ($form->hasValue($field['name'], $field['type'])) {
							$checked = $form->getValue($field['name'], $field['type']);
						}
						if($checked) {
							$option = $field['options'][0];
							if (isSet($option['hide'])) {
								for ($j = 0; $j < count($option['hide']); $j++) {
									if (!isset($option['show']) || (isset($option['show']) && !in_array($option['hide'][$j],$option['show'])))
										$form->hideMarking($option['hide'][$j]);
								}
							}
						} else {
							$option = $field['options'][1];
							if (isSet($option['hide'])) {
								for ($j = 0; $j < count($option['hide']); $j++) {
									if (!isset($option['show']) || (isset($option['show']) && !in_array($option['hide'][$j],$option['show'])))
										$form->hideMarking($option['hide'][$j]);
								}
							}
						}
					}
				}
			}
		}
	}


?>
