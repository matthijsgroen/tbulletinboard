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
	require_once($TBBclassDir.'tbblib.php');

	if ($TBBsession->getMessage()) {
		if ($TBBsession->getMessage() == "tagAdd") {
			$feedback->addMessage("TBBtag&trade; toegevoegd!");
			$TBBsession->eraseMessage();
		}
		if ($TBBsession->getMessage() == "tagEdit") {
			$feedback->addMessage("TBBtag&trade; bewerkt!");
			$TBBsession->eraseMessage();
		}
	}

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Instellingen';
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');
	require_once($TBBclassDir.'Location.class.php');
	require_once($libraryClassDir.'Table.class.php');
	require_once($libraryClassDir.'Form.class.php');
	require_once($libraryClassDir.'FormFields.class.php');
	require_once($libraryClassDir.'formcomponents/RecordSelect.class.php');
	require_once($libraryClassDir.'TextParser.class.php');
	require_once($TBBclassDir.'Text.class.php');
	require_once($TBBclassDir.'ActionHandler.class.php');
	require_once($TBBclassDir.'TagListManager.class.php');
	require_once($TBBclassDir.'TBBEmoticonList.class.php');

	if (isSet($_GET['action']) && isSet($_GET['actionID'])) {
		if ((($_GET['action'] == 'activate') || ($_GET['action'] == 'deactivate')) && ($_GET['actionID'] == $TBBsession->getActionID())) {
			$action = new ActionHandler($feedback, $_GET);
			$action->check($TBBcurrentUser->isAdministrator(), 'Deze actie is alleen voor Administrators!');
			$action->isNumeric('id', 'Geen geldige id gegeven!');
			if ($action->correct)
				$action->check($GLOBALS['TBBtagListManager']->setTagActive($_GET['id'], ($_GET['action'] == 'activate')), 'tag kon niet worden geactiveerd!');
			$action->finish('Tag status aangepast');
		}
	}
	if (isSet($_POST['actionName']) && isSet($_POST['actionID'])) {
		if (($_POST['actionName'] == 'delete') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			$action = new ActionHandler($feedback, $_POST);
			$action->check($TBBcurrentUser->isAdministrator(), 'Deze actie is alleen voor Administrators!');
			for ($i = 0; $i < count($_POST["recordID"]); $i++) {
				$action->check($GLOBALS['TBBtagListManager']->deleteTag($_POST['recordID'][$i]), 'Tag kon niet worden verwijderd!');
			}
			$action->finish('Tag verwijderd');
		}
	}
	$feedback->showMessages();
?>
	<script type="text/javascript"><!--
		var selectedRow = -1;
		var selectedEnabled = -1;

		function selectTag(id, activated) {
			selectedRow = id;
			selectedEnabled = activated;
		}

		function editTag() {
			if (selectedRow == -1) {
				alert("Er is geen tag geselecteerd");
				return;
			}
			popupWindow('popups/edittag.php?id='+selectedRow, 500, 300, 'editprofile', 1);
		}

		function activate() {
			if (selectedRow == -1) {
				alert("Er is geen tag geselecteerd");
				return;
			}
			if (selectedEnabled == 'yes') {
				document.location.href = 'admintags.php?action=deactivate&id='+selectedRow+'&actionID=<?=$TBBsession->getActionID() ?>';
			} else {
				document.location.href = 'admintags.php?action=activate&id='+selectedRow+'&actionID=<?=$TBBsession->getActionID() ?>';
			}
		}
	// -->
	</script>
<?php

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation('Systeem instellingen', 'adminboard.php');
	$here->addLocation('TBBtags&trade;', 'admintags.php');
	$here->showLocation();

	if (!$TBBsession->isLoggedIn()) {
		$text = new Text();
		$text->addHTMLText("Sorry, gasten hebben geen instellingen venster!");
		$text->showText();
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}

	if (!$TBBcurrentUser->isAdministrator()) {
		$text = new Text();
		$text->addHTMLText("Sorry, dit venster is alleen voor administrators!");
		$text->showText();
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}

	include($TBBincludeDir.'configmenu.php');
	$adminMenu->itemIndex = 'system';
	$adminMenu->showMenu('configMenu');

	include($TBBincludeDir.'admin_menu.php');
	$menu->itemIndex = 'tags';
	$menu->showMenu('adminMenu');

?>
	<div class="adminContent">
<?php
	$menu = new Menu();
	$menu->addItem("add", "", "TBBtag&trade; toevoegen", "javascript:popupWindow('popups/edittag.php', 500, 300, 'editprofile', 1)", "", "", 0, false, '');
	$menu->addItem("edit", "", "TBBtag&trade; bewerken", "javascript:editTag()", "", "", 0, false, '');
	$menu->addItem("delete", "", "TBBtag(s)&trade; verwijderen", "javascript:deleteChecked(document.tagForm['recordID[]'], document.tagForm, 'Geen Tags aangevinkt!')", "", "", 0, false, '');
	$menu->addItem("activate", "", "(de)activeren", "javascript:activate()", "", "", 0, false, '');
	$menu->showMenu('toolbar');


	$tbbTags = $GLOBALS['TBBtagListManager']->getTBBtags(true);

	if ($tbbTags->getTagCount() > 0) {
		$table = new Table();
		$table->setHeader("recordID", "Start", "Beschrijving", "Parameters", "Actief", "active");
		for ($i = 0; $i < $tbbTags->getTagCount(); $i++) {
			$tbbTag = $tbbTags->getTag($i);
			$table->addRow(
				$tbbTag->getID(),
				'<a href="javascript:popupWindow(\'popups/poptag.php?id='.$tbbTag->getID().'\', 300, 350, \'tagpop\', 1)" title="Klik voor informatie">'.$tbbTag->getName().'</a>',
				htmlConvert($tbbTag->getDescription()),
				(!$tbbTag->allowAllParameters() && (count($tbbTag->getAcceptedParameters()) == 0)) ? '&nbsp;' : "&bull;",
				($tbbTag->isActive()) ? '&bull;':'&nbsp;',
				(($tbbTag->isActive()) ? "yes" : "no")

				/*
				(($tbbTag->isActive()) ?
					sprintf(
						'<a href="admintags.php?action=deactivate&amp;id=%s&amp;actionID=%s" title="deactiveer deze tag">deactiveer</a>',
						$tbbTag->getID(),
						$TBBsession->getActionID()
					)
				:
					sprintf(
						'<a href="admintags.php?action=activate&amp;id=%s&amp;actionID=%s" title="deactiveer deze tag">activeer</a>',
						$tbbTag->getID(),
						$TBBsession->getActionID()
					))
				*/
			);
		}
		$table->setCheckboxColumn(0);
		$table->setRowSelect(array(0, 5), 'selectTag');
		$table->hideColumn(5);

		$tagForm = new Form("tagForm", "admintags.php");
		$tagForm->addHiddenField("actionID", $TBBsession->getActionID());
		$tagForm->addHiddenField("actionName", "delete");

		$tagForm->addComponent(new FormRecordSelect($table));
		$tagForm->writeForm();

	} else {
		$text = new Text();
		$text->addHTMLText("Geen tags gevonden");
		$text->showText();
	}

	$testForm = new Form("testForm", "admintags.php");
	$formFields = new StandardFormFields();
	$testForm->addFieldGroup($formFields);
	$formFields->activeForm = $testForm;

	$formFields->startGroup("TBBtag&trade; test gebied");
	$exampleTextParser = new TextParser();

	$formFields->addText("HTML resultaat", "resultaat van tags", (isSet($_POST['text']) ? $exampleTextParser->parseMessageText($_POST['text'], $GLOBALS['TBBemoticonList'], $tbbTags) : ""));

	$formFields->addTextBlockField("text", "Tekst", "tekst met tags", 8, false);
	$formFields->endGroup();
	$formFields->addSubmit("test", false);
	$testForm->writeForm();

?>
	</div>
<?php
	writeJumpLocationField(-1, "admincontrol");

	include($TBBincludeDir.'htmlbottom.php');
?>
