ALTER TABLE `payment` DROP INDEX `paymentDateIDX`,
ADD INDEX `paymentCreationIDX` ( `creationDate` ),
ADD INDEX `paymentDateIDX` ( `paymentDate` ),
ADD INDEX `paymentMonthIDX` ( `paymentMonth` );

ALTER TABLE `payment` ADD `paymentMonth` VARCHAR( 7 ) NOT NULL AFTER `paymentDate`;
UPDATE payment SET paymentMonth = DATE_FORMAT( IF(DAYOFMONTH(paymentDate) > 24, DATE_ADD(paymentDate, INTERVAL 1 MONTH), paymentDate ), '%Y-%m');
