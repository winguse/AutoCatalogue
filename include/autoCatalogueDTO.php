<?php

class CatalogueInfDTO {
	private $code;
	private $message;
	private $bookname;
	private $isbn;
	private $source;
	private $fetchTime;
	private $url;
	private $htmlText;
	private $needUpdated;
	private $trusted;
	/*
	 * $infArray=array(
	 * 		array(
	 * 			array("clCode" => "编目号", "clName" => "编目名", "clValue" => "编目值")
	 * 		)
	 * )
	 * 这个是直接表格的表示。
	 * */
	private $infArray;
	
	private $sourceText;//数据源提供的导出文本。
	private $sourceMARC;//数据源提供的MARC文件
	
	public function __construct($code = 0, $message = "", $bookname = "", 
	$isbn = "", $source = "", $fetchTime = null, $url = "",$htmlText="",$needUpdated="", $trusted = 0, $infArray = array()) {
		$this -> code = $code;
		$this -> message = $message;
		$this -> bookname = $bookname;
		$this -> isbn = $isbn;
		$this -> source = $source;
		$this -> fetchTime = $fetchTime;
		$this -> url = $url;
		$this->htmlText=$htmlText;
		$this->needUpdated=$needUpdated;
		$this -> trusted = $trusted;
		$this -> infArray = $infArray;
	}

	public function setTrusted($value){
		$this->trusted=$value;
	}

	public function getTrusted(){
		return $this->trusted;
	}
	public function setNeedUpdated($value, $reWrite = true) {
		if($reWrite)
			$this -> needUpdated = $value;
		else
			$this -> needUpdated |= $value;

	}

	public function setHtmlText($value) {
		$this -> htmlText = $value;
	}

	public function getNeedUpdated() {
		return $this -> needUpdated;
	}

	public function getHtmlText() {
		return $this -> htmlText;
	}

	public function setCode($value) {
		$this -> code = $value;
	}

	public function setMessage($value) {
		$this -> message = $value;
	}

	public function addMessage($value) {
		$this -> message .= $value;
	}

	public function setBookname($value) {
		$this -> bookname = $value;
	}

	public function setIsbn($value) {
		$this -> isbn = $value;
	}

	public function setSource($value) {
		$this -> source = $value;
	}

	public function setFetchTime($value) {
		$this -> fetchTime = $value;
	}

	public function setUrl($value) {
		$this -> url = $value;
	}

	public function setInfArray($value) {
		$this -> infArray = $value;
	}

	public function getCode() {
		return $this -> code;
	}

	public function getMessage() {
		return $this -> message;
	}

	public function getBookname() {
		return $this -> bookname;
	}

	public function getIsbn() {
		return $this -> isbn;
	}

	public function getSource() {
		return $this -> source;
	}

	public function getFetchTime() {
		return $this -> fetchTime;
	}

	public function getUrl() {
		return $this -> url;
	}

	public function getInfArray() {
		return $this -> infArray;
	}

	public function setInfArrayItem($clCode, $clName, $clValue) {
		$this -> infArray[] = array("clCode" => $clCode, "clName" => $clName, "clValue" => $clValue);
	}

	public function getInfArrayItem($clCode) {
		//好像没什么必要
		$ret = array();
		foreach($this -> infArray as $item) {
			if($item["clCode"] == $clCode)
				$ret[] = $item;
		}
		return $ret;
	}

	public function toArray() {
		$ret = array();
		$ret["code"] = $this -> code;
		$ret["message"] = $this -> message;
		$ret["description"]["bookname"] = $this -> bookname;
		$ret["description"]["isbn"] = $this -> isbn;
		$ret["description"]["source"] = $this -> source;
		$ret["description"]["fetchTime"] = $this -> fetchTime;
		$ret["description"]["url"] = $this -> url;
		$ret["main"] = $this -> infArray;
		return $ret;
	}

	public function getSourceText(){
		return $this->sourceText;
	}
	
	public function getSourceMARC(){
		return $this->sourceMARC;
	}
	
	public function setSourceText($sourceText){
		$this->sourceText=$sourceText;
	}
	
	public function setSourceMARC($sourceMARC){
		$this->sourceMARC=$sourceMARC;
	}
	
	public function getText(){
		$text="";
		foreach($this->infArray as $item){
			$text.=sprintf("%s\t%s\t%s\n",$item["clCode"],$item["clName"],$item["clValue"]);
		}
		return $text;
	}
	
	public function getCSV(){
		$csv="\"编号\",\"项目\",\"值\"\n";
		foreach($this->infArray as $item){
			$csv.=sprintf("\"%s\",\"%s\",\"%s\"\n",str_replace("\"","\"\"",$item["clCode"]),str_replace("\"","\"\"",$item["clName"]),str_replace("\"","\"\"",$item["clValue"]));
		}
		return $csv;
	}
}

class CatalogueRawDTO {
	private $isbn;
	private $htmlText;
	private $fetchTime;
	private $source;
	private $url;
	private $sourceText;//数据源提供的导出文本。
	private $sourceMARC;//数据源提供的MARC文件
	/*
	 * $infArray =array("编目名"=>array("编目值","编目值"))
	 * 因为有时候，同一个编目名下，数据源提供多个参考值，这里用这样的结构保存
	 * */
	private $infArray;
	public function __construct($isbn = "", $htmlText = "", $fetchTime = null, $source = "", $url = "", $infArray = array()) {
		$this -> isbn = $isbn;
		$this -> htmlText = $htmlText;
		$this -> fetchTime = $fetchTime;
		$this -> source = $source;
		$this -> url = $url;
		$this -> infArray = $infArray;
	}

	public function toArray() {
		$ret = array();
		$ret["description"]["isbn"] = $this -> isbn;
		$ret["description"]["source"] = $this -> source;
		$ret["description"]["url"] = $this -> url;
		$ret["description"]["fetchTime"] = $this -> fetchTime;
		$ret["description"]["htmlText"] = $this -> htmlText;
		$ret["main"] = $this -> infArray;
		return $ret;
	}

	public function setIsbn($value) {
		$this -> isbn = $value;
	}

	public function setHtmlText($value) {
		$this -> htmlText = $value;
	}

	public function setFetchTime($value) {
		$this -> fetchTime = $value;
	}

	public function setSourceName($value) {
		$this -> source = $value;
	}

	public function setUrl($value) {
		$this -> url = $value;
	}

	public function setInfArray($value) {
		$this -> infArray = $value;
	}

	public function setInfArrayItem($key, $value) {
		$this -> infArray[$key][] = $value;
	}

	public function getInfArrayItem($key) {
		return $this -> infArray[$key];
	}

	public function getIsbn() {
		return $this -> isbn;
	}

	public function getHtmlText() {
		return $this -> htmlText;
	}

	public function getFetchTime() {
		return $this -> fetchTime;
	}

	public function getSource() {
		return $this -> source;
	}

	public function getUrl() {
		return $this -> url;
	}

	public function getInfArray() {
		return $this -> infArray;
	}
	public function getSourceText(){
		return $this->sourceText;
	}
	
	public function getSourceMARC(){
		return $this->sourceMARC;
	}
	
	public function setSourceText($sourceText){
		$this->sourceText=$sourceText;
	}
	
	public function setSourceMARC($sourceMARC){
		$this->sourceMARC=$sourceMARC;
	}
}

/*
 $t=new CatalogueRawDTO("isbn","htmltext",time(),"sourcename","url");
 $t->setInfArrayItem("x", "y");
 $t->setInfArrayItem("x", "yy");
 $t->setInfArrayItem("xx", "yyy");

 print_r($t);

 echo json_encode($t->toArray());
 */
?>