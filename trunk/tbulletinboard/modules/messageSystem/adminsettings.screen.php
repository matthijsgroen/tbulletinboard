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

	importClass("interface.Form");
	importClass("board.BoardProfiles");
	importClass("board.ActionHandler");

	global $formTitleTemplate;
	global $TBBsession;
	global $TBBconfiguration;
	// Retrieve the needed form components
	includeFormComponents("TextField", "TemplateField", "Submit", "Select");
	// Define the form
	$form = new Form("settings", "");
	// name of the action and the unique action ID (this is to prevent post refresh actions!)
	$form->addHiddenField("actionName", "messageSettings");
	$form->addComponent(new FormTemplateField($formTitleTemplate, "Berichten instellingen"));
	$profileSelect = new FormSelect("Board profiel", "Profiel met toegestane onderwerptypen en tags", "profile");
	$profiles = $GLOBALS['TBBboardProfileList']->getProfiles();
	for ($i = 0; $i < count($profiles); $i++) {
		$profileSelect->addComponent(new FormOption($profiles[$i]->getName(), $profiles[$i]->getID()));
	}
	$form->addComponent($profileSelect);
	$form->addComponent(new FormSubmit("Opslaan", "", "", "storeButton"));

	// get the table that handles the data of this form
	include($moduleDir . "MessageSettings.bean.php");
	$database = $TBBconfiguration->getDatabase();
	$settingsTable = new MessageSettingsTable($database);

	// A message log to show action error messages	
	$feedback = new Messages();
	$actionManager = new ActionHandler($feedback, $_POST);
	$actionManager->definePostAction("messageSettings", "profile");
	if ($data = $actionManager->inAction("messageSettings")) {
		if ($actionManager->check($form->checkPostedFields($feedback), "")) {
			$settingsTable->editSettings($data->getProperty("profile"));
			$actionManager->finish("Instellingen opgeslagen");
		}	
	}
	$form->setValue("profile", $settingsTable->getProfileID());
	$form->addHiddenField("actionID", $TBBsession->getActionID());
	
	$feedback->showMessages();
	
	$form->writeForm();



?>
