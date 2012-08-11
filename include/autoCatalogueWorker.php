<?php
include_once 'systemConfig.php';
include_once 'function.php';
include_once 'autoCatalogueFetchers.php';
include_once 'catalogueMARCLoader.php';
/*
 * @author ching
 * */
class AutoCatalogueWorker {//性能起见，这个生成的对象可以考虑存如Session
	private $acf;
	private $outputType;
	/*
	 * 自动编目主体类，可以把原始表格信息和编目好对应上，并转换格式，写入数据库缓存
	 * @param $acf AutoCatalogueFetcher 的实体类
	 * @param $outType 字符串，返回类型，可选 XML,JSON,非二者则返回数组。
	 * */
	public function __construct(AutoCatalogueFetcher $acf = null, $outputType = "JSON") {
		$this -> acf = $acf;
		$this -> outputType = strtoupper($outputType);
	}

	/*
	 * @param $outType 字符串，返回类型，可选 XML,JSON,TEXT,CSV,MARC,Z39.50,非二者则返回数组。
	 * */
	public function setOutputType($outputType) {
		$this -> outputType = strtoupper($outputType);
	}

	public function setFetcher(AutoCatalogueFetcher $acf) {
		$this -> acf = $acf;
	}

	/*
	 * @return CatalogueInfDTO
	 * */
	public function getCatalogueInfFromQuery($ISBN, $htmlText = "") {
		global $CatalogueName2Code;
		global $CatalogueCode2Name;
		$messageCode = 0;
		$message = "";
		try {
			$rawDto = $this -> acf -> work($ISBN, $htmlText);
			$messageCode = 0;
			$message = "获取成功！";
		} catch(CatalogueInformationNotFoundException $e) {
			$messageCode = 1;
			//后台处理的时候，应该更换数据源
			$message = "目标数据源没有对应的记录，系统未能获取相关信息。\n\n详细技术信息：" . $e -> getMessage();
		} catch(FetchException $e) {
			$messageCode = 2;
			//前台处理的时候，应该停止
			$message = "系统获取数据出现错误，请尝试到系统设置页面修改获取参数。\n\n详细技术信息：" . $e -> getMessage();
		} catch(Excetpion $e) {
			$messageCode = 3;
			$message = "系统出现未知异常。\n\n详细技术信息：" . $e -> getMessage();
		}
		$infDTO = new CatalogueInfDTO($messageCode, $message);
		$tmp = 0;
		if($messageCode == 0) {
			$infDTO -> setIsbn($rawDto -> getIsbn());
			$infDTO -> setSource($rawDto -> getSource());
			$infDTO -> setFetchTime($rawDto -> getFetchTime());
			$infDTO -> setUrl($rawDto -> getUrl());
			$infDTO -> setHtmlText($rawDto -> getHtmlText());
			$infDTO -> setNeedUpdated(0, true);
			$needUpdate = false;
			$infDTO->setSourceMARC($rawDto->getSourceMARC());
			$infDTO->setSourceText($rawDto->getSourceText());
			foreach($rawDto->getInfArray() as $key => $value) {
				$tmp++;
				$clCode = $CatalogueName2Code[$key];
				if($clCode == "") {//没办法找到数据源的编目名对应的号码
					addUndefineCatalogueName($key);
					//在系统缓存增加一个未定义记录将其对应到   “未定义编目号[数据源编目名]***” 的格式
					$clCode = "未定义编目号[$key]***";
					//前台返回这个值，也就是那个函数存的格式（上个注释）
					$clName = "未定义[$key]的编目号，无法获得统一编目名_$tmp";
					//对于我们需求的编目名译名，我们更加无法确定，所以用这个表示
				} else {
					$clName = $CatalogueCode2Name[$clCode];
					//找对应的编号译名
					if($clName == "") {//对应的翻译木有找到，其实应该是个奇迹
						if(false === strpos($clCode, "未定义")) {//同时，即将加进去的编目号码不能是未定义的
							addUndefineCatalogueCode($clCode);
							//在系统缓存增加一个未定义记录将其对应到   “未定义编目名[缓存中已经找到的编目号]***” 的格式
							$clName = "未定义编目名[$clCode]***";
							//直接上面说的格式
						} else {
							$clName = "未定义[$key]的编目号，无法获得统一编目名_$tmp";
						}
					}
				}
				//注意Value是数组
				if(0 === strpos($clCode, "未定义") || 0 === strpos($clName, "未定义"))
					$needUpdate = true;
				foreach($value as $v)
					$infDTO -> setInfArrayItem($clCode, $clName, $v);
				if($clCode == "200") {//TODO 可能还有其他的值
					$infDTO -> setBookname($value[0]);
				}
			}
			$fg = true;
			if($_SESSION["needUpateCatalogueCode2Name"] == true) {
				$infDTO -> setNeedUpdated(1);
				if($fg)
					persistCatalogueCodeAndNameCache();
				$fg = false;
				//只刷新一次缓存。
				$infDTO -> addMessage("处理过程中，出现未定义的编目号，<a href='editCatalogueCode.html'>请及时更新系统设置</a>。");
			}
			if($_SESSION["needUpateCatalogueName2Code"] == true) {
				$infDTO -> setNeedUpdated(1);
				if($fg)
					persistCatalogueCodeAndNameCache();
				$fg = false;
				//只刷新一次缓存。
				$infDTO -> addMessage("处理过程中，出现未定义的编目名，<a href='editCatalogueName.html'>请及时更新系统设置</a>。");
			}
			if($needUpdate) {
				$infDTO -> setNeedUpdated(1);
			}
		}
		return $infDTO;
	}

	public function getCatalogueFromDatabase($ISBN) {
	//	return false;
		global $DB;
		mysql_connect($DB["Server"], $DB["Username"], $DB["Password"]);
		mysql_select_db($DB["Name"]);
		$dbQuery = mysql_query("select CatalogueInfDTO from Catalogue where Source='" . mysql_escape_string($this -> acf -> getFetcherName()) . "' and ISBN='" . mysql_escape_string($ISBN) . "'");
		if($rs = mysql_fetch_array($dbQuery)) {
			$infDTO = unserialize($rs["CatalogueInfDTO"]);
			$infDTO -> addMessage("本次查询结果来源于数据库缓存。");
		} else {
			$infDTO = false;
		}
		mysql_close();
		return $infDTO;
	}

	public function encodeCatalogueInf($infDTO) {
		$result = $infDTO -> toArray();
		//这个函数里面的，以后或许更好的做法是在DTO里面实现。
		if($this -> outputType == "JSON") {
			return json_encode($result);
		} else if($this -> outputType == "XML") {
			$xml = "<root>";
			$xml .= "<message code='" . $result["code"] . "'><![CDATA[" . $result["message"] . "]]></message>";
			$xml .= "<description>";
			foreach($result["description"] as $key => $value) {
				$xml .= "<$key><![CDATA[$value]]></$key>";
			}
			$xml .= "</description>";
			$xml .= "<main>";
			foreach($result["main"] as $catalogue) {
				$xml .= "<catalogue code='" . $catalogue["clCode"] . "' name='" . $catalogue["clName"] . "'>";
				foreach($catalogue["clValue"] as $value) {
					$xml .= "<value><![CDATA[$value]]></value>";
				}
				$xml .= "</catalogue>";
			}
			$xml .= "</main>";
			$xml .= "</root>";
			return $xml;
		} else if($this -> outputType == "TEXT") {//TODO 不过，考虑到编目信息繁多，这里先放下，我目前需要整理编目信息
			$text = "[基本描述]\n";
			$text .= sprintf("%20s:\n");
			return $result;
		} else if($this -> outputType == "CSV") {
			return $result;
		} else if($this -> outputType == "MARC") {
			return $result;
		}else if($this -> outputType == "Z39.20") {
			return $result;
		} else {
			return $result;
		}
	}

	public function persistCatalogueInf($infDTO, $id = -1) {
	//	return;
		global $DB;
		session_start();
		//TODO 木有登录呢？
		mysql_connect($DB["Server"], $DB["Username"], $DB["Password"]);
		mysql_select_db($DB["Name"]);
		if($id == -1){
			$s=mysql_query("insert into Catalogue(
			LogUserId,Source,ISBN,BookName,CatalogueInfDTO,LogTime,LastCheckTime,NeedUpdated,Trusted)
			values (" . $_SESSION["login"]["UserId"] . ",'" . mysql_escape_string($infDTO -> getSource()) . "',
			'" . mysql_escape_string($infDTO -> getIsbn()) . "','" . mysql_escape_string($infDTO -> getBookname()) . "',
			'" . mysql_escape_string(serialize($infDTO)) . "',now(),now()," . $infDTO -> getNeedUpdated() . "," . $infDTO -> getTrusted() . ")");
			if($s==false){
				echo("insert into Catalogue(
			LogUserId,Source,ISBN,BookName,CatalogueInfDTO,LogTime,LastCheckTime,NeedUpdated,Trusted)
			values (" . $_SESSION["login"]["UserId"] . ",'" . mysql_escape_string($infDTO -> getSource()) . "',
			'" . mysql_escape_string($infDTO -> getIsbn()) . "','" . mysql_escape_string($infDTO -> getBookname()) . "',
			'" . mysql_escape_string(serialize($infDTO)) . "',now(),now()," . $infDTO -> getNeedUpdated() . "," . $infDTO -> getTrusted() . ")");
			}
		}else{
			$s=mysql_query("update Catalogue set
			BookName='" . mysql_escape_string($infDTO -> getBookname()) . "',
			CatalogueInfDTO='" . mysql_escape_string(serialize($infDTO)) . "',
			LastCheckTime=now(),
			NeedUpdated=" . $infDTO -> getNeedUpdated() . ",
			Trusted=" . $infDTO -> getTrusted() . " where CatalogueId=$id");
			if($s===false){
				echo ("update Catalogue set
			BookName='" . mysql_escape_string($infDTO -> getBookname()) . "',
			CatalogueInfDTO='" . mysql_escape_string(serialize($infDTO)) . "',
			LastCheckTime=now(),
			NeedUpdated=" . $infDTO -> getNeedUpdated() . ",
			Trusted=" . $infDTO -> getTrusted() . " where CatalogueId=$id");
			}
		}
		mysql_close();
	}

	public function getCatalogueInf($ISBN) {
		$infDTO = $this -> getCatalogueFromDatabase($ISBN);
		if($infDTO == false) {
			$infDTO = $this -> getCatalogueInfFromQuery($ISBN);
			if($infDTO -> getCode() == 0)
				$this -> persistCatalogueInf($infDTO);
		}
		return $this -> encodeCatalogueInf($infDTO);
	}

	public function rework($isbn,$htmlText, $id,$sourceText,$sourceMARC) {
		$infDTO = $this -> getCatalogueInfFromQuery($isbn, $htmlText);
		$infDTO->setSourceMARC($sourceMARC);
		$infDTO->setSourceText($sourceText);
		if($infDTO -> getCode() == 0)
			$this -> persistCatalogueInf($infDTO, $id);
		return $infDTO;
	}

	/*
	 public function getBookname($result) {//写得不太漂亮
	 foreach($result["main"] as $c) {
	 if($c["clCode"] == "200")//TODO 这个的确比较麻烦200#1,200#2都可以啊
	 return $c["clValue"][0];
	 }
	 }
	 */
}

//	$acw = new AutoCatalogueWorker(new NationalLibraryFetcher(), "JSONd");
//	print_r($acw -> getCatalogueInf("9787040240986"));
?>