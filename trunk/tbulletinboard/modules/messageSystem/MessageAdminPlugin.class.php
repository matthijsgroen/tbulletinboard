<?php

	global $TBBclassDir;
	require_once($TBBclassDir . "AdminPlugin.class.php");
	global $ivLibDir;
	require_once($ivLibDir . "Table.class.php");

	class MessageAdminPlugin extends AdminPlugin {
		var $privateVars;

		function MessageAdminPlugin() {
			$this->AdminPlugin();
		}

		function handlePageActions(&$feedback) {
		}

		function createMenu(&$menu) {
			$menu->addGroup("messages", "Berichten");
			$menu->addItem("mess_settings", "messages", "Bericht instellingen", 
				"adminplugin.php?id=".$this->getModulename(), '', '', 0, false, '');
		}

		function selectMenuItem(&$menu) {
			$menu->itemIndex = "mess_settings";
		}

		function getLocation(&$location) {
			$location->addLocation("Bericht instellingen", "panelplugin.php?id=".$this->getModuleName()."&screen=inbox");
		}

		function getPageTitle() {
			return "Bericht instellingen";
		}

		function getPage() {
			$moduleDir = $this->getModuleDir();
			$step = 1;
			include $moduleDir . "adminsettings.screen.php";

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
