<?php
	//global $ivLibDir;
	//require_once($ivLibDir . "FormFields.class.php");
	global $TBBclassDir;
	require_once($TBBclassDir . "SearchPlugin.class.php");
	require_once($TBBclassDir . "BoardFormFields.class.php");
	require_once($TBBclassDir . "User.bean.php");

	class MemberSearchPlugin extends SearchPlugin {
		
		private $userAccessCache = Array();
		private $userTravianCache = Array();

		function MemberSearchPlugin() {
			$this->SearchPlugin();
		}

		function getSearchName() {
			return "Leden";
		}

		function hasAccess(&$user) {
			return true;
		}

		function buildSearchForm(&$form, $step, $boardID) {
			global $formTitleTemplate;
			includeFormComponents("TextField", "TemplateField", "Submit");
			$form->addComponent(new FormTemplateField($formTitleTemplate, "Lid zoeken"));
			$form->addComponent(new FormTextField("name", "Nick", "", 255, true));
			$form->addComponent(new FormSubmit("Zoeken", "", "", "searchButton"));
			
		}

		function hasMoreSearchFormSteps($wizzStep) {
			return ($wizzStep < 1);
		}

		function handleSearchForm(&$feedback, &$form, $step) {
			return $form->checkPostedFields($feedback);
		}

		function executeSearch(&$searchResult, &$feedback) {
			$searchResult->setSearchSubject("lid", "leden");
			
			$searchResult->defineColumnNames("Naam", "Laatst gezien", "Lid sinds", "Berichten");
			$searchResult->defineColumnTypes("text", "number", "number", "number");
			$searchResult->defineSortColumns(0, 1, 2, 3);
			
			$moduleDir = $this->getModuleDir();
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$userTable = new UserTable($database);
			
			$searchFilter = new DataFilter();
			if (isSet($_POST['name']) && ($_POST['name'] != "")) {
				$searchFilter->addLike("nickname", "%".$_POST['name']."%");
			}
			
			$sorting = new ColumnSorting();
			$sorting->addColumnSort("nickname", false);
			
			$userTable->selectRows($searchFilter, $sorting);
			$i = 0;			
			while ($resultRow = $userTable->getRow()) {			
				$searchResult->addResultRow(50.0 + (0.1 * $i),
					$resultRow->getValue("nickname"), 
					sprintf('<a href="user.php?id=%s">%s</a>', $resultRow->getValue("ID"), $resultRow->getValue("nickname")), 
					
					$resultRow->getValue("lastSeen")->getTimestamp(),
					$TBBconfiguration->parseDate($resultRow->getValue("lastSeen")), 
					$resultRow->getValue("date")->getTimestamp(),
					$TBBconfiguration->parseDate($resultRow->getValue("date")),

					$resultRow->getValue("posts") + $resultRow->getValue("topic"), 
					$resultRow->getValue("posts") + $resultRow->getValue("topic")
				);
				$i++;
			}

			return true;
		}

	}

?>
