ALTER TABLE `glpi_plugin_order_configs` 
	ADD `generate_assets` int(11) NOT NULL default '0',
	ADD `generated_name` varchar(255) collate utf8_unicode_ci default NULL,
	ADD `generated_serial` varchar(255) collate utf8_unicode_ci default NULL,
	ADD `generated_otherserial` varchar(255) collate utf8_unicode_ci default NULL,
	ADD `default_asset_entities_id` int(11) NOT NULL default '0',
	ADD `default_asset_states_id` int(11) NOT NULL default '0',
	ADD `generate_ticket` int(11) NOT NULL default '0',
	ADD `generated_title` varchar(255) collate utf8_unicode_ci default NULL,
	ADD `generated_content` text collate utf8_unicode_ci,
	ADD `default_ticketcategories_id` int(11) NOT NULL default '0';

INSERT INTO `glpi_notificationtemplates` VALUES (NULL, 'Order Reception', 'PluginOrderOrder_Item', '2011-01-25 15:00:00','');

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_order_ordertypes`
-- 

DROP TABLE IF EXISTS `glpi_plugin_order_ordertypes`;
CREATE TABLE `glpi_plugin_order_ordertypes` (
	`id` int(11) NOT NULL auto_increment,
	`name` varchar(255) collate utf8_unicode_ci default NULL,
	`comment` text collate utf8_unicode_ci,
	PRIMARY KEY  (`id`),
	KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

-- --------------------------------------------------------
-- 
-- Structure de la table `glpi_plugin_order_ordertypes`
--

ALTER TABLE `glpi_plugin_order_orders`
	ADD `plugin_order_ordertypes_id` int (11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_ordertypes (id)';

-- --------------------------------------------------------