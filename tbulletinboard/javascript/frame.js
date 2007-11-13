//var iv_popup_array = new Array();

function setFramesetSizes(frameset, rowSizes, colSizes) {
	try {
		var FrameSet = parent.document.getElementById(frameset);
		if (rowSizes.length > 0) FrameSet.rows = rowSizes;
		if (colSizes.length > 0) FrameSet.cols = colSizes;
	} catch (e) {}
}

function setFramesetSizesDoc(doc, frameset, rowSizes, colSizes) {
	try {
		if(doc != null) {
			var FrameSet = doc.getElementById(frameset);
			if (FrameSet != null) {
				if (rowSizes.length > 0) FrameSet.rows = rowSizes;
				if (colSizes.length > 0) FrameSet.cols = colSizes;
			}
		}
	} catch (e) {}
}

function htmlToText(text) {
	var result = text;
	while (result.indexOf("&apos;") > 0)
		result = result.replace("&apos;", "'");
	return result;
}

/**
 * The popup function as exclusivly used by the Javascript.class.php
 */
function js_class_popupWindow(url, sizeX, sizeY, name, location, lPos, tPos, scrollbar, resizable) {
	var left;
	var top;

	if (sizeX == -1 && sizeY == -1) {
		sizeX  = window.screen.availWidth-10;
		sizeY  = window.screen.availHeight-30;
		left = 0;
		top = 0;
	} else {
		left = (window.screen.availWidth / 2) - (sizeX / 2);
		top = (window.screen.availHeight / 2) - (sizeY / 2);
	}
	if (lPos >= 0) {
		left = lPos;
	}
	if (tPos >= 0) {
		top = tPos;
	}
	params = "toolbar=0, location="+location+", directories=0, status=0, menubar=0, scrollbars="+scrollbar+", left="+left+", top="+top+", screenX="+left+", screenY="+top+", resizable="+resizable+", width="+sizeX+", height="+sizeY;
	popupRef = window.open(url, name, params);

	//iv_popup_array[iv_popup_array.length] = popupRef;
	//alert("grootte : "+iv_popup_array.length);
}


function popupWindow(url, sizeX, sizeY, name) {
	var left;
	var top;

	if (sizeX == -1 && sizeY == -1) {
		sizeX  = window.screen.availWidth-10;
		sizeY  = window.screen.availHeight-30;
		left = 0;
		top = 0;
	} else {
		left = (window.screen.availWidth / 2) - (sizeX / 2);
		top = (window.screen.availHeight / 2) - (sizeY / 2);

	}

	params = "toolbar=0, location=0, directories=0, status=0, menubar=0, scrollbars=1, left="+left+", top="+top+", screenX="+left+", screenY="+top+", resizable=1, width="+sizeX+", height="+sizeY;
	popupRef = window.open(url, name, params);

	//iv_popup_array[iv_popup_array.length] = popupRef;
	//alert("grootte : "+iv_popup_array.length);
}

function popupWindowScrollbar(url, sizeX, sizeY, name, scrollbar) {
	if (sizeX == -1 && sizeY == -1) {
		sizeX  = window.screen.availWidth-20;
		sizeY  = window.screen.availHeight-30;
	}
	params = "toolbar=0, location=0, directories=0, status=0, menubar=0, left=0,top=0,scrollbars="+scrollbar+", resizable=1, width="+sizeX+", height="+sizeY;
	popupRef = window.open(url, name, params);

	//iv_popup_array[iv_popup_array.length] = popupRef;
	//alert("grootte : "+iv_popup_array.length);
}

function popupWindowFullScreen(url,name, scrollbar) {
	params = "fullscreen=yes, toolbar=0, location=0, directories=0, status=0, menubar=0, scrollbars="+scrollbar+", resizable=0, width="+window.screen.availWidth+", height="+window.screen.availHeight;
	popupRef = window.open(url, name, params);

	//iv_popup_array[iv_popup_array.length] = popupRef;
	//alert("grootte : "+iv_popup_array.length);
}

function getFrameUrl(frameArray) {
	getFrameUrl(frameArray,'top');
}

function getFrameUrl(frameArray,topName) {
	var url = 'error.html';
	var evalCode = topName;
	var evalCheckCode = "";
	for (var i=0; i < frameArray.length; i++) {
		evalCode += ".frames['" + frameArray[i] + "']";
		evalCheckCode += "if("+evalCode+" != null) ";
	}
	evalCode = "url = "+evalCode+".location.href";
	try {
		eval(evalCheckCode + evalCode);
	} catch (e) {}
	return url;
}

function setFrameUrl(frameArray, url) {
	setFrameUrlTop(frameArray, url, 'top');
}

function setFrameUrlPopup(frameArray, url) {
	setFrameUrlTop(frameArray, url, 'window.opener.top');
}

function setFrameUrlTop(frameArray, url, topname) {
	var evalCode = topname;
	var evalCheckCode = '';

	for (var i=0; i < frameArray.length; i++) {
		evalCode += ".frames['" + frameArray[i] + "']";
	}

	evalCode += ".location.href = '" + url + "'";
	try {
		eval(evalCheckCode + evalCode);
	} catch (e) {}
}

function framePrint(frameArray) {
	var evalCode = 'top';
	var evalCheckCode = "";

	for (var i=0; i < frameArray.length; i++) {
		evalCode += ".frames['" + frameArray[i] + "']";
	}

	var focus = evalCode + '.focus()';
	var printer = evalCode + '.print()';

	eval(focus);
	eval(printer);
}

function adjustToScreenSize(elementName, widthExtra, heightExtra) {
	newHeight = 0;
	newWidth = 0;

	if( typeof( window.innerWidth ) == 'number' ) {
		//Non-IE
		newHeight = window.innerHeight;
		newWidth = window.innerWidth;
	} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
		//IE 6+ in 'standards compliant mode'
		newHeight = document.documentElement.clientHeight;
		newWidth = document.documentElement.clientWidth;
	} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
		//IE 4 compatible
		newHeight = document.body.clientHeight;
		newWidth = document.body.clientWidth;
	}


	newHeight += heightExtra;
	newWidth += widthExtra;


	var element = document.getElementById(elementName);
	try {
		element.style.height = newHeight + 'px';
		element.style.width  = newWidth + 'px';
	}
	catch(e) {}

}

function getFrameWidth(frameArray) {
	var result = 0;
	var evalCode = 'top';
	var evalCheckCode = "";

	for (var i=0; i < frameArray.length; i++) {
		evalCode += ".frames['" + frameArray[i] + "']";
	}
	//evalCheckCode += "if("+evalCode+" != null) ";

	if( typeof( window.innerWidth ) == 'number' ) {		//Non-IE
		evalCode += ".window.innerWidth;";
	} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {		//IE 6+ in 'standards compliant mode'
		evalCode += "newWidth = document.documentElement.clientWidth;";
	} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {		//IE 4 compatible
		evalCode += ".document.body.clientWidth;";
	}
	try {
		eval(evalCheckCode + "result = " + evalCode);
		return result;
	} catch (e) { return 0;}

}

function getFrameHeight(frameArray) {
	var result = 0;
	var evalCode = 'top';
	var evalCheckCode = "";

	for (var i=0; i < frameArray.length; i++) {
		evalCode += ".frames['" + frameArray[i] + "']";
	}
	//evalCheckCode += "if("+evalCode+" != null) ";

	if( typeof( window.innerWidth ) == 'number' ) {		//Non-IE
		evalCode += ".window.innerHeight;";
	} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {		//IE 6+ in 'standards compliant mode'
		evalCode += "newWidth = document.documentElement.clientHeight;";
	} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {		//IE 4 compatible
		evalCode += ".document.body.clientHeight;";
	}

	try {
		eval(evalCheckCode + "result = " + evalCode);
		return result;
	} catch (e) { return 0;}
}


/**
 * Add/Changes a parameter with value to a given url.
 * If the parameter (key) already excists it will be overwriten, else it will be added
 *@param String the url
 *@param String the parameter name (key)
 *@param String the value
 *@return String the new URL
 **/
function setParameterInURL(url,key,value) {
	var keyPos = -1;
	var subPart = "";
	if (url.indexOf("#") > -1) {
		subPart = url.substring(url.indexOf("#"), url.length);
		url = url.substring(0, url.indexOf("#"));
	}

	if(url.lastIndexOf("?"+key+"=") > -1) keyPos = url.lastIndexOf("?"+key+"=");
	if(url.lastIndexOf("&"+key+"=") > -1) keyPos = url.lastIndexOf("&"+key+"=");

	if (keyPos > -1) {
		var equalsPos = keyPos + key.length + 2;
		var urlPart1 = url.substring(0,equalsPos);
		var urlPart2 = url.substring(equalsPos+1,url.length);
		if(urlPart2.indexOf("&") > -1) {
			urlPart2 = urlPart2.substring(urlPart2.indexOf("&"),urlPart2.length);
		} else urlPart2 = "";
		url = urlPart1 + value + urlPart2;
	} else {
		if(hasUrlParameters(url)) url = url + "&"+key+"="+value;
		else url = url+"?"+key+"="+value;
	}
	return url + subPart;
}

/**
* Checks whether a url has parameters or not
*@param String the url
*@return bool the result
**/
function hasUrlParameters(url) {
	if(url.lastIndexOf("?") > -1) return true;
	return false;
}