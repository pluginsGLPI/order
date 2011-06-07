-- 
-- Structure de la table `glpi_plugin_order`
-- 

DROP TABLE IF EXISTS `glpi_plugin_order`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_order` (
   `ID` int(11) NOT NULL auto_increment,
   `name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
   `numorder` varchar(255) NOT NULL collate utf8_unicode_ci default '',
   `budget` int (11) NOT NULL default 0,
   `taxes` FLOAT NOT NULL default 0,
   `payment` int (11) NOT NULL default 0,
   `status` int(11) NOT NULL default 1,
   `FK_entities` int(11) NOT NULL default 0,
   `date` date,
   `FK_enterprise` INT(11) NOT NULL DEFAULT 0,
   `location` int(11) NOT NULL default 0,
   `FK_contact` int(11) NOT NULL default 0,
   `port_price` FLOAT NOT NULL default 0,
   `recursive` INT(1) NOT NULL default 1,
   `deleted` INT(1) NOT NULL default 0,
   `notes` TEXT,
   `comment` varchar(255) collate utf8_unicode_ci NOT NULL default '',
   PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_order_detail`
-- 

DROP TABLE IF EXISTS `glpi_plugin_order_detail`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_order_detail` (
   `ID` int(11) NOT NULL auto_increment,
   `FK_order` int(11) NOT NULL default 0,
   `device_type` int(11) NOT NULL default 0,
   `FK_device` int(11) NOT NULL default 0,
   `FK_reference` int(11) NOT NULL default 0,
   `deliverynum` varchar(255) NOT NULL collate utf8_unicode_ci default '',
   `price_taxfree` FLOAT NOT NULL default 0,
   `price_discounted` FLOAT NOT NULL default 0,
   `discount` FLOAT NOT NULL default 0,
   `price_ati` FLOAT NOT NULL default 0,
   `status` int(1) NOT NULL default 0,
   `date` date NOT NULL default 0,
   PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_order_references`
-- 

DROP TABLE IF EXISTS `glpi_plugin_order_references`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_order_references` (
   `ID` int(11) NOT NULL auto_increment,
   `FK_entities` int(11) NOT NULL DEFAULT 0,
   `FK_glpi_enterprise` int(11) NOT NULL DEFAULT 0,
   `FK_type` INT(11) NOT NULL DEFAULT 0,
   `FK_model` INT(11) NOT NULL DEFAULT 0,
   `name` varchar(255) collate utf8_unicode_ci NOT NULL,
   `type` int(11) NOT NULL DEFAULT 0,
   `template` int(11) NOT NULL DEFAULT 0,
   `recursive` int(11) NOT NULL DEFAULT 0,
   `deleted` int(11) NOT NULL DEFAULT 0,
   `comments` text  collate utf8_unicode_ci NULL,
   PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_order_suppliers`
-- 

CREATE TABLE IF NOT EXISTS `glpi_plugin_order_suppliers` (
   `ID` int(11) NOT NULL auto_increment,
   `FK_order` int(11) NOT NULL default 0,
   `numquote` varchar(255) NOT NULL collate utf8_unicode_ci default '',
   `numorder` varchar(255) NOT NULL collate utf8_unicode_ci default '',
   `numbill` varchar(255) NOT NULL collate utf8_unicode_ci default '',
   PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_order_references_manufacturers`
-- 

DROP TABLE IF EXISTS `glpi_plugin_order_references_manufacturers`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_order_references_manufacturers` (
   `ID` int(11) NOT NULL auto_increment,
   `FK_entities` int(11) NOT NULL DEFAULT 0,
   `FK_reference` int(11) NOT NULL DEFAULT 0,
   `FK_enterprise` int(11) NOT NULL DEFAULT 0,
   `price_taxfree` float NOT NULL DEFAULT 0,
   `reference_code` varchar(255) NOT NULL collate utf8_unicode_ci default '',
   PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table `glpi_dropdown_plugin_order_payment`
-- 

DROP TABLE IF EXISTS `glpi_dropdown_plugin_order_payment`;
CREATE TABLE IF NOT EXISTS `glpi_dropdown_plugin_order_payment` (
   `ID` int(11) NOT NULL auto_increment,
   `name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
   `comments` text,
   PRIMARY KEY  (`ID`),
   KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

-- 
-- Structure de la table `glpi_dropdown_plugin_order_taxes`
-- 

DROP TABLE IF EXISTS `glpi_dropdown_plugin_order_taxes`;
CREATE TABLE IF NOT EXISTS `glpi_dropdown_plugin_order_taxes` (
   `ID` int(11) NOT NULL auto_increment,
   `name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
   `comments` text,
   PRIMARY KEY  (`ID`),
   KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_dropdown_plugin_order_taxes` (ID,name) VALUES (1,'5.5'), (2,'19.6');

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_order_profiles`
-- 

DROP TABLE IF EXISTS `glpi_plugin_order_profiles`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_order_profiles` (
   `ID` int(11) NOT NULL auto_increment,
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `order` char(1) default NULL,
   `reference` char(1) default NULL,
   `budget` char(1) default NULL,
   `validation` char(1) default NULL,
   `cancel` char(1) default NULL,
   `undo_validation` char(1) default NULL,
   PRIMARY KEY  (`ID`),
   KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_order_config`
-- 

DROP TABLE IF EXISTS `glpi_plugin_order_config`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_order_config` (
   `ID` int(11) NOT NULL auto_increment,
   `use_validation` int(11) NOT NULL default 0,
   `default_taxes` int(11) NOT NULL default 0,
   PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_order_config` (ID,use_validation,default_taxes) VALUES (1,0,0);

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_order_mailing`
-- 

DROP TABLE IF EXISTS `glpi_plugin_order_mailing`;
CREATE TABLE IF NOT EXISTS `glpi_plugin_order_mailing` (
   `ID` int(11) NOT NULL auto_increment,
   `type` varchar(255) collate utf8_unicode_ci default NULL,
   `FK_item` int(11) NOT NULL default '0',
   `item_type` int(11) NOT NULL default '0',
   PRIMARY KEY  (`ID`),
   UNIQUE KEY `mailings` (`type`,`FK_item`,`item_type`),
   KEY `type` (`type`),
   KEY `FK_item` (`FK_item`),
   KEY `item_type` (`item_type`),
   KEY `items` (`item_type`,`FK_item`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_order_budgets`
-- 

DROP TABLE IF EXISTS `glpi_plugin_order_budgets`;
CREATE TABLE `glpi_plugin_order_budgets` (
   `ID` INT( 11 ) NOT NULL AUTO_INCREMENT ,
   `name` VARCHAR( 255 ) collate utf8_unicode_ci NULL,
   `FK_entities` int(11) NOT NULL DEFAULT 0,
   `FK_budget` INT( 11 ) NOT NULL ,
   `deleted` int(11) NOT NULL DEFAULT 0,
   `comments` text  collate utf8_unicode_ci NULL,
   `startdate` DATE NULL ,
   `enddate` DATE NULL ,
   `value` FLOAT( 11 ) NOT NULL ,
   PRIMARY KEY ( `ID` )
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

INSERT INTO glpi_display VALUES (NULL,'3150','1','1','0');
INSERT INTO glpi_display VALUES (NULL,'3150','2','2','0');
INSERT INTO glpi_display VALUES (NULL,'3150','4','4','0');
INSERT INTO glpi_display VALUES (NULL,'3150','5','5','0');
INSERT INTO glpi_display VALUES (NULL,'3150','6','6','0');
INSERT INTO glpi_display VALUES (NULL,'3150','7','7','0');
INSERT INTO glpi_display VALUES (NULL,'3150','10','10','0');

INSERT INTO glpi_display VALUES (NULL,'3151','1','1','0');
INSERT INTO glpi_display VALUES (NULL,'3151','2','4','0');
INSERT INTO glpi_display VALUES (NULL,'3151','4','5','0');
INSERT INTO glpi_display VALUES (NULL,'3151','5','9','0');
INSERT INTO glpi_display VALUES (NULL,'3151','6','6','0');
INSERT INTO glpi_display VALUES (NULL,'3151','7','7','0');

INSERT INTO glpi_display VALUES (NULL,'3153','2','1','0');
INSERT INTO glpi_display VALUES (NULL,'3153','4','2','0');
INSERT INTO glpi_display VALUES (NULL,'3153','5','3','0');
INSERT INTO glpi_display VALUES (NULL,'3153','6','4','0');