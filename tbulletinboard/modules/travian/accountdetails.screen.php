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
	includeFormComponents("NumberField", "TemplateField", "Submit", "Checkbox", "PlainText", "RadioGroup", "RadioButton", "CheckboxGroup");

	global $TBBsession;
	global $formTitleTemplate;

	$database = $TBBconfiguration->getDatabase();
	require_once($moduleDir . "TravianDetails.bean.php");
	$travianSitterTable = new TravianDetailsTable($database);
	$filter = new DataFilter();
	$filter->addEquals("userID", $TBBcurrentUser->getUserID());
	$sorting = new ColumnSorting();
	$travianSitterTable->selectRows($filter, $sorting);

	if ($detailInfo = $travianSitterTable->getRow()) {
	} else {
		$detailInfo = $travianSitterTable->addRow();
	}

	$text = new Text();
	$text->addHTMLText("Hier kan je allerlei details over je reilen en zeilen in travian opgeven. De informatie is niet verplicht,".
		"en alleen alliantiegenoten kunnen deze informatie zien via je profiel pagina");
	$text->showText();
	
	$form = new Form("addSitter", "");
	$form->addComponent(new FormTemplateField($formTitleTemplate, "Grondstoffen"));
	$form->addComponent(new FormNumberField("wood", "Hout", "", 6, false, false, 
		sprintf('<img src="%1$s/images/%2$s.gif" alt="icon" />', $this->getModuleOnlineDir(), "wood"), "per uur"));
	$form->addComponent(new FormNumberField("clay", "Klei", "", 6, false, false, 
		sprintf('<img src="%1$s/images/%2$s.gif" alt="icon" />', $this->getModuleOnlineDir(), "clay"), "per uur"));
	$form->addComponent(new FormNumberField("iron", "IJzer", "", 6, false, false, 
		sprintf('<img src="%1$s/images/%2$s.gif" alt="icon" />', $this->getModuleOnlineDir(), "iron"), "per uur"));
	$form->addComponent(new FormNumberField("crop", "Graan", "", 6, false, false, 
		sprintf('<img src="%1$s/images/%2$s.gif" alt="icon" />', $this->getModuleOnlineDir(), "crop"), "per uur"));

	$form->addComponent(new FormTemplateField($formTitleTemplate, "Overschot"));
	$form->addComponent(new FormPlainText("Handel", "", 
		"De volgende grondstoffen wil ik wel ruilen", "Text", true));
	$tooMuchField = new FormCheckboxGroup("Overschot", "", "alot");
	$tooMuchField->addComponent(new FormCheckbox("Hout", sprintf('<img src="%1$s/images/%2$s.gif" alt="icon" /> %3$s', 
		$this->getModuleOnlineDir(), "wood", "Hout"), "", "woodTrade", "yes", false));
	$tooMuchField->addComponent(new FormCheckbox("Klei", sprintf('<img src="%1$s/images/%2$s.gif" alt="icon" /> %3$s', 
		$this->getModuleOnlineDir(), "clay", "Klei"), "", "clayTrade", "yes", false));
	$tooMuchField->addComponent(new FormCheckbox("IJzer", sprintf('<img src="%1$s/images/%2$s.gif" alt="icon" /> %3$s', 
		$this->getModuleOnlineDir(), "iron", "IJzer"), "", "ironTrade", "yes", false));

	$tooMuchField->addComponent(new FormCheckbox("Graan", sprintf('<img src="%1$s/images/%2$s.gif" alt="icon" /> %3$s', 
		$this->getModuleOnlineDir(), "crop", "Graan"), "", "cropTrade", "yes", false));
	$form->addComponent($tooMuchField);

	$form->addComponent(new FormTemplateField($formTitleTemplate, "Troepen"));
	$viewRace = $travianRow->getValue("race");

	if ($viewRace == 1) {
		$unitList = array("Legionnaire" => 6, "Praetorian" => 5, "Imperian" => 7, "Equites Legati" => 16, 
			"Equites Imperatoris" => 14, "Equites Caesaris" => 10, "Battering Ram" => 4, "Fire Catapult" => 3, "Senator" => 4, "Settler" => 5);
	} else
	if ($viewRace == 2) {
		$unitList = array("Clubswinger" => 7, "Spearman" => 7, "Axeman" => 6, "Scout" => 9, 
			"Paladin" => 10, "Teutonic Knight" => 9, "Ram" => 4, "Catapult" => 3, "Chief" => 4, "Settler" => 5);
	} else
	if ($viewRace == 3) {
		$unitList = array("Phalanx" => 7, "Swordmen" => 6, "Scout" => 17, "Teutatis Thunder" => 19, 
			"Druid Rider" => 16, "Haeduan" => 13, "Ram" => 4, "Trebuchet" => 3, "Chieftain" => 5, "Settler" => 5);
	}
	
	$index = 1;
	foreach($unitList as $unitName => $speed) {
		$form->addComponent(new FormNumberField("unitType" . $index, $unitName, "", 6, false, false, 
			sprintf('<img src="%1$s/images/%2$s.gif" alt="icon" />', $this->getModuleOnlineDir(), ($index + (($viewRace-1) * 10))), "stuks"));
		$index++;
	}			

	$form->addComponent(new FormTemplateField($formTitleTemplate, "Held"));
	$form->addComponent(new FormNumberField("heroLevel", "Level", "", 2, false, false, "level", 
		sprintf('<img src="%1$s/images/%2$s.gif" alt="icon" />', $this->getModuleOnlineDir(), "hero")));
	$form->addComponent(new FormNumberField("heroXP", "Level Voortgang", "", 2, false, false, 
		"", "%"));
	

	$form->addComponent(new FormTemplateField($formTitleTemplate, "Overnachting"));

	$campingField = new FormRadioGroup("Camping", "mogen anderen bij je overnachten?", "camping");
	$campingField->addComponent(new FormRadioButton("Ja", "Maar op eigen risico!", "camping", "yes", false));
	$campingField->addComponent(new FormRadioButton("Nee", "Niet veilig, of niet genoeg crop", "camping", "no", false));
	$campingField->addComponent(new FormRadioButton("Geen idee", "pff weet t niet hoor", "camping", "unknown", true));

	$form->addComponent($campingField);

	$form->addComponent(new FormSubmit("Bijwerken", "", "", "submitButton")); //$caption, $title, $description, $name, $onclick = ""

	$fieldArray = array("wood" => "woodPerHour", "clay" => "clayPerHour", "iron" => "ironPerHour", 
		"crop" => "cropPerHour", "heroLevel" => "heroLevel", "heroXP" => "heroXP");
	$tradeArray = array("woodTrade", "clayTrade", "ironTrade", "cropTrade");
	if (isSet($_POST['actionName']) && isSet($_POST['actionID'])) {
		$feedback = new Messages();
		if (($_POST['actionName'] == 'travianDetails') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			if ($form->checkPostedFields($feedback)) {
				$detailInfo->setValue("userID", $TBBcurrentUser->getUserID());
				$detailInfo->setValue("travianID", $travianRow->getValue("travianID"));

				foreach($fieldArray as $formName => $dbName) {
					if (isSet($_POST[$formName]) && ($_POST[$formName] != "")) {
						$detailInfo->setValue($dbName, $_POST[$formName]);
					} else $detailInfo->setNull($dbName);
				}
				foreach($tradeArray as $tradeName) {
					if (isSet($_POST[$tradeName]) && ($_POST[$tradeName] == "yes")) {
						$detailInfo->setValue($tradeName, true);
					} else $detailInfo->setValue($tradeName, false);
				}
				for ($index = 1; $index <= 10; $index++) {
					if (isSet($_POST["unitType".$index]) && ($_POST["unitType".$index] != "")) {
						$detailInfo->setValue("unitType".$index, $_POST["unitType".$index]);
					} else $detailInfo->setNull("unitType".$index);
				}
				if (isSet($_POST["camping"]) && ($_POST["camping"] != "")) {
					$detailInfo->setValue("camping", $_POST["camping"]);
				}
								
				$detailInfo->setValue("lastUpdated", new LibDateTime());
				$detailInfo->store();
				
				$TBBsession->actionHandled();
			}
		}
		$feedback->showMessages();
	}	
	$form->addHiddenField("actionName", "travianDetails");
	$form->addHiddenField("actionID", $TBBsession->getActionID());

	foreach($fieldArray as $formName => $dbName) {
		if (!$detailInfo->isNull($dbName)) $form->setValue($formName, $detailInfo->getValue($dbName));
	}
	foreach($tradeArray as $tradeName) {
		$form->setValue($tradeName, $detailInfo->getValue($tradeName));
	}
	for ($index = 1; $index <= 10; $index++) {
		if (!$detailInfo->isNull("unitType".$index)) $form->setValue("unitType".$index, $detailInfo->getValue("unitType".$index));
	}
	$form->setValue("camping", $detailInfo->getValue("camping"));


	print $form->writeForm();

?>
