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

	importClass("interface.FormFields");	
	importClass("interface.Messages");	

	/**
	 * FileUpload handles the upload of files.
	 * This can include checking of image resolution when set.
	 * This class can create an uploadFormfield using the StandardFormFields class
	 */
	class FileUpload {

		var $privateVars;

		function FileUpload($name, $uploadDir, $title, $maxSizeKb) {
			$this->privateVars = array();
			$this->privateVars['name'] = $name;
			$this->privateVars['uploadDir'] = $uploadDir;
			$this->privateVars['title'] = $title;
			$this->privateVars['size'] = $maxSizeKb;
			$this->privateVars['randomName'] = false;
			$this->privateVars['extensions'] = array();
			$this->privateVars['mimeTypes'] = array();
			$this->privateVars['fileName'] = "";
			$this->privateVars['checkResolution'] = false;
			$this->privateVars['overwriteFile'] = "";
			$this->privateVars['forceFilename'] = "";
		}

		function setRandomName($prefix) {
			$this->privateVars['randomName'] = true;
			$this->privateVars['randomPrefix'] = $prefix;
		}

		function overwriteFile($filename) {
			$this->privateVars['overwriteFile'] = $filename;
		}

		function forceFilename($filename) {
			$this->privateVars['forceFilename'] = $filename;
		}

		function setExtensions($extensions) {
			$this->privateVars['extensions'] = func_get_args();
		}

		function setMimeTypes($mimeTypes) {
			$this->privateVars['mimeTypes'] = func_get_args();
		}

		function addFormField(&$formFields) {
			if ($this->privateVars['checkResolution']) {
				$formFields->addImageUploadField(
					$this->privateVars['name'],
					$this->privateVars['title'],
					implode(", ", $this->privateVars['extensions']),
					$this->privateVars['size'],
					$this->privateVars['maxX'],
					$this->privateVars['maxY']
				);
			} else {
				$formFields->addImageUploadField(
					$this->privateVars['name'],
					$this->privateVars['title'],
					implode(", ", $this->privateVars['extensions']),
					$this->privateVars['size']
				);
			}
		}

		function getExtension($file){
			$extension = substr($file, strrpos($file, '.'));
		  return $extension;
		}

		function fileChoosen() {
			$name = $this->privateVars['name'];
			global $HTTP_POST_FILES;
			return (isSet($HTTP_POST_FILES[$name]));
		}

		function checkUpload(&$feedback) {
			$name = $this->privateVars['name'];
			$size = $this->privateVars['size'];
			global $HTTP_POST_FILES;
			if (!isSet($HTTP_POST_FILES[$name])) {
				$feedback->addMessage('Geen bestand gekozen!');
				return false;
			}
			if (!is_uploaded_file($HTTP_POST_FILES[$name]['tmp_name'])) {
				$feedback->addMessage('Geen bestand geupload!');
				return false;
			}
			if ($HTTP_POST_FILES[$name]['size'] > ($size * 1024)) {
				$feedback->addMessage($this->privateVars['title'] . ' is te groot! ('.$HTTP_POST_FILES[$name]['size'].' bytes). Maximum grootte is '.$size.'kB');
				return false;
			}
			$validMimeTypes = $this->privateVars['mimeTypes'];
			$valid = false;
			for ($i = 0; $i < count($validMimeTypes); $i++) {
				if ($HTTP_POST_FILES[$name]['type'] == $validMimeTypes[$i])
					$valid = true;
			}
			if (!$valid) {
				$feedback->addMessage('Ongeldige Mime type! ('.$HTTP_POST_FILES[$name]['type'].')');
				return false;
			}
			if ($this->privateVars['checkResolution']) {
				list($width, $height, $type, $attr) = getimagesize($HTTP_POST_FILES[$name]['tmp_name']);

				if (($this->privateVars['maxX'] < $width) || ($this->privateVars['maxY'] < $height)) {
					$feedback->addMessage('Afbeelding is te groot! ('.$width.'x'.$height.'). De maximale grootte is: '.$this->privateVars['maxX'].'x'.$this->privateVars['maxY']);
					return false;
				}
			}
			if ((strLen($this->privateVars['overwriteFile']) == 0) && (strLen($this->privateVars['forceFilename']) == 0)) {
				if ((!$this->privateVars['randomName']) && file_exists($this->privateVars['uploadDir'] . $HTTP_POST_FILES[$name]['name'])) {
					$feedback->addMessage('Bestand bestaat al! ('.$HTTP_POST_FILES[$name]['name'].')');
					return false;
				}
			}
			$this->privateVars['fileName'] = $HTTP_POST_FILES[$name]['name'];
			if ($this->privateVars['randomName']) {
				$this->privateVars['fileName'] = uniqid($this->privateVars['randomPrefix']) . $this->getExtension($this->privateVars['uploadDir'] . $HTTP_POST_FILES[$name]['name']);
			}
			if (strLen($this->privateVars['forceFilename']) > 0) {
				$this->privateVars['fileName'] = $this->privateVars['forceFilename'];
			}
			$res = @move_uploaded_file($HTTP_POST_FILES[$name]['tmp_name'], $this->privateVars['uploadDir'] . $this->getFileName());
			if (!$res) {
				$feedback->addMessage('Bestand verplaatsen mislukt!');
				return false;
			}
			chmod($this->privateVars['uploadDir'] . $this->getFileName(), 0755);
			if (strLen($this->privateVars['overwriteFile']) > 0) {
				if ($this->privateVars['overwriteFile'] != $this->privateVars['fileName']) {
					if (file_exists($this->privateVars['uploadDir'] . $this->privateVars['overwriteFile']))
						unlink($this->privateVars['uploadDir'] . $this->privateVars['overwriteFile']);
				}
			}
			return true;
		}

		function getFileName() {
			return $this->privateVars['fileName'];
		}

		function setMaximumResolution($x, $y) {
			$this->privateVars['checkResolution'] = true;
			$this->privateVars['maxX'] = $x;
			$this->privateVars['maxY'] = $y;
		}
	}

?>
