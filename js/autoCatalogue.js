/**
 * 图书自动编目处理，前端业务逻辑 JavaScript
 * 2012-06-22 22:29
 * (c)2012 All Rights Reserved
 * @author winguse
 * */

// 为什么每次Debug都是我自己……

"use strict";

/**
 * @param id 
 * @return DOM对象
 * */
function $$(id){
	return document.getElementById(id);
}


var SRC_NOTIONALLIB="NationalLibrary",SRC_USCONGRESS="LibiaryOfUSCongress",SRC_DEFAULT=SRC_NOTIONALLIB;
/**
 * 获取前端处理类
 * */
function AutoCatalogueFetcher(object_name){
	/*== 内部变量 ==*/
	var	ACF=this,status=new Array(),source=SRC_DEFAULT,autoFetch=true,
		isbnID="fetch-isbn",fetchQue=new Array(),myname=object_name,onDue=false;
	/*== 初始化 ==*/
	setInterval(dueFetchQue,200);
	if(myname==null)myname='acf';
	/*== 内部方法 ==*/
	/**
	 * 处理队列中的ISBN，间歇调用
	 * */
	function dueFetchQue(){
		if(fetchQue.length==0)return;
		if(onDue)return;//Only One Due At the same time;
		onDue=true;
		var f=fetchQue.shift();
		var isbn=f[0],source=f[1];
		status[isbn+source]=2;
		$("#"+isbn+source+"_status").removeClass();
		$("#"+isbn+source+"_status").addClass("label label-info");
		$("#"+isbn+source+"_status").text("处理");
		$("#"+isbn+source+"_main").html(
			'<div class="alert alert-info"><h4 class="alert-heading">处理中</h4>条目处理中，网络通信正在进行，可能需要一点时间……</div>'
		);
		$.get("autoCatalogue.php", {
			"action" : "query",
			"ISBN" : isbn,
			"type" : "JSON",
			"source":source,
			"r" : Math.random()
		}, function(data) {
			if(data.code == 1 || data.code != 0) {
				status[isbn+source] = -1;
				ShowMessage(data.message,MSG_ERROR);
				$("#"+isbn+source+"_status").removeClass();
				$("#"+isbn+source+"_status").addClass("label label-important");
				$("#"+isbn+source+"_status").text("失败");
				//TODO 其他错误处理
				$("#"+isbn+source+"_main").html(
					'<div class="alert alert-error"><h4 class="alert-heading">获取失败</h4>'+data.message+'</div>'
				);
			} else if(data.code == 0) {
				status[isbn+source] = 0;
				ShowMessage(data.message,MSG_INFO);
				$("#"+isbn+source+"_status").removeClass();
				$("#"+isbn+source+"_status").addClass("label label-success");
				$("#"+isbn+source+"_status").text("成功");
				$("#"+isbn+source+"_name").html(data.description.bookname);
				$("#"+isbn+source+"_name").attr("title", data.message);
				
				var trs = "";
				for(var i = 0; i < data.main.length; i++) {
					var item=data.main[i];
					trs +=
					/*	*/'<tr>' +
					/*		*/'<td>' + htmlEscape(item.clCode) + '</td>' +
					/*		*/'<td>' + htmlEscape(item.clName) + '</td>' +
					/*		*/'<td>' + htmlEscape(item.clValue) + '</td>' +
					/*	*/'</tr>';
				}
				$("#"+isbn+source+"_main").html(
				/*	*/'<h3>'+
				/*		*/'<div class="btn-group pull-right">' +
				/*			*/'<a class="btn btn-success" href="#historys?q='+data.description.isbn +'">'+
				/*				*/'<i class="icon-share-alt icon-white"></i> 显示详细信息 '+
				/*			*/'</a>'+
				/*		*/'</div>'+
				/*		*/htmlEscape(data.description.bookname)+
				/*		*/'<br /><small>'+ 
				/*			*/'ISBN: '+data.description.isbn +' / '+
				/*			*/DateStr(new Date(data.description.fetchTime*1000))+' / '+
				/*			*/data.description.source +' '+
				/*			*/'[<a href="' + data.description.url + '">#</a>]'+
				/*		*/'</small>'+
				/*	*/'</h3>' +
				/*	*/'<div id="detailInformation_' + isbn+source + '">' +
				/*		*/'<table summary="详细编目信息" class="table table-striped">' +
				/*			*/'<thead>' +
				/*			*/'<tr><th class="span1">记录号</th><th class="span2">字段名</th><th class="">值</th><tr>'+
				/*			*/'</thead>' +
				/*			*/'<tbody>' +
				/*				*/trs +
				/*			*/'</tbody>' +
				/*		*/'</table>' +
				/*	*/'</div>');
			}
			$("#"+isbn+source+"_name").append('<a class="close" style="float:none;" href="#" onclick="'+myname+'.deleteItem(\''+isbn+source+'\')">&times;</a>')
			onDue=false;// Finshed Due
		}, "json");
	};
	/**
	 * 检查ISBN是否合法
	 * @param isbn ISBN字符串
	 * @returns true ISBN正确， false 反之
	 * */
	function checkISBN(isbn){
		if(isbn.length>13){
			ShowMessage("ISBN超长！",MSG_WARNING);
			return false;
		}
		if(isbn.length==13){
			if(!/^978\d{9}[0-9xX]$/.test(isbn)){
				ShowMessage("ISBN格式错误！",MSG_WARNING);
				return false;
			}
		}
		if(isbn.length==10){//美国国会图书馆的一些ISBN？
			if(/^978\d{7}$/.test(isbn))return false;
			if(/^\d{9}[0-9xX]$/.test(isbn)){
				return true;
			}
		}
		if(isbn.length<13){
			ShowMessage("ISBN长度不足！",MSG_WARNING);
			return false;
		}
		return true;
	}
	/*== 外部调用接口 ==*/
	/**
	 * ISBN输入框内容检测，自动识别处理
	 * */
	ACF.autoFetchCallback=function(){
		if(!autoFetch)return;
		var isbn=$$(isbnID).value;
		if(isbn.length==0){
			ShowMessage("请输入ISBN号码以开始工作……",MSG_SUCCESS);
			return;
		}
		if(isbn.length==10&&!/^978\d{7}$/.test(isbn)){
			if(!checkISBN(isbn)){
				return;
			}
		}else if(isbn.length<13){
			ShowMessage("正在检测输入，已经收到 "+ isbn.length+" 位，请继续输入……",MSG_INFO);
			return;
		}
		if(!checkISBN(isbn)){
			return;
		}
		$$(isbnID).select();
		ACF.fetch(isbn);
	};
	ACF.manalFetch=function(){
		var isbn=$$(isbnID).value;
		if(!checkISBN(isbn)){
			return;
		}
		$$(isbnID).select();
		ACF.fetch(isbn);
	};
	/**
	 * 通信获得ISBN号对应的信息，以及显示到页面
	 * @param isbn 需要获取的ISBN号
	 * */
	ACF.fetch=function(isbn){
		if(status[isbn+source]==null){
			status[isbn+source]=1;
			fetchQue.push(new Array(isbn,source));
			$("#fetch_item_list").prepend(
			/*	*/'<li id="'+isbn+source+'_list">'+
			/*		*/'<a href="#'+isbn+source+'_main" data-toggle="tab">'+
			/*		*/'<span class="label" id="'+isbn+source+'_status">列队</span> <span id="'+isbn+source+'_name" title="">'+isbn+'</span></a>'+
			/*	*/'</li>'
			);
			$("#fetch_item_main").prepend(
			/*	*/'<div class="tab-pane" id="'+isbn+source+'_main">'+
			/*		*/'<div class="alert alert-warning"><h4 class="alert-heading">正在列队中</h4>当前条目正在等待其他条目完成后处理……</div>'+
			/*	*/'</div>'
			);
			return;
		}else if(status[isbn+source]==0){
			ShowMessage("ISBN: "+isbn+" 已经获取成功。",MSG_SUCCESS);
			return;
		}else if(status[isbn+source]<0){
			ShowMessage("ISBN: "+isbn+" 获取失败。",MSG_INFO);
			return;
		}else if(status[isbn+source]==1){
			ShowMessage("ISBN: "+isbn+" 正在列队中，请稍等……",MSG_INFO);
			return;
		}else if(status[isbn+source]>1){
			ShowMessage("ISBN: "+isbn+" 正在处理中，请稍等……",MSG_INFO);
			return;
		}
	};
	/**
	 * 设置当前获取数据的数据源
	 * @param scr 数据源名称，使用宏定义 SRC_NOTIONALLIB, SRC_USCONGRESS, SRC_DEFAULT
	 * */
	ACF.setSource=function(scr){
		source=scr;
		switch(source){
		case SRC_NOTIONALLIB:
			$("#current_source").text("中国国家图书馆");
			break;
		case SRC_USCONGRESS:
			$("#current_source").text("美国国会图书馆");
			break;
		}
	};
	/**
	 * 设置是否自动判断输入框然后处理
	 * @param af 是否，true, false
	 * */
	ACF.setAutoFetch=function(af){
		autoFetch=af;
		if(autoFetch){
			$("#fetch_submit>.btn").removeClass("btn-info");
			$("#fetch_submit>.btn").addClass("btn-primary");
		}else{
			$("#fetch_submit>.btn").removeClass("btn-primary");
			$("#fetch_submit>.btn").addClass("btn-info");
		}
	};
	/**
	 * 删除一个获取的条目，仅仅节目
	 * @param item ISBN+source的ID
	 * */
	ACF.deleteItem=function(item){
		$("#"+item+"_list").remove();
		$("#"+item+"_main").remove();
	};
	/**
	 * 显示界面
	 * */
	ACF.show=function(t){
		if(t==null)t=0;
		$("#fetch-control").fadeIn(t);
		$("#fetch-results").fadeIn(t);
	};
	/**
	 * 显示界面
	 * */
	ACF.hide=function(){
		$("#fetch-control").hide();
		$("#fetch-results").hide();
	};
}

/**
 * 历史记录管理类
 * */
function AutoCatalogueHistory(object_name){
	/*== 内部变量 ==*/
	var	page=0,pages=1,pagesize=10,keyword="",keywordID="history-keyword",ACH=this,myname=object_name,achData,atomLength=new Array();
	/*== 初始化 ==*/
	if(myname==null)myname='ach';
	/*== 内部方法 ==*/
	function dueHistoryItem(item){
		var ret={"list":"","main":"","callback":function(){}};
		var id=item.CatalogueId;
		var trs="";
		ret.list=
		/*	*/'<li id="history_'+id+'_list">'+
		/*		*/'<a href="#history_'+id+'_main" data-toggle="tab">'+
		/*			*/htmlEscape(item.BookName) + 
		/*		*/'</a>'+
		/*	*/'</li>';
		var info=item.CatalogueInfDTO;
		atomLength[id]=info.main.length;
		for(var j = 0; j < info.main.length; j++) {
			var clCode = htmlEscape(info.main[j].clCode);
			var clName = htmlEscape(info.main[j].clName);
			var clValue = htmlEscape(info.main[j].clValue);
			trs +=
			/*	*/'<tr id="CatalogueAtom_' + id + '_' + j + '">' +
			/*		*/'<td>' +
			/*			*/'<span id="atomCode_' + id + '_' + j + '">' + clCode + '</span>' +
			/*			*/'<input class="span1" type="text" maxlength="255" onchange="'+myname+'.editAtomCode(\'' +
			/*			*/	clCode + '\',' + id + ',' + j + ')" value="' + clCode + '" id="atomCodeInput_' + id + '_' + j + '" />' +
			/*		*/'</td>' +
			/*		*/'<td>' +
			/*			*/'<span id="atomName_' + id + '_' + j + '">' + clName + '</span>' +
			/*			*/'<input class="span2" type="text" maxlength="255" onchange="'+myname+'.editAtomName(\'' +
			/*			*/	clName + '\',' + id + ',' + j + ')" value="' + clName + '" id="atomNameInput_' + id + '_' + j + '" />' +
			/*		*/'</td>' +
			/*		*/'<td>' +
			/*			*/'<span id="atomValue_' + id + '_' + j + '">' + clValue + '</span>' +
			/*			*/'<input class="" style="width:90%" type="text" maxlength="255" onchange="'+myname+'.editAtomValue(\'' +
			/*			*/	clValue + '\',' + id + ',' + j + ')" value="' + clValue + '" id="atomValueInput_' + id + '_' + j + '" />' +
			/*		*/'</td>' +
			/*		*/'<td class="atomAction">' +
			/*			*/'<input class="btn btn-warning" type="button" id="atomDeleteButton_' + id + '_' + j + '" onclick="'+myname+'.deleteAtom(' + id + ',' + j + ')" value="删除记录" />' +
			/*		*/'</td>' +
			/*	*/'</tr>';
		}
		//alert(htmlEscape(item.BookName));
		ret.main=
		/*	*/'<div class="tab-pane" id="history_'+id+'_main">'+
		/*		*/'<h3>'+
		/*			*/'<div class="btn-group pull-right">' +
		/*				*/'<a class="btn btn-success dropdown-toggle" data-toggle="dropdown" href="#">'+
		/*					*/'<i class="icon-share-alt icon-white"></i> 导出数据 '+
		/*					*/'<b class="caret"></b>'+
		/*				*/'</a>'+
		/*				*/'<ul class="dropdown-menu" style="font-size:13px">'+
		/*					*/'<li><a href="autoCatalogue.php?action=getSourceMARC&id='+id+'">原始MARC</a></li>'+
		/*					*/'<li><a href="autoCatalogue.php?action=getSourceText&id='+id+'">原始文本</a></li>'+
		/*					*/'<li class="divider"></li>'+
		/*					*/'<li><a href="autoCatalogue.php?action=getText&id='+id+'">处理后文本</a></li>'+
		/*					*/'<li><a href="autoCatalogue.php?action=getCSV&id='+id+'">处理后CSV</a></li>'+
		/*				*/'</ul>'+
		/*			*/'</div>'+
		/*			*/'<span id="history_bookname_'+id+'_span" >'+htmlEscape(item.BookName)+'</span>'+
		/*			*/'<input onchange="$$(\'history_bookname_'+id+'_span\').innerHTML=this.value;" type="text" id="history_bookname_'+id+'_input" maxlength="255" class="span6" value="'+htmlEscape(item.BookName)+'"/><br />'+
		/*			*/'<small style="font-size:12px">  '+
		/*				*/'数据来源：'+item.Source+'[<a href="#">#</a>] / '+
		/*				*/'ISBN：'+item.ISBN+' / '+
		/*				*/'获取时间：<em>'+ item.LogTime+'</em> / '+
		/*				*/'最后修改时间：<em id="history_lastchecktime_'+id+'">'+ item.LastCheckTime+'</em> / '+
		/*				*/'UserID：'+item.LogUserId+'[<a href="#">#</a>]'+
		/*			*/'</small>'+
		/*		*/'</h3>'+
		/*		*/'<div>'+
		/*			*/'<div class="form-inline pull-left">' +
		/*				*/'<label for="history_needupdate_' +id + '">记录状态：</label>'+
		/*				*/'<select id="history_needupdate_' +id + '" disabled="disabled" class="span2">' +
		/*					*/'<option value="0">不需要更新 </option>' +
		/*					*/'<option value="1">需要更新编目号 </option>' +
		/*					*/'<option value="2">用户标记需要更新 </option>' +
		/*					*/'<option value="4">太旧 </option>' +
		/*				*/'</select> ' +
		/*				*/'<label for="history_trusted_' +id + '">信任否：</label>'+
		/*				*/'<select id="history_trusted_' + id + '" disabled="disabled" class="span1">' +
		/*					*/'<option value="0">否 </option>' +
		/*					*/'<option value="1">是 </option>' +
		/*				*/'</select>' +
		/*			*/'</div>'+
		/*			*/'<div class="pull-right" style="height:38px">'+
		/*				*/'<a href="#" class="btn btn-info" onclick="'+myname+'.rework(\''+id+'\');return false;"><i class="icon-repeat icon-white"></i> 离线重新处理</a> '+
		/*				*/'<a href="#" class="btn btn-primary" onclick="'+myname+'.openEdit(\''+id+'\');return false;"><i class="icon-edit icon-white"></i> 编辑</a> '+
		/*				*/'<a href="#" id="historyDeleteButton_'+id+'" class="btn btn-danger" onclick="'+myname+'.deleteItem(\''+id+'\');return false;"><i class="icon-trash icon-white"></i> 删除</a> '+
		/*			*/'</div>'+
		/*		*/'</div>'+
		/*		*/'<table class="table table-striped" id="history_table_'+id+'">'+
		/*			*/'<thead><tr><th class="span1">记录号</th><th class="span2">字段名</th><th class="" style="width:80%">值</th><th><input class="btn" value="关闭编辑" type="button" onclick="'+myname+'.closeEdit(\''+id+'\');" style="margin:0" /></th><tr></thead>'+
		/*			*/'<tbody>'+trs+'</tbody>'+
		/*		*/'</table>'+
		/*		*/'<div class="edit-control" id="edit_control_'+id+'">'+
		/*			*/'<button class="btn btn-primary" href="#" onclick="'+myname+'.submit(\''+id+'\');return false;"><i class="icon-ok icon-white"></i> 提交</button> '+
		/*			*/'<button class="btn btn-info" href="#" onclick="'+myname+'.addAtom(\''+id+'\');return false;"><i class="icon-plus-sign icon-white"></i> 增加一项</button> '+
		/*			*/'<button href="#" class="btn btn-danger" onclick="'+myname+'.refresh(\''+id+'\');return false;" title="重新从服务器获取"><i class="icon-refresh icon-white"></i> 重置</button> '+
		/*			*/'<button class="btn" href="#" onclick="'+myname+'.closeEdit(\''+id+'\');return false;"><i class="icon-ban-circle"></i> 关闭编辑</button>'+
		/*		*/'</div>'+
		/*	*/'</div>';
		ret.callback=function(){
			$$("history_needupdate_" + id).value = item.NeedUpdated;
			$$("history_trusted_" + id).value = item.Trusted;
			$$("history_needupdate_" + id).disabled = true;
			$$("history_trusted_" + id).disabled = true;
		};
		return ret;
	}
	/*== 外部接口 ==*/
	/**
	 * 重置搜索并载入默认条目
	 * */
	ACH.reset=function(){
		$$(keywordID).value="";
		ACH.search();
	};
	/**
	 * 搜索
	 * */
	ACH.search=function(kw){
//		if(keyword==$$(keywordID).value){
//			ShowMessage("搜索关键词未改变，无需重新载入。")
//			return;
//		}
		if(kw==null)
			keyword=$$(keywordID).value;
		else{
			$$(keywordID).value=keyword=kw;
		}
		ACH.load(1,true);
	};
	/**
	 * 加载
	 * @param page 第几页
	 * */
	ACH.load=function(pg,force){
		if(force==null)force=false;
		pg = parseInt(pg);
		if(!force&&(pg==page||(pg==-1&&page==pages))){
			ShowMessage("已经是当前页……",MSG_ERROR);
			return;
		}
		page=pg;
		if(page > pages || page == -1)
			page = pages;
		else if(page < 1)
			page = 1;
		$.get("autoCatalogue.php", {
			"action" : "getHistory",
			"page" : page,
			"pagesize":pagesize,
			"searchKeyword" : keyword
		}, function(data) {
			if(data.code == 0) {
				achData=data;
				atomLength=new Array();
				page = data.description.page;
				pages = data.description.maxPage;
				ShowMessage(data.message+"当前第 "+page+" 页，共 "+pages+" 页，"+data.description.recordsCount+" 条记录。",MSG_SUCCESS);
				$("#history_item_main").html("");
				$("#history_item_list").html("");
				//$("#recordsCount").html(data.description.recordsCount);
				//$("#maxPage").html(data.description.maxPage);
				//`CatalogueId`,`LogUserId`,`Source`,`ISBN`,`BookName`,`LogTime`,`LastCheckTime`,`NeedUpdated`,`Trusted`
				if(data.description.recordsCount != 0) {
					for(var i=0;i< data.main.length;i++) {////
						var item=data.main[i];
						var ret=dueHistoryItem(item);
						$("#history_item_list").append(ret.list);
						$("#history_item_main").append(ret.main);
						ret.callback();
					}
					$('#history_item_list a:first').tab('show');
					window.scrollTo(0);
				} else {
					ShowMessage("数据库中没有相关历史记录。",MSG_ERROR);
				}
			}
		}, "json");
	};
	/**
	 * 打开修改
	 * @param id 记录ID号
	 * */
	ACH.openEdit=function(id){
		$('#history_'+id+'_main input').show();
		$('#edit_control_'+id).show();
		$('#history_'+id+'_main span').hide();
		$$("history_needupdate_" + id).disabled = false;
		$$("history_trusted_" + id).disabled = false;
	};
	/**
	 * 关闭修改
	 * @param id 记录ID号
	 * */
	ACH.closeEdit=function(id){
		$('#edit_control_'+id).hide();
		$('#history_'+id+'_main input').hide();
		$('#history_'+id+'_main span').show();
		$$("history_needupdate_" + id).disabled = true;
		$$("history_trusted_" + id).disabled = true;
	};
	/**
	 * 提交
	 * @param id 记录ID号
	 * */
	ACH.submit=function(id){
		//	alert(id);
		var atom = new Array("", "", ""), item = {
			clCode : "",
			clName : "",
			clValue : "",
			bookname : "",
			needUpdated : 0,
			trusted : 0
		};
		item.bookname = $$('history_bookname_'+id+'_input').value;
		item.needUpdated = $$("history_needupdate_"+id).value;
		item.trusted = $$("history_trusted_"+id).value;
		$("#history_table_"+id+">tbody>tr").each(function(trIndex) {
			//alert(trIndex);
			$(this).children("td").each(function(tdIndex) {
				if(tdIndex < 3) {
					atom[tdIndex] = $(this).children("input").attr("value");
				}
			});
			if(trIndex) {
				item.clCode += "|";
				item.clName += "|";
				item.clValue += "|";
			}
			item.clCode += atom[0].replace(/\|/g, "\\|");
			//g是多次匹配
			item.clName += atom[1].replace(/\|/g, "\\|");
			item.clValue += atom[2].replace(/\|/g, "\\|");
		});
		$.post("autoCatalogue.php?action=editItem&id=" + id, item, function(data) {
			if(data.code == 0) {
				ShowMessage(data.message,MSG_INFO);
				ACH.refresh(id,"服务器上的数据已经更新，且");
			}else{
				ShowMessage(data.message,MSG_ERROR);
			}
		}, "json");
	};
	/**
	 * 让服务器离线重新处理
	 * @param id 记录ID号
	 * */
	ACH.rework=function(id){
		$.get("autoCatalogue.php", {
			"action" : "rework",
			"id" : id
		}, function(data) {
			if(data.code == 0) {
				ShowMessage("服务器已经处理完成，正在载入本地……",MSG_INFO);
				ACH.refresh(id,"服务器处理完成，且");
			}else{
				ShowMessage(data.message,MSG_ERROR);
			}
		}, "json");
	};
	/**
	 * 从服务器重新获取
	 * @param id 记录ID号
	 * */
	ACH.refresh=function(id,preMsg){
		if(preMsg==null)preMsg="";
		$.get("autoCatalogue.php", {
			"action" : "getItem",
			"id" : id
		}, function(data) {
			if(data.code == 0) {
				var ret=dueHistoryItem(data.main);
				$('#history_'+id+'_list').replaceWith(ret.list);
				$('#history_'+id+'_main').replaceWith(ret.main);
				ret.callback();
				$('#history_'+id+'_list >a').tab('show');
				ShowMessage(preMsg+'条目已经从服务器重新载入！',MSG_SUCCESS);
			}else{
				ShowMessage(data.message,MSG_ERROR);
			}
		}, "json");
	};
	/**
	 * 删除
	 * @param id 记录ID号
	 * */
	ACH.deleteItem=function(id){
		Asking(
			"你确定删除吗？",
			"你正在删除 ID 为 "+id+" 的记录，该操作不可撤销！",
			function(){
				$.get("autoCatalogue.php", {
					"action" : "deleteItem",
					"id" : id
				}, function(data) {
					if(data.code == 0) {
						ShowMessage(data.message,MSG_INFO);
						$('#history_'+id+'_main').remove();
						$('#history_'+id+'_list').remove();
						$('#history_item_list a:first').tab('show');
					}else{
						ShowMessage(data.message,MSG_ERROR);
					}
				}, "json");
			},function(){}
		);
	};
	/**
	 * 增加一个记录子项
	 * @param id 记录ID号
	 * */
	ACH.addAtom=function(id){
		var j=atomLength[id]++,clCode="",clName="",clValue="";
		$("#history_table_" + id + ">tbody").append(
		/*	*/'<tr id="CatalogueAtom_' + id + '_' + j + '">' +
		/*		*/'<td>' +
		/*			*/'<span style="display:none" id="atomCode_' + id + '_' + j + '">' + htmlEscape(clCode) + '</span>' +
		/*			*/'<input style="display:block" class="span1" type="text" maxlength="255" onchange="'+myname+'.editAtomCode(\'' +
		/*			*/	htmlEscape(clCode) + '\',' + id + ',' + j + ')" value="' + htmlEscape(clCode) + '" id="atomCodeInput_' + id + '_' + j + '" />' +
		/*		*/'</td>' +
		/*		*/'<td>' +
		/*			*/'<span style="display:none" id="atomName_' + id + '_' + j + '">' + htmlEscape(clName) + '</span>' +
		/*			*/'<input style="display:block" class="span2" type="text" maxlength="255" onchange="'+myname+'.editAtomName(\'' +
		/*			*/	htmlEscape(clName) + '\',' + id + ',' + j + ')" value="' + htmlEscape(clName) + '" id="atomNameInput_' + id + '_' + j + '" />' +
		/*		*/'</td>' +
		/*		*/'<td>' +
		/*			*/'<span style="display:none" id="atomValue_' + id + '_' + j + '">' + htmlEscape(clValue) + '</span>' +
		/*			*/'<input style="display:block;width:90%" class="" type="text" maxlength="255" onchange="'+myname+'.editAtomValue(\'' +
		/*			*/	htmlEscape(clValue) + '\',' + id + ',' + j + ')" value="' + htmlEscape(clValue) + '" id="atomValueInput_' + id + '_' + j + '" />' +
		/*		*/'</td>' +
		/*		*/'<td class="atomAction">' +
		/*			*/'<input style="display:block" class="btn btn-warning" type="button" id="atomDeleteButton_' + id + '_' + j + '" onclick="'+myname+'.deleteAtom(' + id + ',' + j + ')" value="删除记录" />' +
		/*		*/'</td>' +
		/*	*/'</tr>');
		$$('atomCodeInput_' + id + '_' + j ).focus();
	};
	/**
	 * 编辑子项的 记录号 的CallBack
	 * @param id 记录ID号
	 * */
	ACH.editAtomCode=function(orignal,id,no){
		$$("atomCode_"+id+"_"+no).innerHTML = $$("atomCodeInput_"+id+"_"+no).value;
		//TODO 不太重要的内容，主要希望可以智能地判断，然后自动向翻译字段添加条目
	};
	/**
	 * 编辑子项的 字段名 的CallBack
	 * @param id 记录ID号
	 * */
	ACH.editAtomName=function(orignal,id,no){
		$$("atomName_"+id+"_"+no).innerHTML = $$("atomNameInput_"+id+"_"+no).value;
		//TODO 不太重要的内容，主要希望可以智能地判断，然后自动向翻译字段添加条目
	};
	/**
	 * 编辑子项的 值 的CallBack
	 * @param id 记录ID号
	 * */
	ACH.editAtomValue=function(orignal,id,no){
		$$("atomValue_"+id+"_"+no).innerHTML = $$("atomValueInput_"+id+"_"+no).value;
		//TODO 不太重要，比如合法性判断
	};
	/**
	 * 提交
	 * @param id 记录ID号
	 * */
	ACH.deleteAtom=function(id,no){
		if($$("atomDeleteButton_"+id+"_"+no).value == "删除记录") {
			$$("atomDeleteButton_"+id+"_"+no).value = "确认删除";
			$("#atomDeleteButton_"+id+"_"+no).removeClass("btn-warning");
			$("#atomDeleteButton_"+id+"_"+no).addClass("btn-danger");
			setTimeout(function() {
				if(!$$("atomDeleteButton_"+id+"_"+no))return;//FOR Deleted Before 3000ms
				$$("atomDeleteButton_"+id+"_"+no).value = "删除记录";
				$("#atomDeleteButton_"+id+"_"+no).removeClass("btn-danger");
				$("#atomDeleteButton_"+id+"_"+no).addClass("btn-warning");
			}, 3000);
			return;
		}
		$("#CatalogueAtom_"+id+"_"+no).remove();
	};
	/**
	 * 显示界面
	 * @param t 动画时间，默认0
	 * */
	ACH.show=function(t){
		if(t==null)t=0;
		$("#history-control").fadeIn(t);
		$("#fetch-historys").fadeIn(t);
	};
	/**
	 * 隐藏界面
	 * */
	ACH.hide=function(){
		$("#history-control").hide();
		$("#fetch-historys").hide();
	};
	/**
	 * 上一页
	 * */
	ACH.previous=function(){
		if(page-1<1){
			ShowMessage("已经是第一页……",MSG_WARNING);
			return;
		}
		ACH.load(page-1);
	};
	/**
	 * 下一页
	 * */
	ACH.next=function(){
		if(page+1>pages){
			ShowMessage("已经是最后一页……",MSG_WARNING);
			return;
		}
		ACH.load(page+1);
	};
}

function AutoCatalogueCodeAndName(obj_name){
	/*== 内部变量 ==*/
	var workType="Code2Name",ACCN=this,myname=obj_name||"accn",inited=false;
	/*== 初始化 ==*/
	/*== 内部方法 ==*/
	/*== 外部接口方法 ==*/
	/**
	 * 设置工作模式，因为字段名和记录号两者处理方式相似，故合成一个
	 * @param wt 工作模式，字符串，Code2Name OR Name2Code
	 * */
	ACCN.setWorkType=function(wt){
		if(wt=="Code2Name")
			workType="Code2Name";
		else
			workType="Name2Code";
	};
	/**
	 * 更新一个子项目
	 * @param part 是字段名，还是记录号 Code OR Name
	 * @param id 记录ID
	 * */
	ACCN.updateItem=function(part, id) {
		var postData;
		if (part == "Code") {
			postData = {
				id : id,
				"CatalogueCode" : $("#cateCode_"+myname + id).attr("value")
			}
		} else {
			postData = {
				id : id,
				"CatalogueName" : $("#cateName_"+myname + id).attr("value")
			}
		}
		$.post("editCatalogueCodeAndName.php?action=update" + workType + "_" + part
				+ "&r=" + Math.random(), postData, function(data) {
			if (data.code != 0) {
				document.execCommand('undo');
				ShowMessage(data.message,MSG_ERROR);
			}else{
				ShowMessage(data.message,MSG_SUCCESS);
			}
		}, "json");
	};
	/**
	 * 删除一个记录
	 * @param id 记录ID
	 * */
	ACCN.deleteItem=function(id) {
		if ($("#deleteItem_"+myname + id).attr("value") != "确认删除") {
			$("#deleteItem_"+myname + id).attr("value", "确认删除");
			$("#deleteItem_"+myname + id).removeClass("btn-warning");
			$("#deleteItem_"+myname + id).addClass("btn-danger");
			setTimeout(function() {
				$("#deleteItem_"+myname + id).attr("value", "　删除　");
				$("#deleteItem_"+myname + id).removeClass("btn-danger");
				$("#deleteItem_"+myname + id).addClass("btn-warning");
			}, 3000);
			return;
		}
		$.post("editCatalogueCodeAndName.php?action=delete" + workType + "&r="
				+ Math.random(), {
			id : id,
			workType : workType
		}, function(data) {
			if (data.code == 0) {
				ShowMessage(data.message,MSG_SUCCESS);
				$("#Item_"+myname + id).fadeOut("slow", function() {
					$("#Item_"+myname + id).remove();
				});
			}else{
				ShowMessage(data.message,MSG_ERROR);
			}
		}, "json");
	};
	/**
	 * 增加一个记录
	 * @param e event触发事件
	 * @param lostFocus 是否失去焦点
	 * */
	ACCN.addItem=function(e, lostFocus) {
		e = e ? e : event;
		lostFocus = 'undefined';
		// 需要更加清晰地处理焦点失去的情况，避免误操作
		if (e.which != 13 && e.which != 1 && lostFocus == 'undefined')
			return;
		var CatalogueCode = $("#cateCode_add_"+myname).attr("value");
		if (CatalogueCode == "") {
			if (e.which == 13 || e.which == 1)
				$("#cateCode_add_"+myname).focus();
			return;
		}
		var CatalogueName = $("#cateName_add_"+myname).attr("value");
		if (CatalogueName == "") {
			if (e.which == 13 || e.which == 1)
				$("#cateName_add_"+myname).focus();
			return;
		}
		$.post("editCatalogueCodeAndName.php?action=add" + workType + "&r="
				+ Math.random(), {
			"CatalogueCode" : CatalogueCode,
			"CatalogueName" : CatalogueName
		}, function(data) {
			if (data.code == 0) {
				ShowMessage(data.message,MSG_INFO);
				ACCN.showItem(data.main.newId, CatalogueCode, CatalogueName, true);
				$("#cateCode_add_"+myname).attr("value", "");
				$("#cateCode_add"+myname).focus();
				$("#cateName_add_"+myname).attr("value", "");
			} else if (workType == "Code2Name") {
				ShowMessage(data.message,MSG_ERROR);
				$("#cateCode_add_"+myname).focus();
			} else {
				ShowMessage(data.message,MSG_ERROR);
				$("#cateName_add_"+myname).focus();
			}
		}, "json");
	};
	/**
	 * 显示一个项目
	 * @param Id 记录ID
	 * @param CatalogueCode 记录号
	 * @param CatalogueName 字段名
	 * @param ani 是否打开渐变动画
	 * */
	ACCN.showItem=function(Id, CatalogueCode, CatalogueName, ani) {
		var nameStr = '<div class="span4"><input placeholder="字段名" id="cateName_'+myname + Id
				+ '" type="text" maxlength="255" value="' + htmlEscape(CatalogueName)
				+ '" onchange="'+myname+'.updateItem(\'Name\',' + Id + ')" class="span4"/></div>';
		var codeStr = '<div class="span3"><input placeholder="记录号" id="cateCode_'+myname + Id
				+ '" type="text" maxlength="255" value="' + htmlEscape(CatalogueCode)
				+ '" onchange="'+myname+'.updateItem(\'Code\',' + Id + ')" class="span3" /></div>';
		var str;
		if (workType == "Code2Name") {
			str = codeStr + nameStr;
		} else {
			str = nameStr + codeStr;
		}
		$("#editContainer_"+myname).prepend(
				'<div class="p_row" id="Item_'+myname + Id + '">' + str
						+ ' <div class="span1"><input type="button" class="btn btn-warning" id="deleteItem_'+myname + Id
						+ '" value="　删除　" onclick="'+myname+'.deleteItem(' + Id + ')" />'
						+ '</div></div>');
		if (ani)
			$("#Item_"+myname + Id).fadeIn("slow");
		else
			$("#Item_"+myname + Id).show();
	};
	/**
	 * 页面载入
	 * */
	ACCN.pageInit=function() {
		if(inited)return;
		inited=true;
		$("#editContainer_"+myname).html("");
		$.get("editCatalogueCodeAndName.php?action=" + workType + "&r="
				+ Math.random(), function(data) {
			if (data.code == 0) {
				for (var id in data.main) {
					ACCN.showItem(id, data.main[id].Code, data.main[id].Name);
				}
				ShowMessage(data.message,MSG_INFO);
			} else {
				ShowMessage(data.message,MSG_ERROR);
				// TODO err
			}
		}, "json");
	};
}

function AutoCatalogueSettings(obj_name){
	/*== 内部变量 ==*/
	var ACS=this,myname=obj_name||"acs",nlLoaded=false,ucLoaded=false,usLoaded=false,nlCfgData,usCfgData;
	/*== 初始化 ==*/
	/*== 内部方法 ==*/
	/*== 外部接口方法 ==*/
	/**
	 * 显示界面
	 * @param t 动画时间，默认0
	 * */
	ACS.show=function(t){
		if(t==null)t=0;
		$("#fetch-settings").fadeIn(t);
	};
	/**
	 * 显示界面
	 * */
	ACS.hide=function(){
		$("#fetch-settings").hide();
	};
	/**
	 * 载入 中国国家图书馆 Xpath 参数
	 * @param force 强制加载，true or false
	 * */
	ACS.loadNationalLibiaryXPath=function(force){
		force=force||false;
		if(!force&&nlLoaded)return;
		nlLoaded=true;
		$.get(
			"EditFetcherConfig.php",
			{
				"action":"getNationalLibrayConfig"
			},
			function(data){
				if(data.code==0){
					ShowMessage(data.message,MSG_INFO);
					$("#cfg_national_libiary_main").html("");
					nlCfgData=data.main;
					for(var key in data.main){
						$("#cfg_national_libiary_main").append(
						/*	*/'<div class="control-group">'+
						/*		*/'<label class="control-label" for="cfg_nl_'+key+'">'+key+'： </label>'+
						/*		*/'<div class="controls"><input id="cfg_nl_'+key+'" class="span4" type="text" /></div>'+
						/*	*/'</div>'
						);
						$$("cfg_nl_"+key).value=data.main[key];
					}
					$("#cfg_national_libiary_main").append(
					/*	*/'<div class="form-actions">'+
					/*		*/'<button type="submit" class="btn btn-primary" onclick="'+myname+'.submitNationalLibiaryXPath(true)">提交</button>'+
					/*		*/'<button type="reset" class="btn" onclick="'+myname+'.loadNationalLibiaryXPath(true)">重置</button>'+
					/*	*/'</div>'
					);
				}else{
					ShowMessage(data.message,MSG_ERROR);
				}
			},
			"json"
		);
	};
	/**
	 * 载入 美国国会图书馆 Xpath 参数
	 * @param force 强制加载，true or false
	 * */
	ACS.loadLibiaryOfUSCongressXPath=function(force){
		force=force||false;
		if(!force&&usLoaded)return;
		usLoaded=true;
		$.get(
			"EditFetcherConfig.php",
			{
				"action":"getLibiaryOfUSCongressConfig"
			},
			function(data){
				if(data.code==0){
					ShowMessage(data.message,MSG_INFO);
					$("#cfg_us_congress_main").html("");
					usCfgData=data.main;
					for(var key in data.main){
						$("#cfg_us_congress_main").append(
						/*	*/'<div class="control-group">'+
						/*		*/'<label class="control-label" for="cfg_us_'+key+'">'+key+'： </label>'+
						/*		*/'<div class="controls"><input id="cfg_us_'+key+'" class="span4" type="text" /></div>'+
						/*	*/'</div>'
						);
						$$("cfg_us_"+key).value=data.main[key];
					}
					$("#cfg_us_congress_main").append(
					/*	*/'<div class="form-actions">'+
					/*		*/'<button type="submit" class="btn btn-primary" onclick="'+myname+'.submitLibiaryOfUSCongressXPath()">提交</button>'+
					/*		*/'<button type="reset" class="btn" onclick="'+myname+'.loadLibiaryOfUSCongressXPath(true)">重置</button>'+
					/*	*/'</div>'
					);
				}else{
					ShowMessage(data.message,MSG_ERROR);
				}
			},
			"json"
		);
	};
	/**
	 * 中国国家图书馆 XPath 数据提交
	 * */
	ACS.submitNationalLibiaryXPath=function(){
		for(var key in nlCfgData){
			nlCfgData[key]=$$("cfg_nl_"+key).value;
		}
		$.post(
			"EditFetcherConfig.php?action=setNationalLibrayConfig",
			nlCfgData,
			function(data){
				if(data.code==0){
					ShowMessage(data.message,MSG_SUCCESS);
				}else{
					ShowMessage(data.message,MSG_ERROR);
				}
			},
			"json"
		);
	};
	/**
	 * 美国国会图书馆 XPath 数据提交
	 * */
	ACS.submitLibiaryOfUSCongressXPath=function(){
		for(var key in usCfgData){
			usCfgData[key]=$$("cfg_us_"+key).value;
		}
		$.post(
			"EditFetcherConfig.php?action=setLibiaryOfUSCongressConfig",
			usCfgData,
			function(data){
				if(data.code==0){
					ShowMessage(data.message,MSG_SUCCESS);
				}else{
					ShowMessage(data.message,MSG_ERROR);
				}
			},
			"json"
		);
	};
}

var MSG_WARNING = 0, MSG_INFO = 1, MSG_ERROR = 2, MSG_SUCCESS = 3, msgTimeout = 10000, msgTimeoutHandle;
/**
 * @param message
 *            消息内容
 * @param type
 *            消息类型：MSG_WARNING, MSG_INFO, MSG_ERROR,MSG_SUCCESS
 * @return void Show message
 */
function ShowMessage(message, type) {
	type = type || MSG_WARNING;
	clearTimeout(msgTimeoutHandle);
	$("#messageOuter").hide();
	$("#message_main").html(message);
	$("#messageOuter").fadeIn(300);
	$("#messageContainer").removeClass();
	switch (type) {
	case MSG_WARNING:
		$("#messageContainer").addClass("alert fade in");
		break;
	case MSG_INFO:
		$("#messageContainer").addClass("alert alert-info fade in");
		break;
	case MSG_ERROR:
		$("#messageContainer").addClass("alert alert-error fade in");
		break;
	case MSG_SUCCESS:
		$("#messageContainer").addClass("alert alert-success fade in");
		break;
	}
	msgTimeoutHandle = setTimeout(function() {
		$("#messageOuter").fadeOut(500);
	}, msgTimeout);
}

function Asking(title,question,yesCallback,noCallback){
	$$("asking_title").innerHTML=title;
	$$("asking_content").innerHTML='<p>'+question+'</p>';
	$('#asking_yes').bind('click', yesCallback);
	$('#asking_no').bind('click', noCallback);
	$('#asking').modal();
}

function DateStr(d){
	return d.getFullYear()+'-'+(d.getMonth()+1)+'-'+d.getDate()+' '+d.getHours()+':'+d.getMinutes()+':'+d.getSeconds();
}

function Navigation() {
	$(".nav-collapse li.active").removeClass("active");
	var hash = location.hash;
	if(/^#historys\?q=.+$/.test(hash)){
		var keyword = hash.replace(/^#historys\?q=(.+)$/, "$1");
		history_load=false;
		document.title=title_name+" - 历史记录 - 查找 ["+keyword+"]";
		ach.search(keyword);
		acf.hide();
		acs.hide();
		$("#nav_history").addClass("active");
		ach.show(500);
		return;
	}
	
	switch(hash){
	case "":
	case "#main":
		ach.hide();
		acs.hide();
		acf.show(500);
		document.title=title_name+" - 首页"
		$("#nav_main").addClass("active");
		$$("fetch-isbn").focus();
		break;
	case "#settings":
		$("#nav_settings").addClass("active");
		ach.hide();
		acf.hide();
		acs.show(500);
		document.title=title_name+" - 设置"
		break;
	case "#historys":
		acf.hide();
		acs.hide();
		ach.show(500);
		if(history_load){
			ach.reset();
			history_load=false;
		}
		$("#nav_history").addClass("active");
		document.title=title_name+" - 历史记录"
		break;
	default:
		ShowMessage("Unexpected! ["+hash+"]",MSG_ERROR);
		break;
	}
	ShowMessage("当前位置："+document.title,MSG_SUCCESS);
}
/**
 * 转换HTML编码，注意XSS
 **/
function htmlEscape(str){
	return String(str)
		.replace(/<script>.*<\/script>/g,'')
		.replace(/&/g, '&amp;')
		.replace(/"/g, '&quot;')
		.replace(/'/g, '&#39;')
		.replace(/</g, '&lt;')
		.replace(/>/g, '&gt;');
}
/**
 * Debug需要
 */
window.onload=function(){
	$("body").append('<div id="ajaxError" style="display:none"></div>');
	$("#ajaxError").ajaxError(function(event,request, settings){
	    $(this).append("<li>出错页面:" + settings.url + "</li>");
	    ShowMessage("载入页面出错了：<a href='"+settings.url+"'>" + settings.url+"</a>，服务器可能出现错误了，请稍候再试……",MSG_ERROR);
	});
};
/* - - - - - - - - - - - - - - - - - */
var title_name="图书自动编目",history_load=true;
var acf=new AutoCatalogueFetcher();
var ach=new AutoCatalogueHistory();
var acs=new AutoCatalogueSettings();
var code2name=new AutoCatalogueCodeAndName("code2name");
code2name.setWorkType("Code2Name");
var name2code=new AutoCatalogueCodeAndName("name2code");
name2code.setWorkType("Name2Code");
$("#cateCode_add_code2name").focusout(function(event){
	code2name.addItem(event,true);
});
$("#cateName_add_code2name").focusout(function(event){
	code2name.addItem(event,true);
});
$("#cateName_add_name2code").focusout(function(event){
	name2code.addItem(event,true);
});
$("#cateCode_add_name2code").focusout(function(event){
	name2code.addItem(event,true);
});
//window.location="#main";
Navigation();
jQuery(window).bind(
	'hashchange',Navigation
);
