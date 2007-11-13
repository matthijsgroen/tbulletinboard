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

	require_once($ivLibDir.'TextParser.class.php');
	require_once($ivLibDir.'Menu.class.php');
	require_once($ivLibDir.'Form.class.php');
	require_once($ivLibDir.'FormFields.class.php');
	require_once($ivLibDir.'FileUpload.class.php');

	require_once($TBBclassDir.'TagListManager.class.php');
	require_once($TBBclassDir.'Text.class.php');
	require_once($TBBclassDir.'ActionHandler.class.php');
	require_once($TBBclassDir.'TopicIconList.class.php');

	$pageTitle = 'Systeem avatar uploaden';
	include($TBBincludeDir.'popuptop.php');

	$iconUpload = new FileUpload("imageFile", $TBBconfiguration->uploadDir . 'topicicons/', "Bestand", 3);
	$iconUpload->setExtensions(".gif");
	$iconUpload->setMimeTypes("image/gif");

	$topicIconList = new TopicIconList();
	if (isSet($_POST['actionName']) && isSet($_POST['actionID'])) {
		if (($_POST['actionName'] == 'addTopicIcon') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			$action = new ActionHandler($feedback, $_POST);
			$action->check($TBBcurrentUser->isAdministrator(), 'Deze actie is alleen voor Administrators!');
			$action->notEmpty('name', 'Geen naam gegeven!');
			if ($action->correct && ($iconUpload->checkUpload($feedback))) {
				$topicIconList->addIcon(trim($_POST['name']), $iconUpload->getFileName());
				$action->finish('Onderwerp icoon toegevoegd');
				$TBBsession->setMessage("uploadIcon");
				?>
				<script type="text/javascript"><!--
					window.opener.location.href = "<?=$docRoot; ?>admintopicicons.php";
					window.close();
				// -->
				</script>
				<?
			}
		}
	}


?>
	<h2>Onderwerp icoon uploaden</h2>
<?php
	$feedback->showMessages();

	$form = new Form("addTopicIcon", "uploadicon.php");
	$formFields = new StandardFormFields();
	$form->addFieldGroup($formFields);
	$formFields->activeForm = $form;
	$form->addHiddenField("actionName", "addTopicIcon");
	$form->addHiddenField("actionID", $TBBsession->getActionID());
	$formFields->startGroup("Onderwerp icoon uploaden");
	$formFields->addTextField("name", "Naam", "Naam van icoon", 30);
	$iconUpload->addFormField($formFields);
	$formFields->addSubmit("Uploaden", false);
	$formFields->endGroup();
	$form->writeForm();


	include($TBBincludeDir.'popupbottom.php');
?>
