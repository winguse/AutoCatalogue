CREATE SCHEMA `autocatalogue` ;

USE `autocatalogue` ;

DROP TABLE IF EXISTS `Catalogue`;
DROP TABLE IF EXISTS `User`;
CREATE TABLE `User` (
  `UserId` int(10) NOT NULL AUTO_INCREMENT,
  `Username` varchar(45) NOT NULL,
  `Password` varchar(45) DEFAULT NULL,
  `LoginIp` varchar(45) DEFAULT NULL,
  `LoginTime` varchar(45) DEFAULT NULL,
  `Email` varchar(45) DEFAULT NULL,
  `Authority` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`UserId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE `Catalogue` (
  `CatalogueId` int(10) NOT NULL AUTO_INCREMENT ,
  `LogUserId` int(10) NULL ,
  `Source` VARCHAR(255) NULL ,
  `ISBN` VARCHAR(45) NULL ,
  `BookName` LONGTEXT NULL ,
  `CatalogueInfDTO` LONGTEXT NULL ,/*再次更新，这次直接存放对象，20110813*/
  `LogTime` DATETIME NULL,
  `LastCheckTime` DATETIME NULL ,
  `NeedUpdated` TINYINT(2)  DEFAULT 0 ,/* 0不需要更新 1需要更新编目号/名 2用户标记需要更新 4太旧 */
  `Trusted` TINYINT(1)  NULL DEFAULT false ,
  PRIMARY KEY (`CatalogueId`) ,
  CONSTRAINT `FK_LogUserId`  FOREIGN KEY (`LogUserId`)
    REFERENCES `User`(`UserId`)  ON DELETE CASCADE  ON UPDATE CASCADE
)ENGINE = InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS  `Name2Code`;
CREATE TABLE `Name2Code` (
  `Name2CodeId` INT NOT NULL AUTO_INCREMENT ,
  `Name` VARCHAR(45) NULL ,
  `Code` VARCHAR(45) NULL ,
  `UpdateTime` INT NULL,
  PRIMARY KEY (`Name2CodeId`),
  UNIQUE INDEX `Name2CodeName_UNIQUE` (`Name` ASC)
)ENGINE = InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `MARCInf`;
CREATE TABLE `MARCInf` (
  `MARCInfId` INT NOT NULL AUTO_INCREMENT ,
  `MARCInfCode` VARCHAR(45) NULL DEFAULT NULL ,
  `MARCInfName` VARCHAR(45) NULL DEFAULT NULL ,
  `MARCInfNecessary` TINYINT(1)  NULL DEFAULT False ,
  `UpdateTime` INT NULL,
  PRIMARY KEY (`MARCInfId`) ,
  UNIQUE INDEX `MARCInfCode_UNIQUE` (`MARCInfCode` ASC)
)ENGINE = InnoDB DEFAULT CHARSET=utf8;

/*
DROP TABLE IF EXISTS `Source`;
CREATE TABLE `Source` (
  `SourceId` int(10) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) DEFAULT NULL,
  `URL` longtext,
  `Description` longtext,
  PRIMARY KEY (`SourceId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
*/

/*
DROP TABLE IF EXISTS `CatalogueCodautocatelogue.usere2Name`;
CREATE TABLE `CatalogueCode2Name`(
    `CatalogueCNId` int(10) NOT NULL AUTO_INCREMENT ,
    `CatalogueCode` VARCHAR(10) NOT NULL ,
    `CatalogueName` VARCHAR(40) NOT NULL,
    PRIMARY KEY (`CatalogueCNId`)
)ENGINE = InnoDB DEFAULT CHARSET=utf8;

CREATE  TABLE `autocatalogue`.`FetchConfig` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `FetcherName` VARCHAR(45) NULL ,
  `ConfigArray` LONGTEXT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;



INSERT INTO `autocatalogue`.`cataloguecode2name` (`CatalogueCode`, `CatalogueName`) VALUES ('001', '记录控制号');
INSERT INTO `autocatalogue`.`cataloguecode2name` (`CatalogueCode`, `CatalogueName`) VALUES ('010', '国际标准书号');
INSERT INTO `autocatalogue`.`cataloguecode2name` (`CatalogueCode`, `CatalogueName`) VALUES ('011', '国际标准连续出版物号');
*/

