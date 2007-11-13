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
	require_once($TBBclassDir.'TagListManager.class.php');
	require_once($TBBclassDir.'Text.class.php');
	require_once($ivLibDir.'Form.class.php');
	require_once($ivLibDir.'FormFields.class.php');
	require_once($ivLibDir.'TextParser.class.php');
	require_once($ivLibDir.'Menu.class.php');
	require_once($TBBclassDir.'ActionHandler.class.php');
	require_once($TBBclassDir.'Board.class.php');

	$pageTitle = 'Profiel tags';
	include($TBBincludeDir.'popuptop.php');

	if (isSet($_GET["id"])) $profileID = $_GET["id"];
	if (isSet($_POST["id"])) $profileID = $_POST["id"];

	if (!isSet($profileID)) {
		?>
			<h2>Geen id meegegeven</h2>
		<?php
		$text = new Text();
		$text->addHTMLText("Geen profiel id opgegeven!");
		$text->showText();
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}
	$boardProfile = $GLOBALS['TBBboardProfileList']->getBoardProfile($profileID);
	if (!is_Object($boardProfile)) {
		?>
			<h2>Profiel niet gevonden!</h2>
		<?php
		$text = new Text();
		$text->addHTMLText("Geen geldige profiel id opgegeven!");
		$text->showText();
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}

	if (!$TBBcurrentUser->isAdministrator()) {
		?>
			<h2>Geen toegang!</h2>
		<?php
		$text = new Text();
		$text->addHTMLText("Dit scherm is alleen voor administrators!");
		$text->showText();
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}

	if (isSet($_POST['actionName']) && isSet($_POST['actionID'])) {
		if (($_POST['actionName'] == 'editTags') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			$action = new ActionHandler($feedback, $_POST);
			$action->check($TBBcurrentUser->isAdministrator(), 'Deze actie is alleen voor Administrators!');
			$boardProfile->updateAllowedTags(explode(",", $_POST["tags"]));
			$TBBsession->actionHandled();
			$feedback->addMessage("Toegestane tags bijgewerkt");
		}
	}

?>
	<h2>TBBTags&trade; van profiel: "<?=$boardProfile->getName() ?>"</h2>
<?php

	$feedback->showMessages();

	$navMenu = new Menu();
	$navMenu->itemIndex = "tags";
	$navMenu->addItem('profile', '', 'Algemeen', 'editboardprofile.php?id='.$profileID, '', '', 0, false, '');
	$navMenu->addItem('tags', '', 'Tags', 'profiletags.php?id='.$profileID, '', '', 0, false, '');
	$navMenu->addItem('boards', '', 'Boards ('.$boardProfile->getNrUsed().')', 'profileboards.php?id='.$profileID, '', '', 0, false, '');
	$navMenu->addItem('topics', '', 'Onderwerpen', 'profiletopics.php?id='.$profileID, '', '', 0, false, '');
	$navMenu->showMenu("configMenu");


	$profileTags = new Form("profileTags", "profiletags.php");
	$profileTags->addHiddenField('id', $profileID);
	$profileTags->addHiddenField("actionID", $TBBsession->getActionID());
	$profileTags->addHiddenField("actionName", "editTags");


	$formFields = new StandardFormFields();
	$profileTags->addFieldGroup($formFields);
	$formFields->activeForm = $profileTags;

	$formFields->startGroup("Profiel tags instellen");
	$options = array();

	$completeList = $GLOBALS['TBBtagListManager']->getTBBtags();
	for ($i = 0; $i < $completeList->getTagCount(); $i++) {
		$tag = $completeList->getTag($i);
		$options[$tag->getID()] = $tag->getDescription();
	}


	$formFields->addMultiSelect("tags", "Toegestane Tags&trade;", "De tags in de bovenste box zijn toegestaan in dit profiel", $options, ",", false);
	$formFields->endGroup();
	$formFields->addSubmit("Instellen", false);

	$tagList = $boardProfile->getTBBtagList();
	$selectedTags = array();
	for ($i = 0; $i < $tagList->getTagCount(); $i++) {
		$tag = $tagList->getTag($i);
		$selectedTags[] = $tag->getID();
	}
	$idList = implode(",", $selectedTags);
	$profileTags->setValue("tags", $idList);

	$profileTags->writeForm();


	include($TBBincludeDir.'popupbottom.php');
?>
