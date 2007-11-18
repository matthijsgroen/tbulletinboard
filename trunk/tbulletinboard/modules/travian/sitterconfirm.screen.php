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


	global $libraryClassDir;
	require_once($libraryClassDir . "Form.class.php");
	require_once($libraryClassDir . "formcomponents/TemplateField.class.php");
	require_once($libraryClassDir . "formcomponents/PlainText.class.php");
	require_once($libraryClassDir . "formcomponents/Submit.class.php");
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
