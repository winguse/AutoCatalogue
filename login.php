<?php
//TODO Cookie
include_once 'include/systemConfig.php';
include_once 'include/function.php';

$username = mysql_escape_string($_POST["username"]);
$password = $_POST["password"];
$verifyCode = $_POST["verifyCode"];

if($username == "" || $password == "" || $verifyCode == "") {
	$ret["code"] = 1;
	$ret["message"] = "表单有不允许的空项目。";
}else if($verifyCode != $_SESSION["verifyCode"] && false) {//TODO 认证码
	$ret["code"] = 3;
	$ret["message"] = "认证码错误。";
} else {
	$password = sha1($password . $securityCode);
	//echo $password;
	mysql_connect($DB["Server"], $DB["Username"], $DB["Password"]);
	mysql_select_db($DB["Name"]);
	$result = mysql_query("select * from User where username='$username'");
	if($rs = mysql_fetch_array($result)) {
		if($rs["Password"] == $password) {
			/*
			 `UserId` int(10) NOT NULL AUTO_INCREMENT,
			 `Username` varchar(45) NOT NULL,
			 `Password` varchar(45) DEFAULT NULL,
			 `LoginIp` varchar(45) DEFAULT NULL,
			 `LoginTime` varchar(45) DEFAULT NULL,
			 `Email` varchar(45) DEFAULT NULL,
			 `Authority` int(10) NOT NULL DEFAULT 0,
			 */
			$ret["code"] = 0;
			$ret["message"] = "恭喜，登录成功。";
			session_start();
			$_SESSION["login"] = array("UserId" => $rs["UserId"], "Username" => $rs["Username"], "Password" => $rs["Password"], "LoginIp" => $rs["LoginIp"], "LoginTime" => $rs["LoginTime"], "Email" => $rs["Email"], "Authority" => $rs["Authority"], );
			mysql_query("update user set LoginIp='".$_SERVER[SERVER_ADDR]."',LoginTime=".time()." where UserId=".$rs["UserId"]);
			///TODO ecoder
			$_SESSION["ecoder_login"]=1;
		} else {
			$ret["code"] = 5;
			$ret["message"] = "密码错误，请检查。";
		}
	} else {
		$ret["code"] = 4;
		$ret["message"] = "用户名不存在，请检查。";
	}
	mysql_free_result($result);
	mysql_close();
}
echo json_encode($ret);
?>
