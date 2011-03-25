SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

DROP SCHEMA IF EXISTS `suivfin` ;
CREATE SCHEMA IF NOT EXISTS `suivfin` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `suivfin` ;

-- -----------------------------------------------------
-- Table `suivfin`.`type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`type` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`type` (
  `typeID` INT NOT NULL AUTO_INCREMENT ,
  `typeName` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`typeID`) ,
  UNIQUE INDEX `typeNameIDX` (`typeName` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `suivfin`.`currency`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`currency` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`currency` (
  `currencyID` INT NOT NULL AUTO_INCREMENT ,
  `currencyName` VARCHAR(255) NOT NULL ,
  `currencySymbol` VARCHAR(4) NOT NULL ,
  PRIMARY KEY (`currencyID`) ,
  UNIQUE INDEX `currencyNameIDX` (`currencyName` ASC) ,
  INDEX `currencySymbol` (`currencySymbol` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `suivfin`.`method`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`method` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`method` (
  `methodID` INT NOT NULL AUTO_INCREMENT ,
  `methodName` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`methodID`) ,
  UNIQUE INDEX `methodNameIDX` (`methodName` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `suivfin`.`origin`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`origin` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`origin` (
  `originID` INT NOT NULL AUTO_INCREMENT ,
  `originName` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`originID`) ,
  UNIQUE INDEX `originNameIDX` (`originName` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `suivfin`.`status`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`status` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`status` (
  `statusID` INT NOT NULL AUTO_INCREMENT ,
  `statusName` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`statusID`) ,
  UNIQUE INDEX `statusNameIDX` (`statusName` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `suivfin`.`owner`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`owner` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`owner` (
  `ownerID` INT NOT NULL AUTO_INCREMENT ,
  `ownerName` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`ownerID`) ,
  UNIQUE INDEX `ownerNameIDX` (`ownerName` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `suivfin`.`location`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`location` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`location` (
  `locationID` INT NOT NULL ,
  `locationName` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`locationID`) ,
  UNIQUE INDEX `locationNameIDX` (`locationName` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `suivfin`.`flux`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`flux` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`flux` (
  `fluxID` INT NOT NULL AUTO_INCREMENT ,
  `fluxDate` DATETIME NOT NULL ,
  `fluxUpdate` DATETIME NOT NULL ,
  `fluxTypeFK` INT NOT NULL ,
  `fluxAmount` FLOAT NOT NULL ,
  `fluxCurrencyFK` INT NOT NULL ,
  `fluxMethodFK` INT NOT NULL ,
  `fluxLabel` VARCHAR(255) NOT NULL ,
  `fluxRecipient` VARCHAR(255) NOT NULL ,
  `fluxOriginFK` INT NOT NULL ,
  `fluxRecurrent` TINYINT(1)  NOT NULL ,
  `fluxStatusFK` INT NOT NULL ,
  `fluxOwnerFK` INT NOT NULL ,
  `fluxComment` TEXT NULL ,
  `fluxLocationFK` INT NOT NULL ,
  PRIMARY KEY (`fluxID`) ,
  INDEX `fluxDateIDX` (`fluxDate` ASC) ,
  INDEX `fluxTypeFK` (`fluxTypeFK` ASC) ,
  INDEX `fluxCurrencyFK` (`fluxCurrencyFK` ASC) ,
  INDEX `fluxMethodFK` (`fluxMethodFK` ASC) ,
  INDEX `fluxOriginFK` (`fluxOriginFK` ASC) ,
  INDEX `fluxStatusFK` (`fluxStatusFK` ASC) ,
  INDEX `fluxOwnerFK` (`fluxOwnerFK` ASC) ,
  INDEX `fluxLocationFK` (`fluxLocationFK` ASC) ,
  CONSTRAINT `fluxTypeFK`
    FOREIGN KEY (`fluxTypeFK` )
    REFERENCES `suivfin`.`type` (`typeID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fluxCurrencyFK`
    FOREIGN KEY (`fluxCurrencyFK` )
    REFERENCES `suivfin`.`currency` (`currencyID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fluxMethodFK`
    FOREIGN KEY (`fluxMethodFK` )
    REFERENCES `suivfin`.`method` (`methodID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fluxOriginFK`
    FOREIGN KEY (`fluxOriginFK` )
    REFERENCES `suivfin`.`origin` (`originID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fluxStatusFK`
    FOREIGN KEY (`fluxStatusFK` )
    REFERENCES `suivfin`.`status` (`statusID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fluxOwnerFK`
    FOREIGN KEY (`fluxOwnerFK` )
    REFERENCES `suivfin`.`owner` (`ownerID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fluxLocationFK`
    FOREIGN KEY (`fluxLocationFK` )
    REFERENCES `suivfin`.`location` (`locationID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
COMMENT = 'contient les entrées et sorties d\'argent';


-- -----------------------------------------------------
-- Table `suivfin`.`balance`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`balance` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`balance` (
  `balanceID` INT NOT NULL ,
  `balanceCurrencyFK` INT NOT NULL ,
  `balanceOriginFK` INT NOT NULL ,
  `balanceTypeFK` INT NOT NULL ,
  PRIMARY KEY (`balanceID`) ,
  INDEX `balanceCurrencyFK` (`balanceCurrencyFK` ASC) ,
  INDEX `balanceOriginFK` (`balanceOriginFK` ASC) ,
  INDEX `balanceTypeFK` (`balanceTypeFK` ASC) ,
  CONSTRAINT `balanceCurrencyFK`
    FOREIGN KEY (`balanceCurrencyFK` )
    REFERENCES `suivfin`.`currency` (`currencyID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `balanceOriginFK`
    FOREIGN KEY (`balanceOriginFK` )
    REFERENCES `suivfin`.`origin` (`originID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `balanceTypeFK`
    FOREIGN KEY (`balanceTypeFK` )
    REFERENCES `suivfin`.`type` (`typeID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `suivfin`.`evolution`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`evolution` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`evolution` (
  `evolutionID` INT NOT NULL AUTO_INCREMENT ,
  `evolutionDate` DATETIME NOT NULL ,
  `evolutionBalanceFK` INT NOT NULL ,
  PRIMARY KEY (`evolutionID`) ,
  UNIQUE INDEX `evolutionDateIDX` (`evolutionDate` ASC) ,
  INDEX `evolutionBalanceFK` (`evolutionBalanceFK` ASC) ,
  CONSTRAINT `evolutionBalanceFK`
    FOREIGN KEY (`evolutionBalanceFK` )
    REFERENCES `suivfin`.`balance` (`balanceID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------
-- Data for table `suivfin`.`type`
-- -----------------------------------------------------
SET AUTOCOMMIT=0;
USE `suivfin`;
INSERT INTO `suivfin`.`type` (`typeID`, `typeName`) VALUES ('1', 'dépôt');
INSERT INTO `suivfin`.`type` (`typeID`, `typeName`) VALUES ('2', 'retrait');
INSERT INTO `suivfin`.`type` (`typeID`, `typeName`) VALUES ('3', 'dépense permanente');
INSERT INTO `suivfin`.`type` (`typeID`, `typeName`) VALUES ('4', 'dépense diverse');

COMMIT;

-- -----------------------------------------------------
-- Data for table `suivfin`.`currency`
-- -----------------------------------------------------
SET AUTOCOMMIT=0;
USE `suivfin`;
INSERT INTO `suivfin`.`currency` (`currencyID`, `currencyName`, `currencySymbol`) VALUES ('1', 'Euro', '€');
INSERT INTO `suivfin`.`currency` (`currencyID`, `currencyName`, `currencySymbol`) VALUES ('2', 'Franc', 'CHF');

COMMIT;

-- -----------------------------------------------------
-- Data for table `suivfin`.`method`
-- -----------------------------------------------------
SET AUTOCOMMIT=0;
USE `suivfin`;
INSERT INTO `suivfin`.`method` (`methodID`, `methodName`) VALUES ('1', 'liquide');
INSERT INTO `suivfin`.`method` (`methodID`, `methodName`) VALUES ('2', 'prélèvement');
INSERT INTO `suivfin`.`method` (`methodID`, `methodName`) VALUES ('3', 'virement');
INSERT INTO `suivfin`.`method` (`methodID`, `methodName`) VALUES ('4', 'carte');
INSERT INTO `suivfin`.`method` (`methodID`, `methodName`) VALUES ('5', 'chèque');

COMMIT;

-- -----------------------------------------------------
-- Data for table `suivfin`.`origin`
-- -----------------------------------------------------
SET AUTOCOMMIT=0;
USE `suivfin`;
INSERT INTO `suivfin`.`origin` (`originID`, `originName`) VALUES ('1', 'liquide €');
INSERT INTO `suivfin`.`origin` (`originID`, `originName`) VALUES ('2', 'liquide CHF');
INSERT INTO `suivfin`.`origin` (`originID`, `originName`) VALUES ('3', 'BNP Commun');
INSERT INTO `suivfin`.`origin` (`originID`, `originName`) VALUES ('4', 'BNP Guillaume');
INSERT INTO `suivfin`.`origin` (`originID`, `originName`) VALUES ('5', 'BNP Kariade');
INSERT INTO `suivfin`.`origin` (`originID`, `originName`) VALUES ('6', 'Postfinance Commun');
INSERT INTO `suivfin`.`origin` (`originID`, `originName`) VALUES ('7', 'Postfinance Guillaume');
INSERT INTO `suivfin`.`origin` (`originID`, `originName`) VALUES ('8', 'Postfinance Kariade');

COMMIT;

-- -----------------------------------------------------
-- Data for table `suivfin`.`status`
-- -----------------------------------------------------
SET AUTOCOMMIT=0;
USE `suivfin`;
INSERT INTO `suivfin`.`status` (`statusID`, `statusName`) VALUES ('1', 'Vérifié');
INSERT INTO `suivfin`.`status` (`statusID`, `statusName`) VALUES ('2', 'Prévisible');
INSERT INTO `suivfin`.`status` (`statusID`, `statusName`) VALUES ('3', 'A vérifier');
INSERT INTO `suivfin`.`status` (`statusID`, `statusName`) VALUES ('4', 'A payer');

COMMIT;

-- -----------------------------------------------------
-- Data for table `suivfin`.`owner`
-- -----------------------------------------------------
SET AUTOCOMMIT=0;
USE `suivfin`;
INSERT INTO `suivfin`.`owner` (`ownerID`, `ownerName`) VALUES ('1', 'Guillaume');
INSERT INTO `suivfin`.`owner` (`ownerID`, `ownerName`) VALUES ('2', 'Kariade');
INSERT INTO `suivfin`.`owner` (`ownerID`, `ownerName`) VALUES ('3', 'Commun');

COMMIT;

-- -----------------------------------------------------
-- Data for table `suivfin`.`location`
-- -----------------------------------------------------
SET AUTOCOMMIT=0;
USE `suivfin`;
INSERT INTO `suivfin`.`location` (`locationID`, `locationName`) VALUES ('1', 'Genève');
INSERT INTO `suivfin`.`location` (`locationID`, `locationName`) VALUES ('2', 'Carouge');
INSERT INTO `suivfin`.`location` (`locationID`, `locationName`) VALUES ('3', 'Saint-Julien-en-Genevois');
INSERT INTO `suivfin`.`location` (`locationID`, `locationName`) VALUES ('4', 'Collonge-sous-Salève');
INSERT INTO `suivfin`.`location` (`locationID`, `locationName`) VALUES ('5', 'Annemasse');
INSERT INTO `suivfin`.`location` (`locationID`, `locationName`) VALUES ('6', 'Etrembière');

COMMIT;
