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
