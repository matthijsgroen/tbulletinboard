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

	class Buttonbar {

		var $privateVars;

		function Buttonbar() {
			$this->privateVars = array();
			$this->privateVars['buttons'] = array();
		}

		function addButton($id, $caption, $title, $url) {
			$this->privateVars['buttons'][] = array('id' => $id, 'caption' => $caption, 'title' => $title, 'url' => $url);
		}

		function showBar() {
?>
	<div class="center">
		<div class="buttonBar">
<?php
			for ($i = 0; $i < count($this->privateVars['buttons']); $i++) {
				$button = $this->privateVars['buttons'][$i];
?>
			<a href="<?=$button['url'] ?>" title="<?=$button['title'] ?>"><?=$button['caption'] ?></a>
<?php
			}
?>
		</div>
	</div>
<?php
		}

	}


?>
