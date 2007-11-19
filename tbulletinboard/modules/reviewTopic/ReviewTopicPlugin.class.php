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

	importClass("util.PageNavigation");
	importClass("util.TextParser");
	importClass("interface.Table");
	importClass("interface.Form");
	importClass("interface.FormFields");
	importClass("interface.Menu");
	importClass("orm.DataObjects");
	importClass("board.Board");
	importBean("board.Topic");
	importBean("board.Reaction");
	importClass("board.Buttonbar");
	importClass("board.BoardFormFields");
	importClass("board.TBBEmoticonList");
	importClass("board.TopicPlugin");
	importClass("board.ActionHandler");

	require_once($moduleDir . 'ReviewTypes.bean.php');
	require_once($moduleDir . 'ReviewTopic.bean.php');
	require_once($moduleDir . 'ReviewTopicFields.bean.php');
	require_once($moduleDir . 'ReviewTopicScore.bean.php');
	require_once($moduleDir . 'ReviewReaction.bean.php');
	require_once($moduleDir . 'ReviewFields.bean.php');
	require_once($moduleDir . 'ReviewFieldValues.bean.php');
	require_once($moduleDir . 'ReviewReactionScore.bean.php');
	require_once($moduleDir . 'ReviewReaction.class.php');
	require_once($moduleDir . 'ReviewTopic.class.php');

	/*
	global $libraryClassDir;
	global $TBBclassDir;

	require_once($libraryClassDir . 'PageNavigation.class.php');
	require_once($libraryClassDir . 'TextParser.class.php');
	require_once($libraryClassDir . 'Table.class.php');
	require_once($libraryClassDir . 'Form.class.php');
	require_once($libraryClassDir . 'FormFields.class.php');
	require_once($libraryClassDir . 'Menu.class.php');
	require_once($libraryClassDir . 'DataObjects.class.php');

	require_once($libraryClassDir . 'formcomponents/FloatField.class.php');
	require_once($libraryClassDir . 'formcomponents/NumberField.class.php');
	require_once($libraryClassDir . 'formcomponents/TextField.class.php');
	require_once($libraryClassDir . 'formcomponents/Date.class.php');
	require_once($libraryClassDir . 'formcomponents/Time.class.php');

	require_once($TBBclassDir . 'ActionHandler.class.php');
	require_once($TBBclassDir . 'TopicPlugin.class.php');
	require_once($TBBclassDir . 'Board.class.php');
	require_once($TBBclassDir . 'Topic.bean.php');
	require_once($TBBclassDir . 'Reaction.bean.php');
	require_once($TBBclassDir . 'Buttonbar.class.php');
	require_once($TBBclassDir . 'BoardFormFields.class.php');
	require_once($TBBclassDir . 'TBBEmoticonList.class.php');
	*/

	class ReviewTopicPlugin extends TopicPlugin {

		function ReviewTopicPlugin() {
			$this->TopicPlugin();
		}

		function getSelectionName() {
			return "Recentie";
		}

		function setAddTopicForm(&$form, $currentStep, &$board) {
			if ($currentStep > 1) return; // no forms for that
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			if ($currentStep == 0) {
				$formFields = new StandardFormFields();
				$form->addFieldGroup($formFields);
				$formFields->activeForm = $form;
				$formFields->startGroup("Nieuwe recentie starten");

				$options = array();
				$reviewTypes = new ReviewTypesTable($database);
				$reviewTypes->selectAll();
				while ($reviewType = $reviewTypes->getRow()) {
					$options[$reviewType->getValue("ID")] = $reviewType->getValue("name");
				}
				$formFields->addSelect("reviewType", "Soort recentie", "", $options, "");

				$formFields->endGroup();
				$formFields->addSubmit("Verder &raquo;", false);
			}
			if ($currentStep == 1) {
				$reviewTypeID = $_POST['reviewType'];

				$formFields = new StandardFormFields();
				$form->addFieldGroup($formFields);
				$formFields->activeForm = $form;
				$reviewType = $this->getReviewType($reviewTypeID);
				$formFields->startGroup("Nieuwe ".$reviewType->getValue("name")." recentie starten");
				$form->addHiddenField("reviewType", $reviewTypeID);

				$this->createReviewTopicForm($form, $reviewTypeID);
				$formFields->endGroup();
				$formFields->addSubmit("Plaatsen", false);
			}
		}

		function createReviewTopicForm(&$form, $reviewTypeID) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$reviewType = $this->getReviewType($reviewTypeID);
			$formFields = new StandardFormFields();
			$form->addFieldGroup($formFields);
			$formFields->activeForm = $form;

			$boardFormFields = new BoardFormFields();
			$form->addFieldGroup($boardFormFields);
			$boardFormFields->activeForm = $form;

			$formFields->addTextField("title", "Titel", "Titel van recentie", 80, true, false);
			$boardFormFields->addIconBar("icon", "Pictogram", "pictogram van het onderwerp");
			// Maak de formFields voor de reviewvelden
			$reviewFields = new ReviewFieldsTable($database);
			$reviewFieldValues = new ReviewFieldValuesTable($database);

			$fields = $this->readReviewFields($reviewTypeID);
			reset($fields);
			while (list($key, $value) = each($fields)) {
				$fieldType = $value['type'];
				switch ($fieldType) {
					case "time":
						$form->addComponent(new FormTime($value["name"], "tijd", "field".$key, false, false, $value["prefix"], $value["postfix"]));
						break;
					case "date":
						$form->addComponent(new FormDate($value["name"], "datum", "field".$key, false, false, $value["prefix"], $value["postfix"]));
						break;
					case "text":
						$form->addComponent(new FormTextField("field".$key, $value["name"], "tekst", 40, false, false, $value["prefix"], $value["postfix"]));
						break;
					case "number":
						$form->addComponent(new FormNumberField("field".$key, $value["name"], "geheelgetal", 10, false, false, $value["prefix"], $value["postfix"]));
						break;
					case "float":
						$form->addComponent(new FormFloatField("field".$key, $value["name"], "kommagetal", 10, false, false, $value["prefix"], $value["postfix"]));
						break;
					case "select":
						$dataFilter = new DataFilter();
						$dataFilter->addEquals("fieldID", $key);

						$reviewFieldValues->selectRows($dataFilter, new ColumnSorting());
						$options = array();
						while ($fieldValue = $reviewFieldValues->getRow()) {
							$options[$fieldValue->getValue("value")] = $fieldValue->getValue("value");
						}
						$formFields->addSelect("field".$key, $value["name"], "keuzelijst", $options, "");
						break;
				}
			}

			$boardFormFields->addPostTextField("post", "Recentie tekst", "Bespreek hier de recentie", true, true, true, true);

			$formFields->endGroup();
			$formFields->startGroup("Beoordeling");

			$scores = $this->readReviewScores($reviewTypeID);
			reset($scores);
			while (list($key, $value) = each($scores)) {
				$form->addComponent(new FormNumberField("score".$key, $value["name"], "", 5, true, false, "", "/ ".$value["maxScore"]));
			}
			$form->addComponent(new FormFloatField("finalScore", "Eindbeoordeling", "Max. ".$reviewType->getValue("prefix").$reviewType->getValue("maxValue").$reviewType->getValue("postfix"), 5, true, false, $reviewType->getValue("prefix"), $reviewType->getValue("postfix")));

			$options = array(
				array("value" => "yes", "caption" => "Maak URLs automatisch", "description" => "zet automatisch [url] en [/url] om een internet adres", "name" => "autoUrl", "checked" => true),
				array("value" => "yes", "caption" => "Geen smiles in dit bericht", "description" => "", "name" => "noSmile", "checked" => false),
				array("value" => "yes", "caption" => "Toon handtekening", "description" => "zet je handtekening onder dit bericht", "name" => "signature", "checked" => true)
				//array("value" => "yes", "caption" => "Bericht bij reacties", "description" => "reacties op dit bericht per email ontvangen", "name" => "subscribe", "checked" => false),
				//array("value" => "yes", "caption" => "Opslaan als concept", "description" => "Bericht niet plaatsen maar opslaan als concept", "name" => "concept", "checked" => false)
			);
			$formFields->addCheckboxes("Opties", "", $options);
		}

		function handleAddTopicAction(&$feedback, &$board) {
			if ($_POST['wizzStep'] == 1) return true;

			if ($_POST['wizzStep'] == 2) {
				global $TBBcurrentUser;
				global $TBBconfiguration;
				$database = $TBBconfiguration->getDatabase();

				$reviewType = $this->getReviewType($_POST['reviewType']);
				// Check the input
				$form = new Form("newTopic", "addtopic.php");
				$this->createReviewTopicForm($form, $_POST['reviewType']);
				if (!$form->checkPostedFields($feedback)) return false;
				if ($_POST['finalScore'] > $reviewType->getValue("maxValue")) {
					$feedback->addMessage("Maximale waarde van Eindbeoordeling is " . $reviewType->getValue("prefix") . $reviewType->getValue("maxValue") . $reviewType->getValue("postfix"));
					return false;
				}
				$scores = $this->readReviewScores($_POST['reviewType']);
				reset($scores);
				while (list($key, $value) = each($scores)) {
					$component = $form->getComponentByIdentifier("score".$key);
					if ($component->hasValue()) {
						$typevalue = $component->getValue();
						if ($typevalue > $value["maxScore"]) {
							$feedback->addMessage("Maximale waarde van ".$component->title." is ".$value["maxScore"]);
							return false;
						}
					}
				}
				$state = "online";
				$closed = "no";
				$special = "no";

				$topicID = $board->addTopic($TBBcurrentUser, trim($_POST['title']), $_POST['icon'], $this->getModuleName(), $state, $closed, $special);
				if ($topicID == false) {
					$feedback->addMessage("Fout bij plaatsen van onderwerp in database!");
					return false;
				}
				$parseUrls = isSet($_POST['autoUrl']) ? true : false;
				$smilies = isSet($_POST['noSmile']) ? false : true;
				$signature = isSet($_POST['signature']) ? true : false;

				$discTopicTable = new ReviewTopicTable($database);
				$newTopic = $discTopicTable->addRow();
				$newTopic->setValue("signature", $signature);
				$newTopic->setValue("smileys", $smilies);
				$newTopic->setValue("parseUrls", $parseUrls);
				$newTopic->setValue("message", $_POST['post']);
				$newTopic->setValue("reviewType", $_POST['reviewType']);
				$newTopic->setValue("score", $_POST['finalScore']);
				$newTopic->setNull("userScore");
				$newTopic->setNull("lastChange");
				$newTopic->setNull("changeBy");
				$newTopic->setValue("ID", $topicID);
				$newTopic->store();

				// Store the review fields
				$topicFields = new ReviewTopicFieldsTable($database);
				/*
				$reviewFields = new ReviewFieldsTable($database);
				$dataFilter = new DataFilter();
				$dataFilter->addEquals("reviewType", $_POST['reviewType']);
				$reviewFields->selectRows($dataFilter, new ColumnSorting());
				while ($field = $reviewFields->getRow()) {
				*/
				$fields = $this->readReviewFields($_POST['reviewType']);
				reset($fields);
				while (list($key, $value) = each($fields)) {
					$fieldType = $value['type'];

					//$fieldType = $field->getValue("type");
					switch ($fieldType) {
						case "time":
							$component = $form->getComponentByIdentifier("field".$key);
							if ($component->hasValue()) {
								$newField = $topicFields->addRow();
								$newField->setValue("topicID", $topicID);
								$newField->setValue("fieldID", $key);
								$newField->setValue("timeValue", $component->getTimestamp());
								$newField->store();
							}
							break;
						case "date":
							$component = $form->getComponentByIdentifier("field".$key);
							if ($component->hasValue()) {
								$newField = $topicFields->addRow();
								$newField->setValue("topicID", $topicID);
								$newField->setValue("fieldID", $key);
								$newField->setValue("dateValue", $component->getTimestamp());
								$newField->store();
							}
							break;
						case "text":
							$component = $form->getComponentByIdentifier("field".$key);
							if ($component->hasValue()) {
								$newField = $topicFields->addRow();
								$newField->setValue("topicID", $topicID);
								$newField->setValue("fieldID", $key);
								$newField->setValue("textValue", $component->getValue());
								$newField->store();
							}
							break;
						case "number":
							$component = $form->getComponentByIdentifier("field".$key);
							if ($component->hasValue()) {
								$newField = $topicFields->addRow();
								$newField->setValue("topicID", $topicID);
								$newField->setValue("fieldID", $key);
								$newField->setValue("intValue", $component->getValue());
								$newField->store();
							}
							break;
						case "float":
							$component = $form->getComponentByIdentifier("field".$key);
							if ($component->hasValue()) {
								$newField = $topicFields->addRow();
								$newField->setValue("topicID", $topicID);
								$newField->setValue("fieldID", $key);
								$newField->setValue("floatValue", $component->getValue());
								$newField->store();
							}
							break;
						case "select":
							$newField = $topicFields->addRow();
							$newField->setValue("topicID", $topicID);
							$newField->setValue("fieldID", $key);
							$newField->setValue("textValue", $_POST['field'.$key]);
							$newField->store();
							break;
					}
				}

				// Store the reviewScores
				$topicScores = new ReviewTopicScoresTable($database);
				$scores = $this->readReviewScores($_POST['reviewType']);
				reset($scores);
				while (list($key, $value) = each($scores)) {
					$component = $form->getComponentByIdentifier("score".$key);
					if ($component->hasValue()) {
						$value = $component->getValue();
						$newScore = $topicScores->addRow();
						$newScore->setValue("topicID", $topicID);
						$newScore->setValue("scoreID", $key);
						$newScore->setValue("value", $component->getValue());
						$newScore->store();
					}
				}
				return true;
			}
		}

		function hasMoreAddTopicSteps($currentStep) {
			return ($currentStep < 2);
		}

		function setEditTopicForm(&$form, $currentStep, &$topic) {
			if ($currentStep > 1) return; // no forms for that
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$reviewTopic = new ReviewTopic($topic);
			$reviewTypeID = $reviewTopic->getReviewType();
			$reviewType = $this->getReviewType($reviewTypeID);

			$formFields = new StandardFormFields();
			$form->addFieldGroup($formFields);
			$formFields->activeForm = $form;
			$reviewType = $this->getReviewType($reviewTypeID);
			$formFields->startGroup($reviewType->getValue("name")." recentie bewerken");

			$this->createReviewTopicForm($form, $reviewTypeID);
			$formFields->endGroup();
			$formFields->addSubmit("Bewerken", false);

			$form->setValue("title", htmlConvert($reviewTopic->getTitle()));
			$form->setValue("post", htmlConvert($reviewTopic->getTopicText()));
			$form->setValue("icon", $reviewTopic->getIconID());
			//$form->setValue("noSmile", ($action->smiliesOn()) ? null : "yes");
			$form->setValue("signature", ($reviewTopic->hasSignature()) ? "yes" : false);
			$form->setValue("finalScore", $reviewTopic->getScore());

			$fields = $this->readReviewFields($reviewType->getValue("ID"));
			$topicFields = new ReviewTopicFieldsTable($database);
			$filter = new DataFilter();
			$filter->addEquals("topicID", $topic->getID());
			$topicFields->selectRows($filter, new ColumnSorting());
			while ($topicField = $topicFields->getRow()) {
				$fieldInfo = $fields[$topicField->getValue("fieldID")];
				$type = $fieldInfo['type'];
				$value = "";
				switch($type) {
					case "text": $form->setValue("field".$topicField->getValue("fieldID"), $topicField->getValue("textValue")); break;
					case "number":  $form->setValue("field".$topicField->getValue("fieldID"), $topicField->getValue("intValue")); break;
					case "float":  $form->setValue("field".$topicField->getValue("fieldID"), $topicField->getValue("floatValue")); break;
					case "select": $form->setValue("field".$topicField->getValue("fieldID"), $topicField->getValue("textValue")); break;
					case "date":
						$dateValue = $topicField->getValue("dateValue");
						$form->setValue("field".$topicField->getValue("fieldID"), $dateValue->toString("d-m-Y"));
						break;
					case "time":
						$timeValue = $topicField->getValue("timeValue");
						$form->setValue("field".$topicField->getValue("fieldID"), $timeValue->toString("H:i"));
						break;
				}
			}

			$topicScores = new ReviewTopicScoresTable($database);
			$filter = new DataFilter();
			$filter->addEquals("topicID", $topic->getID());
			$topicScores->selectRows($filter, new ColumnSorting());
			while ($topicScore = $topicScores->getRow()) {
				$form->setValue("score".$topicScore->getValue("scoreID"), $topicScore->getValue('value'));
			}

		}

		function handleEditTopicAction(&$feedback, &$topic) {
			global $TBBcurrentUser;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$reviewTopicTable = new ReviewTopicTable($database);
			$reviewTopic = $reviewTopicTable->getRowByKey($topic->getID());
			$reviewType = $this->getReviewType($reviewTopic->getValue("reviewType"));

			$form = new Form("editTopic", "edittopic.php");
			$this->createReviewTopicForm($form, $reviewTopic->getValue("reviewType"));
			if (!$form->checkPostedFields($feedback)) return false;
			if ($_POST['finalScore'] > $reviewType->getValue("maxValue")) {
				$feedback->addMessage("Maximale waarde van Eindbeoordeling is " . $reviewType->getValue("prefix") . $reviewType->getValue("maxValue") . $reviewType->getValue("postfix"));
				return false;
			}
			$scores = $this->readReviewScores($reviewTopic->getValue("reviewType"));
			reset($scores);
			while (list($key, $value) = each($scores)) {
				$component = $form->getComponentByIdentifier("score".$key);
				if ($component->hasValue()) {
					$typevalue = $component->getValue();
					if ($typevalue > $value["maxScore"]) {
						$feedback->addMessage("Maximale waarde van ".$component->title." is ".$value["maxScore"]);
						return false;
					}
				}
			}

			$parseUrls = isSet($_POST['autoUrl']) ? true : false;
			$smilies = isSet($_POST['noSmile']) ? false : true;
			$signature = isSet($_POST['signature']) ? true : false;

			$reviewTopic->setValue("message", $_POST['post']);
			$reviewTopic->setValue("signature", $signature);
			$reviewTopic->setValue("smileys", $smilies);
			$reviewTopic->setValue("parseUrls", $parseUrls);
			$reviewTopic->setValue("lastChange", new LibDateTime());
			$reviewTopic->setValue("changeBy", $TBBcurrentUser->getUserID());
			$reviewTopic->setValue("score", $_POST['finalScore']);
			$reviewTopic->store();

			$fieldFilter = new DataFilter();
			$topicID = $reviewTopic->getValue("ID");
			$fieldFilter->addEquals("topicID", $topicID);
			$topicFieldTable = new ReviewTopicFieldsTable($database);
			$topicFieldTable->selectRows($fieldFilter, new ColumnSorting());
			$oldFields = array();
			while ($oldField = $topicFieldTable->getRow()) {
				$oldFields[$oldField->getValue("fieldID")] = $oldField;
			}

			$fields = $this->readReviewFields($reviewTopic->getValue("reviewType"));
			reset($fields);
			while (list($key, $value) = each($fields)) {
				$fieldType = $value['type'];
				switch ($fieldType) {
					case "time":
						$component = $form->getComponentByIdentifier("field".$key);
						if ($component->hasValue()) {
							if (!isSet($oldFields[$key])) {
								$newField = $topicFieldTable->addRow();
							} else {
								$newField = $oldFields[$key];
							}
							$newField->setValue("topicID", $topicID);
							$newField->setValue("fieldID", $key);
							$newField->setValue("timeValue", $component->getTimestamp());
							$newField->store();
						} else {
							if (isSet($oldFields[$key])) {
								$oldField = $oldFields[$key];
								$oldField->delete();
							}
						}
						break;
					case "date":
						$component = $form->getComponentByIdentifier("field".$key);
						if ($component->hasValue()) {
							if (!isSet($oldFields[$key])) {
								$newField = $topicFieldTable->addRow();
							} else {
								$newField = $oldFields[$key];
							}
							$newField->setValue("topicID", $topicID);
							$newField->setValue("fieldID", $key);
							$newField->setValue("dateValue", $component->getTimestamp());
							$newField->store();
						} else {
							if (isSet($oldFields[$key])) {
								$oldField = $oldFields[$key];
								$oldField->delete();
							}
						}
						break;
					case "text":
						$component = $form->getComponentByIdentifier("field".$key);
						if ($component->hasValue()) {
							if (!isSet($oldFields[$key])) {
								$newField = $topicFieldTable->addRow();
							} else {
								$newField = $oldFields[$key];
							}
							$newField->setValue("topicID", $topicID);
							$newField->setValue("fieldID", $key);
							$newField->setValue("textValue", $component->getValue());
							$newField->store();
						} else {
							if (isSet($oldFields[$key])) {
								$oldField = $oldFields[$key];
								$oldField->delete();
							}
						}
						break;
					case "number":
						$component = $form->getComponentByIdentifier("field".$key);
						if ($component->hasValue()) {
							if (!isSet($oldFields[$key])) {
								$newField = $topicFieldTable->addRow();
							} else {
								$newField = $oldFields[$key];
							}
							$newField->setValue("topicID", $topicID);
							$newField->setValue("fieldID", $key);
							$newField->setValue("intValue", $component->getValue());
							$newField->store();
						} else {
							if (isSet($oldFields[$key])) {
								$oldField = $oldFields[$key];
								$oldField->delete();
							}
						}
						break;
					case "float":
						$component = $form->getComponentByIdentifier("field".$key);
						if ($component->hasValue()) {
							if (!isSet($oldFields[$key])) {
								$newField = $topicFieldTable->addRow();
							} else {
								$newField = $oldFields[$key];
							}
							$newField->setValue("topicID", $topicID);
							$newField->setValue("fieldID", $key);
							$newField->setValue("floatValue", $component->getValue());
							$newField->store();
						} else {
							if (isSet($oldFields[$key])) {
								$oldField = $oldFields[$key];
								$oldField->delete();
							}
						}
						break;
					case "select":
						if (!isSet($oldFields[$key])) {
							$newField = $topicFieldTable->addRow();
						} else {
							$newField = $oldFields[$key];
						}
						$newField->setValue("topicID", $topicID);
						$newField->setValue("fieldID", $key);
						$newField->setValue("textValue", $_POST['field'.$key]);
						$newField->store();
						break;
				}
			}

			$scoreFilter = new DataFilter();
			$topicID = $reviewTopic->getValue("ID");
			$scoreFilter->addEquals("topicID", $topicID);
			$topicScoreTable = new ReviewTopicScoresTable($database);
			$topicScoreTable->selectRows($scoreFilter, new ColumnSorting());
			$oldScores = array();
			while ($oldScore = $topicScoreTable->getRow()) {
				$oldScores[$oldScore->getValue("scoreID")] = $oldScore;
			}

			$scores = $this->readReviewScores($reviewTopic->getValue('reviewType'));
			reset($scores);
			while (list($key, $value) = each($scores)) {
				$component = $form->getComponentByIdentifier("score".$key);
				if ($component->hasValue()) {
					$value = $component->getValue();
					if (!isSet($oldScores[$key])) {
						$newScore = $topicScoreTable->addRow();
					} else {
						$newScore = $oldScores[$key];
					}
					$newScore->setValue("topicID", $topicID);
					$newScore->setValue("scoreID", $key);
					$newScore->setValue("value", $component->getValue());
					$newScore->store();
				} else {
					if (isSet($oldScores[$key])) {
						$oldScore = $oldScores[$key];
						$oldScore->delete();
					}
				}
			}
			return true;
		}

		function hasMoreEditTopicSteps($currentStep) {
			return false;
		}

		function hasTitleInfo(&$topic) {
			global $TBBcurrentUser;
			$nrPosts = $topic->getNrPost();
			$nrPages = ceil(($nrPosts +1) / $TBBcurrentUser->getReactionsPerPage());
			return ($nrPages > 1) ? true : false;
		}

/*
		function getTitleInfo(&$topic) {
			global $TBBcurrentUser;
			global $TBBconfiguration;
			$nrPosts = $topic->getNrPost();
			$nrPages = ceil(($nrPosts +1) / $TBBcurrentUser->getReactionsPerPage());
			$pageNavigation = new PageNavigation($nrPages, -1, "topic.php?pageNr=%s&amp;id=".$topic->getID(), 5);
			return "<br />".$pageNavigation->quickPageBarStr("topicPageNav", $TBBconfiguration->imageOnlineDir."multipage.gif");
		}
*/
		function getTitlePrefix(&$topic) {
			global $TBBcurrentUser;
			$reviewTopic = new ReviewTopic($topic);

			$reviewType = $this->getReviewType($reviewTopic->getReviewType());
			return $reviewType->getValue("name") . ": ";
		}

		function getReviewType($typeID) {
			if (isSet($this->privateVars['ReviewTypeCache'][$typeID])) {
				return $this->privateVars['ReviewTypeCache'][$typeID];
			}
			global $TBBconfiguration;
			$moduleDir = $this->getModuleDir();
			require_once($moduleDir . "ReviewTypes.bean.php");

			$database = $TBBconfiguration->getDatabase();
			$reviewTypeTable = new ReviewTypesTable($database);
			$reviewType = $reviewTypeTable->getRowByKey($typeID);
			$this->privateVars['ReviewTypeCache'][$typeID] = $reviewType;
			return $reviewType;
		}

		function getLastPostDate(&$topic, $pageNr) {
			if (!isSet($this->privateVars['lastPostDate'])) return false;
			return $this->privateVars['lastPostDate'];
		}

		function showTopic(&$topic, $pageNr, $options=array()) {
			global $TBBcurrentUser;
			global $TBBconfiguration;
			global $textParser;
			global $TBBemoticonList;
			$database = $TBBconfiguration->getDatabase();

			$topic->incView();
			$board = $topic->board;

			$nrPosts = $topic->getNrPost();
			$nrPages = ceil(($nrPosts +1) / $TBBcurrentUser->getReactionsPerPage());
			$lastRead = $topic->lastReadTime();

			if (isSet($options['goto'])) {
				if ($options['goto'] == 'lastpost') $pageNr = $nrPages - 1;
			}
			$highlights = array();
			if (isSet($options['highlight'])) $highlights = $options['highlight'];
?>
<div class="center">
	<div id="discussionTopic">
<?php
			$pageNavUrl = sprintf("topic.php?pageNr=%%s&amp;id=%s%s", $topic->getID(),
				(count($highlights) > 0) ? "&amp;highlight=".implode("%%20", $highlights) : "");
			$pageNavigation = new PageNavigation($nrPages, $pageNr+1, $pageNavUrl, 10);
			$pageNavigation->showPagebar("topPageBar");

			$buttonBar = new ButtonBar();
			if ($board->canAddTopics($TBBcurrentUser))
				$buttonBar->addButton("newtopic", "Nieuw onderwerp", "Nieuw onderwerp starten", sprintf("addtopic.php?boardID=%s", $board->getID()));
			if ($board->canWrite($TBBcurrentUser) && ((!$topic->isLocked()) || ($TBBcurrentUser->isActiveAdmin())))
				$buttonBar->addButton("newreaction", "Plaats reactie", "Reactie plaatsen", sprintf("editreply.php?topicID=%s", $topic->getID()));
			$buttonBar->showBar();

			$posts = new Table();
			$posts->setClass("topicPosts");
			$posts->setHeader("Auteur", "Bericht");
			$posts->setHeaderClasses("author", "message");
			$posts->setCellLimit(2);
			$posts->setRowClasses("author", "message", "time", "posttools");
			$posts->setAlignment("left", "left", "left", "left");
			
			$reviewTopic = new ReviewTopic($topic);
			$reactions = array();

			$boardProfile = $board->getBoardSettings();
			$tbbTags = $boardProfile->getTBBtagList();
			$emoticons = $reviewTopic->smiliesOn() ? $TBBemoticonList : false;
			if (!$TBBcurrentUser->showEmoticons()) $emoticons = false;

			if ($pageNr == 0) {
				$starter = $reviewTopic->getStarter();
				$iconInfo = $topic->getIconInfo();

				$canEdit = false;
				if ($TBBcurrentUser->isActiveAdmin()) $canEdit = true;
				if ((!$topic->isLocked()) && ($starter->isCurrentUser())) $canEdit = true;

				$toolbar = new Menu();

				if ($canEdit) {
					$toolbar->addItem('edit','', 'bewerken', 'edittopic.php?id='.$topic->getID(), '', $TBBconfiguration->imageOnlineDir.'edit.gif', 0, false, '');
				}
				$TBBconfiguration->getMessageToolbar($toolbar, $starter);

				$underPost = "";
				if ($reviewTopic->isEdited()) {
					$editor = $reviewTopic->editedBy();
					$underPost = sprintf(
						'<p class="edited">bewerkt door %s op %s</p>',
						htmlConvert($editor->getNickname()),
						$TBBconfiguration->parseDate($reviewTopic->lastChange())
					);
				}
				if ($TBBcurrentUser->showSignatures() && $TBBconfiguration->getSignaturesAllowed() && $boardProfile->allowSignatures()) {
					if ($reviewTopic->hasSignature()) {
						$underPost .= sprintf('<div class="signature">%s</div>',
							$starter->getSignatureHTML()
						);
					}
				}
				$readIcon = '<img src="images/posticon.gif" alt="gelezen" /> ';
				if ($lastRead->before($reviewTopic->getTime())) {
					$readIcon = '<img src="images/posticonnew.gif" alt="ongelezen" /> ';
				}
				$fieldsTable = $this->getReviewPropertiesTable($reviewTopic);
				$fieldsTable->hideHeader();
				$fieldsTable->setClass("reviewProperties table");
				$fieldsTable->setRowClasses("name", "value");

				$scoreTable = $this->getReviewScoreTable($reviewTopic);
				$scoreTable->hideHeader();
				$scoreTable->setClass("reviewScores table");
				$scoreTable->setRowClasses("name", "value");

				$reviewType = $this->getReviewType($reviewTopic->getReviewType());

				$finalScore = sprintf('<div class="reviewScore">Beoordeling: <strong>%s%s%s</strong> / <em>%s%s%s</em></div>',
					$reviewType->getValue("prefix"),
					$reviewTopic->getScore(),
					$reviewType->getValue("postfix"),
					$reviewType->getValue("prefix"),
					$reviewType->getValue("maxValue"),
					$reviewType->getValue("postfix")
				);

			 	$functions = new FunctionDescriptions();
			 	$functions->addAverage('score', 'userScore');
			 	$topicFilter = new DataFilter();
			 	$topicFilter->addEquals('topicID', $reviewTopic->getID());
			 	$reactionTable = new ReactionTable($database);

			 	$rowFilter = new DataFilter();
			 	$rowFilter->addJoinDataFilter("ID", "ID", $reactionTable, $topicFilter);

			 	$reviewReactionTable = new ReviewReactionTable($database);
			 	$resultSet = $reviewReactionTable->executeDataFunction($functions, $rowFilter);

			 	if ($resultRow = $resultSet->getRow()) {
			 		$avgUserScore = $resultRow['userScore'];
					$userScore = sprintf('<div class="reviewScore">Gemiddelde leden beoordeling: <strong>%s%s%s</strong> / <em>%s%s%s</em></div>',
						$reviewType->getValue("prefix"),
						$avgUserScore,
						$reviewType->getValue("postfix"),
						$reviewType->getValue("prefix"),
						$reviewType->getValue("maxValue"),
						$reviewType->getValue("postfix")
					);
				} else {
					$userScore = "";
				}

				$posts->addRow(
					sprintf(
						'<span class="author">%s</span><br />%s',
						htmlConvert($starter->getNickName()),
						$TBBconfiguration->getUserInfoBlock($starter)
					),
					sprintf(
						'<h4>%s%s</h4><p class="messageText">%s</p><div class="revProperties">%s%s</div><p>%s%s</p>%s',
						($reviewTopic->hasIcon()) ? '<img src="'.$iconInfo['imgUrl'].'" title="'.$iconInfo['name'].'" alt="" /> ' : "",
						$textParser->breakLongWords(htmlConvert($reviewTopic->getTitle()), 40, $highlights),
						$textParser->parseMessageText($reviewTopic->getTopicText(), $emoticons, $tbbTags, $highlights),
						$fieldsTable->getTableString(),
						$scoreTable->getTableString(),
						$finalScore,
						$userScore,
						$underPost
					),
					$readIcon .
					$TBBconfiguration->parseDate($reviewTopic->getTime()),
					$toolbar->getMenuStr("ptoolbar")
				);
				$this->privateVars['lastPostDate'] = $reviewTopic->getTime();


				$reactions = $reviewTopic->getReactions(0, $TBBcurrentUser->getReactionsPerPage() - 1);
			} else {
				$reactions = $reviewTopic->getReactions(($pageNr * $TBBcurrentUser->getReactionsPerPage()) -1, $TBBcurrentUser->getReactionsPerPage());
			}
			$startPost = ($nrPages-1) * $TBBcurrentUser->getReactionsPerPage();
			for ($i = 0; $i < count($reactions); $i++) {
				$reaction = $reactions[$i];
				$starter = $reaction->getUser();
				$iconInfo = $reaction->getIconInfo();
				$canEdit = false;
				$this->privateVars['lastPostDate'] = $reaction->getTime();
				if ($TBBcurrentUser->isActiveAdmin()) $canEdit = true;
				if ((!$topic->isLocked()) && ($starter->isCurrentUser())) $canEdit = true;

				$toolbar = new Menu();
				if ($canEdit) {
				//	$toolbar->addItem('edit','', 'bewerken', 'editreply.php?topicID='.$topic->getID().'&amp;edit=yes&amp;postID='.$reaction->getID(), '', $TBBconfiguration->imageOnlineDir.'edit.gif', 0, false, '');
				}
				$TBBconfiguration->getMessageToolbar($toolbar, $starter);

				$underPost = "";
				if ($reaction->isEdited()) {
					$editor = $reaction->editedBy();
					$underPost = sprintf(
						'<p class="edited">bewerkt door %s op %s</p>',
						htmlConvert($editor->getNickname()),
						$TBBconfiguration->parseDate($reaction->lastChange())
					);
				}
				if ($TBBcurrentUser->showSignatures() && $TBBconfiguration->getSignaturesAllowed() && $boardProfile->allowSignatures()) {
					if ($reaction->hasSignature()) {
						$underPost .= sprintf('<div class="signature">%s</div>',
							$starter->getSignatureHTML()
						);
					}
				}

				$emoticons = $reaction->smiliesOn() ? $TBBemoticonList : false;
				if (!$TBBcurrentUser->showEmoticons()) $emoticons = false;
				$readIcon = '<img src="images/posticon.gif" alt="gelezen" /> ';
				$reactionTime = $reaction->getTime();
				if ($lastRead->before($reactionTime)) {
					$readIcon = '<img src="images/posticonnew.gif" alt="ongelezen" /> ';
				}

				if (!$reaction->isReview()) {
					$posts->addRow(
						sprintf(
							'<a name="post%s" />%s<span class="author">%s</span><br />%s',
							$reaction->getID(),
							(($i + $startPost) == ($nrPosts-1)) ? '<a name="lastpost" />' : '',
							htmlConvert($starter->getNickName()),
							$TBBconfiguration->getUserInfoBlock($starter)
						),
						sprintf(
							'<h4>%s%s</h4><p class="messageText">%s</p>%s',
							($reaction->hasIcon()) ? '<img src="'.$iconInfo['imgUrl'].'" title="'.$iconInfo['name'].'" alt="" /> ' : "",
							$textParser->breakLongWords(htmlConvert($reaction->getTitle()), 40),
							$textParser->parseMessageText($reaction->getMessage(), $emoticons, $tbbTags),
							$underPost
						),
						$readIcon .
						$TBBconfiguration->parseDate($reaction->getTime()),
						$toolbar->getMenuStr("ptoolbar")
					);
				} else {
					$scoreTable = $this->getReviewReactionScoreTable($reaction, $reviewTopic->getReviewType());
					$scoreTable->hideHeader();
					$scoreTable->setClass("reviewScores table");
					$scoreTable->setRowClasses("name", "value");
					$finalScore = sprintf('<div class="reviewScore">Beoordeling: <strong>%s%s%s</strong> / <em>%s%s%s</em></div>',
						$reviewType->getValue("prefix"),
						$reaction->getScore(),
						$reviewType->getValue("postfix"),
						$reviewType->getValue("prefix"),
						$reviewType->getValue("maxValue"),
						$reviewType->getValue("postfix")
					);

					$posts->addRow(
						sprintf(
							'<a name="post%s" />%s<span class="author">%s</span><br />%s',
							$reaction->getID(),
							(($i + $startPost) == ($nrPosts-1)) ? '<a name="lastpost" />' : '',
							htmlConvert($starter->getNickName()),
							$TBBconfiguration->getUserInfoBlock($starter)
						),
						sprintf(
							'<h4>%s%s</h4><p class="messageText">%s</p><div class="revProperties">%s</div><p>%s</p>%s',
							($reaction->hasIcon()) ? '<img src="'.$iconInfo['imgUrl'].'" title="'.$iconInfo['name'].'" alt="" /> ' : "",
							$textParser->breakLongWords(htmlConvert($reaction->getTitle()), 40),
							$textParser->parseMessageText($reaction->getMessage(), $emoticons, $tbbTags),
							$scoreTable->getTableString(),
							$finalScore,
							$underPost
						),
						$readIcon .
						$TBBconfiguration->parseDate($reaction->getTime()),
						$toolbar->getMenuStr("ptoolbar")
					);

				}
			}
			$posts->showTable();
			$buttonBar->showBar();
			$pageNavigation->showPagebar("botPageBar");
?>
	</div>
</div>
<?php
		}

		function createReviewReactionForm(&$form, $reviewTypeID) {
			$formFields = new StandardFormFields();
			$form->addFieldGroup($formFields);
			$formFields->activeForm = $form;

			$boardFormFields = new BoardFormFields();
			$form->addFieldGroup($boardFormFields);
			$boardFormFields->activeForm = $form;

			$formFields->addTextField("title", "Titel", "Titel van het onderwerp", 80, false);
			$boardFormFields->addIconBar("icon", "Pictogram", "pictogram van het onderwerp");
			$radioOptions = array(
				array(
					"value" => "comment",
					"caption" => "Commentaar",
					"description" => "",
					"show" => array(),
					"hide" => array("scores")
				),
				array(
					"value" => "review",
					"caption" => "Scores geven",
					"description" => "",
					"show" => array("scores"),
					"hide" => array()
				)
			);

			$formFields->addRadioViewHide("type", "Soort reactie", "Commentaar of eigen ervaringen delen?", $radioOptions, "comment");
			$boardFormFields->addPostTextField("post", "Bericht", "", true, true, true);
			$form->startMarking("scores");
			$formFields->startGroup("Beoordeling");

			$reviewType = $this->getReviewType($reviewTypeID);
			$scores = $this->readReviewScores($reviewTypeID);
			reset($scores);
			while (list($key, $value) = each($scores)) {
				$form->addComponent(new FormNumberField("score".$key, $value["name"], "", 5, true, false, "", "/ ".$value["maxScore"]));
			}
			$form->addComponent(new FormFloatField("finalScore", "Eindbeoordeling", "Max. ".$reviewType->getValue("prefix").$reviewType->getValue("maxValue").$reviewType->getValue("postfix"), 5, true, false, $reviewType->getValue("prefix"), $reviewType->getValue("postfix")));

			$form->endMarking();
			$options = array(
				array("value" => "yes", "caption" => "Maak URLs automatisch", "description" => "zet automatisch [url] en [/url] om een internet adres", "name" => "autoUrl", "checked" => true),
				array("value" => "yes", "caption" => "Geen smiles in dit bericht", "description" => "", "name" => "noSmile", "checked" => false),
				array("value" => "yes", "caption" => "Toon handtekening", "description" => "zet je handtekening onder dit bericht", "name" => "signature", "checked" => true)
				//array("value" => "yes", "caption" => "Bericht bij reacties", "description" => "reakties op dit bericht per email ontvangen", "name" => "subscribe", "checked" => false)
				//array("value" => "yes", "caption" => "Opslaan als concept", "description" => "Bericht niet plaatsen maar opslaan als concept", "name" => "concept", "checked" => false)
			);
			$formFields->addCheckboxes("Opties", "", $options);
		}

		function addReactionForm(&$form, $currentStep, &$topic) {
			$reviewTopic = new ReviewTopic($topic);

			$formFields = new StandardFormFields();
			$form->addFieldGroup($formFields);
			$formFields->activeForm = $form;

			$formFields->startGroup("Reactie plaatsen");
			$reviewTypeID = $reviewTopic->getReviewType();
			$this->createReviewReactionForm($form, $reviewTypeID);
			$formFields->addSubmit("Plaatsen", true);
			$formFields->endGroup();
		}

		function handleAddReactionAction(&$feedback, &$topic) {
			$state = "online";
			global $TBBcurrentUser;
			global $TBBsession;
			global $TBBconfiguration;

			if ($_POST['type'] == 'comment') {
				$reactionID = $topic->addReaction($TBBcurrentUser, $state);
				if ($reactionID == false) {
					$feedback->addMessage("Fout bij plaatsen van reactie in database!");
					return false;
				}
				$parseUrls = isSet($_POST['autoUrl']) ? true : false;
				$smilies = isSet($_POST['noSmile']) ? false : true;
				$signature = isSet($_POST['signature']) ? true : false;

				$database = $TBBconfiguration->getDatabase();
				$discReactionTable = new ReviewReactionTable($database);
				$newReaction = $discReactionTable->addRow();
				$newReaction->setValue("ID", $reactionID);
				$newReaction->setValue("icon", $_POST['icon']);
				$newReaction->setValue("title", $_POST['title']);
				$newReaction->setValue("message", $_POST['post']);
				$newReaction->setValue("signature", $signature);
				$newReaction->setValue("smileys", $smilies);
				$newReaction->setValue("parseUrls", $parseUrls);
				$newReaction->setValue("replyType", "comment");
				$newReaction->store();

				$TBBsession->actionHandled();
				$TBBconfiguration->redirectUri('topic.php?id='.$topic->getID().'&goto=lastpost#lastpost');
			}
			if ($_POST['type'] == 'review') {
				$form = new Form("addReaction", "editreply.php");
				$reviewTopic = new ReviewTopic($topic);
				$reviewTypeID = $reviewTopic->getReviewType();
				$reviewType = $this->getReviewType($reviewTypeID);
				$this->createReviewReactionForm($form, $reviewTypeID);
				if (!$form->checkPostedFields($feedback)) return false;
				if ($_POST['finalScore'] > $reviewType->getValue("maxValue")) {
					$feedback->addMessage("Maximale waarde van Eindbeoordeling is " . $reviewType->getValue("prefix") . $reviewType->getValue("maxValue") . $reviewType->getValue("postfix"));
					return false;
				}
				$scores = $this->readReviewScores($reviewTypeID);
				reset($scores);
				while (list($key, $value) = each($scores)) {
					$component = $form->getComponentByIdentifier("score".$key);
					if ($component->hasValue()) {
						$typevalue = $component->getValue();
						if ($typevalue > $value["maxScore"]) {
							$feedback->addMessage("Maximale waarde van ".$component->title." is ".$value["maxScore"]);
							return false;
						}
					}
				}

				$reactionID = $topic->addReaction($TBBcurrentUser, $state);
				if ($reactionID == false) {
					$feedback->addMessage("Fout bij plaatsen van reactie in database!");
					return false;
				}
				$parseUrls = isSet($_POST['autoUrl']) ? true : false;
				$smilies = isSet($_POST['noSmile']) ? false : true;
				$signature = isSet($_POST['signature']) ? true : false;

				$database = $TBBconfiguration->getDatabase();
				$discReactionTable = new ReviewReactionTable($database);
				$newReaction = $discReactionTable->addRow();
				$newReaction->setValue("ID", $reactionID);
				$newReaction->setValue("icon", $_POST['icon']);
				$newReaction->setValue("title", $_POST['title']);
				$newReaction->setValue("message", $_POST['post']);
				$newReaction->setValue("signature", $signature);
				$newReaction->setValue("smileys", $smilies);
				$newReaction->setValue("parseUrls", $parseUrls);
				$newReaction->setValue("replyType", "review");
				$newReaction->setValue("score", $_POST["finalScore"]);
				$newReaction->store();

				// Store the reviewScores
				$reactionScores = new ReviewReactionScoresTable($database);
				$scores = $this->readReviewScores($reviewTypeID);
				reset($scores);
				while (list($key, $value) = each($scores)) {
					$component = $form->getComponentByIdentifier("score".$key);
					if ($component->hasValue()) {
						$value = $component->getValue();
						$newScore = $reactionScores->addRow();
						$newScore->setValue("reactionID", $reactionID);
						$newScore->setValue("scoreID", $key);
						$newScore->setValue("value", $component->getValue());
						$newScore->store();
					}
				}
				$TBBsession->actionHandled();
				$TBBconfiguration->redirectUri('topic.php?id='.$topic->getID().'&goto=lastpost#lastpost');
			}
			return true;
		}
/*
		function editReactionForm(&$form, $currentStep, &$topic, $postID) {
			global $textParser;

			$discussionTopic = new DiscussionTopic($topic);
			$reaction = $discussionTopic->getReaction($postID);

			$formFields = new StandardFormFields();
			$form->addFieldGroup($formFields);
			$formFields->activeForm = $form;

			$boardFormFields = new BoardFormFields();
			$form->addFieldGroup($boardFormFields);
			$boardFormFields->activeForm = $form;

			$formFields->startGroup("Reactie bewerken");
			$formFields->addTextField("title", "Titel", "Titel van het onderwerp", 80);

			$boardFormFields->addIconBar("icon", "Pictogram", "pictogram van het onderwerp");
			$boardFormFields->addPostTextField("post", "Bericht", "", true, true, true);
			$options = array(
				array("value" => "yes", "caption" => "Maak URLs automatisch", "description" => "zet automatisch [url] en [/url] om een internet adres", "name" => "autoUrl", "checked" => true),
				array("value" => "yes", "caption" => "Geen smiles in dit bericht", "description" => "", "name" => "noSmile", "checked" => false),
				array("value" => "yes", "caption" => "Toon handtekening", "description" => "zet je handtekening onder dit bericht", "name" => "signature", "checked" => true)
				//array("value" => "yes", "caption" => "Bericht bij reacties", "description" => "reacties op dit bericht per email ontvangen", "name" => "subscribe", "checked" => false)
				//array("value" => "yes", "caption" => "Opslaan als concept", "description" => "Bericht niet plaatsen maar opslaan als concept", "name" => "concept", "checked" => false)
			);
			$formFields->addCheckboxes("Opties", "", $options);
			$formFields->addSubmit("Plaatsen", true);
			$formFields->endGroup();

			$form->setValue("title", htmlConvert($reaction->getTitle()));
			$form->setValue("post", htmlConvert($reaction->getMessage()));
			$form->setValue("icon", $reaction->getIconID());
			$form->setValue("noSmile", ($reaction->smiliesOn()) ? null : "yes");
		}

		function handleEditReactionAction(&$feedback, &$topic) {
			$state = "online";
			global $TBBcurrentUser;
			global $TBBsession;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$changed = $topic->editReaction($_POST['postID'], $TBBcurrentUser, $state);
			if ($changed == false) {
				$feedback->addMessage("Fout bij bewerken van reactie in database!");
				return false;
			}
			$parseUrls = isSet($_POST['autoUrl']) ? true : false;
			$smilies = isSet($_POST['noSmile']) ? false : true;
			$signature = isSet($_POST['signature']) ? true : false;

			$discReactionTable = new ReviewReactionTable($database);
			$reaction = $discReactionTable->getRowByKey($_POST['postID']);
			$reaction->setValue("icon", $_POST['icon']);
			$reaction->setValue("title", $_POST['title']);
			$reaction->setValue("message", $_POST['post']);
			$reaction->setValue("signature", $signature);
			$reaction->setValue("smileys", $smilies);
			$reaction->setValue("parseUrls", $parseUrls);
			$reaction->store();
			$TBBsession->actionHandled();
			//todo: jump to correct post!
			$TBBconfiguration->redirectUri('topic.php?id='.$topic->getID().'&goto=lastpost#lastpost');
			return true;
		}
*/

		function install($moduleID) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$reviewTypes = new ReviewTypesTable($database);
			$reviewTypes->createTable();

			$reviewFields = new ReviewFieldsTable($database);
			$reviewFields->createTable();

			$reviewFieldValues = new ReviewFieldValuesTable($database);
			$reviewFieldValues->createTable();

			$reviewScore = new ReviewScoreTable($database);
			$reviewScore->createTable();

			$reviewTopic = new ReviewTopicTable($database);
			$reviewTopic->createTable();

			$reviewTopicFields = new ReviewTopicFieldsTable($database);
			$reviewTopicFields->createTable();

			$reviewTopicScore = new ReviewTopicScoreTable($database);
			$reviewTopicScore->createTable();

			$reviewReaction = new ReviewReactionTable($database);
			$reviewReaction->createTable();

			$reviewReactionScore = new ReviewReactionScoreTable($database);
			$reviewReactionScore->createTable();

			return true;
		}

/*
		function hasAdminPage() {
			return true;
		}
/*
		function getAdminModule() {
			$modDir = $this->getModuleDir();
			require_once($modDir."ReviewAdminModule.class.php");
			$adminModule = new ReviewAdminModule();
			return $adminModule;
		}
*/

		function getReviewPropertiesTable(&$topic) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$reviewType = $this->getReviewType($topic->getReviewType());
			$fieldsTable = new Table();
			$fieldsTable->setHeader("Naam", "Waarde");
			$fields = $this->readReviewFields($reviewType->getValue("ID"));

			$topicFields = new ReviewTopicFieldsTable($database);
			$filter = new DataFilter();
			$filter->addEquals("topicID", $topic->getID());
			$topicFields->selectRows($filter, new ColumnSorting());
			while ($topicField = $topicFields->getRow()) {
				$fieldInfo = $fields[$topicField->getValue("fieldID")];
				$type = $fieldInfo['type'];
				$value = "";
				switch($type) {
					case "text": $value = $topicField->getValue("textValue"); break;
					case "number": $value = $topicField->getValue("intValue"); break;
					case "float": $value = $topicField->getValue("floatValue"); break;
					case "select": $value = $topicField->getValue("textValue"); break;
					case "date":
						$dateValue = $topicField->getValue("dateValue");
						$value = $dateValue->toString("d-m-Y");
						break;
					case "time":
						$timeValue = $topicField->getValue("timeValue");
						$value = $timeValue->toString("H:i");
						break;
				}
				$fieldsTable->addRow(
					$fieldInfo['name'],
					$fieldInfo['prefix'].' '.$value.' '.$fieldInfo['postfix']
				);
			}
			return $fieldsTable;
		}

		function readReviewFields($reviewID) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$moduleDir = $this->getModuleDir();
			require_once($moduleDir . "ReviewFields.bean.php");

			$fieldsTable = new ReviewFieldsTable($database);
			$dataFilter = new DataFilter();
			$dataFilter->addEquals("reviewType", $reviewID);
			$fieldsTable->selectRows($dataFilter, new ColumnSorting());
			$fieldsArray = array();
			while ($field = $fieldsTable->getRow()) {
				$fieldsArray[$field->getValue("ID")] = array(
					"name" => $field->getValue("name"),
					"prefix" => $field->getValue("prefix"),
					"postfix" => $field->getValue("postfix"),
					"type" => $field->getValue("type")
				);
			}
			return $fieldsArray;
		}

		function getReviewScoreTable(&$topic) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$reviewType = $this->getReviewType($topic->getReviewType());
			$scoreTable = new Table();
			$scoreTable->setHeader("Facet", "Beoordeling");
			$scores = $this->readReviewScores($reviewType->getValue("ID"));

			$topicScores = new ReviewTopicScoresTable($database);
			$filter = new DataFilter();
			$filter->addEquals("topicID", $topic->getID());
			$topicScores->selectRows($filter, new ColumnSorting());
			while ($topicScore = $topicScores->getRow()) {
				$scoreInfo = $scores[$topicScore->getValue("scoreID")];
				$scoreTable->addRow(
					$scoreInfo['name'],
					'<span class="partScore">'.$topicScore->getValue('value').'</span>/'.$scoreInfo['maxScore']
				);
			}
			return $scoreTable;
		}

		function getReviewReactionScoreTable(&$reaction, $reviewTypeID) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$reviewType = $this->getReviewType($reviewTypeID);
			$scoreTable = new Table();
			$scoreTable->setHeader("Facet", "Beoordeling");
			$scores = $this->readReviewScores($reviewTypeID);

			$reactionScores = new ReviewReactionScoresTable($database);
			$filter = new DataFilter();
			$filter->addEquals("reactionID", $reaction->getID());
			$reactionScores->selectRows($filter, new ColumnSorting());
			while ($reactionScore = $reactionScores->getRow()) {
				$scoreInfo = $scores[$reactionScore->getValue("scoreID")];
				$scoreTable->addRow(
					$scoreInfo['name'],
					'<span class="partScore">'.$reactionScore->getValue('value').'</span>/'.$scoreInfo['maxScore']
				);
			}
			return $scoreTable;
		}

		function readReviewScores($reviewID) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$moduleDir = $this->getModuleDir();
			require_once($moduleDir . "ReviewScore.bean.php");

			$scoreTable = new ReviewScoreTable($database);
			$dataFilter = new DataFilter();
			$dataFilter->addEquals("reviewType", $reviewID);
			$scoreTable->selectRows($dataFilter, new ColumnSorting());
			$scoreArray = array();
			while ($score = $scoreTable->getRow()) {
				$scoreArray[$score->getValue("ID")] = array(
					"name" => $score->getValue("name"),
					"maxScore" => $score->getValue("maxScore")
				);
			}
			return $scoreArray;
		}

		function deleteTopic($id) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$reviewTopicTable = new ReviewTopicTable($database);
			$reviewReactionTable = new ReviewReactionTable($database);
			$reactionTable = new ReactionTable($database);
			$reviewFieldTable = new ReviewTopicFieldsTable($database);
			$reviewScoreTable = new ReviewTopicScoresTable($database);
			$reactionScoreTable = new ReviewReactionScoresTable($database);

			$removeID = $id;
			$topicFilter = new DataFilter();
			$topicFilter->addEquals("topicID", $removeID);
			// Delete topic fields
			$reviewFieldTable->deleteRows($topicFilter);
			// Delete topic scores
			$reviewScoreTable->deleteRows($topicFilter);
			// Delete reaction scores
			$reactionFilter = new DataFilter();
			$reactionFilter->addJoinDataFilter("reactionID", "ID", $reactionTable, $topicFilter);
			$reactionScoreTable->deleteRows($reactionFilter);
			// Delete reactions
			$reactionFilter = new DataFilter();
			$reactionFilter->addJoinDataFilter("ID", "ID", $reactionTable, $topicFilter);
			$reviewReactionTable->deleteRows($reactionFilter);
			// Delete topic
			$reviewTopic = $reviewTopicTable->getRowByKey($removeID);
			$reviewTopic->delete();
			// All info is deleted, now clean up
			return true;
		}
	}

?>
