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

	class ReportPart {
		
		private $playerName;
		private $villageName;
		private $resourceName;
		private $troops = array();
		private $resources = array();
		private $info = array();
		private $role;
		
		function ReportPart($playerName, $villageName, $role) {
			$this->playerName = $playerName;
			$this->villageName = $villageName;
			$this->role = $role;
			$this->race = 0;
		}
		
		function addTroops($name) {
			if (is_array($name)) {
				$this->troops[] = $name;
			} else
			$this->troops[] = func_get_args();
		}

		function addResources($name) {
			if (is_array($name)) {
				$this->resources[] = $name;
			} else
			$this->resources[] = func_get_args();
		}

		function addInfo($name) {
			$this->info[] = $name;
		}
		
		function setRace($imgNr) {
			$this->race = $imgNr;
		}
		
		function getRace() {
			if ($this->race == 0) {
				// Try an DB lookup
				global $TBBconfiguration;
				$database = $TBBconfiguration->getDatabase();
				$playerTable = new TravianPlaceTable($database);
				$filter = new DataFilter();
				$filter->addEquals("playerName", $this->playerName);
				$filter->setLimit(1);
				$playerTable->selectRows($filter, new ColumnSorting());
				if ($playerRow = $playerTable->getRow()) {
					$items = $playerRow->getValue("race");
					$this->race = (($items -1) * 10) + 1;
				}
			
			}		
			return $this->race;
		}
		
		function toHTML($imageFolder) {
			$hasHero = false;
			if ((isSet($this->troops[0])) && (count($this->troops[0]) > 11)) $hasHero = true;


			$cell = "<td style=\"border-collapse: collapse; border: 1px solid #c0c0c0; background-color: white; text-align: center;\">%s</td>";
			$cellGrayHead = "<td style=\"border-collapse: collapse; border: 1px solid #c0c0c0; background-color: #f5f5f5; text-align: center; padding: 3px; width: 100px;\">%s</td>";

			$villageTitle = "<td colspan=\"".($hasHero ? 11 : 10)."\" style=\"border-collapse: collapse; border: 1px solid #c0c0c0; background-color: #f5f5f5; text-align: center; padding: 3px; width: 380px;\">".
				"<span style=\"color: #71d000; font-weight: bold;\">%s</span> from the village <span style=\"color: #71d000; font-weight: bold;\">%s</span></td>";

			$result = '<table style="border-collapse: collapse; border: 1px solid #c0c0c0; margin: 3px;">';
			if ($this->role == "Attacker") {
				$result .= "<tr>".sprintf($cellGrayHead, '<span style="color: #ff8000; font-weight: bold;">Attacker</span>').
					sprintf($villageTitle, $this->playerName, $this->villageName)."</tr>";
			} else {
				$result .= "<tr>".sprintf($cellGrayHead, '<span style="color: #71d000; font-weight: bold;">Defender</span>').
					sprintf($villageTitle, $this->playerName, $this->villageName)."</tr>";
			}
			
			$result .= "<tr>".sprintf($cell, "");
			if ($this->getRace() == 0) 
				for ($i = $this->race; $i < $this->race + 10; $i++) $result .= sprintf($cell, "?");
			else for ($i = $this->race; $i < $this->race + 10; $i++) $result .= sprintf($cell, "<img src=\"".$imageFolder.$i.".gif\">");
			if ($hasHero) $result .= sprintf($cell, "<img src=\"".$imageFolder."hero.gif\">");
			
			$result .= "</tr>";
			for ($i = 0; $i < count($this->troops); $i++) {
				$result .= "<tr>".sprintf($cell, $this->troops[$i][0]);
				for ($j = 1; $j <= ($hasHero ? 11 : 10); $j++) {
					if ($this->troops[$i][$j] == "0") {
						$result .= sprintf($cell, '<span style="color: #c0c0c0">0</span>');
					} else
						$result .= sprintf($cell, $this->troops[$i][$j]);
				}
				$result .= "</tr>";
			}
			for ($i = 0; $i < count($this->info); $i++) {
				$result .= "<tr>".sprintf($cell, "Info");
				$infoLine = $this->info[$i];
				for ($x = 0; $x < 10; $x++) $infoLine = str_replace($x, "<b>".$x."</b>", $infoLine);
				$infoLine = str_replace("</b><b>", "", $infoLine); 
				if ($this->race != 0) {
					$cata = false;
					if (strpos($infoLine, "damaged") !== false) $cata = true;
					if (strpos($infoLine, "destroyed") !== false) $cata = true;
					if ($cata) $infoLine = "<img src=\"".$imageFolder.($this->race + 7).".gif\"> " . $infoLine;
				}
				
				$result .= sprintf("<td colspan=\"".($hasHero ? 11 : 10)."\" style=\"border-collapse: collapse; border: 1px solid #c0c0c0; background-color: white; text-align: left;\">%s</td>", $infoLine);
				$result .= "</tr>";
			}
			for ($i = 0; $i < count($this->resources); $i++) {
				$result .= "<tr>".sprintf($cellGrayHead, $this->resources[$i][0]);
				$resourceLine = "";
				for ($j = 1; $j <= 4; $j++) {
					$img = "wood";
					switch($j) {
						case 2: $img = "clay"; break;
						case 3: $img = "iron"; break;
						case 4: $img = "crop"; break;
					}
					$resourceLine .= "<img src=\"" . $imageFolder . $img . ".gif\">" . $this->resources[$i][$j] . " ";
				}
				$result .= sprintf("<td colspan=\"".($hasHero ? 11 : 10)."\" style=\"border-collapse: collapse; border: 1px solid #c0c0c0; background-color: #f5f5f5; text-align: left;\">%s</td>", $resourceLine);
				$result .= "</tr>";
			}
			
			

			$result .= "</table>";
			
			return $result;
		}	
	}

	class TravianReport {
	
		private $reportType;
		private $villageOne;
		private $villageTwo;
		private $dateTime;
		private $reportPart = array();
	
		public function TravianReport() {
			$this->dateTime = new LibDateTime();
		}
		
		public function setType($type, $villageOne, $villageTwo) {
			$this->reportType = $type;
			$this->villageOne = $villageOne;
			$this->villageTwo = $villageTwo;
		}
		
		public function setDateTime($dateTime) {
			$this->dateTime = $dateTime;
		}
		
		public function addReportPart($reportPart) {
			$this->reportPart[] = $reportPart;
		}
		
		public function getHTML($imageFolder) {
			$result = '<table style="border-collapse: collapse; border: 1px solid #c0c0c0;">';
			$header1 = "<th style=\"background-image: url('".$imageFolder."reportHeader.gif'); border-collapse: collapse; border: 1px solid #c0c0c0; padding: 5px; width: 110px;\">%s</th>";
			$header2 = "<th style=\"background-image: url('".$imageFolder."reportHeader.gif'); border-collapse: collapse; border: 1px solid #c0c0c0; padding: 5px; width: 370px;\">%s</th>";
			$cell = "<td style=\"border-collapse: collapse; border: 1px solid #c0c0c0; background-color: white; padding: 5px;\">%s</td>";
			$cellColspan = "<td colspan=\"%2\$s\" style=\"border-collapse: collapse; border: 1px solid #c0c0c0; background-color: white;\">%1\$s</td>";
			$type = " ".$this->reportType." ";
			
			$result .= "<tr>".sprintf($header1, "Subject: ").sprintf($header2, $this->villageOne . $type . $this->villageTwo)."</tr>";
			$result .= "<tr>".sprintf($cell, "Sent: ").sprintf($cell, "on ".$this->dateTime->toString("d.m.y")." at ".$this->dateTime->toString("H:i:s")." o'clock")."</tr>";
			$result .= "<tr>".sprintf($cellColspan, "", "2")."</tr>";
			
			$reportParts = "";
			for ($i = 0; $i < count($this->reportPart); $i++) {
				$reportParts .= $this->reportPart[$i]->toHTML($imageFolder);
			}
		
			$result .= "<tr>".sprintf($cellColspan, $reportParts, "2")."</tr>";
			$result .= "</table>";
			
			return $result;		
		}
	
	}


	class TravianReportTag extends TBBTag {

		var $privateVars;
		private $imageFolder; 

		function TravianReportTag($starttag, $acceptParameters, $acceptAll, $endtag, $htmlcode, $endTagRequired, $inTags, $subTags, $imageFolder) {
			$this->TextTag($starttag, $acceptParameters, $acceptAll, $endtag, $htmlcode, $endTagRequired, $inTags, $subTags);
			$this->imageFolder = $imageFolder;
		}

		function parseTag($text, $parameter) {
			$images = $this->imageFolder; //"modules/travian/images/";
			
			$report = new TravianReport();
			$nameMatch = "0-9a-zA-Z_\-[:space:]\'\\\"`?";
			$cleanText = str_replace("<p>", "", $text);
			$cleanText = str_replace("</p>", "", $cleanText);
			$originallines = explode("\n", str_replace("<br />", "", $cleanText));
			$startLine = 0;
			$lines = array();
			// parse subject
			for ($i = 0; $i < count($originallines); $i++) {
				if (strlen(trim($originallines[$i])) > 0) 
					$lines[] = $originallines[$i];
			}
			
			$firstPos = strpos($lines[0], "Subject:");
			if ($firstPos !== false) {
				$types = array("attacks", "scouts");
				$line = substr($lines[0], $firstPos + 8);
				for ($j = 0; $j < count($types); $j++) {
					$typePos = strpos($line, $types[$j]);
					if ($typePos > 0) {
						$village1 = trim(substr($line, 0, $typePos));
						$village2 = trim(substr($line, $typePos + strlen($types[$j])));
						$report->setType($types[$j], $village1, $village2);
						break;
					}
				}
			}
			// parse timestamp
			if (preg_match("/on ([0-9]+)\.([0-9]+)\.([0-9]+) at ([0-9]+):([0-9]+):([0-9]+) o/", $lines[1], $matches)) {
				//var_dump($matches);
				// $hour = -1, $minute = -1, $second = -1, $month = -1, $day = -1, $year = -1
				$dateTime = new LibDateTime($matches[4], $matches[5], $matches[6], $matches[2], $matches[1], "20".$matches[3]);
				$report->setDateTime($dateTime);
			}

			// parse the parts
			for ($i = 2; $i < count($lines); ) {
				
				if (preg_match("/^(Attacker|Defender) ([".$nameMatch."]+) from the village ([".$nameMatch."]+)/", $lines[$i], $matches)) {
					//var_dump($matches);
					$reportPart = new ReportPart($matches[2], $matches[3], $matches[1]);
					$i++;
					if (strpos($lines[$i], "Phalanx") !== false) { $reportPart->setRace(21); $i++; }
					if (strpos($lines[$i], "Legionnaire") !== false) { $reportPart->setRace(1); $i++; }
					if (strpos($lines[$i], "Clubswinger") !== false) { $reportPart->setRace(11); $i++; }
					if (strpos($lines[$i], "Rat") !== false) { $reportPart->setRace(31); $i++; }
					if ((trim($matches[2]) == "Nature") && (trim($matches[3]) == "abandoned valley")) { $reportPart->setRace(31); }
					
					//print $lines[$i];
					$pattern = "/^(Troops|Casualties|Prisoners)";
					for ($x = 0; $x < 10; $x++) { $pattern .= "[[:space:]]+([0-9]+)"; }
					while (@preg_match($pattern."[[:space:]]*([0-9]+)?/", $lines[$i], $matches)) {
						$params = array_slice($matches, 1);
						//var_dump($matches);
						$reportPart->addTroops($params);
						$i++;
					}
					$info = "";
					$once = false;
					while (@preg_match("/Info/", $lines[$i], $matches)) {
						$infoMode = true;
						while ($infoMode) {
							$infoMode = false;
							while (strpos($lines[$i], " conquered ") !== false) {
								$info .= str_replace("Info", "", $lines[$i]) . "\n"; $i++; $infoMode = true;
							}
							while (strpos($lines[$i], " level ") !== false) {
								$info .= str_replace("Info", "", $lines[$i]) . "\n"; $i++; $infoMode = true;
							}
							while (strpos($lines[$i], " Level ") !== false) {
								$info .= str_replace("Info", "", $lines[$i]) . "\n"; $i++; $infoMode = true;
							}
							while (@preg_match("/\bdestroyed\b/", $lines[$i], $matches)) {
								$info .= str_replace("Info", "", $lines[$i]) . "\n"; $i++; $infoMode = true;
							}
							if (($info == "") && (!$once)) { $infoMode = true; $i++; $once = true; }
						}
						if ($info == "") $i++;
					}
					if ($info != "") $reportPart->addInfo($info);
					
					
					$pattern = "/^(Resources|Bounty)";
					for ($x = 0; $x < 4; $x++) { $pattern .= "[[:space:]]+([0-9]+)"; }
					while (@preg_match($pattern."/", $lines[$i], $matches)) {
						$params = array_slice($matches, 1);
						$reportPart->addResources($params);
						$i++;
					}
					
					$report->addReportPart($reportPart);
				} else $i++;			
			}
			
			return /*$text.*/$report->getHTML($images);
		}
	}

?>
