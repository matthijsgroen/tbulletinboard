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

	class TopicPlugin extends ModulePlugin {

		var $active;
		var $default;

		var $privateVars;

		function TopicPlugin() {
			$this->ModulePlugin();
			$this->privateVars = array();
		}

		function setAddTopicForm(&$form, $currentStep, &$board) {
			$formFields = new StandardFormFields();
			$form->addFieldGroup($formFields);
			$formFields->activeForm = $form;
			$formFields->startGroup("Geen formulier gedefin&iuml;eerd!");
			$formFields->addText("Fout", "", "Er is geen formulier gedefin&iuml;eerd om onderwerpen toe te voegen. Waarschijnlijk is de onderwerp module incompleet.");
			$formFields->endGroup();
		}

		function handleAddTopicAction(&$feedback, &$board) {
			$feedback->addMessage("Geen actie afhandelingen gespecificeerd!");
			return false;
		}

		function hasMoreAddTopicSteps($currentStep) {
			return false;
		}

		function getSelectionName() {
			return $this->getModuleName();
		}

		function setEditTopicForm(&$form, $currentStep, &$topic) {
			$formFields = new StandardFormFields();
			$form->addFieldGroup($formFields);
			$formFields->activeForm = $form;
			$formFields->startGroup("Geen formulier gedefin&iuml;eerd!");
			$formFields->addText("Fout", "", "Er is geen formulier gedefin&iuml;eerd om het onderwerp te bewerken. Waarschijnlijk kunnen deze onderwerpen niet worden bewerkt.");
			$formFields->endGroup();
		}

		function handleEditTopicAction(&$feedback, &$topic) {
			$feedback->addMessage("Geen actie afhandelingen gespecificeerd!");
			return false;
		}

		function hasMoreEditTopicSteps($currentStep) {
			return false;
		}

		function hasTitleInfo(&$topic) {
			return false;
		}

		function getTitleInfo(&$topic) {
			return "";
		}

		function getTitlePrefix(&$topic) {
			return "";
		}

		function getFirstUnreadLink(&$topic) {
			return false;
		}

		function getTopicStateIcon(&$topic) {
			$onlineDir = $this->getModuleOnlineDir();
			$hot = $topic->isHot();
			$read = $topic->isRead();
			if ($topic->isLocked() && $read) return $onlineDir."icon_lock.gif";
			if ($topic->isLocked()) return $onlineDir."icon_newlock.gif";
			if ($hot && $read) return $onlineDir."icon_hot.gif";
			if ($hot) return $onlineDir."icon_hotnew.gif";
			if ($read) return $onlineDir."icon_normal.gif";
			return $onlineDir."icon_new.gif";
		}

		function openNewFrame(&$topic) {
			return false;
		}

		function showTopic(&$topic, $pageNr, $options=array()) {
		}

		function getLastPostDate(&$topic, $pageNr) {
			return false;
		}

		function addReactionForm(&$form, $currentStep, &$topic) {
		}

		function editReactionForm(&$form, $currentStep, &$topic, $postID) {
		}

		function handleAddReactionAction(&$feedback, &$topic) {
			$feedback->addMessage("Geen actie afhandelingen gespecificeerd!");
			return false;
		}

		function handleEditReactionAction(&$feedback, &$topic) {
			$feedback->addMessage("Geen actie afhandelingen gespecificeerd!");
			return false;
		}

		function removeTopics(&$board) {
		}

		function install($moduleID) {
			return true;
		}

		function deleteTopic($id) {
			return false;
		}

		function searchText(&$searchResult, $startPeriod, $endPeriod, $text, $locations, $user, &$feedback) {
			return true;
		}

	}

?>
