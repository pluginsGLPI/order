-- 
-- Structure de la table `glpi_plugin_order_orders`
-- 

DROP TABLE IF EXISTS `glpi_plugin_order_orders`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_order_orders` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
	`is_recursive` tinyint(1) NOT NULL default '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `num_order` varchar(255) collate utf8_unicode_ci default NULL,
   `budgets_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_budgets (id)',
   `plugin_order_ordertaxes_id` float NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_ordertaxes (id)',
   `plugin_order_orderpayments_id` int (11)  NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orderpayments (id)',
   `order_date` date default NULL,
   `suppliers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)',
   `contacts_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_contacts (id)',
   `locations_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_locations (id)',
   `states_id` int(11) NOT NULL default 1,
   `port_price` float NOT NULL default 0,
   `comment` text collate utf8_unicode_ci,
   `notepad` longtext collate utf8_unicode_ci,
   `is_deleted` tinyint(1) NOT NULL default '0',
   PRIMARY KEY  (`id`),
	KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `plugin_order_ordertaxes_id` (`plugin_order_ordertaxes_id`),
   KEY `plugin_order_orderpayments_id` (`plugin_order_orderpayments_id`),
   KEY `states_id` (`states_id`),
   KEY `suppliers_id` (`suppliers_id`),
   KEY `contacts_id` (`contacts_id`),
   KEY `locations_id` (`locations_id`),
   KEY `is_deleted` (`is_deleted`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_order_orders_items`
-- 

DROP TABLE IF EXISTS `glpi_plugin_order_orders_items`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_order_orders_items` (
   `id` int(11) NOT NULL auto_increment,
   `plugin_order_orders_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orders (id)',
   `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
   `items_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',
   `plugin_order_references_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_references (id)',
   `delivery_number` varchar(255) collate utf8_unicode_ci default NULL,
   `price_taxfree` float NOT NULL default 0,
   `price_discounted` float NOT NULL default 0,
   `discount` float NOT NULL default 0,
   `price_ati` float NOT NULL default 0,
   `states_id` int(11) NOT NULL default 1,
   `delivery_date` date default NULL,
   PRIMARY KEY  (`id`),
   KEY `FK_device` (`items_id`,`itemtype`),
   KEY `item` (`itemtype`,`items_id`),
   KEY `plugin_order_references_id` (`plugin_order_references_id`),
   KEY `states_id` (`states_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_order_references`
-- 

DROP TABLE IF EXISTS `glpi_plugin_order_references`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_order_references` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL default '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `manufacturers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_manufacturers (id)',
   `types_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtypes tables (id)',
   `models_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemmodels tables (id)',
   `itemtype` varchar(100) collate utf8_unicode_ci NOT NULL COMMENT 'see .class.php file',
   `templates_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',
   `comment` text collate utf8_unicode_ci,
   `is_deleted` tinyint(1) NOT NULL default '0',
   `notepad` longtext collate utf8_unicode_ci,
   PRIMARY KEY  (`id`),
	KEY `name` (`name`),
   KEY `entities_id` (`entities_id`),
   KEY `manufacturers_id` (`manufacturers_id`),
   KEY `types_id` (`types_id`),
   KEY `models_id` (`models_id`),
   KEY `templates_id` (`templates_id`),
   KEY `is_deleted` (`is_deleted`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_order_references_suppliers`
-- 

DROP TABLE IF EXISTS `glpi_plugin_order_references_suppliers`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_order_references_suppliers` (
   `id` int(11) NOT NULL auto_increment,
   `plugin_order_references_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_references (id)',
   `suppliers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)',
   `price_taxfree` float NOT NULL DEFAULT 0,
   `reference_code` varchar(255) collate utf8_unicode_ci default NULL,
   PRIMARY KEY  (`id`),
   KEY `entities_id` (`entities_id`),
   KEY `plugin_order_references_id` (`plugin_order_references_id`),
   KEY `suppliers_id` (`suppliers_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_order_budgets`
-- 

DROP TABLE IF EXISTS `glpi_plugin_order_budgets`;
CREATE TABLE `glpi_plugin_order_budgets` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default '0',
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `budgets_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_budgets (id)',
   `start_date` date default NULL,
   `end_date` date default NULL,
   `value` float NOT NULL DEFAULT 0,
   `comment` text collate utf8_unicode_ci,
   `is_deleted` tinyint(1) NOT NULL default '0',
   PRIMARY KEY  (`id`),
   KEY `entities_id` (`entities_id`),
   KEY `budgets_id` (`budgets_id`),
   KEY `is_deleted` (`is_deleted`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_order_orders_suppliers`
-- 

CREATE TABLE IF NOT EXISTS `glpi_plugin_order_orders_suppliers` (
   `id` int(11) NOT NULL auto_increment,
   `plugin_order_orders_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orders (id)',
   `suppliers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)',
   `num_quote` varchar(255) collate utf8_unicode_ci default NULL,
   `num_order` varchar(255) collate utf8_unicode_ci default NULL,
   `num_bill` varchar(255) collate utf8_unicode_ci default NULL,
   PRIMARY KEY  (`id`),
   KEY `plugin_order_orders_id` (`plugin_order_orders_id`),
   KEY `suppliers_id` (`suppliers_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_order_orderpayments`
-- 

DROP TABLE IF EXISTS `glpi_plugin_order_orderpayments`;
CREATE TABLE `glpi_plugin_order_orderpayments` (
	`id` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

-- 
-- Structure de la table `glpi_plugin_order_ordertaxes`
-- 

DROP TABLE IF EXISTS `glpi_plugin_order_ordertaxes`;
CREATE TABLE `glpi_plugin_order_ordertaxes` (
	`id` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_order_ordertaxes` (id,name) VALUES (1,'5.5'), (2,'19.6');

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_order_profiles`
-- 

DROP TABLE IF EXISTS `glpi_plugin_order_profiles`;
CREATE TABLE `glpi_plugin_order_profiles` (
	`id` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`order` char(1) collate utf8_unicode_ci default NULL,
	`reference` char(1) collate utf8_unicode_ci default NULL,
	`budget` char(1) collate utf8_unicode_ci default NULL,
	`validation` char(1) collate utf8_unicode_ci default NULL,
	`cancel` char(1) collate utf8_unicode_ci default NULL,
	`undo_validation` char(1) collate utf8_unicode_ci default NULL,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_order_configs`
-- 

DROP TABLE IF EXISTS `glpi_plugin_order_configs`;
CREATE TABLE `glpi_plugin_order_configs` (
	`id` int(11) NOT NULL auto_increment,
	`use_validation` int(11) NOT NULL default 0,
	`default_taxes` int(11) NOT NULL default 0,
	PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_order_configs` (id,use_validation,default_taxes) VALUES (1,0,0);

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_order_mailingsettings`
-- 

DROP TABLE IF EXISTS `glpi_plugin_order_mailingsettings`;
CREATE TABLE `glpi_plugin_order_mailingsettings` (
	`id` int(11) NOT NULL auto_increment,
	`type` varchar(255) collate utf8_unicode_ci default NULL COMMENT 'VALUE in (order)',
	`items_id` int(11) NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',
	`itemtype` int(11) NOT NULL default '0'  COMMENT 'see define.php *_MAILING_TYPE constant',
	PRIMARY KEY  (`id`),
	UNIQUE KEY `mailings` (`type`,`items_id`,`itemtype`),
	KEY `FK_item` (`items_id`),
	KEY `items` (`itemtype`,`items_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

INSERT INTO glpi_displaypreferences VALUES (NULL,'PluginOrderOrder','1','1','0');
INSERT INTO glpi_displaypreferences VALUES (NULL,'PluginOrderOrder','2','2','0');
INSERT INTO glpi_displaypreferences VALUES (NULL,'PluginOrderOrder','4','4','0');
INSERT INTO glpi_displaypreferences VALUES (NULL,'PluginOrderOrder','5','5','0');
INSERT INTO glpi_displaypreferences VALUES (NULL,'PluginOrderOrder','6','6','0');
INSERT INTO glpi_displaypreferences VALUES (NULL,'PluginOrderOrder','7','7','0');
INSERT INTO glpi_displaypreferences VALUES (NULL,'PluginOrderOrder','10','10','0');

INSERT INTO glpi_displaypreferences VALUES (NULL,'PluginOrderReference','1','1','0');
INSERT INTO glpi_displaypreferences VALUES (NULL,'PluginOrderReference','2','4','0');
INSERT INTO glpi_displaypreferences VALUES (NULL,'PluginOrderReference','4','5','0');
INSERT INTO glpi_displaypreferences VALUES (NULL,'PluginOrderReference','5','9','0');
INSERT INTO glpi_displaypreferences VALUES (NULL,'PluginOrderReference','6','6','0');
INSERT INTO glpi_displaypreferences VALUES (NULL,'PluginOrderReference','7','7','0');

INSERT INTO glpi_displaypreferences VALUES (NULL,'PluginOrderBudget','2','1','0');
INSERT INTO glpi_displaypreferences VALUES (NULL,'PluginOrderBudget','4','2','0');
INSERT INTO glpi_displaypreferences VALUES (NULL,'PluginOrderBudget','5','3','0');
INSERT INTO glpi_displaypreferences VALUES (NULL,'PluginOrderBudget','6','4','0');