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