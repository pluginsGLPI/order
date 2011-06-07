ALTER TABLE `glpi_plugin_order` RENAME `glpi_plugin_order_orders`;
ALTER TABLE `glpi_plugin_order_detail` RENAME `glpi_plugin_order_orders_items`;
ALTER TABLE `glpi_plugin_order_suppliers` RENAME `glpi_plugin_order_orders_suppliers`;
ALTER TABLE `glpi_plugin_order_references_manufacturers` RENAME `glpi_plugin_order_references_suppliers`;
ALTER TABLE `glpi_dropdown_plugin_order_payment` RENAME `glpi_plugin_order_orderpayments`;
ALTER TABLE `glpi_dropdown_plugin_order_taxes` RENAME `glpi_plugin_order_ordertaxes`;
ALTER TABLE `glpi_plugin_order_config` RENAME `glpi_plugin_order_configs`;
DROP TABLE IF EXISTS `glpi_plugin_order_mailing`;
DROP TABLE IF EXISTS `glpi_dropdown_plugin_order_status`;
ALTER TABLE `glpi_dropdown_plugin_order_deliverystate` RENAME `glpi_plugin_order_deliverystates`;

ALTER TABLE `glpi_plugin_order_orders` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `recursive` `is_recursive` tinyint(1) NOT NULL default '0',
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `numorder` `num_order` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `budget` `budgets_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_budgets (id)',
   CHANGE `taxes` `plugin_order_ordertaxes_id` float NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_ordertaxes (id)',
   CHANGE `payment` `plugin_order_orderpayments_id` int (11)  NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orderpayments (id)',
   CHANGE `date` `order_date` date default NULL,
   CHANGE `FK_enterprise` `suppliers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)',
   CHANGE `FK_contact` `contacts_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_contacts (id)',
   CHANGE `location` `locations_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_locations (id)',
   CHANGE `status` `states_id` int(11) NOT NULL default 1,
   CHANGE `comment` `comment` text collate utf8_unicode_ci,
   CHANGE `notes` `notepad` longtext collate utf8_unicode_ci,
   CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL default '0',
   ADD INDEX (`name`),
   ADD INDEX (`entities_id`),
   ADD INDEX (`plugin_order_ordertaxes_id`),
   ADD INDEX (`plugin_order_orderpayments_id`),
   ADD INDEX (`states_id`),
   ADD INDEX (`suppliers_id`),
   ADD INDEX (`contacts_id`),
   ADD INDEX (`locations_id`),
   ADD INDEX (`is_deleted`);


ALTER TABLE `glpi_plugin_order_orders_items` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_order` `plugin_order_orders_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orders (id)',
   CHANGE `device_type` `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
   CHANGE `FK_device` `items_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',
   CHANGE `FK_reference` `plugin_order_references_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_references (id)',
   CHANGE `delivery_status` `plugin_order_deliverystates_id` int (11)  NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_deliverystates (id)',
   CHANGE `deliverynum` `delivery_number` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `delivery_comments` `delivery_comment` text collate utf8_unicode_ci,
   CHANGE `status` `states_id` int(11) NOT NULL default 1,
   CHANGE `date` `delivery_date` date default NULL,
   ADD INDEX `FK_device` (`items_id`,`itemtype`),
   ADD INDEX `item` (`itemtype`,`items_id`),
   ADD INDEX (`plugin_order_references_id`),
   ADD INDEX (`plugin_order_deliverystates_id`),
   ADD INDEX (`states_id`);

ALTER TABLE `glpi_plugin_order_references` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `recursive` `is_recursive` tinyint(1) NOT NULL default '0',
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `FK_glpi_enterprise` `manufacturers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_manufacturers (id)',
   CHANGE `FK_type` `types_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtypes tables (id)',
   CHANGE `FK_model` `models_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemmodels tables (id)',
   CHANGE `type` `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
   CHANGE `template` `templates_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',
   CHANGE `comments` `comment` text collate utf8_unicode_ci,
   ADD `notepad` longtext collate utf8_unicode_ci,
   CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL default '0',
   ADD INDEX (`name`),
   ADD INDEX (`entities_id`),
   ADD INDEX (`manufacturers_id`),
   ADD INDEX (`types_id`),
   ADD INDEX (`models_id`),
   ADD INDEX (`templates_id`),
   ADD INDEX (`is_deleted`);

ALTER TABLE `glpi_plugin_order_references_suppliers` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
	ADD `is_recursive` tinyint(1) NOT NULL default '0',
   CHANGE `FK_reference` `plugin_order_references_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_references (id)',
   CHANGE `FK_enterprise` `suppliers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)',
   CHANGE `reference_code` `reference_code` varchar(255) collate utf8_unicode_ci default NULL,
   ADD INDEX (`plugin_order_references_id`),
   ADD INDEX (`suppliers_id`);

ALTER TABLE `glpi_plugin_order_budgets` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `FK_entities` `entities_id` int(11) NOT NULL default '0',
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `FK_budget` `budgets_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_budgets (id)',
   CHANGE `startdate` `start_date` date default NULL,
   CHANGE `enddate` `end_date` date default NULL,
   CHANGE `value` `value` float NOT NULL DEFAULT 0,
   CHANGE `comments` `comment` text collate utf8_unicode_ci,
   CHANGE `deleted` `is_deleted` tinyint(1) NOT NULL default '0',
   ADD INDEX (`entities_id`),
   ADD INDEX (`budgets_id`),
   ADD INDEX (`is_deleted`);

ALTER TABLE `glpi_plugin_order_orders_suppliers` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   ADD `entities_id` int(11) NOT NULL default '0',
	ADD `is_recursive` tinyint(1) NOT NULL default '0',
   CHANGE `FK_order` `plugin_order_orders_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orders (id)',
   ADD `suppliers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)',
   CHANGE `numquote` `num_quote` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `numorder` `num_order` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `numbill` `num_bill` varchar(255) collate utf8_unicode_ci default NULL,
   ADD INDEX (`plugin_order_orders_id`),
   ADD INDEX (`suppliers_id`);

ALTER TABLE `glpi_plugin_order_orderpayments` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `comments` `comment` text collate utf8_unicode_ci;

ALTER TABLE `glpi_plugin_order_ordertaxes` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `comments` `comment` text collate utf8_unicode_ci;

ALTER TABLE `glpi_plugin_order_profiles` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   ADD `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
   CHANGE `order` `order` char(1) collate utf8_unicode_ci default NULL,
   CHANGE `reference` `reference` char(1) collate utf8_unicode_ci default NULL,
   CHANGE `budget` `budget` char(1) collate utf8_unicode_ci default NULL,
   CHANGE `validation` `validation` char(1) collate utf8_unicode_ci default NULL,
   CHANGE `cancel` `cancel` char(1) collate utf8_unicode_ci default NULL,
   CHANGE `undo_validation` `undo_validation` char(1) collate utf8_unicode_ci default NULL,
   ADD INDEX (`profiles_id`);

ALTER TABLE `glpi_plugin_order_configs` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment;
   
-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_order_others`
-- 

DROP TABLE IF EXISTS `glpi_plugin_order_others`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_order_others` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `othertypes_id` int(11) NOT NULL default '0',
   PRIMARY KEY  (`ID`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `othertypes_id` (`othertypes_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_order_othertypes`
-- 

DROP TABLE IF EXISTS `glpi_plugin_order_othertypes`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_order_othertypes` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `comment` text collate utf8_unicode_ci,
   PRIMARY KEY  (`ID`),
   KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `glpi_plugin_order_deliverystates` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   CHANGE `name` `name` varchar(255) collate utf8_unicode_ci default NULL,
   CHANGE `comments` `comment` text collate utf8_unicode_ci;

ALTER TABLE `glpi_plugin_order_surveysuppliers` 
   CHANGE `ID` `id` int(11) NOT NULL auto_increment,
   ADD `entities_id` int(11) NOT NULL default '0',
	ADD `is_recursive` tinyint(1) NOT NULL default '0',
   CHANGE `FK_order` `plugin_order_orders_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orders (id)',
   CHANGE `FK_enterprise` `suppliers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)',
   CHANGE `comment` `comment` text collate utf8_unicode_ci,
   ADD INDEX (`plugin_order_orders_id`),
   ADD INDEX (`suppliers_id`);

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_order_preferences`
-- 

DROP TABLE IF EXISTS `glpi_plugin_order_preferences`;
CREATE TABLE `glpi_plugin_order_preferences` (
	`id` int(11) NOT NULL auto_increment,
	`user_id` int(11) NOT NULL default 0,
	`template` varchar(255) collate utf8_unicode_ci default NULL,
	`sign` varchar(255) collate utf8_unicode_ci default NULL,
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_notificationtemplates` VALUES(NULL, 'Order Validation', 'PluginOrderOrder', '2010-03-12 22:36:46','');