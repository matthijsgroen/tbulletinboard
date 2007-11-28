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
	require_once($TBBclassDir . 'library.php');
	importClass("board.Configuration");
	importClass("board.user.Session");
	if (!file_exists($docRoot . 'upload/settings/settings.php')) {
		die("The bulletinboard is not yet installed. See the instructions in the documentation folder on how to install TBB2.");
	}
	require_once($docRoot . 'upload/settings/settings.php');

	/*****************************************
	 * Account settings part
	 */

	$GLOBALS['ivTableSpace'] = 0;
	$GLOBALS['calendar_daynames'] = array("ma", "di", "wo", "do", "vr", "za", "zo");
	$boardVersion = "2.0.11 &alpha;lpha version";
		
	// set security settings
	date_default_timezone_set('CET');
	importClass("orm.MySQLDatabase");

	$database = new MySQLDatabase($dbServer, $dbDatabase, $dbUser, $dbPassword);
	$database->setTablePrefix("tbb_");
	$database->connect();

	$formTitleTemplate = "<div class=\"formtitle\">%text%</div>";
	// Prepare other classes
	importClass("interface.Messages");
	$feedback = new Messages();
	
	if (!isSet($limitedConfig) || (!$limitedConfig)) {
		$TBBconfiguration = new Configuration($database);
		//$TBBconfiguration->smtpServer = 'smtp.athome.nl';
		$TBBconfiguration->onlineTimeout = 10;
		$TBBconfiguration->imageOnlineDir = 'images/';
		$TBBconfiguration->uploadDir = $uploadPath;
		$TBBconfiguration->uploadOnlineDir = $uploadOnlinePath;
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
	}


	importClass("util.TextParser");
	$textParser = new TextParser();

?>
