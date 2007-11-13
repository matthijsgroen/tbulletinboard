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
	require_once($TBBclassDir . 'Board.class.php');
	require_once($ivLibDir . 'Form.class.php');
	require_once($ivLibDir . 'FormFields.class.php');
	require_once($ivLibDir . 'formcomponents/PlainText.class.php');

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Onderwerp verwijderen';
	include($TBBincludeDir . 'popuptop.php');

	$topicID = 0;
	if (isSet($_GET['id'])) $topicID = $_GET['id'];
	if (isSet($_POST['ID'])) $topicID = $_POST['ID'];

	$topic = $GLOBALS['TBBtopicList']->getTopic($topicID);
	if (!is_object($topic)) {
		?>
		<h2>Onderwerp niet gevonden!</h2>
		<?
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}

	$boardID = $topic->board->getID();

	?>
		<h2>Onderwerp verwijderen</h2>
	<?
	if (!$TBBsession->isLoggedIn()) {
		$text = new Text();
		$text->addHTMLText("Sorry, gasten hebben geen toegang tot deze functionaliteit!");
		$text->showText();
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}

	if (!$TBBcurrentUser->isAdministrator()) {
		$text = new Text();
		$text->addHTMLText("Sorry, deze functionaliteit is alleen voor Administrators!");
		$text->showText();
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}

	if (isSet($_POST['actionName']) && isSet($_POST['actionID'])) {
		if (($_POST['actionName'] == 'deletetopic') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			if (isSet($_POST['sure']) && ($_POST['sure'] == "yes")) {
				if ($topic->delete()) {
					$TBBsession->actionHandled();
					$TBBsession->setMessage("topicDeleted");
					?>
					<script type="text/javascript">
						window.opener.location.href="<?=$docRoot; ?>index.php?id=<?=$topic->board->getID(); ?>";
						window.close();
					</script>
					<?
				} else {
					$feedback->addMessage("Onderwerp kon niet worden verwijderd!");
				}
			} else {
				$feedback->addMessage("Geen bevestiging gegeven!");
			}
		}
	}

	$form = new Form("deletetopic", "deletetopic.php");
	$formFields = new StandardFormFields();
	$formFields->activeForm = $form;
	$form->addFieldGroup($formFields);

	$form->addHiddenField('actionID', $TBBsession->getActionID());
	$form->addHiddenField('actionName', 'deletetopic');
	$form->addHiddenField('ID', $topicID);
	$formFields->startGroup("Onderwerp verwijderen");
	if ($topic->board->deletesPermanent()) {
		$form->addComponent(new FormPlainText("Waarschuwing", "", "Let op! Deze bewerking kan NIET ongedaan worden gemaakt! Het is daarom veiliger om onderwerpen die weg moeten te verplaatsen naar een archief!"));
	} else {
		$form->addComponent(new FormPlainText("Waarschuwing", "", "Weet je zeker dat je dit onderwerp wilt verwijderen?"));
	}

	$checkboxes = array(
		array(
			"name" => "sure",
			"value" => "yes",
			"caption" => "Ik weet het zeker",
			"description" => "",
			"checked" => false,
		)
	);
	$formFields->addCheckboxes("Bevestiging", "", $checkboxes);

	$formFields->addSubmit("Verwijder", false);
	$formFields->endGroup();


	$feedback->showMessages();
	$form->writeForm();

	include($TBBincludeDir.'popupbottom.php');
?>
