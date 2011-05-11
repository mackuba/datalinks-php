/****************************************************************************
 *   Copyright by Jakub Suder                                               *
 *                                                                          *
 *   This program is free software; you can redistribute it and/or modify   *
 *   it under the terms of the GNU General Public License as published by   *
 *   the Free Software Foundation; either version 2 of the License, or      *
 *   (at your option) any later version.                                    *
 ****************************************************************************/

// global vars
var scriptName;
var categoryId;
var parentId;
var selectedLinkId = null;

// XML DOM constants
var NodeTypes = {
	ELEMENT_NODE: 1,
	TEXT_NODE: 3
};



// --------------- general purpose functions ---------------

/* Makes sure that the string can be inserted into the ajax call parameter list. */
function escapeAjax(text) {
	return text.replace(/&/g, "%26");
}

/* Adds a "http://" prefix to the url, if it hasn't got the protocol part at all. */
function addHttp(url) {
	if ((url.indexOf('http://') == -1) && (url.indexOf('ftp://') == -1) && (url.indexOf('https://') == -1)) {
		url = 'http://' + url;
	}
	return url;
}

/* (debugging function) */
function logger(txt) {
	$('titleBar').innerHTML += "<br />" + txt + "\n";
}

/* Returns the (x,y) position of the top-left corner of the selected element on the page. */
function findElementPosition(element) {
	curleft = 0;
	curtop = 0;
	if (element.offsetParent) {
		curleft = element.offsetLeft;
		curtop = element.offsetTop;
		while (element = element.offsetParent) {
			curleft += element.offsetLeft;
			curtop += element.offsetTop;
		}
	}
	return [curleft, curtop];
}

/* Returns the (x,y) page coordinates of the top-left corner of the part of the page which is visible on the screen. */
function getScrollPosition() {
	scrOfX = 0;
	scrOfY = 0;
	if (typeof(window.pageYOffset) == 'number') {
		//Netscape compliant
		scrOfY = window.pageYOffset;
		scrOfX = window.pageXOffset;
	} else if (document.body && (document.body.scrollLeft || document.body.scrollTop)) {
		//DOM compliant
		scrOfY = document.body.scrollTop;
		scrOfX = document.body.scrollLeft;
	} else if (document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop)) {
		//IE6 standards compliant mode
		scrOfY = document.documentElement.scrollTop;
		scrOfX = document.documentElement.scrollLeft;
	}
	return [scrOfX, scrOfY];
}

/* Returns the width and height of the part of the browser window used to display the page. */
function getWindowSize() {
	myWidth = 0;
	myHeight = 0;
	if (typeof(window.innerWidth) == 'number') {
		//Non-IE
		myWidth = window.innerWidth;
		myHeight = window.innerHeight;
	} else if (document.documentElement && (document.documentElement.clientWidth || document.documentElement.clientHeight)) {
		//IE 6+ in 'standards compliant mode'
		myWidth = document.documentElement.clientWidth;
		myHeight = document.documentElement.clientHeight;
	} else if (document.body && (document.body.clientWidth || document.body.clientHeight)) {
		//IE 4 compatible
		myWidth = document.body.clientWidth;
		myHeight = document.body.clientHeight;
	}
	return [myWidth, myHeight];
}

/* Scrolls the page down until the whole element is visible on the screen. */
function scrollToShow(element) {
	pos = findElementPosition(element);
	dim = Element.getDimensions(element);
	bottomRight = [pos[0] + dim.width, pos[1] + dim.height];

	scrollPos = getScrollPosition();
	windowSize = getWindowSize();
	windowBottomRight = [scrollPos[0] + windowSize[0], scrollPos[1] + windowSize[1]];
	
	diff = [Math.max(bottomRight[0] - windowBottomRight[0], 0), Math.max(bottomRight[1] - windowBottomRight[1], 0)];

	window.scrollBy(diff[0], diff[1]);
}

/* Increases (or decreases, if val < 0) a value in the statistics box (total number of categories or links) by val. */
function addToStatistics(fieldName, val) {
	field = $(fieldName + 'Stats');
	oldVal = parseInt(field.innerHTML.split(' ')[0]);
	oldVal += val;
	field.innerHTML = oldVal + " " + (fieldName == "link" ?
		(oldVal == 1 ? "link" : "links") :
		(oldVal == 1 ? "category" : "categories"));
}


// --------------- xml functions ---------------

/* Tells if the node is an HTML tag tagName with class attribute tagClass. */
function isTag(node, tagName, tagClass) {
	return ((node.nodeType == NodeTypes.ELEMENT_NODE)
		&& ((tagName) ? (node.nodeName.toUpperCase() == tagName.toUpperCase()) : true)
		&& ((tagClass) ? (Element.hasClassName(node, tagClass)) : true));
}

/* Tells if the node is a <li class="entry">. */
function isLiEntry(node) {
	return isTag(node, 'li', 'entry');
}

/* Tells if the node is a <a class="title">. */
function isLinkTitle(node) {
	return isTag(node, 'a', 'title');
}

/* Tells if the node is a <a class="url">. */
function isLinkURL(node) {
	return isTag(node, 'a', 'url');
}

/* Returns the link title extracted from a <a class="title"> inside this div node. */
function getLinkTitle(divNode) {
	return $A(divNode.childNodes).find(isLinkTitle).innerHTML;
}

/* Checks if enter has been pressed in a text field. */
function wasEnterPressed(e) {
	var keyNum;
	if (window.event) { // IE
		keyNum = e.keyCode;
	} else if (e.which) { // all normal browsers
		keyNum = e.which;
	}
	return (keyNum == 13);
}

// --------------- field functions ---------------

/* If the field has white background, changes it to red, and if it's red, changes it back to white. */
function switchFieldColor(elementName) {
	element = $(elementName);
	if (element.style.backgroundColor.parseColor() == '#ff8080') {
		element.style.backgroundColor = '#ffffff';
	} else {
		element.style.backgroundColor = '#ff8080';
	}
}

/* Blinks the specified element (field) in red specified number of times. */
function highlightField(element, count) {
	if (count == undefined) count = 6; // 3 times to white, 3 times to red
	if (count != 0) {
		switchFieldColor(element);
		setTimeout("highlightField('" + element + "', " + (count - 1) + ")", 100);
	} else {
		Field.focus(element);
	}
}

/* Checks if the specified fields aren't empty, and if they are, highlight them in red.
	Returns false if any of them is empty. */
function checkRequiredFields() {
	bad = false;

	// browse the field list backwards, so that the first empty one is the one
	// that gets the focus in the end
	for (i=checkRequiredFields.arguments.length-1; i>=0; i--) {
		field = checkRequiredFields.arguments[i];
		if ($F(field) == '') {
			highlightField(field);
			bad = true;
		}
	}

	return !bad;
}



// --------------- link search functions ---------------

/* Creates a piece of HTML representing a new link entry. */
function createLinkHTML(linkId, linkName, linkURL, linkDescription) {
	itemContents = "<li class=\"entry\" id=\"link" + linkId + "\">\n"
		+ "<a href=\"" + linkURL + "\" class=\"title\">" + linkName + "</a><br />\n"
		+ "<div class=\"description\">" +linkDescription + "</div>\n"
		+ "<a href=\"" + linkURL + "\" class=\"url\">" + linkURL + "</a>\n"
		+ "- <a href=\"index.php#\" onclick=\"this.blur(); showLinkDialog('editLinkDialog', this, "
			+ linkId + "); resetEditLinkDialog(); return false;\" class=\"linkact\">Edit</a>\n"
		+ "- <a href=\"index.php#\" onclick=\"this.blur(); showLinkDialog('deleteLinkDialog', this, "
			+ linkId + "); return false;\" class=\"linkact\">Delete</a>\n"
		+ "- <a href=\"index.php#\" onclick=\"this.blur(); resetMoveLinkDialog(); showLinkDialog('moveLinkDialog', this, "
			+ linkId + "); return false;\" class=\"linkact\">Move</a>\n"
		+ "<br class=\"lynx\" /><br class=\"lynx\" />\n"
		+ "</li>\n";
	return itemContents;
}

/* Finds a place to insert a new link into the link list and returns the <li> element of the last link before it. */
function findLinkInsertPosition(linkName) {
	// make a list of all <li class="entry"> elements and nothing else
	list = $A($('linksOl').childNodes).findAll(isLiEntry);

	// find a first <li> whose title is greater than linkName, or null if all titles are less than linkName
	return list.find(function(node) {
		ltitle = getLinkTitle(node);
		return (ltitle.toUpperCase() > linkName.toUpperCase());
	});
}

/* Removes a link's <li> from the link list. */
function removeLinkFromList(linkId) {
	node = $('link' + linkId);
	if (node) {
		Element.remove(node);
		if ($('linksOl').innerHTML.replace(/[ \n]/g, '').length == 0) {
			Element.remove($('linksOl'));
		}
	}
}

/* Inserts a link's <li> at an appropriate place in the link list. */
function insertLinkIntoList(linkId, linkName, linkURL, linkDescription) {
	// create the <ol> element, if it's not present
	if ($('linksOl') == undefined) {
		new Insertion.Bottom($('links'), "<ol id=\"linksOl\">\n</ol>\n");
	}

	node = findLinkInsertPosition(linkName);
	itemContents = createLinkHTML(linkId, linkName, linkURL, linkDescription);
	
	if (node != null) {
		new Insertion.Before(node, itemContents);
	} else {
		new Insertion.Bottom($('linksOl'), itemContents);
	}
}

/* Edits a link's <li> and moves it into an appropriate place in the link list. */
function editLinkInList(linkId, linkName, linkURL, linkDescription) {
	node = $('link' + linkId);
	if (node) {
		linkList = $('linksOl');
		Element.remove(node);
		newPlace = findLinkInsertPosition(linkName);
		itemContents = createLinkHTML(linkId, linkName, linkURL, linkDescription);

		if (newPlace != null) {
			new Insertion.Before(newPlace, itemContents);
		} else {
			new Insertion.Bottom(linkList, itemContents);
		}
	}
}



// --------------- dialog functions ---------------

/* Shows or hides a specified dialog, depending on what state it is in at the moment.
	If fieldToFocus is given, focuses the field as soon as the operation is finished. */
function switchDialog(dialog, fieldToFocus, after) {
	Effect.toggle(dialog, 'appear', {duration: 0.3, afterFinish: function() {
		if (fieldToFocus) {
			try {
				// IE can have problems with this
				Field.focus(fieldToFocus);
			} catch (err) {
				// ignore it
			}
		}
		if (after) {
			eval(after);
		}
	}});
}

function setDialogInverted(dialog, inverted) {
	leftTriangle = $A(dialog.childNodes).find(function(node) {return isTag(node, "img", "dialogTriangle");});
	rightTriangle = $A(dialog.childNodes).find(function(node) {return isTag(node, "img", "dialogRightTriangle");});
	if (inverted) {
		Element.hide(leftTriangle);
		Element.show(rightTriangle);
		// for ie7:
		rightTriangle.style.visibility = "visible";
		leftTriangle.style.visibility = "hidden";
	} else {
		Element.show(leftTriangle);
		Element.hide(rightTriangle);
		// for ie7:
		rightTriangle.style.visibility = "hidden";
		leftTriangle.style.visibility = "visible";
	}
}

/* Shows the dialog under the link which triggers this action. */
function showDialogAtLink(dialogName, theLink, fieldToFocus, dist) {
	linkPos = findElementPosition(theLink);
	dim = Element.getDimensions(theLink);
	dialog = $(dialogName);

	dlgdim = Element.getDimensions(dialog);
	rightEnd = linkPos[0] + 10 + dlgdim.width;
	scrollPos = getScrollPosition();
	windowSize = getWindowSize();
	windowRightEnd = scrollPos[0] + windowSize[0];
	invertedDialog = (rightEnd > windowRightEnd);
	setDialogInverted(dialog, invertedDialog);
	
	if (dist == undefined) dist = 1.35;
	dialog.style.left = ((invertedDialog) ? (linkPos[0] + 10 - dlgdim.width) : (linkPos[0] + 10)) + "px";
	dialog.style.top = (linkPos[1] + Math.round(dim.height * dist)) + "px";
	switchDialog(dialogName, fieldToFocus, "scrollToShow($('" + dialogName + "'));");
}

/* Alias for switchDialog (used only to hide the dialog, not to show it). */
function hideDialog(dialogName) {
	switchDialog(dialogName);
}

/* Hides all other category dialogs, then shows this one. */
function showCategoryDialog(dialogName, theLink, focusName) {
	categoryDialogs = Array('editCategoryDialog', 'deleteCategoryDialog', 'moveCategoryDialog');
	categoryDialogs.each(function(dname) {
		dialog = $(dname);
		if ((dname != dialogName) && (dialog) && (dialog.style.display != 'none')) {
			hideDialog(dname);
			switchCategoryButton($(dname.replace("Dialog", "Link")));
		}
	});

	switchCategoryButton(theLink);
	show = ($(dialogName).style.display == 'none');
	showDialogAtLink(dialogName, theLink, focusName);
}

/* Hides all other link dialogs, then shows this one. */
function showLinkDialog(dialogName, theLink, linkId, focusName) {
	oldLinkId = selectedLinkId;
	selectedLinkId = linkId;

	linkDialogs = Array('editLinkDialog', 'deleteLinkDialog', 'moveLinkDialog');
	linkDialogs.each(function(dname) {
		dialog = $(dname);
		if ((dname != dialogName) && (dialog) && (dialog.style.display != 'none')) {
			hideDialog(dname);
		} else if ((dname == dialogName) && (linkId != oldLinkId) && (dialog)) {
			dialog.style.display = 'none';
		}
	});

	show = ($(dialogName).style.display == 'none');
	showDialogAtLink(dialogName, theLink, focusName, 1.75);
}

/* Shows or hides addLinkDialog or addCategoryDialog. */
function showAddDialog(dialogName, focusName) {
	show = ($(dialogName).style.display == 'none');
	switchDialog(dialogName, focusName);
	if (show) setTimeout("scrollToShow($('" + dialogName + "'));", 100);
}

/* Reads the link name, url and description from the list and enters its data into the edit link dialog. */
function resetEditLinkDialog() {
	node = $('link' + selectedLinkId);
	
	if (node) {
		$A(node.childNodes).each(function(node) {
			if (isLinkTitle(node)) {
				$('editLinkName').value = node.innerHTML.unescapeHTML();
			} else if (isLinkURL(node)) {
				$('editLinkURL').value = node.innerHTML.unescapeHTML();
			} else if (isTag(node, 'div', 'description')) {
				$('editLinkDescription').value = node.innerHTML.unescapeHTML();
			}
		});
	}
}

/* Reads the name of the current category from the path in the category bar and puts it in the edit category dialog. */
function resetEditCategoryDialog() {
	cname = $('currentCategory').innerHTML.unescapeHTML();
	$('editCategoryName').value = cname;
	
	categoryType = $('currentCategory').attributes.getNamedItem("class").value;
	if (categoryType == "hidden") {
		$('typeHidden').checked = true;
	} else if (categoryType == "private") {
		$('typePrivate').checked = true;
	} else {
		$('typePublic').checked = true;
	}
}

/* Initializes the moveLinkDialog. */
function resetMoveLinkDialog() {
	if (!Element.visible($('moveLinkDialog'))) {
		reloadMoveDialog(categoryId, 'Link');
	}
}

/* Starts loading a category list in the internal frame of moveCategoryDialog or moveLinkDialog. */
function reloadMoveDialog(category, objectType) {
	frame = $('move' + objectType + 'Frame');
		
	new Ajax.Request(scriptName, {
		method: 'post',
		parameters: 'action=show' + objectType + 'Frame&cat=' + categoryId + '&show=' + category,
		onLoading: function(request) {
			frame.innerHTML = "<p style=\"text-align: center;\"><span class=\"throbberLabelInfo\">Loading...</span>"
				+ "<img class=\"throbber\" src=\"images/throbber_dblue.gif\" width=\"16\" height=\"16\" alt=\"\" /></p>";
		},
		onSuccess: function(request) {
			if (request.responseText.substring(0, 2) == "OK") {
				frame.innerHTML = request.responseText.substring(2);
			} else {
				frame.innerHTML = '';
				alert("Error: " + request.responseText);
			}
		},
		onFailure: function(request) {
			frame.innerHTML = '';
			alert("Error connecting to the server: " + request.statusText);
		}
	});
}

/* Initializes the move category dialog. */
function resetMoveCategoryDialog() {
	if (!Element.visible($('moveCategoryDialog'))) {
		reloadMoveDialog(parentId, 'Category');
	}
}

/* Shows or hides one of the buttons in the category path bar. */
function switchCategoryButton(element) {
	if (element.style.backgroundColor.parseColor() == "#90b8ef") {
		element.style.backgroundColor = "#a9c9ef";
		element.style.borderTopColor = "#e0e0ff";
		element.style.borderLeftColor = "#e0e0ff";
		element.style.borderBottomColor = "#8080a0";
		element.style.borderRightColor = "#8080a0";
	} else {
		element.style.backgroundColor = "#90b8ef";
		element.style.borderTopColor = "#8080a0";
		element.style.borderLeftColor = "#8080a0";
		element.style.borderBottomColor = "#e0e0ff";
		element.style.borderRightColor = "#e0e0ff";
	}
}



// --------------- link management functions ---------------

/* Adds a link in the database using AJAX, then adds it to the list. */
function addLink() {
	if (!checkRequiredFields('addLinkURL', 'addLinkName')) {
		return;
	}

	linkName = $F('addLinkName');
	linkURL = addHttp($F('addLinkURL'));
	linkDescription = $F('addLinkDescription');

	new Ajax.Request(scriptName, {
		method: 'post',
		parameters: 'action=addLink&cat=' + categoryId + '&name=' + escapeAjax(linkName) + '&url=' + escapeAjax(linkURL) + '&desc='
			+ escapeAjax(linkDescription),
		onLoading: function(request) {
			Element.show('addLinkThrobber');
			Element.show('addLinkInfo');
			Element.hide('addLinkAdd');
			Element.hide('addLinkCancel');
		},
		onSuccess: function(request) {
			Element.hide('addLinkThrobber');
			Element.hide('addLinkInfo');
			Element.show('addLinkAdd');
			Element.show('addLinkCancel');
			response = request.responseText.split(" ");
			if (response[0] == "OK") {
				hideDialog('addLinkDialog');
				Field.clear('addLinkName');
				Field.clear('addLinkURL');
				Field.clear('addLinkDescription');

				linkId = response[1];
 				insertLinkIntoList(linkId, linkName.escapeHTML(), linkURL.escapeHTML(), linkDescription.escapeHTML());
				appearLink(linkId);
				addToStatistics('link', 1);
			} else {
				alert("Error: " + request.responseText);
			}
		},
		onFailure: function(request) {
			Element.hide('addLinkThrobber');
			Element.hide('addLinkInfo');
			Element.show('addLinkAdd');
			Element.show('addLinkCancel');
			alert("Error connecting to the server: " + request.statusText);
		}
	});
}

/* Edits a link in the database using AJAX, then edits it in the list. */
function editLink() {
	if (!checkRequiredFields('editLinkURL', 'editLinkName')) {
		return;
	}

	linkName = $F('editLinkName');
	linkURL = addHttp($F('editLinkURL'));
	linkDescription = $F('editLinkDescription');

	new Ajax.Request(scriptName, {
		method: 'post',
		parameters: 'action=editLink&link=' + selectedLinkId + '&name=' + escapeAjax(linkName) + '&url=' + escapeAjax(linkURL)
			+ '&desc=' + escapeAjax(linkDescription),
		onLoading: function(request) {
			Element.show('editLinkThrobber');
			Element.show('editLinkInfo');
			Element.hide('editLinkSave');
			Element.hide('editLinkReset');
			Element.hide('editLinkCancel');
		},
		onSuccess: function(request) {
			Element.hide('editLinkThrobber');
			Element.hide('editLinkInfo');
			Element.show('editLinkSave');
			Element.show('editLinkReset');
			Element.show('editLinkCancel');
			if (request.responseText == "OK") {
				hideDialog('editLinkDialog');
				editLinkInList(selectedLinkId, linkName.escapeHTML(), linkURL.escapeHTML(), linkDescription.escapeHTML());
			} else {
				alert("Error: " + request.responseText);
			}
		},
		onFailure: function(request) {
			Element.hide('editLinkThrobber');
			Element.hide('editLinkInfo');
			Element.show('editLinkSave');
			Element.show('editLinkReset');
			Element.show('editLinkCancel');
			alert("Error connecting to the server: " + request.statusText);
		}
	});
}

/* Deletes a link from the database using AJAX, then deletes it from the list. */
function deleteLink() {
	new Ajax.Request(scriptName, {
		method: 'post',
		parameters: 'action=deleteLink&link=' + selectedLinkId,
		onLoading: function(request) {
			Element.show('deleteLinkThrobber');
			Element.show('deleteLinkInfo');
			Element.hide('deleteLinkNo');
			Element.hide('deleteLinkYes');
		},
		onSuccess: function(request) {
			Element.hide('deleteLinkThrobber');
			Element.hide('deleteLinkInfo');
			Element.show('deleteLinkNo');
			Element.show('deleteLinkYes');
			if (request.responseText == "OK") {
				hideDialog('deleteLinkDialog');
				disappearLink(selectedLinkId);
				addToStatistics('link', -1);
			} else {
				alert("Error: " + request.responseText);
			}
		},
		onFailure: function(request) {
			Element.hide('deleteLinkThrobber');
			Element.hide('deleteLinkInfo');
			Element.show('deleteLinkNo');
			Element.show('deleteLinkYes');
			alert("Error connecting to the server: " + request.statusText);
		}
	});
}

/* Moves this link to another category in the database using AJAX, then deletes it from the list. */
function moveLink() {
	destination = $('frameLinkCurrentCategory').innerHTML;
	if (destination == categoryId) {
		hideDialog('moveLinkDialog');
		return;
	}

	new Ajax.Request(scriptName, {
		method: 'post',
		parameters: 'action=moveLink&link=' + selectedLinkId + '&dest=' + destination,
		onLoading: function(request) {
			Element.show('moveLinkThrobber');
			Element.show('moveLinkInfo');
			Element.hide('moveLinkMove');
			Element.hide('moveLinkCancel');
		},
		onSuccess: function(request) {
			Element.hide('moveLinkThrobber');
			Element.hide('moveLinkInfo');
			Element.show('moveLinkMove');
			Element.show('moveLinkCancel');
			if (request.responseText == "OK") {
				hideDialog('moveLinkDialog');
				disappearLink(selectedLinkId);
			} else {
				alert("Error: " + request.responseText);
			}
		},
		onFailure: function(request) {
			Element.hide('moveLinkThrobber');
			Element.hide('moveLinkInfo');
			Element.show('moveLinkMove');
			Element.show('moveLinkCancel');
			alert("Error connecting to the server: " + request.statusText);
		}
	});
}

function appearLink(id) {
	new Effect.Highlight('link' + id, {duration: 3.0, startcolor: '#ffd060', endcolor: '#f0f4ff', restorecolor: '#f0f4ff'});
}

function disappearLink(selectedLinkId) {
	Effect.BlindUp('link' + selectedLinkId, {duration: 0.7, afterFinish: function() {removeLinkFromList(selectedLinkId);}});
}

// --------------- category management functions ---------------

/* Extracts category path bar from the inner frame to be inserted to the main page in place of the old bar. */
function extractCategoryPath(bar, newCategoryId, newCateg) {
	result = '';

	aTags = $A(bar.childNodes).findAll(function(x) {return isTag(x, 'a') && (x.id != 'frameCategoryCurrentCategory');});
	aTags.each(function(a) {
		onc = a.attributes['onclick'].value;
		catId = onc.match(/\d+/)[0];
		catName = a.innerHTML;
		catType = Element.hasClassName(a, 'public') ? "public" : (Element.hasClassName(a, 'private') ? "private" : "hidden");
		result += "<a class=\"" + catType + "\" href=\"index.php?cat=" + catId + "\">" + catName + "</a> &raquo; ";
	});
	return result;
}

/* Re-reads the subcategory list columns into memory. */
function examineLists() {
	leftColumn = examineColumn($('subcategoryListLeft'));
	rightColumn = examineColumn($('subcategoryListRight'));
}

/* Rescans a column of the subcategory list. */
function examineColumn(column) {
	if (!column) return new Array();

	table = new Array();

	items = $A(column.childNodes).findAll(function(node) {
		return isTag(node);
	});
	
	items.each(function(node) {
		link = $A(node.childNodes).find(function(node) {
			return isTag(node, 'a');
		});
		catName = link.innerHTML;
		table.push([node, catName]);
	});
	
	return table;
}

/* Moves subcategories from one column to another if the lengths of the columns differ too much. */
function equalizeLists(isleft) {
	llist = $('subcategoryListLeft');
	rlist = $('subcategoryListRight');

	if ((isleft) && (leftColumn.length == singleColumnLimit + 1) && (rightColumn.length == 0)) {
		half = Math.ceil((singleColumnLimit + 1) / 2);
		if (rlist == undefined) {
			new Insertion.Bottom($('subcategoryTdRight'), "<ul id=\"subcategoryListRight\">\n</ul>\n");
			rlist = $('subcategoryListRight');
		}
		for (i=leftColumn.length-1; i>=half; i--) {
			Element.remove(leftColumn[i][0]);
			new Insertion.Top(rlist, "<li>" + leftColumn[i][0].innerHTML + "</li>");
		}
		examineLists();
	} else if ((isleft) && (rightColumn.length > 0) && (leftColumn.length > rightColumn.length + 1)) {
		half = Math.ceil((leftColumn.length + rightColumn.length) / 2);
		for (i=leftColumn.length-1; i>=half; i--) {
			Element.remove(leftColumn[i][0]);
			new Insertion.Top(rlist, "<li>" + leftColumn[i][0].innerHTML + "</li>");
		}
		examineLists();
	} else if ((!isleft) && (leftColumn.length + 1 < rightColumn.length)) {
		half = Math.ceil((leftColumn.length + rightColumn.length) / 2);
		pos = half - leftColumn.length;
		for (i=0; i<pos; i++) {
			Element.remove(rightColumn[i][0]);
			new Insertion.Bottom(llist, "<li>" + rightColumn[i][0].innerHTML + "</li>");
		}
		examineLists();
	}
}

/* Finds a place to insert a new category into the column of category list and returns the <li> element of the last category before it. */
function findCategoryInsertPosition(list, categoryName) {
	found = list.find(function(element) {
		return (element[1].toUpperCase() > categoryName.toUpperCase());
	});

	if (found) {
		return found[0];
	} else {
		return null;
	}
}

/* Inserts a new category to the subcategory list. */
function insertCategory(categoryId, categoryName) {
	itemContents = "<li id=\"category" + categoryId + "\">\n"
		+ "<a href=\"index.php?cat=" + categoryId + "\">" + categoryName + "</a>\n"
		+ "<span class=\"amount\">(0)</span>\n"
		+ "</li>\n";

	isleft = true;
	node = findCategoryInsertPosition(leftColumn, categoryName);
	if (node != null) {
		new Insertion.Before(node, itemContents);
	} else {
		if (rightColumn.length > 0) {
			node = findCategoryInsertPosition(rightColumn, categoryName);
			if (node != null) {
				new Insertion.Before(node, itemContents);
				isleft = false;
			} else {
				if ($('subcategoryListRight') == undefined) {
					new Insertion.Bottom($('subcategoryTdRight'), "<ul id=\"subcategoryListRight\">\n</ul>\n");
				}
				new Insertion.Bottom($('subcategoryListRight'), itemContents);
				isleft = false;
			}
		} else {
			if ($('subcategoryListLeft') == undefined) {
				new Insertion.Bottom($('subcategoryTdLeft'), "<ul id=\"subcategoryListLeft\">\n</ul>\n");
			}
			new Insertion.Bottom($('subcategoryListLeft'), itemContents);
		}
	}

	examineLists();
	equalizeLists(isleft);
}

/* Adds a new subcategory in the database using AJAX, then inserts it into the list. */
function addCategory() {
	if (!checkRequiredFields('addCategoryName')) {
		return;
	}

	categoryName = $F('addCategoryName');
	dialog = $('addCategoryDialog');
	
	new Ajax.Request(scriptName, {
		method: 'post',
		parameters: 'action=addCategory&cat=' + categoryId + '&name=' + escapeAjax(categoryName),
		onLoading: function(request) {
			Element.show('addCategoryThrobber');
			Element.show('addCategoryInfo');
			Element.hide('addCategoryAdd');
			Element.hide('addCategoryCancel');
		},
		onSuccess: function(request) {
			Element.hide('addCategoryThrobber');
			Element.hide('addCategoryInfo');
			Element.show('addCategoryAdd');
			Element.show('addCategoryCancel');
			response = request.responseText.split(" ");
			if (response[0] == "OK") {
				hideDialog('addCategoryDialog');
				Field.clear('addCategoryName');
				insertCategory(response[1], categoryName.escapeHTML());
				appearCategory(response[1]);
				addToStatistics('category', 1);
			} else {
				alert("Error: " + request.responseText);
			}
		},
		onFailure: function(request) {
			Element.hide('addCategoryThrobber');
			Element.hide('addCategoryInfo');
			Element.show('addCategoryAdd');
			Element.show('addCategoryCancel');
			alert("Error connecting to the server: " + request.statusText);
		}
	});
}

/* Deletes this category from the database using AJAX, then moves to its parent category. */
function deleteCategory() {
	deleteLinks = $('alsoDeleteLinksYes').checked;

	new Ajax.Request(scriptName, {
		method: 'post',
		parameters: 'action=deleteCategory&cat=' + categoryId + (deleteLinks ? '&deleteLinks=1' : ''),
		onLoading: function(request) {
			Element.show('deleteCategoryThrobber');
			Element.show('deleteCategoryInfo');
			Element.hide('deleteCategoryYes');
			Element.hide('deleteCategoryNo');
			$('alsoDeleteLinksYes').disabled = true;
			$('alsoDeleteLinksNo').disabled = true;
		},
		onSuccess: function(request) {
			if (request.responseText == "OK") {
				location.href = scriptName + "?cat=" + parentId;
			} else {
				Element.hide('deleteCategoryThrobber');
				Element.hide('deleteCategoryInfo');
				Element.show('deleteCategoryYes');
				Element.show('deleteCategoryNo');
				$('alsoDeleteLinksYes').disabled = false;
				$('alsoDeleteLinksNo').disabled = false;
				alert("Error: " + request.responseText);
			}
		},
		onFailure: function(request) {
			Element.hide('deleteCategoryThrobber');
			Element.hide('deleteCategoryInfo');
			Element.show('deleteCategoryYes');
			Element.show('deleteCategoryNo');
			$('alsoDeleteLinksYes').disabled = false;
			$('alsoDeleteLinksNo').disabled = false;
			alert("Error connecting to the server: " + request.statusText);
		}
	});
}

/* Moves this category to another category in the database using AJAX, then reloads the page. */
function moveCategory() {
	destination = $('frameCategoryCurrentCategory').innerHTML;
	if (destination == parentId) {
		hideDialog('moveCategoryDialog');
		switchCategoryButton($('moveCategoryLink'));
		return;
	}

	new Ajax.Request(scriptName, {
		method: 'post',
		parameters: 'action=moveCategory&cat=' + categoryId + '&dest=' + destination,
		onLoading: function(request) {
			Element.show('moveCategoryThrobber');
			Element.show('moveCategoryInfo');
			Element.hide('moveCategoryMove');
			Element.hide('moveCategoryCancel');
		},
		onSuccess: function(request) {
			Element.hide('moveCategoryThrobber');
			Element.hide('moveCategoryInfo');
			Element.show('moveCategoryMove');
			Element.show('moveCategoryCancel');
			if (request.responseText == "OK") {
				hideDialog('moveCategoryDialog');
				switchCategoryButton($('moveCategoryLink'));
				div = $A($('categoryBar').childNodes).find(function(x) {return isTag(x, 'div');});
				path = $A(div.childNodes).find(function(x) {return isTag(x, 'span', 'path');});
				newBar = extractCategoryPath($('frameCategoryBar'));
				currentCategoryNode = $('currentCategory').cloneNode(true);
				path.innerHTML = newBar;
				path.appendChild(currentCategoryNode);
				parentId = destination;
			} else {
				alert("Error: " + request.responseText);
			}
		},
		onFailure: function(request) {
			Element.hide('moveCategoryThrobber');
			Element.hide('moveCategoryInfo');
			Element.show('moveCategoryMove');
			Element.show('moveCategoryCancel');
			alert("Error connecting to the server: " + request.statusText);
		}
	});
}

/* Edits this category in the database using AJAX, then updates the name and the color in the category bar. */
function editCategory() {
	categoryName = $F('editCategoryName');
	if (categoryName == '') {
		highlightField('editCategoryName');
		return;
	}

	if ($('typeHidden').checked) {
		categoryType = 'hidden';
	} else if ($('typePrivate').checked) {
		categoryType = 'private';
	} else {
		categoryType = 'public';
	}
	
	dialog = $('editCategoryDialog');
	new Ajax.Request(scriptName, {
		method: 'post',
		parameters: 'action=editCategory&cat=' + categoryId + '&name=' + escapeAjax(categoryName) + '&type=' + categoryType,
		onLoading: function(request) {
			Element.show('editCategoryThrobber');
			Element.show('editCategoryInfo');
			Element.hide('editCategorySave');
			Element.hide('editCategoryReset');
			Element.hide('editCategoryCancel');
		},
		onSuccess: function(request) {
			Element.hide('editCategoryThrobber');
			Element.hide('editCategoryInfo');
			Element.show('editCategorySave');
			Element.show('editCategoryReset');
			Element.show('editCategoryCancel');
			if (request.responseText == "OK") {
				hideDialog('editCategoryDialog');
				Field.clear('editCategoryName');
				switchCategoryButton($('editCategoryLink'));

				$('currentCategory').innerHTML = categoryName.escapeHTML();
				Element.removeClassName('currentCategory', 'hidden');
				Element.removeClassName('currentCategory', 'public');
				Element.removeClassName('currentCategory', 'private');
				Element.addClassName('currentCategory', categoryType);
			} else {
				alert("Error: " + request.responseText);
			}
		},
		onFailure: function(request) {
			dialog.style.width = '28em';
			Element.hide('editCategoryThrobber');
			Element.hide('editCategoryInfo');
			Element.show('editCategorySave');
			Element.show('editCategoryReset');
			Element.show('editCategoryCancel');
			alert("Error connecting to the server: " + request.statusText);
		}
	});
}

function appearCategory(id) {
	new Effect.Highlight('category' + id, {duration: 2.0, startcolor: '#ffd060', endcolor: '#f0f4ff', restorecolor: '#f0f4ff'});
}
