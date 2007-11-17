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

	require_once($TBBclassDir."ModulePlugin.class.php");
	require_once($TBBclassDir."Board.class.php");

	class SearchPlugin extends ModulePlugin {

		function SearchPlugin() {
			$this->ModulePlugin();
		}

		function getSearchName() {
			return $this->getModuleName();
		}
		
		function hasAccess(&$user) {
			return false;
		}

		function buildSearchForm(&$form, $step, $boardID) {
		}

		function hasMoreSearchFormSteps($wizzStep) {
			return false;
		}

		function handleSearchForm(&$feedback, &$form, $step) {
			return false;
		}

		function executeSearch(&$searchResult, &$feedback) {
			return true;
		}

		function getSearchLocations($parentID) {
			global $TBBboardList;
			global $TBBcurrentUser;
			return $TBBboardList->getReadableBoardIDs($parentID, $TBBcurrentUser);
		}

	}

?>
