<?
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
	importClass("board.Board");

	function buildJumpBoards($boardStructure, $level, $selectedBoard) {
		global $TBBcurrentUser;
		global $textParser;
		global $TBBboardList;
		$subBoards = $boardStructure["childs"];
		$levelStr = '';
		for ($i = 0; $i < $level; $i++) $levelStr .= '-';
		if ($level > 0) $levelStr .= ' ';
		for ($i = 0; $i < count($subBoards); $i++) {
			$subBoard = $subBoards[$i];
			if (($TBBboardList->canReadBoard($subBoard['ID'], $TBBcurrentUser)) && ((!$subBoard["hidden"]) || ($TBBcurrentUser->isActiveAdmin()))) {
				writeJumpSelectOption("index.php?id=".$subBoard["ID"], $levelStr.htmlConvert($subBoard["name"]), $subBoard["ID"], $selectedBoard);
				buildJumpBoards($subBoard, $level+1, $selectedBoard);
			}
		}
	}

	function writeJumpSelectOption($url, $name, $alias, $selectedLocation) {
		print "\t\t".sprintf('<option value="%s"%s>%s</option>', $url, ($alias == $selectedLocation) ? ' selected="selected"' : "", $name)."\n";
	}

	function writeJumpLocationField($selectedBoard, $selectedLocation) {
		global $TBBboardList;
		global $TBBsession;
		global $TBBcurrentUser;
		global $TBBconfiguration;
		global $TBBModuleManager;
?>
<div class="center">
	<form id="locJump" action="index.php" method="post">
		<div id="locationJump">
			<input type="hidden" name="actionName" value="jump" />
			<span class="locationTitle">Ga naar:</span>
			<select name="page" onchange="form.submit();">
<?php
		print "\t\t".'<optgroup label="Gebruiker">'."\n";
		if ($TBBsession->isLoggedIn()) {
			writeJumpSelectOption("usercontrol.php", "Instellingen", "usercontrol", $selectedLocation);
			writeJumpSelectOption("login.php?actionName=logout", "Uitloggen", "logout", $selectedLocation);
		} else {
			writeJumpSelectOption("login.php", "Inloggen", "login", $selectedLocation);
			writeJumpSelectOption("register.php", "Registreren", "register", $selectedLocation);
		}
		print "\t\t".'</optgroup>'."\n";
?>
				<optgroup label="Opties">
					<option value="index.php"<?=($selectedBoard == 0) ? 'selected="selected"' : ''; ?>>Overzicht</option>
<?php
	if ($TBBcurrentUser->isAdministrator()) {
		writeJumpSelectOption("adminboard.php", "Systeem Instellingen", "admincontrol", $selectedLocation);
		writeJumpSelectOption("adminmodules.php", "Plugin Instellingen", "plugincontrol", $selectedLocation);
	}
	$searchPlugins = $TBBModuleManager->getPluginInfoType("search", true);
	if (count($searchPlugins) > 0)
		writeJumpSelectOption("search.php".((isSet($GLOBALS['boardID'])) ? "?boardID=".$GLOBALS['boardID'] : ""), "Zoeken", "search", $selectedLocation);

	if ($TBBconfiguration->getHelpBoardID() !== false)
		writeJumpSelectOption("index.php?id=".$TBBconfiguration->getHelpBoardID(), "Help", $TBBconfiguration->getHelpBoardID(), $selectedBoard);
?>
				</optgroup>
				<optgroup label="Fora">
<?php
		$boardStructure = $TBBboardList->getStructureCache();
		buildJumpBoards($boardStructure, 0, $selectedBoard);
?>
				</optgroup>
			</select>
		</div>
	</form>
</div>
<?php
	}


function array_csort() {  //coded by Ichier2003
   $args = func_get_args();
   $marray = array_shift($args);
   $msortline = "return(array_multisort(";
   $i = 0;
   foreach ($args as $arg) {
       $i++;
       if (is_string($arg)) {
           foreach ($marray as $row) {
               $a = strtoupper($row[$arg]);
               $sortarr[$i][] = $a;
           }
       } else {
           $sortarr[$i] = $arg;
       }
       $msortline .= "\$sortarr[".$i."],";
   }
   $msortline .= "\$marray));";

   eval($msortline);
   return $marray;
}

?>
