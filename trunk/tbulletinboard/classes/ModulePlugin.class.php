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

	global $libraryClassDir;

	require_once($libraryClassDir . 'XMLParser.class.php');
	require_once($libraryClassDir . 'PackFile.class.php');
	require_once($TBBclassDir . 'Plugin.bean.php');
	require_once($TBBclassDir . 'Module.bean.php');

	class ModulePlugin {
		var $privateVars;

		function ModulePlugin() {
			$this->privateVars = array();
		}

		function setModuleDir($directory) {
			$this->privateVars['moduleDirectory'] = $directory;
		}

		function getModuleDir() {
			return $this->privateVars['moduleDirectory'];
		}

		function setModuleOnlineDir($directory) {
			$this->privateVars['moduleOnlineDirectory'] = $directory;
		}

		function getModuleOnlineDir() {
			return $this->privateVars['moduleOnlineDirectory'];
		}

		function setModuleName($name) {
			$this->privateVars['moduleName'] = $name;
		}

		function getModuleName() {
			return $this->privateVars['moduleName'];
		}

		function setPluginName($name) {
			$this->privateVars['pluginName'] = $name;
		}

		function getPluginName() {
			return $this->privateVars['pluginName'];
		}


		function setActive($active) {
			$this->privateVars['moduleActive'] = $active;
		}

		function isActive() {
			return $this->privateVars['moduleActive'];
		}

	}

	class ModuleAdministration {
		var $privateVars;

		function ModuleAdministration() {
			$this->privateVars = array();
			$this->privateVars['moduleCache'] = array();
			$this->privateVars['pluginCache'] = array();
			$this->privateVars['pluginObjectCache'] = array();
		}

		function getPackContents($filename) {
			$result = array();
			$result['info'] = array('group' => 'unknown', 'name' => 'unknown', 'version' => 'unknown', 'description' => '');
			$result['author'] = array('name' => 'unknown', 'url' => '', 'email' => '');
			$result['plugins'] = array();
			$xmlContents = file_get_contents($filename);
			$parser = new XMLParser($xmlContents);

			if ($parser->containsTag("info")) {
				$moduleInfo = $parser->getTagContent("info");
				if ($moduleInfo->containsTag("group")) {
					$group = $moduleInfo->getTagContent("group");
					$result['info']['group'] = $group->getText();
				}
				if ($moduleInfo->containsTag("name")) {
					$name = $moduleInfo->getTagContent("name");
					$result['info']['name'] = $name->getText();
				}
				if ($moduleInfo->containsTag("version")) {
					$version = $moduleInfo->getTagContent("version");
					$result['info']['version'] = $version->getText();
				}
				if ($moduleInfo->containsTag("description")) {
					$description = $moduleInfo->getTagContent("description");
					$result['info']['description'] = $description->getText();
				}
			}

			if ($parser->containsTag("author")) {
				$authorInfo = $parser->getTagContent("author");
				if ($authorInfo->containsTag("name")) {
					$authorName = $authorInfo->getTagContent("name");
					$result['author']['name'] = $authorName->getText();
				}
				if ($authorInfo->containsTag("url")) {
					$authorUrl = $authorInfo->getTagContent("url");
					$result['author']['url'] = $authorUrl->getText();
				}
				if ($authorInfo->containsTag("email")) {
					$authorEmail = $authorInfo->getTagContent("email");
					$result['author']['email'] = $authorEmail->getText();
				}
			}
			while ($parser->containsTag("plugin")) {
				$plugAttr = $parser->getTagAttributes("plugin");
				$plugin = $parser->getTagContent("plugin");
				$pluginInfo = array();
				if (isSet($plugAttr['type'])) $pluginInfo['type'] = $plugAttr['type'];
				if ($plugin->containsTag("name")) {
					$plugName = $plugin->getTagContent("name");
					$pluginInfo['name'] = $plugName->getText();
				}
				if ($plugin->containsTag("version")) {
					$field = $plugin->getTagContent("version");
					$pluginInfo['version'] = $field->getText();
				}
				if ($plugin->containsTag("build")) {
					$field = $plugin->getTagContent("build");
					$pluginInfo['build'] = $field->getText();
				}
				if ($plugin->containsTag("filename")) {
					$field = $plugin->getTagContent("filename");
					$pluginInfo['filename'] = $field->getText();
				}
				if ($plugin->containsTag("classname")) {
					$field = $plugin->getTagContent("classname");
					$pluginInfo['classname'] = $field->getText();
				}

				$result['plugins'][] = $pluginInfo;
			}
			return $result;
		}

		function installModule($moduleFile, $groupName, &$feedback) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$moduleFolder = $TBBconfiguration->uploadDir.'modules/'.$groupName.'/';
			@mkdir($moduleFolder);
			$moduleFile = new PackFile();
			$moduleFile->load($TBBconfiguration->uploadDir.'temp/newmodule.tbbmod');
			$moduleFile->saveAllFiles($moduleFolder);
			$packageContents = $this->getPackContents($moduleFolder.'deploy.xml');

			$moduleTable = new ModuleTable($database);
			$filter = new DataFilter();
			$filter->addEquals("group", $groupName);
			$moduleTable->deleteRows($filter);

			$newModule = $moduleTable->addRow();
			$newModule->setValue("group", $packageContents['info']['group']);
			$newModule->setValue("name", $packageContents['info']['name']);
			$newModule->setValue("version", $packageContents['info']['version']);
			$newModule->setValue("author", $packageContents['author']['name']);
			$newModule->setValue("authorUrl", $packageContents['author']['url']);
			$newModule->setValue("authorEmail", $packageContents['author']['email']);
			$newModule->setValue("description", $packageContents['info']['description']);
			$newModule->store();


			$pluginTable = new PluginTable($database);
			$filter = new DataFilter();
			$filter->addEquals("group", $groupName);
			$pluginTable->deleteRows($filter);
			for ($i = 0; $i < count($packageContents['plugins']); $i++) {
				$pluginInfo = $packageContents['plugins'][$i];
				$newPlugin = $pluginTable->addRow();
				$newPlugin->setValue("group", $packageContents['info']['group']);
				$newPlugin->setValue("name", $pluginInfo['name']);
				$newPlugin->setValue("version", $pluginInfo['version']);
				$newPlugin->setValue("build", $pluginInfo['build']);
				$newPlugin->setValue("type", $pluginInfo['type']);
				$newPlugin->setValue("active", false);
				$newPlugin->setValue("filename", $pluginInfo['filename']);
				$newPlugin->setValue("classname", $pluginInfo['classname']);
				$newPlugin->setValue("installDate", new LibDateTime());
				$newPlugin->store();
			}
			return true;
		}

		function hasModule($modulename) {
			if (isSet($this->privateVars['moduleCache'][$modulename])) {
				return ($this->privateVars['moduleCache'] !== false);
			}
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$pluginTable = new PluginTable($database);
			$filter = new DataFilter();
			$filter->addEquals("group", $modulename);
			$sorting = new ColumnSorting();
			$pluginTable->selectRows($filter, $sorting);
			if ($pluginTable->getSelectedRowCount() < 1) {
				$this->privateVars['moduleCache'][$modulename] = false;
				return false;
			}
			$this->privateVars['moduleCache'][$modulename] = array();
			while ($pluginRow = $pluginTable->getRow()) {
				$this->privateVars['moduleCache'][$modulename][] = $pluginRow;
				$type = $pluginRow->getValue("type");
				if (!isSet($this->privateVars['pluginCache'][$type])) {
					$this->privateVars['pluginCache'][$type] = array();
				}
				$this->privateVars['pluginCache'][$type][$modulename] = $pluginRow;
			}
			return true;
		}

		function getNrPluginsOf($modulename) {
			if (!$this->hasModule($modulename)) return false;
			return count($this->privateVars['moduleCache'][$modulename]);
		}

		function getPluginInfo($modulename, $plugintype) {
			if (isSet($this->privateVars['pluginCache'][$plugintype]) &&
					isSet($this->privateVars['pluginCache'][$plugintype][$modulename]))  {
				return $this->privateVars['pluginCache'][$plugintype][$modulename];
			}
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$pluginTable = new PluginTable($database);
			$filter = new DataFilter();
			$filter->addEquals("group", $modulename);
			$filter->addEquals("type", $plugintype);
			$sorting = new ColumnSorting();
			$pluginTable->selectRows($filter, $sorting);
			if (!isSet($this->privateVars['pluginCache'][$plugintype])) {
				$this->privateVars['pluginCache'][$plugintype] = array();
			}
			if ($pluginTable->getSelectedRowCount() < 1) {
				$this->privateVars['pluginCache'][$plugintype][$modulename] = false;
				return false;
			}
			if ($pluginRow = $pluginTable->getRow()) {
				$this->privateVars['pluginCache'][$plugintype][$modulename] = $pluginRow;
				return $pluginRow;
			}
			return false;
		}

		function getPluginInfoType($plugintype, $needActive = false) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$pluginTable = new PluginTable($database);
			$filter = new DataFilter();
			$filter->addEquals("type", $plugintype);
			if ($needActive)
				$filter->addEquals("active", true);
			$sorting = new ColumnSorting();
			$pluginTable->selectRows($filter, $sorting);
			if (!isSet($this->privateVars['pluginCache'][$plugintype])) {
				$this->privateVars['pluginCache'][$plugintype] = array();
			}
			$result = array();
			while ($pluginRow = $pluginTable->getRow()) {
				$modulename = $pluginRow->getValue("group");
				$this->privateVars['pluginCache'][$plugintype][$modulename] = $pluginRow;
				$result[] = $pluginRow;
			}
			return $result;
		}

		function getPlugin($modulename, $plugintype) {
			if (isSet($this->privateVars['pluginObjectCache'][$plugintype]) &&
					isSet($this->privateVars['pluginObjectCache'][$plugintype][$modulename]))  {
				return $this->privateVars['pluginObjectCache'][$plugintype][$modulename];
			}
			global $TBBconfiguration;
			$info = $this->getPluginInfo($modulename, $plugintype);
			if ($info === false) return false;

			$className = $info->getValue("classname");
			if (isSet($_GLOBALS['developmentMode'])) {
				$moduleDir = $TBBconfiguration->uploadDir.'modules/'.$modulename.'/';
				$moduleOnlineDir = $TBBconfiguration->uploadOnlineDir.'modules/'.$modulename.'/';
			} else {
				$moduleDir = $TBBconfiguration->uploadDir.'../modules/'.$modulename.'/';
				$moduleOnlineDir = $TBBconfiguration->uploadOnlineDir.'../modules/'.$modulename.'/';
			}

			require_once($moduleDir.$info->getValue("filename"));

			$obj = new $className();
			$obj->setModuleDir($moduleDir);
			$obj->setModuleName($modulename);
			$obj->setModuleOnlineDir($moduleOnlineDir);
			$obj->setPluginName($info->getValue("name"));
			$obj->setActive($info->getValue("active"));
			if (!isSet($this->privateVars['pluginObjectCache'][$plugintype])) {
				$this->privateVars['pluginObjectCache'][$plugintype] = array();
			}
			$this->privateVars['pluginObjectCache'][$plugintype][$modulename] = $obj;
			return $obj;
		}

		function getNormalPluginTypeName($type) {
			switch($type) {
				case "search": return "Zoek plugin";
				case "topic": return "Onderwerp plugin";
				case "admin": return "Admin plugin";
				case "usertype": return "Leden type plugin";
				case "userpanel": return "Leden paneel plugin";
				default: return "Onbekend";
			}
		}

	}

	$GLOBALS['TBBModuleManager'] = new ModuleAdministration();

?>
