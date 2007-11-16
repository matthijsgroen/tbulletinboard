<?php
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
