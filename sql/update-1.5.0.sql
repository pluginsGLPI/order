DROP TABLE IF EXISTS `glpi_plugin_order_orderstates`;
CREATE TABLE `glpi_plugin_order_orderstates` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `glpi_plugin_order_orders_items` 
   ADD `plugin_order_bills_id` INT( 11 ) NOT NULL DEFAULT '0';

ALTER TABLE  `glpi_plugin_order_configs` 
   ADD  `order_status_draft` int(11) NOT NULL default '0',
   ADD  `order_status_waiting_approval` int(11) NOT NULL default '0',
   ADD  `order_status_approved` int(11) NOT NULL default '0',
   ADD  `order_status_partially_delivred` int(11) NOT NULL default '0',
   ADD  `order_status_completly_delivered` int(11) NOT NULL default '0',
   ADD  `order_status_canceled` int(11) NOT NULL default '0';

CREATE TABLE IF NOT EXISTS `glpi_plugin_order_bills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `number` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `billdate` datetime DEFAULT NULL,
  `validationdate` datetime DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  `plugin_order_billstates_id` int(11) NOT NULL DEFAULT '0',
  `value` float NOT NULL DEFAULT '0',
  `plugin_order_billtypes_id` int(11) NOT NULL DEFAULT '0',
  `users_id_validation` int(11) NOT NULL DEFAULT '0',
  `entities_id` int(11) NOT NULL DEFAULT '0',
  `is_recursive` int(11) NOT NULL DEFAULT '0',
  `notepad` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;


CREATE TABLE IF NOT EXISTS `glpi_plugin_order_billstates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_order_billtypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;
