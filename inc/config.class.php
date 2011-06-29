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

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginOrderConfig extends CommonDBTM {

   
   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_order']['config'][0];
   }
   
   function showConfigForm(){
      global $LANG;
      
      $this->getFromDB(1);
      echo "<div class='center'>";
      echo "<form name='form' method='post' action='".$this->getFormURL()."'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2'>".$LANG['plugin_order']['config'][0]."</th></tr>";
      
      echo "<input type='hidden' name='id' value='1'>";
      echo "<tr class='tab_bg_1' align='center'><td>".$LANG['plugin_order']['config'][1].
            "</td><td>";
      Dropdown::show('PluginOrderOrderTaxe', array('name' => "default_taxes",
                                                   'value' => $this->fields["default_taxes"],
                                                   'display_emptychoice' => true,
                                                   'emptylabel' => $LANG['plugin_order']['config'][20]));
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1' align='center'>
                  <td>".$LANG['plugin_order']['config'][2]."</td><td>";
                  Dropdown::showYesNo("use_validation",$this->fields["use_validation"]); 
      echo "</td>";
      echo "</tr>";

      // Automatic actions
      echo "<tr class='tab_bg_1' align='center'>
               <th colspan='2'>".$LANG['plugin_order']['config'][3]."</th>
            </tr>";
      
      // ASSETS
      echo "<tr class='tab_bg_1' align='center'>
               <th colspan='2'>".$LANG['common'][1]."</th>
            </tr>";

      echo "<tr class='tab_bg_1' align='center'>
                  <td>".$LANG['plugin_order']['config'][4]."</td><td>";
                  Dropdown::showYesNo("generate_assets", $this->fields["generate_assets"]);
      echo "</td></tr>";
      
      if ($this->fields["generate_assets"]) {
         echo "<tr class='tab_bg_1' align='center'>
               <td>".$LANG['plugin_order']['config'][5]."</td><td>";
                  autocompletionTextField($this, "generated_name");
         echo "</td></tr>";
   
         echo "<tr class='tab_bg_1' align='center'>
                  <td>".$LANG['plugin_order']['config'][6]."</td><td>";
                  autocompletionTextField($this, "generated_serial");
         echo "</td></tr>";
         
         echo "<tr class='tab_bg_1' align='center'>
                  <td>".$LANG['plugin_order']['config'][7]."</td><td>";
                  autocompletionTextField($this, "generated_otherserial");
         echo "</td></tr>";
         
         echo "<tr class='tab_bg_1' align='center'>";
         echo "<td>".$LANG['plugin_order']['config'][8]."</td><td>";
         if (isMultiEntitiesMode()) {
            Dropdown::show('Entity', array(  'name'   => "default_asset_entities_id",
                                             'value'  => $this->fields["default_asset_entities_id"],
                                             'entity' => $_SESSION["glpiactiveentities"]));
         } else {
            echo $_SESSION["glpiactive_entity"];
         }
         echo "</td></tr>";
   
         echo "<tr class='tab_bg_1' align='center'>
                  <td>".$LANG['plugin_order']['config'][12]."</td><td>";
                  Dropdown::show('State', 
                                 array('name'   => 'default_asset_states_id',
                                       'value'  => $this->fields["default_asset_states_id"],
                                       'entity' => $_SESSION["glpiactiveentities"]));
         echo "</td></tr>";

      }
      
      // TICKETS
      echo "<tr class='tab_bg_1' align='center'>
               <th colspan='2'>".$LANG['job'][38]."</th>
            </tr>";
   
      echo "<tr class='tab_bg_1' align='center'>
            <td>".$LANG['plugin_order']['config'][4]."</td><td>";
                  Dropdown::showYesNo("generate_ticket", $this->fields["generate_ticket"]);
      echo "</td></tr>";
      
      if ($this->fields["generate_ticket"]) {
         echo "<tr class='tab_bg_1' align='center'>
                  <td>".$LANG['plugin_order']['config'][10]."</td><td>";
                  autocompletionTextField($this, "generated_title");
         echo "</td></tr>";
         
         echo "<tr class='tab_bg_1' align='center'>
                  <td>".$LANG['plugin_order']['config'][11]."</td><td>";
         echo "<textarea cols='60' rows='4' name='generated_content'>" .
                     $this->fields["generated_content"] . "</textarea>";
         echo "</td></tr>";
         
         echo "<tr class='tab_bg_1' align='center'>
                  <td>".$LANG['plugin_order']['config'][9]."</td><td>";
                  Dropdown::show('TicketCategory', 
                                 array('name'   => 'default_ticketcategories_id',
                                       'value'  => $this->fields["default_ticketcategories_id"],
                                       'entity' => $_SESSION["glpiactiveentities"]));
         echo "</td></tr>";

      }

      /* Workflow */
      echo "<tr class='tab_bg_1' align='center'>
               <th colspan='2'>".$LANG['plugin_order']['config'][13]."</th>
            </tr>";

      echo "<tr class='tab_bg_1' align='center'>
            <td>".$LANG['plugin_order']['config'][14]."</td><td>";
            Dropdown::show('PluginOrderOrderState', 
                           array('name'   => 'order_status_draft',
                                 'value'  => $this->fields["order_status_draft"]));
      echo "</td></tr>";
      
      echo "<tr class='tab_bg_1' align='center'>
            <td>".$LANG['plugin_order']['config'][15]."</td><td>";
            Dropdown::show('PluginOrderOrderState', 
                           array('name'   => 'order_status_waiting_approval',
                                 'value'  => $this->fields["order_status_waiting_approval"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1' align='center'>
            <td>".$LANG['plugin_order']['config'][16]."</td><td>";
            Dropdown::show('PluginOrderOrderState', 
                           array('name'   => 'order_status_approved',
                                 'value'  => $this->fields["order_status_approved"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1' align='center'>
            <td>".$LANG['plugin_order']['config'][17]."</td><td>";
            Dropdown::show('PluginOrderOrderState', 
                           array('name'   => 'order_status_partially_delivred',
                                 'value'  => $this->fields["order_status_partially_delivred"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1' align='center'>
            <td>".$LANG['plugin_order']['config'][18]."</td><td>";
            Dropdown::show('PluginOrderOrderState', 
                           array('name'   => 'order_status_completly_delivered',
                                 'value'  => $this->fields["order_status_completly_delivered"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1' align='center'>
            <td>".$LANG['plugin_order']['config'][19]."</td><td>";
            Dropdown::show('PluginOrderOrderState', 
                           array('name'   => 'order_status_canceled',
                                 'value'  => $this->fields["order_status_canceled"]));
      echo "</td></tr>";


      echo "<tr class='tab_bg_1' align='center'>"; 
      echo "<td colspan='2' align='center'>"; 
      echo "<input type='submit' name='update' value=\"".$LANG['buttons'][7]."\" class='submit' >"; 
      echo"</td>";
      echo "</tr>";
      
      echo "</table></form></div>";
   }
   
   function getConfig(){
   
      $this->getFromDB(1);
      return $this->fields; 
   }
   
   function getDefaultTaxes() {
   
      $config = $this->getConfig();
      return $config["default_taxes"];
   }
   
   static function install(Migration $migration) {
      global $DB;
      $table = getTableForItemType(__CLASS__);


      $config = new self();

      //This class is available since version 1.3.0
      if (!TableExists($table) && !TableExists("glpi_plugin_order_config")) {
            $migration->displayMessage("Installing $table");

            //Install
            $query = "CREATE TABLE `glpi_plugin_order_configs` (
                     `id` int(11) NOT NULL auto_increment,
                     `use_validation` int(11) NOT NULL default 0,
                     `default_taxes` int(11) NOT NULL default 0,
                     `generate_assets` int(11) NOT NULL default 0,
                     `generated_name` varchar(255) collate utf8_unicode_ci default NULL,
                     `generated_serial` varchar(255) collate utf8_unicode_ci default NULL,
                     `generated_otherserial` varchar(255) collate utf8_unicode_ci default NULL,
                     `default_asset_entities_id` int(11) NOT NULL default '0',
                     `default_asset_states_id` int(11) NOT NULL default '0',  
                     `generate_ticket` int(11) NOT NULL default '0',
                     `generated_title` varchar(255) collate utf8_unicode_ci default NULL,
                     `generated_content` text collate utf8_unicode_ci,
                     `default_ticketcategories_id` int(11) NOT NULL default '0',
                     `order_status_draft` int(11) NOT NULL default '0',
                     `order_status_waiting_approval` int(11) NOT NULL default '0',
                     `order_status_approved` int(11) NOT NULL default '0',
                     `order_status_partially_delivred` int(11) NOT NULL default '0',
                     `order_status_completly_delivered` int(11) NOT NULL default '0',
                     `order_status_canceled` int(11) NOT NULL default '0',
                     PRIMARY KEY  (`id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;"; 
               $DB->query($query) or die ($DB->error());
               
               $tobefilled = "TOBEFILLED";
               $tmp = array('id' => 1, 'use_validation' => 0, 'default_taxes' => 0, 
                            'generate_assets' => 0, 'generated_name' => $tobefilled, 
                            'generated_serial' => $tobefilled, 'generated_otherserial' => $tobefilled,
                            'default_asset_entities_id' => 0, 'default_asset_states_id' => 0,
                            'generate_ticket' => 0, 'generated_title' => $tobefilled, 
                            'generated_content' => $tobefilled, 'default_ticketcategories_id' => 0);
               $config->add($tmp);
      } else {
            //Upgrade
            $migration->displayMessage("Upgrading $table");

            //1.2.0
            $migration->renameTable("glpi_plugin_order_config", $table);

            if (!countElementsInTable("glpi_plugin_order_configs")) {
               $query = "INSERT INTO `glpi_plugin_order_configs`(id,use_validation,default_taxes) VALUES (1,0,0);";
               $DB->query($query) or die($DB->error());
            }
  
            $migration->changeField($table, "ID", "id", "int(11) NOT NULL auto_increment");
            
            //1.3.0
            $migration->addField($table, "generate_assets", "int(11) NOT NULL default '0'");
            $migration->addField($table, "generated_name", 
                                 "varchar(255) collate utf8_unicode_ci default NULL");
            $migration->addField($table, "generated_serial", 
                                 "varchar(255) collate utf8_unicode_ci default NULL");
            $migration->addField($table, "generated_otherserial", 
                                 "varchar(255) collate utf8_unicode_ci default NULL");
            $migration->addField($table, "default_asset_entities_id", 
                                 "int(11) NOT NULL default '0'");
            $migration->addField($table, "default_asset_states_id", 
                                 "int(11) NOT NULL default '0'");
            $migration->addField($table, "generate_ticket", 
                                 "int(11) NOT NULL default '0'");
            $migration->addField($table, "generated_title", 
                                 "varchar(255) collate utf8_unicode_ci default NULL");
            $migration->addField($table, "generated_content", 
                                 "text collate utf8_unicode_ci");
            $migration->addField($table, "default_ticketcategories_id", 
                                 "int(11) NOT NULL default '0'");
            $migration->migrationOneTable($table);
            
      }

      $migration->displayMessage("Add default order state workflow");
      $new_states = array('order_status_draft'               => 1, 
                          'order_status_waiting_approval'    => 2, 
                          'order_status_approved'            => 3, 
                          'order_status_partially_delivred'  => 4, 
                          'order_status_completly_delivered' => 5, 
                         'order_status_canceled'            => 6);
                           
      foreach ($new_states as $field => $value) {
         $migration->addField($table, $field, "int(11) NOT NULL default '0'");
      }
      $migration->migrationOneTable($table);
      
      $new_states['id'] = 1;
      $config->update($new_states);

   }
   
   static function uninstall() {
      global $DB;
      
      //Old table
      $DB->query("DROP TABLE IF EXISTS `glpi_plugin_order_config`");

      //New table
      $DB->query("DROP TABLE IF EXISTS `".getTableForItemType(__CLASS__)."`");
   }
}

?>