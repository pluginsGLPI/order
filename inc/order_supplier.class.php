<?php
/*
 * @version $Id: HEADER 1 2010-03-03 21:49 Tsmr $
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
// Original Author of file: NOUH Walid & FONTAN Benjamin & CAILLAUD Xavier
// Purpose of file: plugin order v1.2.0 - GLPI 0.78
// ----------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginOrderOrder_Supplier extends CommonDBChild {
   
   public $itemtype = 'PluginOrderOrder';
   public $items_id = 'plugin_order_orders_id';
   public $dohistory=true;
   
   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_order'][4];
   }
   
   function canCreate() {
      return plugin_order_haveRight('order', 'w');
   }

   function canView() {
      return plugin_order_haveRight('order', 'r');
   }
   
   function getSearchOptions() {
      global $LANG;

      $tab = array();
    
      $tab['common'] = $LANG['plugin_order'][4];

      $tab[1]['table'] = $this->getTable();
      $tab[1]['field'] = 'num_quote';
      $tab[1]['linkfield'] = 'num_quote';
      $tab[1]['name'] = $LANG['plugin_order'][30];
      $tab[1]['datatype'] = 'text';

      $tab[2]['table'] = $this->getTable();
      $tab[2]['field'] = 'num_order';
      $tab[2]['linkfield'] = 'num_order';
      $tab[2]['name'] = $LANG['plugin_order'][31];
      $tab[2]['datatype'] = 'text';
      
      $tab[3]['table'] = $this->getTable();
      $tab[3]['field'] = 'num_bill';
      $tab[3]['linkfield'] = 'num_bill';
      $tab[3]['name'] = $LANG['plugin_order'][28];
      $tab[3]['datatype'] = 'text';
      
      $tab[4]['table'] = 'glpi_suppliers';
      $tab[4]['field'] = 'name';
      $tab[4]['linkfield'] = 'suppliers_id';
      $tab[4]['name'] = $LANG['financial'][26];
      $tab[4]['datatype']='itemlink';
      $tab[4]['itemlink_type']='Supplier';
      $tab[4]['forcegroupby']=true;
      
      $tab[30]['table'] = $this->getTable();
      $tab[30]['field'] = 'id';
      $tab[30]['linkfield'] = '';
      $tab[30]['name']=$LANG['common'][2];

      /* entity */
      $tab[80]['table'] = 'glpi_entities';
      $tab[80]['field'] = 'completename';
      $tab[80]['linkfield'] = 'entities_id';
      $tab[80]['name'] = $LANG['entity'][0];
      
      return $tab;
   }
   
   function defineTabs($options=array()) {
      global $LANG;
      /* principal */
      $ong[1] = $LANG['title'][26];
      $ong[12] = $LANG['title'][38];

      return $ong;
   }
   
   function prepareInputForAdd($input) {
      // Not attached to reference -> not added
      if (!isset($input['plugin_order_orders_id']) || $input['plugin_order_orders_id'] <= 0) {
         return false;
      }
      return $input;
   }
   
   function getFromDBByOrder($plugin_order_orders_id) {
      global $DB;
      
      $query = "SELECT * FROM `".$this->getTable()."`
               WHERE `plugin_order_orders_id` = '" . $plugin_order_orders_id . "' ";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         } else {
            return false;
         }
      }
      return false;
   }
   
   function showForm ($ID, $options=array()) {
      global $LANG;
      
      if (!$this->canView())
         return false;
      
      $plugin_order_orders_id = -1;
      if (isset($options['plugin_order_orders_id'])) {
         $plugin_order_orders_id = $options['plugin_order_orders_id'];
      }
        
      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         $input=array('plugin_order_orders_id' => $options['plugin_order_orders_id']);
         $this->check(-1,'w',$input);
      }
      
      if (strpos($_SERVER['PHP_SELF'],"order_supplier")) {
         $this->showTabs($options);
      }
      $this->showFormHeader($options);
      
      $PluginOrderOrder = new PluginOrderOrder();
      $PluginOrderOrder->getFromDB($plugin_order_orders_id);
      echo "<input type='hidden' name='plugin_order_orders_id' value='$plugin_order_orders_id'>";
      echo "<input type='hidden' name='entities_id' value='".$PluginOrderOrder->getEntityID()."'>";
      echo "<input type='hidden' name='is_recursive' value='".$PluginOrderOrder->isRecursive()."'>";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . $LANG['financial'][26] . ": </td>";
      $supplier = $PluginOrderOrder->fields["suppliers_id"];
      if ($ID > 0)
         $supplier = $this->fields["suppliers_id"];
         
      echo "<td>";
      $link=getItemTypeFormURL('Supplier');
      echo "<a href=\"" . $link. "?id=" . $supplier . "\">" . Dropdown::getDropdownName("glpi_suppliers", $supplier) . "</a></td>";
      echo "<input type='hidden' name='suppliers_id' value='".$supplier."'>";
      
      /* number of quote */
      echo "<td>" . $LANG['plugin_order'][30] . ": </td><td>";
      autocompletionTextField($this,"num_quote");
      echo "</td>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      /* num order supplier */
      echo "<td>" . $LANG['plugin_order'][31] . ": </td><td>";
      autocompletionTextField($this,"num_order");
      echo "</td>";
      
      /* number of bill */
      echo "<td>" . $LANG['plugin_order'][28] . ": </td><td>";
      autocompletionTextField($this,"num_bill");
      echo "</td>";
      
      echo "</tr>";
      
      $options['candel'] = false;
      $this->showFormButtons($options);
      
      if (strpos($_SERVER['PHP_SELF'],"order_supplier")) {
         $this->addDivForTabs();
      }
      return true;
   }
   
   function showOrderSupplierInfos($target, $ID) {
      global $LANG, $DB, $CFG_GLPI;

      $order = new PluginOrderOrder;
      $order->getFromDB($ID);

      initNavigateListItems($this->getType(),$LANG['plugin_order'][7] ." = ". $order->fields["name"]);

      $candelete =$order->can($ID,'w');
      $query = "SELECT * FROM `".$this->getTable()."` WHERE `plugin_order_orders_id` = '$ID' ";
      $result = $DB->query($query);
      $rand=mt_rand();
      echo "<div class='center'>";
      echo "<form method='post' name='show_supplierinfos$rand' id='show_supplierinfos$rand' action=\"$target\">";
      echo "<input type='hidden' name='plugin_order_orders_id' value='" . $ID . "'>";
      echo "<table class='tab_cadre_fixe'>";
      
      echo "<tr><th colspan='5'>".$LANG['plugin_order'][4]."</th></tr>";
      echo "<tr><th>&nbsp;</th>";
      echo "<th>" . $LANG['financial'][26] . "</th>";
      echo "<th>" . $LANG['plugin_order'][30] . "</th>";
      echo "<th>" . $LANG['plugin_order'][31] . "</th>";
      echo "<th>" . $LANG['plugin_order'][28] . "</th>";
      echo "</tr>";

      if ($DB->numrows($result) > 0) {

         while ($data = $DB->fetch_array($result)) {
            addToNavigateListItems($this->getType(),$data['id']);
            echo "<input type='hidden' name='item[" . $data["id"] . "]' value='" . $ID . "'>";
            echo "<tr class='tab_bg_1 center'>";
            echo "<td>";
            if ($candelete) {
               echo "<input type='checkbox' name='check[" . $data["id"] . "]'";
               if (isset($_POST['check']) && $_POST['check'] == 'all')
                  echo " checked ";
               echo ">";
            }
            echo "</td>";
            $link=getItemTypeFormURL($this->getType());
            echo "<td><a href='".$link."?id=".$data["id"]."&plugin_order_orders_id=".$ID."'>" .Dropdown::getDropdownName("glpi_suppliers", $data["suppliers_id"]) . "</a></td>";
            echo "<td>";
            echo $data["num_quote"];
            echo "</td>";
            echo "<td>";
            echo $data["num_order"];
            echo "</td>";
            echo "<td>";
            echo $data["num_bill"];
            echo "</td>";
            echo "</tr>";
         }
         echo "</table>";

         if ($candelete)
         {
            echo "<div class='center'>";
            echo "<table width='900px' class='tab_glpi'>";
            echo "<tr><td><img src=\"".$CFG_GLPI["root_doc"]."/pics/arrow-left.png\" alt=''></td><td class='center'><a onclick= \"if ( markCheckboxes('show_supplierinfos$rand') ) return false;\" href='#'>".$LANG['buttons'][18]."</a></td>";

            echo "<td>/</td><td class='center'><a onclick= \"if ( unMarkCheckboxes('show_supplierinfos$rand') ) return false;\" href='#'>".$LANG['buttons'][19]."</a>";
            echo "</td><td align='left' width='80%'>";
            echo "<input type='submit' name='delete' value=\"" . $LANG['buttons'][6] . "\" class='submit' >";
            echo "</td>";
            echo "</table>";
            echo "</div>";
         }
      }
      else
         echo "</table>";

      echo "</form>";
      echo "</div>";
   }
   
   function checkIfSupplierInfosExists($plugin_order_orders_id) {
      
      if ($plugin_order_orders_id) {
         $devices = getAllDatasFromTable($this->getTable(), "`plugin_order_orders_id` = '$plugin_order_orders_id' ");
         if (!empty($devices))
            return true;
         else
            return false;
      }
   }
   
   function showDeliveries($suppliers_id) {
      global $LANG,$DB;
      
      $query = "SELECT COUNT(`glpi_plugin_order_orders_items`.`plugin_order_references_id`) AS ref, `glpi_plugin_order_orders_items`.`plugin_order_deliverystates_id`, `glpi_plugin_order_orders`.`entities_id` 
                  FROM `glpi_plugin_order_orders_items` 
                  LEFT JOIN `glpi_plugin_order_orders` ON (`glpi_plugin_order_orders`.`id` = `glpi_plugin_order_orders_items`.`plugin_order_orders_id`)
                  WHERE `glpi_plugin_order_orders`.`suppliers_id` = '".$suppliers_id."' 
                  AND `glpi_plugin_order_orders_items`.`states_id` = '".PluginOrderOrder::ORDER_DEVICE_DELIVRED."' "
                  .getEntitiesRestrictRequest(" AND ","glpi_plugin_order_orders",'','',true);
      $query.= "GROUP BY `glpi_plugin_order_orders`.`entities_id`,`glpi_plugin_order_orders_items`.`plugin_order_deliverystates_id`";
      $result = $DB->query($query);
      $nb = $DB->numrows($result);
      
      echo "<br><div class='center'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      echo "<th>".$LANG['entity'][0]."</th>";
      echo "<th>" . $LANG['plugin_order']['status'][13] ."</th>";
      echo "</tr>";
      
      if ($nb) {
         for ($i=0 ; $i <$nb ; $i++) {
            $ref = $DB->result($result,$i,"ref");
            $entities_id = $DB->result($result,$i,"entities_id");
            $plugin_order_deliverystates_id = $DB->result($result,$i,"plugin_order_deliverystates_id");
            echo "<tr class='tab_bg_1'>";
            echo "<td>";
            echo Dropdown::getDropdownName("glpi_entities",$entities_id);
            echo "</td>";
            if ($plugin_order_deliverystates_id > 0)
               $name = Dropdown::getDropdownName("glpi_plugin_order_deliverystates",$plugin_order_deliverystates_id);
            else
               $name = $LANG['plugin_order']['status'][4];
            echo "<td>" .$ref. "&nbsp;".$name."</td>";
            echo "</tr>";
         }
      }
      echo "</table>";
      echo "</div>";
   }
}

?>