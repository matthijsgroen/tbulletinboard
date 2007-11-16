<?php ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Online file packer</title>
</head>
<body>
<?php
	$ivLibDir = "../lib/";
	require_once($ivLibDir . "PackFile.class.php");
	$dir = substr(__file__, 0, strrpos(__file__, "/"));

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
