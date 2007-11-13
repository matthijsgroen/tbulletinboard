<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
	 */

	require_once("folder.config.php");
	// Load the configuration
	require_once($ivLibDir.'Table.class.php');
	require_once($ivLibDir.'PackFile.class.php');
	require_once($TBBconfigDir.'configuration.php');
	require_once($TBBclassDir.'tbblib.php');
	require_once($TBBclassDir.'Location.class.php');
	require_once($TBBclassDir.'Text.class.php');
	require_once($TBBclassDir.'ActionHandler.class.php');
	require_once($TBBclassDir.'Plugin.bean.php');
	require_once($TBBclassDir.'ModulePlugin.class.php');

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Instellingen';
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');

	if (isSet($_GET['action']) && (($_GET['action'] == 'activate') || ($_GET['action'] == 'deactivate'))) {
		$pluginID = $_GET['plugin'];
		$pluginTable = new PluginTable($database);
		$pluginRow = $pluginTable->getRowByKey($pluginID);
		$pluginRow->setValue("active", ($_GET['action'] == 'activate'));
		$pluginRow->store();
		$feedback->addMessage(sprintf('Plugin ge%sactiveerd', ($_GET['action'] == 'activate') ? "" : "de" ));
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

	$currGroup = "";
	while ($pluginRow = $pluginTable->getRow()) {
		if ($currGroup != $pluginRow->getValue("group")) {
			$moduleInfo = $moduleTable->getRow();
			/*
			$table->startSubgroup(true, $pluginRow->getValue("group"),
				$pluginRow->getValue("group"), $moduleInfo->getValue("name"),
				$moduleInfo->getValue("author"), $moduleInfo->getValue("version"), "", "group");
			*/
			$table->addGroup(sprintf("%s (v %s) by %s", $moduleInfo->getValue("name"), $moduleInfo->getValue("version"),
				$moduleInfo->getValue("author")));

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
