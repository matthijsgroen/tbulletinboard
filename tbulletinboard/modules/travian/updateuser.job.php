<?php
    
    // Preferences
    $mysqlhost = 'localhost';
    $mysqluser = 'menhir_user';
    $mysqlpass = 'traviantest';
    $mysqldb = 'menhir_data';
    $path = '/home/menhir/public_html/upload/modules/travian/data';
    
    // Create database connection and select database
    $db = @mysql_connect($mysqlhost, $mysqluser, $mysqlpass) OR die('Can not connect to DB-Server!');
    $db_select = @mysql_select_db($mysqldb) OR die('Can not select DB!');
    
	$query = "SELECT * FROM `tbb_travian_user`";
	// Perform Query
	$result = mysql_query($query);

	// Check result
	// This shows the actual query sent to MySQL, and the error. Useful for debugging.
	if (!$result) {
		$message  = 'Invalid query: ' . mysql_error() . "\n";
		$message .= 'Whole query: ' . $query;
		die($message);
	}

	// Use result
	// Attempting to print $result won't allow access to information in the resource
	// One of the mysql result functions must be used
	// See also mysql_result(), mysql_fetch_array(), mysql_fetch_row(), etc.
	while ($row = mysql_fetch_assoc($result)) {
		$dumpResult = mysql_query(sprintf("SELECT * FROM x_world WHERE `uid`='%s'", $row['travianID']));

		$allianceID = "";
		$allianceName = "";
		$race = 0;
		$villages = 0;
		$population = 0;
		
		while ($travianRow = mysql_fetch_assoc($dumpResult)) {
			$allianceID = $travianRow['aid'];
			$allianceName = $travianRow['alliance'];
			$race = $travianRow['tid'];
			$villages++;
			$population += $travianRow['population'];
		}
		
		if ($dumpResult) {
			mysql_query(sprintf("UPDATE `tbb_travian_user` SET `allianceID` = '%s', `pop` = '%s', `vill` = '%s', `race` = '%s', `alliance` = '%s' ".
				"WHERE `ID` = '%s'",
				$allianceID, $population, $villages, $race, $allianceName, $row['ID']));
			mysql_free_result($dumpResult);
		}
	}

	// Free the resources associated with the result set
	// This is done automatically at the end of the script
	mysql_free_result($result);
    
    // Close database connection
    @mysql_close($db);

?> 
