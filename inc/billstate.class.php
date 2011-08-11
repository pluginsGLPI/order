<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 --------------------------------------------------------------------------
// ----------------------------------------------------------------------
// Original Authors of file: 
// NOUH Walid & FONTAN Benjamin & CAILLAUD Xavier & François Legastelois
// Purpose of file: 
// ----------------------------------------------------------------------
// ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginOrderBillState extends CommonDropdown {

   const NOTPAID = 0;
   const PAID    = 1;

   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_order']['bill'][2];
   }

   function canCreate() {
      return plugin_order_haveRight('bill', 'w');
   }

   function canView() {
      return plugin_order_haveRight('bill', 'r');
   } 
   
   static function getStates() {
      global $LANG;
      return array(self::NOTPAID => $LANG['plugin_order']['bill'][7], 
                   self::PAID    => $LANG['plugin_order']['bill'][6]);
   }
   
   static function getState($states_id) {
      $states = self::getStates();
      if (isset($states[$states_id])) {
         return $states[$states_id];
      } else {
         return '';
      }
   }
   
   static function install(Migration $migration) {
      global $DB;
      
      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $migration->displayMessage("Installing $table");
         $query ="CREATE TABLE IF NOT EXISTS `glpi_plugin_order_billstates` (
                 `id` int(11) NOT NULL AUTO_INCREMENT,
                 `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                 `comment` text COLLATE utf8_unicode_ci,
                 PRIMARY KEY (`id`),
                 KEY `name` (`name`)
               ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die ($DB->error());
      }
   }
   
   static function uninstall() {
      global $DB;
      $DB->query("DROP TABLE IF EXISTS `".getTableForItemType(__CLASS__)."`") or die ($DB->error());
   }
}
?>