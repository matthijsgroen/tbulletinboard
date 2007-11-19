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
	includeFormComponents("TemplateField", "Submit", "PlainText");
	
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
