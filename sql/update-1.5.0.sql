DROP TABLE IF EXISTS `glpi_plugin_order_orderstates`;
CREATE TABLE `glpi_plugin_order_orderstates` (
	`id` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE  `glpi_plugin_order_orders` 
	CHANGE  `states_id`  `plugin_order_orderstates_id` INT( 11 ) NOT NULL DEFAULT  '1';
	
ALTER TABLE  `glpi_plugin_order_configs` 
	ADD  `order_status_draft` int(11) NOT NULL default '0',
	ADD  `order_status_waiting_approval` int(11) NOT NULL default '0',
	ADD  `order_status_approved` int(11) NOT NULL default '0',
	ADD  `order_status_partially_delivred` int(11) NOT NULL default '0',
	ADD  `order_status_completly_delivered` int(11) NOT NULL default '0',
	ADD  `order_status_canceled` int(11) NOT NULL default '0';