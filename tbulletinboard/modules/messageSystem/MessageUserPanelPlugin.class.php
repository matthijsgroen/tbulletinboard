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


	global $TBBclassDir;
	require_once($TBBclassDir . "AdminPlugin.class.php");
	global $libraryClassDir;
	require_once($libraryClassDir . "Table.class.php");

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
