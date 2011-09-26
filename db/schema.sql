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
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `typeNameIDX` (`name` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `suivfin`.`currency`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`currency` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`currency` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
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
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `methodNameIDX` (`name` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `suivfin`.`origin`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`origin` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`origin` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `originNameIDX` (`name` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `suivfin`.`status`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`status` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`status` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `statusNameIDX` (`name` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `suivfin`.`owner`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`owner` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`owner` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
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
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(255) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `recipientNameIDX` (`name` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `suivfin`.`payment`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`payment` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`payment` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `label` VARCHAR(255) NOT NULL ,
  `paymentDate` DATE NOT NULL ,
  `paymentMonth` VARCHAR(7) NOT NULL ,
  `amount` DECIMAL(7,2) UNSIGNED NOT NULL ,
  `comment` TEXT NULL ,
  `recurrent` TINYINT(1)  NOT NULL ,
  `recipientFK` INT(11) UNSIGNED NOT NULL ,
  `typeFK` INT(11) UNSIGNED NOT NULL ,
  `currencyFK` INT(11) UNSIGNED NOT NULL ,
  `methodFK` INT(11) UNSIGNED NOT NULL ,
  `originFK` INT(11) UNSIGNED NOT NULL ,
  `statusFK` INT(11) UNSIGNED NOT NULL ,
  `ownerFK` INT(11) UNSIGNED NOT NULL ,
  `locationFK` INT(11) UNSIGNED NOT NULL ,
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
  INDEX `paymentMonthIDX` (`paymentMonth` ASC) ,
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
COMMENT = 'contient les entrées et sorties d\'argent' ;


-- -----------------------------------------------------
-- Table `suivfin`.`evolution`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`evolution` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`evolution` (
  `originFK` INT(11) NOT NULL ,
  `evolutionDate` DATE NOT NULL ,
  `amount` DECIMAL(10,2) NOT NULL ,
  PRIMARY KEY (`originFK`, `evolutionDate`) ,
  INDEX `originIDX` (`originFK` ASC) ,
  INDEX `evolutionDateIDX` (`evolutionDate` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `suivfin`.`limits`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `suivfin`.`limits` ;

CREATE  TABLE IF NOT EXISTS `suivfin`.`limits` (
  `ownerFK` INT(11) UNSIGNED NOT NULL ,
  `originFK` INT(11) UNSIGNED NOT NULL ,
  `currencyFK` INT(11) UNSIGNED NOT NULL ,
  PRIMARY KEY (`ownerFK`, `originFK`, `currencyFK`) ,
  INDEX `originFK` (`originFK` ASC) ,
  INDEX `ownerFK` (`ownerFK` ASC) ,
  INDEX `currencyFK` (`currencyFK` ASC) ,
  CONSTRAINT `originFK`
    FOREIGN KEY (`originFK` )
    REFERENCES `suivfin`.`origin` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `ownerFK`
    FOREIGN KEY (`ownerFK` )
    REFERENCES `suivfin`.`owner` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `currencyFK`
    FOREIGN KEY (`currencyFK` )
    REFERENCES `suivfin`.`currency` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------
-- Data for table `suivfin`.`type`
-- -----------------------------------------------------
START TRANSACTION;
USE `suivfin`;
INSERT INTO `suivfin`.`type` (`id`, `name`) VALUES (1, 'dépôt');
INSERT INTO `suivfin`.`type` (`id`, `name`) VALUES (2, 'dépense');
INSERT INTO `suivfin`.`type` (`id`, `name`) VALUES (3, 'transfert');

COMMIT;

-- -----------------------------------------------------
-- Data for table `suivfin`.`currency`
-- -----------------------------------------------------
START TRANSACTION;
USE `suivfin`;
INSERT INTO `suivfin`.`currency` (`id`, `name`, `symbol`) VALUES (1, 'Euro', '€');
INSERT INTO `suivfin`.`currency` (`id`, `name`, `symbol`) VALUES (2, 'Franc', 'CHF');

COMMIT;

-- -----------------------------------------------------
-- Data for table `suivfin`.`method`
-- -----------------------------------------------------
START TRANSACTION;
USE `suivfin`;
INSERT INTO `suivfin`.`method` (`id`, `name`) VALUES (1, 'prélèvement');
INSERT INTO `suivfin`.`method` (`id`, `name`) VALUES (2, 'virement');
INSERT INTO `suivfin`.`method` (`id`, `name`) VALUES (3, 'carte');
INSERT INTO `suivfin`.`method` (`id`, `name`) VALUES (4, 'chèque');
INSERT INTO `suivfin`.`method` (`id`, `name`) VALUES (5, 'liquide');

COMMIT;

-- -----------------------------------------------------
-- Data for table `suivfin`.`origin`
-- -----------------------------------------------------
START TRANSACTION;
USE `suivfin`;
INSERT INTO `suivfin`.`origin` (`id`, `name`) VALUES (1, 'BNP Commun');
INSERT INTO `suivfin`.`origin` (`id`, `name`) VALUES (2, 'BNP Guillaume');
INSERT INTO `suivfin`.`origin` (`id`, `name`) VALUES (3, 'BNP Kariade');
INSERT INTO `suivfin`.`origin` (`id`, `name`) VALUES (4, 'Postfinance Commun');
INSERT INTO `suivfin`.`origin` (`id`, `name`) VALUES (5, 'Postfinance Guillaume');
INSERT INTO `suivfin`.`origin` (`id`, `name`) VALUES (6, 'Postfinance Kariade');
INSERT INTO `suivfin`.`origin` (`id`, `name`) VALUES (7, 'Liquide Euro');
INSERT INTO `suivfin`.`origin` (`id`, `name`) VALUES (8, 'Liquide Franc');

COMMIT;

-- -----------------------------------------------------
-- Data for table `suivfin`.`status`
-- -----------------------------------------------------
START TRANSACTION;
USE `suivfin`;
INSERT INTO `suivfin`.`status` (`id`, `name`) VALUES (1, 'Vérifié');
INSERT INTO `suivfin`.`status` (`id`, `name`) VALUES (2, 'Prévisible');
INSERT INTO `suivfin`.`status` (`id`, `name`) VALUES (3, 'À vérifier');
INSERT INTO `suivfin`.`status` (`id`, `name`) VALUES (4, 'À payer');

COMMIT;

-- -----------------------------------------------------
-- Data for table `suivfin`.`owner`
-- -----------------------------------------------------
START TRANSACTION;
USE `suivfin`;
INSERT INTO `suivfin`.`owner` (`id`, `name`) VALUES (1, 'Guillaume');
INSERT INTO `suivfin`.`owner` (`id`, `name`) VALUES (2, 'Kariade');
INSERT INTO `suivfin`.`owner` (`id`, `name`) VALUES (3, 'Commun');

COMMIT;

-- -----------------------------------------------------
-- Data for table `suivfin`.`location`
-- -----------------------------------------------------
START TRANSACTION;
USE `suivfin`;
INSERT INTO `suivfin`.`location` (`id`, `name`) VALUES (1, 'Genève');
INSERT INTO `suivfin`.`location` (`id`, `name`) VALUES (2, 'Carouge');
INSERT INTO `suivfin`.`location` (`id`, `name`) VALUES (3, 'Saint-Julien-en-Genevois');
INSERT INTO `suivfin`.`location` (`id`, `name`) VALUES (4, 'Collonge-sous-Salève');
INSERT INTO `suivfin`.`location` (`id`, `name`) VALUES (5, 'Annemasse');
INSERT INTO `suivfin`.`location` (`id`, `name`) VALUES (6, 'Etrembière');

COMMIT;

-- -----------------------------------------------------
-- Data for table `suivfin`.`recipient`
-- -----------------------------------------------------
START TRANSACTION;
USE `suivfin`;
INSERT INTO `suivfin`.`recipient` (`id`, `name`) VALUES (1, 'BNP Commun');
INSERT INTO `suivfin`.`recipient` (`id`, `name`) VALUES (2, 'BNP Guillaume');
INSERT INTO `suivfin`.`recipient` (`id`, `name`) VALUES (3, 'BNP Kariade');
INSERT INTO `suivfin`.`recipient` (`id`, `name`) VALUES (4, 'Postfinance Commun');
INSERT INTO `suivfin`.`recipient` (`id`, `name`) VALUES (5, 'Postfinance Guillaume');
INSERT INTO `suivfin`.`recipient` (`id`, `name`) VALUES (6, 'Postfinance Kariade');
INSERT INTO `suivfin`.`recipient` (`id`, `name`) VALUES (7, 'Liquide Euro');
INSERT INTO `suivfin`.`recipient` (`id`, `name`) VALUES (8, 'Liquide Franc');

COMMIT;

-- -----------------------------------------------------
-- Data for table `suivfin`.`limits`
-- -----------------------------------------------------
START TRANSACTION;
USE `suivfin`;
INSERT INTO `suivfin`.`limits` (`ownerFK`, `originFK`, `currencyFK`) VALUES (1, 2, 1);
INSERT INTO `suivfin`.`limits` (`ownerFK`, `originFK`, `currencyFK`) VALUES (1, 5, 2);
INSERT INTO `suivfin`.`limits` (`ownerFK`, `originFK`, `currencyFK`) VALUES (2, 3, 1);
INSERT INTO `suivfin`.`limits` (`ownerFK`, `originFK`, `currencyFK`) VALUES (2, 6, 2);
INSERT INTO `suivfin`.`limits` (`ownerFK`, `originFK`, `currencyFK`) VALUES (3, 1, 1);
INSERT INTO `suivfin`.`limits` (`ownerFK`, `originFK`, `currencyFK`) VALUES (3, 4, 2);
INSERT INTO `suivfin`.`limits` (`ownerFK`, `originFK`, `currencyFK`) VALUES (1, 7, 1);
INSERT INTO `suivfin`.`limits` (`ownerFK`, `originFK`, `currencyFK`) VALUES (1, 8, 2);
INSERT INTO `suivfin`.`limits` (`ownerFK`, `originFK`, `currencyFK`) VALUES (2, 7, 1);
INSERT INTO `suivfin`.`limits` (`ownerFK`, `originFK`, `currencyFK`) VALUES (2, 8, 2);
INSERT INTO `suivfin`.`limits` (`ownerFK`, `originFK`, `currencyFK`) VALUES (3, 7, 1);
INSERT INTO `suivfin`.`limits` (`ownerFK`, `originFK`, `currencyFK`) VALUES (3, 8, 2);

COMMIT;
