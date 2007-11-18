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
	 * Messages is a class to create a Log like message list
	 */
	class Messages {

		var $privateVars;

		function Messages() {
			$this->privateVars['messages'] = array();
		}

		function addMessage($text) {
			$this->privateVars['messages'][] = array('text' => $text);
		}

		function showMessages($popup=false) {
			if($popup) $this->popupMessages();
			else $this->printMessages();
		}

		function printMessages() {
			if (count($this->privateVars['messages']) > 0) {
				?>
				<div class="messages">
				<?
				for ($i = 0; $i < count($this->privateVars['messages']); $i++) {
					$message = $this->privateVars['messages'][$i];
					?>
					<p><?=$message['text']; ?></p>
					<?
				}
				?>
				</div>
				<?
			}
		}

		function popupMessages() {
			if (count($this->privateVars['messages']) > 0) {
				$messages = "";
				for ($i = 0; $i < count($this->privateVars['messages']); $i++) {
					$messages .= $this->privateVars['messages'][$i]['text'].'\n';
				}
				?>
				<script type="text/javascript">
					alert("<?=$messages?>");
				</script>
				<?
			}
		}

	}


?>
