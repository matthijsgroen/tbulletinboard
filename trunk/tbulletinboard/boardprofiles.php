<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
	 */

	require_once("folder.config.php");
	// Load the configuration
	require_once($TBBconfigDir.'configuration.php');
	require_once($TBBclassDir.'tbblib.php');

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Instellingen';
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');
	require_once($TBBclassDir.'Location.class.php');
	require_once($TBBclassDir.'BoardProfiles.class.php');
	require_once($ivLibDir.'Table.class.php');
	require_once($TBBclassDir.'Text.class.php');

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
	$menu->addItem("add", "", "Profiel toevoegen", "javascript:popupWindow('popups/editboardprofile.php', 500, 400, 'editprofile')", "", "", 0, false, '');
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
		$text->addHTMLText("Geen profielen gevonden");
		$text->showText();
	}
?>
	</div>
<?php
	writeJumpLocationField(-1, "admincontrol");

	include($TBBincludeDir.'htmlbottom.php');
?>