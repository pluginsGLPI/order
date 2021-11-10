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

// Class for a Dropdown
class PluginOrderOrderState extends CommonDropdown {
   const DRAFT                = 1;
   const WAITING_FOR_APPROVAL = 2;
   const VALIDATED            = 3;
   const BEING_DELIVERING     = 4;
   const DELIVERED            = 5;
   const CANCELED             = 6;
   const PAID                 = 7;

   public static $rightname   = 'plugin_order_order';


   public static function getTypeName($nb = 0) {
      return __("Order status", "order");
   }


   public function pre_deleteItem() {
      if ($this->getID() <= self::CANCELED) {
         Session::addMessageAfterRedirect(__("You cannot remove this status", "order").": "
                                          .$this->fields['name'],
                                          false, ERROR);
         return false;
      } else {
         return true;
      }
   }


   public static function install(Migration $migration) {
      global $DB;

      $table = self::getTable();

      //1.2.0
      $DB->query("DROP TABLE IF EXISTS `glpi_dropdown_plugin_order_status`;");

      if (!$DB->tableExists($table)) {
         $migration->displayMessage("Installing $table");

         $default_charset = DBConnection::getDefaultCharset();
         $default_collation = DBConnection::getDefaultCollation();

         $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_order_orderstates` (
                  `id` int unsigned NOT NULL auto_increment,
                  `name` varchar(255) default NULL,
                  `comment` text,
                  PRIMARY KEY  (`id`),
                  KEY `name` (`name`)
               ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
         $DB->query($query) or die ($DB->error());
      }

      $state = new self();
      foreach ([
         1 => __("Draft", "order"),
         2 => __("Waiting for approval", "order"),
         3 => __("Validated", "order"),
         4 => __("Being delivered", "order"),
         5 => __("Delivered", "order"),
         6 => __("Canceled", "order"),
         7 => __("Paid", "order")
      ] as $id => $label) {
         if (!countElementsInTable($table, ['id' => $id])) {
            $state->add([
               'id'   => $id,
               'name' => Toolbox::addslashes_deep($label)
            ]);
         }
      }
   }


   public static function uninstall() {
      global $DB;
      $DB->query("DROP TABLE IF EXISTS `".self::getTable()."`") or die ($DB->error());
   }


}
