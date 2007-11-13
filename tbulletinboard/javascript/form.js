


function insertAtCursor(myField, myValue) {
	//IE support
	if (document.selection) {
		myField.focus();
		sel = document.selection.createRange();
		sel.text = myValue;
	}
	//MOZILLA/NETSCAPE support
	else if (myField.selectionStart || myField.selectionStart == '0') {
		var startPos = myField.selectionStart;
		var endPos = myField.selectionEnd;
		myField.value = myField.value.substring(0, startPos) + myValue + myField.value.substring(endPos, myField.value.length);
	} else {
		myField.value += myValue;
	}
	myField.focus();
}

function deleteChecked(field, form, noselectMessage) {
	var nrChecked = 0;
	for (i = 0; i < field.length; i++) {
		if (field[i].checked == true) nrChecked++;
	}
	if (nrChecked > 0) {
		if (confirm("Weet u zeker dat u "+nrChecked+" items wilt verwijderen?")) {
			form.submit();
		}
	} else {
		alert(noselectMessage);
	}
}

function check(field, checkAll) {
	field.checked = checkAll.checked;
	for (i = 0; i < field.length; i++) {
		field[i].checked = checkAll.checked;
	}
}


function deleteSelected(list) {
	for (i = 0; i < list.length; i++) {
		var option = list.options[i];
		if (option.selected) {
			list.options[i] = null;
			i--;
		}
	}
}

function selectAll(list) {
	for (i = 0; i < list.length; i++) {
		list.options[i].selected = true;
	}
	return true;
}

function moveSelected(list1, list2) {
	for (i = list1.length -1; i >= 0; i--) {
		var option = list1.options[i];
		if (option.selected) {
			var newOption = new Option(option.text, option.value);
			var index = list2.length;
			list2.options[index] = newOption;
			list1.options[i] = null;
		}
	}
}

function implode(glue, list) {
	var result = '';
	for (i = 0; i < list.length; i++) {
		result = result + list.options[i].value;
		if (i < (list.length-1)) result = result + '' + glue;
	}
	return result;
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
 	//win =
 	window.open(url, name, params);
}

function popupWindowScrollbar(url, sizeX, sizeY, name, scrollbar) {
	if (sizeX == -1 && sizeY == -1) {
 		sizeX  = window.screen.availWidth-20;
 		sizeY  = window.screen.availHeight-30;
 	}
 	params = "toolbar=0, location=0, directories=0, status=0, menubar=0, left=0,top=0,scrollbars="+scrollbar+", resizable=1, width="+sizeX+", height="+sizeY;
 	//win =
 	window.open(url, name, params);
 }

function popupWindowFullScreen(url,name, scrollbar) {
 	params = "fullscreen=yes, toolbar=0, location=0, directories=0, status=0, menubar=0, scrollbars="+scrollbar+", resizable=0, width="+window.screen.availWidth+", height="+window.screen.availHeight;
 	//win =
 	window.open(url, name, params);
}

var isNN = (navigator.appName.indexOf("Netscape")!=-1);

var fieldDisJump = 0;

function autoTab(input,len, e) {
	var now = new Date();
	var s = now.getSeconds();
	var m = now.getMinutes();
	var h = now.getHours();
	var ms = now.getMilliseconds();
	test = (((((h * 60) + m) * 60) + s) * 1000) + ms;

	var keyCode = (isNN) ? e.which : e.keyCode;
	var filter = (isNN) ? [0,8,9] : [0,8,9,16,17,18,37,38,39,40,46];
	if(input.value.length >= len && !containsElement(filter,keyCode))	{

		if (test < fieldDisJump) return;

		input.value = input.value.slice(0, len);
		var fieldIndex = (getIndex(input)+1) % input.form.length;
		input.form[fieldIndex].focus();
		input.form[fieldIndex].select();
	}
	var now = new Date();
	var s = now.getSeconds();
	var m = now.getMinutes();
	var h = now.getHours();
	var ms = now.getMilliseconds();
	fieldDisJump = (((((h * 60) + m) * 60) + s) * 1000) + ms + 50;

	function containsElement(arr, ele) {
		var found = false, index = 0;
		while(!found && index < arr.length)
		if(arr[index] == ele)
		found = true;
		else
		index++;
		return found;
	}

	function getIndex(input) {
		var index = -1, i = 0, found = false;
		while (i < input.form.length && index == -1)
		if (input.form[i] == input)index = i;
		else i++;
		return index;
	}

	return true;
}

function isNumeric(sText) {
	var validChars = "0123456789";
	var character;
	for (i = 0; i < sText.length; i++) {
		character = sText.charAt(i);
		if (validChars.indexOf(character) == -1) {
			return false;
		}
	}
	return true;
}

function getMinuteValue(theForm, timeName) {
	var hour = theForm[timeName+'_hours'].value;
	var minute = theForm[timeName+'_minutes'].value;
	if (hour.length == 0) return -2;
	if (minute.length == 0) return -2;
	if (!isNumeric(hour)) return -1;
	if (!isNumeric(minute)) return -1;

	// http://www.breakingpar.com/bkp/home.nsf/Doc?OpenNavigator&U=87256B280015193F87256C85006A6604
	var intHour = parseInt(hour, 10);
	var intMinute = parseInt(minute, 10);
	if (intHour > 23) return -1;
	if (intMinute > 59) return -1;
	var result = (intHour * 60) + intMinute;
	return result;
}

function disableSubmit(element) {
	element.disabled = true;
	element.form.submit();
}

/*
* This function will not return until (at least)
* the specified number of milliseconds have passed.
* It does a busy-wait loop.
*/
function pause(numberMillis) {
		var now = new Date();
		var exitTime = now.getTime() + numberMillis;
		while (true) {
				now = new Date();
				if (now.getTime() > exitTime)
						return;
		}
}
