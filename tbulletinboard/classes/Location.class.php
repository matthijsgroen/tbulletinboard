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

	global $ivLibDir;
	require_once($ivLibDir.'TextParser.class.php');

	class Location {

		var $privateVars;
		var $disableLast;

		function Location() {
			$this->privateVars['folders'] = array();
			$this->disableLast = true;
		}

		function addLocation($name, $url) {
			$this->privateVars['folders'][] = array('name' => $name, 'url' => $url);
		}

		function locationCount() {
			return count($this->privateVars['folders']);
		}

		function showLocation() {
			if (count($this->privateVars['folders']) == 0)  return;
?>
	<div class="location">
<?php

			$textParser = new TextParser();
			for ($i = 0; $i < count($this->privateVars['folders']); $i++) {
				$location = $this->privateVars['folders'][$i];
				if ((strlen($location['url']) == 0) || ($this->disableLast && ($i == count($this->privateVars['folders'])-1))) {
					print '<span class="folder">'.$textParser->breakLongWords($location['name'], 40).'</span>';
				} else {
					print '<a href="'.htmlConvert($location['url']).'">'.$textParser->breakLongWords($location['name'], 30).'</a>';
				}
				if ($i < count($this->privateVars['folders']) - 1) print ' &gt; ';
			}
?>
	</div>
<?php
		}

	}
?>
