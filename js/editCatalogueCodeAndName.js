function updateItem(part, id) {
	var postData;
	if (part == "Code") {
		postData = {
			id : id,
			"CatalogueCode" : $("#cateCode_" + id).attr("value")
		}
	} else {
		postData = {
			id : id,
			"CatalogueName" : $("#cateName_" + id).attr("value")
		}
	}
	$.post("editCatalogueCodeAndName.php?action=update" + workType + "_" + part
			+ "&r=" + Math.random(), postData, function(data) {
		if (data.code != 0) {
			document.execCommand('undo');
		}
		$("#messageContainer").html(data.message);
	}, "json");
}

function deleteItem(id) {
	if ($("#deleteItem_" + id).attr("value") != "确认删除") {
		$("#deleteItem_" + id).attr("value", "确认删除");
		setTimeout(function() {
			$("#deleteItem_" + id).attr("value", "删除");
		}, 3000);
		return;
	}
	$.post("editCatalogueCodeAndName.php?action=delete" + workType + "&r="
			+ Math.random(), {
		id : id,
		workType : workType
	}, function(data) {
		$("#messageContainer").html(data.message);
		if (data.code == 0) {
			$("#Item_" + id).fadeOut("slow", function() {
				$("#Item_" + id).remove();
			});
		}
	}, "json");
}

function addItem(e, lostFocus) {
	e = e ? e : event;
	lostFocus = 'undefined';
	// 需要更加清晰地处理焦点失去的情况，避免误操作
	if (e.which != 13 && e.which != 1 && lostFocus == 'undefined')
		return;
	CatalogueCode = $("#cateCode_add").attr("value");
	if (CatalogueCode == "") {
		if (e.which == 13 || e.which == 1)
			$("#cateCode_add").focus();
		return;
	}
	CatalogueName = $("#cateName_add").attr("value");
	if (CatalogueName == "") {
		if (e.which == 13 || e.which == 1)
			$("#cateName_add").focus();
		return;
	}
	$.post("editCatalogueCodeAndName.php?action=add" + workType + "&r="
			+ Math.random(), {
		"CatalogueCode" : CatalogueCode,
		"CatalogueName" : CatalogueName
	}, function(data) {
		$("#messageContainer").html(data.message);
		if (data.code == 0) {
			showItem(data.main.newId, CatalogueCode, CatalogueName, true);
			$("#cateCode_add").attr("value", "");
			$("#cateCode_add").focus();
			$("#cateName_add").attr("value", "");
		} else if (workType == "Code2Name") {
			$("#cateCode_add").focus();
		} else {
			$("#cateName_add").focus();
		}
	}, "json");
}

function showItem(Id, CatalogueCode, CatalogueName, ani) {
	nameStr = ' 编目译名： <input name="cateName_' + Id + '" id="cateName_' + Id
			+ '" type="text" maxlength="255" value="' + CatalogueName
			+ '" onchange="updateItem(\'Name\',' + Id + ')" />';
	codeStr = ' 编目号： <input name="cateCode_' + Id + '" id="cateCode_' + Id
			+ '" type="text" maxlength="255" value="' + CatalogueCode
			+ '" onchange="updateItem(\'Code\',' + Id + ')" />';
	if (workType == "Code2Name") {
		str = codeStr + nameStr;
	} else {
		str = nameStr + codeStr;
	}
	$("#editContainer").prepend(
			'<p id="Item_' + Id + '">' + str
					+ ' <input type="button" id="deleteItem_' + Id
					+ '" value="删除" onclick="deleteItem(' + Id + ')" />'
					+ '</p>');
	if (ani)
		$("#Item_" + Id).fadeIn("slow");
	else
		$("#Item_" + Id).show();
}

function pageInit() {
	$("#editContainer").html("");
	$.get("editCatalogueCodeAndName.php?action=" + workType + "&r="
			+ Math.random(), function(data) {
		if (data.code == 0) {
			for (id in data.main) {
				showItem(id, data.main[id].Code, data.main[id].Name);
			}
		} else {
			// TODO err
		}
	}, "json");
}

pageInit();