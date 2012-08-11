<?php
include_once 'include/systemConfig.php';
include_once 'include/function.php';

try{
	$CatalogueCode = substr($_POST["CatalogueCode"],0,45);
	$CatalogueName = substr($_POST["CatalogueName"],0,45);//TODO 太长的处理
	$id = $_POST["id"];
	if($id!=""&&!is_numeric($id)){
		throw new Exception("你提交的ID参数有误。");
	}
	
	$ret=array("code"=>0,"message"=>"","main"=>array());

	switch($_GET["action"]) {
		case "Name2Code":
			$rs=$mysql->query("SELECT Name2CodeId,Name,Code FROM Name2Code ORDER BY Code ASC",MYSQLI_STORE_RESULT);
			if($rs==false){
				$ret["code"]=-1;
				$ret["message"]="找不到相关的编目名到编目号码数据。".$mysql->error;
			}else{
				$ret["message"]="编目名到编目号码数据获取成功。";
				while($row=$rs->fetch_assoc()){
					$ret["main"][$row["Name2CodeId"]]=array("Name"=>$row["Name"],"Code"=>$row["Code"]);
				}
				$rs->free();
			}
			break;
		case "Code2Name":
			$rs=$mysql->query("SELECT MARCInfId,MARCInfName,MARCInfCode FROM MARCInf ORDER BY MARCInfCode ASC",MYSQLI_STORE_RESULT);
			if($rs==false){
				$ret["code"]=-1;
				$ret["message"]="找不到相关MARC数据.".$mysql->error;
			}else{
				$ret["message"]="MARC数据获取成功.";
				while($row=$rs->fetch_assoc()){
					$ret["main"][$row["MARCInfId"]]=array("Name"=>$row["MARCInfName"],"Code"=>$row["MARCInfCode"]);
				}
				$rs->free();
			}
			break;
		case "addCode2Name":
			$pstm=$mysql->prepare("INSERT INTO MARCInf(MARCInfName,MARCInfCode,UpdateTime) VALUES(?,?,?)");
			$pstm->bind_param("ssi",$CatalogueName, $CatalogueCode,time());
			$pstm->execute();
			$pstm->free_result();
			$pstm->close();
			$ret["code"]=0;
			$ret["message"]="新的MARC数据插入成功。";
			$ret["main"]["newId"]=$mysql->insert_id;
			break;
		case "addName2Code":
			$pstm=$mysql->prepare("INSERT INTO Name2Code(Name,Code,UpdateTime) VALUES(?,?,?)");
			$pstm->bind_param("ssi",$CatalogueName, $CatalogueCode,time());
			$pstm->execute();
			$pstm->free_result();
			$pstm->close();
			$ret["code"]=0;
			$ret["message"]="新的编目名到编目号数据插入成功。";
			$ret["main"]["newId"]=$mysql->insert_id;
			break;
		case "updateCode2Name_Code":
			$pstm=$mysql->prepare("UPDATE MARCInf SET MARCInfCode=? WHERE MARCInfId=?");
			$pstm->bind_param("si",$CatalogueCode, $id);
			$pstm->execute();
			$pstm->free_result();
			$pstm->close();
			$ret["code"]=0;
			$ret["message"]="MARC编目号更新成功。";
			break;
		case "updateCode2Name_Name":
			$pstm=$mysql->prepare("UPDATE MARCInf SET MARCInfName=? WHERE MARCInfId=?");
			$pstm->bind_param("si",$CatalogueName, $id);
			$pstm->execute();
			$pstm->free_result();
			$pstm->close();
			$ret["code"]=0;
			$ret["message"]="MARC编目名更新成功。";
			break;
		case "updateName2Code_Code":
			$pstm=$mysql->prepare("UPDATE Name2Code SET Code=? WHERE Name2CodeId=?");
			$pstm->bind_param("si",$CatalogueCode, $id);
			$pstm->execute();
			$pstm->free_result();
			$pstm->close();
			$ret["code"]=0;
			$ret["message"]="编目名到编目号信息，编目号更新成功。";
			break;
		case "updateName2Code_Name":
			$pstm=$mysql->prepare("UPDATE Name2Code SET Name=? WHERE Name2CodeId=?");
			$pstm->bind_param("si",$CatalogueName, $id);
			$pstm->execute();
			$pstm->free_result();
			$pstm->close();
			$ret["code"]=0;
			$ret["message"]="编目名到编目号信息，编目名更新成功。";
			break;
		case "deleteCode2Name":
			$mysql->query("DELETE FROM MARCInf WHERE MARCInfId=".$id);
			$ret["code"]=0;
			$ret["message"]="MARC记录 ".$id." ，已经删除。";
			break;
		case "deleteName2Code":
			$mysql->query("DELETE FROM Name2Code WHERE Name2CodeId=".$id);
			$ret["code"]=0;
			$ret["message"]="编目名到编目号信息 ".$id." ，已经删除。";
			break;
		default :
			$ret["code"]=1;
			$ret["message"]="Unknow Request!";
	}

}catch(Exception $e){
	$ret["code"]=-999;
	$ret['message']='错误：'.$e->getMessage();
}

echo json_encode($ret);

include_once 'include/cleanup.php';

?>