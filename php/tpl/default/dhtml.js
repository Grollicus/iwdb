var reqrunning = false;
var callback = false;
var Selects = new Object();
var AjaxRequests = 0;
var CollapseGroups = new Object();

function flugzeit(g1, s1, p1, g2, s2, p2, solspeed, galaspeed) {
	if(p1 == false && p2 == false) //Flug zwischen Stargates
		return 600;
	if(g1 == g2 && s1 == s2) { //gleiches System
		return 1500000*Math.log(Math.abs(p1-p2)+6)/solspeed;
	}
	if(!galaspeed)
		return false;
	var mod = g1 != g2 ? 100 : 5;
	var gal = Math.abs(g1-g2);
	var sol = Math.abs(s1-s2);
	var pla = Math.abs(p1-p2);
	return (15000000/galaspeed)*Math.pow((3000*gal*gal/Math.log(gal+50)+mod*sol*Math.max(3, sol)/Math.log(sol+2)+pla), 0.25);
}
function flugspeed(g1, s1, p1, g2, s2, p2, fz) {
	return flugzeit(g1, s1, p1, g2, s2, p2, fz, fz); //selbstinvers o.o
}
function formatdate(ts) {
	return moment.unix(ts).format("DD.MM.YYYY HH:mm:ss");
}

function getElById(id) {
	if(document.all) {
		return document.all[id];
	} else if (document.getElementById) {
		return document.getElementById(id);
	}
}

function viewLoadingState(state) {
	var el = getElById('dhtml_loading_header');
	if(state) {
		el.innerHTML = 'Loading...';
		el.style.backgroundColor = '#00FF00';
	} else {
		el.innerHTML = '';
		el.style.backgroundColor = '';
	}
}

function toggleVisibility(item) {
	if(item.style.display == 'none') {
		item.style.display = '';
	} else {
		item.style.display = 'none';
	}
}

function toggleTableRow(row) {
	if(row.style.display == 'none') {
		if(document.all) {
			if(window.opera) //Opera
				row.style.display = 'table-row';
			else //IE
				row.style.display = 'block';			
		} else //fx
			row.style.display = 'table-row';
	} else {
		row.style.display = 'none';
	}
}

function getXMLRequester() {
	var req = false;
	if(window.XMLHttpRequest) { //Moz, Safari
		req = new XMLHttpRequest();
		if(req.overrideMimeType)
			req.overrideMimeType('text/xml');
	} else if (window.ActiveXObject) {//IE
		try {
			req = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				req = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e) {}
		}
	}
	return req;
}

function AjaxRequest(action, params) {
	var req = getXMLRequester();
	if(params) {
		req.open('GET', scriptinterface+"?a="+action+"&sid="+sid+"&"+params, true);
	} else {
		req.open('GET', scriptinterface+"?a="+action+"&sid="+sid, true);
	}
	req.onreadystatechange = function() {
		AjaxCallback(req);
	}
	req.send(null);
	AjaxRequests++;
	viewLoadingState(true);
}

function requestData(url, postargs, callback, param) {
	reqrunning = getXMLRequester();
	if(!reqrunning)
		return false;
	if(postargs) {
		reqrunning.open('POST', url, true);
		reqrunning.onreadystatechange = callback;
		reqrunning.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		reqrunning.setRequestHeader("Content-length", postargs.length);
		reqrunning.setRequestHeader("Connection", "close");
	} else {
		reqrunning.open('GET', url, true);
		reqrunning.onreadystatechange = callback;
	}
	reqrunning.send(postargs);
	return true;
}

function scriptRequest(action, postargs, cb) {
	viewLoadingState(true);
	callback = cb;
	requestData(scriptinterface+"?a="+action+"&sid="+sid, postargs, scriptRequestCallback);
}
function scriptRequestCallback() {
	if(reqrunning.readyState == 4) {
		viewLoadingState(false);
		var req = reqrunning;
		reqrunning = false;
		if(req.status == 200) {
			callback(req);
		} else {
			alert("Request-Fehler: "+req.status);
		}
	}
}

function FillSelect(SelectID, XML, useValues) {
	if(useValues == null)
		useValues = true;
	var fillSelect = getElById(SelectID);
	var reselectValue = fillSelect.options[fillSelect.selectedIndex].value;
	
	var selectValueNodes = XML.firstChild.getElementsByTagName("select");
	if(selectValueNodes.length > 0) {
		reselectValue = selectValueNodes[0].textContent;
	}
	var data = XML.firstChild.getElementsByTagName("option");
	var options="";
	for(var i = 0; i < data.length; ++i) {
		var option = data[i];
		var value;
		if(useValues)
			value = option.getElementsByTagName("value")[0].textContent;
		else
			value = option.getElementsByTagName("description")[0].textContent;
		var desc = option.getElementsByTagName("description")[0].textContent;
		if(reselectValue == value) {
			options += "<option value=\""+value+"\" selected=\"selected\">"+desc+"<\/option>";
		} else {
			options += "<option value=\""+value+"\">"+desc+"<\/option>";
		}
	}
	fillSelect.innerHTML = options;
	return data.length;
}

function OnSelectChanged(SelectID, cb) {
	var sel = getElById(SelectID);
	Selects[SelectID] = {Select:sel, Callback:cb, Changed:false, InitialValue:false};
	
	sel.onfocus = SelectOnFocus;
	sel.onchange = SelectOnChange;
	sel.onkeydown = SelectOnKeyDown;
	sel.onclick = SelectOnClick;
	sel.onblur = SelectOnBlur;
}

function AddCollapsable(Category, ID) {
	if(!CollapseGroups[Category])
		CollapseGroups[Category] = new Array(ID);
	else
		CollapseGroups[Category].push(ID);
}
function ToggleCollapsables(Category) {
	var c = CollapseGroups[Category];
	var collapse = true;
	var i =0;
	for(i=0;i<c.length;i++) {
		if(getElById(c[i]).style.display == 'none') {
			collapse = false;
			break;
		}
	}
	for(i=0;i<c.length;i++) {
		var n = getElById(c[i]) 
		if(collapse) {
			n.style.display = 'none';
		} else {
			n.style.display = '';
		}
	}
}

function filterTable(tblId, colNum, needle) {
	var tbl = getElById(tblId);
	for(var i = 1; i < tbl.rows.length-1; ++i) {
		var row = tbl.rows[i];
		var cell = row.cells[colNum];
		if(cell.innerHTML.indexOf(needle) == -1) {
			if(row.style.display != 'none')
				toggleTableRow(row);
		} else {
			if(row.style.display == 'none')
				toggleTableRow(row);
		}
	}
}

/* Private Funktionen */

function AjaxCallback(req) {
	if(req.readyState == 4) {
		if(req.status != 200) {
			if(req.status != 0)
				alert("Fehler " + req.status + " beim Ajax-Request!");
		} else {
			var resp = req.responseXML;
			var setValues = resp.getElementsByTagName('setValue');
			for(var i = 0; i < setValues.length; ++i) {
				var setValue = setValues[i];
				var el = getElById(setValue.attributes.getNamedItem("elementId").value);
				el.innerHTML = setValue.textContent;
			}
			var setIDs = resp.getElementsByTagName('setID');
			for(var i = 0; i < setIDs.length; ++i) {
				var setID = setIDs[i];
				var el = getElById(setID.attributes.getNamedItem("elementId").value);
				el.id = setID.attributes.getNamedItem("newId").value;
			}
		}
		viewLoadingState(--AjaxRequests > 0);
	}
}

function SelectOnFocus() {
	var dta = Selects[this.id];
	dta.InitialValue = this.value;
	return true;
}
function SelectOnChange() {
	var dta = Selects[this.id];
	if(!dta.Changed)
		return false;
	dta.Callback();
	dta.Changed = false
	return true;
}
function SelectOnKeyDown(evt) {
	var dta = Selects[this.id];
	var evt = (evt) ? evt : ((event) ? event : null);
	if ((evt.keyCode == 9 || evt.keyCode == 13) && this.value != dta.InitialValue) { //enter & tab
		dta.Callback();
		dta.Changed = false;
	} else if(evt.keyCode == 27) { //escape
		this.value = dta.InitialValue;
	} else {
		dta.Changed = false;
	}
	return true;
}
function SelectOnClick() {
	var dta = Selects[this.id];
	dta.Changed = (dta.InitialValue != this.value);
}
function SelectOnBlur() {
	var dta = Selects[this.id];
	if(dta.Changed) {
		dta.Callback();
		dta.Changed = false;
	}
}
