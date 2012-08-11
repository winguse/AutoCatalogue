<?php

//include_once '../include/systemConfig.php';
//include_once '../include/function.php';
//$mysql=new mysqli($DB["Server"], $DB["Username"], $DB["Password"],$DB["Name"]);

$rs=$mysql->query("SELECT Name,Code FROM Name2Code",MYSQLI_STORE_RESULT);
if($rs==false){
	die("Fail to load name to code information");
}

while($row=$rs->fetch_row()){
	$CatalogueName2Code[$row[0]]=$row[1];
}
$rs->free();

$rs=$mysql->query("SELECT MARCInfCode,MARCInfName FROM MARCInf",MYSQLI_STORE_RESULT);
if($rs==false){
	die("Fail to load name to code information");
}

while($row=$rs->fetch_row()){
	$CatalogueCode2Name[$row[0]]=$row[1];
}
$rs->free();



//print_r($CatalogueName2Code);
//print_r($CatalogueCode2Name);
?>
