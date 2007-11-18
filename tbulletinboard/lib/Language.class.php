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

	require_once($libraryClassDir."library.php");

	class Language {

		var $sets;
		var $dictionary = "";
		var $folder;

		function Language() {

		}

		function setDictionaryFolder($folder) {
			$this->folder = $folder;
		}

		function loadDictionary($dictionary) {
			if ($dictionary == $this->dictionary) return;
			$this->dictionary = $dictionary;
			$this->sets = array();
		}

		function getDictionary() {
			return $this->dictionary;
		}

		function getSentence($set, $index, $defaultText) {
			$this->loadSet($set);
			if (isSet($this->sets[$set][$index])) {
				if (defined("setting_developmode") && setting_developmode) $vm = "*"; // Visible Mark
				else $vm="";
				return convertTextCharacters($this->sets[$set][$index]).$vm;
			}
			return $defaultText;
		}

		function getSentenceLanguage($lang, $set, $index, $defaultText) {
			$currLang = $this->dictionary;
			$this->loadDictionary($lang);
			$this->loadSet($set);
			if (isSet($this->sets[$set][$index])) {
				if (defined("setting_developmode") && setting_developmode) $vm = "*"; // Visible Mark
				else $vm="";
				return convertTextCharacters($this->sets[$set][$index]).$vm;
			}
			$this->loadDictionary($currLang);
			return $defaultText;
		}

		function loadSet($set) {
			if (isSet($this->sets[$set])) return true;
			global $rootDir;
			$setFilename = $this->folder.$this->dictionary."/".$set.".lang.".$this->dictionary.".php";
			if (file_exists($setFilename)) {
				include($setFilename);
				$this->sets[$set] = $dictionary;
				return true;
			}
			$this->sets[$set] = array();
			return false;
		}

	}

	function ivMLGS($set, $index, $defaultText) {
		global $language;
		return $language->getSentence($set, $index, $defaultText);
	}

	function ivMLGSL($lang, $set, $index, $defaultText) {
		global $language;
		return $language->getSentenceLanguage($lang, $set, $index, $defaultText);
	}


	$GLOBALS['language'] = new Language();

?>
