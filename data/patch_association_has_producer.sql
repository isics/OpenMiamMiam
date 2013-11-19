SET @commission = 12;

ALTER TABLE `association_has_producer`
DROP PRIMARY KEY,
ADD `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
ADD commission NUMERIC(10, 2) NOT NULL;

ALTER TABLE sales_order_row ADD commission NUMERIC(10, 2) NOT NULL;

UPDATE `association_has_producer` SET `commission` = @commission;
UPDATE `sales_order_row` SET `commission` = @commission;
