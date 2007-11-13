<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
	 */
	class Text {

		var $privateVars;

		function Text() {
			$this->privateVars = array();
			$this->privateVars['text'] = array();
		}

		function addHTMLText($text) {
			$this->privateVars['text'][] = array('type' => 'html', 'content' => $text);
		}

		function addHTMLheader($text) {
			$this->privateVars['text'][] = array('type' => 'htmlheader', 'content' => $text);
		}

		function showText() {
?>
		<div class="center">
<?php
			for ($i = 0; $i < count($this->privateVars['text']); $i++) {
				$textPart = $this->privateVars['text'][$i];
				if ($textPart['type'] == 'html') {
					print "<div class=\"text\"><p>".$textPart['content']."</p></div>";
				}
				if ($textPart['type'] == 'htmlheader') {
					print "<h3 class=\"textheader\">".$textPart['content']."</h3>";
				}
			}
?>
		</div>
<?php
		}
	}

?>