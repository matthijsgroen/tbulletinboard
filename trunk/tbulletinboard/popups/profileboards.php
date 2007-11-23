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

	require_once("folder.config.php");
	// Load the configuration
	require_once($TBBconfigDir.'configuration.php');

	importClass("board.TagListManager");	
	importClass("interface.Text");	
	importClass("util.TextParser");	
	importClass("interface.Menu");	
	importClass("interface.Form");	
	importClass("interface.FormFields");	
	importClass("interface.Table");	
	importClass("board.Board");	
	importClass("board.ActionHandler");	

	$pageTitle = 'Profiel boards';
	include($TBBincludeDir.'popuptop.php');

	if (isSet($_GET["id"])) $profileID = $_GET["id"];
	if (isSet($_POST["id"])) $profileID = $_POST["id"];

	if (!isSet($profileID)) {
		?>
			<h2>Geen id meegegeven</h2>
		<?php
		$text = new Text();
		$text->addHTMLText("Geen profiel id opgegeven!");
		$text->showText();
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}
	$boardProfile = $GLOBALS['TBBboardProfileList']->getBoardProfile($profileID);
	if (!is_Object($boardProfile)) {
		?>
			<h2>Profiel niet gevonden!</h2>
		<?php
		$text = new Text();
		$text->addHTMLText("Geen geldige profiel id opgegeven!");
		$text->showText();
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}

	if (!$TBBcurrentUser->isAdministrator()) {
		?>
			<h2>Geen toegang!</h2>
		<?php
		$text = new Text();
		$text->addHTMLText("Dit scherm is alleen voor administrators!");
		$text->showText();
		include($TBBincludeDir.'popupbottom.php');
		exit;
	}

?>
	<h2>Boards die gebruik maken van profiel: "<?=$boardProfile->getName() ?>"</h2>
	<script type="text/javascript">
	<!--
		var selectedBoard = -1;

		function selectBoard(id) {
			selectedBoard = id;
		}

		function editBoard() {
			if (selectedBoard == -1) {
				alert("Geen board geselecteerd");
				return;
			}
			window.opener.location = '<?=$docRoot; ?>editboard.php?id=' + selectedBoard;
			window.close();
		}


	// -->
	</script>
<?php
	$usingBoards = $boardProfile->getUsingBoards();

	$feedback->showMessages();

	$navMenu = new Menu();
	$navMenu->itemIndex = "boards";
	$navMenu->addItem('profile', '', 'Algemeen', 'editboardprofile.php?id='.$profileID, '', '', 0, false, '');
	$navMenu->addItem('tags', '', 'Tags', 'profiletags.php?id='.$profileID, '', '', 0, false, '');
	$navMenu->addItem('boards', '', 'Boards ('.count($usingBoards).')', 'profileboards.php?id='.$profileID, '', '', 0, false, '');
	$navMenu->addItem('topics', '', 'Onderwerpen', 'profiletopics.php?id='.$profileID, '', '', 0, false, '');
	$navMenu->showMenu("configMenu");

	if (count($usingBoards) == 0) {
		$menu = new Menu();
		$menu->addItem("edit", "", "Board bewerken", "", "", "", 0, false, '');
		//$menu->addItem("delete", "", "Profiel verwijderen", "javascript:delProfile()", "", "", 0, false, '');
		$menu->showMenu('toolbar');

		$text = new Text();
		$text->addHTMLText("Dit profiel wordt niet gebruikt.");
		$text->showText();
	} else {
		$menu = new Menu();
		$menu->addItem("edit", "", "Board bewerken", "javascript:editBoard()", "", "", 0, false, '');
		//$menu->addItem("delete", "", "Profiel verwijderen", "javascript:delProfile2()", "", "", 0, false, '');
		$menu->showMenu('toolbar');

		$table = new Table();
		$table->setHeader("ID", "Naam");
		for ($i = 0; $i < count($usingBoards); $i++) {
			$board = $usingBoards[$i];
			$table->addRow(
				$board->getID(),
				$board->getName()
			);
			$table->setClickColumn(0, "selectBoard", true);
		}
		$table->showTable();
	}



	include($TBBincludeDir.'popupbottom.php');
?>
