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

	$TBBclassDir = "../classes/";
	require_once($TBBclassDir."library.php");
	date_default_timezone_set('CET');	
	if (isSet($_POST['action']) && ($_POST['action'] == "createpatch")) {
		$filename = sprintf("ptch%s - %s.php", time(), $_POST['name']);

		header("Content-type: text/plain");
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		$template = file_get_contents("templates/dbpatch.txt");
		printf($template, $_POST['name'], $_POST['author'], $_POST['query']);

		exit;
	}	
	
?>
<html>
<head>
	<title>Een nieuwe patch maken</title>
</head>
<body>
<?	
	importClass("interface.Form");
	includeFormComponents("TextField", "TemplateField", "Submit", "TextArea");
	$newPatchForm = new Form("newPatch", "");
	$newPatchForm->addHiddenField("action", "createpatch");
	$newPatchForm->addComponent(new FormTemplateField("<h2>%text%</h2>", "Patch maken"));
	$newPatchForm->addComponent(new FormTextField("name", "Naam", "Naam van de patch", 60, true));
	$newPatchForm->addComponent(new FormTextField("author", "Maker", "jouw naam", 60, true));
	$newPatchForm->addComponent(new FormTextArea("query", "Query", "", 80, 10, true));
	$newPatchForm->addComponent(new FormSubmit("Download", "", "", "downloadButton"));
	$newPatchForm->writeForm();

?>
</body>
</html>
