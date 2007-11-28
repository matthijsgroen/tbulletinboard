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

	importClass("board.plugin.ModulePlugin");	
	importBean("board.TextParsing");

	class TravianTagsPlugin extends ModulePlugin {
		private $tags;

		function TravianTagsPlugin() {
			$this->ModulePlugin();
			$this->tags = array("report");
		}
		
		function activate() {
			$this->deactivate();
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$tagsTable = new TextParsingTable($database);
			
			$newTag = $tagsTable->addRow();
			$newTag->setValue("origin", "system");
			$newTag->setValue("startName", "report");			
			$newTag->setValue("acceptAll", false);
			$newTag->setValue("acceptedParameters", "");
			$newTag->setValue("endTags", "report");
			$newTag->setValue("endTagRequired", true);
			$newTag->setValue("htmlReplace", $this->getPluginID());
			$newTag->setValue("allowParents", "{all}");
			$newTag->setValue("allowChilds", "{text}");
			$newTag->setValue("description", "Travian Report");
			$newTag->setValue("example", "[report]
Subject: 	Hakumei central scouts Winter Home
sent: 	on 28.11.07 at 16:44:14 o'clock

Attacker 	Hakumei from the village Hakumei central
 	[Phalanx] 	[Swordsman] 	[Pathfinder] 	[Theutates Thunder] 	[Druidrider] 	[Haeduan] 	[Ram] 	[Trebuchet] 	[Chieftain] 	[Settler]
Troops	0	0	3	0	0	0	0	0	0	0
Casualties	0	0	0	0	0	0	0	0	0	0
Info	City Wall Level 1

Defender 	BurningToad from the village Winter Home
 	[Legionnaire] 	[Praetorian] 	[Imperian] 	[Equites Legati] 	[Equites Imperatoris] 	[Equites Caesaris] 	[Battering Ram] 	[Fire Catapult] 	[Senator] 	[Settler]
Troops	0	0	0	0	0	0	0	0	0	0
[/report]");
			$newTag->setValue("wordBreaks", "all");
			$newTag->store();
						
		}
		
		function deactivate() {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$tagsTable = new TextParsingTable($database);
			$filter = new DataFilter();
			$filter->addEquals("origin", "system");
			$tagFilter = new DataFilter();
			$tagFilter->setMode("or");
			for ($i = 0; $i < count($this->tags); $i++)
				$tagFilter->addEquals("startName", $this->tags[$i]);
			$filter->addDataFilter($tagFilter);
			$tagsTable->deleteRows($filter, true);
		}
		
		function getTag($starttag, $acceptParameters, $acceptAll, $endtag, $htmlcode, $endTagRequired, $inTags, $subTags) {
			if ($starttag == "report") {
				require_once($this->getModuleDir() . "ReportTag.class.php");
				require_once($this->getModuleDir() . "TravianPlace.bean.php");
				$imageFolder = $this->getModuleOnlineDir() . "images/";
				return new TravianReportTag($starttag, $acceptParameters, $acceptAll, $endtag, $htmlcode, $endTagRequired, $inTags, $subTags, $imageFolder);
			}
		}

	}

?>
