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

	// Load the configuration
	require_once("folder.config.php");
	require_once($TBBconfigDir.'configuration.php');
	require_once($TBBclassDir.'tbblib.php');
	
	importClass("interface.Table");
	importClass("util.PackFile");
	importClass("interface.Location");
	importClass("interface.Text");
	importClass("board.ActionHandler");
	importBean("board.plugin.Plugin");
	importClass("board.plugin.ModulePlugin");

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Instellingen';
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');

	if (isSet($_GET['action']) && (($_GET['action'] == 'activate') || ($_GET['action'] == 'deactivate'))) {
		$pluginID = $_GET['plugin'];
		$pluginTable = new PluginTable($database);
		$pluginRow = $pluginTable->getRowByKey($pluginID);
		if (is_Object($pluginRow)) {
			$plugin = $TBBModuleManager->getPluginByID($pluginID);
			if ($_GET['action'] == 'activate') {
				$plugin->activate();
			} else {
				$plugin->deactivate();			
			}			
			$pluginRow->setValue("active", ($_GET['action'] == 'activate'));
			$pluginRow->store();
			$feedback->addMessage(sprintf('Plugin ge%sactiveerd', ($_GET['action'] == 'activate') ? "" : "de" ));
		}
	}

	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation('Plugin instellingen', 'adminmodules.php');
	$here->addLocation('Module beheer', 'adminmodsearch.php');
	$here->showLocation();

	if (!$TBBsession->isLoggedIn()) {
		$text = new Text();
		$text->addHTMLText("Sorry, gasten hebben geen instellingen venster!");
		$text->showText();
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}

	if (!$TBBcurrentUser->isAdministrator()) {
		$text = new Text();
		$text->addHTMLText("Sorry, dit venster is alleen voor administrators!");
		$text->showText();
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}

	include($TBBincludeDir.'configmenu.php');
	$adminMenu->itemIndex = 'plugin';
	$adminMenu->showMenu('configMenu');

	include($TBBincludeDir.'admin_extra.php');
	$menu->itemIndex = 'modulelist';
	$menu->showMenu('adminMenu');

?>
	<script type="text/javascript"><!--
		var selectedPlugin = '';
		var selectedActive = '';
		function moduleSelect(id, active) {
			selectedPlugin = id;
			selectedActive = active;
		}

		function moduleDelete() {
			if (selectedPlugin == '') {
				alert("Kies eerst een module");
				return;
			}
			if (selectedActive != 'group') {
				alert("Plugins kunnen niet los worden verwijderd. Kies de gehele module");
				return;
			}
			confirm("Weet je zeker dat je deze module wilt verwijderen?");
			//document.location.href="?action=activate&plugin="+selectedPlugin;
		}

		function moduleActivate() {
			if (selectedPlugin == '') {
				alert("Kies eerst een plugin");
				return;
			}
			if (selectedActive == 'group') {
				alert("Een module kan niet in geheel worden geactiveerd. Kies elke plugin afzonderlijk");
				return;
			}
			if (selectedActive == 'yes') {
				document.location.href="?action=deactivate&plugin="+selectedPlugin;
			} else {
				document.location.href="?action=activate&plugin="+selectedPlugin;
			}
		}
	// -->
	</script>
	<div class="adminContent">
<?php
	$menu = new Menu();
	$menu->addItem("add", "", "Toevoegen", $TBBcurrentUser->isMaster() ? "javascript:popupWindow('popups/uploadmodule.php', 600, 400, 'uploadModule')" : "", "", "", 0, false, 'Nieuwe module installeren');
	$menu->addItem("delete", "", "Verwijderen", $TBBcurrentUser->isMaster() ? "javascript:moduleDelete();" : "", "", "", 0, false, 'Module verwijderen');
	$menu->addItem("activate", "", "(de)activeren", "javascript:moduleActivate();", "", "", 0, false, 'plugin activeren');
	$menu->showMenu('toolbar');

	$pluginTable = new PluginTable($database);
	$dataFilter = new DataFilter();
	$columnSorting = new ColumnSorting();
	$columnSorting->addColumnSort("group", true);
	$columnSorting->addColumnSort("type", true);
	$pluginTable->selectRows($dataFilter, $columnSorting);

	$moduleTable = new ModuleTable($database);
	$dataFilter = new DataFilter();
	$columnSorting = new ColumnSorting();
	$columnSorting->addColumnSort("group", true);
	$moduleTable->selectRows($dataFilter, $columnSorting);

	$table = new Table();
	$table->setClass("table moduleTable");
	$table->setHeader("ID", "group", "Soort plugin", "Naam", "Versie", "Actief", "active");
	$table->setRowClasses("pluginID", "moduleID", "pluginType", "pluginName", "pluginVersion", "pluginActive", "active");
	$table->allowSubgroups(0, true);
	
	if (isSet($GLOBALS['developmentMode']) && ($GLOBALS['developmentMode'] === true)) {
		importClass("util.PackFile");
		$dir = substr(__file__, 0, strrpos(__file__, "/")) . "/modules";
	}	
	

	$currGroup = "";
	while ($pluginRow = $pluginTable->getRow()) {
		if ($currGroup != $pluginRow->getValue("group")) {
			$moduleInfo = $moduleTable->getRow();

			$download = "";
			if (isSet($GLOBALS['developmentMode']) && ($GLOBALS['developmentMode'] === true)) {
				$file = $moduleInfo->getValue("group");
				if (is_dir($dir . "/" . $file) && (strpos($file, ".") !== 0)) {
					$download = " [<a href=\"upload/modules/".$file.".tbbmod\">download</a>]";
					$packFile = new PackFile("tbbmod");
					$packFile->addFolder($dir . "/" . $file);
					$packFile->save($dir."/../upload/modules/".$file.'.tbbmod');
				}
			}
			
			$table->addGroup(sprintf("%s (v %s) by %s %s", $moduleInfo->getValue("name"), $moduleInfo->getValue("version"),
				$moduleInfo->getValue("author"), $download));

			$currGroup = $pluginRow->getValue("group");
		}
		$table->addRow($pluginRow->getValue("ID"), $pluginRow->getValue("group"),
			$TBBModuleManager->getNormalPluginTypeName($pluginRow->getValue("type")),
			htmlConvert($pluginRow->getValue("name")), htmlConvert($pluginRow->getValue("version")." (build: ".$pluginRow->getValue("build").")"),
			($pluginRow->getValue("active") ? "&bull;" : ""),
			($pluginRow->getValue("active") ? "yes" : "no"));
	}
	//$table->endSubgroup();
	$table->hideColumn(0);
	$table->hideColumn(1);
	$table->hideColumn(6);

	$table->setRowSelect(array(0, 6), "moduleSelect");
	$table->showTable();

?>
	</div>
<?php
	writeJumpLocationField(-1, "plugincontrol");

	include($TBBincludeDir.'htmlbottom.php');
?>
