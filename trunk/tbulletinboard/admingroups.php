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

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Instellingen';
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');
	
	importClass("board.Location");
	importClass("interface.Form");
	importClass("interface.FormFields");
	importClass("board.MemberGroups");
	importClass("interface.Table");
	importClass("board.Text");
	importClass("board.ModulePlugin");

	$wizzStep = 0; // step in the addgroup wizard.
	$modID = 0;

	if (isSet($_POST['actionName']) && isSet($_POST['actionID']) && $TBBcurrentUser->isAdministrator()) {
		if (isSet($_POST['wizzStep']) && is_numeric($_POST['wizzStep'])) $wizzStep = $_POST['wizzStep'];
		$correct = true;
		if (isSet($_POST['modID'])) {
			$modID = trim($_POST['modID']);
			/*
			if ($correct && (!is_numeric($modID))) {
				$feedback->addMessage("ongeldige ModuleID!");
				$correct = false;
			}
			*/
			if ($correct) $memberModule = $TBBModuleManager->getPlugin($modID, "usertype");
			if ($correct && (!is_object($memberModule))) {
				$feedback->addMessage("module niet gevonden (".htmlConvert($modID).")!");
				$correct = false;
			}
		}
		if (($_POST['actionName'] == 'chooseMod') && ($_POST['actionID'] == $TBBsession->getActionID()) && ($wizzStep == 1)) {
			if ($correct && (!is_object($memberModule))) {
				$feedback->addMessage("Module niet geladen!");
				$correct = false;
			}
			if ($correct) {
				if (!$memberModule->hasMoreAddGroupSteps($wizzStep)) $wizzStep = 0;
				$TBBsession->actionHandled();
			}
		}
		if (($wizzStep > 1) && (is_object($memberModule)) && ($_POST['actionID'] == $TBBsession->getActionID())) {
			if ($memberModule->handleAddGroupAction($feedback)) {
				if (!$memberModule->hasMoreAddGroupSteps($wizzStep)) $wizzStep = 0;
				$TBBsession->actionHandled();
			} else $wizzStep--;
		}
		if (($wizzStep > 1) && (is_object($memberModule)) && ($_POST['actionID'] != $TBBsession->getActionID())) {
			$wizzStep = 0;
		}
	}

	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation('Systeem instellingen', 'adminboard.php');
	$here->addLocation('Groepen', 'admingroups.php');
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
	$menu->itemIndex = 'groups';
	$menu->showMenu('adminMenu');

?>
	<div class="adminContent">
<?php

	$groups = $GLOBALS['TBBmemberGroupList']->getMemberGroups();
	$currentModuleID = -1;

	?>
	<div class="center"><div class="text">
		<p><?=sprintf("%s groep(en) aanwezig", count($groups)); ?></p>
	</div></div>

<?php
	$table = new Table();
	$table->setHeader("Naam", "Opties");
	for ($i = 0; $i < count($groups); $i++) {
		$group = $groups[$i];
		if ($currentModuleID <> $group->getModuleID()) {
			$groupModule = $group->getModule();
			$table->addGroup(htmlConvert($groupModule->getPluginName()));
			$currentModuleID = $group->getModuleID();
		}
		$table->addRow(htmlConvert($group->getName()), "");
	}
	$table->showTable();

	$memberModules = $TBBModuleManager->getPluginInfoType("usertype", true);
	if (count($memberModules) > 0) {

		if ($wizzStep == 0) {
			$form = new Form("addGroup", "admingroups.php");
			$formFields = new StandardFormFields();
			$form->addFieldGroup($formFields);
			$formFields->activeForm = $form;

			$form->addHiddenField("wizzStep", ($wizzStep + 1));
			$form->addHiddenField('actionID', $TBBsession->getActionID());
			$form->addHiddenField('actionName', 'chooseMod');
			$formFields->startGroup("Groep toevoegen (stap ".($wizzStep + 1).")");
			$values = array();
			for ($i = 0; $i < count($memberModules); $i++) {
				$module = $memberModules[$i];
				$values[$module->getValue("group")] = $module->getValue("name");
			}
			$formFields->addSelect("modID", "Module", "Leden module waar de groep vandaan komt", $values, "1");

			$formFields->addSubmit("Volgende &gt;", false);
			$formFields->endGroup();
			$form->writeForm();
		}
		if ($wizzStep > 0) {
			$form = new Form("addGroup", "admingroups.php");
			$formFields = new StandardFormFields();
			$form->addFieldGroup($formFields);
			$formFields->activeForm = $form;

			$form->addHiddenField("wizzStep", ($wizzStep + 1));
			$form->addHiddenField("modID", $modID);
			$form->addHiddenField('actionID', $TBBsession->getActionID());
			$formFields->startGroup("Groep toevoegen (stap ".($wizzStep + 1).")");
			$formFields->addText("Module", "Leden module waar de groep vandaan komt", $memberModule->getPluginName());
			$memberModule->getAddGroupForm($form, $formFields, $wizzStep);
			$formFields->endGroup();
			$form->writeForm();
		}
	} else {
		$text = new Text();
		$text->addHTMLText("Er kunnen geen groepen worden toegevoegd. Er zijn geen leden modules ge&iuml;nstalleerd!");
		$text->showText();
	}

	/*
	$memberModules = $TBBconfiguration->getMemberModulesInfo();
	if (count($memberModules) > 0) {

		if ($wizzStep == 0) {
			$form = new Form("addGroup", "admingroups.php");
			$formFields = new StandardFormFields();
			$form->addFieldGroup($formFields);
			$formFields->activeForm = $form;

			$form->addHiddenField("wizzStep", ($wizzStep + 1));
			$form->addHiddenField('actionID', $TBBsession->getActionID());
			$form->addHiddenField('actionName', 'chooseMod');
			$formFields->startGroup("Groep toevoegen (stap ".($wizzStep + 1).")");
			$values = array();
			for ($i = 0; $i < count($memberModules); $i++) {
				$module = $memberModules[$i];
				$values[$module['ID']] = $module['name'];
			}
			$formFields->addSelect("modID", "Module", "Leden module waar de groep vandaan komt", $values, "1");

			$formFields->addSubmit("Volgende &gt;", false);
			$formFields->endGroup();
			$form->writeForm();
		}
		if ($wizzStep > 0) {
			$form = new Form("addGroup", "admingroups.php");
			$formFields = new StandardFormFields();
			$form->addFieldGroup($formFields);
			$formFields->activeForm = $form;

			$form->addHiddenField("wizzStep", ($wizzStep + 1));
			$form->addHiddenField("modID", $modID);
			$form->addHiddenField('actionID', $TBBsession->getActionID());
			$formFields->startGroup("Groep toevoegen (stap ".($wizzStep + 1).")");
			$formFields->addText("Module", "Leden module waar de groep vandaan komt", $memberModule->getModuleName());
			$memberModule->getAddGroupForm($form, $formFields, $wizzStep);
			$formFields->endGroup();
			$form->writeForm();
		}
	} else {
		$text = new Text();
		$text->addHTMLText("Er kunnen geen groepen worden toegevoegd. Er zijn geen leden modules ge&iuml;nstalleerd!");
		$text->showText();
	}
	*/
?>
	</div>
<?php
	writeJumpLocationField(-1, "admincontrol");

	include($TBBincludeDir.'htmlbottom.php');
?>
