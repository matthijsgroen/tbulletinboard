<?php

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
