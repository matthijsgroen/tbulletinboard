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
	require_once($TBBclassDir.'Location.class.php');
	require_once($TBBclassDir.'Text.class.php');
	require_once($ivLibDir.'Table.class.php');
	require_once($ivLibDir.'Menu.class.php');
	require_once($TBBclassDir.'AvatarList.class.php');
	require_once($TBBclassDir.'ActionHandler.class.php');

	$avatarList = new AvatarList();

	if (isSet($_GET['actionName']) && isSet($_GET['actionID'])) {
		if (($_GET['actionName'] == 'changeAvatar') && ($_GET['actionID'] == $TBBsession->getActionID())) {
			$action = new ActionHandler($feedback, $_GET);
			$action->check(!$TBBcurrentUser->isGuest(), 'Alleen voor ingelogde gebruikers mogelijk!');
			$action->isNumeric('avatar', 'Geen geldige id opgegeven!');
			if ($action->correct) {
				$action->check($TBBcurrentUser->changeAvatar($_GET['avatar']), 'Avatar kon niet worden veranderd!');
				$action->finish('Avatar veranderd!');
			}
		}
		if (($_GET['actionName'] == 'deleteAvatar') && ($_GET['actionID'] == $TBBsession->getActionID())) {
			$action = new ActionHandler($feedback, $_GET);
			$action->check(!$TBBcurrentUser->isGuest(), 'Alleen voor ingelogde gebruikers mogelijk!');
			$action->isNumeric('avatar', 'Geen geldige id opgegeven!');
			if ($action->correct) {
				$action->check($avatarList->removeUserAvatar($TBBcurrentUser, $_GET['avatar']), 'Avatar kon niet worden verwijderd!');
				$action->finish('Avatar verwijderd!');
			}
		}
	}
	if ($TBBsession->getMessage() == "userAvatarUpload") {
		$feedback->addMessage("Eigen avatar toegevoegd");
		$TBBsession->eraseMessage();
	}

	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation(sprintf('Instellingen voor %s', htmlConvert($TBBcurrentUser->getNickname())), 'usercontrol.php');
	$here->addLocation('Avatar', 'useroptions.php');
	$here->showLocation();

	if (!$TBBsession->isLoggedIn()) {
		$text = new Text();
		$text->addHTMLText("Sorry, gasten hebben geen instellingen venster!");
		$text->showText();
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}


	include($TBBincludeDir.'configmenu.php');
	$adminMenu->itemIndex = 'user';
	$adminMenu->showMenu('configMenu');

	include($TBBincludeDir.'user_control_menu.php');
	$menu->itemIndex = 'avatar';
	$menu->showMenu('adminMenu');

	$avatars = $avatarList->getSystemAvatarInfo();
	$userAvatars = $avatarList->getUserAvatarInfo($TBBcurrentUser->getUserID());

	$avatarID = $TBBcurrentUser->getAvatarID();

?>
	<script type="text/javascript"><!--
		var hasOwnAvatar = <?=(($avatarID !== false) && (!$avatarList->isSystemAvatar($avatarID))) ? "true" : "false" ?>;
		var currentSelected = '<?=$avatarID ?>';
		var avatarCount = <?=count($userAvatars); ?>

		var selectAvatarID = -1;
		var selectType = 'empty';

		function selectAvatar(cellNr, avatarID, avatarType) {
			selectAvatarID = avatarID;
			selectType = avatarType;
		}

		function applyAvatar() {
			avatarType = selectType;
			avatarID = selectAvatarID;
			if (avatarType == 'empty') return;

			if ((avatarType != 'user') && (hasOwnAvatar)) {
				//if (!confirm('Een andere avatar kiezen heeft tot gevolg dat je huidige avatar verdwijnt! doorgaan?')) {
				//	return;
				//}
			}
			document.location.href="?actionName=changeAvatar&actionID=<?=$TBBsession->getActionID() ?>&avatar="+avatarID;
		}

		function uploadAvatar() {
			if (avatarCount < 3) {
				popupWindow('popups/uploaduseravatar.php', 400, 300, 'useravatar', 1);
			} else {
				alert('Je mag maximaal 3 eigen avatars instellen!');
			}
		}

		function deleteAvatar() {
			if (selectType == 'empty') return;
			if (selectType != 'user') {
				alert('Je kan alleen je eigen avatars verwijderen');
				return;
			}
			if (confirm("Weet je zeker dat je deze avatar wilt verwijderen?")) {
				document.location.href="?actionName=deleteAvatar&actionID=<?=$TBBsession->getActionID() ?>&avatar="+selectAvatarID;
			}
		}

	//-->
	</script>
	<div class="adminContent">
<?php
	$menu = new Menu();
	$menu->addItem("add", "", "Avatar uploaden", "javascript:uploadAvatar()", "", "", 0, false, 'Je eigen avatar uploaden');
	$menu->addItem("apply", "", "Avatar instellen", "javascript:applyAvatar()", "", "", 0, false, 'Avatar instellen');
	$menu->addItem("delete", "", "Avatar verwijderen", (count($userAvatars) > 0) ? "javascript:deleteAvatar()" : "", "", "", 0, false, 'Avatar verwijderen');
	$menu->showMenu('toolbar');

	$width = 4;

	$avatarChoice = new Table();
	$avatarChoice->setClass("table avatarTable");
	$avatarChoice->setHeader("ID", "type", "Avatar");

	$avatarChoice->setCellSelect(array(0,1),array(2), "selectAvatar");
	$avatarChoice->hideColumn(0);
	$avatarChoice->hideColumn(1);
	$avatarChoice->hideHeader();
	$avatarChoice->setCellLimit($width * 3);

	$nrAvatars = count($avatars);
	if ($nrAvatars > 0) $avatarChoice->addGroup("Standaard Avatars");
	for ($i = 0; $i < $nrAvatars; $i++) {
		$avatar = $avatars[$i];
		$avatarChoice->addRow($avatar['ID'], "system", sprintf('<img src="avatar.php?id=%s" alt="avatar" />', $avatar['ID']));
	}
	if (count($userAvatars) == 0) {
		$avatarChoice->addRow('0', 'clear', '(geen)');
		$nrAvatars++;
	}
	$restRows = $width - ($nrAvatars % $width);
	if ($restRows < $width) for ($i = 0; $i < $restRows; $i++) $avatarChoice->addRow('', 'empty', '');

	if (count($userAvatars) > 0) {
		$avatarChoice->addGroup("Eigen avatars");
		$nrAvatars = count($userAvatars);
		for ($i = 0; $i < $nrAvatars; $i++) {
			$avatar = $userAvatars[$i];
			$avatarChoice->addRow($avatar['ID'], "user", sprintf('<img src="avatar.php?id=%s" alt="avatar" />', $avatar['ID']));
		}
		$avatarChoice->addRow('0', 'clear', '(geen)');
		$restRows = $width - (($nrAvatars+1) % $width);
		if ($restRows < $width) for ($i = 0; $i < $restRows; $i++) $avatarChoice->addRow('', 'empty', '');
	}
	if ($avatarID !== false) {
		$avatarChoice->selectCell($avatarID, 0, 2);
	} else {
		$avatarChoice->selectCell('clear', 1, 2);
	}

	$avatarChoice->showTable();

?>
	</div>
<?php
	writeJumpLocationField(-1, "usercontrol");

	include($TBBincludeDir.'htmlbottom.php');
?>
