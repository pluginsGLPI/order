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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginOrderBillState extends CommonDropdown {
   const NOTPAID = 0;
   const PAID    = 1;

   public static $rightname = 'plugin_order_bill';


   public static function getTypeName($nb = 0) {
      return __("Bill status", "order");
   }


   public function pre_deleteItem() {
      if ($this->getID() <= self::PAID) {
         Session::addMessageAfterRedirect(__("You cannot remove this status", "order").
                                          ": ".$this->fields['name'],
                                          false, ERROR);
         return false;
      } else {
         return true;
      }
   }


   public static function getStates() {
      return [self::NOTPAID => __("Being paid", "order"),
              self::PAID    => __("Paid", "order")];
   }


   public static function getState($states_id) {
      $states = self::getStates();
      if (isset($states[$states_id])) {
         return $states[$states_id];
      } else {
         return '';
      }
   }


   public static function install(Migration $migration) {
      global $DB;

      $default_charset = DBConnection::getDefaultCharset();
      $default_collation = DBConnection::getDefaultCollation();
      $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

      $table = self::getTable();
      if (!$DB->tableExists($table)) {
         $migration->displayMessage("Installing $table");

         $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_order_billstates` (
                    `id` int {$default_key_sign} NOT NULL AUTO_INCREMENT,
                    `name` varchar(255) DEFAULT NULL,
                    `comment` text,
                    PRIMARY KEY (`id`),
                    KEY `name` (`name`)
                  ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
         $DB->query($query) or die ($DB->error());
      }
      if (countElementsInTable($table) < 2) {
         $state = new self();
         foreach ([self::PAID     => __("Paid", "order"),
                   self::NOTPAID  => __("Not paid", "order")] as $id => $label) {
            $state->add(['id'   => $id,
                         'name' => Toolbox::addslashes_deep($label)]);
         }
      }
   }


   public static function uninstall() {
      global $DB;

      $DB->query("DROP TABLE IF EXISTS `".self::getTable()."`") or die ($DB->error());
   }


}
