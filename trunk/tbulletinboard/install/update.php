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

	header("Status: 200");
	header("Cache-Control: must-revalidate");
	set_time_limit(180 * 60); // 3 hours

	$TBBconfigDir = "../config/";
	$TBBclassDir = "../classes/";
	require_once($TBBconfigDir . "configuration.php");
	require_once($TBBclassDir . "library.php");
	date_default_timezone_set('CET');	
	
?>
<html>
<head>
	<title>Update TBB2</title>
	<style type="text/css">
		body {
			background-color: white;
			color: black;
			font-family: Courier new;
			font-size: 11px;
		}
	</style>
</head>
<body>
<?php
	//importClass("util.LibDateTime");
	$database = $TBBconfiguration->getDatabase();
	importClass("updater.ModuleUpdater");
	$modUpdater = new ModuleUpdater("core", "patches/", $database);
	
	printf("%s patches in totaal, waarvan nieuw: %s", $modUpdater->getTotalPatchCount(), $modUpdater->getNewPatchCount());
	$modUpdater->executePatches();
	
/*
	$startTime = new LibDateTime();
	print "Current time: " . $startTime->toString("d-m-Y H:i") . "<br /><br />";
	print "<u>Patch Indexing</u><br />";


	print "<u>Database Connection</u><br />";
	print "Connecting to account database...";

	print "<br />\n<u>Actual Patching</u><br />";


	$endTime = new LibDateTime();
	print "Time needed: ";
	$hours = $endTime->getDifference($startTime, LibDateTime::hour());
	if ($hours > 0) print $hours." uur, ";
	$minutes = $startTime->getDifference($endTime, LibDateTime::minute()) % 60;
	$seconds = $startTime->getDifference($endTime, LibDateTime::second()) % 60;
	print $minutes . "m " . $seconds . "s <br />";
*/
?>
</body>
</html>
