<?php

session_start();
define("SYSBASEPATH","/home/AutoCatalogue/");
define("ONDEBUG",true);

if(ONDEBUG){
	$_SESSION["login"]["UserId"]=1;//写入数据库的需要。
}

$DB=array(
	"Server"=>"127.0.0.1",
	"Username"=>"shuxiao",
	"Password"=>"mylove",
	"Name"=>"autocatalogue",
	"securityCode"=>"Wish-Shuxiao-Happy~"
);

$mysql=new mysqli($DB["Server"], $DB["Username"], $DB["Password"],$DB["Name"]);//全局数据库连接
if($mysql->connect_error){
	die("Could not connect to database.");
}

?>
