<?php

	global $ivLibDir;
	require_once($ivLibDir . "Form.class.php");
	require_once($ivLibDir . "formcomponents/TemplateField.class.php");
	require_once($ivLibDir . "formcomponents/PlainText.class.php");
	require_once($ivLibDir . "formcomponents/Submit.class.php");
	global $TBBsession;
	global $formTitleTemplate;
	
	$form = new Form("searchForumUser", "");
	$form->addHiddenField("actionName", "addConfirm");
	$form->addHiddenField("actionID", $TBBsession->getActionID());
	$form->addHiddenField("travianuserID", $playerRow->getValue("playerID"));
	$form->addHiddenField("travianName", $playerRow->getValue("playerName"));

	$form->addComponent(new FormTemplateField($formTitleTemplate, "Sitter bevestigen"));

	$form->addComponent(new FormPlainText("Speler", "", $playerRow->getValue("playerName")));
	$form->addComponent(new FormPlainText("Alliantie", "", $playerRow->getValue("allianceName")));
	$form->addComponent(new FormPlainText("Population (total)", "", $population));
	$form->addComponent(new FormPlainText("Villages (total)", "", $villages));
	$form->addComponent(new FormSubmit("Deze persoon is mijn sitter", "", "", "submitButton")); //$caption, $title, $description, $name, $onclick = ""
	print $form->writeForm();

?>
