<?php
/*
 * @version $Id: HEADER 2011-03-23 15:41:26 tsmr $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

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
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
// ----------------------------------------------------------------------
// Original Authors of file: 
// NOUH Walid & FONTAN Benjamin & CAILLAUD Xavier & François Legastelois
// Purpose of file: plugin order v1.4.0 - GLPI 0.80
// ----------------------------------------------------------------------
// ---------------------------------------------------------------------- */

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

   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_order']['status'][0];
   }
   
   function canCreate() {
      return plugin_order_haveRight('order', 'w');
   }

   function canView() {
      return plugin_order_haveRight('order', 'r');
   } 
   
   function pre_deleteItem() {
      global $LANG;
      if ($this->getID() <= self::CANCELED ) {
         addMessageAfterRedirect($LANG['plugin_order']['status'][15].": ".$this->fields['name'], 
                                 false, ERROR);
         return false;
      } else {
         return true;
      }
   }
   
   static function install(Migration $migration) {
      global $DB, $LANG;
      
      $table = getTableForItemType(__CLASS__);
      //1.2.0
      $DB->query("DROP TABLE IF EXISTS `glpi_dropdown_plugin_order_status`;");
      
      if (!TableExists($table)) {
         $migration->displayMessage("Installing $table");
         $query ="CREATE TABLE IF NOT EXISTS `glpi_plugin_order_orderstates` (
                  `id` int(11) NOT NULL auto_increment,
                  `name` varchar(255) collate utf8_unicode_ci default NULL,
                  `comment` text collate utf8_unicode_ci,
                  PRIMARY KEY  (`id`),
                  KEY `name` (`name`)
               ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die ($DB->error());
      }
      
      $state = new self();
      foreach (array (1 => $LANG['plugin_order']['status'][9], 
                      2 => $LANG['plugin_order']['status'][7],
                      3 => $LANG['plugin_order']['status'][12],
                      4 => $LANG['plugin_order']['status'][1],
                      5 => $LANG['plugin_order']['status'][2],
                      6 => $LANG['plugin_order']['status'][10]) as $id => $label) {
         if (!countElementsInTable($table, "`id`='$id'")) {
            $state->add(array('id' => $id, 'name' => addslashes_deep($label)));
         }
      }

   }

   static function uninstall() {
      global $DB;
      $DB->query("DROP TABLE IF EXISTS `".getTableForItemType(__CLASS__)."`") or die ($DB->error());
   }
}

?>