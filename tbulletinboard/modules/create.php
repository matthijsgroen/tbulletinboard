<?php ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Module download</title>
</head>
<body>
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
	$TBBclassDir = "../classes/";
	require_once($TBBclassDir . "library.php");
	importClass("util.PackFile");
	$filename = str_replace("\\", "/", __file__);
	$dir = substr($filename, 0, strrpos($filename, "/"));

	print 'scanning: '.$dir."<br />";
	// open the current directory by opendir
	$handle = opendir($dir);

	print "<ul>";
	while (($file = readdir($handle))!==false) {
		if (is_dir($file) && (strpos($file, ".") !== 0)) {
			echo "<li><a href=\"../upload/modules/".$file.".tbbmod\">".$file.".tbbmod</a></li>";
			
			$packFile = new PackFile("tbbmod");
			$packFile->addFolder($dir . "/" . $file);
			$packFile->save($dir."/../upload/modules/".$file.'.tbbmod');
		}
	}
	print "</ul>";

	closedir($handle);

?>
</body>
</html>
