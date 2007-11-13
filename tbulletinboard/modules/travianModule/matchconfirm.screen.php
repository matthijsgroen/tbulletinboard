<?php

	global $ivLibDir;
	require_once($ivLibDir . "Form.class.php");
	require_once($ivLibDir . "formcomponents/TemplateField.class.php");
	require_once($ivLibDir . "formcomponents/PlainText.class.php");
	require_once($ivLibDir . "formcomponents/Submit.class.php");
	global $TBBsession;
	global $formTitleTemplate;
	
	$form = new Form("searchForumUser", "");
	$form->addHiddenField("actionName", "matchConfirm");
	$form->addHiddenField("actionID", $TBBsession->getActionID());
	$form->addHiddenField("boaruserID", $userRow->getValue("ID"));
	$form->addHiddenField("travianuserID", $playerRow->getValue("playerID"));

	$form->addComponent(new FormTemplateField($formTitleTemplate, "Koppeling bevestigen"));

	$form->addComponent(new FormPlainText("Board alias", "", $userRow->getValue("nickname")));
	$form->addComponent(new FormPlainText("Travian player", "", $playerRow->getValue("playerName")));
	$form->addComponent(new FormPlainText("Population (total)", "", $population));
	$form->addComponent(new FormPlainText("Villages (total)", "", $villages));
	$form->addComponent(new FormSubmit("Koppelen", "", "", "submitButton")); //$caption, $title, $description, $name, $onclick = ""
	print $form->writeForm();

?>
