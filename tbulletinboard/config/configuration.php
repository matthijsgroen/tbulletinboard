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
	importClass("board.Session");
	require_once($TBBconfigDir . 'settings.php');

	/*****************************************
	 * Account settings part
	 */
	
	// set security settings
	date_default_timezone_set('CET');
	importClass("orm.MySQLDatabase");

	$database = new MySQLDatabase($dbServer, $dbDatabase, $dbUser, $dbPassword);
	$database->setTablePrefix("tbb_");
	$database->connect();
	
	$TBBconfiguration = new Configuration($database);
	//$TBBconfiguration->smtpServer = 'smtp.athome.nl';
	$TBBconfiguration->onlineTimeout = 10;
	$TBBconfiguration->imageOnlineDir = 'images/';
	$TBBconfiguration->uploadDir = $uploadPath;
	$TBBconfiguration->uploadOnlineDir = $uploadOnlinePath;

	/*****************************************
	 * End account settings
	 */


	$formTitleTemplate = "<div class=\"formtitle\">%text%</div>";
	// Prepare other classes
	importClass("interface.Messages");
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


	importClass("util.TextParser");
	$textParser = new TextParser();

?>
