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
	importClass("board.Text");
	require_once($moduleDir . "ReferenceTopic.bean.php");
	require_once($moduleDir . "ReferenceTopic.class.php");
	importClass("board.TopicPlugin");
	importClass("board.ActionHandler");

	/*
	$libraryClassDir = $GLOBALS['libraryClassDir'];
	$TBBclassDir = $GLOBALS['TBBclassDir'];

	require_once($TBBclassDir . 'ActionHandler.class.php');
	require_once($TBBclassDir . 'TopicPlugin.class.php');
	require_once($TBBclassDir . 'Board.class.php');
	require_once($TBBclassDir . 'Topic.bean.php');
	require_once($TBBclassDir . 'Reaction.bean.php');
	require_once($TBBclassDir . 'Buttonbar.class.php');
	require_once($TBBclassDir . 'BoardFormFields.class.php');
	require_once($TBBclassDir . 'TBBEmoticonList.class.php');
	require_once($TBBclassDir . 'Text.class.php');

	require_once($moduleDir . 'ReferenceTopic.bean.php');
	require_once($moduleDir . 'ReferenceTopic.class.php');
	*/

	class ReferenceTopicPlugin extends TopicPlugin {

		function ReviewTopicPlugin() {
			$this->TopicPlugin();
		}

		function getSelectionName() {
			return "Verwijzing";
		}

		function setAddTopicForm(&$form, $currentStep, &$board) {
			if ($currentStep > 1) return; // no forms for that
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			if ($currentStep == 0) {
				$formFields = new StandardFormFields();
				$form->addFieldGroup($formFields);
				$formFields->activeForm = $form;

				$boardFormFields = new BoardFormFields();
				$form->addFieldGroup($boardFormFields);
				$boardFormFields->activeForm = $form;

				$formFields->startGroup("Nieuwe verwijzing aanmaken");
				$formFields->addTextField("title", "Titel", "Titel van het onderwerp", 80, true);
				$boardFormFields->addIconBar("icon", "Pictogram", "pictogram van het onderwerp");

				$referenceTypes = array(
					array("value" => "board", "caption" => "Board", "description" => "Referentie naar een board", "show" => array("board"), "hide" => array("topic", "url")),
					array("value" => "topic", "caption" => "Onderwerp", "description" => "Referentie naar een onderwerp", "show" => array("topic"), "hide" => array("board", "url")),
					array("value" => "url", "caption" => "Url", "description" => "Referentie naar een internetsite", "show" => array("url"), "hide" => array("topic", "board"))
				);
				$formFields->addRadioViewHide("type", "Soort verwijzing", "Selecteer de soort verwijzing", $referenceTypes, "topic");
				$form->startMarking("board");
				$form->addComponent(new FormTextField("refBoardID", "Board ID", "ID nummer van board", 9));
				$form->startMarking("topic");
				$form->addComponent(new FormTextField("refTopicID", "Onderwerp ID", "ID nummer van onderwerp", 9));
				$form->startMarking("url");
				$form->addComponent(new FormTextField("refUrl", "URL", "adres van site", 255));
				$form->endMarking();

				$windowTypes = array(
					array("value" => "same", "caption" => "Zelfde", "description" => "Open in zelfde venster"),
					array("value" => "new", "caption" => "Nieuw", "description" => "Open in nieuw venster")
				);
				$formFields->addRadio("newWindow", "Venster", "Kies in welk venster verwijzing opent", $windowTypes, "same");

				$formFields->endGroup();
				$formFields->addSubmit("Ok", false);
			}
		}

		function handleAddTopicAction(&$feedback, &$board) {
			global $TBBcurrentUser;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			// Check the input
			$form = new Form("newTopic", "addtopic.php");
			$formFields = new StandardFormFields();
			$form->addFieldGroup($formFields);
			$formFields->activeForm = $form;
			$formFields->addTextField("title", "Titel", "Titel van het onderwerp", 80, true);
			$postValue = "";
			switch($_POST['type']) {
				case 'board':
					$form->addComponent(new FormTextField("refBoardID", "Board ID", "ID nummer van board", 9));
					$postValue = $_POST["refBoardID"];
					break;
				case 'topic':
					$form->addComponent(new FormTextField("refTopicID", "Onderwerp ID", "ID nummer van onderwerp", 9));
					$postValue = $_POST["refTopicID"];
					break;
				case 'url':
					$form->addComponent(new FormTextField("refUrl", "URL", "adres van site", 255));
					$postValue = $_POST["refUrl"];
					break;
			}
			if (!$form->checkPostedFields($feedback)) return false;

			$state = "online";
			$closed = "no";
			$special = "no";

			$topicID = $board->addTopic($TBBcurrentUser, trim($_POST['title']), $_POST['icon'], $this->getModuleName(), $state, $closed, $special);
			if ($topicID == false) {
				$feedback->addMessage("Fout bij plaatsen van onderwerp in database!");
				return false;
			}

			$refTopicTable = new ReferenceTopicTable($database);
			$newTopic = $refTopicTable->addRow();
			$newTopic->setValue("ID", $topicID);
			$newTopic->setValue("type", $_POST["type"]);
			$newTopic->setValue("newWindow", ($_POST["newWindow"] == "new"));
			$newTopic->setValue("value", $postValue);
			$newTopic->setValue("created", "user");

			$newTopic->store();

			return true;
		}

		function hasMoreAddTopicSteps($currentStep) {
			return ($currentStep < 1);
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

		function getTitlePrefix(&$topic) {
			$refTop = new ReferenceTopic($topic);
			if ($refTop->getCreated() == "user") return "Verwijzing: ";
			return "Verplaatst: ";
		}

		function openNewFrame(&$topic) {
			$refTop = new ReferenceTopic($topic);
			global $TBBcurrentUser;
			if ($TBBcurrentUser->isActiveAdmin()) return false;
			if (!$refTop->isLocked())
				return $refTop->getNewWindow();
			else return false;
		}

		function getLastPostDate(&$topic, $pageNr) {
			if (!isSet($this->privateVars['lastPostDate'])) return false;
			return $this->privateVars['lastPostDate'];
		}

		function showTopic(&$topic, $pageNr, $options=array()) {
			global $TBBcurrentUser;

			$topic->incView();
			$refTop = new ReferenceTopic($topic);
			$this->privateVars['lastPostDate'] = $topic->getLastReactionTime();

				if (!$topic->isLocked()) {
					if (!$TBBcurrentUser->isActiveAdmin()) {
					?>
						<script type="text/javascript"><!--
						<?php
							switch($refTop->getType()) {
								case 'topic': $redir = 'document.location.href="topic.php?id=%s"'; break;
								case 'board': $redir = 'document.location.href="index.php?id=%s"'; break;
								default: $redir = 'document.location.href="%s"'; break;
							}
							printf($redir, $refTop->getValue());
						?>
						// -->
						</script>
					<?php
				} else {
					switch($refTop->getType()) {
						case 'topic': $redir = 'topic.php?id=%s'; break;
						case 'board': $redir = 'index.php?id=%s'; break;
						default: $redir = '%s'; break;
					}
					if ($refTop->getNewWindow()) $frame = ' target="_blank"'; else $frame = "";

					$text = new Text();
					$text->addHTMLText("<a href=\"".sprintf($redir, $refTop->getValue())."\"".$frame.">Klik hier om de verwijzing te volgen</a>");
					$text->showText();

				}
			} else {
				$text = new Text();
				$text->addHTMLText("Verwijzing is gesloten.");
				$text->showText();
			}
		}

		function install($moduleID) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$referenceTable = new ReferenceTopicTable($database);
			$referenceTable->createTable();

			$TBBconfiguration->setReferenceID($moduleID);
			return true;
		}

		function createReferenceOfTopic(&$topic) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$starter = $topic->getStarter();

			$state = "online";
			$closed = "no";
			$special = "no";
			if ($topic->isSticky()) {
				$special = "sticky";
			}
			$board = $topic->board;
			$topicID = $board->addTopic($starter, $topic->getTitle(), $topic->getIconID(), $this->getModuleName(), $state, $closed, $special);
			if ($topicID == false) {
				$feedback->addMessage("Fout bij plaatsen van onderwerp in database!");
				return false;
			}

			$refTopicTable = new ReferenceTopicTable($database);
			$newTopic = $refTopicTable->addRow();
			$newTopic->setValue("ID", $topicID);
			$newTopic->setValue("type", "topic");
			$newTopic->setValue("newWindow", false);
			$newTopic->setValue("value", $topic->getID());
			$newTopic->setValue("created", "system");
			$newTopic->store();

			$topicTable = new TopicTable($database);
			$refTopic = $topicTable->getRowByKey($topicID);
			$refTopic->setValue("date", $topic->getTime());
			$refTopic->setValue("lastReaction", $topic->lastReadTime());
			$refTopic->store();
		}

		function deleteTopic($id) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$refTopicTable = new ReferenceTopicTable($database);

			$removeID = $id;
			$refTopic = $refTopicTable->getRowByKey($removeID);
			$refTopic->delete();
			// all info is deleted, now clean up
			return true;
		}

	}

?>
