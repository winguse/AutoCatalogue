CREATE DATABASE  IF NOT EXISTS `autocatalogue` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `autocatalogue`;
-- MySQL dump 10.13  Distrib 5.5.9, for Win32 (x86)
--
-- Host: localhost    Database: autocatalogue
-- ------------------------------------------------------
-- Server version	5.1.63-0ubuntu0.11.10.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `FetchConfig`
--

DROP TABLE IF EXISTS `FetchConfig`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `FetchConfig` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `FetcherName` varchar(45) DEFAULT NULL,
  `ConfigArray` longtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `FetchConfig`
--

LOCK TABLES `FetchConfig` WRITE;
/*!40000 ALTER TABLE `FetchConfig` DISABLE KEYS */;
INSERT INTO `FetchConfig` VALUES (1,'NationalLibrayConfig','a:14:{s:12:\"interfaceUrl\";s:60:\"http://opac.nlc.gov.cn/F/?func=find-m&find_code=ISB&request=\";s:9:\"failXPath\";s:25:\"string(//html/head/title)\";s:10:\"failString\";s:33:\"中文及特藏文 - 多库检索\";s:15:\"set_numberXpath\";s:66:\"string(//html/body//form/input[@id=\'set_number\']/attribute::value)\";s:13:\"sessionString\";s:66:\"string(//html/head/meta[@http-equiv=\'REFRESH\']/attribute::content)\";s:9:\"detailUrl\";s:63:\"?func=full-set-set_body&set_entry=000001&format=002&set_number=\";s:19:\"catalogueNamesXpath\";s:10:\"//tr/td[1]\";s:20:\"catalogueValuesXpath\";s:10:\"//tr/td[2]\";s:9:\"importURL\";s:70:\"string(//div[@id=\'operate\']/a[@title=\'保存/邮寄\']/attribute::href)\";s:20:\"importURLReplaceFrom\";s:11:\"full-mail-0\";s:18:\"importURLReplaceTo\";s:9:\"full-mail\";s:13:\"textParameter\";s:11:\"&format=002\";s:13:\"MARCParameter\";s:11:\"&format=997\";s:15:\"fileDownloadURL\";s:58:\"string(//html/body/p[@class=\'text3\']/a[1]/attribute::href)\";}'),(2,'LibiaryOfUSCongressConfig','a:11:{s:8:\"homepage\";s:22:\"http://catalog.loc.gov\";s:12:\"interfaceUrl\";s:64:\"http://catalog.loc.gov/cgi-bin/Pwebrecon.cgi?DB=local&PAGE=First\";s:13:\"queryUrlXPath\";s:45:\"string(//html/body/form[1]/attribute::action)\";s:8:\"PidXPath\";s:62:\"string(//html/body/form[1]/table/tr/input[1]/attribute::value)\";s:8:\"SeqXPath\";s:62:\"string(//html/body/form[1]/table/tr/input[2]/attribute::value)\";s:14:\"queryFailXPath\";s:25:\"string(//html/head/title)\";s:15:\"queryFailString\";s:34:\"Library of Congress Online Catalog\";s:14:\"detailUrlXpath\";s:55:\"string(//html/body/form/center[2]/a[2]/attribute::href)\";s:19:\"catalogueNamesXpath\";s:34:\"//html/body/form/table[1]//tr/*[1]\";s:20:\"catalogueValuesXpath\";s:34:\"//html/body/form/table[1]//tr/*[2]\";s:8:\"RIDXPath\";s:60:\"string(//html/body/form/input[@name=\'RID\']/attribute::value)\";}');
/*!40000 ALTER TABLE `FetchConfig` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2012-06-27 22:20:03
