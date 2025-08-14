<?php

/**
 * -------------------------------------------------------------------------
 * Order plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Order.
 *
 * Order is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Order is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Order. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2009-2023 by Order plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/order
 * -------------------------------------------------------------------------
 */



class PluginOrderReferenceFree extends CommonDBTM
{
    public static $rightname         = 'plugin_order_reference';
    public $dohistory                = true;

    public static function getTypeName($nb = 0)
    {
        return __("Reference free", "order");
    }

    public static function install(Migration $migration)
    {
        /** @var \DBmysql $DB */
        global $DB;

        $default_charset = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();
        $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

        $table = getTableForItemType(__CLASS__);
        if (!$DB->tableExists($table)) {
            $migration->displayMessage("Installing $table");

            //Install
            $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_order_referencefrees` (
               `id` int {$default_key_sign} NOT NULL auto_increment,
               `entities_id` int {$default_key_sign} NOT NULL default '0',
               `is_recursive` tinyint NOT NULL default '0',
               `plugin_order_orders_id` int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orders (id)',
               `name` varchar(255) default NULL,
               `manufacturers_id` int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to glpi_manufacturers (id)',
               `manufacturers_reference` varchar(255) NOT NULL DEFAULT '',
               `itemtype` varchar(100) NOT NULL COMMENT 'see .class.php file',
               `templates_id` int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',
               `comment` text,
               `ecotax_price` decimal(20,6) NOT NULL DEFAULT '0.000000',
               `price_taxfree` decimal(20,6) NOT NULL DEFAULT '0.000000',
               `price_discounted` decimal(20,6) NOT NULL DEFAULT '0.000000',
               `discount` decimal(20,6) NOT NULL DEFAULT '0.000000',
               `price_ati` decimal(20,6) NOT NULL DEFAULT '0.000000',
               `states_id` int {$default_key_sign} NOT NULL default 1,
               `delivery_date` date default NULL,
               `plugin_order_ordertaxes_id` int {$default_key_sign} NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_ordertaxes (id)',
               `is_deleted` tinyint NOT NULL default '0',
               `is_active` tinyint NOT NULL default '1',
               `notepad` longtext,
               `date_mod` timestamp NULL default NULL,
               PRIMARY KEY  (`id`),
               KEY `name` (`name`),
               KEY `entities_id` (`entities_id`),
               KEY `manufacturers_id` (`manufacturers_id`),
               KEY `templates_id` (`templates_id`),
               KEY `plugin_order_ordertaxes_id` (`plugin_order_ordertaxes_id`),
               KEY `states_id` (`states_id`),
               KEY `is_active` (`is_active`),
               KEY `is_deleted` (`is_deleted`),
               KEY date_mod (date_mod)
            ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
            $DB->doQuery($query);
        } else {
            // Add ecotax field if it doesn't exist
            if (!$DB->fieldExists($table, 'ecotax_price')) {
                $migration->addField(
                    $table,
                    'ecotax_price',
                    "decimal(20,6) NOT NULL DEFAULT '0.000000'",
                    ['after' => 'comment'],
                );
                $migration->migrationOneTable($table);
            }
        }
    }

    public static function uninstall()
    {
        /** @var \DBmysql $DB */
        global $DB;

        $table  = getTableForItemType(__CLASS__);
        foreach (
            ["glpi_displaypreferences", "glpi_documents_items", "glpi_savedsearches",
                "glpi_logs",
            ] as $t
        ) {
            $query = "DELETE FROM `$t` WHERE `itemtype`='" . __CLASS__ . "'";
            $DB->doQuery($query);
        }

        $DB->doQuery("DROP TABLE IF EXISTS `$table`");
    }

    /**
     * Get the ecotax price for this reference
     *
     * @return float price
     */
    public function getEcotaxPrice()
    {
        return $this->fields['ecotax_price'] ?? 0;
    }
}
