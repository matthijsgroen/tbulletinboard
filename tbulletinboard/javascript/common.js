	function URLEncode(plaintext) {
		// The Javascript escape and unescape functions do not correspond
		// with what browsers actually do...
		var SAFECHARS = "0123456789" +					// Numeric
			"ABCDEFGHIJKLMNOPQRSTUVWXYZ" +	// Alphabetic
			"abcdefghijklmnopqrstuvwxyz" +
			"-_.!~*'()";					// RFC2396 Mark characters
		var HEX = "0123456789ABCDEF";

		var encoded = "";
		for (var i = 0; i < plaintext.length; i++ ) {
			var ch = plaintext.charAt(i);
			if (ch == " ") encoded += "+";				// x-www-urlencoded, rather than %20
			else if (SAFECHARS.indexOf(ch) != -1) encoded += ch;
			else {
				var charCode = ch.charCodeAt(0);
				if (charCode > 255) {
					alert( "Unicode Character '" + ch
						+ "' cannot be encoded using standard URL encoding.\n" +
						"(URL encoding only supports 8-bit characters.)\n" +
						"A space (+) will be substituted." );
					encoded += "+";
				} else {
					encoded += "%";
					encoded += HEX.charAt((charCode >> 4) & 0xF);
					encoded += HEX.charAt(charCode & 0xF);
				}
			}
		} // for
		return encoded;
	}

	function trim(inputString) {
		if (typeof inputString != "string") { return inputString; }
		var retValue = inputString;
		var ch = retValue.substring(0, 1);
		while (ch == " ") {
			retValue = retValue.substring(1, retValue.length);
			ch = retValue.substring(0, 1);
		}
		ch = retValue.substring(retValue.length-1, retValue.length);
		while (ch == " ") {
			retValue = retValue.substring(0, retValue.length-1);
			ch = retValue.substring(retValue.length-1, retValue.length);
		}
		while (retValue.indexOf("  ") != -1) {
			retValue = retValue.substring(0, retValue.indexOf("  ")) + retValue.substring(retValue.indexOf("  ")+1, retValue.length);
		}
		return retValue;
	}

	function ArrayContains(value) {
		for (var i = 0; i < this.length; i++) {
			if (this[i] == value) return true;
		}
		return false;
	}

	Array.prototype.contains = ArrayContains;

	function ArrayIndexOf(value) {
		for (var i = 0; i < this.length; i++) {
			if (this[i] == value) return i;
		}
		return i;
	}

	Array.prototype.indexOf = ArrayIndexOf;


	function ArrayMerge(glue) {
		var result = "";
		for (var i = 0; i < this.length; i++) {
			result += this[i];
			if(i < this.length-1) result += glue;
		}
		return result;
	}

	Array.prototype.merge = ArrayMerge;


	function isArray(a) {
		return isObject(a) && a.constructor == Array;
	}

	function isObject(a) {
	    return (a && typeof a == 'object') || isFunction(a);
	}

	function isFunction(a) {
	    return typeof a == 'function';
	}

	function convertEntityToChar(text) {
		/*
		$text = str_replace("�", "&eacute;", $text);
		$text = str_replace("�", "&aacute;", $text);
		$text = str_replace("�", "&iacute;", $text);
		$text = str_replace("�", "&oacute;", $text);
		$text = str_replace("�", "&uacute;", $text);
		*/
		text = text.replace(/&eacute;/g, "\u00e9");
		text = text.replace(/&aacute;/g, "\u00e1");
		text = text.replace(/&iacute;/g, "\u00ed");
		text = text.replace(/&oacute;/g, "\u00f3");
		text = text.replace(/&uacute;/g, "\u00fa");

		/*
		$text = str_replace("�", "&ecirc;", $text);
		$text = str_replace("�", "&acirc;", $text);
		$text = str_replace("�", "&ucirc;", $text);
		$text = str_replace("�", "&ocirc;", $text);
		$text = str_replace("�", "&icirc;", $text);
		*/
		text = text.replace(/&ecirc;/g, "\u00ea");
		text = text.replace(/&acirc;/g, "\u00e2");
		text = text.replace(/&ucirc;/g, "\u00fb");
		text = text.replace(/&ocirc;/g, "\u00f4");
		text = text.replace(/&icirc;/g, "\u00ee");
		/*
		$text = str_replace("�", "&euml;", $text);
		$text = str_replace("�", "&auml;", $text);
		$text = str_replace("�", "&uuml;", $text);
		$text = str_replace("�", "&ouml;", $text);
		$text = str_replace("�", "&iuml;", $text);
		*/
		text = text.replace(/&euml;/g, "\u00eb");
		text = text.replace(/&auml;/g, "\u00e4");
		text = text.replace(/&uuml;/g, "\u00fc");
		text = text.replace(/&ouml;/g, "\u00f6");
		text = text.replace(/&iuml;/g, "\u00ef");
		/*
		$text = str_replace("�", "&egrave;", $text);
		$text = str_replace("�", "&agrave;", $text);
		$text = str_replace("�", "&ugrave;", $text);
		$text = str_replace("�", "&ograve;", $text);
		$text = str_replace("�", "&igrave;", $text);
		*/
		text = text.replace(/&egrave;/g, "\u00e8");
		text = text.replace(/&agrave;/g, "\u00e0");
		text = text.replace(/&ugrave;/g, "\u00f9");
		text = text.replace(/&ograve;/g, "\u00f2");
		text = text.replace(/&igrave;/g, "\u00ec");
		/*
		$text = str_replace("�", "&atilde;", $text);
		$text = str_replace("�", "&otilde;", $text);
		$text = str_replace("�", "&ntilde;", $text);
		*/
		text = text.replace(/&atilde;/g, "\u00e3");
		text = text.replace(/&otilde;/g, "\u00f5");
		text = text.replace(/&ntilde;/g, "\u00f1");

		/*
		$text = str_replace("�", "&iexcl;", $text);
		$text = str_replace("�", "&iquest;", $text);
		*/

		text = text.replace(/&iexcl;/g, "\u00a1");
		text = text.replace(/&iquest;/g, "\u00bf");

		return text;
	}

	function alertML(mlText) {
		alert(convertEntityToChar(mlText));
	}

	function confirmML(mlText) {
		return confirm(convertEntityToChar(mlText));
	}

	function UrlData() {
		this.items = new Array();
		this.add = addUrlData;
		this.getString = urlDataString;
	}

	function addUrlData(name, value) {
		this.items[this.items.length] = new Array(name, value);
	}

	function urlDataString() {
		var result = "";
		for (var i = 0; i < this.items.length; i++) {
			result += URLEncode(this.items[i][0]) + "=" + URLEncode(this.items[i][1]);
			if (i < this.items.length -1) result += "&";
		}
		return result;
	}