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

	importClass("board.plugin.AdminPlugin");	
	importClass("interface.Table");	

	class TravianUserPanelAlliancePlugin extends AdminPlugin {
		var $privateVars;

		function TravianUserPanelAlliancePlugin() {
			$this->AdminPlugin();
		}

		function handlePageActions(&$feedback) {
		}

		function createMenu(&$menu) {
			global $TBBcurrentUser;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			
			$selectQuery = sprintf("SELECT * FROM tbb_travian_user WHERE tbbID='%s'", $TBBcurrentUser->getUserID());
			$selectResult = $database->executeQuery($selectQuery);
			if ($isRow = $selectResult->getRow()) {
			} else return;

			
			$menu->addGroup("travian", "Travian");
			$menu->addItem("travianmap", "travian", "Kaartje", 
				"panelplugin.php?id=".$this->getPluginID()."&screen=travianmap", '', '', 0, false, '');

		}

		function selectMenuItem(&$menu) {
			if ($_GET['screen'] == "travianmap") $menu->itemIndex = "travianmap";
		}

		function getLocation(&$location) {
			if ($_GET['screen'] == "travianmap")
				$location->addLocation("Travian kaart", "panelplugin.php?id=".$this->getPluginID()."&screen=travianmap");
		}

		function getPageTitle() {
			return "Travian Kaart";
		}

		function getPage() {
			global $TBBcurrentUser;
			global $TBBconfiguration;
			global $TBBsession;
			$database = $TBBconfiguration->getDatabase();
			$moduleDir = $this->getModuleDir();
			$step = 1;
			
			require_once($moduleDir . "TravianUser.bean.php");
			$travianUserTable = new TravianPlayerTable($database);
			$filter = new DataFilter();
			$filter->addEquals("tbbID", $TBBcurrentUser->getUserID());
			$travianUserTable->selectRows($filter, new ColumnSorting());
			if ($travianRow = $travianUserTable->getRow()) {
			} else return;
			
			if (($_GET['screen'] == "travianmap") && ($step == 1)) 
				include $moduleDir . "travianmap.screen.php";

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
