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

	class XMLRequestHelper {

		function changeSelectOptionsBySelect($functionName, $xmlUrl, $resultSelect, $resultForm, &$script) {
			$script->addXMLReader("loadSelectValues", "", "fillSelect");

			$script->startFunction("fillSelect", array("req"));
			$script->addLine("document.forms['".$resultForm."'].".$resultSelect.".options.length = 0;");
			$script->addLine("var items = req.responseText.split(\"\\n\");");
			$script->addLine("for(var i = 0; i < items.length; i++) {");
			$script->addLine("if (items[i].length > 2) {");
			$script->addLine("var item = items[i].split(\"|\");");
			$script->addLine("document.forms['".$resultForm."'].".$resultSelect.".options[i] = new Option(item[0], item[1]);");
			//$script->addLine("if(item[2] == 'selected') document.forms['".$resultForm."'].".$resultSelect.".options[i].selected = true;");
			$script->addLine("}");
			$script->addLine("}");
			$script->endBlock();

			$script->startfunction($functionName, array("triggerValue"));
			$script->addLine("var valueID = 0;");
			$script->addLine("var selectedItem = triggerValue.selectedIndex;");
			$script->addLine("valueID = triggerValue.options[selectedItem].value;");
			$script->addLine("loadSelectValues('".$xmlUrl."&valueID=' + valueID);");
			$script->endBlock();
		}

		function changeSelectOptionsByValue($functionName, $xmlUrl, $resultSelect, $resultForm, &$script) {
			$script->addXMLReader("loadSelectValues", "", "fillSelect");

			$script->startFunction("fillSelect", array("req"));
			$script->addLine("document.forms['".$resultForm."'].".$resultSelect.".options.length = 0;");
			$script->addLine("var items = req.responseText.split(\"\\n\");");
			$script->addLine("for(var i = 0; i < items.length; i++) {");
			$script->addLine("if (items[i].length > 2) {");
			$script->addLine("var item = items[i].split(\"|\");");
			$script->addLine("document.forms['".$resultForm."'].".$resultSelect.".options[i] = new Option(item[0], item[1]);");
			$script->addLine("}");
			$script->addLine("}");
			$script->endBlock();

			$script->startfunction($functionName, array("triggerValue"));
			$script->addLine("loadSelectValues('".$xmlUrl."&valueID=' + triggerValue);");
			$script->endBlock();
		}

	}

?>
