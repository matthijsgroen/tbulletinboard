<?php

	if (get_magic_quotes_gpc() != 0) {
		foreach($_GET as $key => $value) {
			$_GET[$key] = stripslashes($value);
		}
		foreach($_POST as $key => $value) {
			if (is_array($_POST[$key])) {
				for ($i = 0; $i < count($_POST[$key]); $i++) {
					$_POST[$key][$i] = stripslashes($_POST[$key][$i]);
				}
			} else
			$_POST[$key] = stripslashes($value);
		}
	}

	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (matthijs.groen at gmail.com)
	 *@version 2.0
	 */
	require_once($ivLibDir . 'library.php');
	require_once($TBBclassDir . 'Configuration.class.php');
	require_once($TBBclassDir . 'Session.class.php');
	require_once($TBBconfigDir . 'settings.php');

	// set security settings
	//ini_set('session.use_only_cookies', true);
	//ini_set('session.use_trans_sid', false);
	//ini_set('url_rewriter.tags', '');
	
	date_default_timezone_set('CET');

	require_once($ivLibDir . 'MySQLDatabase.class.php');
	$database = new MySQLDatabase("localhost", "tbb2", "root", "msdb3181");
	//$database = new MySQLDatabase("localhost", "menhir_data", "menhir_user", "traviantest");
	$database->setTablePrefix("tbb_");
	$database->connect();
	//$database->setVersion3();
	
	$TBBconfiguration = new Configuration($database);
	//$TBBconfiguration->smtpServer = 'smtp.athome.nl';
	$TBBconfiguration->onlineTimeout = 10;
	$TBBconfiguration->imageOnlineDir = 'images/';
	$TBBconfiguration->uploadDir = '/var/www/tbb2/upload/';
	$developmentMode = true;

	//$TBBconfiguration->uploadDir = '/home/menhir/public_html/upload/';
	$TBBconfiguration->uploadOnlineDir = 'upload/';

	$formTitleTemplate = "<div class=\"formtitle\">%text%</div>";
	// Prepare other classes
	require_once($ivLibDir.'Messages.class.php');
	$feedback = new Messages();
	$TBBname = $TBBconfiguration->getBoardName();

	if (!$TBBconfiguration->isOnline()) {
		$feedback->addMessage($TBBname . ' is offline. Probeert u het later nog eens.');
		$feedback->addMessage("Reden: ".htmlConvert($TBBconfiguration->getOfflineReason()));
		$pageTitle = $TBBconfiguration->getBoardName() . ' - Offline!';
		include($TBBincludeDir.'htmltop.php');
		$feedback->showMessages();
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}
	$TBBsession = new TBBSession(); // "/tbb/", "localhost"


	require_once($ivLibDir.'TextParser.class.php');
	$textParser = new TextParser();

?>
