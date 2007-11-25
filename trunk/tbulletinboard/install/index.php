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
	$TBBname = "";
	$pageTitle = "Installatie";
	$formTitleTemplate = "<div class=\"formtitle\">%text%</div>";
	$queriesExecuted = 0;
	$boardVersion = "2.0";
	date_default_timezone_set('CET');	

	if (file_exists($docRoot . 'upload/settings/settings.php')) {
		die("The bulletinboard is installed.");
	}

	importClass("interface.Form");
	importClass("interface.Messages");
	includeFormComponents("TextField", "TemplateField", "PlainText", "ButtonBar", "Submit", "PasswordField", "NumberField", 
		"EmailField", "Checkbox");
	
	function getForm($step) {
		global $formTitleTemplate;
		$maxStep = 5;
		$stepText = sprintf("(Stap %s van %s)", $step -1 , $maxStep);
		
		if ($step == 1) {
			$form = new Form("installstep", "");
			$form->addComponent(new FormTemplateField($formTitleTemplate, "Welkom bij TBB2!"));
			$form->addHiddenField("step", $step);
			$text = "Welkom bij de installatie van TBB2.\n".
				"De volgende stappen zullen we doorlopen:\n".
				"- Licentie\n".
				"- Controle directory rechten\n".
				"- Connectie naar de MySQL database\n".
				"- Naamgeving & Aanmaken eerste gebruiker\n".
				"- Installatie en update\n";
			$form->addComponent(new FormPlainText("Welkom", "", str_replace("\n", "<br />\n", $text)));
			$bar = new FormButtonBar("", "", "buttonbar");
			$bar->addComponent(new FormSubmit("Volgende &raquo;", "", "", "nextstep"));
			$form->addComponent($bar);
			return $form;
		} else if ($step == 2) {
			$form = new Form("installstep", "");
			$form->addComponent(new FormTemplateField($formTitleTemplate, "Licentie ".$stepText));
			$form->addHiddenField("step", $step);
			$text = "TBB2 maakt gebruik van de GPL3 licentie\n".
				"Om de licentie te lezen, ga naar:\n".
				"<a href=\"http://www.gnu.org/licenses/gpl.html\">GNU General Public License v3</a>\n";
			$form->addComponent(new FormPlainText("Licentie", "", str_replace("\n", "<br />\n", $text)));
			$form->addComponent(new FormCheckbox("Ik ga akkoord met deze licentie", "Licentie", "", "accept", "agree", false));
			$bar = new FormButtonBar("", "", "buttonbar");
			$bar->addComponent(new FormSubmit("Volgende &raquo;", "", "", "nextstep"));
			$form->addComponent($bar);
			return $form;
		} else if ($step == 3) {
			$form = new Form("installstep", "");
			$form->addComponent(new FormTemplateField($formTitleTemplate, "Directory controle ".$stepText));
			$form->addHiddenField("step", $step);
			$text = "Er wordt nu gekeken of er schrijfrechten zijn in de upload folders.";
			$form->addComponent(new FormPlainText("Schrijfrechten", "", str_replace("\n", "<br />\n", $text)));
			$folderCheck = array("emoticons", "modules", "settings", "systemavatars", "temp", "topicicons");
			$notWriteable = array();
			
			for ($i = 0; $i < count($folderCheck); $i++) {
				if (!is_writable("../upload/".$folderCheck[$i])) {
					$notWriteable[] = $folderCheck[$i];
				}
			}
			if (count($notWriteable) == 0) {
				$form->addComponent(new FormPlainText("Test", "", "De schrijfrechten zijn ok!"));
				$bar = new FormButtonBar("", "", "buttonbar");
				$bar->addComponent(new FormSubmit("Volgende &raquo;", "", "", "nextstep"));
				$form->addComponent($bar);
			} else {
				$form->addComponent(new FormPlainText("Test", "", "De volgende mappen in de upload folder hebben nog geen schrijfrechten: <b>".
					implode(", ", $notWriteable)."</b>"));

				$bar = new FormButtonBar("", "", "buttonbar");
				$bar->addComponent(new FormSubmit("Verversen", "", "", "retry"));
				$form->addComponent($bar);
			}
			return $form;
		} else if ($step == 4) {
			$form = new Form("installstep", "");
			$form->addComponent(new FormTemplateField($formTitleTemplate, "MySQL Database verbinding ".$stepText));
			$form->addHiddenField("step", $step);
			$text = "We gaan nu toegang proberen te maken naar de MySQL database. Er dient dus een lege database aanwezig te zijn.";
			$form->addComponent(new FormPlainText("Database", "", str_replace("\n", "<br />\n", $text)));
			$form->addComponent(new FormTextField("server", "Server", "ip of domeinnaam", 255, true, false));
			$form->setValue("server", "localhost");
			$form->addComponent(new FormNumberField("port", "Poort", "leeg is default", 4, false, false));
			$form->addComponent(new FormTextField("dbname", "Database", "naam van de database", 255, true, false));
			$form->addComponent(new FormTextField("dbuser", "Gebruiker", "", 255, true, false));
			$form->addComponent(new FormPasswordField("dbpassw", "Wachtwoord", "", 255, true, false));

			$bar = new FormButtonBar("", "", "buttonbar");
			$bar->addComponent(new FormSubmit("Volgende &raquo;", "", "", "nextstep"));
			$form->addComponent($bar);
			return $form;
		} else if ($step == 5) {
			$form = new Form("installstep", "");
			$form->addComponent(new FormTemplateField($formTitleTemplate, "Naamgeving en gebruiker aanmaken ".$stepText));
			$form->addHiddenField("step", $step);
			$form->addComponent(new FormTextField("name", "Boardnaam", "naam voor het bulletinboard", 255, true, false));
			$form->setValue("name", "TBB2");
			$form->addComponent(new FormTemplateField($formTitleTemplate, "Gebruikersaccount aanmaken"));
			$form->addComponent(new FormTextField("username", "Inlognaam", "Naam waarmee ingelogt wordt op het forum", 15, true, false));
			$form->addComponent(new FormTextField("nickname", "Nick", "Naam die bij je berichten wordt geplaatst", 30, true, false));
			$form->addComponent(new FormPasswordField("password", "Wachtwoord", "Wachtwoord om mee in te loggen", 30, true, false));
			$form->addComponent(new FormPasswordField("passwordrepeat", "Wachtwoord bevestiging", "nogmaals om vergissingen te voorkomen", 30, true, false));
			$form->addComponent(new FormEmailField("email", "Email", "Email adres voor terugvragen wachtwoord", 60, true));

			$bar = new FormButtonBar("", "", "buttonbar");
			$bar->addComponent(new FormSubmit("Volgende &raquo;", "", "", "nextstep"));
			$form->addComponent($bar);
			return $form;
		} else if ($step == 6) {
			$form = new Form("installstep", "");
			$form->addComponent(new FormTemplateField($formTitleTemplate, "Installatie en update ".$stepText));
			$form->addHiddenField("step", $step);
			$form->addHiddenField("name", $_POST['name']);
			$form->addHiddenField("username", $_POST['username']);
			$form->addHiddenField("nickname", $_POST['nickname']);
			$form->addHiddenField("password", $_POST['password']);
			$form->addHiddenField("email", $_POST['email']);

			$text = "Alle informatie voor de installatie van de database en forum is nu compleet.\n".
				"Na de druk op de knop wordt de database geinstalleerd en de gebruiker aangemaakt.\n".
				"Hoe lang de volgende stap duurt ligt aan hoe lang het update proces duurt.";
			$form->addComponent(new FormPlainText("Installatie", "", str_replace("\n", "<br />\n", $text)));

			$bar = new FormButtonBar("", "", "buttonbar");
			$bar->addComponent(new FormSubmit("Installeer", "", "", "nextstep"));
			$form->addComponent($bar);
			return $form;
		} else if ($step == 7) {
			$form = new Form("installstep", "");
			$form->addHiddenField("step", $step);
			$form->addComponent(new FormTemplateField($formTitleTemplate, "Klaar!"));
			$text = "Het forum is geinstalleerd en bijgewerkt. Klik op voltooien om naar het inlogscherm te gaan!";
			$form->addComponent(new FormPlainText("Klaar", "", str_replace("\n", "<br />\n", $text)));

			$bar = new FormButtonBar("", "", "buttonbar");
			$bar->addComponent(new FormSubmit("Voltooien", "", "", "nextstep"));
			$form->addComponent($bar);
			return $form;
		}		
		$form = new Form("installstep", "");
		return $form;
	}

	$step = 1;
	$feedback = new Messages();
	if (isSet($_POST['step'])) {
		$submitStep = $_POST['step'];
		$step = $submitStep;
		if ($submitStep == 1) $step = $submitStep + 1;
		if ($submitStep == 2) {
			if (isSet($_POST['accept']) && ($_POST['accept'] == "agree")) {
				$step = $submitStep + 1;
			} else {
				$feedback->addMessage("U dient de voorwaarden te accepteren voordat u verder kunt gaan. ".
					"Indien u niet akkoord gaat moet u de installatie afbreken.");
			}
		}
		if ($submitStep == 3) {
			if (isSet($_POST['submitValue']) && ($_POST['submitValue'] == "nextstep")) $step = $submitStep + 1;
		}
		if ($submitStep == 4) {
			$form = getForm($submitStep);
			if ($form->checkPostedFields($feedback)) {
				importClass("orm.MySQLDatabase");
				$port = 3306;
				
				$testConnection = new MySQLDatabase($_POST['server'], $_POST['dbname'], $_POST['dbuser'], $_POST['dbpassw'], $port);
				$testConnection->connect();
				if (!$testConnection->isConnected()) {
					$feedback->addMessage("Er kon geen verbinding worden gemaakt met de database. Controleer de gegevens");
				} else {
					$template = file_get_contents("settings.template.txt");
					$absPath = substr(__file__, 0, strlen(__file__) - strlen("/install/index.php")) . "/upload/";
					file_put_contents("../upload/settings/settings.php", sprintf($template,
						$absPath, "upload/", $_POST['server'], $_POST['dbname'], $_POST['dbuser'], $_POST['dbpassw'], $port));
					$step = $submitStep + 1;
				}
			}
		}
		if ($submitStep == 5) {
			$form = getForm($submitStep);
			if ($form->checkPostedFields($feedback)) $step = $submitStep + 1;
		}	
		if ($submitStep == 6) { // the big one...
			include("../upload/settings/settings.php");
			importClass("orm.MySQLDatabase");

			$installConnection = new MySQLDatabase($dbServer, $dbDatabase, $dbUser, $dbPassword, $dbPort);
			$installConnection->setTablePrefix("tbb_");
			$installConnection->connect();
			importClass("updater.ModuleUpdater");
			$installer = new ModuleUpdater("core", "patches/", $installConnection);
			$installer->importDump("tbb-core-1.0.sql");
			// insert userinfo before patching
			$now = new LibDateTime();
			$insSettings = sprintf("INSERT INTO `tbb_globalsettings`(name, online, onlineTime, adminContact) VALUES('%s', 'yes', '%s', '%s')",
				addSlashes($_POST['name']), $now->toString("Y-m-d H:i:s"), addSlashes($_POST['email']));
			$installConnection->executeQuery($insSettings);
			$insSettings = sprintf("INSERT INTO `tbb_users`(username, date, nickname) VALUES('%s', '%s', '%s')",
				addSlashes($_POST['username']), $now->toString("Y-m-d H:i:s"), addSlashes($_POST['nickname']));
			$result = $installConnection->executeQuery($insSettings);
			$userID = $result->getInsertID();
			$insSettings = sprintf("INSERT INTO `tbb_usersettings`(userID, password, email) VALUES('%s', '%s', '%s')",
				$userID, md5(trim($_POST["password"])), addSlashes($_POST['email']));
			$result = $installConnection->executeQuery($insSettings);
			$insSettings = sprintf("INSERT INTO `tbb_administrators`(userID, security, typeAdmin) VALUES('%s', '%s', '%s')",
				$userID, "high", "master");
			$result = $installConnection->executeQuery($insSettings);
			$installer->executePatches();
			$step = $submitStep + 1;			
		}
		if ($submitStep == 7) {
			/* Redirect to a different page in the current directory that was requested */
			$host  = $_SERVER['HTTP_HOST'];
			$path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
			$uri   = substr($path, 0, strlen($path) - strlen("install/"));
			$extra = 'login.php';
			header("Location: http://$host$uri/$extra");
			exit;
		}
	}


	include($TBBincludeDir.'htmltop.php');
	$feedback->showMessages();
	$form = getForm($step);
	$form->writeForm();

	include($TBBincludeDir.'htmlbottom.php');
?>
