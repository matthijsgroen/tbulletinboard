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

	require_once($libraryClassDir.'TextParser.class.php');
	require_once($libraryClassDir.'Menu.class.php');
	require_once($libraryClassDir.'Form.class.php');
	require_once($libraryClassDir.'FormFields.class.php');
	require_once($libraryClassDir.'FileUpload.class.php');

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