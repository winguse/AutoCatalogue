<?php
?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta charset="utf-8" />
<title>图书自动编目</title>
<meta name="description" content="图书自动编目" />
<meta name="author" content="Winguse" />
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<!--[if lt IE 8]>
<script type="text/javascript">
	alert("对不起，由于本系统基于HTML5和CSS3新技术标准构建，IE8以下的浏览器使用本系统会有很多已知缺陷，出现非常糟糕的体验，请您升级您的浏览器！推荐使用Chrome或FireFox！");
</script>
<![endif]-->
<link rel="stylesheet" type="text/css" href="css/autoCatalogue.css"
	title="default" />
<link rel="stylesheet" type="text/css" href="css/bootstrap.css"
	title="default" />
<link rel="stylesheet" type="text/css"
	href="css/bootstrap-responsive.css" title="default" />
<meta name="description" content="图书自动编目 | wish you happy, shuxiao." />
<meta name="author" content="Winguse Ching" />
<link rel="shortcut icon" href="favicon.ico" />
</head>
<body>
<div class="navbar navbar-fixed-top">
<div class="navbar-inner">
<div class="container-fluid"><a class="btn btn-navbar"
	data-toggle="collapse" data-target=".nav-collapse"> <span
	class="icon-bar"></span> <span class="icon-bar"></span> <span
	class="icon-bar"></span> </a> <a class="brand" href="#">图书自动编目</a>
<div class="nav-collapse">
<ul class="nav">
	<li id="nav_main"><a href="#main">首页</a></li>
	<li id="nav_history"><a href="#historys">历史记录</a></li>
	<li id="nav_settings"><a href="#settings">系统设置</a></li>
	<li><a data-toggle="modal" href="#about">关于<img src="http://acm.nenu.edu.cn/winguse/img/b_as_ac.png" width="1px" height="1px" /></a></li>
</ul>
<p class="navbar-text pull-right" style="display: none"><a href="#"
	title="修改个人资料">Winguse</a> | <a href="#">注销</a></p>
</div>
<!--/.nav-collapse --></div>
</div>
<div id="messageOuter">
<div id="messageContainer"><strong id="message_title"></strong> <span
	id="message_main"></span> <a class="close"
	style="float: none; top: 0; right: 0; font-size: inherit;"
	onclick="clearTimeout(msgTimeoutHandle);$('#messageOuter').fadeOut(500);return false;"
	href="#">&times;</a></div>
</div>
</div>
<div class="container"><header> <img id="logo" src="img/book.png"
	alt="图书自动编目" />
<div id="logo-left"><!-- 获取部分 -->
<div id="fetch-control" class="well form-search" style="display: none">
<!-- 因为CSS文档流的问题，右边浮动的元素要先出现，否则就会挤到下一行  -->
<div id="fetch-control-right" class="btn-toolbar">
<div class="btn-group"><a class="btn dropdown-toggle"
	data-toggle="dropdown" href="#"> 从<span id="current_source">中国国家图书馆</span>
<span class="caret"></span> </a>
<ul class="dropdown-menu">
	<li><a href="#" onclick="acf.setSource(SRC_NOTIONALLIB);return false;">中国国家图书馆</a></li>
	<li><a href="#" onclick="acf.setSource(SRC_USCONGRESS);return false;">美国国会图书馆</a></li>
</ul>
</div>
<div class="btn-group" id="fetch_submit"><a class="btn btn-primary"
	href="#" onclick="acf.manalFetch();return false;">获取</a> <a
	class="btn dropdown-toggle btn-primary" data-toggle="dropdown" href="#">
<span class="caret"></span> </a>
<ul class="dropdown-menu">
	<li><a href="#" onclick="acf.setAutoFetch(true);return false;">打开自动识别处理</a></li>
	<li><a href="#" onclick="acf.setAutoFetch(false);return false;">关闭自动识别处理</a></li>
</ul>
</div>
</div>
<div id="fetch-control-left"><input class="search-query"
	placeholder="请输入ISBN号……" type="text" maxlength="20" id="fetch-isbn"
	onkeyup="acf.autoFetchCallback()" /></div>
</div>
<!-- 历史记录部分 -->
<div id="history-control" class="well form-search" style="display: none">
<!-- 因为CSS文档流的问题，右边浮动的元素要先出现，否则就会挤到下一行  -->
<div id="history-control-right" class="btn-toolbar"><a
	class="btn btn-primary" href="#" onclick="ach.search();return false;">搜索</a>
<a class="btn btn-info" href="#" onclick="ach.reset();return false;">重置</a>
</div>
<div id="history-control-left"><input class="search-query"
	placeholder="请输入搜索关键词……" type="text" maxlength="20"
	id="history-keyword" /></div>
</div>
</div>
</header> <!-- #首页# -->
<div id="fetch-results" class="tabbable tabs-left" style="display: none">
<ul class="nav nav-tabs" id="fetch_item_list">
</ul>
<div class="tab-content" id="fetch_item_main">
<div class="tab-pane active">
<h3 style="text-align: center">欢迎使用<br />
<small>Welcome</small></h3>
<div class="main_text">
<p>图书编目是图书馆工作的重要组成部分，传统的手工编目耗时耗力，如果能尽可能的实现自动化、数字化、信息化，能极大的提高工作效率。再此基础上，<a
	data-toggle="modal" href="#about">本系统</a>提供一套接口，可以获取<a
	href="http://www.nlc.gov.cn/">中国国家图书馆</a>，<a href="http://www.loc.gov/">美国国会图书馆</a>的编目资料，减少重复录入资料的繁琐性，进一步提高信息化程度和提高工作效率。</p>
<h4>使用说明</h4>
<p></p>
<ol>
	<li>获取信息
	<p>在<a href="#main">本页</a>上方法输入框输入ISBN号，系统将自动识别处理，如需关闭自动处理，点击获取按钮下拉菜单取消。默认数据源是<a
		href="http://www.nlc.gov.cn/">中国国家图书馆</a>，如果是外文书籍，建议切换为<a
		href="http://www.loc.gov/">美国国会图书馆</a>。</p>
	</li>
	<li>查看历史记录
	<p>点击上方导航栏，转至<a href="#historys">历史记录</a>。可以查看、搜索已获取的书目信息，并可进行修改和删除。</p>
	</li>
	<li>系统设置
	<p>点击上方导航栏，转至<a href="#settings">系统设置</a>。可以修改获取参数，由于使用<a
		href="http://www.w3schools.com/xpath">xPath</a>技术处理数据，所以即使数据源网页发生改变，仅需修改对应的<a
		href="http://www.w3schools.com/xpath">xPath</a>参数即可，无需修改程序。也可对相关的字段名和记录号信息进行管理。</p>
	</li>
</ol>
<p>详细<a href="autocatalogue_guide.pdf">说明文档</a>点击这里下载。</p>
<p><b style="color: red">* 建议</b>使用<a href="http://chrome.google.com">Chrome</a>、<a
	href="http://firefox.com">FireFox</a>、 <a
	href="http://windows.microsoft.com/zh-CN/internet-explorer/downloads/ie">Internet
Explorer 9</a>等支持HTML5技术的浏览器。</p>
</div>
</div>
</div>
</div>
<!-- #历史# -->
<div id="fetch-historys" class="tabbable tabs-left"
	style="display: none">
<ul class="nav nav-tabs" id="history_item_list">
</ul>
<div class="tab-content" id="history_item_main"></div>
<div style="clear: both"></div>
<div class="pagination pagination-centered">
<ul>
	<li><a href="#" onclick="ach.load(1);return false;">第一页</a></li>
	<li><a href="#" onclick="ach.previous();return false;">上一页</a></li>
	<li><a href="#" onclick="ach.next();return false;">下一页</a></li>
	<li><a href="#" onclick="ach.load(-1);return false;">最后页</a></li>
</ul>
</div>
</div>
<!-- #设置# -->
<div id="fetch-settings" class="tabbable tabs-left"
	style="display: none">
<ul class="nav nav-tabs" id="settings_list">
	<li id="settings_xpath" class="active"><a href="#settings_xpath_main"
		data-toggle="tab">数据源分析参数</a></li>
	<li id="settings_name2code_list"><a href="#settings_name2code_main"
		data-toggle="tab" onclick="name2code.pageInit();"><strong>字段名</strong>
	到 <strong>记录号</strong></a></li>
	<li id="settings_code2name_list"><a href="#settings_code2name_main"
		data-toggle="tab" onclick="code2name.pageInit();"><strong>记录号</strong>
	到 <strong>字段名</strong></a></li>
</ul>
<div class="tab-content" id="settings_main">
<div class="tab-pane active" id="settings_xpath_main">
<div class="accordion-group">
<div class="accordion-heading"><a
	onclick="acs.loadNationalLibiaryXPath()" class="accordion-toggle"
	data-toggle="collapse" data-parent="#settings_xpath_main"
	href="#cfg_national_libiary"> 中国国家图书馆 XPath 等参数 </a></div>
<div id="cfg_national_libiary" class="accordion-body collapse">
<div class="accordion-inner">
<div class="form-horizontal well" id="cfg_national_libiary_main"></div>
</div>
</div>
</div>
<div class="accordion-group">
<div class="accordion-heading"><a
	onclick="acs.loadLibiaryOfUSCongressXPath()" class="accordion-toggle"
	data-toggle="collapse" data-parent="#settings_xpath_main"
	href="#cfg_us_congress"> 美国国会图书馆 XPath 等参数 </a></div>
<div id="cfg_us_congress" class="accordion-body collapse">
<div class="accordion-inner">
<div class="form-horizontal well" id="cfg_us_congress_main"></div>
</div>
</div>
</div>
</div>
<div class="tab-pane" id="settings_code2name_main">
<div id="controlContainer_code2name" class="form-inline well">
<div class="p_row" id="addContainer_code2name">
<div class="span3"><input placeholder="记录号" class="span3"
	id="cateCode_add_code2name" type="text" maxlength="255"
	onkeypress="code2name.addItem(event);" /></div>
<div class="span4"><input placeholder="字段名" class="span4"
	id="cateName_add_code2name" type="text" maxlength="255"
	onkeypress="code2name.addItem(event);" /></div>
<div class="span1"><input class="btn btn-success" type="button"
	value="　增加　" onclick="code2name.addItem(event)" /></div>
</div>
</div>
<div id="editContainer_code2name" class="form-inline well"></div>
</div>
<div class="tab-pane" id="settings_name2code_main">
<div id="controlContainer_name2code" class="form-inline well">
<div id="addContainer_name2code" class="p_row">
<div class="span4"><input placeholder="字段名" class="span4"
	id="cateName_add_name2code" type="text" maxlength="255"
	onkeypress="name2code.addItem(event);" /></div>
<div class="span3"><input placeholder="记录号" class="span3"
	id="cateCode_add_name2code" type="text" maxlength="255"
	onkeypress="name2code.addItem(event);" /></div>
<div class="span1"><input class="btn btn-success" type="button"
	value="　增加　" onclick="name2code.addItem(event)" /></div>
</div>
</div>
<div id="editContainer_name2code" class="form-inline well"></div>
</div>
</div>
</div>
<hr>
<footer>
<p>&copy;2012 <a href="http://blog.winguse.com/">Winguse</a></p>
</footer></div>
<div id="about" class="modal hide fade">
<div class="modal-header"><a class="close" data-dismiss="modal">×</a>
<h3>关于</h3>
</div>
<div class="modal-body">
<h3 style="text-align: center">图书自动编目 <br />
<small>计算机科学与信息技术学院2011～2012年度科研立项</small></h3>
<p></p>
<p>指导老师：杨贵福</p>
<p>项目组成员：程颖宇 文毅 刘美君 袁小康 何泽林</p>
<p>UI Base On <a href="http://builtwithbootstrap.tumblr.com/">Bootstrap</a>.</p>
<p style="text-align: right"><a href="http://blog.winguse.com/">&copy;2012
Winguse</a></p>
</div>
<div class="modal-footer"><a href="#" data-dismiss="modal"
	class="btn btn-primary">确定</a></div>
</div>
<div id="asking" class="modal hide fade">
<div class="modal-header"><a class="close" data-dismiss="modal">×</a>
<h3 id="asking_title"></h3>
</div>
<div class="modal-body" id="asking_content"></div>
<div class="modal-footer"><a href="#" data-dismiss="modal" class="btn"
	id="asking_no">否</a> <a href="#" data-dismiss="modal"
	class="btn btn-primary" id="asking_yes">是</a></div>
</div>
<script src="js/global.js" type="text/javascript"></script>
<script src="js/jQuery.js" type="text/javascript"></script>
<script src="js/bootstrap.js" type="text/javascript"></script>
<script src="js/jquery.ba-hashchange.js" type="text/javascript"></script>
<script src="js/autoCatalogue.js" type="text/javascript"></script>
</body>
</html>
