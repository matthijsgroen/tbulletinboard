/**
 * Functions for the Table class
 */

function removeCssClass(el, className) {
	if (!(el && el.className)) {
		return;
	}
	var cls = el.className.split(" ");
	var ar = new Array();
	for (var i = cls.length; i > 0;) {
		if (cls[--i] != className) {
			ar[ar.length] = cls[i];
		}
	}
	el.style.display = "none";
	el.className = ar.join(" ");
	el.style.display = "";
}

function hasCssClass(el, className) {
	if (!(el && el.className)) {
		return false;
	}
	var cls = el.className.split(" ");
	var ar = new Array();
	for (var i = cls.length; i > 0;) {
		if (cls[--i] == className) {
			return true;
		}
	}
	return false;
}

function addCssClass(el, className) {
	if (!(el && el.className)) {
		return false;
	}
	removeCssClass(el, className);
	el.style.display = "none";
	el.className += " " + className;
	el.style.display = "";
}

function changeGroup(rowIDarray, imgID, openImage, closeImage) {
	//alert(rowIDarray.length + " rijen!");
	var open = true;
	var element;
	for (var i = 0; i < rowIDarray.length; i++) {
		element = document.getElementById(rowIDarray[i]);
		if (hasCssClass(element, "closeGroup")) {
			removeCssClass(element, "closeGroup");
		} else {
			addCssClass(element, "closeGroup");
			open = false;
		}
	}
	var image = document.getElementById(imgID);

	if (open == true) {
		image.src = openImage;
	}	else {
		image.src = closeImage;
	}
}

function closeAll(rowIDs, imgIDs, closeImage, tableID) {
	var element;
	for (var i = 0; i < rowIDs.length; i++) {
		element = document.getElementById(tableID + rowIDs[i]);
		addCssClass(element, "closeGroup");
	}
	var image;
	for (var i = 0; i < imgIDs.length; i++) {
		image = document.getElementById(tableID + imgIDs[i] + "img");
		image.src = closeImage;
	}
}

var cellVarName;
var selectPreviousCell;
var selectCurrentCell;

function tableSelectPreviousCell() {
	var currSelect = document.getElementById(selectCurrentCell);
	removeCssClass(currSelect, 'selectedCell');
	if (selectPreviousCell != '') {
		var oldSelect = document.getElementById(selectPreviousCell);
		addCssClass(oldSelect, 'selectedCell');
	}
	eval(cellVarName+" = '"+selectPreviousCell+"';");
}

function check(checkFields, checkStatus) {
	for (var i = 0; i < checkFields.length; i++) {
		checkFields[i].checked = checkStatus.checked;
	}	
}
