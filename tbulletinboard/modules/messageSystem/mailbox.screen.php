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


	$menu = new Menu();
	$menu->addItem("new", "", "Bericht opstellen", "panelplugin.php?id=".$this->getModuleName()."&screen=compose", "", "", 0, false, '');
	$menu->addItem("open", "", "Openen", "panelplugin.php?id=".$this->getModuleName()."&screen=message", "", "", 0, false, '');
	$menu->addItem("delete", "", "Verwijderen", "", "", "", 0, false, '');
	$menu->showMenu('toolbar');
			
	$messages = new Table();
	$messages->setHeader("ID", "Read", "Icon", "Subject", "Sender", "Time");
	$messages->hideColumn(0);
	
	$messages->addRow("1", "*", ":-)", "Test mailtje!", "Matthijs Groen", "2 mins ago");
	
	print $messages->showTable();

?>
