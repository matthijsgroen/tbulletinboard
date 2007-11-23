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

	function Player(name, xpos, ypos, population, villageName, raceID, special) {
		this.name = name;
		this.xpos = xpos;
		this.ypos = ypos;
		this.population = population;
		this.villageName = villageName;
		this.raceID = raceID;
		this.special = special;
	}

	function PlayerList(name, className, prefix, lowColor, highColor) {
		this.name = name;
		this.className = className;
		this.prefix = prefix;
		this.members = new Array();
		this.lowColor = lowColor;
		this.highColor = highColor;
		this.add = function(player) { this.members[this.members.length] = player; }
		this.count = function() { return this.members.length; }
		this.get = function(index) { return this.members[index]; }
	}

	function drawMap(startx, starty, width, height, zoom) {

		var endx = startx + (zoom * width);
		var endy = starty - (zoom * height);
		var tableBox = document.getElementById("map");
		tableBox.innerHTML = "";
		
		var table = document.createElement("TABLE");
		var tableBody = document.createElement("TBODY");
		// create headers
		var headerRow = document.createElement("TR");
		var firstCell = document.createElement("TH");
		//firstCell.appendChild(document.createTextNode(""));
		headerRow.appendChild(firstCell);

		for (var j = startx; j < endx; j += zoom) {
			var headerCell = document.createElement("TH");
			headerCell.appendChild(document.createTextNode(j));
			headerRow.appendChild(headerCell);
		}
		tableBody.appendChild(headerRow);

		for (var i = starty; i > endy; i -= zoom) {
			var newRow = document.createElement("TR");
			var headerCell = document.createElement("TH");
			headerCell.appendChild(document.createTextNode(i));
			newRow.appendChild(headerCell);
	
			for (var j = startx; j < endx; j += zoom) {
				var rowCell = document.createElement("TD");
				for (var l = 0; l < lists.length; l++) {
				
					for (var k = 0; k < lists[l].count(); k++) {
						var player = lists[l].get(k);

						if ((player.xpos >= j) && (player.xpos < j + zoom) &&
							(player.ypos >= i) && (player.ypos <  i + zoom)) {
							var linkNode = createPlayerLink(l, k, lists[l].prefix + (k+1));
							linkNode.className = lists[l].className;
							if (player.special) {
								rowCell.style.borderColor = "#30b356";
								rowCell.style.borderWidth = "2px";
								rowCell.style.borderStyle = "dashed";
								rowCell.style.backgroundColor = "#fff190";
							}
							rowCell.appendChild(linkNode);
							rowCell.appendChild(document.createTextNode(" "));
						}
					}
				}
				if ((0 >= j) && (0 < j + zoom) ||
					(0 >= i) && (0 <  i + zoom)) {
					//if ((j == 0) || (i == 0)) 
					rowCell.className = "origin";
				}
				newRow.appendChild(rowCell);
			}
			tableBody.appendChild(newRow);
		}
		table.appendChild(tableBody);
		tableBox.appendChild(table);
	}

	function d2h(d) { return d.toString(16); }
	function h2d(h) { return parseInt(h,16); }
	function mixComp(from, to, percentage) {
		var value = "" + d2h(h2d(from) + Math.ceil((h2d(to) - h2d(from)) * percentage));
		if (value.length == 1) value = "0" + value;
		return value;
	}	
	function mixColors(start, end, percentage) {
		return "" +
			mixComp(start.substr(0, 2), end.substr(0, 2), percentage)
			+ mixComp(start.substr(2, 2), end.substr(2, 2), percentage)
			+ mixComp(start.substr(4, 2), end.substr(4, 2), percentage);
	}

	function createPlayerLink(listIndex, playerIndex, textContent) {
		var linkNode = document.createElement("A");
		var player = lists[listIndex].get(playerIndex);
		linkNode.setAttribute("title", lists[listIndex].name + ": " + player.name + " (" + player.xpos + ", " + player.ypos + ") pop: " + player.population);
		linkNode.setAttribute("href", "javascript:setTravelItem("+listIndex+", "+playerIndex+")");
		linkNode.appendChild(document.createTextNode(textContent));
		var percentage = 1 - (player.population / maxPop);
		var lowColor = lists[listIndex].lowColor;
		var highColor = lists[listIndex].highColor;
		var background = mixColors(lowColor, highColor, percentage);		

		if (percentage < 0.2) linkNode.style.color = "#FFFFFF";
		linkNode.style.backgroundColor = "#" + background;
		
		return linkNode;
	}

	function drawPlayerList(listIndex, listID, expanded) {
		var tableBox = document.getElementById(listID);
		tableBox.innerHTML = "";
		var list = lists[listIndex];
		var title = document.createElement("H2");
		var expandLink = document.createElement("A");
		
		expandLink.appendChild(document.createTextNode((expanded ? "+" : "-") + " " + list.name));
		var expStr = "" + (!expanded);
		expandLink.href = "javascript:drawPlayerList("+listIndex+", \""+listID+"\", "+expStr+")";
		title.appendChild(expandLink);		
		tableBox.appendChild(title);

		if (expanded) {
			var listBox = document.createElement("OL");
				
			for (var k = 0; k < list.count(); k++) {
				var player = list.get(k);
				var item = document.createElement("LI");			
				var linkNode = createPlayerLink(listIndex, k, player.name);
				linkNode.className = list.className;
				item.appendChild(linkNode);
				var coords = document.createElement("SPAN");
				coords.className = "coord";
				coords.appendChild(document.createTextNode(" (" + player.xpos + ", " + player.ypos + ")"));
				item.appendChild(coords);
				listBox.appendChild(item);
			}
			tableBox.appendChild(listBox);
		}
	}

	function Void(number) {
	}

	var fromList = null;
	var fromMember = null;

	var toList = null;
	var toMember = null;

	function setTravelItem(listIndex, index) {
		if (fromList == null) { fromList = listIndex; fromMember = index; }
		else if (toList == null) { toList = listIndex; toMember = index; }
		updateTravelText();				
	}
	function clearTravelItems() {
		fromList = null;
		toList = null;
		updateTravelText();
	}

	function updateTravelText() {
		var infoBox = document.getElementById("travelInfo");
		infoBox.innerHTML = "";
		if ((fromList != null) && (toList == null)) {
			infoBox.innerHTML = "Toon reisinformatie van <span class=\""+lists[fromList].className+"\">" + lists[fromList].get(fromMember).name + 
				"</span> naar ... <br/><a href=\"javascript:clearTravelItems()\">wis</a>";
		} else if ((fromList != null) && (toList != null)) {
			var fromPlayer = lists[fromList].get(fromMember);			
			var toPlayer = lists[toList].get(toMember);

			infoBox.innerHTML = "Toon reisinformatie van <span class=\""+lists[fromList].className+"\">" + fromPlayer.name + 
				"</span> naar <span class=\""+lists[toList].className+"\">" + 
				lists[toList].get(toMember).name + "</span>: <a href=\"http://www.javaschubla.de/2006/travian/wegerechner/wegerechner-t3i.html?"+
				"sx="+fromPlayer.xpos+"&sy="+fromPlayer.ypos+"&ex="+toPlayer.xpos+"&ey="+toPlayer.ypos+
				"&turnier=0&speed=1&lang=nl\" target=\"_blank\">bekijk</a> <br/>" +
				"<a href=\"javascript:clearTravelItems()\">wis</a>";
			//
			
		}
	}

