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

	/**
	 * Import the FormComponent superclass
	 */
	importClass("interface.Form");

	/**
	 * Component to put a link (html allowed) in forms
	 */
	class Link extends FormComponent {

		var $privateVars;
		//var $tag;
		//var $groupTag;
		// var $name; is title
		//var $hint; is description

		function Link($title, $description, $url, $target, $icon, $contains, $highlight) {
			$this->FormComponent($title, $description, $title);
			$this->privateVars = array();
			$this->privateVars["url"] = $url;
			$this->privateVars["target"] = $target;
			$this->privateVars["icon"] = $icon;
			$this->privateVars["contains"] = $contains;
			$this->privateVars["highlight"] = $highlight;
			$this->rowClass = "link";
		}

		function getInput() {
			$linkString = "";
			$imageString = "";
			if(strLen($this->privateVars['icon']) > 0) {
				$imageString = '<img src="'.$this->privateVars['icon'].'" alt="" title="'.$this->description.'" border="0" />';
			}
			if(strLen($this->privateVars['url']) > 0 || $this->disabled) {
				$linkString = '<a href="'.$this->privateVars['url'].'" title="'.$this->description.'" target="'.$this->privateVars['target'].'">';
				$linkString .= $imageString;
				$title = $this->title;
				if(strLen($this->privateVars['contains']) > 0) $title .= '('.$this->privateVars['contains'].')';
				if($this->privateVars['highlight'] == true) $title = '<b>'.$title.'</b>';
				$linkString .= $title."</a>";
			} else {
				$linkString = '<span class="disabled">';
				$linkString .= $imageString;
				$linkString .= $this->title;
				$linkString .= '</span>';
			}
			return $linkString;
		}
	}
?>
