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
	// Load the configuration
	require_once($TBBconfigDir.'configuration.php');
	require_once($TBBclassDir.'tbblib.php');

	importClass("interface.Table");
	importClass("util.PackFile");
	importClass("board.Location");
	importClass("board.Text");
	importClass("board.ActionHandler");
	importBean("board.Plugin");
	importClass("board.ModulePlugin");

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

	require_once($libraryClassDir."TextParser.class.php");
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
