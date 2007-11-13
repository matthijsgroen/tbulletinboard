<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (matthijs at ivinity.nl)
	 *@version 2.0
	 */

	require_once("folder.config.php");
	// Load the configuration
	require_once($TBBconfigDir.'configuration.php');
	require_once($TBBclassDir.'tbblib.php');
	require_once($TBBclassDir.'ModulePlugin.class.php');
	require_once($TBBclassDir.'SearchResult.class.php');
	require_once($TBBclassDir.'Text.class.php');

	require_once($ivLibDir.'Table.class.php');
	require_once($ivLibDir.'Menu.class.php');
	require_once($ivLibDir.'Form.class.php');
	require_once($ivLibDir.'FormFields.class.php');

	$boardID = 0;
	if (isSet($_POST['boardID'])) $boardID = $_POST['boardID'];
	if (isSet($_GET['boardID'])) $boardID = $_GET['boardID'];

	$pageTitle = $TBBconfiguration->getBoardName().' - '.'Zoeken';
	include($TBBincludeDir.'htmltop.php');
	include($TBBincludeDir.'usermenu.php');

	$searchPlugins = $TBBModuleManager->getPluginInfoType("search");
	$validTypes = array();
	for ($i = 0; $i < count($searchPlugins); $i++) {
		$validTypes[] = $searchPlugins[$i]->getValue("group");
	}

	$searchType = $validTypes[0];
	if (isSet($_GET['type'])) {
		if (in_Array($_GET['type'], $validTypes)) $searchType = $_GET['type'];
		else $feedback->addMessage('geen geldige zoeksoort: '.htmlConvert($_GET['type']));
	}
	if (isSet($_POST['type'])) {
		if (in_Array($_POST['type'], $validTypes)) $searchType = $_POST['type'];
		else $feedback->addMessage('geen geldige zoeksoort: '.htmlConvert($_POST['type']));
	}

	$searchPlugin = $TBBModuleManager->getPlugin($searchType, "search");
	if (!$searchPlugin->hasAccess($TBBcurrentUser)) {
		$feedback->addMessage('geen geldige zoeksoort: '.htmlConvert($searchPlugin->getModuleName()));
	}
	
	$wizzStep = 0; // step in the addgroup wizard.
	$searchState = 'form';

	if (isSet($_POST['actionName']) && isSet($_POST['actionID'])) {
		if (isSet($_POST['wizzStep']) && is_numeric($_POST['wizzStep'])) $wizzStep = $_POST['wizzStep'];
		if (($_POST['actionName'] == 'search') && ($_POST['actionID'] == $TBBsession->getActionID())) {
			$correct = true;

			$searchForm = new Form("search", "search.php");
			$searchForm->addHiddenField("actionID", $TBBsession->getActionID());
			$searchForm->addHiddenField("actionName", "search");
			$searchForm->addHiddenField("wizzStep", $wizzStep);
			$searchForm->addHiddenField("type", $searchType);
			$searchPlugin->buildSearchForm($searchForm, $wizzStep, $boardID);
						
			if ($searchPlugin->handleSearchForm($feedback, $searchForm, $wizzStep)) {
				if (!$searchPlugin->hasMoreSearchFormSteps($wizzStep+1)) {
					$wizzStep = 0;
					$TBBsession->actionHandled();
					$searchState = 'search';
				} else $wizzStep++;
			}
		}
	}
	if (isSet($_GET['result'])) {
		$searchState = 'result';
		$resultID = $_GET['result'];
		// Instead of searching, get the searchresult from the cache
		$searchResult = new SearchResult();
		if (!$searchResult->getCachedResult($resultID)) {
			$feedback->addMessage("Zoekresultaat ongeldig of niet teruggevonden. Probeer de zoekactie opnieuw");
			$searchState = 'form';
		}
	}

	if ($searchState == 'search') {
		// execute the search
		$searchResult = new SearchResult();
		if ($searchPlugin->executeSearch($searchResult, $feedback)) {
			if ($searchResult->getResultCount() > 0)
				$resultID = $searchResult->cacheInDatabase();
			$searchState = 'result';
		} else {
			$seachState = 'form';
		}
	}

	$feedback->showMessages();

	$here = new Location();
	$here->addLocation($TBBconfiguration->getBoardName(), 'index.php');
	$here->addLocation('Zoeken', 'search.php');
	$here->showLocation();
	$searchMenu = new Menu();
	for ($i = 0; $i < count($searchPlugins); $i++) {
		$someSearchPlugin = $TBBModuleManager->getPlugin($searchPlugins[$i]->getValue("group"), "search");
		if ($someSearchPlugin->hasAccess($TBBcurrentUser)) {
			$searchMenu->addItem($searchPlugins[$i]->getValue("group"), '', $someSearchPlugin->getSearchName(), '?type='.$searchPlugins[$i]->getValue("group"), '', '', 0, false, '');
		}
	}
	$searchMenu->itemIndex = $searchType;
	$searchMenu->showMenu('configMenu');

	if ($searchPlugin->hasAccess($TBBcurrentUser)) {

		if ($searchState == 'form') {
			$searchForm = new Form("search", "search.php");
			$searchForm->addHiddenField("actionID", $TBBsession->getActionID());
			$searchForm->addHiddenField("actionName", "search");
			$searchForm->addHiddenField("wizzStep", $wizzStep);
			$searchForm->addHiddenField("type", $searchType);
			$searchPlugin->buildSearchForm($searchForm, $wizzStep, $boardID);
			$searchForm->writeForm();
		} else
		if ($searchState == 'result') {
			$resultCount = $searchResult->getResultCount();
			$text = new Text();
			$text->addHTMLText(sprintf('Er %s %s %s gevonden<br /><a href="search.php?type=%s">Een nieuwe zoekactie starten</a>', ($resultCount == 1) ? "is" : "zijn", $resultCount, $searchResult->getSearchSubject(($resultCount == 1)), $searchType));
			$text->showText();

			$sortColumn = 0;
			if (isSet($_GET['sortColumn'])) $sortColumn = $_GET['sortColumn'];
			$sortType = 'none';
			if (isSet($_GET['sortType'])) $sortType = $_GET['sortType'];
			$pageLimit = 30;
			$pageNr = 0;
			if (isSet($_GET['pageNr'])) $pageNr = $_GET['pageNr'] -1;

			if ($resultCount > $pageLimit) {
				require_once($ivLibDir.'PageNavigation.class.php');
				$pageBar = new PageNavigation(ceil($resultCount / $pageLimit), ($pageNr+1),
					sprintf("search.php?pageNr=%%s&amp;result=%s&amp;sortType=%s&amp;sortColumn=%s&amp;type=%s", $resultID, $sortType, $sortColumn, $searchType), 10);
				$pageBar->showPagebar("searchPageBar");
			}
			if ($resultCount > 0) {
				$results = $searchResult->getResultTable(array("result=".$resultID, "type=".$searchType), $pageNr * $pageLimit, $pageLimit, $sortColumn, $sortType);
				$results->showTable();
			}

			if ($resultCount > $pageLimit) {
				$pageBar->showPagebar("searchPageBar");
			}

		}
	}

	writeJumpLocationField(-1, "search");

	include($TBBincludeDir.'htmlbottom.php');
?>
