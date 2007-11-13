<?php
	/**
	 * THAiSies Bulletin Board
	 * 2003 Rewrite
	 *
	 *@author Matthijs Groen (thaisi at servicez.org)
	 *@version 2.0
	 */

	require_once("folder.config.php");
	// Load the configuration
	require_once($TBBconfigDir.'configuration.php');
	require_once($TBBclassDir.'AvatarList.class.php');

	$avatarList = new AvatarList();
	if (!isSet($_GET['id'])) die('id parameter required');
	if (!is_numeric($_GET['id'])) die('id parameter must be a number');

	function getExtension($file){
		$extension = substr($file, strrpos($file, '.'));
		return $extension;
	}

	$avatarInfo = $avatarList->getLocalName($_GET['id']);
	$extension = getExtension($avatarInfo);
	$mimeType = "images/gif";
	switch ($extension) {
		case '.gif': $mimeType = "images/gif";
		case '.jpg': $mimeType = "images/jpeg";
		case '.png': $mimeType = "images/png";
	}


	header('Content-type: '.$mimeType);
	$download_size = filesize($avatarInfo);
	header("Content-type: application/x-download");
	header("Content-Disposition: attachment; filename=avatar".$extension.";");
	header("Accept-Ranges: bytes");
	header("Content-Length: $download_size");
	@readfile($avatarInfo);

?>