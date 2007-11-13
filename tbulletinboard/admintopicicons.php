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
	require_once($ivLibDir.'Form.class.php');
	require_once($ivLibDir.'FormFields.class.php');
	require_once($TBBclassDir.'TopicIconList.class.php');
	require_once($ivLibDir.'Table.class.php');
	require_once($TBBclassDir.'Text.class.php');
	require_once($ivLibDir.'FileUpload.class.php');
	require_once($TBBclassDir.'ActionHandler.class.php');

	$topicIconList = new TopicIconList();

	$feedback->showMessages();

	if ($TBBsession->getMessage() == "uploadIcon") {
		$feedback->addMessage("Onderwerp icoon toegevoegd");
		$TBBsession->eraseMessage();
	}
	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation('Systeem instellingen', 'adminboard.php');
	$here->addLocation('Onderwerp iconen', 'admintopicicons.php');
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
	$menu->itemIndex = 'topicIcons';
	$menu->showMenu('adminMenu');

?>
	<script type="text/javascript"><!--
		var selectIconID = -1;

		function selectIcon(iconID) {
			selectIconID = iconID;
		}

		function uploadIcon() {
			popupWindow('popups/uploadicon.php', 400, 300, 'topicicon', 1);
		}

		function deleteIcon() {
			if (selectIconID == -1) {
				alert("geen onderwerp icoon geselecteerd!");
				return;
			}
			if (!confirm("Weet je zeker dat je deze icoon wilt verwijderen?")) return;
			document.location.href="?actionName=deleteIcon&actionID=<?=$TBBsession->getActionID() ?>&icon="+selectIconID;
		}

	//-->
	</script>
	<div class="adminContent">
<?php
	$iconList = $topicIconList->getIconsInfo();
	$menu = new Menu();
	$menu->addItem("add", "", "Toevoegen", "javascript:uploadIcon()", "", "", 0, false, 'Onderwerp icoon toevoegen');
	$menu->addItem("delete", "", "Verwijderen", (count($iconList) > 0) ? "javascript:deleteIcon()" : "", "", "", 0, false, 'Onderwerpicoon verwijderen');
	$menu->showMenu('toolbar');


	$text = new Text();
	$text->addHTMLText("Onderwerp iconen zijn de iconen waar gebruikers uit kunnen kiezen bij het starten van hun onderwerp");
	$text->showText();

	if (count($iconList) == 0) {
		$text = new Text();
		$text->addHTMLText("Geen onderwerp iconen gevonden!");
		$text->showText();
	} else {
		$table = new Table();
		$table->setHeader("ID", "Naam", "Icoon");
		for ($i = 0; $i < count($iconList); $i++) {
			$icon = $iconList[$i];
			$table->addRow($icon["ID"], htmlConvert($icon['name']), sprintf('<img src="%s" alt="%s" />', $icon['imgUrl'], $icon['name']));
		}
		$table->hideColumn(0);
		$table->setRowSelect(array(0), "selectIcon");

		$table->showTable();
	}
?>
	</div>
<?php
	writeJumpLocationField(-1, "admincontrol");

	include($TBBincludeDir.'htmlbottom.php');
?>