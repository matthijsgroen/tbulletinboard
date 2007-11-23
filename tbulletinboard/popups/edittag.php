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

	require_once("folder.config.php");
	// Load the configuration
	require_once($TBBconfigDir.'configuration.php');



	importClass("interface.Location");	
	importClass("interface.Form");	
	importClass("interface.FormFields");	
	importClass("interface.Table");	
	importClass("interface.Text");	
	importClass("util.TextParser");	
	importClass("board.ActionHandler");	
	importClass("board.TagListManager");	

	// Declare the form
	if (isSet($_GET["id"])) $tagId = $_GET["id"];
	if (isSet($_POST["id"])) $tagId = $_POST["id"];

	if (isSet($tagId)) {
		$tbbTagList = $GLOBALS['TBBtagListManager']->getTBBtags(true);
		$tagIndex = $tbbTagList->indexOf($tagId);
		if ($tagIndex >= 0) {
			$tag = $tbbTagList->getTag($tagIndex);
		}
	}

	$form = new Form("addTag", "edittag.php");
	$formFields = new StandardFormFields();
	$form->addFieldGroup($formFields);
	$formFields->activeForm = $form;

	$form->addHiddenField("actionID", $TBBsession->getActionID());

	if (isSet($tag)) {
		$form->addHiddenField("actionName", "editTag");
		$form->addHiddenField("id", $tagId);
		$formFields->startGroup("TBBtag&trade; bewerken");
	} else {
		$form->addHiddenField("actionName", "addTag");
		$formFields->startGroup("TBBtag&trade; toevoegen");
	}

	$formFields->addTextField("startTag", "Tagnaam", "de naam die de tag opend. Zonder [ en ]!<br />bijv: <strong>color</strong>", 20, true);
	$options = array();
	$options[] = array('value' => 'yes', 'caption' => 'Ja', 'description' => 'Voor dynamische parameters', 'show' => array(), 'hide' => array('statParam'));
	$options[] = array('value' => 'no', 'caption' => 'Nee', 'description' => 'Voor statische of geen parameters', 'show' => array('statParam'), 'hide' => array());
	$formFields->addRadioViewHide("acceptAll", "Accepteer alle parameters", "", $options, "yes", true);

	$form->startMarking("statParam");
	$formFields->addMultiField("parameters", "Parameters", "statische parameters, alleen als hierboven 'nee' gekozen is.", " ", false);
	$form->endMarking();

	$formFields->addMultiField("endTags", "Eind tags", "tags die als einde gezien moeten worden.", " ", true);
	$options = array();
	$options[] = array('value' => 'yes', 'caption' => 'Ja', 'description' => '');
	$options[] = array('value' => 'no', 'caption' => 'Nee', 'description' => '');
	$formFields->addRadio("endRequired", "Tag einde verplicht", "Voor als de bovenstaande eindes niet gevonden worden", $options, "yes");
	$formFields->addTextBlockField("htmlReplace", "HTML resultaat", "gebruik {text} voor de text tussen de tags, en {parameter} voor de tag parameter.<br />Bijv. <strong>".htmlConvert('<a href="{parameter}" target="_blank">{text}</a>')."</strong>", 6, true);
	$formFields->addText("Extra uitleg", "uitleg over Ouders en Kinderen", "Voorbeeld: [b][u]Text[/u] text[/b]. In dit geval is <strong>b</strong> een ouder van <strong>u</strong>. <strong>u</strong> is in dit geval het kind van <strong>b</strong>. Met onderstaande velden kan worden ingesteld wat de toegestane ouders en kinderen zijn van deze tag. Gebruik <strong>{all}</strong> als alle ouders of kinderen worden geaccepteerd, gebruikt <strong>{text}</strong> als text word geaccepteerd.", true);
	$formFields->addMultiField("allowParents", "Toegestane Ouders", "Zie uitleg hierboven voor meer informatie", ",", true);
	$formFields->addMultiField("allowChilds", "Toegestane Kinderen", "Zie uitleg hierboven voor meer informatie", ",", true);
	$formFields->addTextField("description", "Beschrijving", "Zeer korte beschijving van de functie van de tag", 20, true);
	$formFields->addTextBlockField("example", "Voorbeeld", "Stukje voorbeeld code met gebruik van tag", 6, true);
	$checkboxes = array(
		array("name" => "breakText", "caption" => "Tekst", "description" => "", "value" => "yes", "checked" => true),
		array("name" => "breakParam", "caption" => "Parameter", "description" => "", "value" => "yes", "checked" => true)
	);
	$formFields->addCheckboxes("Woorden afbreken", "Woorden afbreken die te lang zijn", $checkboxes);
	$formFields->endGroup();

	if (isSet($tag)) {
		$formFields->addSubmit("bewerken", false);
		$form->setValue("startTag", $tag->getName());
		$form->setValue("acceptAll", $tag->allowAllParameters() ? "yes" : "no");
		$form->setValue("parameters", implode($tag->getAcceptedParameters(), " "));
		$form->setValue("endTags", implode($tag->getAcceptedEndTags(), " "));
		$form->setValue("endRequired", $tag->endTagRequired() ? "yes" : "no");
		$form->setValue("htmlReplace", $tag->getHtmlReplace());
		$form->setValue("allowParents", implode($tag->getAllowedParents()), ",");
		$form->setValue("allowChilds", implode($tag->getAllowedChilds()), ",");
		$form->setValue("description", $tag->getDescription());
		$form->setValue("example", $tag->getExample());
		$form->setValue("breakText", (($tag->getWordBreaks() == TextTag::breakText()) || ($tag->getWordBreaks() == TextTag::breakAll())));
		$form->setValue("breakParam", (($tag->getWordBreaks() == TextTag::breakParameter()) || ($tag->getWordBreaks() == TextTag::breakAll())));

	} else {
		$formFields->addSubmit("toevoegen", false);
	}

	$closeAndRefresh = false;
	$pageTitle = $TBBconfiguration->getBoardName() . ' - Instellingen';
	if (isSet($_POST['actionName']) && isSet($_POST['actionID'])) {
		if (($_POST['actionName'] == 'addTag') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			$action = new ActionHandler($feedback, $_POST);
			$action->check($TBBcurrentUser->isAdministrator(), 'Deze actie is alleen voor Administrators!');
			if (($action->correct) && ($form->checkPostedFields($feedback))) {
				$acceptAll = ($_POST['acceptAll'] == "yes") ? true : false;
				$endRequired = ($_POST['endRequired'] == "yes") ? true : false;
				$breakText = (isSet($_POST['breakText']) && ($_POST['breakText'] == "yes"));
				$breakParam = (isSet($_POST['breakParam']) && ($_POST['breakParam'] == "yes"));

				$breakStatus = TextTag::breakNone();
				if ($breakText) $breakStatus = TextTag::breakText();
				if ($breakParam) $breakStatus = TextTag::breakParameter();
				if (($breakParam) && ($breakText)) $breakStatus = TextTag::breakAll();

				$GLOBALS['TBBtagListManager']->addTBBtagToDB(
					$_POST['startTag'], $_POST['parameters'], $acceptAll, $_POST['endTags'],
					$_POST['htmlReplace'], $endRequired, $_POST['allowParents'], $_POST['allowChilds'],
					$_POST['description'], $_POST['example'], $breakStatus);
				$action->actionHandled("Tag toegevoegd!");
				$TBBsession->setMessage('tagAdd');
				$closeAndRefresh = true;
			}
		}
		if (($_POST['actionName'] == 'editTag') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			$action = new ActionHandler($feedback, $_POST);
			$action->check($TBBcurrentUser->isAdministrator(), 'Deze actie is alleen voor Administrators!');
			if (($action->correct) && ($form->checkPostedFields($feedback))) {
				$acceptAll = ($_POST['acceptAll'] == "yes") ? true : false;
				$endRequired = ($_POST['endRequired'] == "yes") ? true : false;
				$breakText = (isSet($_POST['breakText']) && ($_POST['breakText'] == "yes"));
				$breakParam = (isSet($_POST['breakParam']) && ($_POST['breakParam'] == "yes"));

				$breakStatus = TextTag::breakNone();
				if ($breakText) $breakStatus = TextTag::breakText();
				if ($breakParam) $breakStatus = TextTag::breakParameter();
				if (($breakParam) && ($breakText)) $breakStatus = TextTag::breakAll();

				$GLOBALS['TBBtagListManager']->editTBBtagInDB(
					$tagId, $_POST['startTag'], $_POST['parameters'], $acceptAll, $_POST['endTags'],
					$_POST['htmlReplace'], $endRequired, $_POST['allowParents'], $_POST['allowChilds'],
					$_POST['description'], $_POST['example'], $breakStatus);
				$action->finish("Tag bewerkt!");
				$TBBsession->setMessage('tagEdit');
				$closeAndRefresh = true;
			}
		}
	}

	include($TBBincludeDir.'popuptop.php');

	if ($closeAndRefresh) {
?>
	<script type="text/javascript">
		window.opener.document.location.href='<?=$docRoot; ?>admintags.php';
		window.close();
	</script>
<?php
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}

?>
	<h2>TBBtag&trade; <?=(isSet($tag)) ? "bewerken" : "toevoegen" ?></h2>
<?php

	$feedback->showMessages();

	if (!$TBBsession->isLoggedIn()) {
		$text = new Text();
		$text->addHTMLText("Sorry, gasten hebben geen instellingen venster!");
		$text->showText();
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}

	if (!$TBBcurrentUser->isAdministrator()) {
		$text = new Text();
		$text->addHTMLText("Sorry, dit venster is alleen voor administrators!");
		$text->showText();
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}

	$form->writeForm();

	include($TBBincludeDir.'popupbottom.php');
?>
