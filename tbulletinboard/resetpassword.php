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

	require_once($ivLibDir.'Form.class.php');
	require_once($TBBclassDir.'Location.class.php');
	require_once($TBBclassDir.'UserManagement.class.php');
	require_once($TBBclassDir.'Text.class.php');
	require_once($TBBclassDir.'ActionHandler.class.php');

	if (isSet($_GET['code'])) {
		if ($TBBuserManagement->resetPassword($_GET['code'])) {
			$feedback->addMessage('Wachtwoord veranderd. Het nieuwe wachtwoord is gemaild.');
		} else {
			$feedback->addMessage('Code onjuist.');
		}
	} else {
		$TBBconfiguration->redirectUri('index.php');
	}

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Wachtwoord terugzetten';
	include($TBBincludeDir.'htmltop.php');
	$feedback->showMessages();


	include($TBBincludeDir.'htmlbottom.php');

?>