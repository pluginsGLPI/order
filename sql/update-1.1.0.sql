ALTER TABLE `glpi_plugin_order_detail` ADD `discount` FLOAT( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_order` 
   ADD `port_price` FLOAT NOT NULL default '0',
   ADD `taxes` FLOAT NOT NULL default '0',
   DROP INDEX `name`;
ALTER TABLE `glpi_plugin_order_references` CHANGE `FK_manufacturer` `FK_glpi_enterprise` int(11) NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_order_references_manufacturers` ADD `reference_code` varchar(255) NOT NULL collate utf8_unicode_ci default '';

CREATE TABLE IF NOT EXISTS `glpi_plugin_order_suppliers` (
   `ID` int(11) NOT NULL auto_increment,
   `FK_order` int(11) NOT NULL default 0,
   `numquote` varchar(255) NOT NULL collate utf8_unicode_ci default '',
   `numorder` varchar(255) NOT NULL collate utf8_unicode_ci default '',
   `numbill` varchar(255) NOT NULL collate utf8_unicode_ci default '',
   PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE `glpi_dropdown_plugin_order_status`;