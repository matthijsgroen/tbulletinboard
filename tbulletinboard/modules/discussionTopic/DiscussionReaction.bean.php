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

	class DiscussionReactionTable extends DataTable {
		var $privateVars;

		function DiscussionReactionTable(&$database) {
			$this->DataTable($database, $database->getTablePrefix() . "tm_discreaction");
			$this->defineInt("ID", "reactionID", false);
			$this->setPrimaryKey("ID");
			$this->defineInt("icon", "icon", false);
			$this->defineText("title", "title", 80, false);
			$this->defineText("message", "message", 2000000000, false);
			$this->defineBool("signature", "signature", false);
			$this->defineBool("smileys", "smilies", false);
			$this->defineBool("parseUrls", "parseurls", false);
		}
	}

?>
