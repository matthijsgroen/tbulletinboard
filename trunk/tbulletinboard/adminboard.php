<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
	 */

	require_once("folder.config.php");
	// Load the configuration
	require_once($TBBconfigDir.'configuration.php');
	require_once($TBBclassDir.'tbblib.php');

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Instellingen';
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');
	require_once($TBBclassDir.'Location.class.php');
	require_once($TBBclassDir.'Text.class.php');
	require_once($TBBclassDir.'BoardProfiles.class.php');
	require_once($TBBclassDir.'Board.class.php');
	require_once($ivLibDir.'Form.class.php');
	require_once($ivLibDir.'FormFields.class.php');

	if ($TBBcurrentUser->isMaster()) {
		if (isSet($_POST['actionName']) && isSet($_POST['actionID'])) {
			if (($_POST['actionName'] == 'editConfig') && ($_POST['actionID'] == $TBBsession->getActionID())) {
				$correct = true;
				if (!isSet($_POST['sigProfile']) && ($_POST['signatures'] == "yes")) {
					$feedback->addMessage('Geen signature profiel geselecteerd!');
					$correct = false;
				}

				if ($correct) {
					$TBBconfiguration->setBoardName($_POST['name']);
					$TBBconfiguration->setAllowSignatures(($_POST['signatures'] == "yes"));
					if ($_POST['signatures']) {
						$TBBconfiguration->setSignatureProfileID($_POST['sigProfile']);
					}
					if ($_POST['recyclebin'] == 'yes') {
						$TBBconfiguration->setBinBoardID($_POST['binboard']);
					} else {
						$TBBconfiguration->clearBinBoardID();
					}
					if ($_POST['helpboard'] != '-1') {
						$TBBconfiguration->setHelpBoardID($_POST['helpboard']);
					} else {
						$TBBconfiguration->clearHelpBoardID();
					}
					$TBBsession->actionHandled();
					$feedback->addMessage("Instellingen aangepast");
				}
			}
		}
	}


	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation('Systeem instellingen', 'adminboard.php');
	$here->showLocation();

	if (!$TBBsession->isLoggedIn()) {
		$text = new Text();
		$text->addHTMLText("Sorry, gasten hebben geen instellingen venster!");
		$text->showText();
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}

	if (!$TBBcurrentUser->isAdministrator()) {
		$text = new Text();
		$text->addHTMLText("Sorry, dit venster is alleen voor administrators!");
		$text->showText();
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}

	include($TBBincludeDir.'configmenu.php');
	$adminMenu->itemIndex = 'system';
	$adminMenu->showMenu('configMenu');

	include($TBBincludeDir.'admin_menu.php');
	$menu->itemIndex = 'stats';
	$menu->showMenu('adminMenu');

?>
	<div class="adminContent">
<?php
	if ($TBBcurrentUser->isMaster()) {
		$form = new Form("globalConfig", "adminboard.php");
		$formFields = new StandardFormFields();
		$form->addFieldGroup($formFields);
		$formFields->activeForm = $form;

		$form->addHiddenField('actionID', $TBBsession->getActionID());
		$form->addHiddenField('actionName', 'editConfig');
		$formFields->startGroup("Instellingen");
		$radioOptions = array(
			array(
				"value" => "yes",
				"caption" => "Ja",
				"description" => "Signatures aan",
				"show" => array("sigProfile"),
				"hide" => array()
			),
			array(
				"value" => "no",
				"caption" => "Nee",
				"description" => "Signatures uit",
				"show" => array(),
				"hide" => array("sigProfile")
			)
		);
		$formFields->addTextfield("name", "Naam", "Naam van het forum", 255, true, false);
		$formFields->addRadioViewHide("signatures", "Signatures", "handtekeningen onder de berichten", $radioOptions, "yes");

		$profiles = $TBBboardProfileList->getProfiles();
		$profileNames = array();
		for ($i = 0; $i < count($profiles); $i++) {
			$profileNames[$profiles[$i]->getID()] = $profiles[$i]->getName();
		}
		$form->startMarking("sigProfile");
		$formFields->addSelect("sigProfile", "Signature profiel", "voor toegestane tags", $profileNames, 0);
		$form->endMarking();

		$radioOptions = array(
			array(
				"value" => "no",
				"caption" => "Verwijderen",
				"description" => "Berichten verdwijnen om nooit meer gezien te worden",
				"show" => array(),
				"hide" => array("recyclebin")
			),
			array(
				"value" => "yes",
				"caption" => "Prullenbak",
				"description" => "Berichten kunnen weer teruggehaald worden",
				"show" => array("recyclebin"),
				"hide" => array()
			)
		);
		$formFields->addRadioViewHide("recyclebin", "Verwijderen", "Kies hoe verwijderen moet werken", $radioOptions, "yes");

		function walkBoards(&$board, $level, $result) {
			global $TBBcurrentUser;
			global $textParser;
			$subBoards = $board->getSubBoards();
			$levelStr = '';
			for ($i = 0; $i < $level; $i++) $levelStr .= '-';
			if ($level > 0) $levelStr .= ' ';
			for ($i = 0; $i < count($subBoards); $i++) {
				$subBoard = $subBoards[$i];
				$profile = $subBoard->getBoardSettings();
				if ($subBoard->canRead($TBBcurrentUser)) {
					$result[''.$subBoard->getID()] = $levelStr.htmlConvert($subBoard->getName());
					$result = walkBoards($subBoard, $level+1, $result);
				}
			}
			return $result;
		}

		$jumpBoard = $TBBboardList->getBoard(0);
		$boards = array();
		$boards = walkBoards($jumpBoard, 0, $boards);

		$form->startMarking("recyclebin");
		$formFields->addSelect("binboard", "Prullenbak", "board waar afval in verdwijnt", $boards, 0);
		$form->endMarking();

		$boards = array('-1' => "(geen)") + $boards;
		$formFields->addSelect("helpboard", "Helpboard", "board dat als helppagina dient", $boards, -1);

		$formFields->endGroup();
		$formFields->addSubmit('Wijzigen', false);

		$form->setValue("name", $TBBconfiguration->getBoardName());		
		$activeSigProfile = $TBBconfiguration->getSignatureProfile();
		if (is_Object($activeSigProfile)) {
			$form->setValue("sigProfile", $activeSigProfile->getID());
		}
		$form->setValue("signatures", $TBBconfiguration->allowSignatures() ? "yes" : "no");
		if ($TBBconfiguration->getBinBoardID() !== false) {
			$form->setValue('recyclebin', 'yes');
			$form->setValue('binboard', $TBBconfiguration->getBinBoardID());
		} else {
			$form->setValue('recyclebin', 'no');
		}
		if ($TBBconfiguration->getHelpBoardID() !== false) $form->setValue('helpboard', $TBBconfiguration->getHelpBoardID());
		else $form->setValue('helpboard', -1);

		$form->writeForm();
	}

?>
	</div>
<?php
	writeJumpLocationField(-1, "admincontrol");

	include($TBBincludeDir.'htmlbottom.php');
?>
