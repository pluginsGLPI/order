<?php
/*
 LICENSE

 This file is part of the order plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Order. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   order
 @author    the order plugin team
 @copyright Copyright (c) 2010-2015 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginOrderBillOrder extends CommonDropdown
{
   public static $rightname  = 'plugin_order_billorder';
   

   public static function getTypeName($nb = 0) {
      return __("Bill", "order");
   }

   public function post_getEmpty() {
      $this->fields['value'] = 0;
   }

   

  

  

   

  

   public static function install(Migration $migration) {
      global $DB;

      $table = "glpi_plugin_order_billsorder";

      if (!TableExists($table)) {
         $migration->displayMessage("Installing $table");
         $query ="CREATE TABLE IF NOT EXISTS `glpi_plugin_order_billsorder` (
             `id` int(11) NOT NULL AUTO_INCREMENT,
                    `glpi_plugin_order_orders_id` int(11) NOT NULL DEFAULT '0',
                    `glpi_plugin_order_bills_id` int(11) NOT NULL DEFAULT '0',
                    PRIMARY KEY (`id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;";
         $DB->query($query) or die ($DB->error());
      } 
      
   }

   public static function uninstall() {
      global $DB;

      $table = "glpi_plugin_order_billsorder";
      
      $DB->query("DROP TABLE IF EXISTS`" . $table . "`") or die ($DB->error());
   }


}
