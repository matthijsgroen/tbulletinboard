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
	require_once($ivLibDir.'Table.class.php');
	require_once($TBBclassDir.'ActionHandler.class.php');

	if (isSet($_GET['actionName']) && isSet($_GET['actionID'])) {
		if (($_GET['actionName'] == 'add') && ($_GET['actionID'] == $TBBsession->getActionID())) {
			$action = new ActionHandler($feedback, $_GET);
			$action->check($TBBcurrentUser->isMaster(), 'Deze actie is alleen voor Masters!');
			$action->notEmpty('moduleClassName', 'Geen klasnaam ingevult!');
			if ($action->correct) {
				$className = trim($_GET['moduleClassName']);
				$action->check(class_exists($className), 'De klasse <strong>'.$className.'</strong> bestaat niet!');
			}
			if ($action->correct)
				$action->check(@is_subclass_of(new $className(), 'MemberModule'), 'De klasse <strong>'.$className.'</strong> stamt niet af van <strong>MemberModule</strong>!');
			if ($action->correct)
				$action->check($TBBconfiguration->addMemberModule($className), 'module kon niet worden toegevoegd!');
			$action->finish('Module toegevoegd');
		}
	}

	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation('Systeem instellingen', 'adminboard.php');
	$here->addLocation('Leden Modules', 'adminmodmem.php');
	$here->showLocation();

	if (!$TBBsession->isLoggedIn()) {
		$text = new Text();
		$text->addHTMLText("Sorry, gasten hebben geen instellingen venster!");
		$text->showText();
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}

	if (!$TBBcurrentUser->isMaster()) {
		$text = new Text();
		$text->addHTMLText("Sorry, dit venster is alleen voor masters!");
		$text->showText();
		include($TBBincludeDir.'htmlbottom.php');
		exit;
	}

	include($TBBincludeDir.'configmenu.php');
	$adminMenu->itemIndex = 'system';
	$adminMenu->showMenu('configMenu');

	include($TBBincludeDir.'admin_menu.php');
	$menu->itemIndex = 'membermodules';
	$menu->showMenu('adminMenu');

?>
	<div class="adminContent">
<?php

	$memberModulesInfo = $TBBconfiguration->getMemberModulesInfo();

	$foundModules = array();
	//Search possible modules
	$classNames = get_declared_classes();
	for ($i = 0; $i < count($classNames); $i++) {
		$modClassName = $classNames[$i];
		$pos = strPos($modClassName, '__');
		if (($pos != 0) or ($pos === false)) {
			if (strCaseCmp(get_parent_class($modClassName), 'MemberModule') == 0) {
				$foundModules[] = $modClassName;
			}
		}
	}

	if ((count($memberModulesInfo) + (count($foundModules))) == 0) {
		$text = new Text();
		$text->addHTMLText("Sorry, dit venster is alleen voor masters!");
		$text->showText();
	} else {
		$table = new Table();
		$table->setHeader("Naam", "Class", "Beschrijving", "Opties");
		for ($i = 0; $i < count($memberModulesInfo); $i++) {
			$memberModuleInfo = $memberModulesInfo[$i];
			$module = $TBBconfiguration->getMemberModule($memberModuleInfo['ID']);
			$table->addRow(
				htmlConvert($memberModuleInfo['name']),
				htmlConvert($memberModuleInfo['className']),
				htmlConvert($module->getModuleDescription()),
				""
			);
		}
		for ($i = 0; $i < count($foundModules); $i++) {
			$moduleClassName = $foundModules[$i];
			if (!$TBBconfiguration->isMemberModuleInstalled($moduleClassName)) {
				$module = new $moduleClassName();
				$table->addRow(
					htmlConvert($module->getModuleName()),
					htmlConvert($moduleClassName),
					htmlConvert($module->getModuleDescription()),
					sprintf("<a href=\"adminmodmem.php?actionName=add&amp;actionID=%s&amp;moduleClassName=%s\" class=\"actionLink\">voeg toe</a>", $TBBsession->getActionID(), htmlConvert($moduleClassName))
				);
			}
		}
		$table->showTable();
	}
?>
	</div>
<?php
	writeJumpLocationField(-1, "admincontrol");

	include($TBBincludeDir.'htmlbottom.php');
?>
