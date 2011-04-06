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
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `typeNameIDX` (`name` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `suivfin`.`currency`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`currency` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`currency` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(255) NOT NULL ,
  `symbol` VARCHAR(4) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `currencyNameIDX` (`name` ASC) ,
  INDEX `currencySymbol` (`symbol` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `suivfin`.`method`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`method` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`method` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `methodNameIDX` (`name` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `suivfin`.`origin`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`origin` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`origin` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `originNameIDX` (`name` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `suivfin`.`status`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`status` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`status` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `statusNameIDX` (`name` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `suivfin`.`owner`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`owner` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`owner` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `ownerNameIDX` (`name` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `suivfin`.`location`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`location` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`location` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `locationNameIDX` (`name` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `suivfin`.`recipient`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`recipient` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`recipient` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `recipientNameIDX` (`name` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `suivfin`.`payment`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`payment` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`payment` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `label` VARCHAR(255) NOT NULL ,
  `paymentDate` DATE NOT NULL ,
  `amount` FLOAT NOT NULL ,
  `comment` TEXT NULL ,
  `recurrent` TINYINT(1)  NOT NULL ,
  `recipientFK` INT NOT NULL ,
  `typeFK` INT NOT NULL ,
  `currencyFK` INT NOT NULL ,
  `methodFK` INT NOT NULL ,
  `originFK` INT NOT NULL ,
  `statusFK` INT NOT NULL ,
  `ownerFK` INT NOT NULL ,
  `locationFK` INT NOT NULL ,
  `creationDate` DATETIME NOT NULL ,
  `modificationDate` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `paymentDateIDX` (`creationDate` ASC) ,
  INDEX `paymentTypeFK` (`typeFK` ASC) ,
  INDEX `paymentCurrencyFK` (`currencyFK` ASC) ,
  INDEX `paymentMethodFK` (`methodFK` ASC) ,
  INDEX `paymentOriginFK` (`originFK` ASC) ,
  INDEX `paymentStatusFK` (`statusFK` ASC) ,
  INDEX `paymentOwnerFK` (`ownerFK` ASC) ,
  INDEX `paymentLocationFK` (`locationFK` ASC) ,
  INDEX `paymentRecipientFK` (`recipientFK` ASC) ,
  CONSTRAINT `paymentTypeFK`
    FOREIGN KEY (`typeFK` )
    REFERENCES `suivfin`.`type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `paymentCurrencyFK`
    FOREIGN KEY (`currencyFK` )
    REFERENCES `suivfin`.`currency` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `paymentMethodFK`
    FOREIGN KEY (`methodFK` )
    REFERENCES `suivfin`.`method` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `paymentOriginFK`
    FOREIGN KEY (`originFK` )
    REFERENCES `suivfin`.`origin` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `paymentStatusFK`
    FOREIGN KEY (`statusFK` )
    REFERENCES `suivfin`.`status` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `paymentOwnerFK`
    FOREIGN KEY (`ownerFK` )
    REFERENCES `suivfin`.`owner` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `paymentLocationFK`
    FOREIGN KEY (`locationFK` )
    REFERENCES `suivfin`.`location` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `paymentRecipientFK`
    FOREIGN KEY (`recipientFK` )
    REFERENCES `suivfin`.`recipient` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
COMMENT = 'contient les entrées et sorties d\'argent';


-- -----------------------------------------------------
-- Table `suivfin`.`balance`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`balance` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`balance` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `currencyFK` INT NOT NULL ,
  `originFK` INT NOT NULL ,
  `typeFK` INT NOT NULL ,
  `lastUpdate` DATE NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `balanceCurrencyFK` (`currencyFK` ASC) ,
  INDEX `balanceOriginFK` (`originFK` ASC) ,
  INDEX `balanceTypeFK` (`typeFK` ASC) ,
  CONSTRAINT `balanceCurrencyFK`
    FOREIGN KEY (`currencyFK` )
    REFERENCES `suivfin`.`currency` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `balanceOriginFK`
    FOREIGN KEY (`originFK` )
    REFERENCES `suivfin`.`origin` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `balanceTypeFK`
    FOREIGN KEY (`typeFK` )
    REFERENCES `suivfin`.`type` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `suivfin`.`evolution`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`evolution` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`evolution` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `evolutionDate` DATE NOT NULL ,
  `balanceFK` INT NOT NULL ,
  `amount` FLOAT NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `evolutionDateIDX` (`evolutionDate` ASC) ,
  INDEX `evolutionBalanceFK` (`balanceFK` ASC) ,
  CONSTRAINT `evolutionBalanceFK`
    FOREIGN KEY (`balanceFK` )
    REFERENCES `suivfin`.`balance` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `suivfin`.`list_timestamp`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`list_timestamp` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`list_timestamp` (
  `id` VARCHAR(255) NOT NULL ,
  `stamp` TIMESTAMP NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `stampIDX` (`stamp` ASC) )
ENGINE = MyISAM;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------
-- Data for table `suivfin`.`type`
-- -----------------------------------------------------
SET AUTOCOMMIT=0;
USE `suivfin`;
INSERT INTO `suivfin`.`type` (`id`, `name`) VALUES ('1', 'dépôt');
INSERT INTO `suivfin`.`type` (`id`, `name`) VALUES ('2', 'dépense');
INSERT INTO `suivfin`.`type` (`id`, `name`) VALUES ('3', 'transfert');

COMMIT;

-- -----------------------------------------------------
-- Data for table `suivfin`.`currency`
-- -----------------------------------------------------
SET AUTOCOMMIT=0;
USE `suivfin`;
INSERT INTO `suivfin`.`currency` (`id`, `name`, `symbol`) VALUES ('1', 'Euro', '€');
INSERT INTO `suivfin`.`currency` (`id`, `name`, `symbol`) VALUES ('2', 'Franc', 'CHF');

COMMIT;

-- -----------------------------------------------------
-- Data for table `suivfin`.`method`
-- -----------------------------------------------------
SET AUTOCOMMIT=0;
USE `suivfin`;
INSERT INTO `suivfin`.`method` (`id`, `name`) VALUES ('1', 'prélèvement');
INSERT INTO `suivfin`.`method` (`id`, `name`) VALUES ('2', 'virement');
INSERT INTO `suivfin`.`method` (`id`, `name`) VALUES ('3', 'carte');
INSERT INTO `suivfin`.`method` (`id`, `name`) VALUES ('4', 'chèque');

COMMIT;

-- -----------------------------------------------------
-- Data for table `suivfin`.`origin`
-- -----------------------------------------------------
SET AUTOCOMMIT=0;
USE `suivfin`;
INSERT INTO `suivfin`.`origin` (`id`, `name`) VALUES ('1', 'BNP Commun');
INSERT INTO `suivfin`.`origin` (`id`, `name`) VALUES ('2', 'BNP Guillaume');
INSERT INTO `suivfin`.`origin` (`id`, `name`) VALUES ('3', 'BNP Kariade');
INSERT INTO `suivfin`.`origin` (`id`, `name`) VALUES ('4', 'Postfinance Commun');
INSERT INTO `suivfin`.`origin` (`id`, `name`) VALUES ('5', 'Postfinance Guillaume');
INSERT INTO `suivfin`.`origin` (`id`, `name`) VALUES ('6', 'Postfinance Kariade');

COMMIT;

-- -----------------------------------------------------
-- Data for table `suivfin`.`status`
-- -----------------------------------------------------
SET AUTOCOMMIT=0;
USE `suivfin`;
INSERT INTO `suivfin`.`status` (`id`, `name`) VALUES ('1', 'Vérifié');
INSERT INTO `suivfin`.`status` (`id`, `name`) VALUES ('2', 'Prévisible');
INSERT INTO `suivfin`.`status` (`id`, `name`) VALUES ('3', 'À vérifier');
INSERT INTO `suivfin`.`status` (`id`, `name`) VALUES ('4', 'À payer');

COMMIT;

-- -----------------------------------------------------
-- Data for table `suivfin`.`owner`
-- -----------------------------------------------------
SET AUTOCOMMIT=0;
USE `suivfin`;
INSERT INTO `suivfin`.`owner` (`id`, `name`) VALUES ('1', 'Guillaume');
INSERT INTO `suivfin`.`owner` (`id`, `name`) VALUES ('2', 'Kariade');
INSERT INTO `suivfin`.`owner` (`id`, `name`) VALUES ('3', 'Commun');

COMMIT;

-- -----------------------------------------------------
-- Data for table `suivfin`.`location`
-- -----------------------------------------------------
SET AUTOCOMMIT=0;
USE `suivfin`;
INSERT INTO `suivfin`.`location` (`id`, `name`) VALUES ('1', 'Genève');
INSERT INTO `suivfin`.`location` (`id`, `name`) VALUES ('2', 'Carouge');
INSERT INTO `suivfin`.`location` (`id`, `name`) VALUES ('3', 'Saint-Julien-en-Genevois');
INSERT INTO `suivfin`.`location` (`id`, `name`) VALUES ('4', 'Collonge-sous-Salève');
INSERT INTO `suivfin`.`location` (`id`, `name`) VALUES ('5', 'Annemasse');
INSERT INTO `suivfin`.`location` (`id`, `name`) VALUES ('6', 'Etrembière');

COMMIT;
