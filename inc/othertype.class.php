<?php

/**
 * -------------------------------------------------------------------------
 * Order plugin for GLPI
 * Copyright (C) 2009-2022 by the Order Development Team.
 *
 * https://github.com/pluginsGLPI/order
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Order.
 *
 * Order is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Order is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Order. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginOrderOtherType extends CommonDropdown {

   public static $rightname = 'plugin_order_order';


   public static function getTypeName($nb = 0) {
      return __("Other type of item", "order");
   }


   public static function install(Migration $migration) {
      global $DB;

      //Only avaiable since 1.2.0
      $table = self::getTable();
      if (!$DB->tableExists($table)) {
         $migration->displayMessage("Installing $table");

         $default_charset = DBConnection::getDefaultCharset();
         $default_collation = DBConnection::getDefaultCollation();

         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                  `id` int unsigned NOT NULL auto_increment,
                  `name` varchar(255) default NULL,
                  `comment` text,
                  PRIMARY KEY  (`ID`),
                  KEY `name` (`name`)
               ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
         $DB->query($query) or die ($DB->error());
      }
   }


   public static function uninstall() {
      global $DB;

      //Current table name
      $DB->query("DROP TABLE IF EXISTS `".self::getTable()."`") or die ($DB->error());
   }


}
