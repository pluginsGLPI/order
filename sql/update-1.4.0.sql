ALTER TABLE `glpi_plugin_order_profiles`
	DROP `budget` ;
	
DROP TABLE IF EXISTS `glpi_plugin_order_budgets`;

DELETE FROM `glpi_displaypreferences` WHERE `itemtype` = 'PluginOrderBudget';
DELETE FROM `glpi_documents_items` WHERE `itemtype` = 'PluginOrderBudget';
DELETE FROM `glpi_bookmarks` WHERE `itemtype` = 'PluginOrderBudget';
DELETE FROM `glpi_logs` WHERE `itemtype` = 'PluginOrderBudget';

ALTER TABLE  `glpi_plugin_order_orders_items` 
	ADD  `plugin_order_ordertaxes_id` FLOAT NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_ordertaxes (id)';