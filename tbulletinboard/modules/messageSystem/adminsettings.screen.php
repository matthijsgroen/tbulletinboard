<?php

	global $ivLibDir;
	require_once($ivLibDir . "Form.class.php");

	global $formTitleTemplate;
	includeFormComponents("TextField", "TemplateField", "Submit");
	$form = new Form("settings", "");
	$form->addComponent(new FormTemplateField($formTitleTemplate, "Berichten instellingen"));
	
	$form->writeForm();



?>
