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

	require_once("folder.config.php");
	// Load the configuration
	require_once($TBBconfigDir.'configuration.php');
	require_once($libraryClassDir.'TextParser.class.php');
	require_once($libraryClassDir.'Menu.class.php');
	require_once($libraryClassDir.'Table.class.php');
	require_once($libraryClassDir.'Form.class.php');
	require_once($libraryClassDir.'FormFields.class.php');
	require_once($libraryClassDir.'formcomponents/RecordSelect.class.php');
	require_once($libraryClassDir.'formcomponents/Submit.class.php');
	require_once($TBBclassDir.'TagListManager.class.php');
	require_once($TBBclassDir.'Text.class.php');
	require_once($TBBclassDir.'ActionHandler.class.php');
	require_once($TBBclassDir.'BoardProfiles.class.php');
	require_once($TBBclassDir.'ModulePlugin.class.php');

	$pageTitle = 'Profiel boards';
	include($TBBincludeDir.'popuptop.php');

	if (isSet($_GET["id"])) $profileID = $_GET["id"];
	if (isSet($_POST["id"])) $profileID = $_POST["id"];

	if (!isSet($profileID)) {
		?>
			<h2>Geen id meegegeven</h2>
		<?php
		$text = new Text();
		$text->addHTMLText("Geen profiel id opgegeven!");
		$text->showText();
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}
	$boardProfile = $GLOBALS['TBBboardProfileList']->getBoardProfile($profileID);
	if (!is_Object($boardProfile)) {
		?>
			<h2>Profiel niet gevonden!</h2>
		<?php
		$text = new Text();
		$text->addHTMLText("Geen geldige profiel id opgegeven!");
		$text->showText();
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}

	if (!$TBBcurrentUser->isAdministrator()) {
		?>
			<h2>Geen toegang!</h2>
		<?php
		$text = new Text();
		$text->addHTMLText("Dit scherm is alleen voor administrators!");
		$text->showText();
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}

	if (isSet($_POST['actionName']) && isSet($_POST['actionID'])) {
		if (($_POST['actionName'] == 'setTopics') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			$action = new ActionHandler($feedback, $_POST);
			$action->check($TBBcurrentUser->isAdministrator(), 'Deze actie is alleen voor Administrators!');
			$selectedTopicTypes = array();
			if (isSet($_POST['recordID'])) $selectedTopicTypes = $_POST['recordID'];
			$boardProfile->setAllowedTopicTypes($selectedTopicTypes);
			if ($_POST['defaultTopic'] != '-1') {
				// set the defaulttopic
				$boardProfile->setDefaultTopicTypeID($_POST['defaultTopic']);
			}
			$TBBsession->actionHandled();
			$feedback->addMessage("Toegestane onderwerp soorten aangepast");
		}
	}

?>
	<h2>Topics mogelijk in profiel: "<?=$boardProfile->getName() ?>"</h2>
	<script type="text/javascript">
	<!--
		var selectedTopic = -1;

		function selectTopic(id) {
			selectedTopic = id;
		}

		function setDefault(field, form) {
			if (selectedTopic == -1) {
				alert("Geen onderwerp type geselecteerd!");
				return;
			}
			var nrChecked = 0;
			for (i = 0; i < field.length; i++) {
				if (field[i].value == selectedTopic) {
					if (field[i].checked == true) {
						form.defaultTopic.value = selectedTopic;
						form.submit();
					} else {
						alert("selecteerde onderwerp is niet toegestaan!");
					}
				}
			}
			if (!field.length) {
				if (field.checked == true) {
					form.defaultTopic.value = selectedTopic;
					form.submit();
				} else {
					alert("selecteerde onderwerp is niet toegestaan!");
				}
			}
		}

	// -->
	</script>
<?php

	$feedback->showMessages();

	$navMenu = new Menu();
	$navMenu->itemIndex = "topics";
	$navMenu->addItem('profile', '', 'Algemeen', 'editboardprofile.php?id='.$profileID, '', '', 0, false, '');
	$navMenu->addItem('tags', '', 'Tags', 'profiletags.php?id='.$profileID, '', '', 0, false, '');
	$navMenu->addItem('boards', '', 'Boards ('.$boardProfile->getNrUsed().')', 'profileboards.php?id='.$profileID, '', '', 0, false, '');
	$navMenu->addItem('topics', '', 'Onderwerpen', 'profiletopics.php?id='.$profileID, '', '', 0, false, '');
	$navMenu->showMenu("configMenu");

	$menu = new Menu();
	$menu->addItem("default", "", "Als standaard instellen", "javascript:setDefault(document.boardTopicForm['recordID[]'], document.boardTopicForm)", "", "", 0, false, '');
	$menu->showMenu('toolbar');


	$completeList = $TBBModuleManager->getPluginInfoType("topic");

	$table = new Table();
	$table->setClass("table topicModules");
	$table->setHeader("recordID", "Naam", "Actief");
	$table->setHeaderClasses("checkbox", "moduleName", "moduleActive");
	$table->setRowClasses("checkbox", "moduleName", "moduleActive");
	$selectedModules = $boardProfile->getAllowedTopicPlugins();
	$defaultModule = $boardProfile->getDefaultTopicTypeID();
	if ($defaultModule !== false) {
		$table->selectRow($defaultModule);
	}

	for ($i = 0; $i < count($completeList); $i++) {
		$topicModule = $completeList[$i];
		$table->addRow(
			$topicModule->getValue('group'),
			($defaultModule === $topicModule->getValue('group')) ?
			"<strong>".$topicModule->getValue('name')."</strong>" :
			$topicModule->getValue('name'),
			($topicModule->getValue('active')) ? "&bull;" : ""
		);
	}
	$table->setCheckboxColumn(0);
	$table->setClickColumn(0, "selectTopic", false);
	$table->setCheckedValues($selectedModules);

	$topicForm = new Form("boardTopicForm", "profiletopics.php");
	$topicForm->addHiddenField("actionID", $TBBsession->getActionID());
	$topicForm->addHiddenField("actionName", "setTopics");
	$topicForm->addHiddenField("defaultTopic", "-1");
	$topicForm->addHiddenField("id", $profileID);

	$topicForm->addComponent(new FormRecordSelect($table));

	//$formFields = new StandardFormFields();
	//$topicForm->addFieldGroup($formFields);
	//$formFields->activeForm = $topicForm;

	//$formFields->addSubmit("Aanpassen", false);
	$topicForm->addComponent(new FormSubmit("Aanpassen", "", "", "change", ""));

	$topicForm->writeForm();


	include($TBBincludeDir.'popupbottom.php');
?>
