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
	require_once($TBBclassDir.'tbblib.php');

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Instellingen';
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');

	importClass("interface.Location");
	importClass("board.BoardProfiles");
	importClass("interface.Table");
	importClass("interface.Text");

	if (isSet($_GET['actionName']) && isSet($_GET['actionID'])) {
		if (($_GET['actionName'] == 'delProfile') && ($_GET['actionID'] == $TBBsession->getActionID())) {
			$GLOBALS['TBBboardProfileList']->delBoardProfile($_GET['id']);
			$feedback->addMessage("Profiel verwijderd!");
			$TBBsession->actionHandled();
		}
	}

	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation('Systeem instellingen', 'adminboard.php');
	$here->addLocation('Board profielen', 'boardprofiles.php');
	$here->showLocation();

	if (!$TBBsession->isLoggedIn()) {
		$text = new Text();
		$text->addHTMLText("Sorry, gasten hebben geen instellingen venster!");
		$text->showText();
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}

	if (!$TBBcurrentUser->isAdministrator()) {
		$text = new Text();
		$text->addHTMLText("Sorry, dit venster is alleen voor administrators!");
		$text->showText();
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}

	include($TBBincludeDir.'configmenu.php');
	$adminMenu->itemIndex = 'system';
	$adminMenu->showMenu('configMenu');

	include($TBBincludeDir.'admin_menu.php');
	$menu->itemIndex = 'bprofile';
	$menu->showMenu('adminMenu');

?>
	<script type="text/javascript"><!--
		var selectedRow = -1;
		var selectedUsed = -1;

		function selectProfile(id, nrUsed) {
			selectedRow = id;
			selectedUsed = nrUsed;
		}

		function editProfile() {
			if (selectedRow == -1) {
				alert("Er is geen profiel geselecteerd");
				return;
			}
			popupWindow('popups/editboardprofile.php?id='+selectedRow, 500, 400, 'editprofile', 1);
		}

		function deleteProfile() {
			if (selectedRow == -1) {
				alert("Er is geen profiel geselecteerd");
				return;
			}
			if (selectedUsed != 0) {
				alert("Dit profiel kan niet worden verwijderd. Het is nog in gebruik");
				return;
			}
			if (confirm("Weet u zeker dat u dit profiel wilt verwijderen?")) {
				document.location.href = 'boardprofiles.php?actionID=<?=$TBBsession->getActionID(); ?>&actionName=delProfile&id='+selectedRow;
			}
		}

	// -->
	</script>
	<div class="adminContent">
<?php
	$menu = new Menu();
	$addPopupLink = "javascript:popupWindow('popups/editboardprofile.php', 500, 400, 'editprofile')";
	$menu->addItem("add", "", "Profiel toevoegen", $addPopupLink, "", "", 0, false, '');
	$menu->addItem("edit", "", "Profiel bewerken", "javascript:editProfile()", "", "", 0, false, '');
	$menu->addItem("delete", "", "Profiel verwijderen", "javascript:deleteProfile()", "", "", 0, false, '');
	$menu->showMenu('toolbar');


	$boardProfiles = $GLOBALS['TBBboardProfileList']->getProfiles();
	if (count($boardProfiles) > 0) {
		$table = new Table();
		$table->setHeader("ID", "Naam", "Modus", "Tel PostCount", "Signatures", "Gebruik");
		for ($i = 0; $i < count($boardProfiles); $i++) {
			$profile = $boardProfiles[$i];
			$table->addRow(
				$profile->getID(),
				htmlConvert($profile->getName()),
				htmlConvert($profile->getViewModus()),
				($profile->increasePostCount() ? "&bull;" : "" ),
				($profile->allowSignatures() ? "&bull;" : "" ),
				$profile->getNrUsed()
			);
		}
		$table->setRowSelect(array(0, 5), "selectProfile");
		$table->hideColumn(0);
		$table->showTable();
	} else {
		$text = new Text();
		$text->addHTMLText("Geen profielen gevonden. Klik <a href=\"".$addPopupLink."\">hier</a> om een nieuw profiel aan te maken.");
		$text->showText();
	}
?>
	</div>
<?php
	writeJumpLocationField(-1, "admincontrol");

	include($TBBincludeDir.'htmlbottom.php');
?>
