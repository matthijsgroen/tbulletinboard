<?php

	global $ivLibDir;
	require_once($ivLibDir . "Form.class.php");
	require_once($ivLibDir . "formcomponents/TextField.class.php");
	require_once($ivLibDir . "formcomponents/Submit.class.php");
	require_once($ivLibDir . "formcomponents/TemplateField.class.php");
	global $TBBsession;
	global $formTitleTemplate;
	
	$form = new Form("searchForumUser", "");
	$form->addHiddenField("actionName", "matchPlayer");
	$form->addHiddenField("actionID", $TBBsession->getActionID());
	$form->addComponent(new FormTemplateField($formTitleTemplate, "Speler koppelen aan forum"));
	$form->addComponent(new FormTextField("boardnick", "Alias", "Alias op het board", 255, true));
	$form->addComponent(new FormTextField("traviannick", "Travian", "Playername in Travian", 255, true));
	$form->addComponent(new FormSubmit("Zoeken", "", "", "submitButton")); //$caption, $title, $description, $name, $onclick = ""
	print $form->writeForm();

?>
