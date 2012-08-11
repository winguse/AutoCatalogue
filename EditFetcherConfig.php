<?php 
include 'include/fetcherConfig.php';

$ret["code"] = 0;
$ret["message"] = "";
switch ($_GET["action"]){
	case "getLibiaryOfUSCongressConfig":
		$ret["main"]=$LibiaryOfUSCongressConfig;
		$ret["message"] = "已经成功加载 美国国会图书馆 XPath 设置。";
		break;
	case "setLibiaryOfUSCongressConfig":
		foreach ($LibiaryOfUSCongressConfig as $key => $value){
			if($_POST[$key]==""){
				$ret["code"] = 1;
				$ret["message"] = "美国国会图书馆 XPath 设置 更新失败，表单存在空项目！";
				break;
			}
			$LibiaryOfUSCongressConfig[$key]=$_POST[$key];
		}
		if($ret["code"]==0){
			setFetcherConfigArray("LibiaryOfUSCongressConfig",$LibiaryOfUSCongressConfig);
			$ret["message"] = "美国国会图书馆 XPath 设置 更新成功。";
		}
		break;
	case "getNationalLibrayConfig":
		$ret["main"]=$NationalLibrayConfig;
		$ret["message"] = "已经成功加载 中国国家图书馆 XPath 设置。";
		break;
	case "setNationalLibrayConfig":
		foreach ($NationalLibrayConfig as $key => $value){
			if($_POST[$key]==""){
				$ret["code"] = 1;
				$ret["message"] = "中国国家图书馆 XPath 设置 更新失败，表单存在空项目！";
				break;
			}
			$NationalLibrayConfig[$key]=$_POST[$key];
		}
		if($ret["code"]==0){
			setFetcherConfigArray("NationalLibrayConfig",$NationalLibrayConfig);
			$ret["message"] = "中国国家图书馆 XPath 设置 更新成功。";
		}
		break;
	default:
		$ret["code"] = -1;
		$ret["message"] = "Unknow Request!";
		break;
}
echo json_encode($ret);
?>