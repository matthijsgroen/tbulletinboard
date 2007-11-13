<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
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