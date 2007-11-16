<?php

	header("Content-Type: image/gif\n");
	header("Content-Transfer-Encoding: binary");

	$pageTime = microTime();
	$procCounter = 0;
	$docRoot = '../../../';
	$TBBconfigDir = $docRoot . 'config/';
	$TBBincludeDir = $docRoot . 'include/';
	$TBBclassDir = $docRoot . 'classes/'; // always configure the classdir before loading the classes;
	$ivLibDir = $docRoot . 'lib/';

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
