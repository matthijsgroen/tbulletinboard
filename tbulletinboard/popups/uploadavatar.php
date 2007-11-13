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


	$pageTitle = 'Systeem avatar uploaden';
	include($TBBincludeDir.'popuptop.php');

	$avatarList = new AvatarList();
	$iconUpload = new FileUpload("avatarFile", $TBBconfiguration->uploadDir.'systemavatars/', "Avatar", 6);
	$iconUpload->setExtensions(".gif", ".jpg", ".png");
	$iconUpload->setMimeTypes("image/gif", "image/pjpeg", "image/jpeg", "image/png");
	$iconUpload->setRandomName('sav_');
	$iconUpload->setMaximumResolution(60, 60);

	if (isSet($_POST['actionName']) && isSet($_POST['actionID'])) {
		if (($_POST['actionName'] == 'addAvatar') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			$action = new ActionHandler($feedback, $_POST);
			$action->check($TBBcurrentUser->isAdministrator(), 'Alleen voor administrators!');
			$action->checkUpload($iconUpload, '');
			if ($action->correct) {
				$id = $avatarList->addSystemAvatar($iconUpload->getFileName());
				$action->finish('Avatar geupload!');
				$TBBsession->setMessage("systemAvatarUpload");
				?>
				<script type="text/javascript"><!--
					window.opener.location.href = "<?=$docRoot; ?>adminavatars.php";
					window.close();
				// -->
				</script>
				<?
			}
		}
	}

?>
	<h2>Systeem avatar uploaden</h2>
<?php
	$feedback->showMessages();

	$formFields = new StandardFormFields();
	$form = new Form("addAvatar", "uploadavatar.php");
	$form->addFieldGroup($formFields);
	$formFields->activeForm = $form;


	$form->addHiddenField("actionName", "addAvatar");
	$form->addHiddenField("actionID", $TBBsession->getActionID());
	$formFields->startGroup("Avatar uploaden");
	$iconUpload->addFormField($formFields);
	$formFields->addSubmit("Uploaden", false);
	$formFields->endGroup();
	$form->writeForm();


	include($TBBincludeDir.'popupbottom.php');
?>
