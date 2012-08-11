<?php
include_once 'autoCatalogueDTO.php';
include_once 'autoCatalogueExceptions.php';
include_once 'function.php';
include_once 'fetcherConfig.php';

interface  AutoCatalogueFetcher {
	public function work($ISBN, $htmlText = "");
	public function getFetcherName();
}

// $keyword = "字段名格式";
// $key = "table[cellspacing=2]";
class NationalLibraryFetcher implements AutoCatalogueFetcher {
	private $configArray;
	const FETCHER_NAME = "中国国家图书馆";
	public function __construct($configArray = null) {
		global $NationalLibrayConfig;
		if($configArray == null)
			$this -> configArray = $NationalLibrayConfig;
		else
			$this -> configArray = $configArray;
	}

	public function getFetcherName() {
		return self::FETCHER_NAME;
	}

	public function setConfigArray($NationalLibrayConfig) {
		$this -> configArray = $NationalLibrayConfig;
	}

	public function work($ISBN, $htmlText = "") {
		$resultDOMDocumnt = new DOMDocument("1.0", "utf-8");
		$resultDOMXPath = new DOMXPath($resultDOMDocumnt);
		$cA = &$this -> configArray;
		$MARCFile="";
		$TextFile="";
		if($htmlText == "") {
			$query = curl_init($cA["interfaceUrl"] . $ISBN);
			curl_setopt($query, CURLOPT_RETURNTRANSFER, true);
			$htmlText = curl_exec($query);
			curl_close($query);
			if($htmlText == false) {
				throw new FetchException(self::FETCHER_NAME . "获取" . $cA["interfaceUrl"] . $ISBN . "出现错误，可能是链接过期，或许需要修改链接或者升级系统。");
			}
			@$resultDOMDocumnt -> loadHTML($htmlText);
			//加@不输出那些错误
			$resultDOMXPath -> __construct($resultDOMDocumnt);
			//	print_r($resultDOMXPath -> evaluate($cA["failXPath"]));
			if($resultDOMXPath -> evaluate($cA["failXPath"]) == $cA["failString"]) {
				throw new CatalogueInformationNotFoundException(self::FETCHER_NAME . "查找" . $ISBN . "失败。\n");
			}
			$sessionString=$resultDOMXPath -> evaluate($cA["sessionString"]);	
			$sessionString=substr($sessionString,10,78);
			//	echo ($resultDOMXPath -> evaluate($cA["detailUrlXpath"]));
			$setNumber = $resultDOMXPath -> evaluate($cA["set_numberXpath"]);
			if($setNumber===false||$setNumber=="") {
				throw new FetchException(self::FETCHER_NAME . "，查询页面返回正常，但未能定位详细页面链接地址，可能需要升级系统代码或修改查询特征。本次定位XPath：" .$cA["detailUrlXpath"] . "，链接地址：" . $cA["interfaceUrl"] . $ISBN. "。");
			}
			
			$importURL= $resultDOMXPath -> evaluate($cA["importURL"]);
			if($importURL===false||$importURL=="") {
				throw new FetchException(self::FETCHER_NAME . "，查询页面返回正常，但未能定位卡片格式和MARC格式导出页面地址，可能需要升级系统代码或修改查询特征。本次定位XPath：" .$cA["importURL"] . "，链接地址：" . $cA["interfaceUrl"] . $ISBN. "。");
			}
			$importURL=str_replace($cA["importURLReplaceFrom"], $cA["importURLReplaceTo"], $importURL);//替换，其实发句牢骚，这样子做，不是挺好的机器处理办法，要改好多代码的。不过要把这些模式记录下来，挺麻烦的，以后研究将这种模式提升一种层次吧。
			//这里发现国图获取文本数据的时候出错了。
			try{
		//	echo $importURL.$cA["textParameter"];
			//文本导出，国图的是字段名。
			$query = curl_init($importURL.$cA["textParameter"]);
			curl_setopt($query, CURLOPT_RETURNTRANSFER, true);
			$htmlText = curl_exec($query);
			//这个链接应该是存在，除非国图网页有错误
			if($htmlText == false) {
				throw new FetchException(self::FETCHER_NAME . "获取" . $importURL.$cA["textParameter"] . "出现错误，这个链接[1]应该是存在，除非国图网页有错误，联系管理员～".$detailUrl);
			}
			curl_close($query);
			//分析实际文件下载地址
			@$resultDOMDocumnt -> loadHTML($htmlText);
	//		echo $htmlText;
			$resultDOMXPath -> __construct($resultDOMDocumnt);
			$FileURL=$resultDOMXPath -> evaluate($cA["fileDownloadURL"]);
			//下载文件
			$query=curl_init($FileURL);
			curl_setopt($query, CURLOPT_RETURNTRANSFER, true);
			$TextFile = curl_exec($query);
			curl_close($query);
			$TextFile=iconv("GB2312","UTF-8",$TextFile);
		//	echo $TextFile."   00000  ";
		//	*/
			}catch(Exception $e){
				$TextFile=$e -> getMessage();
			}
			//MARC导出
			$query = curl_init($importURL.$cA["MARCParameter"]);
			curl_setopt($query, CURLOPT_RETURNTRANSFER, true);
			$htmlText = curl_exec($query);
			//这个链接应该是存在，除非国图网页有错误
			if($htmlText == false) {
				throw new FetchException(self::FETCHER_NAME . "获取" . $importURL.$cA["MARCParameter"] . "出现错误，这个链接[2]应该是存在，除非国图网页有错误，联系管理员～".$detailUrl);
			}
			curl_close($query);
			//分析实际文件下载地址
			@$resultDOMDocumnt -> loadHTML($htmlText);
			$resultDOMXPath -> __construct($resultDOMDocumnt);
			$FileURL=$resultDOMXPath -> evaluate($cA["fileDownloadURL"]);
			//下载文件
			$query=curl_init($FileURL);
			curl_setopt($query, CURLOPT_RETURNTRANSFER, true);
			$MARCFile = curl_exec($query);
			curl_close($query);
			$MARCFile=iconv("GB2312","UTF-8",$MARCFile);
		//	echo $MARCFile;
			
			//详细信息页面地址
			$detailUrl=$sessionString.$cA["detailUrl"].$setNumber;
			$query = curl_init($detailUrl);
			curl_setopt($query, CURLOPT_RETURNTRANSFER, true);
			$htmlText = curl_exec($query);
			//这个链接应该是存在，除非国图网页有错误
			if($htmlText == false) {
				throw new FetchException(self::FETCHER_NAME . "获取" . $detailUrl . "出现错误，这个链接[3]应该是存在，除非国图网页有错误，联系管理员～".$detailUrl);
			}
			curl_close($query);
		}
		$htmlText='<html><head><META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8" > </head><body><table>'.$htmlText.'</table></body></html>';
	//	echo $htmlText;
		@$resultDOMDocumnt -> loadHTML($htmlText);
		$resultDOMXPath -> __construct($resultDOMDocumnt);
		//	print_r($resultDOMXPath->evaluate($cA["catalogueNamesXpath"]));
		//	print_r($resultDOMXPath->evaluate($cA["catalogueValuesXpath"]));

		$catalogueNames = $resultDOMXPath -> evaluate($cA["catalogueNamesXpath"]);
		$catalogueValues = $resultDOMXPath -> evaluate($cA["catalogueValuesXpath"]);
		if($catalogueNames == false || $catalogueNames -> length == 0) {
			throw new FetchException(self::FETCHER_NAME . "，获取数据出现错误，可能原始网页格式特征改变。本次寻址特征：" . $cA["catalogueNamesXpath"] . " ， " . $cA["catalogueValuesXpath"] . "链接地址：" . $detailUrl . "。");
		}
		$rawDTO = new CatalogueRawDTO($ISBN, $htmlText, time(), self::FETCHER_NAME, "#");
		//	for($i = 0; $i < $catalogueNames -> length; $i++) {
		//		echo "begin:" . trim($catalogueNames -> item($i) -> nodeValue) . ":end\n";
		//		echo "begin:" . trim($catalogueValues -> item($i) -> nodeValue) . ":end\n";
		//	}
		for($i = 0; $i < $catalogueValues -> length; $i++) {
			$rawDTO -> setInfArrayItem(trim($catalogueNames -> item($i) -> nodeValue), html_entity_decode(trim($catalogueValues -> item($i) -> nodeValue), ENT_QUOTES, "UTF-8"));
		//	echo $catalogueNames -> item($i) -> nodeValue;
		}
		if($MARCFile!=""){//对于重新处理的，这部分是不需要修改的，文件还是用原来那个。
			$rawDTO->setSourceMARC($MARCFile);
			$rawDTO->setSourceText($TextFile);
		}
		return $rawDTO;
	}

}

// $xx = new NationalLibraryFetcher();
// $xx -> work("9787040240986");
// echo "\n\n--------\n\n";
// echo serialize($xx);
/*
 * 美国国会图书馆：
 * 当存在PID时，直接返回项目页面，但是不是详细的
 * http://catalog.loc.gov/cgi-bin/Pwebrecon.cgi?SAB1=0764548026&BOOL1=all+of+these&FLD1=LCCN-ISBN-ISSN+%28KNUM%29+%28KNUM%29&CNT=10&PID=7v-TX6yMI8GJlxrG48wzo4Oo5zze
 *
 * PID的处理，有点弯路哦，等着吧
 * http://catalog.loc.gov/cgi-bin/Pwebrecon.cgi?Search_Arg=0764548026&Search_Code=STNO&PID=kVycfQBM7LbkaUn0Tr6-4ilHwH_P
 * */
//http://catalog.loc.gov/cgi-bin/Pwebrecon.cgi?PID=MLJcrdibOxrIo_sxAbM44lNd-lpX&RID=12431348&SAVE=Press+to+SAVE+or+PRINT&RD=1|3
 class LibiaryOfUSCongressFetcher implements AutoCatalogueFetcher{
 	private $configArray;
	const FETCHER_NAME = "美国国会图书馆";
	public function __construct($configArray = null) {
		global $LibiaryOfUSCongressConfig;
		if($configArray == null)
			$this -> configArray = $LibiaryOfUSCongressConfig;
		else
			$this -> configArray = $configArray;
	}
	public function work($ISBN, $htmlText = ""){
		$resultDOMDocumnt = new DOMDocument("1.0", "utf-8");
		$resultDOMXPath = new DOMXPath($resultDOMDocumnt);
		$cA = &$this -> configArray;
		$sourceMARC="";
		$sourceText="";
		if($htmlText==""){
			$cookie = tempnam ("/tmp", "CURLCOOKIE");
			$query = curl_init($cA["interfaceUrl"]);
			curl_setopt($query, CURLOPT_RETURNTRANSFER, true);
		//	curl_setopt( $query, CURLOPT_COOKIEFILE, $cookie );
		//	curl_setopt( $query, CURLOPT_COOKIEJAR, $cookie );
			$htmlText = curl_exec($query);
			curl_close($query);
			if($htmlText == false) {
				throw new FetchException(self::FETCHER_NAME . "获取" . $cA["interfaceUrl"] . "出现错误，可能是链接过期，或许需要修改链接或者升级系统。");
			}
		//	echo $htmlText;
			@$resultDOMDocumnt -> loadHTML($htmlText);
			$resultDOMXPath -> __construct($resultDOMDocumnt);
			$pid=$resultDOMXPath -> evaluate($cA["PidXPath"]);
			if($pid==false){
				throw new FetchException(self::FETCHER_NAME . "，查询页面返回正常，但未能定位检索参数PID的值，可能需要升级系统代码或修改查询特征。本次定位XPath：" . $cA["PidXPath"]. "，链接地址：" . $cA["interfaceUrl"]. "。");
			}
			$seq=$resultDOMXPath -> evaluate($cA["SeqXPath"]);
			if($seq==false){
				throw new FetchException(self::FETCHER_NAME . "，查询页面返回正常，但未能定位检索参数SEQ的值，可能需要升级系统代码或修改查询特征。本次定位XPath：" .$cA["SeqXPath"]. "，链接地址：" . $cA["interfaceUrl"]. "。");
			}
			$queryUrl=$resultDOMXPath -> evaluate($cA["queryUrlXPath"]);
			if($queryUrl===false){
				throw new FetchException(self::FETCHER_NAME . "，查询页面返回正常，但未能定位检索参数查询链接的值，可能需要升级系统代码或修改查询特征。本次定位XPath：" . $cA["queryUrlXPath"]. "，链接地址：" . $cA["interfaceUrl"]. "。");
			}
			if(substr($queryUrl,0,1)=="/"){
				$queryUrl=$cA["homepage"].$queryUrl;
			}
			$queryUrl=$queryUrl."?Search_Arg=".$ISBN."&Search_Code=STNO&PID=".$pid."&SEQ=".$seq."&CNT=100&HIST=1";
			$query = curl_init($queryUrl);
			curl_setopt($query, CURLOPT_RETURNTRANSFER, true);
		//	curl_setopt($query, CURLOPT_REFERER, $cA["interfaceUrl"]);
		//	curl_setopt( $query, CURLOPT_COOKIEFILE, $cookie );
		//	curl_setopt( $query, CURLOPT_COOKIEJAR, $cookie );
			$htmlText = curl_exec($query);
			if($htmlText == false) {
				throw new FetchException(self::FETCHER_NAME . "获取" . $queryUrl . "出现错误，查询失败。");
			}
		//	echo $htmlText;
			@$resultDOMDocumnt -> loadHTML($htmlText);
			$resultDOMXPath -> __construct($resultDOMDocumnt);
			if($resultDOMXPath->evaluate($cA["queryFailXPath"])==$cA["queryFailString"]){
				throw new CatalogueInformationNotFoundException(self::FETCHER_NAME . "查找" . $ISBN . "失败，查询URL：".$queryUrl."。\n");
			}
			
			$detailUrl=$resultDOMXPath->evaluate($cA["detailUrlXpath"]);
			
			if($detailUrl == false) {
				throw new FetchException(self::FETCHER_NAME . "，查询页面返回正常，但未能定位详细页面链接地址，可能需要升级系统代码或修改查询特征。本次抽取详细页面链接地址关键词：" . $cA["detailUrlXpath"]. "，链接地址：" . $queryUrl. "。");
			}
			if(substr($detailUrl,0,1)=="/"){
				$detailUrl=$cA["homepage"].$detailUrl;
			}

			$query = curl_init($detailUrl);
			curl_setopt($query, CURLOPT_RETURNTRANSFER, true);
		//	curl_setopt($query, CURLOPT_REFERER, $queryUrl);
		//	curl_setopt( $query, CURLOPT_COOKIEFILE, $cookie );
		//	curl_setopt( $query, CURLOPT_COOKIEJAR, $cookie );
			$htmlText = curl_exec($query);
			if($htmlText == false) {
				throw new FetchException(self::FETCHER_NAME . "获取" . $detailUrl . "出现错误，这个链接应该是存在，除非美国国会图书馆网页有错误，这个是个奇迹，但是它就是发生了，至于你信不信，反正我是信了。嗯，等会试试吧，实在不行，联系管理员～");
			}
			@$resultDOMDocumnt -> loadHTML($htmlText);
			$resultDOMXPath -> __construct($resultDOMDocumnt);
			$rid=$resultDOMXPath -> evaluate($cA["RIDXPath"]);
			$query = curl_init("http://catalog.loc.gov/cgi-bin/Pwebrecon.cgi?PID=$pid&RID=$rid&SAVE=Press+to+SAVE+or+PRINT&RD=1");
	//		echo "http://catalog.loc.gov/cgi-bin/Pwebrecon.cgi?PID=$pid&RID=$rid&SAVE=Press+to+SAVE+or+PRINT&RD=1";
			curl_setopt($query, CURLOPT_RETURNTRANSFER, true);
			$sourceText = curl_exec($query);////////////
			$query = curl_init("http://catalog.loc.gov/cgi-bin/Pwebrecon.cgi?PID=$pid&RID=$rid&SAVE=Press+to+SAVE+or+PRINT&RD=3");
			curl_setopt($query, CURLOPT_RETURNTRANSFER, true);
			$sourceMARC = curl_exec($query);////////////
			curl_close($query);
		}
		@$resultDOMDocumnt -> loadHTML($htmlText);
		$resultDOMXPath -> __construct($resultDOMDocumnt);
		$catalogueNames = $resultDOMXPath -> evaluate($cA["catalogueNamesXpath"]);
		$catalogueValues = $resultDOMXPath -> evaluate($cA["catalogueValuesXpath"]);
		if($catalogueNames == false || $catalogueNames -> length == 0) {
			throw new FetchException(self::FETCHER_NAME . "，获取数据出现错误，可能原始网页格式特征改变。本次寻址特征：" . $cA["catalogueNamesXpath"] . " ， " . $cA["catalogueValuesXpath"] . "链接地址：" . $detailUrl . "。");
		}
		$rawDTO = new CatalogueRawDTO($ISBN, $htmlText, time(), self::FETCHER_NAME, "#");
		if($sourceMARC!=""){
			$rawDTO->setSourceMARC($sourceMARC);
			$rawDTO->setSourceText($sourceText);
		}
		$lastClName="";
		for($i = 0; $i < $catalogueValues -> length; $i++) {
			if($catalogueNames -> item($i) -> attributes->getNamedItem("colspan")->nodeValue=="2")break;
			$clName=trim($catalogueNames -> item($i) -> nodeValue);
			$clValue=trim($catalogueValues -> item($i) -> nodeValue);

			if($clName=="LCCN permalink:"){//TODO 或许该写得更优美些
				$rawDTO->setUrl($clValue);
			}

			if($clName==""){
				$clName=$lastClName;
			}
			// echo $clName."  -  ".$clValue."
// ";
			$lastClName=$clName;
			$rawDTO -> setInfArrayItem(html_entity_decode($clName), html_entity_decode($clValue), ENT_QUOTES, "UTF-8");

		}
		return $rawDTO;
	}

	public function getFetcherName() {
		return self::FETCHER_NAME;
	}

	public function setConfigArray($CongressLibrayConfig) {
		$this -> configArray = $CongressLibrayConfig;
	}

 }
 
// $xx = new LibiaryOfUSCongressFetcher();
// $xx -> work("156091131X");//0764548026
 
function newFetcherFromSource($source) {
	switch($source){
		case "中国国家图书馆":
			return new NationalLibraryFetcher();
			break;
		case "美国国会图书馆":
			return new LibiaryOfUSCongressFetcher();
			break;
		default:
	}
}
?>
