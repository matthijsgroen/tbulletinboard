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

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Plugin Info';
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');

	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation('Plugin Info', 'plugininfo.php');
	$here->showLocation();

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
	$table->setHeader("Plugins");
	$table->setAlignment("left");

	require_once($ivLibDir."TextParser.class.php");
	$textParser = new TextParser();

	$currGroup = "";
	while ($pluginRow = $pluginTable->getRow()) {
		if ($currGroup != $pluginRow->getValue("group")) {
			$moduleInfo = $moduleTable->getRow();
			$table->addGroup(sprintf("%s (v %s) by %s", $moduleInfo->getValue("name"), $moduleInfo->getValue("version"),
				$moduleInfo->getValue("author")));
			$table->addRow($textParser->parseMessageText($moduleInfo->getValue("description"), false, false));
			
			$currGroup = $pluginRow->getValue("group");
		}
		
	}
	$table->showTable();

?>
	</div>
<?php
	writeJumpLocationField(-1, "plugincontrol");

	include($TBBincludeDir.'htmlbottom.php');
?>
