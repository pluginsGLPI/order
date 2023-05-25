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

class PluginOrderDocumentCategory extends CommonDBTM {


   static function getTypeName($nb = 0) {
      return __("Document category", "order");
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      $config = PluginOrderConfig::getConfig();

      if ($item->getType() == "DocumentCategory" && $config->canRenameDocuments()) {
         return __("Orders", "order");
      }

      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      $config = PluginOrderConfig::getConfig();

      if ($item->getType() == "DocumentCategory" && $config->canRenameDocuments()) {
         self::showForDocumentCategory($item);
      }
      return true;
   }


   static function purgeItem($item) {
      $temp = new self();
      $temp->deleteByCriteria([
         'documentcategories_id' => $item->getField("id")
      ]);
   }


   static function showForDocumentCategory($item) {
      $documentCategory = new self();
      if (!$documentCategory->getFromDBByCrit(['documentcategories_id' => $item->fields['id']])) {
         $documentCategory->getEmpty();
      }

      echo "<form name='form' method='post' action='".
      Toolbox::getItemTypeFormURL($documentCategory->getType())."'>";

      echo "<div align='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2'>".__('Document category prefix', 'order')."</th></tr>";

      echo "<tr class='tab_bg_1'>";
      // Dropdown group
      echo "<td>";
      echo __('Document category prefix', 'order');
      echo "</td>";
      echo "<td>";
      echo "<input type='text' name='documentcategories_prefix' value='".$documentCategory->fields['documentcategories_prefix']."'>";
      echo "</td>";
      echo "</tr>";

      echo "<tr>";
      echo "<td class='tab_bg_2 center' colspan='6'>";
      echo "<input type='submit' name='update' class='submit' value='"._sx('button', 'Save')."' >";
      echo Html::hidden('documentcategories_id', ['value' => $item->getID()]);
      echo "</td>";
      echo "</tr>";
      echo "</table></div>";
      Html::closeForm();
   }


   //------------------------------------------------------------
   //--------------------Install / uninstall --------------------
   //------------------------------------------------------------

   static function install(Migration $migration) {
      global $DB;

      $default_charset = DBConnection::getDefaultCharset();
      $default_collation = DBConnection::getDefaultCollation();
      $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

      $table = self::getTable();
      //Installation
      if (!$DB->tableExists($table)
          && !$DB->tableExists("glpi_plugin_order_documentcategories")) {
         $migration->displayMessage("Installing $table");

         $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_order_documentcategories` (
                     `id` int {$default_key_sign} NOT NULL auto_increment,
                     `documentcategories_id` int {$default_key_sign} NOT NULL default '0',
                     `documentcategories_prefix` varchar(255) default NULL,
                     PRIMARY KEY  (`id`),
                     KEY `documentcategories_id` (`documentcategories_id`),
                     UNIQUE KEY `unicity` (`documentcategories_id`, `documentcategories_prefix`)
                  ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
         $DB->query($query) or die ($DB->error());
      }
   }


   static function uninstall() {
      global $DB;
      //Current table name
      $DB->query("DROP TABLE IF EXISTS  `".self::getTable()."`") or die ($DB->error());
   }


}
