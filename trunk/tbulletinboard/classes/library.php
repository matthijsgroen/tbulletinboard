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

	function importClass($classPath) {
		global $TBBclassDir;
		$path = $TBBclassDir . str_replace(".", "/", $classPath) . ".class.php";
		if (!file_exists($path)) {
			$trace = debug_backtrace();
			die("'".$classPath . "' not found in <b>" . $trace[0]["file"]."</b> on line <b>" . $trace[0]["line"]."</b>");
		}
		require_once($path);
	}

	function importBean($classPath) {
		global $TBBclassDir;
		require_once($TBBclassDir . str_replace(".", "/", $classPath) . ".bean.php");
	}


	/**
	 * Checks if the given enitity is valid in the XHTML 1.0 specs.
	 *
	 * @param string $entity the entity to test for validity
	 * @return bool true if the given entity is a correct XHTML 1.0 entity, false otherwise
	 */
	function isEntity($entity) {
		$test = html_entity_decode($entity);
		$test = htmlEntities($test);
		if ($test == $entity) return true;
		$entityList = array(
			"&fnof;", "&Alpha;", "&Beta;", "&Gamma;", "&Delta;", "&Epsilon;", "&Zeta;", "&Eta;", "&Theta;", "&Iota;", "&Kappa;",
			"&Lambda;", "&Mu;", "&Nu;", "&Xi;", "&Omicron;", "&Pi;", "&Rho;", "&Sigma;", "&Tau;", "&Upsilon;", "&Phi;", "&Chi;",
			"&Psi;", "&Omega;", "&alpha;", "&beta;", "&gamma;", "&delta;", "&epsilon;", "&zeta;", "&eta;", "&theta;", "&iota;",
			"&kappa;", "&lambda;", "&mu;", "&nu;", "&xi;", "&omicron;", "&pi;", "&rho;", "&sigmaf;", "&sigma;", "&tau;",
			"&upsilon;", "&phi;", "&chi;", "&psi;", "&omega;", "&thetasym;", "&upsih;", "&piv;", "&bull;", "&hellip;", "&prime;",
			"&Prime;", "&oline;", "&frasl;", "&weierp;", "&image;", "&real;", "&trade;", "&alefsym;", "&larr;", "&uarr;", "&rarr;",
			"&darr;", "&harr;", "&crarr;", "&lArr;", "&uArr;", "&rArr;", "&dArr;", "&hArr;", "&forall;", "&part;", "&exist;", "&empty;",
			"&nabla;", "&isin;", "&notin;", "&ni;", "&prod;", "&sum;", "&minus;", "&lowast;", "&radic;", "&prop;", "&infin;",
			"&ang;", "&and;", "&or;", "&cap;", "&cup;", "&int;", "&there4;", "&sim;", "&cong;", "&asymp;", "&ne;", "&equiv;", "&le;",
			"&ge;", "&sub;", "&sup;", "&nsub;", "&sube;", "&supe;", "&oplus;", "&otimes;", "&perp;", "&sdot;", "&lceil;", "&rceil;",
			"&lfloor;", "&rfloor;", "&lang;", "&rang;", "&loz;", "&spades;", "&clubs;", "&hearts;", "&diams;",

			"&quot;", "&amp;", /*"&apos;",*/"&#39;", "&lt;", "&gt;", "&OElig;", "&oelig;", "&Scaron;", "&scaron;", "&Yuml;", "&circ;", "&tilde;", "&ensp;",
			"&emsp;", "&thinsp;", "&zwnj;", "&zwj;", "&lrm;", "&rlm;", "&ndash;", "&mdash;", "&lsquo;", "&rsquo;", "&sbquo;", "&ldquo;",
			"&rdquo;", "&bdquo;", "&dagger;", "&Dagger;", "&permil;", "&lsaquo;", "&rsaquo;", "&euro;");

		return in_array($entity, $entityList);
	}

	/**
	 * Strips the HTML tags from a text and returns the result
	 *
	 * @param String $text the text containing HTML tags
	 * @return String the text without HTML tags
	 */
	function stripHTML($text) {
		$text = str_replace("&nbsp;"," ",$text);
		return ereg_replace("<[^>]*>","",$text);
	}

	/**
	 *
	 * @param String $uniCode
	 * @param String $replace
	 * @param STRING $text
	 * @deprecated empty function
	 */
	function unicodeReplace($uniCode, $replace, $text) {
		//str_replace("\xe2\x82\x20\xac", "&euro;", $text);
	}

	/**
	 * Converts HTML entities to special characters
	 *
	 * @param String $text the text to be converted
	 * @return String the text where all the HTML entities are converted to characters
	 */
	function convertTextCharacters($text) {
		$text = mb_convert_encoding($text, 'HTML-ENTITIES', "windows-1252,ISO-8859-1,UTF-8,UTF-7,ASCII");
		//$text = mb_convert_encoding($text, 'HTML-ENTITIES', "UTF-8,UTF-7,windows-1252,ISO-8859-1,ASCII");
		return $text;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $str
	 * @return unknown
	 */
	function getHexEntities($str) {
		$result = '';
		for($i = 0; $i < strlen($str); $i++) {
			$result .= '&#x'.bin2hex(substr($str, $i, 1)).';';
		}
		return $result;
	}
	/**
	 * Converts the given text to html
	 *
	 * The given text will be transform to reflect html text. This means that special characters like < and > will
	 * be converted to their respective entity. &lt; and &gt; Entities in the text are allowed, as long as they are
	 * valid XHTML 1.0 entities. Other entities will be shown as text.
	 *
	 * @param string $text the text to convert to html
	 * @return string text to show in html documents
	 */
	function htmlConvert($text) {
		$text = convertTextCharacters($text);
		$text = str_replace("<", "&lt;", $text);
		$text = str_replace(">", "&gt;", $text);
		$text = str_replace('"', "&quot;", $text);
		$text = str_replace("'", "&#39;", $text);

		$ampPos = strPos($text, "&");
		while ($ampPos !== false) {
			$firstPos = $ampPos;
			$entityPos = strPos($text, ";", $ampPos + 1);
			$ampPos = strPos($text, "&", $ampPos + 1);

			if (($entityPos !== false) && (($entityPos < $ampPos) && ($ampPos !== false))) {
				$entity = subStr($text, $firstPos, ($entityPos - $firstPos)+1);
				$fix = 1;
				if (!isEntity($entity)) {
					$entity = "&amp;" . subStr($entity, 1);
					$fix = 6;
				}
				$text = subStr($text, 0, $firstPos) . $entity . subStr($text, $entityPos+1);
				$ampPos = strPos($text, "&", $entityPos + $fix);
			} else
			if (($entityPos !== false) && ($ampPos === false)) {
				$entity = subStr($text, $firstPos, ($entityPos - $firstPos)+1);
				if (!isEntity($entity)) {
					$entity = "&amp;" . subStr($entity, 1);
				}
				$text = subStr($text, 0, $firstPos) . $entity . subStr($text, $entityPos+1);
			} else
			if (($entityPos !== false) && (($entityPos >= $ampPos) && ($ampPos !== false))) {
				$text = subStr($text, 0, $firstPos) . "&amp;" . subStr($text, $firstPos+1);
				$ampPos = strPos($text, "&", $firstPos + 1);
			} else
			if ($entityPos === false) {
				$text = subStr($text, 0, $firstPos) . "&amp;" . subStr($text, $firstPos+1);
				$ampPos = strPos($text, "&", $firstPos + 1);
			}
		}
		return $text;
	}

	/**
	 * Converts special characters to HTML entities
	 *
	 * @param String $text the text to be converted
	 * @return String the converted text
	 */
	function textToXmlConvert($text) {
		$text = str_replace("<", "&lt;", $text);
		$text = str_replace(">", "&gt;", $text);
		$text = str_replace('"', "&quot;", $text);
		$text = str_replace("'", "&#39;", $text);
		return $text;
	}

	/**
	 * Converts special HTML entities to characters
	 *
	 * @param String $text the text to be converted
	 * @return String the converted text
	 */
	function xmlToTextConvert($text) {
		$text = str_replace("&lt;", "<", $text);
		$text = str_replace("&gt;", ">", $text);
		$text = str_replace("&quot;", '"', $text);
		$text = str_replace("&#39;", "'", $text);
		return $text;
	}


	define("email_priority_low",1);
	define("email_priority_normal",2);
	define("email_priority_high",3);


	/**
	* Sends an email
	* @param String fromName, name of the sender
	* @param String fromEmail, emailadres of the sender
	* @param String toName, the name of the receiver
	* @param String toEmail, emailadres of the receiver
	* @param String subject, the subject of the email
	* @param String message, the message of the email
	* @param int priority, priority of email, use definitions: email_priority_low, email_priority_normal, email_priority_high (default = email_priority_low)
	* @param bool htmlContent, whether the message contains HTML data (default = true)
	* @param array attachments, array with attachments, format array(array(fileName,fileData,fileMimeType)) (default = array())
	* @param bool relatedAttachments, whether the attachments are used in the message-body, if true it will hide known MIME-TYPE files from the attachmentlist
	* @return bool, whether send was succesfull
	* @deprecated use Email.class.php instead
	**/
	function email2($fromName, $fromEmail, $toName, $toEmail, $subject, $message, $priority=email_priority_normal, $htmlContent = true, $attachments = array(), $relatedAttachments = false) {
		$headers = "";
		$endLine = "\n"; // "\r\n"

		$mime_boundary = "<<<:" . md5(uniqid(mt_rand(), 1));
		$headers .= "MIME-Version: 1.0\n";
		$hasAttachments = (count($attachments) > 0) ? true : false;

		// FORMAT
		$format = "text/plain";
		if($htmlContent) $format = "text/html";
		if($hasAttachments) {
			if($relatedAttachments) $format = "multipart/related";
			else $format = "multipart/mixed";
		}

		// CHARSET
		$charSet = ($hasAttachments) ? '' : 'charset="iso-8859-1"';

		$headers .= "Content-Type: ".$format."; ". $charSet.$endLine;
		if($hasAttachments) $headers .= " boundary=\"". $mime_boundary . "\"".$endLine;

		// FROM
		$headers .= "From: ".$fromName." <".$fromEmail.">".$endLine;

		// PRIORITY
		$xPriority = '';
		$msPriority = '';
		switch($priority) {
		 case email_priority_low	: $xPriority = '3'; $msPriority = 'Low'; break;
		 case email_priority_normal	: $xPriority = '3'; $msPriority = 'Normal'; break;
		 case email_priority_high	: $xPriority = '1'; $msPriority = 'High'; break;
		 default 					: $xPriority = '3'; $msPriority = 'Normal'; break;
		}
		$headers .= "X-Priority: ".$xPriority.$endLine;
		$headers .= "X-MSMail-Priority: ".$msPriority.$endLine;

		//MISC (anti-spam)
		$headers .= "X-Mailer: PHP".$endLine;
		$headers .= "X-MimeOLE: Produced by IVlib (http://www.ivinity.nl)".$endLine;

		// ATTACHMENTS
		if($hasAttachments) {
			$format = "text/plain";
			if($htmlContent) $format = "text/html";

			// MESSAGE
			$mime = "This is a multi-part message in MIME format.".$endLine;
			$mime .= $endLine;
			$mime .= "--" . $mime_boundary .$endLine;
			$mime .= "Content-transfer-encoding: 7bit".$endLine;
			$mime .= "Content-type: ".$format."; charset=\"iso-8859-1\"".$endLine;
			$mime .= $endLine;
			$mime .= $message .$endLine;
			$mime .= "--" . $mime_boundary .$endLine;

			// ATTACHMENTS
			$i = 0;
			foreach($attachments AS $attachment) {
				$fileName = $attachment[0];
				$fileData = $attachment[1];

				$fileMime = "application/octet-stream";
				if(isSet($attachment[2]) && strLen($attachment[2]) > 0) $fileMime = $attachment[2];

				$mime .= "Content-id: <".$fileName.">".$endLine;
				$mime .= "Content-type: ".$fileMime."; name=\"". $fileName ."\"".$endLine;
				$mime .= "Content-transfer-encoding: base64".$endLine;
				$mime .= "Content-disposition: attachment; filename=\"". $fileName. "\"".$endLine;;

				$mime .= $endLine.chunk_split(base64_encode($fileData)) .$endLine;
				if($i < count($attachments)-1) {
					$mime .= "--" . $mime_boundary .$endLine;	// CLOSE ATTACHMENT, PREPARE FOR NEXT
				} else {
					$mime .= "--" . $mime_boundary . "--".$endLine; // CLOSE ATTACHMENT PART
				}
				$i++;
			}
			$message = $mime;
		}

		$toString = sprintf("%s<%s>",$toName,$toEmail);


		if(mail($toString, $subject, $message, $headers)) return true;
		return false;
	}

	/**
	* Sends an email (DEPRICATED, use email2() )
	* @param String fromName, name of the sender
	* @param String fromEmail, emailadres of the sender
	* @param String toEmail, emailadres of the receiver
	* @param String subject, the subject of the email
	* @param String message, the message of the email
	* @param int priority, priority of email, use string: low, normal, high (default = normal)
	* @param bool HTML, whether the message contains HTML data (default = true)
	* @param bool withAttachment, whether the email contains attachments (default = false)
	* @param String fname, the filename of the file
	* @param String data, the data of the file
	* @param String type, type of the attached file (default = Application/Octet-Stream)
	* @return bool, whether send was succesfull
	* @deprecated use Email.class.php instead
	**/
	function email($fromName, $fromEmail, $toEmail, $subject, $message , $priority='normal', $HTML=true, $withAttachment=false, $fname = '', $data = '', $type="Application/Octet-Stream") {
		// PRIORITY
		$prio = '';
		switch($priority) {
			case 'low' 		: $prio = email_priority_low; break;
			case 'normal' : $prio = email_priority_normal; break;
			case 'high' 	: $prio = email_priority_high; break;
			default 			:	$prio = email_priority_normal; break;
		}

		// ATTACHMENTS
		$attachments = array();
		if($withAttachment) {
			$attachments[] = array($fname,$data);
		}
		return email2($fromName,$fromEmail, $toEmail,$toEmail,$subject,$message,$prio, $HTML, $attachments);


		/*$headers = "";
		$mime_boundary = "<<<:" . md5(uniqid(mt_rand(), 1));

		$headers .= "MIME-Version: 1.0\n";

		$format = "text/html";
		if(!$HTML) $format = "text/plain";
		if($withAttachment) $format = "multipart/mixed";
		$char = '';
		if(!$withAttachment) {$char = "charset=\"iso-8859-1\"";}

		$headers .= "Content-Type: ".$format."; ". $char."\n";
		if($withAttachment) $headers .= " boundary=\"" . $mime_boundary . "\"\n";
		$headers .= "From: ".$fromName." <".$fromEmail.">\n";
		//$headers .= "Reply-To: ".$fromName."<".$fromEmail.">\n";

		$xPriority = "3";
		$msPriority = "Normal";
		switch($priority) {
			case 'low' : $xPriority ='3'; $msPriority = 'Low'; break;
			case 'high' : $xPriority ='1'; $msPriority = 'High'; break;
		}
		$headers .= "X-Priority: $xPriority\n";
		$headers .= "X-MSMail-Priority: ".$msPriority."\n";
		//$headers .= "X-Mailer: Servicez.org";

		if($withAttachment)
		{
			$format = "text/html";
			if(!$HTML) $format = "text/plain";
			$mime = "This is a multi-part message in MIME format.\r\n";
			$mime .= "\r\n";
			$mime .= "--" . $mime_boundary . "\r\n";
			$mime .= "Content-Transfer-Encoding: 7bit\r\n";
			$mime .= "Content-Type: ".$format."; charset=\"iso-8859-1\"\r\n";
			$mime .= "\r\n";
			$mime .= $message . "\r\n\r\n";
			$mime .= "--" . $mime_boundary . "\r\n";

			$mime .= "Content-Transfer-Encoding: base64\r\n";
			$mime .= "Content-Type: $type;\r\n";
			$mime .=" name=\"$fname\"\r\n";
			$mime .= "Content-Disposition: attachment;\r\n ";
			$mime .= " name=\"$fname\"\r\n\r\n";
			$mime .= base64_encode($data) . "\r\n\r\n";
			$mime .= "--" . $mime_boundary . "--\r\n";
			$message = $mime;
		}

		if(@mail($toEmail, $subject, $message, $headers)) return true;
		return false;*/
	}

	/**
	* Reads the file data from the given info array and returns an
	* email attachments array that can be used in the email2 function
	* @param array fileInfo, array with file info in format: array(fileName => fileLocation)
	* @return array emailAttachments, array in format array(array(fileName,fileData,fileMimeType))
	**/
	function prepareEmailAttachments($fileInfo = array()) {
		$attachments = array();

		foreach($fileInfo AS $fileName => $fileLocation) {

			// Encode the file ready to send it off
			$handle = fopen($fileLocation,'rb');
			$fileContent = fread($handle,filesize($fileLocation));
			$mimeType = "";
			$mimeType = mime_content_type($fileLocation);
			fclose($handle);

			$attachments[] = array($fileName, $fileContent, $mimeType);
		}
		return $attachments;
	}

	/**
	* Backup function of the mime_content_type() function
	* @param String fileName, the name of the file
	* @return String mimeType, the mimeType of the file
	**/
	function mime_content_type_($fileName) {
		$mime = array(
			'.jpg' => 'image/jpeg',
			'.jpe' => 'image/jpeg',
			'.jpeg' => 'image/jpeg',
			'.gif' => 'image/gif',
			'.png' => 'image/png',
			'.bmp' => 'image/bmp',
			'.css' => 'text/css',
			'.html' => 'text/html',
			'.txt' => 'text/plain',
			'.pdf' => 'application/pdf');

		return $mime[strrchr($fileName, '.')];
	}

	/**
	 * Padds a string with the given fillchar until the maxlength is reached
	 * @param string $string the string that must be padded
	 * @param char $fillChar the char that is used to padd
	 * @param int $maxLength the maximum length of string
	 * @param bool $post if true the fillChar is added at the end of the string, else in front of
	 * @return string the resultstring
	 **/
	function paddString($string, $fillChar, $maxLength, $post=true) {
		while(strLen($string) < $maxLength) {
			if($post) $string .= $fillChar;
			else $string = $fillChar.$string;
		}
		return $string;
	}

	/**
	* Get the current time in Millisecond from 1-1-1970 as Float
	* @return float the current time in milliseconds
	**/
	function microtime_float() {
	   list($usec, $sec) = explode(" ", microtime());
	   return ((float)$usec + (float)$sec);
	}

	/**
	 * Rewrites the parameters in an URL Query
	 * @param String, the url query
	 * @param Array, array with parameters to be rewriten. "name" => "newvalue"
	 * @return String, returns the rewriten url query
	 **/
	function rewriteUrlQueryParameters($query, $parameters = array()) {
		$orgParams = explode("&", $query);
		$params = array();
		foreach($orgParams as $orgParam) {
			if (strPos($orgParam, '=') !== false) {
				list($parname, $parvalue) = explode("=", $orgParam);
				$params[$parname] = $parvalue;
				foreach($parameters as $parameterName => $newValue) {
					if ($parname == $parameterName) $params[$parname] = $newValue;
				}
			}
		}
		foreach($parameters as $parameterName => $newValue) {
			if (!isSet($params[$parameterName])) $params[$parameterName] = $newValue;
		}
		$newQueryString = array();
		foreach($params as $parname => $parvalue) {
			$newQueryString[] = $parname.'='.$parvalue;
		}
		$newQuery = implode('&', $newQueryString);
		return $newQuery;
	}

	/**
	 * Rewrites the parameters in an URL
	 * @param String, the url
	 * @param Array, array with parameters to be rewriten. "name" => "newvalue"
	 * @return String, returns the rewriten url
	 **/
	function rewriteUrlParameters($url, $parameters = array()) {
		// todo
		if (count($parameters) == 0) return $url;
		if (strPos($url, "?") !== false) {
			$pageQuery = subStr($url, strPos($url, "?") + 1);
			$urlPre = subStr($url, 0, strPos($url, "?") );
			$newQuery = rewriteUrlQueryParameters($pageQuery, $parameters);
			return $urlPre . "?" . $newQuery;
		} else {
			$newQuery = $url . "?";
			$pairs = array();
			foreach($parameters as $key => $value) {
				$pairs[] = $key."=".$value;
			}
			return $newQuery . implode("&", $pairs);
		}
	}

	/**
	 * Create a hash from a given key
	 * default is 2^32 = 2147483648
	 *
	 * @param String $key the String to hash
	 * @param  int $hashSize size of the hash and default max int
	 * @return String hashvalue
	 */

	function stringHash($key, $hashSize=2147483648) {
		$hashValue = 0;
		for ($i = 0; $i < strLen($key); $i++) {
			$hashValue = 37 * $hashValue + ord($key{$i});
		}

		$hashValue = $hashValue % $hashSize;
		if ($hashValue < 0) {
			$hashValue += $hashSize;
		}

		return $hashValue;
	}

	/**
	 * Function to compare references of objects and check if they point to the same object
	 *
	 * @param mixed $a reference to object
	 * @param mixed $b reference to object
	 * @return boolean whether the objects point to the same object
	 * @deprecated test function, function is not used in code anymore
	 */
	function compareReferences(&$a, &$b) {
			// creating unique name for the new member variable
			$tmp = uniqid("");
			// creating member variable with the name $tmp in the object $a
			$a->$tmp = true;
			// checking if it appeared in $b
			$bResult = !empty($b->$tmp);
			// cleaning up
			unset($a->$tmp);
			return $bResult;
	}

	/**
	 * Converts a string to a valid filename (illegal characters are converted)
	 *
	 * @param String $fileName the filename
	 * @return String the legal filename
	 */
	function convertToValidFileName($fileName) {
		$invalidCharArray = array("/" => "-",
								"\\" => "-",
								":" => "-",
								"*" => "-",
								"?" => "-",
								"\"" => "-",
								"<" => "-",
								">" => "-",
								"|" => "-"
								);
		foreach($invalidCharArray AS $invalidChar => $correctChar) {
			$fileName = str_replace($invalidChar,$correctChar,$fileName);
		}
		return $fileName;
	}


	/**
	 * Detects if the given position is inside a string of the text
	 *@param string $text the text to investigate
	 *@param int $position the position to investigate
	 *@param array $stringCaps array of valid string chars, like " or '
	 *@return bool true if the position is inside a string, false otherwise
	 */
	function isInString($text, $position, $stringCaps) {
		$textBefore = subStr($text, 0, $position);
		//$stringCaps = $this->syntax->stringCaps;
		//print "<b>".htmlConvert($textBefore)."</b><br />";

		$searchStart = 0;
		$hasMore = true;
		while ($hasMore) {
			$hasMore = false;
			$closestPos = -1;
			$stringType = '';
			// loop through each type of string, to see wich one is first
			foreach($stringCaps as $stringCap) {
				$strPos = strPos($textBefore, $stringCap, $searchStart);
				if ($strPos !== false) {
					if ($closestPos == -1) { $stringType = $stringCap; $closestPos = $strPos; }
					elseif ($closestPos > $strPos) { $stringType = $stringCap; $closestPos = $strPos; }
				}
			}
			if ($closestPos != -1) { // found a string occurance?
				//print "found string, type: ".$stringType."<br />";

				// we are before the first string occurance, therefor not in a string
				if ($position < $closestPos) return false;
				$validStringEnd = false;
				while (!$validStringEnd) {
					// search the end of the string
					if (($closestPos + strLen($stringCap)) > strLen($textBefore)) return true;
					$stringEnd = strPos($textBefore, $stringCap, $closestPos + 1);
					// is the end behind our character (out of the scope of beforeText) we are inside a string
					if ($stringEnd === false) return true;
					// is the end character at our cursor? we are still inside a string
					if ($stringEnd == 0) return true;
					// check if the string char is escaped.
					$walkBack = $stringEnd-1;
					$escaped = (subStr($textBefore, $walkBack, 1) == '\\');
					// if the char is escaped, the escape could also be escaped. check that
					$checkEscape = $escaped;
					while ($checkEscape) {
						$checkEscape = false;
						$walkBack --;
						$escapedAgain = (subStr($textBefore, $walkBack, 1) == '\\');
						// found another escape? than the current escape state is not true
						// try it again
						if ($escapedAgain) {
							$escaped = !$escaped;
							$checkEscape = true;
						}
					}
					// the end of the string is valid if it isn't escaped
					$validStringEnd = !$escaped;
					$closestPos = $stringEnd + strLen($stringCap);
				}
				// if the position was before the string end, it was inside the string
				if ($position < $closestPos) return true;
				$searchStart = $closestPos;
				$hasMore = true;
				continue;
			}
		}
		return false;
	}

	/**
	 * Splits a text (CSV format) in an array
	 *
	 * @param String $data the data
	 * @param String $fieldSeperator the character that functions as the fieldseperator
	 * @param String $textSeperator the character that is used to seperate texts
	 * @return Array result as an array of strings
	 */
	function splitText($data, $fieldSeperator, $textSeperator) {
		$result = array();

		$dataArray = explode($fieldSeperator, $data);
		for($i = 0; $i < count($dataArray); $i++) {
			$item = $dataArray[$i];
			$tmpVar = $item . " ";
			if(isInString($tmpVar,strLen($tmpVar)-1, array($textSeperator))) {
				$done = false;
				while(!$done) {
					$i++;
					$item .= $dataArray[$i];
					$tmpVar = $item . " ";
					$done = !isInString($tmpVar,strLen($tmpVar)-1, array($textSeperator));
				}
			}
			$result[] = $item;
		}
		return $result;
	}

	/**
	 * Unlinks the given folder
	 *
	 * @param String $dir the folder
	 * @return boolean whether unlinking was succesfull
	 */
	function unlinkFolder($dir)	{
		$handle = opendir($dir);
		while (false!==($item = readdir($handle)))	{
			if($item != '.' && $item != '..') {
				if(is_dir($dir.'/'.$item)) {
					unlinkFolder($dir.'/'.$item);
				} else {
					unlink($dir.'/'.$item);
				}
			}
		}
		closedir($handle);
		if(rmdir($dir)) {
			$success = true;
		}
		return $success;
	}


	function getPreferredLanguage($availableLanguages) {
		$preferredLanguages = explode(",", strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"]));
		$browseLangCodes = array();
		foreach($preferredLanguages as $browseLang) {
			if (strpos($browseLang, ";") !== false) {
				list($regioCode, $qValue) = explode(";", $browseLang);
				$browseLangCodes[] = $regioCode;
			} else $browseLangCodes[] = $browseLang;
		}

		if (count($browseLangCodes) == 0) return $availableLanguages[0];
		foreach($browseLangCodes as $langCode) {
			foreach($availableLanguages as $availableLanguage) {
				if ($langCode == $availableLanguage) return $availableLanguage;
				if (strPos($langCode, "-") !== false) {
					list($standard, $regio) = explode("-", $langCode);
					if ($standard == $availableLanguage) {
						return $standard;
					}
				}
			}
		}
		return $availableLanguages[0];
	}

?>
