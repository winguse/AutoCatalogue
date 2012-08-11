<?php

class FetchException extends Exception{
	function __construct($msg,$code=0){
		parent::__construct($msg,$code);
	}
}
class CatalogueInformationNotFoundException extends FetchException{
	function __construct($msg,$code=0){
		parent::__construct($msg,$code);
	}
}
?>