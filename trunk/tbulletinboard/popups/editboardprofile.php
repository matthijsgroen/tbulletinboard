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

	importClass("board.BoardProfiles");	
	importClass("board.Text");	
	importClass("board.Location");	

	importClass("interface.Form");	
	importClass("interface.Menu");	
	importClass("interface.FormFields");	

	$boardID = (isSet($_GET['id']) && (is_numeric($_GET['id']))) ? $_GET['id'] : -1;
	$boardID = (isSet($_POST['profileID']) && (is_numeric($_POST['profileID']))) ? $_POST['profileID'] : $boardID;
	if ($boardID != -1)
		$boardProfile = $GLOBALS['TBBboardProfileList']->getBoardProfile($boardID);
	else $boardProfile = false;

	if (isSet($_POST['actionName']) && isSet($_POST['actionID'])) {
		if (($_POST['actionName'] == 'addProfile') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			$correct = true;
			$boardName = trim($_POST["name"]);
			if (strLen($boardName) == 0) {
				$feedback->addMessage("Geen boardnaam ingevult!");
				$correct = false;
			}
			if ($correct) {
				$secLevel = ($TBBcurrentUser->isMaster()) ? $_POST['seclevel'] : 0;
				$countPosts = ($_POST['incCount'] == "0");
				$signatures = ($_POST['signatures'] == "1");
				$newID = $GLOBALS['TBBboardProfileList']->addBoardProfile($boardName, $_POST['viewMode'], $secLevel, $countPosts, $signatures);
				if ($newID !== false) {
					$TBBsession->actionHandled();
					$boardID = $newID;
					$boardProfile = $GLOBALS['TBBboardProfileList']->getBoardProfile($boardID);
					$feedback->addMessage("Profiel toegevoegd");
				} else {
					$feedback->addMessage('Profiel kon niet worden toegevoegd!');
				}
			}
		}
		if (($_POST['actionName'] == 'editProfile') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			$correct = true;
			$boardName = trim($_POST["name"]);
			if (strLen($boardName) == 0) {
				$feedback->addMessage("Geen boardnaam ingevult!");
				$correct = false;
			}
			if ($correct) {
				$secLevel = ($TBBcurrentUser->isMaster()) ? $_POST['seclevel'] : 0;
				$countPosts = ($_POST['incCount'] == "0");
				$signatures = ($_POST['signatures'] == "1");
				if ($boardProfile->updateSettings($boardName, $_POST['viewMode'], $secLevel, $countPosts, $signatures)) {
					$TBBsession->actionHandled();
					$feedback->addMessage("Profiel bijgewerkt");
				} else {
					$feedback->addMessage('Profiel kon niet worden bewerkt!');
				}
			}
		}
	}

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Instellingen';
	include($TBBincludeDir.'popuptop.php');

	if ($boardID != -1) {
		?>
			<h2>Board profiel bewerken</h2>
		<?php
		$navMenu = new Menu();
		$navMenu->itemIndex = "profile";
		$navMenu->addItem('profile', '', 'Algemeen', 'editboardprofile.php?id='.$boardID, '', '', 0, false, '');
		$navMenu->addItem('tags', '', 'Tags', 'profiletags.php?id='.$boardID, '', '', 0, false, '');
		$navMenu->addItem('boards', '', 'Boards ('.$boardProfile->getNrUsed().')', 'profileboards.php?id='.$boardID, '', '', 0, false, '');
		$navMenu->addItem('topics', '', 'Onderwerpen', 'profiletopics.php?id='.$boardID, '', '', 0, false, '');
		$navMenu->showMenu("configMenu");
	}	else {
		?>
			<h2>Board profiel toevoegen</h2>
		<?php
	}

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

	if (($boardID != -1) && (!is_Object($boardProfile))) {
		$text = new Text();
		$text->addHTMLText("Boardprofiel kon niet gevonden worden!");
		$text->showText();
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}

	$feedback->showMessages();

	// Adding a profile
	$editForm = new Form('addProfile', 'editboardprofile.php');
	$formFields = new StandardFormFields();
	$editForm->addFieldGroup($formFields);
	$formFields->activeForm = $editForm;

	$editForm->addHiddenField("actionID", $TBBsession->getActionID());
	if ($boardID == -1) {
		$editForm->addHiddenField("actionName", "addProfile");
		$formFields->startGroup("Board profiel toevoegen");
	} else {
		$editForm->addHiddenField("actionName", "editProfile");
		$editForm->addHiddenField("profileID", $boardID);
		$formFields->startGroup("Board profiel bewerken");
	}
	$formFields->addTextField("name", "Naam", "Naam van het profiel", 50);
	$values = array();
	$values['standard'] = "Standaard (dicht)";
	$values['open'] = "Opengeklapt";
	$values['hidden'] = "Onzichtbaar";
	$values['openHidden'] = "Opengeklapt en Onzichtbaar";
	$formFields->addSelect("viewMode", "Modus", "Hoe het board wordt getoont", $values, "standard");
	if ($TBBcurrentUser->isMaster()) {
		$secLevels = array();
		$secLevels[0] = "Geen";
		$secLevels[1] = "Laag";
		$secLevels[2] = "Middel";
		$secLevels[3] = "Hoog";
		$formFields->addSelect("seclevel", "Beveiliging", "stel in welke administrators toegang hebben", $secLevels, 0);
	}
	$radioOptions = array(
		array('value' => "0", 'caption' => "Ja", 'description' => ''),
		array('value' => "1", 'caption' => "Nee", 'description' => '')
	);

	$formFields->addRadio("incCount", "Berichten tellen", "Tellen berichten in dit board mee met postcount?", $radioOptions, "0");
	$formFields->addRadio("signatures", "Signatures uitschakelen", "Altijd signatures verstoppen?", $radioOptions, "1");
	$formFields->endGroup();
	if ($boardID == -1) {
		$formFields->addSubmit("Toevoegen", false);
	} else {
		$formFields->addSubmit("Bewerken", false);
		$editForm->setValue("name", htmlConvert($boardProfile->getName()));
		$editForm->setValue("viewMode", $boardProfile->getViewModusRaw());
		//todo: security level updatable.
		$editForm->setValue("incCount", ($boardProfile->increasePostCount()) ? "0" : "1");
		$editForm->setValue("signatures", ($boardProfile->allowSignatures()) ? "1" : "0");
	}

	$editForm->writeForm();

	include($TBBincludeDir.'popupbottom.php');
?>
