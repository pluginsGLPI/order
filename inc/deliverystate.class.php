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
 * @copyright Copyright (C) 2009-2022 by Order plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/order
 * -------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginOrderDeliveryState extends CommonDropdown {


   public static function getTypeName($nb = 0) {
      return __("Delivery status", "order");
   }


   public static function install(Migration $migration) {
      global $DB;

      $default_charset = DBConnection::getDefaultCharset();
      $default_collation = DBConnection::getDefaultCollation();
      $default_key_sign = method_exists('DBConnection', 'getDefaultPrimaryKeySignOption') ? DBConnection::getDefaultPrimaryKeySignOption() : '';

      $table = self::getTable();
      if (!$DB->tableExists($table)
          && !$DB->tableExists("glpi_dropdown_plugin_order_deliverystate")) {
         $migration->displayMessage("Installing $table");

         //Install
         $query = "CREATE TABLE `glpi_plugin_order_deliverystates` (
                     `id` int {$default_key_sign} NOT NULL auto_increment,
                     `name` varchar(255) default NULL,
                     `comment` text,
                     PRIMARY KEY  (`id`),
                     KEY `name` (`name`)
                  ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
         $DB->query($query) or die($DB->error());
      } else {
         $migration->displayMessage("Upgrading $table");

         //Upgrade 1.2.0
         $migration->renameTable("glpi_dropdown_plugin_order_deliverystate", $table);
         $migration->changeField($table, "ID", "id", "int {$default_key_sign} NOT NULL auto_increment");
         $migration->changeField($table, "name", "name", "varchar(255) default NULL");
         $migration->changeField($table, "comments", "comment", "text");
         $migration->migrationOneTable($table);
      }
   }


   public static function uninstall() {
      global $DB;

      //Old table
      $DB->query("DROP TABLE IF EXISTS `glpi_dropdown_plugin_order_deliverystate`") or die ($DB->error());

      //New table
      $DB->query("DROP TABLE IF EXISTS `".self::getTable()."`") or die ($DB->error());
   }


}
