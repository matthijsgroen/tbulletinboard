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

	importClass("board.SearchPlugin");
	importClass("board.BoardFormFields");
	importClass("interface.FormFields");

	class TopicSearchPlugin extends SearchPlugin {

		function TopicSearchPlugin() {
			$this->SearchPlugin();
		}

		function getSearchName() {
			return "Onderwerpen";
		}

		function hasAccess(&$user) {
			return true;
		}

		function buildSearchForm(&$form, $step, $boardID) {
			$formFields = new StandardFormFields();
			$form->addFieldGroup($formFields);
			$formFields->activeForm =& $form;

			$boardFormFields = new BoardFormFields();
			$form->addFieldGroup($boardFormFields);
			$boardFormFields->activeForm =& $form;

			$formFields->startGroup("Onderwerpen Zoeken");
			$formFields->addText("Uitleg", "", "Probeer te zoeken op specifieke woorden (meerdere mogen). De meest relevante onderwerpen worden bovenaan geplaatst. Er wordt niet gezocht op algemene woorden die veel voorkomen in berichten. (bijv. de, het, een). Er wordt niet gelet op hoofdletters tijdens het zoeken.");


			$formFields->addTextField("searchText", "Wat", "tekst waarop gezocht wordt", 255);
			$boardFormFields->addBoardSelect("searchLocation", "Waar", "locatie waarin gezocht wordt", $boardID, true);

			$periodOptions = array();
			$periodOptions["10"] = "Afgelopen 10 dagen";
			$periodOptions["20"] = "Afgelopen 20 dagen";
			$periodOptions["30"] = "Afgelopen 30 dagen";
			$periodOptions["60"] = "Afgelopen 60 dagen";
			$periodOptions["120"] = "Afgelopen 120 dagen";
			$periodOptions["180"] = "Afgelopen 180 dagen";
			$periodOptions["360"] = "Afgelopen 360 dagen";
			$formFields->addSelect("searchPeriod", "Wanneer", "periode waarop gezocht wordt", $periodOptions, "30");

			$formFields->endGroup();
			$formFields->addSubmit("Zoeken", true);
		}

		function hasMoreSearchFormSteps($wizzStep) {
			return ($wizzStep < 1);
		}

		function handleSearchForm(&$feedback, &$form, $step) {
			if (strLen(trim($_POST['searchText'])) == 0) {
				$feedback->addMessage("Geen zoekterm opgegeven!");
				return false;
			}
			return true;
		}

		function executeSearch(&$searchResult, &$feedback) {
			$searchResult->setSearchSubject("onderwerp", "onderwerpen");

			$searchResult->defineColumnNames("Onderwerp", "Starter", "Board", "Laatste reactie");
			$searchResult->defineColumnTypes("text", "text", "text", "date");
			$searchResult->defineSortColumns(0, 1, 2, 3);

			$searchLocations = $this->getSearchLocations($_POST['searchLocation']);
			$startPeriod = new LibDateTime();
			$endPeriod = new LibDateTime();
			$endPeriod->sub(ivDay, $_POST['searchPeriod']);

			global $TBBModuleManager;
			$pluginInfo = $TBBModuleManager->getPluginInfoType("topic");
			for ($i = 0; $i < count($pluginInfo); $i++) {
				$topicPlugin = $TBBModuleManager->getPlugin($pluginInfo[$i]->getValue("group"), "topic");
				if (!$topicPlugin->searchText($searchResult, $startPeriod, $endPeriod, $_POST['searchText'],
						$searchLocations, false, $feedback)) {
					return false;
				}
			}
			return true;
		}

	}

?>
