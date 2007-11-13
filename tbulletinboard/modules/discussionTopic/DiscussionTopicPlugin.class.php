<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
	 */
	$ivLibDir = $GLOBALS['ivLibDir'];
	$TBBclassDir = $GLOBALS['TBBclassDir'];

	require_once($ivLibDir . 'PageNavigation.class.php');
	require_once($ivLibDir . 'TextParser.class.php');
	require_once($ivLibDir . 'Table.class.php');
	require_once($ivLibDir . 'Form.class.php');
	require_once($ivLibDir . 'FormFields.class.php');
	require_once($ivLibDir . 'Menu.class.php');
	require_once($ivLibDir . 'DataObjects.class.php');
	require_once($TBBclassDir . 'Board.class.php');
	require_once($TBBclassDir . 'Topic.bean.php');
	require_once($TBBclassDir . 'Reaction.bean.php');
	require_once($TBBclassDir . 'Buttonbar.class.php');
	require_once($TBBclassDir . 'BoardFormFields.class.php');
	require_once($TBBclassDir . 'TBBEmoticonList.class.php');
	require_once($moduleDir . "DiscussionReaction.bean.php");
	require_once($moduleDir . "DiscussionReaction.class.php");
	require_once($moduleDir . "DiscussionTopic.class.php");
	require_once($moduleDir . "DiscussionTopic.bean.php");
	require_once($TBBclassDir . 'TopicPlugin.class.php');

	class DiscussionTopicPlugin extends TopicPlugin {

		function DiscussionTopicPlugin() {
			$this->TopicPlugin();
		}

		function getSelectionName() {
			return "Discussie";
		}

		function setAddTopicForm(&$form, $currentStep, &$board) {
			if ($currentStep > 0) return; // no forms for that
			$formFields = new StandardFormFields();
			$form->addFieldGroup($formFields);
			$formFields->activeForm = $form;

			$boardFormFields = new BoardFormFields();
			$form->addFieldGroup($boardFormFields);
			$boardFormFields->activeForm = $form;

			$formFields->startGroup("Nieuwe discussie starten");
			$formFields->addTextField("title", "Titel", "Titel van het onderwerp", 80);
			$boardFormFields->addIconBar("icon", "Pictogram", "pictogram van het onderwerp");
			$boardFormFields->addPostTextField("post", "Bericht", "", true, true, true);
			$options = array(
				array("value" => "yes", "caption" => "Maak URLs automatisch", "description" => "zet automatisch [url] en [/url] om een internet adres", "name" => "autoUrl", "checked" => true),
				array("value" => "yes", "caption" => "Geen smiles in dit bericht", "description" => "", "name" => "noSmile", "checked" => false),
				array("value" => "yes", "caption" => "Toon handtekening", "description" => "zet je handtekening onder dit bericht", "name" => "signature", "checked" => true)
				//array("value" => "yes", "caption" => "Bericht bij reacties", "description" => "reakties op dit bericht per email ontvangen", "name" => "subscribe", "checked" => false),
				//array("value" => "yes", "caption" => "Opslaan als concept", "description" => "Bericht niet plaatsen maar opslaan als concept", "name" => "concept", "checked" => false)
			);
			$formFields->addCheckboxes("Opties", "", $options);
			$formFields->endGroup();
			$formFields->addSubmit("Plaatsen", false);
		}

		function handleAddTopicAction(&$feedback, &$board) {
			if (strlen(trim($_POST['title'])) == 0) {
				$feedback->addMessage('Titel is verplicht!');
				return false;
			}

			$state = "online";
			$closed = "no";
			$special = "no";
			global $TBBcurrentUser;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$topicID = $board->addTopic($TBBcurrentUser, trim($_POST['title']), $_POST['icon'], $this->getModuleName(), $state, $closed, $special);
			if ($topicID == false) {
				$feedback->addMessage("Fout bij plaatsen van onderwerp in database!");
				return false;
			}
			$parseUrls = isSet($_POST['autoUrl']) ? true : false;
			$smilies = isSet($_POST['noSmile']) ? false : true;
			$signature = isSet($_POST['signature']) ? true : false;

			$discTopicTable = new DiscussionTopicTable($database);
			$newTopic = $discTopicTable->addRow();
			$newTopic->setValue("ID", $topicID);
			$newTopic->setValue("message", $_POST['post']);
			$newTopic->setValue("signature", $signature);
			$newTopic->setValue("smileys", $smilies);
			$newTopic->setValue("parseUrls", $parseUrls);
			$newTopic->store();
			return true;
		}

		function hasMoreAddTopicSteps($currentStep) {
			return false;
		}

		function setEditTopicForm(&$form, $currentStep, &$topic) {
			if ($currentStep > 1) return; // no forms for that

			$discTopic = new DiscussionTopic($topic, $this);

			$formFields = new StandardFormFields();
			$form->addFieldGroup($formFields);
			$formFields->activeForm = $form;

			$boardFormFields = new BoardFormFields();
			$form->addFieldGroup($boardFormFields);
			$boardFormFields->activeForm = $form;

			$formFields->startGroup("Discussie bewerken");
			$formFields->addTextField("title", "Titel", "Titel van het onderwerp", 80);
			$boardFormFields->addIconBar("icon", "Pictogram", "pictogram van het onderwerp");
			$boardFormFields->addPostTextField("post", "Bericht", "", true, true, true);
			$options = array(
				array("value" => "yes", "caption" => "Maak URLs automatisch", "description" => "zet automatisch [url] en [/url] om een internet adres", "name" => "autoUrl", "checked" => true),
				array("value" => "yes", "caption" => "Geen smiles in dit bericht", "description" => "", "name" => "noSmile", "checked" => false),
				array("value" => "yes", "caption" => "Toon handtekening", "description" => "zet je handtekening onder dit bericht", "name" => "signature", "checked" => true)
				//array("value" => "yes", "caption" => "Bericht bij reacties", "description" => "reakties op dit bericht per email ontvangen", "name" => "subscribe", "checked" => false),
				//array("value" => "yes", "caption" => "Opslaan als concept", "description" => "Bericht niet plaatsen maar opslaan als concept", "name" => "concept", "checked" => false)
			);
			$formFields->addCheckboxes("Opties", "", $options);
			$formFields->endGroup();
			$formFields->addSubmit("Bewerken", true);

			$form->setValue("title", htmlConvert($discTopic->getTitle()));
			$form->setValue("post", htmlConvert($discTopic->getTopicText()));
			$form->setValue("icon", $discTopic->getIconID());
			//$form->setValue("noSmile", ($action->smiliesOn()) ? fakse : "yes");
			$form->setValue("signature", $discTopic->hasSignature() ? "yes" : false);
		}

		function handleEditTopicAction(&$feedback, &$topic) {
			if (strlen(trim($_POST['title'])) == 0) {
				$feedback->addMessage('Titel is verplicht!');
				return false;
			}

			//$state = "online";
			//$closed = "no";
			//$special = "no";
			global $TBBcurrentUser;
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();

			$success = $topic->editTopicNameIcon(trim($_POST['title']), $_POST['icon']);
			if ($success == false) {
				$feedback->addMessage("Fout bij bewerken van onderwerp in database!");
				return false;
			}
			$parseUrls = isSet($_POST['autoUrl']) ? true : false;
			$smilies = isSet($_POST['noSmile']) ? false : true;
			$signature = isSet($_POST['signature']) ? true : false;

			$discTopicTable = new DiscussionTopicTable($database);
			$discTopic = $discTopicTable->getRowByKey($topic->getID());
			$discTopic->setValue("message", $_POST['post']);
			$discTopic->setValue("signature", $signature);
			$discTopic->setValue("smileys", $smilies);
			$discTopic->setValue("parseUrls", $parseUrls);
			$discTopic->setValue("lastChange", new LibDateTime());
			$discTopic->setValue("changeBy", $TBBcurrentUser->getUserID());
			$discTopic->store();
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

		function getTitleInfo(&$topic) {
			global $TBBcurrentUser;
			global $TBBconfiguration;

			$nrPosts = $topic->getNrPost();
			$nrPages = ceil(($nrPosts +1) / $TBBcurrentUser->getReactionsPerPage());
			$pageNavigation = new PageNavigation($nrPages, -1, "topic.php?pageNr=%s&amp;id=".$topic->getID(), 5);

			return "<br />".$pageNavigation->quickPageBarStr("topicPageNav", $TBBconfiguration->imageOnlineDir."multipage.gif");
		}

		function getLastPostDate(&$topic, $pageNr) {
			if (!isSet($this->privateVars['lastPostDate'])) return false;
			return $this->privateVars['lastPostDate'];
		}

		function showTopic(&$topic, $pageNr, $options=array()) {
			global $TBBcurrentUser;
			global $TBBconfiguration;

			global $ivLibDir;
			require_once($ivLibDir."TextParser.class.php");
			require_once($ivLibDir . 'PageNavigation.class.php');
			require_once($ivLibDir . 'Table.class.php');
			global $TBBclassDir;
			require_once($TBBclassDir . 'Buttonbar.class.php');
			$moduleDir = $this->getModuleDir();
			require_once($moduleDir . 'DiscussionTopic.class.php');

			$textParser = new TextParser();
			global $TBBemoticonList;

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

			$discussionTopic = new DiscussionTopic($topic, $this);
			$reactions = array();

			$boardProfile = $board->getBoardSettings();
			$tbbTags = $boardProfile->getTBBtagList();
			$emoticons = $discussionTopic->smiliesOn() ? $TBBemoticonList : false;
			if (!$TBBcurrentUser->showEmoticons()) $emoticons = false;

			if ($pageNr == 0) {
				$starter = $discussionTopic->getStarter();
				$iconInfo = $topic->getIconInfo();

				$canEdit = false;
				if ($TBBcurrentUser->isActiveAdmin()) $canEdit = true;
				if ((!$topic->isLocked()) && ($starter->isCurrentUser())) $canEdit = true;

				$toolbar = new Menu();

				if ($canEdit) {
					$toolbar->addItem('edit','', 'bewerken', 'edittopic.php?id='.$topic->getID(), '', $TBBconfiguration->imageOnlineDir.'edit.gif', 0, false, '');
				}
				
				$underPost = "";
				if ($discussionTopic->isEdited()) {
					$editor = $discussionTopic->editedBy();
					$underPost = sprintf(
						'<p class="edited">bewerkt door %s op %s</p>',
						htmlConvert($editor->getNickname()),
						$TBBconfiguration->parseDate($discussionTopic->lastChange())
					);
				}
				if ($TBBcurrentUser->showSignatures() && $TBBconfiguration->getSignaturesAllowed() && $boardProfile->allowSignatures()) {
					if ($discussionTopic->hasSignature()) {
						$underPost .= sprintf('<div class="signature">%s</div>',
							$starter->getSignatureHTML()
						);
					}
				}
				$readIcon = '<img src="images/posticon.gif" alt="gelezen" /> ';
				if ($lastRead->before($discussionTopic->getTime())) {
					$readIcon = '<img src="images/posticonnew.gif" alt="ongelezen" /> ';
				}
				$posts->addRow(
					sprintf(
						'<span class="author">%s</span><br />%s',
						htmlConvert($starter->getNickName()),
						$TBBconfiguration->getUserInfoBlock($starter)
					),
					sprintf(
						'<h4>%s%s</h4><p class="messageText">%s</p>%s',
						($discussionTopic->hasIcon()) ? '<img src="'.$iconInfo['imgUrl'].'" title="'.$iconInfo['name'].'" alt="" /> ' : "",
						$textParser->breakLongWords(htmlConvert($discussionTopic->getTitle()), 40, $highlights),
						$textParser->parseMessageText($discussionTopic->getTopicText(), $emoticons, $tbbTags, $highlights),
						$underPost
					),
					$readIcon .
					$TBBconfiguration->parseDate($discussionTopic->getTime()),
					$toolbar->getMenuStr("ptoolbar")
				);
				$this->privateVars['lastPostDate'] = $discussionTopic->getTime();


				$reactions = $discussionTopic->getReactions(0, $TBBcurrentUser->getReactionsPerPage() - 1);
			} else {
				$reactions = $discussionTopic->getReactions(($pageNr * $TBBcurrentUser->getReactionsPerPage()) -1, $TBBcurrentUser->getReactionsPerPage());
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
					$toolbar->addItem('edit','', 'bewerken', 'editreply.php?topicID='.$topic->getID().'&amp;edit=yes&amp;postID='.$reaction->getID(), '', $TBBconfiguration->imageOnlineDir.'edit.gif', 0, false, '');
				}
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

				$posts->addRow(
					sprintf(
						'<a name="post%s" />%s<span class="author">%s</span><br />%s',
						$reaction->getID(),
						(($i + $startPost) == ($nrPosts-2)) ? '<a name="lastpost" />' : '',
						htmlConvert($starter->getNickName()),
						$TBBconfiguration->getUserInfoBlock($starter)
					),
					sprintf(
						'<h4>%s%s</h4><p class="messageText">%s</p>%s',
						($reaction->hasIcon()) ? '<img src="'.$iconInfo['imgUrl'].'" title="'.$iconInfo['name'].'" alt="" /> ' : "",
						$textParser->breakLongWords(htmlConvert($reaction->getTitle()), 40, $highlights),
						$textParser->parseMessageText($reaction->getMessage(), $emoticons, $tbbTags, $highlights),
						$underPost
					),
					$readIcon .
					$TBBconfiguration->parseDate($reaction->getTime()),
					$toolbar->getMenuStr("ptoolbar")
				);
			}
			$posts->showTable();
			$buttonBar->showBar();
			$pageNavigation->showPagebar("botPageBar");
?>
	</div>
</div>
<?php
		}

		function addReactionForm(&$form, $currentStep, &$topic) {
			$formFields = new StandardFormFields();
			$form->addFieldGroup($formFields);
			$formFields->activeForm = $form;

			$boardFormFields = new BoardFormFields();
			$form->addFieldGroup($boardFormFields);
			$boardFormFields->activeForm = $form;

			$formFields->startGroup("Reactie plaatsen");
			$formFields->addTextField("title", "Titel", "Titel van het onderwerp", 80);

			$boardFormFields->addIconBar("icon", "Pictogram", "pictogram van het onderwerp");
			$boardFormFields->addPostTextField("post", "Bericht", "", true, true, true);
			$options = array(
				array("value" => "yes", "caption" => "Maak URLs automatisch", "description" => "zet automatisch [url] en [/url] om een internet adres", "name" => "autoUrl", "checked" => true),
				array("value" => "yes", "caption" => "Geen smiles in dit bericht", "description" => "", "name" => "noSmile", "checked" => false),
				array("value" => "yes", "caption" => "Toon handtekening", "description" => "zet je handtekening onder dit bericht", "name" => "signature", "checked" => true)
				//array("value" => "yes", "caption" => "Bericht bij reacties", "description" => "reakties op dit bericht per email ontvangen", "name" => "subscribe", "checked" => false)
				//array("value" => "yes", "caption" => "Opslaan als concept", "description" => "Bericht niet plaatsen maar opslaan als concept", "name" => "concept", "checked" => false)
			);
			$formFields->addCheckboxes("Opties", "", $options);
			$formFields->addSubmit("Plaatsen", true);
			$formFields->endGroup();
		}

		function editReactionForm(&$form, $currentStep, &$topic, $postID) {
			global $textParser;

			$discussionTopic = new DiscussionTopic($topic, $this);
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
			$form->setValue("noSmile", ($reaction->smiliesOn()) ? false : "yes");
			$form->setValue("signature", ($reaction->hasSignature()) ? "yes" : false);
		}

		function handleAddReactionAction(&$feedback, &$topic) {
			$state = "online";
			global $TBBcurrentUser;
			global $TBBsession;
			global $TBBconfiguration;

			$reactionID = $topic->addReaction($TBBcurrentUser, $state);
			if ($reactionID == false) {
				$feedback->addMessage("Fout bij plaatsen van reactie in database!");
				return false;
			}
			$parseUrls = isSet($_POST['autoUrl']) ? true : false;
			$smilies = isSet($_POST['noSmile']) ? false : true;
			$signature = isSet($_POST['signature']) ? true : false;

			$database = $TBBconfiguration->getDatabase();
			$discReactionTable = new DiscussionReactionTable($database);
			$newReaction = $discReactionTable->addRow();
			$newReaction->setValue("ID", $reactionID);
			$newReaction->setValue("icon", $_POST['icon']);
			$newReaction->setValue("title", $_POST['title']);
			$newReaction->setValue("message", $_POST['post']);
			$newReaction->setValue("signature", $signature);
			$newReaction->setValue("smileys", $smilies);
			$newReaction->setValue("parseUrls", $parseUrls);
			$newReaction->store();

			$TBBsession->actionHandled();
			$TBBconfiguration->redirectUri('topic.php?id='.$topic->getID().'&goto=lastpost#lastpost');
			return true;
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

			$discReactionTable = new DiscussionReactionTable($database);
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

		function deleteTopic($id) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$discTopicTable = new DiscussionTopicTable($database);
			$discReactionTable = new DiscussionReactionTable($database);
			$reactionTable = new ReactionTable($database);

			$removeID = $id;
			$topicFilter = new DataFilter();
			$topicFilter->addEquals("topicID", $removeID);

			$reactionFilter = new DataFilter();
			$reactionFilter->addJoinDataFilter("ID", "ID", $reactionTable, $topicFilter);

			$discReactionTable->deleteRows($reactionFilter);

			$discTopic = $discTopicTable->getRowByKey($removeID);
			if (is_Object($discTopic)) $discTopic->delete();
			return true;
		}

		function searchText(&$searchResult, $startPeriod, $endPeriod, $searchText, $searchLocations, $searchUser, &$feedback) {
			global $TBBconfiguration;
			$database = $TBBconfiguration->getDatabase();
			$searchWords = explode(" ", $searchText);
			$textQuery = "";
			for ($i = 0; $i < count($searchWords); $i++) {
				$textQuery .= "+".addSlashes($searchWords[$i])."* ";
			}
			$textQuery = trim($textQuery);

			$messageTextQuery = sprintf(
				"SELECT ".
					"SUM(MATCH(`tbb_tm_discreaction`.`title`, `tbb_tm_discreaction`.`message`) AGAINST ('%1\$s')) as 'messageScore', ".
					"MATCH(`tbb_tm_disctopic`.`message`) AGAINST ('%1\$s') as 'topicScore', ".
					"MATCH(`tbb_topic`.`title`) AGAINST ('%1\$s') as 'titleScore', ".
					"`tbb_topic`.`ID`, `tbb_topic`.`title`, `tbb_topic`.`boardID`, `tbb_board`.`name`,`tbb_topic`.`poster`, ".
					"`tbb_topic`.`lastReaction`, `tbb_users`.`nickname` ".
				"FROM `tbb_topic` ".
					"LEFT JOIN `tbb_tm_disctopic` ON `tbb_topic`.`ID` = `tbb_tm_disctopic`.`topicID` ".
					"LEFT JOIN `tbb_reaction` ON `tbb_topic`.`ID` = `tbb_reaction`.`topicID` ".
					"LEFT JOIN `tbb_board` ON `tbb_topic`.`boardID` = `tbb_board`.`ID` ".
					"LEFT JOIN `tbb_users` ON `tbb_topic`.`poster` = `tbb_users`.`ID` ".
					"LEFT JOIN `tbb_tm_discreaction` ON `tbb_tm_discreaction`.`reactionID` = `tbb_reaction`.`ID` ".
				"WHERE ".
					"(%3\$s)  AND ".
					"(`tbb_topic`.`date` < '%4\$s' AND `tbb_topic`.`lastReaction` > '%5\$s')  AND ".
					"((MATCH(`tbb_tm_discreaction`.`title`, `tbb_tm_discreaction`.`message`) AGAINST ('%2\$s' IN BOOLEAN MODE) AND ".
					"`tbb_tm_discreaction`.`message` IS NOT NULL) OR ".
					"MATCH(`tbb_tm_disctopic`.`message`) AGAINST ('%2\$s' IN BOOLEAN MODE) OR ".
					"MATCH(`tbb_topic`.`title`) AGAINST ('%2\$s' IN BOOLEAN MODE)) ".
				"GROUP BY `tbb_topic`.`ID`",

				addSlashes($searchText),
				$textQuery,
				"`tbb_topic`.`boardID` = '".implode("' OR `tbb_topic`.`boardID` = '", $searchLocations)."'",
				$startPeriod->toString("Y-m-d H:i:s"),
				$endPeriod->toString("Y-m-d H:i:s")
			);
			$resultSet = $database->executeQuery($messageTextQuery);

			while ($row = $resultSet->getRow()) {
				$relevance = $row['messageScore'] + ($row['topicScore'] * 1.1) + ($row['titleScore'] * 1.3);
				$topicID = stripSlashes($row['ID']);
				$topicTitle = stripSlashes($row['title']);
				$boardID = stripSlashes($row['boardID']);
				$boardTitle = stripSlashes($row['name']);
				$posterUser = stripSlashes($row['poster']);
				$posterNick = stripSlashes($row['nickname']);

				list($date, $time) = explode(" ", $row['lastReaction']);
				list($year, $month, $dayOfMonth) = explode("-", $date);
				list($hour, $minute, $second) = explode(":", $time);
				$lastReaction = new LibDateTime($hour, $minute, $second, $month, $dayOfMonth, $year);

				$searchResult->addResultRow(
					$relevance,
					$topicTitle,
					sprintf('<a href="topic.php?id=%s&amp;highlight=%s">%s</a>', $topicID, $searchText, $topicTitle),
					$posterNick,
					sprintf('<a href="user.php?id=%s">%s</a>', $posterUser, $posterNick),
					$boardTitle,
					sprintf('<a href="index.php?id=%s">%s</a>', $boardID, $boardTitle),
					$lastReaction,
					$TBBconfiguration->parseDate($lastReaction)
				);
			}
			return true;
		}


	}

?>
