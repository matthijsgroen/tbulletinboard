<?php

	global $ivLibDir;
	require_once($ivLibDir . "Form.class.php");
	require_once($ivLibDir . "formcomponents/TextField.class.php");
	require_once($ivLibDir . "formcomponents/Submit.class.php");
	require_once($ivLibDir . "formcomponents/TemplateField.class.php");
	global $TBBsession;
	global $formTitleTemplate;
	
	$database = $TBBconfiguration->getDatabase();
	require_once($moduleDir . "TravianSitter.bean.php");
	$travianSitterTable = new TravianSitterTable($database);
	$filter = new DataFilter();
	$filter->addEquals("userID", $TBBcurrentUser->getUserID());

	$sorting = new ColumnSorting();
	$sorting->addColumnSort("travianName", true);	
	$travianSitterTable->selectRows($filter, $sorting);

	$text = new Text();
	$text->addHTMLText("Hier kan je opgeven dat je account in de gaten wordt gehouden door andere spelers. Zorg dat dit up-to-date is!<br />".
		"Het heeft namelijk als voordeel dat als je sitter ook een forum gebruiker is hij via de targetzoeker ook targets voor jou kan zoeken, ".
		"en zo in jouw afwezigheid kan raiden voor je!");
	$text->showText();
	
	if ($travianSitterTable->getSelectedRowCount() > 0) {
		$sitterList = new Table();
		$sitterList->setHeader("Naam", "Verwijderen");
		while ($sitterRow = $travianSitterTable->getRow()) {
			$sitterList->addRow(htmlConvert($sitterRow->getValue("travianName")), 
				sprintf('<a href="?id=%s&screen=sitters&actionName=removeSitter&actionID=%s&sitterID=%s">Verwijderen</a>', 
					$this->getModuleName(), $TBBsession->getActionID(), $sitterRow->getValue("ID")));
		}	
		$sitterList->showTable();
	}

	if ($travianSitterTable->getSelectedRowCount() < 2) {

		$form = new Form("addSitter", "");
		$form->addHiddenField("actionName", "addSitter");
		$form->addHiddenField("actionID", $TBBsession->getActionID());
		$form->addComponent(new FormTemplateField($formTitleTemplate, "Sitter toevoegen"));
		$form->addComponent(new FormTextField("traviannick", "Speler", "Spelernaam in Travian", 255, true));
		$form->addComponent(new FormSubmit("Zoeken", "", "", "submitButton")); //$caption, $title, $description, $name, $onclick = ""
		print $form->writeForm();
	}

?>
