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

	if ($TBBsession->getMessage()) {
		if ($TBBsession->getMessage() == "iconAdd") {
			$feedback->addMessage("Emoticon toegevoegd!");
			$TBBsession->eraseMessage();
		}
		if ($TBBsession->getMessage() == "iconEdit") {
			$feedback->addMessage("Emoticon bewerkt!");
			$TBBsession->eraseMessage();
		}
	}

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Instellingen';
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');

	importClass("util.ToolbarHelper");
	importClass("interface.Location");
	importClass("interface.Form");
	importClass("interface.FormFields");
	importClass("interface.formcomponents.RecordSelect");
	importClass("interface.Table");
	importBean("board.Emoticon");
	importClass("board.TBBEmoticonList");
	importClass("interface.Text");
	importClass("board.ActionHandler");
	importClass("orm.DataOrderHelper");

	$actionHandler = new ActionHandler($feedback);
	$actionHandler->definePostAction("delete");
	$actionHandler->defineGetAction("moveUp", "id");
	$actionHandler->defineGetAction("moveDown", "id");

	if ($actionHandler->inAction("delete")) {
		$actionHandler->check($TBBcurrentUser->isAdministrator(), 'Deze actie is alleen voor Administrators!');
		for ($i = 0; $i < count($_POST["iconID"]); $i++) {
			$actionHandler->check($GLOBALS['TBBemoticonList']->deleteEmoticon($_POST['iconID'][$i]), 'Emoticon kon niet worden verwijderd!');
		}
		//$action->finish();
		$actionHandler->actionHandled('Emoticon verwijderd');
	}

	$database = $TBBconfiguration->getDatabase();
	$emoticonTable = new EmoticonTable($database);

	$dataOrderHelper = new DataOrderHelper($emoticonTable, "name", "order");
	if ($actionInfo = $actionHandler->inAction("moveUp")) {
		$iconID = $actionInfo->getProperty("id");
		$dataOrderHelper->moveRecord($iconID, -1, "");
		$GLOBALS['TBBemoticonList']->readEmoticonsInfo(true);
		$actionHandler->actionHandled();
	} else if ($actionInfo = $actionHandler->inAction("moveDown")) {
		$iconID = $actionInfo->getProperty("id");
		$dataOrderHelper->moveRecord($iconID, 1, "");
		$GLOBALS['TBBemoticonList']->readEmoticonsInfo(true);
		$actionHandler->actionHandled();
	}
	
	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation('Systeem instellingen', 'adminboard.php');
	$here->addLocation('Emoticons', 'admintext.php');
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
	$menu->itemIndex = 'emoticons';
	$menu->showMenu('adminMenu');

?>
	<div class="adminContent">
<?php
	$toolbarHelper = new ToolbarHelper("fieldSelect", "emoticon");
	$toolbarHelper->setRecordProperties("ID", "name");

	$menu = new Menu();
	$menu->addItem("add", "", "Emoticon toevoegen", "javascript:addEmoticon()", "", "", 0, false, '');
	$toolbarHelper->addPopup("addEmoticon", 'popups/editemoticon.php', 500, 300);

	$menu->addItem("edit", "", "Emoticon bewerken", "javascript:editEmoticon()", "", "", 0, false, '');
	$toolbarHelper->addRecordPopup("editEmoticon", 'popups/editemoticon.php?id=%ID%', 500, 350);
	
	$menu->addItem("delete", "", "Emoticon(s) verwijderen", "javascript:deleteChecked(document.emoticonForm['iconID[]'], document.emoticonForm, 'Geen Emoticons aangevinkt!')", "", "", 0, false, '');

	$menu->addItem("up", "", "Omhoog", "javascript:moveEmoticonUp()", "", "", 0, false, '');
	$toolbarHelper->addRecordRedirect("moveEmoticonUp", "?action=moveUp&id=%ID%&actionID=".$TBBsession->getActionID());

	$menu->addItem("down", "", "Omlaag", "javascript:moveEmoticonDown()", "", "", 0, false, '');
	$toolbarHelper->addRecordRedirect("moveEmoticonDown", "?action=moveDown&id=%ID%&actionID=".$TBBsession->getActionID());

	$menu->showMenu('toolbar');


	$emoticons = $GLOBALS['TBBemoticonList']->getEmoticons();
	$table = new Table();
	$table->setHeader("iconID", "Emoticon", "Naam", "Codes");
	for ($i = 0; $i < count($emoticons); $i++) {
		$emoticon = $emoticons[$i];
		$code = "";
		for ($j = 0; $j < count($emoticon['textCodes']); $j++) {
			$code .= "<kbd>" . $emoticon['textCodes'][$j] . "</kbd>";
			if ($j < (count($emoticon['textCodes']) - 1)) $code .= ", ";
		}

		$table->addRow(
			$emoticon["ID"],
			sprintf('<img src="%s" alt="" />', $emoticon['imgUrl']),
			htmlConvert($emoticon['name']),
			$code
		);
	}
	$table->setCheckboxColumn(0);
	//$table->setClickColumn(0, "selectEmoticon", false);

	$table->setRowSelect(array(0, 1), 'fieldSelect');
	//$table->hideColumn(0);
	$table->setRowDoubleClickFunction("editEmoticon");

	print $toolbarHelper->getJavascript();

	$tagForm = new Form("emoticonForm", "admintext.php");
	$tagForm->addHiddenField("actionID", $TBBsession->getActionID());
	$tagForm->addHiddenField("action", "delete");

	$tagForm->addComponent(new FormRecordSelect($table));
	$tagForm->writeForm();
?>
	</div>
<?php
	writeJumpLocationField(-1, "admincontrol");

	include($TBBincludeDir.'htmlbottom.php');
?>
