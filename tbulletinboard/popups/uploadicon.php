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

	importClass("board.TagListManager");	
	importClass("board.Text");	
	importClass("util.TextParser");	
	importClass("interface.Menu");	
	importClass("interface.Form");	
	importClass("interface.FormFields");	
	importClass("util.FileUpload");	
	importClass("board.ActionHandler");	
	importClass("board.TopicIconList");	

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
