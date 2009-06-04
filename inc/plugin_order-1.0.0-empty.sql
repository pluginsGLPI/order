DROP TABLE IF EXISTS `glpi_plugin_order`;
CREATE TABLE `glpi_plugin_order` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) UNIQUE collate utf8_unicode_ci NOT NULL default '',
	`numordersupplier` varchar(255) NOT NULL collate utf8_unicode_ci default '',
	`deliverynum` varchar(255) NOT NULL collate utf8_unicode_ci default '',
	`numbill`varchar(255) NOT NULL collate utf8_unicode_ci default '',
	`budget` int (11) NOT NULL default 0,
	`payment` int (11) NOT NULL default 0,
	`status` int(11) NOT NULL default 1,
	`FK_entities` int(11) NOT NULL default 0,
	`date` date,
	`FK_enterprise` INT(11) NOT NULL DEFAULT 0,
    `location` int(11) NOT NULL default 0,
    `FK_contact` int(11) NOT NULL default 0,
	`recursive` INT(1) NOT NULL default 1,
	`deleted` INT(1) NOT NULL default 0,
	`comment` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_dropdown_plugin_order_status`;
	CREATE TABLE `glpi_dropdown_plugin_order_status` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`comments` text,
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_dropdown_plugin_order_payment`;
	CREATE TABLE `glpi_dropdown_plugin_order_payment` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
	`comments` text,
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_dropdown_plugin_order_taxes`;
CREATE TABLE `glpi_dropdown_plugin_order_taxes` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
    `value` FLOAT,
	`comments` text,
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_dropdown_plugin_order_taxes`(ID,name, value) VALUES (1,'5,5', 1.055);
INSERT INTO `glpi_dropdown_plugin_order_taxes`(ID,name, value) VALUES (2,'19,6', 1.196);

DROP TABLE IF EXISTS `glpi_plugin_order_detail`;
CREATE TABLE `glpi_plugin_order_detail` (
  `ID` int(11) NOT NULL auto_increment,
  `FK_order` int(11) NOT NULL default 0,
  `FK_device` int(11) NOT NULL default 0,
  `FK_ref` int(11) NOT NULL default 0,
  `price` FLOAT NOT NULL default 0,
  `reductedprice` FLOAT NOT NULL default 0,
  `taxesprice` FLOAT NOT NULL default 0,
  `status` int(1) NOT NULL default 0,
  `date`date NOT NULL default 0,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_order_device`;
CREATE TABLE `glpi_plugin_order_device` (
	`ID` int(11) NOT NULL auto_increment,
	`FK_order` int(11)  NOT NULL default 0,
	`FK_device` int(11) NOT NULL default 0,
	`device_type` int(11) NOT NULL default 0,
	PRIMARY KEY  (`ID`),
	KEY `FK_device` (`FK_device`,`device_type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_order_profiles`;
CREATE TABLE `glpi_plugin_order_profiles` (
	`ID` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`order` char(1) default NULL,
    `reference` char(1) default NULL,
	PRIMARY KEY  (`ID`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_order_config`;
CREATE TABLE `glpi_plugin_order_config` (
	`ID` int(11) NOT NULL auto_increment,
	`status_creation` int(11) NOT NULL,
	`status_delivered` int(11) NOT NULL,
	`status_nodelivered` int(11) NOT NULL,
	PRIMARY KEY  (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `glpi_plugin_order_config`(ID,status_creation, status_delivered, status_nodelivered) VALUES (1,0,0,0);
	
CREATE TABLE IF NOT EXISTS `glpi_plugin_order_references` (
  `ID` int(11) NOT NULL auto_increment,
  `FK_entities` int(11) NOT NULL DEFAULT 0,
  `FK_manufacturer` int(11) NOT NULL DEFAULT 0,
  `FK_model` INT(11) NOT NULL DEFAULT 0,
  `name` varchar(255) character set latin1 NOT NULL,
  `type` int(11) NOT NULL DEFAULT 0,
  `template` int(11) NOT NULL DEFAULT 0,
  `recursive` int(11) NOT NULL DEFAULT 0,
  `deleted` int(11) NOT NULL DEFAULT 0,
  `comments` text character set latin1 NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `glpi_plugin_order_references_manufacturers` (
  `ID` int(11) NOT NULL auto_increment,
  `FK_reference` int(11) NOT NULL DEFAULT 0,
  `FK_enterprise` int(11) NOT NULL DEFAULT 0,
  `price` float NOT NULL DEFAULT 0,
  PRIMARY KEY  (`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'3150','1','1','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'3150','2','2','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'3150','3','3','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'3150','4','4','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'3150','5','5','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'3150','6','6','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'3150','7','7','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'3150','8','8','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'3150','9','9','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'3151','1','1','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'3151','2','2','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'3151','3','3','0');
INSERT INTO `glpi_display` ( `ID` , `type` , `num` , `rank` , `FK_users` )  VALUES (NULL,'3151','4','4','0');