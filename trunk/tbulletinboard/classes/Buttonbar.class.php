<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
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