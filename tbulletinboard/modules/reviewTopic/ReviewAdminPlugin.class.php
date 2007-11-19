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

	importClass("board.AdminPlugin");
	importClass("interface.Table");

	class ReviewAdminPlugin extends AdminPlugin {
		var $privateVars;

		function ReviewAdminPlugin() {
			$this->AdminPlugin();
		}

		function handlePageActions(&$feedback) {
		}

		function createMenu(&$menu) {
			$menu->addGroup("topics", "Onderwerpen");
			$menu->addItem("reviewAdmin", "topics", "Recenties instellen", "adminplugin?id=".$this->getModulename(), '', '', 0, false, '');
		}

		function selectMenuItem(&$menu) {
			$menu->itemIndex = "reviewAdmin";
		}

		function getLocation(&$location) {
			$location->addLocation("Recentie instellingen", "adminplugin.php?id=".$this->getModuleName());
		}

		function getPageTitle() {
			return "Recentie instellingen";
		}

		function getPage() {
			$moduleDir = $this->getModuleDir();

			require_once($moduleDir."ReviewTypes.bean.php");
			require_once($moduleDir."ReviewFields.bean.php");
			require_once($moduleDir."ReviewScore.bean.php");

			$moduleID = $this->getModuleName();

			$menu = new Menu();
			$menu->addItem("add", "", "Recentie type toevoegen", "javascript:popupWindow('popups/adminpluginpopup.php?id=".$moduleID."&amp;view=Edit', 600, 450, 'editReviewType', 1)", "", "", 0, false, '');
			$menu->addItem("edit", "", "Recentie type bewerken", "javascript:editReview()", "", "", 0, false, '');
			$menu->addItem("delete", "", "Recentie type verwijderen", "", "", "", 0, false, '');
			$menu->showMenu('toolbar');
			?>
			<script type="text/javascript"><!--
				var selectedType = -1;
				function selectType(id) {
					selectedType = id;
				}
				function editReview() {
					if (selectedType == -1) {
						alert('Geen recentie soort geselecteerd!');
						return;
					}
					popupWindow('popups/adminpluginpopup.php?id=<?=$moduleID; ?>&view=Edit&reviewTypeID='+selectedType, 600, 450, 'editReviewType', 1)
				}
			// -->
			</script>
			<?
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$reviewTypes = new ReviewTypesTable($database);
			$reviewTypes->selectAll();
			$fields = new ReviewFieldsTable($database);
			$score = new ReviewScoreTable($database);

			$table = new Table();
			$table->setHeader("typeID", "Recentie soort", "Aantal velden", "Scores");

			while ($reviewType = $reviewTypes->getRow()) {
				$dataFilter = new DataFilter();
				$dataFilter->addEquals("reviewType", $reviewType->getValue("ID"));

				$functions = new FunctionDescriptions();
				$functions->addCount('ID', 'fieldCount');
				$resultSet = $fields->executeDataFunction($functions, $dataFilter);
				$resultRow1 = $resultSet->getRow();

				$functions = new FunctionDescriptions();
				$functions->addCount('ID', 'scoreCount');
				$resultSet = $score->executeDataFunction($functions, $dataFilter);
				$resultRow2 = $resultSet->getRow();

				$table->addRow(
					$reviewType->getValue("ID"),
					$reviewType->getValue("name"),
					$resultRow1['fieldCount'],
					$resultRow2['scoreCount']
				);
			}
			$table->setClickColumn(0, "selectType", true);
			$table->showTable();
		}

		function handlePopupActions(&$feedback) {
			$view = "";
			if (isSet($_GET['view'])) $view = $_GET['view'];
			if (isSet($_POST['view'])) $view = $_POST['view'];
			if ($view == "Edit") $this->handleEditAction($feedback);
			if ($view == "editField") $this->handleEditFieldAction($feedback);
			if ($view == "editScore") $this->handleEditScoreAction($feedback);
		}

		function getPopupTitle() {
			$view = "";
			if (isSet($_GET['view'])) $view = $_GET['view'];
			if (isSet($_POST['view'])) $view = $_POST['view'];
			if ($view == 'Edit') {
				$reviewTypeID = -1;
				if (isSet($_GET['reviewTypeID'])) $reviewTypeID = $_GET['reviewTypeID'];
				if (isSet($_POST['reviewTypeID'])) $reviewTypeID = $_POST['reviewTypeID'];
				if ($reviewTypeID == -1) return "Recentie type toevoegen";
				return "Recentie type bewerken";
			}
			if ($view == 'Fields') {
				$reviewTypeID = -1;
				if (isSet($_GET['reviewTypeID'])) $reviewTypeID = $_GET['reviewTypeID'];
				if (isSet($_POST['reviewTypeID'])) $reviewTypeID = $_POST['reviewTypeID'];

				global $TBBconfiguration;
				$database = $TBBconfiguration->getDatabase();

				$moduleDir = $this->getModuleDir();
				require_once($moduleDir."ReviewTypes.bean.php");

				$reviewTypes = new ReviewTypesTable($database);
				$reviewType = $reviewTypes->getRowByKey($reviewTypeID);
				return 'Velden bewerken voor "'.$reviewType->getValue("name").'"';
			}
			if ($view == 'Scores') {
				$reviewTypeID = -1;
				if (isSet($_GET['reviewTypeID'])) $reviewTypeID = $_GET['reviewTypeID'];
				if (isSet($_POST['reviewTypeID'])) $reviewTypeID = $_POST['reviewTypeID'];

				$moduleDir = $this->getModuleDir();
				require_once($moduleDir."ReviewTypes.bean.php");

				global $TBBconfiguration;
				$database = $TBBconfiguration->getDatabase();
				$reviewTypes = new ReviewTypesTable($database);
				$reviewType = $reviewTypes->getRowByKey($reviewTypeID);
				return 'Score velden bewerken voor "'.$reviewType->getValue("name").'"';
			}
			if ($view == 'editField') {
				$reviewTypeID = -1;
				if (isSet($_GET['reviewTypeID'])) $reviewTypeID = $_GET['reviewTypeID'];
				if (isSet($_POST['reviewTypeID'])) $reviewTypeID = $_POST['reviewTypeID'];

				$moduleDir = $this->getModuleDir();
				require_once($moduleDir."ReviewTypes.bean.php");

				global $TBBconfiguration;
				$database = $TBBconfiguration->getDatabase();
				$reviewTypes = new ReviewTypesTable($database);
				$reviewType = $reviewTypes->getRowByKey($reviewTypeID);


				$fieldID = -1;
				if (isSet($_GET['fieldID'])) $fieldID = $_GET['fieldID'];
				if (isSet($_POST['fieldID'])) $fieldID = $_POST['fieldID'];
				if ($fieldID == -1) return 'Veld toevoegen aan "'.$reviewType->getValue("name").'"';
				return 'Veld bewerken van "'.$reviewType->getValue("name").'"';
			}
			if ($view == 'editScore') {
				$reviewTypeID = -1;
				if (isSet($_GET['reviewTypeID'])) $reviewTypeID = $_GET['reviewTypeID'];
				if (isSet($_POST['reviewTypeID'])) $reviewTypeID = $_POST['reviewTypeID'];

				$moduleDir = $this->getModuleDir();
				require_once($moduleDir."ReviewTypes.bean.php");

				global $TBBconfiguration;
				$database = $TBBconfiguration->getDatabase();
				$reviewTypes = new ReviewTypesTable($database);
				$reviewType = $reviewTypes->getRowByKey($reviewTypeID);
				$scoreID = -1;
				if (isSet($_GET['scoreID'])) $scoreID = $_GET['scoreID'];
				if (isSet($_POST['scoreID'])) $scoreID = $_POST['scoreID'];
				if ($scoreID == -1) return 'Scoreveld toevoegen aan "'.$reviewType->getValue("name").'"';
				return 'Scoreveld bewerken van "'.$reviewType->getValue("name").'"';
			}

			return "Onbekend venster!";
		}

		function getPopupPage() {
			$view = "";
			if (isSet($_GET['view'])) $view = $_GET['view'];
			if (isSet($_POST['view'])) $view = $_POST['view'];
			if ($view == 'Edit') {
				$this->showEditTypePopup();
			}
			if ($view == 'Fields') {
				$this->showFieldsPopup();
			}
			if ($view == 'editField') {
				$this->showEditFieldPopup();
			}
			if ($view == 'Scores') {
				$this->showScoresPopup();
			}
			if ($view == 'editScore') {
				$this->showEditScorePopup();
			}
		}

		function showEditTypePopup() {
			$moduleID = $this->getModuleName();
			global $TBBsession;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$reviewTypeID = -1;
			if (isSet($_GET['reviewTypeID'])) $reviewTypeID = $_GET['reviewTypeID'];
			if (isSet($_POST['reviewTypeID'])) $reviewTypeID = $_POST['reviewTypeID'];


			global $libraryClassDir;
			require_once($libraryClassDir . "Form.class.php");
			require_once($libraryClassDir . "FormFields.class.php");
			require_once($libraryClassDir . 'formcomponents/FloatField.class.php');

			$moduleDir = $this->getModuleDir();
			require_once($moduleDir."ReviewTypes.bean.php");

			$form = new Form("addType", "");
			$formFields = new StandardFormFields();
			$form->addFieldGroup($formFields);
			$formFields->activeForm = $form;

			$form->addHiddenField("id", $moduleID);
			$form->addHiddenField("view", "Edit");
			if ($reviewTypeID == -1) {
				$form->addHiddenField("action", "addReviewType");
				$formFields->startGroup("Recentie type toevoegen");
			} else {
				$form->addHiddenField("action", "editReviewType");
				$form->addHiddenField("reviewTypeID", $reviewTypeID);
				$formFields->startGroup("Recentie type bewerken");
			}
			$form->addHiddenField("actionID", $TBBsession->getActionID());
			$formFields->addTextField("reviewTypeName", "Naam", "naam van het type", 40, true, false);
			$formFields->addTextField("reviewScorePrefix", "Prefix", "prefix voor score", 10, true, false);
			$formFields->addTextField("reviewScorePostfix", "Postfix", "postfix voor score (bijv. %)", 10, true, false);
			$form->addComponent(new FormFloatField("reviewMaxScore", "Maximum score", "", 10));

			$formFields->endGroup();
			if ($reviewTypeID == -1) {
				$formFields->addSubmit("Toevoegen", false);
			} else {
				$formFields->addSubmit("Bewerken", false);
				$reviewTypes = new ReviewTypesTable($database);
				$reviewType = $reviewTypes->getRowByKey($reviewTypeID);
				$form->setValue("reviewTypeName", $reviewType->getValue("name"));
				$form->setValue("reviewScorePrefix", $reviewType->getValue("prefix"));
				$form->setValue("reviewScorePostfix", $reviewType->getValue("postfix"));
				$form->setValue("reviewMaxScore", $reviewType->getValue("maxValue"));

				$navMenu = $this->getEditReviewTypeMenu($reviewTypeID);

				$navMenu->itemIndex = "common";
				$navMenu->showMenu("configMenu");
			}

			$form->writeForm();
		}

		function getEditReviewTypeMenu($reviewID) {
			$moduleID = $this->getModuleName();

			global $libraryClassDir;
			require_once($libraryClassDir . "Menu.class.php");

			$navMenu = new Menu();
			$navMenu->addItem('common', '', 'Algemeen', '?view=Edit&amp;reviewTypeID='.$reviewID.'&amp;id='.$moduleID, '', '', 0, false, '');
			$navMenu->addItem('fields', '', 'Velden', '?view=Fields&amp;reviewTypeID='.$reviewID.'&amp;id='.$moduleID, '', '', 0, false, '');
			$navMenu->addItem('scores', '', 'Score velden', '?view=Scores&amp;reviewTypeID='.$reviewID.'&amp;id='.$moduleID, '', '', 0, false, '');
			return $navMenu;
		}

		function handleEditAction(&$feedback) {
			global $TBBsession;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			global $TBBclassDir;
			require_once($TBBclassDir."ActionHandler.class.php");
			$moduleDir = $this->getModuleDir();
			require_once($moduleDir."ReviewTypes.bean.php");

			if (isSet($_POST['actionID']) && ($_POST['actionID'] == $TBBsession->getActionID())) {
				if ($_POST['action'] == 'addReviewType') {
					$actionHandler = new ActionHandler($feedback, $_POST);
					$actionHandler->notEmpty("reviewTypeName", "Naam is verplicht!");
					$actionHandler->notEmpty("reviewMaxScore", "Maximum score is verplicht!");
					$actionHandler->isNumeric("reviewMaxScore", "Maximum score moet een kommagetal zijn!");

					$reviewTypes = new ReviewTypesTable($database);
					$newType = $reviewTypes->addRow();
					$newType->setValue("name", $_POST['reviewTypeName']);
					$newType->setValue("prefix", $_POST['reviewScorePrefix']);
					$newType->setValue("postfix", $_POST['reviewScorePostfix']);
					$newType->setValue("maxValue", $_POST['reviewMaxScore']);
					$newType->store();
					$actionHandler->finish("Nieuw recentie type toegevoegd");
					$_POST['reviewTypeID'] = $newType->getValue("ID");
					?>
					<script type="text/javascript"><!--
						window.opener.location.href = window.opener.location.href;
					// -->
					</script>
					<?
				}
				if ($_POST['action'] == 'editReviewType') {
					$actionHandler = new ActionHandler($feedback, $_POST);
					$actionHandler->notEmpty("reviewTypeName", "Naam is verplicht!");
					$actionHandler->notEmpty("reviewMaxScore", "Maximum score is verplicht!");
					$actionHandler->isNumeric("reviewMaxScore", "Maximum score moet een kommagetal zijn!");

					$reviewTypes = new ReviewTypesTable($database);
					$newType = $reviewTypes->getRowByKey($_POST['reviewTypeID']);
					$newType->setValue("name", $_POST['reviewTypeName']);
					$newType->setValue("prefix", $_POST['reviewScorePrefix']);
					$newType->setValue("postfix", $_POST['reviewScorePostfix']);
					$newType->setValue("maxValue", $_POST['reviewMaxScore']);
					$newType->store();
					$actionHandler->finish("Recentie type bewerkt");
					?>
					<script type="text/javascript"><!--
						window.opener.location.href = window.opener.location.href;
					// -->
					</script>
					<?
				}
			}
		}

		function showFieldsPopup() {
			global $TBBsession;
			global $TBBconfiguration;
			$moduleID = $this->getModuleName();

			global $TBBclassDir;
			require_once($TBBclassDir."Text.class.php");

			$moduleDir = $this->getModuleDir();
			require_once($moduleDir."ReviewFields.bean.php");

			$database = $TBBconfiguration->getDatabase();
			$reviewTypeID = -1;
			if (isSet($_GET['reviewTypeID'])) $reviewTypeID = $_GET['reviewTypeID'];
			if (isSet($_POST['reviewTypeID'])) $reviewTypeID = $_POST['reviewTypeID'];

			$navMenu = $this->getEditReviewTypeMenu($reviewTypeID);
			$navMenu->itemIndex = "fields";
			$navMenu->showMenu("configMenu");

			$menu = new Menu();
			$menu->addItem("add", "", "Veld toevoegen", '?view=editField&amp;reviewTypeID='.$reviewTypeID.'&amp;id='.$moduleID, "", "", 0, false, '');
			$menu->addItem("edit", "", "Veld bewerken", "javascript:editField()", "", "", 0, false, '');
			$menu->addItem("delete", "", "Veld verwijderen", "", "", "", 0, false, '');
			$menu->showMenu('toolbar');

			$explain = new Text();
			$explain->addHTMLText("De vaste eigenschappen van het item");
			$explain->showText();
			?>
			<script type="text/javascript"><!--
				var selectedField = -1;
				function selectField(id) {
					selectedField = id;
				}

				function editField() {
					if (selectedField == -1) {
						alert('Geen veld geselecteerd!');
						return;
					}
					document.location.href='?view=editField&reviewTypeID=<?=$reviewTypeID; ?>&id=<?=$moduleID ?>&fieldID='+selectedField;
				}
			// -->
			</script>
			<?
			$fieldsTable = new ReviewFieldsTable($database);
			$filter = new DataFilter();
			$filter->addEquals("reviewType", $reviewTypeID);

			$table = new Table();
			$table->setHeader("fieldID", "Naam", "Type");

			$fieldsTable->selectRows($filter, new ColumnSorting());
			while ($field = $fieldsTable->getRow()) {
				$table->addRow(
					$field->getValue("ID"),
					$field->getValue("name"),
					$this->getTypeName($field->getValue("type"))
				);
			}
			$table->setClickColumn(0, "selectField", true);
			$table->showTable();
		}

		function getTypeName($type) {
			switch($type) {
				case "text": return "Tekst";
				case "number": return "Getal";
				case "select": return "Lijst";
				case "time": return "Tijd";
				case "float": return "Kommagetal";
				case "date": return "Datum";
			}
			return "";
		}

		function showEditFieldPopup() {
			$moduleID = $this->getModuleName();
			global $TBBsession;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			global $libraryClassDir;
			require_once($libraryClassDir . "Form.class.php");
			require_once($libraryClassDir . "FormFields.class.php");
			$moduleDir = $this->getModuleDir();
			require_once($moduleDir."ReviewFields.bean.php");

			$fieldID = -1;
			if (isSet($_GET['fieldID'])) $fieldID = $_GET['fieldID'];
			if (isSet($_POST['fieldID'])) $fieldID = $_POST['fieldID'];
			$reviewTypeID = -1;
			if (isSet($_GET['reviewTypeID'])) $reviewTypeID = $_GET['reviewTypeID'];
			if (isSet($_POST['reviewTypeID'])) $reviewTypeID = $_POST['reviewTypeID'];

			$form = new Form("addType", "");
			$formFields = new StandardFormFields();
			$form->addFieldGroup($formFields);
			$formFields->activeForm = $form;

			$form->addHiddenField("id", $moduleID);
			$form->addHiddenField("view", "editField");
			$form->addHiddenField("reviewTypeID", $reviewTypeID);
			if ($fieldID == -1) {
				$form->addHiddenField("action", "addField");
				$formFields->startGroup("Veld toevoegen");
			} else {
				$form->addHiddenField("action", "editField");
				$form->addHiddenField("fieldID", $fieldID);
				$formFields->startGroup("Veld bewerken");
			}
			$form->addHiddenField("actionID", $TBBsession->getActionID());
			$formFields->addTextField("fieldName", "Naam", "naam van het veld", 40, true, false);
			$formFields->addTextField("fieldPre", "Prefix", "eenheid, bijv &euro;", 10, false, false);
			$options = array(
				array("value" => "text", "caption" => "Tekst", "description" => "", "show" => array(), "hide" => array("listValues")),
				array("value" => "number", "caption" => "Getal", "description" => "voor gehele aantallen", "show" => array(), "hide" => array("listValues")),
				array("value" => "float", "caption" => "Kommagetal", "description" => "bijv. valuta", "show" => array(), "hide" => array("listValues")),
				array("value" => "date", "caption" => "Datum", "description" => "bijv. releasedate", "show" => array(), "hide" => array("listValues")),
				array("value" => "time", "caption" => "Tijd", "description" => "bijv. tijdsduur", "show" => array(), "hide" => array("listValues")),
				array("value" => "select", "caption" => "Lijst", "description" => "lijst met voorgedefinieerde waarden", "show" => array("listValues"), "hide" => array())
			);
			$formFields->addRadioViewHide("fieldType", "Type", "soort veld", $options, "text");
			$form->startMarking("listValues");
			$formFields->addMultifield ("fieldListValues", "Waarden", "waaruit gekozen kan worden", "|");
			$form->endMarking();

			$formFields->addTextField("fieldPost", "Postfix", "eenheid, bijv kg.", 10, false, false);

			$formFields->endGroup();
			if ($fieldID == -1) {
				$formFields->addSubmit("Toevoegen", true);
			} else {
				$formFields->addSubmit("Bewerken", true);
				$fieldsTable = new ReviewFieldsTable($database);
				$field = $fieldsTable->getRowByKey($fieldID);
				$form->setValue("fieldName", $field->getValue("name"));
				$form->setValue("fieldPre", $field->getValue("prefix"));
				$form->setValue("fieldPost", $field->getValue("postfix"));
				$form->setValue("fieldType", $field->getValue("type"));

				if ($field->getValue("type") == "select") {
					$filter = new DataFilter();
					$filter->addEquals("fieldID", $field->getValue("ID"));
					$fieldValueTable = new ReviewFieldValuesTable($database);
					$fieldValueTable->selectRows($filter, new ColumnSorting());
					$values = array();
					while($fieldValue = $fieldValueTable->getRow()) {
						$values[] = $fieldValue->getValue("value");
					}
					$form->setValue("fieldListValues", implode("|", $values));
				}
			}

			$form->writeForm();
		}

		function handleEditFieldAction(&$feedback) {
			$moduleID = $this->getModuleName();
			global $TBBsession;
			global $TBBconfiguration;

			global $TBBclassDir;
			require_once($TBBclassDir . "ActionHandler.class.php");
			$moduleDir = $this->getModuleDir();
			require_once($moduleDir."ReviewFields.bean.php");
			require_once($moduleDir."ReviewFieldValues.bean.php");

			$database = $TBBconfiguration->getDatabase();
			if (isSet($_POST['actionID']) && ($_POST['actionID'] == $TBBsession->getActionID())) {
				if ($_POST['action'] == 'addField') {
					$actionHandler = new ActionHandler($feedback, $_POST);
					$actionHandler->notEmpty("fieldName", "Naam is verplicht!");
					if ($_POST['fieldType'] == "select")
						$actionHandler->notEmpty("fieldListValues", "Er dienen veldwaarden opgegeven te zijn!");

					if ($actionHandler->correct) {
						$reviewTypeID = -1;
						if (isSet($_GET['reviewTypeID'])) $reviewTypeID = $_GET['reviewTypeID'];
						if (isSet($_POST['reviewTypeID'])) $reviewTypeID = $_POST['reviewTypeID'];

						$reviewFields = new ReviewFieldsTable($database);
						$newField = $reviewFields->addRow();
						$newField->setValue("reviewType", $reviewTypeID);
						$newField->setValue("name", $_POST['fieldName']);
						$newField->setValue("prefix", $_POST['fieldPre']);
						$newField->setValue("postfix", $_POST['fieldPost']);
						$newField->setValue("type", $_POST['fieldType']);
						$newField->store();

						$valueTable = new ReviewFieldValuesTable($database);
						if ($_POST['fieldType'] == "select") {
							// store the defined values
							$values = explode("|", $_POST["fieldListValues"]);
							for ($i = 0; $i < count($values); $i++) {
								$newValue = $valueTable->addRow();
								$newValue->setValue("fieldID", $newField->getValue("ID"));
								$newValue->setValue("value", $values[$i]);
								$newValue->store();
							}
						}
						$_POST['fieldID'] = $newField->getValue("ID");
					}
					if ($actionHandler->correct) {
					?>
					<script type="text/javascript"><!--
						document.location.href = '?id=<?=$moduleID; ?>&view=Fields&reviewTypeID=<?=$_POST['reviewTypeID'] ?>';
					// -->
					</script>
					<?
					}
					$actionHandler->finish("Nieuw veld toegevoegd");
				}
				if ($_POST['action'] == 'editField') {
					$actionHandler = new ActionHandler($feedback, $_POST);
					$actionHandler->notEmpty("fieldName", "Naam is verplicht!");
					if ($_POST['fieldType'] == "select")
						$actionHandler->notEmpty("fieldListValues", "Er dienen veldwaarden opgegeven te zijn!");

					if ($actionHandler->correct) {
						$reviewTypeID = -1;
						if (isSet($_GET['reviewTypeID'])) $reviewTypeID = $_GET['reviewTypeID'];
						if (isSet($_POST['reviewTypeID'])) $reviewTypeID = $_POST['reviewTypeID'];

						$reviewFields = new ReviewFieldsTable($database);
						$newField = $reviewFields->getRowByKey($_POST['fieldID']);
						$newField->setValue("reviewType", $reviewTypeID);
						$newField->setValue("name", $_POST['fieldName']);
						$newField->setValue("prefix", $_POST['fieldPre']);
						$newField->setValue("postfix", $_POST['fieldPost']);
						$newField->setValue("type", $_POST['fieldType']);
						$newField->store();

						$valueTable = new ReviewFieldValuesTable($database);

						$filter = new DataFilter();
						$filter->addEquals("fieldID", $_POST['fieldID']);
						$fieldValueTable = new ReviewFieldValuesTable($database);
						$fieldValueTable->selectRows($filter, new ColumnSorting());
						if ($_POST['fieldType'] == "select")
							$values = explode("|", $_POST["fieldListValues"]);
						else
							$values = array();

						$newArray = array();
						for ($i = 0; $i < count($values); $i++) {
							$newArray[$values[$i]] = "new";
						}
						while ($fieldValue = $fieldValueTable->getRow()) {
							if ( !in_array($fieldValue->getValue("value"), $values)) {
								$fieldValue->delete();
							} else {
								$newArray[$fieldValue->getValue("value")] = "exists";
							}
						}
						for ($i = 0; $i < count($values); $i++) {
							if ($newArray[$values[$i]] == "new") {
								$newValue = $fieldValueTable->addRow();
								$newValue->setValue("fieldID", $_POST['fieldID']);
								$newValue->setValue("value", $values[$i]);
								$newValue->store();
							}
						}
						$_POST['fieldID'] = $newField->getValue("ID");
					}
					if ($actionHandler->correct) {
					?>
					<script type="text/javascript"><!--
						document.location.href = '?id=<?=$moduleID; ?>&view=Fields&reviewTypeID=<?=$_POST['reviewTypeID'] ?>';
					// -->
					</script>
					<?
					}
					$actionHandler->finish("Veld bewerkt");
				}
			}
		}

		function showScoresPopup() {
			$moduleID = $this->getModuleName();
			global $TBBsession;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$reviewTypeID = -1;
			if (isSet($_GET['reviewTypeID'])) $reviewTypeID = $_GET['reviewTypeID'];
			if (isSet($_POST['reviewTypeID'])) $reviewTypeID = $_POST['reviewTypeID'];

			global $TBBclassDir;
			require_once($TBBclassDir."Text.class.php");

			$moduleDir = $this->getModuleDir();
			require_once($moduleDir."ReviewScore.bean.php");

			$navMenu = $this->getEditReviewTypeMenu($reviewTypeID);
			$navMenu->itemIndex = "scores";
			$navMenu->showMenu("configMenu");

			$menu = new Menu();
			$menu->addItem("add", "", "Veld toevoegen", '?view=editScore&amp;reviewTypeID='.$reviewTypeID.'&amp;id='.$moduleID, "", "", 0, false, '');
			$menu->addItem("edit", "", "Veld bewerken", "javascript:editScore()", "", "", 0, false, '');
			$menu->addItem("delete", "", "Veld verwijderen", "", "", "", 0, false, '');
			$menu->showMenu('toolbar');

			$explain = new Text();
			$explain->addHTMLText("De onderdelen waarop het item beoordeeld gaat worden");
			$explain->showText();

			?>
			<script type="text/javascript"><!--
				var selectedScore = -1;
				function selectScore(id) {
					selectedScore = id;
				}

				function editScore() {
					if (selectedScore == -1) {
						alert('Geen scoreveld geselecteerd!');
						return;
					}
					document.location.href='?view=editScore&reviewTypeID=<?=$reviewTypeID; ?>&id=<?=$moduleID ?>&scoreID='+selectedScore;
				}
			// -->
			</script>
			<?
			$scoreTable = new ReviewScoreTable($database);
			$filter = new DataFilter();
			$filter->addEquals("reviewType", $reviewTypeID);

			$table = new Table();
			$table->setHeader("fieldID", "Naam", "Max");

			$scoreTable->selectRows($filter, new ColumnSorting());
			while ($score = $scoreTable->getRow()) {
				$table->addRow(
					$score->getValue("ID"),
					$score->getValue("name"),
					$score->getValue("maxScore")
				);
			}
			$table->setClickColumn(0, "selectScore", true);
			$table->showTable();
		}

		function showEditScorePopup() {
			$moduleID = $this->getModuleName();
			global $TBBsession;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			global $libraryClassDir;
			require_once($libraryClassDir."Form.class.php");
			require_once($libraryClassDir."FormFields.class.php");
			$moduleDir = $this->getModuleDir();
			require_once($moduleDir."ReviewScore.bean.php");

			$scoreID = -1;
			if (isSet($_GET['scoreID'])) $scoreID = $_GET['scoreID'];
			if (isSet($_POST['scoreID'])) $scoreID = $_POST['scoreID'];
			$reviewTypeID = -1;
			if (isSet($_GET['reviewTypeID'])) $reviewTypeID = $_GET['reviewTypeID'];
			if (isSet($_POST['reviewTypeID'])) $reviewTypeID = $_POST['reviewTypeID'];

			$form = new Form("addScore", "");
			$formFields = new StandardFormFields();
			$form->addFieldGroup($formFields);
			$formFields->activeForm = $form;

			$form->addHiddenField("id", $moduleID);
			$form->addHiddenField("view", "editScore");
			$form->addHiddenField("reviewTypeID", $reviewTypeID);
			if ($scoreID == -1) {
				$form->addHiddenField("action", "addScore");
				$formFields->startGroup("Score veld toevoegen");
			} else {
				$form->addHiddenField("action", "editScore");
				$form->addHiddenField("scoreID", $scoreID);
				$formFields->startGroup("Score veld bewerken");
			}
			$form->addHiddenField("actionID", $TBBsession->getActionID());
			$formFields->addTextField("scoreName", "Naam", "naam van het veld", 40, true, false);
			$formFields->addTextField("scoreMax", "Maximale score", "bijv. 65/100. 100 is dan de max.", 4, true, false);
			if ($scoreID == -1) {
				$formFields->addSubmit("Toevoegen", true);
			} else {
				$formFields->addSubmit("Bewerken", true);
				$scoreTable = new ReviewScoreTable($database);
				$score = $scoreTable->getRowByKey($scoreID);
				$form->setValue("scoreName", $score->getValue("name"));
				$form->setValue("scoreMax", $score->getValue("maxScore"));
			}

			$form->writeForm();
		}

		function handleEditScoreAction(&$feedback) {
			$moduleID = $this->getModuleName();
			global $TBBsession;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			global $TBBclassDir;
			require_once($TBBclassDir . "ActionHandler.class.php");
			$moduleDir = $this->getModuleDir();
			require_once($moduleDir."ReviewScore.bean.php");

			if (isSet($_POST['actionID']) && ($_POST['actionID'] == $TBBsession->getActionID())) {
				if ($_POST['action'] == 'addScore') {
					$actionHandler = new ActionHandler($feedback, $_POST);
					$actionHandler->notEmpty("scoreName", "Naam is verplicht!");
					$actionHandler->isNumeric("scoreMax", "Max moet een geheel getal zijn!");
					if ($actionHandler->correct) {
						$reviewTypeID = -1;
						if (isSet($_GET['reviewTypeID'])) $reviewTypeID = $_GET['reviewTypeID'];
						if (isSet($_POST['reviewTypeID'])) $reviewTypeID = $_POST['reviewTypeID'];

						$reviewScore = new ReviewScoreTable($database);
						$newScore = $reviewScore->addRow();
						$newScore->setValue("reviewType", $reviewTypeID);
						$newScore->setValue("name", $_POST['scoreName']);
						$newScore->setValue("maxScore", $_POST['scoreMax']);
						$newScore->store();

						$_POST['scoreID'] = $newScore->getValue("ID");
					}
					if ($actionHandler->correct) {
					?>
					<script type="text/javascript"><!--
						document.location.href = '?id=<?=$moduleID; ?>&view=Scores&reviewTypeID=<?=$_POST['reviewTypeID'] ?>';
					// -->
					</script>
					<?
					}
					$actionHandler->finish("Nieuw scoreveld toegevoegd");
				}
				if ($_POST['action'] == 'editScore') {
					$actionHandler = new ActionHandler($feedback, $_POST);
					$actionHandler->notEmpty("scoreName", "Naam is verplicht!");
					$actionHandler->isNumeric("scoreMax", "Max moet een geheel getal zijn!");

					if ($actionHandler->correct) {
						$reviewTypeID = -1;
						if (isSet($_GET['reviewTypeID'])) $reviewTypeID = $_GET['reviewTypeID'];
						if (isSet($_POST['reviewTypeID'])) $reviewTypeID = $_POST['reviewTypeID'];

						$reviewScore = new ReviewScoreTable($database);
						$newScore = $reviewScore->getRowByKey($_POST['scoreID']);
						$newScore->setValue("reviewType", $reviewTypeID);
						$newScore->setValue("name", $_POST['scoreName']);
						$newScore->setValue("maxScore", $_POST['scoreMax']);
						$newScore->store();

						$_POST['scoreID'] = $newScore->getValue("ID");
					}
					if ($actionHandler->correct) {
					?>
					<script type="text/javascript"><!--
						document.location.href = '?id=<?=$moduleID; ?>&view=Scores&reviewTypeID=<?=$_POST['reviewTypeID'] ?>';
					// -->
					</script>
					<?
					}
					$actionHandler->finish("Veld bewerkt");
				}
			}
		}

	}

?>
