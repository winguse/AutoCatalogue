<?php
include_once 'include/systemConfig.php';
include_once 'include/function.php';
include_once 'include/autoCatalogueWorker.php';
switch($_GET["action"]) {
	case "query" :
		if($_GET["type"] == "JSON") {
			if($_GET["source"]=="NationalLibrary"){
				$acw = new AutoCatalogueWorker(new NationalLibraryFetcher(), "JSON");
				header("application/json");
				echo $acw -> getCatalogueInf($_GET["ISBN"]);
			}else if($_GET["source"]=="LibiaryOfUSCongress"){
				$acw = new AutoCatalogueWorker(new LibiaryOfUSCongressFetcher(), "JSON");
				header("application/json");
				echo $acw -> getCatalogueInf($_GET["ISBN"]);
			}else{
				echo "unknow request";
			}
			//TODO 合法性判断
		}
		break;
	case "getHistory" :
		echo json_encode(getCatalogueList($_GET["page"], $_GET["pagesize"], $_GET["searchKeyword"]));
		break;
	case "getItem" :
		echo json_encode(getCatalogueItem($_GET["id"]));
		break;
	case "deleteItem" :
		echo json_encode(deleteCatalogueItem($_GET["id"]));
		break;
	case "editItem" :
		echo json_encode(editCatalogueItem($_GET["id"], $_POST["clCode"], $_POST["clName"], $_POST["clValue"], $_POST["bookname"], $_POST["needUpdated"], $_POST["trusted"]));
		break;
	case "rework" :
		echo json_encode(reworkCatalogueinf($_GET["id"]));
		break;
	case "getCatalogueSingle" :
		echo json_encode(getCatalogueSingle($_GET["id"]));
		break;
	case "getSourceMARC":
		getSourceMARC($_GET["id"]);
		break;
	case "getSourceText":
		getSourceText($_GET["id"]);
		break;
	case "getText":
		_getText($_GET["id"]);
		break;
	case "getCSV":
		getCSV($_GET["id"]);
		break;
	default :
		echo "Unknow Request!";
}//6
/*

 item = {
 clCode : "",
 clName : "",
 clValue : "",
 bookname:"",
 needUpdated:0,
 trusted:0
 };
 */
function editCatalogueItem($id, $clCodeStr, $clNameStr, $clValueStr, $bookname, $needUpdated, $trusted) {
	$clCodeArr = preg_split("#(?<!\\\)\|#", $clCodeStr);
	$clNameArr = preg_split("#(?<!\\\)\|#", $clNameStr);
	$clValueArr = preg_split("#(?<!\\\)\|#", $clValueStr);
	if(!(count($clCodeArr) == count($clNameArr) && count($clCodeArr) == count($clValueArr))) {
		return array("code" => 1, "message" => "提交的记录数据有问题。");
	}
	global $DB;
	mysql_connect($DB["Server"], $DB["Username"], $DB["Password"]);
	mysql_select_db($DB["Name"]);
	$id = intval($id);
	$result = mysql_query("select CatalogueInfDTO from Catalogue where CatalogueId=$id");
	if($row = mysql_fetch_row($result)) {
		$infDTO = unserialize($row[0]);
		$infDTO -> setInfArray(array());
		$infDTO -> setBookname($bookname);
		$needUpdated = intval($needUpdated);
		$trusted = intval($trusted);
		$infDTO -> setNeedUpdated($needUpdated, true);
		$infDTO -> setTrusted($trusted);
		$bound = count($clCodeArr);
		for($i = 0; $i < $bound; $i++) {
			$infDTO -> setInfArrayItem($clCodeArr[$i], $clNameArr[$i], $clValueArr[$i]);
		}
		$lastCheckTime = time();
		$result = mysql_query("update Catalogue set CatalogueInfDTO='" . mysql_escape_string(serialize($infDTO)) . "',BookName='" . mysql_escape_string($bookname) . "',Trusted=$trusted,NeedUpdated=$needUpdated,LastCheckTime='" . date('Y-m-d H:i:s', $lastCheckTime) . "' where CatalogueId=$id");
		if($result === false) {
			$arr = array("code" => 1, "message" => "操作数据库时出错了！");
		} else {
			$arr = array("code" => 0, "message" => "记录已经更新。", "lastCheckTime" => $lastCheckTime);
		}
	} else {
		$arr = array("code" => 1, "message" => "不存在这样的记录");
	}
	mysql_close();
	return $arr;
}

function getCatalogueItem($id) {
	global $DB;
	mysql_connect($DB["Server"], $DB["Username"], $DB["Password"]);
	mysql_select_db($DB["Name"]);
	$id = intval($id);
	$result = mysql_query("select `CatalogueId`,`LogUserId`,`Source`,`ISBN`,`BookName`,
			`LogTime`,`LastCheckTime`,`NeedUpdated`,`Trusted`,`CatalogueInfDTO` from Catalogue where CatalogueId=$id");
	$ret["code"] = 0;
	$ret["message"] = "查询成功！";
	if($row = @mysql_fetch_assoc($result)) {
		foreach($row as $key => $value) {
			if($key=='CatalogueInfDTO'){
				$ret["main"][$key] = unserialize($value)->toArray();
			}else{
				$ret["main"][$key] = $value;
			}
		}
	} else {
		$ret["code"]=-1;
		$ret["message"] = "没有找到相应的记录！";
	}
	mysql_close();
	return $ret;
}

function getCatalogueList($page = 1, $pageSize = 10, $searchKeyword = "") {
	if($searchKeyword != "") {
		$searchKeyword = mysql_escape_string($searchKeyword);
		$searchSQL = " where BookName like '%$searchKeyword%' or ISBN like '%$searchKeyword%' or Source like '%$searchKeyword%' ";
	} else {
		$searchSQL = "";
	}
	global $DB;
	$page = intval($page);
	$pageSize = intval($pageSize);
	if($pageSize < 1 || $pageSize > 50)
		$pageSize = 10;
	$ret = array();
	mysql_connect($DB["Server"], $DB["Username"], $DB["Password"]);
	mysql_select_db($DB["Name"]);
	$row = mysql_fetch_array(mysql_query("select count(*) from Catalogue $searchSQL"));
	$rowCount = $row[0];
	$maxPage = ceil($rowCount / $pageSize);
	$ret["code"] = 0;
	$ret["message"] = "查询成功！";
	$ret["description"]["recordsCount"] = $rowCount;
	$ret["description"]["pageSize"] = $pageSize;
	$ret["description"]["maxPage"] = $maxPage;
	$ret["description"]["page"] = $page;
	if($page > $maxPage)
		$page = $maxPage;
	else if($page < 1)
		$page = 1;
	$beginOffset = ($page - 1) * $pageSize;
	$result = mysql_query("
	select `CatalogueId`,`LogUserId`,`Source`,`ISBN`,`BookName`,
			`LogTime`,`LastCheckTime`,`NeedUpdated`,`Trusted`,`CatalogueInfDTO` from Catalogue 
			$searchSQL
			order by CatalogueId desc limit $beginOffset,$pageSize");
	$i=0;
	while($row = @mysql_fetch_assoc($result)) {
		foreach($row as $key => $value) {
			if($key=='CatalogueInfDTO'){
				$ret["main"][$i][$key] = unserialize($value)->toArray();
			}else{
				$ret["main"][$i][$key] = $value;
			}
		}
		$i++;
	}
	mysql_close();
	return $ret;
}

function getCatalogueSingle($id) {
	global $DB;
	$id = intval($id);
	$ret = array();
	mysql_connect($DB["Server"], $DB["Username"], $DB["Password"]);
	mysql_select_db($DB["Name"]);
	$result = mysql_query("select `CatalogueId`,`LogUserId`,`Source`,`ISBN`,`BookName`,`LogTime`,`LastCheckTime`,`NeedUpdated`,`Trusted` from Catalogue where CatalogueId=$id");
	if($row = mysql_fetch_assoc($result)) {
		$ret["code"] = 0;
		$ret["message"] = "查询成功！";
		foreach($row as $key => $value) {
			$ret["main"][$key][] = $value;
		}
	} else {
		$ret["code"] = 1;
		$ret["message"] = "不存在这样的记录！";
	}
	mysql_close();
	return $ret;
}

function deleteCatalogueItem($id) {
	//	return array("code" => 0, "message" => "记录已经删除。");
	global $DB;
	mysql_connect($DB["Server"], $DB["Username"], $DB["Password"]);
	mysql_select_db($DB["Name"]);
	$id = intval($id);
	if(mysql_query("delete from Catalogue where CatalogueId=$id")) {
		$ret = array("code" => 0, "message" => "记录已经删除。");
	} else {
		$ret = array("code" => 1, "message" => "删除记录时出现了错误。");
	}
	mysql_close();
	return $ret;
}

function reworkCatalogueinf($id) {
	global $DB;
	mysql_connect($DB["Server"], $DB["Username"], $DB["Password"]);
	mysql_select_db($DB["Name"]);
	$id = intval($id);
	$result = mysql_query("select ISBN,Source,CatalogueInfDTO from Catalogue where CatalogueId=$id");
	if($row = mysql_fetch_array($result)) {
		$infDTO = unserialize($row["CatalogueInfDTO"]);
		$worker = new AutoCatalogueWorker(newFetcherFromSource($row["Source"]), "JSON");
		$sourceText=$infDTO->getSourceText();
		$sourceMARC=$infDTO->getSourceMARC();
		$infDTO = $worker -> rework($row["ISBN"],$infDTO -> getHtmlText(), $id,$sourceText,$sourceMARC);
		if($infDTO -> getCode() == 0) {
			$ret = array("code" => "0", "message" => "已经成功重新处理。");
		} else {
			$ret = array("code" => "1", "message" => "重新处理出现异常。");
		}
	} else {
		$ret = array("code" => "1", "message" => "不存在这样的记录。");
	}
	@mysql_close();
	//TODO 多个mysql连接了
	return $ret;
}
function getSourceMARC($id){
	global $DB;
	mysql_connect($DB["Server"], $DB["Username"], $DB["Password"]);
	mysql_select_db($DB["Name"]);
	$id = intval($id);
	$result = mysql_query("select Source,CatalogueInfDTO from Catalogue where CatalogueId=$id");
	if($row = mysql_fetch_array($result)) {
		$infDTO = unserialize($row["CatalogueInfDTO"]);
		header("Content-type: text/plain");
        header("Accept-Ranges: bytes");
		header("Content-Disposition: attachment; filename=".$infDTO->getIsbn().".sav");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0" );
        header("Pragma: no-cache" );
        header("Expires: 0" ); 
        echo $infDTO->getSourceMARC();
	} else {
		echo "不存在这样的记录。";
	}
	@mysql_close();
}
function getSourceText($id){
	global $DB;
	mysql_connect($DB["Server"], $DB["Username"], $DB["Password"]);
	mysql_select_db($DB["Name"]);
	$id = intval($id);
	$result = mysql_query("select Source,CatalogueInfDTO from Catalogue where CatalogueId=$id");
	if($row = mysql_fetch_array($result)) {
		$infDTO = unserialize($row["CatalogueInfDTO"]);
		header("Content-type: text/plain");
        header("Accept-Ranges: bytes");
		header("Content-Disposition: attachment; filename=".$infDTO->getIsbn().".txt");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0" );
        header("Pragma: no-cache" );
        header("Expires: 0" ); 
        echo $infDTO->getSourceText();
	} else {
		echo "不存在这样的记录。";
	}
	@mysql_close();
}
function _getText($id){
	global $DB;
	mysql_connect($DB["Server"], $DB["Username"], $DB["Password"]);
	mysql_select_db($DB["Name"]);
	$id = intval($id);
	$result = mysql_query("select Source,CatalogueInfDTO from Catalogue where CatalogueId=$id");
	if($row = mysql_fetch_array($result)) {
		$infDTO = unserialize($row["CatalogueInfDTO"]);
		header("Content-type: text/plain");
        header("Accept-Ranges: bytes");
		header("Content-Disposition: attachment; filename=".$infDTO->getIsbn()."_acl.txt");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0" );
        header("Pragma: no-cache" );
        header("Expires: 0" ); 
        echo $infDTO->getText();
	} else {
		echo "不存在这样的记录。";
	}
	@mysql_close();
}
function getCSV($id){
	global $DB;
	mysql_connect($DB["Server"], $DB["Username"], $DB["Password"]);
	mysql_select_db($DB["Name"]);
	$id = intval($id);
	$result = mysql_query("select Source,CatalogueInfDTO from Catalogue where CatalogueId=$id");
	if($row = mysql_fetch_array($result)) {
		$infDTO = unserialize($row["CatalogueInfDTO"]);
		header("Content-type: application/csv");
        header("Accept-Ranges: bytes");
		header("Content-Disposition: attachment; filename=".$infDTO->getIsbn().".csv");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0" );
        header("Pragma: no-cache" );
        header("Expires: 0" ); 
        echo $infDTO->getCSV();
	} else {
		echo "不存在这样的记录。";
	}
	@mysql_close();
}
?>
