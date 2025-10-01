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



class PluginOrderOther extends CommonDBTM
{
    public static $rightname = 'plugin_order_order';


    public static function getTypeName($nb = 0)
    {
        return __s("Other kind of items");
    }


    public static function install(Migration $migration)
    {
        /** @var DBmysql $DB */
        global $DB;

        $default_charset = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();
        $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

        //Only avaiable since 1.2.0
        $table = self::getTable();
        if (!$DB->tableExists($table)) {
            $migration->displayMessage("Installing $table");

            $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_order_others` (
                  `id` int {$default_key_sign} NOT NULL auto_increment,
                  `entities_id` int {$default_key_sign} NOT NULL default '0',
                  `name` varchar(255) default NULL,
                  `plugin_order_othertypes_id` int {$default_key_sign} NOT NULL default '0',
                  PRIMARY KEY  (`ID`),
                  KEY `name` (`name`),
                  KEY `entities_id` (`entities_id`),
                  KEY `plugin_order_othertypes_id` (`plugin_order_othertypes_id`)
               ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
            $DB->doQuery($query);
        } else {
            $migration->displayMessage("Rename 'othertypes_id' to 'plugin_order_othertypes_id'");
            $migration->changeField($table, "othertypes_id", "plugin_order_othertypes_id", "int {$default_key_sign} NOT NULL default '0'");
        }
    }


    public static function uninstall()
    {
        /** @var DBmysql $DB */
        global $DB;

        //Current table name
        $DB->doQuery("DROP TABLE IF EXISTS `" . self::getTable() . "`");
    }
}
