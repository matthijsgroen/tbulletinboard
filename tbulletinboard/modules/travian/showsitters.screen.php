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
	includeFormComponents("TextField", "Submit", "TemplateField");

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
