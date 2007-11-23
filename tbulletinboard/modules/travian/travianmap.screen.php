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

?>
	<style type="text/css">
		#map table { 
			border-collapse: collapse;
			font-size: 10px;
			border: 1px solid black;
			margin: 10px;
			background-color: #ffffff;
		}
		#map table th, #map table td { 
			border: 1px solid black;
			width: 20px;
		}
		#map table td.origin { 
			background-color: #DDDDDD;
		}
		
		#control table {
			border-collapse: collapse;
			border: 1px solid black;
			background-color: #fff190;
		}
		#control table td {
			border: 1px solid black;
			text-align: center;
			font-size: 11px;
		}

		#control a {
			color: black;
			text-decoration: none;
			font-weight: bold;
		}

		#control {
			margin-bottom: 10px;
		}

		h2 {
			margin: 0px;
			padding: 5px;
			text-align: left;
			color: black;
			font-size: 14px;
		}
		h2 a {
			text-decoration: none;
			color: black;
		}
		.coord {
			color: gray;
		}	
		#travelInfo .player {
			background-color: #99FF99;
		}
		#travelInfo .hostile {
			background-color: #FF9999;
		}
		#travelInfo {
			margin-left: 20px;
			font-size: 10px;
		}
	</style>
	<script type="text/javascript" src="<?=$this->getModuleOnlineDir(); ?>map.js"></script>

	<div id="sidepanel">
		<div id="control" class="controll">
			<table>
			<tbody>
			<tr><td><a href="javascript:zoomIn()">+</a></td><td><a href="javascript:moveUp()">&uarr;</a></td><td></td></tr>
			<tr><td><a href="javascript:moveLeft()">&larr;</a></td><td></td><td><a href="javascript:moveRight()">&rarr;</a></td></tr>
			<tr><td><a href="javascript:zoomOut()">-</a></td><td><a href="javascript:moveDown()">&darr;</a></td><td></td></tr>
			</tbody>
			</table>
		</div>
		<div id="playerList" class="playerlist"></div>
	</div>
	<p id="travelInfo"></p>
	<div id="map"></div>

	<script type="text/javascript">
		var lists = new Array();

		<?php
		$allianceID = $travianRow->getValue("allianceID");
		if ($allianceID != 0) { ?>
			var allyList = new PlayerList("<?=$travianRow->getValue("allianceName") ?>", "player", "", "005000", "DDFFDD");
			lists[0] = allyList;
			<?php
				$minX = false;
				$minY = false;
				$maxX = false;
				$maxY = false;
				$maxPopulation = 0;
				
				require_once($moduleDir."TravianPlace.bean.php");
				$travianTable = new TravianPlaceTable($database);
				$filter = new DataFilter();
				$filter->addEquals("allianceID", $travianRow->getValue("allianceID"));
				$travianTable->selectRows($filter, new ColumnSorting());
				while ($village = $travianTable->getRow()) {
					printf("\t\t".'%s.add(new Player("%s", %s, %s, %s, "%s", %s, %s));'."\n",
						"allyList", 
						$village->getValue("playerName"),
						$village->getValue("x"),
						$village->getValue("y"),
						$village->getValue("population"),
						$village->getValue("villageName"),
						$village->getValue("race"), 
						($village->getValue("playerID") == $travianRow->getValue("travianID")) ? "true" : "false");
					$x = $village->getValue("x");
					$y = $village->getValue("y");
					if ($village->getValue("population") > $maxPopulation) $maxPopulation = $village->getValue("population");
					if ($minX === false) $minX = $x;
					if ($minY === false) $minY = $y;
					if ($maxX === false) $maxX = $x;
					if ($maxY === false) $maxY = $y;
					
					if ($minX > $x) $minX = $x;
					if ($minY > $y) $minY = $y;
					if ($maxX < $x) $maxX = $x;
					if ($maxY < $y) $maxY = $y;
				}
			}	
			$alliance = "";
			if (isSet($_POST['alliance'])) $alliance = $_POST['alliance']; 
			if (isSet($_GET['alliance'])) $alliance = $_GET['alliance']; 

			if (trim($alliance) != "") { 
					require_once($moduleDir."TravianPlace.bean.php");
					$travianTable = new TravianPlaceTable($database);
					$filter = new DataFilter();
					$filter->setLimit(1);
					$filter->addEquals("allianceName", $alliance);
					$travianTable->selectRows($filter, new ColumnSorting());
					if ($allianceRow = $travianTable->getRow()) {
				?>
					var allianceList = new PlayerList("<?=$allianceRow->getValue("allianceName") ?>", "hostile", "", "500000", "FFDDDD");
					lists[1] = allianceList;
					<?php
						if (isSet($_POST['zoom']) && ($_POST["zoom"] == "yes")) {
							$minX = false;
							$minY = false;
							$maxX = false;
							$maxY = false;
						}
					
						$travianTable = new TravianPlaceTable($database);
						$filter = new DataFilter();
						$filter->addEquals("allianceID", $allianceRow->getValue("allianceID"));
						$travianTable->selectRows($filter, new ColumnSorting());
				
						while ($village = $travianTable->getRow()) {
							printf("\t\t".'%s.add(new Player("%s", %s, %s, %s, "%s", %s, false));'."\n",
								"allianceList", 
								$village->getValue("playerName"),
								$village->getValue("x"),
								$village->getValue("y"),
								$village->getValue("population"),
								$village->getValue("villageName"),
								$village->getValue("race"));
							$x = $village->getValue("x");
							$y = $village->getValue("y");
							if ($village->getValue("population") > $maxPopulation) $maxPopulation = $village->getValue("population");
							if ($minX === false) $minX = $x;
							if ($minY === false) $minY = $y;
							if ($maxX === false) $maxX = $x;
							if ($maxY === false) $maxY = $y;
					
							if ($minX > $x) $minX = $x;
							if ($minY > $y) $minY = $y;
							if ($maxX < $x) $maxX = $x;
							if ($maxY < $y) $maxY = $y;
						}
					}
				}
		
			$spanX = ($maxX - $minX);
			$spanY = ($maxY - $minY);
			$spanMax = max($spanX, $spanY);
			$box = ceil($spanMax / 25.0);
		
		?>

		var topx = <?=$minX ?>;
		var topy = <?=$maxY ?>;
		var zoom = <?=$box ?>;
		var maxPop = <?=$maxPopulation ?>;	

		function zoomOut() {
			var mapsizex = 25;
			var mapsizey = 25;
			switch(zoom) {
				case 1: zoom = 5; topx -= 60; topy += 60; break;
				case 5: zoom = 10; topx -= 65; topy += 60; break;
				case 10: zoom = 20; topx -= 130; topy += 120; break;
				case 20: zoom = 50; topx = -400; topy = 400; mapsizex = 16; mapsizey = 16; break;
				default: return;
			}
		
			drawMap(topx, topy, mapsizex, mapsizey, zoom);
		}

		function zoomIn() {
			var mapsizex = 25;
			var mapsizey = 25;
			switch(zoom) {
				case 5: zoom = 1; topx += 60; topy -= 60; break;
				case 10: zoom = 5; topx += 65; topy -= 60; break;
				case 20: zoom = 10; topx += 130; topy -= 120; break;
				case 50: zoom = 20; topx = -240; topy = 240; break;
			}

			drawMap(topx, topy, mapsizex, mapsizey, zoom);
		}

		function moveLeft() {
			if (zoom < 50) topx -= (zoom * 4);
			drawMap(topx, topy, 25, 25, zoom);
		}
		function moveRight() {
			if (zoom < 50) topx += (zoom * 4);
			drawMap(topx, topy, 25, 25, zoom);
		}
		function moveUp() {
			if (zoom < 50) topy += (zoom * 4);
			drawMap(topx, topy, 25, 25, zoom);
		}
		function moveDown() {
			if (zoom < 50) topy -= (zoom * 4);
			drawMap(topx, topy, 25, 25, zoom);
		}

		drawMap(topx, topy, 25, 25, zoom);
	</script>
<?php

	importClass("interface.Form");
	includeFormComponents("TemplateField", "Submit", "TextField", "Checkbox");

	global $TBBsession;
	global $formTitleTemplate;
	
	$form = new Form("searchForumUser", "");
	$form->addHiddenField("actionID", $TBBsession->getActionID());
	$form->addComponent(new FormTemplateField($formTitleTemplate, "Alliantie tonen"));
	$form->addComponent(new FormTextField("alliance", "Alliantie", "", 255, true));
	$form->addComponent(new FormCheckbox("Zoom in", "Zoom in op alliantie", "", "zoom", "yes", false));
	$form->addComponent(new FormSubmit("Toon", "", "", "submitButton")); //$caption, $title, $description, $name, $onclick = ""
	print $form->writeForm();


?>
