<?php
include_once 'systemConfig.php';
//本文件需要生成，宿主未写 TODO

$NationalLibrayConfig=array(
	"interfaceUrl"=>"http://opac.nlc.gov.cn/F/?func=find-m&find_code=ISB&request=",
	"failXPath"=>"string(//html/head/title)",
	"failString"=>"中文及特藏文 - 多库检索",
	"set_numberXpath"=>"string(//html/body//form/input[@id='set_number']/attribute::value)",// XPath语法啊，找了我半天
	"sessionString"=>"string(//html/head/meta[@http-equiv='REFRESH']/attribute::content)",
	"detailUrl"=>"?func=full-set-set_body&set_entry=000001&format=002&set_number=",//这个是用JavaScript加载的
	"catalogueNamesXpath"=>"//tr/td[1]",
	"catalogueValuesXpath"=>"//tr/td[2]",
	"importURL"=>"string(//div[@id='operate']/a[@title='保存/邮寄']/attribute::href)",
	"importURLReplaceFrom"=>"full-mail-0",
	"importURLReplaceTo"=>"full-mail",
	"textParameter" =>"&format=002",
	"MARCParameter"=>"&format=997",
	"fileDownloadURL"=>"string(//html/body/p[@class='text3']/a[1]/attribute::href)"
);

$LibiaryOfUSCongressConfig=array(
	"homepage"=>"http://catalog.loc.gov",
	"interfaceUrl"=>"http://catalog.loc.gov/cgi-bin/Pwebrecon.cgi?DB=local&PAGE=First",
	"queryUrlXPath"=>"string(//html/body/form[1]/attribute::action)",
	"PidXPath"=>"string(//html/body/form[1]/table/tr/input[1]/attribute::value)",
	"SeqXPath"=>"string(//html/body/form[1]/table/tr/input[2]/attribute::value)",
	"queryFailXPath"=>"string(//html/head/title)",
	"queryFailString"=>"Library of Congress Online Catalog",
	"detailUrlXpath"=>"string(//html/body/form/center[2]/a[2]/attribute::href)",
	"catalogueNamesXpath"=>"//html/body/form/table[1]//tr/*[1]",//当存在多个同一个属性的值时，第二个记录起，不再用th，而是用空的td，比较麻烦呢
	"catalogueValuesXpath"=>"//html/body/form/table[1]//tr/*[2]",
	"RIDXPath"=>"string(//html/body/form/input[@name='RID']/attribute::value)"
);

$LibiaryOfUSCongressConfig=getFetcherConfigArray("LibiaryOfUSCongressConfig");
$NationalLibrayConfig=getFetcherConfigArray("NationalLibrayConfig");

//setFetcherConfigArray("NationalLibrayConfig",$NationalLibrayConfig);

function addFetcherConfigArray($fetcherName,$arr){
	global $mysql;
	$pstat=$mysql->prepare("INSERT INTO `FetchConfig`(`ConfigArray`,`FetcherName`) VALUES(?,?)");
	$pstat->bind_param("ss",serialize($arr), $fetcherName);
	$pstat->execute();
	$pstat->free_result();
	$pstat->close();
}
function getFetcherConfigArray($fetcherName){
	global $mysql;
	$arr=Array();
	$rs=$mysql->query(
		"SELECT `ConfigArray` FROM `FetchConfig` WHERE `FetcherName`='".
		$mysql->real_escape_string($fetcherName)."'"
	);
	if($rs==false){
	}else{
		$row=$rs->fetch_assoc();
		$arr=$row["ConfigArray"];
		$rs->free();
	}
	return unserialize($arr);
}

function setFetcherConfigArray($fetcherName,$arr){
	global $mysql;
	$pstat=$mysql->prepare("UPDATE `FetchConfig` SET `ConfigArray`=? WHERE `FetcherName`=?");
	$pstat->bind_param("ss",serialize($arr), $fetcherName);
	$pstat->execute();
	$pstat->free_result();
	$pstat->close();
}
?>
