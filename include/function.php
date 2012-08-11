<?php
include_once "systemConfig.php";

function toCodeStr($str) {
	return addcslashes($str, "\$\\\"");
}

function getXpathFromHtml($htmlText) {
	//may be useful
}

function addUndefineCatalogueName($name){
	global $mysql;
	$pstm=$mysql->prepare("INSERT INTO Name2Code(Name,Code,UpdateTime) VALUES(?,?,?)");
	$code="未定义编目号[".$name."]***";
	$pstm->bind_param("ssi", $name,$code,time());
	$pstm->execute();
	$pstm->close();
}

function addUndefineCatalogueCode($code){
	global $mysql;
	$pstm=$mysql->prepare("INSERT INTO MARCInf(MARCInfCode,MARCInfName,UpdateTime) VALUES(?,?,?)");
	$name="未定义编目名[".$code."]***";
	$pstm->bind_param("ssi", $code,$name,time());
	$pstm->execute();
	$pstm->close();
	
}

?>