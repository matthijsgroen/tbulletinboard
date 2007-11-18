<?php

	global $TBBclassDir;
	require_once($TBBclassDir . "AdminPlugin.class.php");
	global $ivLibDir;
	require_once($ivLibDir . "Table.class.php");

	class MessageUserPanelPlugin extends AdminPlugin {
		var $privateVars;

		function MessageUserPanelPlugin() {
			$this->AdminPlugin();
		}

		function handlePageActions(&$feedback) {
		}

		function createMenu(&$menu) {
			$menu->addGroup("messages", "Berichten");
			$menu->addItem("inbox", "messages", "Prive Berichten", 
				"panelplugin.php?id=".$this->getModulename()."&screen=inbox", '', '', 0, false, '');
		}

		function selectMenuItem(&$menu) {
			$menu->itemIndex = "inbox";
		}

		function getLocation(&$location) {
			$location->addLocation("Berichten", "panelplugin.php?id=".$this->getModuleName()."&screen=inbox");
		}

		function getPageTitle() {
			return "Inbox";
		}

		function getPage() {
			$moduleDir = $this->getModuleDir();
			$step = 1;
			if (($_GET['screen'] == "inbox") && ($step == 1)) 
				include $moduleDir . "mailbox.screen.php";

		}

		function handlePopupActions(&$feedback) {
		}

		function getPopupTitle() {
			return "Onbekend venster!";
		}

		function getPopupPage() {
		}

	}

?>