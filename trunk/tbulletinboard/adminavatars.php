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
	require_once($ivLibDir.'Form.class.php');
	require_once($ivLibDir.'FormFields.class.php');
	require_once($TBBclassDir.'AvatarList.class.php');
	require_once($ivLibDir.'Table.class.php');
	require_once($TBBclassDir.'Text.class.php');
	require_once($ivLibDir.'FileUpload.class.php');
	require_once($TBBclassDir.'ActionHandler.class.php');

	$iconUpload = new FileUpload("avatarFile", $TBBconfiguration->uploadDir . 'systemavatars/', "Avatar", 6);
	$iconUpload->setExtensions(".gif", ".jpg", ".png");
	$iconUpload->setMimeTypes("image/gif", "image/pjpeg", "image/jpeg", "image/png");
	$iconUpload->setRandomName('sav_');
	$iconUpload->setMaximumResolution(60, 60);

	$avatarList = new AvatarList();

	if (isSet($_GET['actionName']) && isSet($_GET['actionID'])) {
		if (($_GET['actionName'] == 'deleteAvatar') && ($_GET['actionID'] == $TBBsession->getActionID())) {
			$action = new ActionHandler($feedback, $_GET);
			$action->check($TBBcurrentUser->isAdministrator(), 'Deze actie is alleen voor Administrators!');
			$action->isNumeric('avatar', 'Geen geldige id opgegeven!');
			if ($action->correct) {
				$action->check($avatarList->removeAvatar($_GET['avatar']), 'Avatar kon niet worden verwijderd!');
				$action->finish('Avatar verwijderd!');
			}
		}
	}
	if ($TBBsession->getMessage() == "systemAvatarUpload") {
		$feedback->addMessage("Systeem avatar toegevoegd");
		$TBBsession->eraseMessage();
	}

	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation('Systeem instellingen', 'adminboard.php');
	$here->addLocation('Avatars', 'adminavatars.php');
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
	$menu->itemIndex = 'avatars';
	$menu->showMenu('adminMenu');

	$avatars = $avatarList->getSystemAvatarInfo();

?>
	<script type="text/javascript"><!--

		var selectAvatarID = -1;
		var selectType = 'empty';

		function selectAvatar(cellNr, avatarID, avatarType) {
			selectAvatarID = avatarID;
			selectType = avatarType;
		}

		function uploadAvatar() {
			popupWindow('popups/uploadavatar.php', 400, 300, 'useravatar', 1);
		}

		function deleteAvatar() {
			if (selectType == 'empty') return;
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
	$menu->addItem("delete", "", "Avatar verwijderen", (count($avatars) > 0) ? "javascript:deleteAvatar()" : "", "", "", 0, false, 'Avatar verwijderen');
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
	$restRows = $width - ($nrAvatars % $width);
	if ($restRows < $width) for ($i = 0; $i < $restRows; $i++) $avatarChoice->addRow('', 'empty', '');

	$avatarChoice->showTable();
?>
	</div>
<?php
	writeJumpLocationField(-1, "admincontrol");

	include($TBBincludeDir.'htmlbottom.php');
?>
