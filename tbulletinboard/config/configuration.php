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

	require_once($libraryClassDir . 'library.php');
	require_once($TBBclassDir . 'Configuration.class.php');
	require_once($TBBclassDir . 'Session.class.php');
	require_once($TBBconfigDir . 'settings.php');

	// set security settings
	//ini_set('session.use_only_cookies', true);
	//ini_set('session.use_trans_sid', false);
	//ini_set('url_rewriter.tags', '');
	
	date_default_timezone_set('CET');

	require_once($libraryClassDir . 'MySQLDatabase.class.php');
	$database = new MySQLDatabase("localhost", "menhir_data", "root", "msdb3181");
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
	require_once($libraryClassDir.'Messages.class.php');
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


	require_once($libraryClassDir.'TextParser.class.php');
	$textParser = new TextParser();

?>
