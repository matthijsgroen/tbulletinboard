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
	require_once($ivLibDir.'Form.class.php');
	require_once($ivLibDir.'FormFields.class.php');
	require_once($ivLibDir.'FileUpload.class.php');
	require_once($TBBclassDir.'AvatarList.class.php');
	require_once($TBBclassDir.'ActionHandler.class.php');
	require_once($TBBclassDir.'BoardFormFields.class.php');
	require_once($TBBclassDir.'BoardProfiles.class.php');

	if (isSet($_POST['actionName']) && isSet($_POST['actionID'])) {
		if (($_POST['actionName'] == 'changeSignature') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			$TBBcurrentUser->setSignature($_POST['signature']);
		}
	}

	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation(sprintf('Instellingen voor %s', htmlConvert($TBBcurrentUser->getNickname())), 'usercontrol.php');
	$here->addLocation('Signature', 'usersignature.php');
	$here->showLocation();

	if (!$TBBsession->isLoggedIn()) {
		$text = new Text();
		$text->addHTMLText("Sorry, gasten hebben geen instellingen venster!");
		$text->showText();
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}

	include($TBBincludeDir.'configmenu.php');
	$adminMenu->itemIndex = 'user';
	$adminMenu->showMenu('configMenu');

	include($TBBincludeDir.'user_control_menu.php');
	$menu->itemIndex = 'signature';
	$menu->showMenu('adminMenu');

	if (!$TBBconfiguration->getSignaturesAllowed()) {
		?>
			<div class="adminContent">
		<?php
		$text = new Text();
		$text->addHTMLText("Signatures zijn niet toegestaan!");
		$text->showText();
		?>
			</div>
		<?php
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}
?>
	<div class="adminContent">
<?php

	$text = new Text();
	$text->addHTMLText($TBBcurrentUser->getSignatureHTML());
	$text->showText();

	$form = new Form("signatureForm", "usersignature.php");
	$formFields = new StandardFormFields();
	$form->addFieldGroup($formFields);
	$formFields->activeForm = $form;

	$boardFormFields = new BoardFormFields();
	$form->addFieldGroup($boardFormFields);
	$boardFormFields->activeForm = $form;

	$form->addHiddenField('actionID', $TBBsession->getActionID());
	$form->addHiddenField('actionName', 'changeSignature');
	$formFields->startGroup('Signature');
	$boardFormFields->addPostTextField('signature', 'Signature', '', true, true, false);
	$formFields->endGroup();
	$formFields->addSubmit('Wijzigen', false);
	$form->setValue("signature", $TBBcurrentUser->getSignature());

	$form->writeForm();


?>
	</div>
<?php
	writeJumpLocationField(-1, "usercontrol");

	include($TBBincludeDir.'htmlbottom.php');
?>
