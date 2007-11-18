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


	header("Content-Type: image/gif\n");
	header("Content-Transfer-Encoding: binary");

	$pageTime = microTime();
	$procCounter = 0;
	$docRoot = '../../../';
	$TBBconfigDir = $docRoot . 'config/';
	$TBBincludeDir = $docRoot . 'include/';
	$TBBclassDir = $docRoot . 'classes/'; // always configure the classdir before loading the classes;
	$libraryClassDir = $docRoot . 'lib/';

	require_once($TBBconfigDir.'configuration.php');
	require_once("TravianFarm.bean.php");

	$image = "unfarm";

	$toggle = false;
	if (isSet($_GET['toggle']) && ($_GET['toggle'] == "true")) $toggle = true;

	$database = $TBBconfiguration->getDatabase();
	$farmTable = new TravianFarmTable($database);
	$filter = new DataFilter();
	$filter->addEquals("travianID", $_GET['id']);
	$farmTable->selectRows($filter, new ColumnSorting());
	if ($resultRow = $farmTable->getRow()) {
		if ($resultRow->getValue("farm")) {
			$image = "farm";
			if ($toggle) {
				$image = "nofarm";
				$resultRow->setValue("farm", false);
				$resultRow->store();
			}
		} else {
			$image = "nofarm";
			if ($toggle) {
				$image = "unfarm";
				$resultRow->delete();
			}
		}		
	} else {
		if ($toggle) {
			$image = "farm";
			$resultRow = $farmTable->addRow();
			$resultRow->setValue("farm", true);
			$resultRow->setValue("travianID", $_GET['id']);
			$resultRow->store();
		}
	}

	$fp=fopen("images/".$image.".gif" , "r");
	if ($fp)
		fpassthru($fp);

?>
