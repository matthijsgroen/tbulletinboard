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

	importClass("orm.DataObjects");

	/**
	 * Usefull for editing schedules
	 */
	class MessageSettingsTable extends DataTable {

		var $privateVars;
		private $settingsRow = null;

		function MessageSettingsTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "message_global");

			$this->defineInt("ID", "id", false);
			$this->setPrimaryKey("ID");
			$this->defineInt("settingID", "settingID", false);
			$this->defineInt("messageParentID", "messageParentID", false);

			$this->selectAll();
			$this->settingsRow = $this->getRow();
		}
		
		function editSettings($profileID) {
			if (!is_Object($this->settingsRow)) {
				$this->settingsRow = $this->addRow();
				$this->createMessageStorage();
			}
			$this->settingsRow->setValue("settingID", $profileID);
			$this->settingsRow->store();
		}			
		
		function getProfileID() {
			if (!is_Object($this->settingsRow)) return null;
			return $this->settingsRow->getValue("settingID");					
		}

		function getMessageParentID() {
			if (!is_Object($this->settingsRow)) return null;
			$id = $this->settingsRow->getValue("messageParentID");
			if ($id == 0) {
				$this->createMessageStorage();
				$this->settingsRow->store();
			}
			$id = $this->settingsRow->getValue("messageParentID");
			return $id;
		}
		
		private function createMessageStorage() {
			importBean("orm.Board");
			
			$boardTable = new BoardTable($this->getDatabase());
			$messageBoard = $boardTable->addRow();
			$messageBoard->setValue("parentID", 0);
			$messageBoard->setValue("name", "Message Storage");
			$messageBoard->setValue("comment", "This is a private message storage board");
			$messageBoard->setValue("read", 0);
			$messageBoard->setValue("write", 0);
			$messageBoard->setValue("topic", 0);
			$messageBoard->setValue("order", 0);
			$messageBoard->setValue("settingsID", $profileID);
			$messageBoard->setValue("views", 0);
			$messageBoard->setValue("type", "messages");
			$messageBoard->store();
			
			$this->settingsRow->setValue("messageParentID", $messageBoard->getValue("ID"));
		}
	}

?>
