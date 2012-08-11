<?php
include_once 'include/systemConfig.php';
include_once 'include/function.php';

$username = mysql_escape_string($_POST["username"]);
$password = $_POST["password"];
$email = mysql_escape_string($_POST["email"]);
$verifyCode = $_POST["verifyCode"];

if($username == "" || $password == "" || $email == "" || $verifyCode == "") {
	$ret["code"] = 1;
	$ret["message"] = "表单有不允许的空项目。";
}else if(preg_match('/^[\-\.\w]+@[\-\w]+(\.\w+)+$/', $email) == 0) {
	$ret["code"] = 2;
	$ret["message"] = "Email地址有问题。";
}else if($verifyCode != $_SESSION["verifyCode"] && false) {//TODO 认证码
	$ret["code"] = 3;
	$ret["message"] = "认证码错误。";
} else {
	$password = sha1($password . $securityCode);
	mysql_connect($DB["Server"], $DB["Username"], $DB["Password"]);
	mysql_select_db($DB["Name"]);
	$result = mysql_query("select * from user where username='$username'");
	if(mysql_fetch_array($result)) {
		$ret["code"] = 4;
		$ret["message"] = "用户名已经存在，请换一个。";
	} else {
		$result=mysql_query("insert into User(username,password,email) values('$username','$password','$email') ");
		if($result == false) {
			$ret["code"] = 5;
			$ret["message"] = "数据库操作发生错误。";
		} else {
			$ret["code"] = 0;
			$ret["message"] = "恭喜，注册成功。";
		}
	}
	mysql_free_result($result);
	mysql_close();
}
echo json_encode($ret);
?>