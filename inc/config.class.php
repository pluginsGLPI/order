<?php
/*
 * @version $Id: HEADER 2011-03-23 15:41:26 tsmr $
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
 @copyright Copyright (c) 2010-2011 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginOrderConfig extends CommonDBTM {

   
   static function getConfig($update = false) {
      static $config = null;

      if (is_null($config)) {
         $config = new self();
      }
      if ($update) {
         $config->getFromDB(1);
      }
      return $config;
   }

   function __construct() {
      if (TableExists($this->getTable())) {
         $this->getFromDB(1);
      }
   }
   
   static function getTypeName($nb=0) {
      return __("Plugin configuration", "order");
   }
   
   function showForm(){
      
      
      $this->getFromDB(1);
      echo "<div class='center'>";
      echo "<form name='form' method='post' action='".$this->getFormURL()."'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2'>".__("Plugin configuration", "order")."</th></tr>";
      
      echo "<input type='hidden' name='id' value='1'>";
      echo "<tr class='tab_bg_1' align='center'><td>".__("Default VAT", "order").
            "</td><td>";
      PluginOrderOrderTax::Dropdown(array('name'                => "default_taxes",
                                           'value'               => $this->fields["default_taxes"],
                                           'display_emptychoice' => true,
                                           'emptylabel'          => __("No VAT", "order")));
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1' align='center'>
                  <td>".__("Use validation process", "order")."</td><td>";
                  Dropdown::showYesNo("use_validation", $this->fields["use_validation"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>
                  <td>".__("Order generation in ODT", "order")."</td><td>";
                  Dropdown::showYesNo("generate_order_pdf", $this->fields["generate_order_pdf"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>
                  <td>".__("Activate suppliers quality satisfaction", "order")."</td><td>";
                  Dropdown::showYesNo("use_supplier_satisfaction",
                                      $this->fields["use_supplier_satisfaction"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>
                  <td>".__("Display order's suppliers informations", "order")."</td><td>";
                  Dropdown::showYesNo("use_supplier_informations",
                                      $this->fields["use_supplier_informations"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>
                  <td>".__("Color to be displayed when order due date is overtaken", "order")."</td><td>";
      echo "<input type='text' name='shoudbedelivered_color' " .
              "value='".$this->fields['shoudbedelivered_color']."'>";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>
                  <td>".__("Copy order documents when a new item is created", "order")."</td><td>";
                  Dropdown::showYesNo("copy_documents",
                                      $this->fields["copy_documents"]);
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1' align='center'>
                  <td>".__("Default heading when adding a document to an order", "order")."</td><td>";
                  DocumentCategory::Dropdown(array('value' => $this->fields["documentcategories_id"]));
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1' align='center'>
                  <td>".__("Author group", "order").' ('.__("Default values").")</td><td>";
      Group::Dropdown(array('value' => $this->fields["groups_id_author"],
                            'name'  => 'groups_id_author'));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>
                  <td>".__("Recipient group", "order").' ('.__("Default values").")</td><td>";
      Group::Dropdown(array('value' => $this->fields["groups_id_recipient"],
                            'name'  => 'groups_id_recipient'));
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>
                  <td>".__("Recipient").' ('.__("Default values").")</td><td>";
      User::Dropdown(array('name'   => 'users_id_recipient',
                            'value'  => $this->fields["users_id_recipient"],
                            'right'  => 'all',
                            'entity' => 0));
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1' align='center'>
                  <td>".__("Hide inactive budgets", 'order')."</td><td>";
      Dropdown::showYesNo("hide_inactive_budgets",
                           $this->fields["hide_inactive_budgets"]);
      echo "</td>";
      echo "</tr>";

      // Automatic actions
      echo "<tr class='tab_bg_1' align='center'>
               <th colspan='2'>".__("Automatic actions when delivery", "order")."</th>
            </tr>";
      
      // ASSETS
      echo "<tr class='tab_bg_1' align='center'>
               <th colspan='2'>".__('Item')."</th>
            </tr>";

      echo "<tr class='tab_bg_1' align='center'>
                  <td>".__("Enable automatic generation", "order")."</td><td>";
                  Dropdown::showYesNo("generate_assets", $this->canGenerateAsset());
      echo "</td></tr>";
      
      echo "<tr class='tab_bg_1' align='center'>
                  <td>".__("Default state", "order")."</td><td>";
      State::Dropdown(array('name' => 'default_asset_states_id',
          'value' => $this->fields["default_asset_states_id"],
          'entity' => $_SESSION["glpiactiveentities"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1' align='center'>
                  <td>".__("Add order location to item", "order")."</td><td>";
      Dropdown::showYesNo("add_location", $this->canAddLocation());
      echo "</td></tr>";

      echo "<tr class='tab_bg_1' align='center'>
                  <td>".__("Add billing details to item", "order")."</td><td>";
      Dropdown::showYesNo("add_bill_details", $this->canAddBillDetails());
      echo "</td></tr>";

      if ($this->canGenerateAsset()) {
         echo "<tr class='tab_bg_1' align='center'>
               <td>".__("Default name", "order")."</td><td>";
                  Html::autocompletionTextField($this, "generated_name");
         echo "</td></tr>";
   
         echo "<tr class='tab_bg_1' align='center'>
                  <td>".__("Default serial number", "order")."</td><td>";
                  Html::autocompletionTextField($this, "generated_serial");
         echo "</td></tr>";
         
         echo "<tr class='tab_bg_1' align='center'>
                  <td>".__("Default inventory number", "order")."</td><td>";
                  Html::autocompletionTextField($this, "generated_otherserial");
         echo "</td></tr>";



         // TICKETS
         echo "<tr class='tab_bg_1' align='center'>
                  <th colspan='2'>".__("Ticket")."</th>
               </tr>";
         echo "<tr class='tab_bg_1' align='center'>
                  <td>".TicketTemplate::getTypeName(1)."</td><td>";
         Dropdown::show('TicketTemplate', array('name' => 'tickettemplates_id_delivery', 
                                                'value' => $this->fields['tickettemplates_id_delivery']));
         echo "</td></tr>";
      }

      /* Workflow */
      echo "<tr class='tab_bg_1' align='center'>
               <th colspan='2'>".__("Order lifecycle", "order")."</th>
            </tr>";

      echo "<tr class='tab_bg_1' align='center'>
            <td>".__("State before validation", "order")."</td><td>";
            PluginOrderOrderState::Dropdown(
                           array('name'   => 'order_status_draft',
                                 'value'  => $this->fields["order_status_draft"]));
      echo "</td></tr>";
      
      echo "<tr class='tab_bg_1' align='center'>
            <td>".__("Waiting for validation state", "order")."</td><td>";
            PluginOrderOrderState::Dropdown(
                           array('name'   => 'order_status_waiting_approval',
                                 'value'  => $this->fields["order_status_waiting_approval"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1' align='center'>
            <td>".__("Validated order state", "order")."</td><td>";
            PluginOrderOrderState::Dropdown(
                           array('name'   => 'order_status_approved',
                                 'value'  => $this->fields["order_status_approved"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1' align='center'>
            <td>".__("Order being delivered state", "order")."</td><td>";
            PluginOrderOrderState::Dropdown(
                           array('name'   => 'order_status_partially_delivred',
                                 'value'  => $this->fields["order_status_partially_delivred"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1' align='center'>
            <td>".__("Order delivered state", "order")."</td><td>";
            PluginOrderOrderState::Dropdown(
                           array('name'   => 'order_status_completly_delivered',
                                 'value'  => $this->fields["order_status_completly_delivered"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1' align='center'>
            <td>".__("Order paied state", "order")."</td><td>";
            PluginOrderOrderState::Dropdown(
                           array('name'   => 'order_status_paid',
                                 'value'  => $this->fields["order_status_paid"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1' align='center'>
            <td>".__("Canceled order state", "order")."</td><td>";
            PluginOrderOrderState::Dropdown(
                           array('name'   => 'order_status_canceled',
                                 'value'  => $this->fields["order_status_canceled"]));
      echo "</td></tr>";


      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td colspan='2' align='center'>";
      echo "<input type='submit' name='update' value=\""._sx("button", "Post")."\" class='submit' >";
      echo"</td>";
      echo "</tr>";
      
      echo "</table>";
      Html::closeForm();
      echo "</div>";
   }
   
   //----------------- Getters and setters -------------------//

   function useValidation() {
      return $this->fields['use_validation'];
   }
   
   function getDraftState() {
      return $this->fields['order_status_draft'];
      
   }
   
   function getWaitingForApprovalState() {
      return $this->fields['order_status_waiting_approval'];
      
   }

   function getApprovedState() {
      return $this->fields['order_status_approved'];
      
   }

   function getPartiallyDeliveredState() {
      return $this->fields['order_status_partially_delivred'];
      
   }

   function getDeliveredState() {
      return $this->fields['order_status_completly_delivered'];
      
   }

   function getCanceledState() {
      return $this->fields['order_status_canceled'];
      
   }

   function getPaidState() {
      return $this->fields['order_status_paid'];
      
   }

   function getDefaultTaxes() {
      return $this->fields['default_taxes'];
   }
   
   function canGenerateAsset() {
      return $this->fields['generate_assets'];
   }

   function canGenerateTicket() {
      return ($this->fields['tickettemplates_id_delivery'] > 0);
   }
   
   function canAddLocation() {
      return $this->fields['add_location'];
   }
   
   function canAddBillDetails() {
      return $this->fields['add_bill_details'];
   }

   function getGeneratedAssetName() {
      return $this->fields['generated_name'];
   }

   function getGeneratedAssetSerial() {
      return $this->fields['generated_serial'];
   }

   function getGeneratedAssetState() {
      return $this->fields['default_asset_states_id'];
   }

   function getGeneratedAssetOtherserial() {
      return $this->fields['generated_otherserial'];
   }

   function canUseSupplierSatisfaction() {
      return $this->fields['use_supplier_satisfaction'];
   }

   function canUseSupplierInformations() {
      return $this->fields['use_supplier_informations'];
   }

   function canGenerateOrderPDF() {
      return $this->fields['generate_order_pdf'];
   }

   function canCopyDocuments() {
      return $this->fields['copy_documents'];
   }
   
   function getShouldBeDevileredColor() {
      return $this->fields['shoudbedelivered_color'];
   }

   function getDefaultDocumentCategory() {
      return $this->fields['documentcategories_id'];
   }

   function getDefaultAuthorGroup() {
      return $this->fields['groups_id_author'];
   }

   function getDefaultRecipientGroup() {
      return $this->fields['groups_id_recipient'];
   }
    
   function getDefaultRecipient() {
      return $this->fields['users_id_recipient'];
   }
   
   function canHideInactiveBudgets() {
      return $this->fields['hide_inactive_budgets'];
   }

   //----------------- Install & uninstall -------------------//

   static function install(Migration $migration) {
      global $DB;


      $table = getTableForItemType(__CLASS__);
      $config = new self();

      //This class is available since version 1.3.0
      if (!TableExists($table) && !TableExists("glpi_plugin_order_config")) {
            $migration->displayMessage("Installing $table");

            //Install
            $query = "CREATE TABLE `$table` (
                     `id` int(11) NOT NULL auto_increment,
                     `use_validation` tinyint(1) NOT NULL default '0',
                     `use_supplier_satisfaction` tinyint(1) NOT NULL default '0',
                     `use_supplier_informations` tinyint(1) NOT NULL default '0',
                     `use_supplier_infos` tinyint(1) NOT NULL default '1',
                     `generate_order_pdf` tinyint(1) NOT NULL default '0',
                     `copy_documents` tinyint(1) NOT NULL default '0',
                     `default_taxes` int(11) NOT NULL default '0',
                     `generate_assets` int(11) NOT NULL default '0',
                     `generated_name` varchar(255) collate utf8_unicode_ci default NULL,
                     `generated_serial` varchar(255) collate utf8_unicode_ci default NULL,
                     `generated_otherserial` varchar(255) collate utf8_unicode_ci default NULL,
                     `default_asset_states_id` int(11) NOT NULL default '0',
                     `tickettemplates_id_delivery` int(11) NOT NULL default '0',
                     `order_status_draft` int(11) NOT NULL default '0',
                     `order_status_waiting_approval` int(11) NOT NULL default '0',
                     `order_status_approved` int(11) NOT NULL default '0',
                     `order_status_partially_delivred` int(11) NOT NULL default '0',
                     `order_status_completly_delivered` int(11) NOT NULL default '0',
                     `order_status_canceled` int(11) NOT NULL default '0',
                     `order_status_paid` int(11) NOT NULL default '0',
                     `shoudbedelivered_color` char(20) collate utf8_unicode_ci default '#ff5555',
                     `documentcategories_id` int(11) NOT NULL default '0',
                     `groups_id_author` int(11) NOT NULL default '0',
                     `groups_id_recipient` int(11) NOT NULL default '0',
                     `users_id_recipient` int(11) NOT NULL default '0',
                     `add_location` tinyint(1) NOT NULL default '0',
                     `add_bill_details` tinyint(1) NOT NULL default '0',
                     `hide_inactive_budgets` tinyint(1) NOT NULL default '0',
                     PRIMARY KEY  (`id`)
                  ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
               $DB->query($query) or die ($DB->error());
               
               $tobefilled = "TOBEFILLED";
               $tmp = array('id' => 1, 'use_validation' => 0, 'default_taxes' => 0,
                            'generate_assets' => 0, 'generated_name' => $tobefilled,
                            'generated_serial' => $tobefilled, 'generated_otherserial' => $tobefilled,
                            'default_asset_states_id' => 0,
                            'generate_ticket' => 0, 'generated_title' => $tobefilled,
                            'generated_content' => $tobefilled, 'default_ticketcategories_id' => 0,
                            'shoudbedelivered_color' => '#ff5555');
               $config->add($tmp);
      } else {
            //Upgrade
            $migration->displayMessage("Upgrading $table");

            //1.2.0
            $migration->renameTable("glpi_plugin_order_config", $table);

            if (!countElementsInTable("glpi_plugin_order_configs")) {
               $query = "INSERT INTO `glpi_plugin_order_configs`(`id`,`use_validation`,`default_taxes`) VALUES (1,0,0);";
               $DB->query($query) or die($DB->error());
            }
  
            $migration->changeField($table, "ID", "id", "int(11) NOT NULL auto_increment");
            
            //1.3.0
            $migration->addField($table, "generate_assets", "tinyint(1) NOT NULL default '0'");
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
                                 "tinyint(1) NOT NULL default '0'");
            $migration->addField($table, "generated_title",
                                 "varchar(255) collate utf8_unicode_ci default NULL");
            $migration->addField($table, "generated_content",
                                 "text collate utf8_unicode_ci");
            $migration->addField($table, "default_ticketcategories_id",
                                 "int(11) NOT NULL default '0'");
            $migration->addField($table, "use_supplier_satisfaction",
                                 "tinyint(1) NOT NULL default '0'");
            $migration->addField($table, "generate_order_pdf",
                                 "tinyint(1) NOT NULL default '0'");
            $migration->addField($table, "use_supplier_informations",
                                 "tinyint(1) NOT NULL default '1'");
            $migration->addField($table, "shoudbedelivered_color",
                                 "char(20) collate utf8_unicode_ci default '#ff5555'");
            $migration->changeField($table, "default_ticketcategories_id",
                                    "default_itilcategories_id", "INTEGER");
            $migration->addField($table, "copy_documents", "tinyint(1) NOT NULL DEFAULT '0'");
            $migration->addField($table, "documentcategories_id", "integer");
            $migration->addField($table, "groups_id_author", "integer");
            $migration->addField($table, "groups_id_recipient", "integer");
            $migration->addField($table, "users_id_recipient", "integer");
                     
            //1.9.0
            $migration->addField("glpi_plugin_order_configs", "add_location", "TINYINT(1) NOT NULL DEFAULT '0'");
            $migration->addField("glpi_plugin_order_configs", "add_bill_details", "TINYINT(1) NOT NULL DEFAULT '0'");
         
            $config = new self();
            $config->getFromDB(1);
            $templateID = false;
            if($config->fields['generate_ticket'] && !FieldExists("glpi_plugin_order_configs", "tickettemplates_id_delivery")) {
               //Create template
               $template = new TicketTemplate();
               $tmp['name'] = 'Order plugin';
               $tmp['entities_id'] = 0;
               $tmp['is_recursive'] = 1;
               $templateID = $template->add($tmp);
               
               //Create predefined fields
               $predefinedFied = new TicketTemplatePredefinedField();
               $tmp = array();
               $tmp['tickettemplates_id'] = $templateID;
               $tmp['num'] = 1;
               $tmp['value'] = $config->fields['generated_title'];
               $predefinedFied->add($tmp);

               $tmp['num'] = 21;
               $tmp['value'] = $config->fields['generated_content'];
               $predefinedFied->add($tmp);
                
               $tmp['num'] = 14;
               $tmp['value'] = 2;
               $predefinedFied->add($tmp);

               if ($config->fields['default_itilcategories_id'] > 0) {
                  $tmp['num'] = 7;
                  $tmp['value'] = $config->fields['generated_content'];
                  $predefinedFied->add($tmp);
               }
            }

            $migration->addField("glpi_plugin_order_configs", "tickettemplates_id_delivery", 'integer');
            $migration->migrationOneTable($table);

            $migration->dropField("glpi_plugin_order_configs", "generated_title");
            $migration->dropField("glpi_plugin_order_configs", "generated_content");
            $migration->dropField("glpi_plugin_order_configs", "default_itilcategories_id");

            $migration->addField("glpi_plugin_order_configs", "hide_inactive_budgets", "bool");

            $migration->migrationOneTable($table);
            if ($templateID) {
               $config->update(array('id' => 1, 'tickettemplates_id_delivery' => $templateID));
            }
            
      }

      $migration->displayMessage("Add default order state workflow");
      $new_states = array('order_status_draft'               => 1,
                          'order_status_waiting_approval'    => 2,
                          'order_status_approved'            => 3,
                          'order_status_partially_delivred'  => 4,
                          'order_status_completly_delivered' => 5,
                          'order_status_canceled'            => 6,
                          'order_status_paid'                => 7);

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
