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

	class PackFile {

		var $privateVars;

		function PackFile($mime = "ivinity/package") {
			$this->privateVars = array();
			$this->privateVars['header'] = 'ivPack'."\x06";
			$this->privateVars['mime'] = $mime;
			$this->privateVars['contents'] = array();
		}

		function getMime() {
			return $this->privateVars['mime'];
		}

		function setMime($mime) {
			$this->privateVars['mime'];
		}

		function addFile($filename, $archiveFolder = ".") {
			$filename = str_replace("\\", "/", $filename);
			if (!file_Exists($filename)) return false;
			if (strpos($filename, "~") == (strlen($filename) -1)) return false;
			$handle = fOpen($filename, "r");
			$contents = fRead($handle, fileSize($filename));
			$compressed = gzCompress($contents, 9);
			$name = explode('/', $filename);
			if ($archiveFolder == ".") $archFolder = "";
			else $archFolder = $archiveFolder . "/";
			//print "adding: " . $archFolder . $name[count($name)-1] . "<br />\n";
			$this->privateVars['contents'][] = array(
				'filename' => $archFolder . $name[count($name)-1],
				'original_size' => fileSize($filename),
				'compress_size' => strLen($compressed),
				'data' => $compressed
			);
			fClose($handle);
			return true;
		}

		function addFolder($folder, $archiveFolder = ".") {
			$folder = str_replace("\\", "/", $folder);
			if (is_dir($folder)) {
				if ($dh = opendir($folder)) {
					while (($file = readdir($dh)) !== false) {
						if (($file != ".") && ($file != "..")) {
							if (is_dir($folder."/".$file)) {
								if (strpos($file, ".") !== 0) {
									$subFolder = $archiveFolder . "/" . $file;
									if ($archiveFolder == ".") $subFolder = $file;
									$this->addFolder($folder . "/" . $file, $subFolder);
								}
							} else {
								$this->addFile($folder . "/" . $file, $archiveFolder);
							}
						}
					}
					closedir($dh);
				}
			}

			return true;
		}

		function delFile($filename) {
			$fileIndex = $this->p_getIndexOfFilename($filename);
			if ($fileIndex === false) return false;
			$startAr = array_slice($this->privateVars['contents'], 0, $fileIndex);
			$endAr = array_slice($this->privateVars['contents'], $fileIndex+1);
			$this->privateVars['contents'] = array_merge($startAr, $endAr);
			return true;
		}

		function hasFile($filename) {
			if ($this->p_getIndexOfFilename($filename) === false) return false;
			return true;
		}

		function p_getIndexOfFilename($filename) {
			for ($i = 0; $i < count($this->privateVars['contents']); $i++) {
				$found = $this->privateVars['contents'][$i];
				if (strCaseCmp($found['filename'], $filename) == 0) return $i;
			}
			return false;
		}

		function saveFile($filename, $folder) {
			$fileIndex = $this->p_getIndexOfFilename($filename);
			if ($fileIndex === false) return false;
			$fileInfo = $this->privateVars['contents'][$fileIndex];
			$fileFolder = explode("/", $filename);
			$plainFilename = array_pop($fileFolder);
			if (count($fileFolder) > 0) {
				foreach($fileFolder as $subFolder) {
					@mkDir($folder);
					$folder .= "/" . $subFolder;
				}
			}

			@mkDir($folder);
			$handle = fOpen($folder.'/'.$plainFilename, "w");
			$uncompress = gzUncompress($fileInfo['data']);
			fWrite($handle, $uncompress, $fileInfo['original_size']);
			fClose($handle);
			return true;
		}

		function saveAllFiles($folder) {
			for ($i = 0; $i < count($this->privateVars['contents']); $i++) {
				$found = $this->privateVars['contents'][$i];
				if (!$this->saveFile($found['filename'], $folder)) return false;
			}
			return true;
		}

		function getIndex() {
			$indexArray = array();
			$filePos = 0;
			for ($i = 0; $i < count($this->privateVars['contents']); $i++) {
				$found = $this->privateVars['contents'][$i];
				$indexArray[] = array(
					'filename' => $found['filename'],
					'original_size' => $found['original_size'],
					'compress_size' => $found['compress_size'],
					'startPos' => $filePos
				);
				$filePos += $found['compress_size'];
			}
			return $indexArray;
		}

		function save($filename) {
			$handle = fOpen($filename, "w");
			$fileHeader = $this->privateVars['header'];
			$mimeCompress = $this->privateVars['mime'];
			$fileHeader .= pack("V", strLen($mimeCompress));
			$fileHeader .= $mimeCompress;

			fWrite($handle, $fileHeader, strLen($fileHeader));

			$index = serialize($this->getIndex());
			$index = gzCompress($index);
			$indexLength = pack("V", strLen($index));
			fWrite($handle, $indexLength, strLen($indexLength));
			fWrite($handle, $index, strLen($index));
			for ($i = 0; $i < count($this->privateVars['contents']); $i++) {
				$found = $this->privateVars['contents'][$i];
				fWrite($handle, $found['data'], strLen($found['data']));
			}
			fClose($handle);
			return true;
		}

		function load($filename) {
			if (!file_Exists($filename)) return false;
			$handle = fOpen($filename, "r");
			$formatHeader = $this->privateVars['header'];
			$fileHeader = fRead($handle, strLen($formatHeader));
			if (strCmp($fileHeader, $formatHeader) != 0) {
				fClose($handle);
				return false;
			}
			$mimetmp = pack("V", 1);
			$mimeLength = unPack("C".strLen($mimetmp)."length", fRead($handle, strLen($mimetmp)));
			$mlength = 0; $factor = 1;
			for ($i = 0; $i < count($mimeLength); $i++) {
				$mlength += $mimeLength['length'.($i+1)] * $factor;
				$factor = $factor * 256;
			}
			$mime = fRead($handle, $mlength);
			$this->privateVars['mime'] = $mime;

			$tmp = pack("V", 1);
			$headerLength = unPack("C".strLen($tmp)."length", fRead($handle, strLen($tmp)));
			$length = 0; $factor = 1;
			for ($i = 0; $i < count($headerLength); $i++) {
				$length += $headerLength['length'.($i+1)] * $factor;
				$factor = $factor * 256;
			}
			$index = fRead($handle, $length);
			$index = gzUncompress($index);
			$index = unSerialize($index);
			$startPos = strLen($fileHeader) + strLen($mimetmp) + $mlength + strLen($tmp) + $length;
			for ($i = 0; $i < count($index); $i++) {
				$fileInfo = $index[$i];

				$fileStartPos = $fileInfo['startPos'];
				$size = $fileInfo['compress_size'];

				fSeek($handle, $startPos + $fileStartPos);
				$data = fRead($handle, $size);

				$this->privateVars['contents'][] = array(
					'filename' => $fileInfo['filename'],
					'original_size' => $fileInfo['original_size'],
					'compress_size' => $fileInfo['compress_size'],
					'data' => $data
				);
			}
			fClose($handle);
			return true;
		}

	}

?>
