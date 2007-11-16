<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
	 */
	global $ivLibDir;
	require_once($ivLibDir . "FormFields.class.php");
	global $TBBclassDir;
	require_once($TBBclassDir.'TopicIconList.class.php');
	require_once($TBBclassDir.'Board.class.php');

	class BoardFormFields extends FormFieldGroup {

		var $activeForm;

		function BoardFormFields() {
		}

		function hasFieldType($type) {
			switch($type) {
				case 'iconBar':
				case 'postText':
				case 'avatars':
				case 'boardSelect':
					return true;
				default:
					return false;
			}
		}

		function writeField(&$form, $type, $fieldData) {
			switch($type) {
				case 'iconBar': $this->writeIconBar(&$form, $fieldData); break;
				case 'postText': $this->writePostTextField(&$form, $fieldData); break;
				case 'avatars': $this->writeAvatars(&$form, $fieldData); break;
				case 'boardSelect': $this->writeBoardSelect(&$form, $fieldData); break;
			}
		}

		function addIconBar($name, $title, $description) {
			$this->activeForm->addField(array('type' => 'iconBar', 'title' => $title, 'description' => $description, 'name' => $name));
		}

		function addPostTextField($name, $title, $description, $emoticons, $tbbBar, $tbbImg, $required = false) {
			if ($tbbBar) {
				$this->activeForm->addField(array('type' => 'tbbBar', 'textinput' => $name, 'img' => $tbbImg));
			}
			$this->activeForm->addField(array('type' => 'postText', 'name' => $name, 'title' => $title,
				'description' => $description, 'emoticons' => $emoticons, 'required' => $required));
		}

		function addSystemAvatars($name, $title, $description) {
			$this->activeForm->addField(array('type' => 'avatars', 'title' => $title, 'description' => $description, 'name' => $name));
		}

		function addBoardSelect($name, $title, $description, $selectedID, $showRoot = false) {
			$this->activeForm->addField(array('type' => 'boardSelect', 'title' => $title,
				'description' => $description, 'name' => $name, 'showRoot' => $showRoot, 'selected' => $selectedID));
		}

		function writeIconbar(&$form, $field) {
			$selectedValue = 0;
			if ($form->hasValue($field['name'], $field['type'])) {
				$selectedValue = $form->getValue($field['name'], $field['type']);
			}
			$extra = sprintf(
				'<input class="radio" id="%s" value="0" %sname="%s" type="radio" tabindex="%s" />&nbsp;<label class="fname" for="%s">Geen&nbsp;pictogram</label>',
				$field['name'].'0',
				($selectedValue == 0) ? 'checked="checked"' : '',
				$field['name'],
				$form->getTabIndex(),
				$field['name'].'0'
			);
			$form->increaseTabIndex();
			$rowClass = "ft-iconbar";
			$iconsStr = '';
			$iconList = new TopicIconList();
			$icons = $iconList->getIconsInfo();
			for ($i = 0; $i < count($icons); $i++) {
				$icon = $icons[$i];
				$iconsStr .= sprintf(
					' <input class="radio" id="%s" value="%s" %sname="%s" type="radio" tabindex="%s" />&nbsp;<label class="fname" for="%s"><img src="%s" alt="%s" /></label>',
					$field['name'].$i,
					$icon['ID'],
					($selectedValue == $icon['ID']) ? 'checked="checked"' : '',
					$field['name'],
					$form->getTabIndex(),
					$field['name'].$i,
					htmlConvert($icon['imgUrl']),
					htmlConvert($icon['name'])
				);
				$form->increaseTabIndex();
			}
			$form->printInputField($field, $extra, $iconsStr, $rowClass);
		}

		function writePostTextField(&$form, $field) {
			$extra = "";
			global $TBBclassDir;
			require_once($TBBclassDir . "TBBEmoticonList.class.php");
			global $TBBemoticonList;
			$TBBemoticonList->readEmoticonsInfo();
			$emoticons = $TBBemoticonList->getEmoticonPicker($field['name'], $form->id);
			$extra = sprintf('<div class="smileybox">%s</div>', 
				$emoticons);
			
			$textField = sprintf(
				'<textarea name="%s" id="%s" rows="15" tabindex="%s">%s</textarea>',
				$field['name'],
				$field['name'],
				$form->getTabIndex(),
				htmlConvert($form->getValue($field['name'], $field['type']))
			);

			$form->printInputField($field, $extra, $textField, 'ft-postText');
			$form->increaseTabIndex();
		}

		function writeAvatars(&$form, $field) {
			global $TBBcurrentUser;
			$avatarID = $TBBcurrentUser->getAvatarID();
?>
						<tr class="ft-iconbar <? if ($field['form-index'] % 2 == 0) print('fr1'); else print('fr2'); ?>">
							<td class="fname">
								<div class="flabel"><span class="fieldtitle"><?=$field['title'] ?>:<br /></span></div>
								<div class="fdesc"><small class="fielddesc"><?=$field['description'] ?></small></div>
								<input class="radio" id="<?=$field['name'].'0' ?>" value="0" name="<?=$field['name']; ?>" type="radio" <?=($avatarID == false) ? 'checked="checked" ' : '' ?>tabindex="<?=$form->getTabIndex(); ?>"/>&nbsp;<label class="fname" for="<?=$field['name'].'0' ?>">Geen&nbsp;avatar</label>
							</td>
							<td class="finput">
<?php
			$form->increaseTabIndex();
			$avatarList = new AvatarList();
			$avatars = $avatarList->getSystemAvatarInfo();
			for ($i = 0; $i < count($avatars); $i++) {
				$avatar = $avatars[$i];
?>
								<input class="radio" id="<?=$field['name'].$i ?>" value="<?=$avatar['ID']; ?>" name="<?=$field['name']; ?>" type="radio" <?=($avatarID == $avatar['ID']) ? 'checked="checked" ' : '' ?>tabindex="<?=$form->getTabIndex(); ?>" />&nbsp;<label for="<?=$field['name'].$i ?>"><img src="avatar.php?id=<?=$avatar['ID']; ?>" alt="avatar" /></label><wbr />
<?php
				$form->increaseTabIndex();
			}
?>
							</td>
						</tr>
<?php
		}

		function getBoardOptions($optionList, $structure, $level) {
			global $TBBcurrentUser;
			global $textParser;
			global $TBBboardList;
			$subBoards = $structure["childs"];
			$levelStr = '';
			for ($i = 0; $i < $level; $i++) $levelStr .= '-';
			if ($level > 0) $levelStr .= ' ';
			for ($i = 0; $i < count($subBoards); $i++) {
				$subBoard = $subBoards[$i];
				if (($TBBboardList->canReadBoard($subBoard['ID'], $TBBcurrentUser)) && ((!$subBoard["hidden"]) || ($TBBcurrentUser->isActiveAdmin()))) {
					$optionList["".$subBoard["ID"]] = $levelStr.htmlConvert($subBoard["name"]);
					$optionList = $this->getBoardOptions($optionList, $subBoard, $level+1);
				}
			}
			return $optionList;
		}

		function writeBoardSelect(&$form, $field) {
			$extra = "";

			$selectedValue = $field['selected'];
			if ($form->hasValue($field['name'], $field['type'])) {
				$selectedValue = $form->getValue($field['name'], $field['type']);
			}

			global $TBBboardList;
			$boardStructure = $TBBboardList->getStructureCache();
			$options = array();
			$level = 0;
			if ($field['showRoot']) {
				$level = 1;
				$options['0'] = "Overzicht";
			}
			$options = $this->getBoardOptions($options, $boardStructure, $level);
			$optionStr = "";
			reset($options);
			while (list($value, $text) = each($options)) {
				$optionStr .= sprintf('<option value="%s"%s>%s</option>'."\n",
					$value, ($selectedValue == $value) ? ' selected="selected"' : '', $text);
			}
			$selectField = sprintf('<select name="%s" tabindex="%s">'."\n%s\n</select>\n",
				$field['name'], $form->getTabIndex(), $optionStr);

			$form->printInputField($field, $extra, $selectField, 'ft-boardSelect');
			$form->increaseTabIndex();
		}
	}
	
	class FormTopicIconBar extends FormComponent {
		
		function FormTopicIconBar($name, $title, $description) { // "icon", "Pictogram", "pictogram van het onderwerp"
			$this->FormComponent($title, $description, $name);
		
		}

		function getInput() {
			$selectedValue = 0;
			if ($this->form->hasValue($this->identifier, 'boardiconbar')) {
				$selectedValue = $this->form->getValue($this->identifier, 'boardiconbar');
			}
			$rowClass = "ft-iconbar";
			$iconsStr = '';
			$iconList = new TopicIconList();
			$icons = $iconList->getIconsInfo();
			for ($i = 0; $i < count($icons); $i++) {
				$icon = $icons[$i];
				$iconsStr .= sprintf(
					' <input class="radio" id="%s" value="%s" %sname="%s" type="radio" tabindex="%s" />&nbsp;<label class="fname" for="%s"><img src="%s" alt="%s" /></label>',
					$field['name'].$i,
					$icon['ID'],
					($selectedValue == $icon['ID']) ? 'checked="checked"' : '',
					$field['name'],
					$form->getTabIndex(),
					$field['name'].$i,
					htmlConvert($icon['imgUrl']),
					htmlConvert($icon['name'])
				);
				$form->increaseTabIndex();
			}
			return $iconsStr;
		}
		
		function getExtra() {
			//print "?".$this->form->getValue($this->identifier, 'boardiconbar')."?";
			$extra = sprintf(
				'<input class="radio" id="%s" value="0" %sname="%s" type="radio" tabindex="%s" />&nbsp;<label class="fname" for="%s">Geen&nbsp;pictogram</label>',
				$this->identifier.'0',
				((!$this->form->hasValue($this->identifier, 'boardiconbar')) || ($this->form->getValue($this->identifier, 'boardiconbar') == 0)) ?
					'checked="checked" ' : '',
				$this->identifier,
				$this->form->getTabIndex(),
				$this->identifier.'0'
			);
			$this->form->increaseTabIndex();
			return $extra;		
		}

	}
	
	class FormBoardTextField extends FormComponent {
	
		var $emoticons;
		var $tbbBar;
		var $tbbImg;
	
		function FormBoardTextField($name, $title, $description, $emoticons, $tbbBar, $tbbImg, $required = false) {
			$this->FormComponent($title, $description, $name);
			$this->required = $required;			
		}
		
		function getInput() {
			return "";
		}
	
	}
	
?>
