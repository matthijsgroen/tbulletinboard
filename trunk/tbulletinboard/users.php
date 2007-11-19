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

	importClass("board.UserManagement");
	importClass("interface.Table");
	importClass("board.ModulePlugin");
	importClass("board.SearchPlugin");
	importClass("board.Location");
	importClass("board.Text");

	$pageTitle = $TBBconfiguration->getBoardName() . ' - Gebruikers';
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');

	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation("Gebruikers", 'users.php');
	$here->showLocation();
	
	$searchState = 'search';
	if (isSet($_GET['result'])) {
		$searchState = 'result';
		$resultID = $_GET['result'];
		// Instead of searching, get the searchresult from the cache
		$searchResult = new SearchResult();
		if (!$searchResult->getCachedResult($resultID)) {
			$searchState = 'search';
		}
	}

	if ($searchState == 'search') {
		// execute the search
		$searchPlugin = $TBBModuleManager->getPlugin("memberSearch", "search");
		if ($searchPlugin === false) {
			$searchState = "na";
		} else {		
			$searchResult = new SearchResult();
			if ($searchPlugin->executeSearch($searchResult, $feedback)) {
				if ($searchResult->getResultCount() > 0)
					$resultID = $searchResult->cacheInDatabase();
				$searchState = 'result';
			} else {
				$seachState = 'form';
			}
		}
	}

	
	if ($searchState == 'result') {
		$resultCount = $searchResult->getResultCount();
		$text = new Text();
		$text->addHTMLText(sprintf('Er %s %s %s gevonden', ($resultCount == 1) ? "is" : "zijn", $resultCount, $searchResult->getSearchSubject(($resultCount == 1))));
		$text->showText();

		$sortColumn = 0;
		if (isSet($_GET['sortColumn'])) $sortColumn = $_GET['sortColumn'];
		$sortType = 'none';
		if (isSet($_GET['sortType'])) $sortType = $_GET['sortType'];
		$pageLimit = 30;
		$pageNr = 0;
		if (isSet($_GET['pageNr'])) $pageNr = $_GET['pageNr'] -1;

		if ($resultCount > $pageLimit) {
			require_once($libraryClassDir.'PageNavigation.class.php');
			$pageBar = new PageNavigation(ceil($resultCount / $pageLimit), ($pageNr+1),
				sprintf("users.php?pageNr=%%s&amp;result=%s&amp;sortType=%s&amp;sortColumn=%s", $resultID, $sortType, $sortColumn), 10);
			$pageBar->showPagebar("searchPageBar");
		}
		if ($resultCount > 0) {
			$results = $searchResult->getResultTable(array("result=".$resultID), $pageNr * $pageLimit, $pageLimit, $sortColumn, $sortType);
			$results->showTable();
		}

		if ($resultCount > $pageLimit) {
			$pageBar->showPagebar("searchPageBar");
		}

	}
	if ($searchState == 'na') {
		$text = new Text();
		$text->addHTMLText('Sorry de ledenlijst is niet beschikbaar. Hier is een leden zoek plugin voor nodig en deze is niet beschikbaar.');
		$text->showText();
	}

	
	$here->showLocation();

	include($TBBincludeDir.'htmlbottom.php');
?>
