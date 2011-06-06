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

class PluginOrderOrder extends CommonDBTM {

   public $dohistory=true;
   
   const ORDER_DEVICE_NOT_DELIVRED        = 0;
   const ORDER_DEVICE_DELIVRED            = 1;

   // Define order status //PluginOrderOrder::
   const ORDER_STATUS_DRAFT               = 0;
   const ORDER_STATUS_WAITING_APPROVAL    = 1;
   const ORDER_STATUS_APPROVED            = 2;
   const ORDER_STATUS_PARTIALLY_DELIVRED  = 3;
   const ORDER_STATUS_COMPLETLY_DELIVERED = 4;
   const ORDER_STATUS_CANCELED            = 5;
   
   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_order']['title'][1];
   }
   
   function canCreate() {
      return plugin_order_haveRight('order', 'w');
   }

   function canView() {
      return plugin_order_haveRight('order', 'r');
   }
   
   function canCancel() {
      return plugin_order_haveRight("cancel", "w");
   }
   
   function canUndo() {
      return plugin_order_haveRight("undo_validation", "w");
   }
   
   function canValidate() {
      return plugin_order_haveRight("validation", "w");
   }
   
   function cleanDBonPurge() {

      $temp = new PluginOrderOrder_Item();
      $temp->deleteByCriteria(array('plugin_order_orders_id' => $this->fields['id']));

   }
   
   function canUpdateOrder($orders_id) {
      
      $ORDER_VALIDATION_STATUS = array (self::ORDER_STATUS_DRAFT,
                                        self::ORDER_STATUS_WAITING_APPROVAL);
                                    
      if ($orders_id > 0) {
         $this->getFromDB($orders_id);
         return (in_array($this->fields["states_id"], $ORDER_VALIDATION_STATUS));
      } else {
         return true;
      }
   }
   
      
   function canDisplayValidationForm($orders_id) {

      $this->getFromDB($orders_id);

      //If it's an order creation -> do not display form
      if (!$orders_id) {
         return false;
      } else {
         return ($this->canValidateOrrder() 
                  || $this->canUndoValidation() 
                     || $this->canCancelOrder());
      }
   }
   
   function canValidateOrrder() {
      
      $PluginOrderConfig = new PluginOrderConfig();
      $config = $PluginOrderConfig->getConfig();
      
      $ORDER_VALIDATION_STATUS = array (self::ORDER_STATUS_DRAFT,
                                        self::ORDER_STATUS_WAITING_APPROVAL);
      //If no validation process -> can validate if order is in draft state
      if (!$config["use_validation"]) {
         return ($this->fields["states_id"] == self::ORDER_STATUS_DRAFT);
      } else {
         //Validation process is used

         //If order is canceled, cannot validate !
         if ($this->fields["states_id"] == self::ORDER_STATUS_CANCELED) {
            return false;
         }

         //If no right to validate
         if (!$this->canValidate()) {
            return false;
         } else {
            return (in_array($this->fields["states_id"], $ORDER_VALIDATION_STATUS));
         }
      }
   }

   function canCancelOrder() {
      //If order is canceled, cannot validate !
      if ($this->fields["states_id"] == self::ORDER_STATUS_CANCELED) {
         return false;
      }

      //If no right to cancel
      if (!$this->canCancel()) {
         return false;
      }

      return true;
   }

   function canDoValidationRequest() {
      
      $PluginOrderConfig = new PluginOrderConfig;
      $config = $PluginOrderConfig->getConfig();
      
      if (!$config["use_validation"]) {
         return false;
      } else {
         return ($this->fields["states_id"] == self::ORDER_STATUS_DRAFT);
      }
   }

   function canCancelValidationRequest() {
   
      return ($this->fields["states_id"] == self::ORDER_STATUS_WAITING_APPROVAL);
   }

   function canUndoValidation() {
      
      $ORDER_VALIDATION_STATUS = array (self::ORDER_STATUS_DRAFT,
                                        self::ORDER_STATUS_WAITING_APPROVAL);
                                    
      //If order is canceled, cannot validate !
      if ($this->fields["states_id"] == self::ORDER_STATUS_CANCELED) {
         return false;
      }

      //If order is not validate, cannot undo validation !
      if (in_array($this->fields["states_id"], $ORDER_VALIDATION_STATUS)) {
         return false;
      }

      //If no right to cancel
      return ($this->canUndo());
   }
   
   function getSearchOptions() {
      global $LANG;

      $tab = array();
    
      $tab['common'] = $LANG['plugin_order']['title'][1];
      /* order_number */
      $tab[1]['table'] = $this->getTable();
      $tab[1]['field'] = 'num_order';
      $tab[1]['name'] = $LANG['plugin_order'][0];
      $tab[1]['datatype'] = 'itemlink';
      /* order_date */
      $tab[2]['table'] = $this->getTable();
      $tab[2]['field'] = 'order_date';
      $tab[2]['name'] = $LANG['plugin_order'][1];
      $tab[2]['datatype']='date';
      /* taxes*/
      $tab[3]['table'] = 'glpi_plugin_order_ordertaxes';
      $tab[3]['field'] = 'name';
      $tab[3]['name'] = $LANG['plugin_order'][25] . " " . $LANG['plugin_order'][26];
      /* location */
      $tab[4]['table'] = 'glpi_locations';
      $tab[4]['field'] = 'completename';
      $tab[4]['name'] = $LANG['plugin_order'][40];
      /* status */
      $tab[5]['table'] = $this->getTable();
      $tab[5]['field'] = 'states_id';
      $tab[5]['name'] = $LANG['plugin_order']['status'][0];
      $tab[5]['searchtype'] = 'equals';
      /* supplier */
      $tab[6]['table'] = 'glpi_suppliers';
      $tab[6]['field'] = 'name';
      $tab[6]['name'] = $LANG['financial'][26];
      $tab[6]['datatype']='itemlink';
      $tab[6]['itemlink_type']='Supplier';
      $tab[6]['forcegroupby']=true;
      /* payment */
      $tab[7]['table'] = 'glpi_plugin_order_orderpayments';
      $tab[7]['field'] = 'name';
      $tab[7]['name'] = $LANG['plugin_order'][32];
      /* contact */
      $tab[8]['table'] = 'glpi_contacts';
      $tab[8]['field'] = 'completename';
      $tab[8]['name'] = $LANG['common'][18];
      $tab[8]['datatype']='itemlink';
      $tab[8]['itemlink_type']='Contact';
      $tab[8]['forcegroupby']=true;
      /* budget */
      $tab[9]['table'] = 'glpi_budgets';
      $tab[9]['field'] = 'name';
      $tab[9]['name'] = $LANG['financial'][87];
      $tab[9]['datatype']='itemlink';
      $tab[9]['itemlink_type']='Budget';
      $tab[9]['forcegroupby']=true;
      /* title */
      $tab[10]['table'] = $this->getTable();
      $tab[10]['field'] = 'name';
      $tab[10]['name'] = $LANG['plugin_order'][39];
      /* type */
      $tab[11]['table'] = 'glpi_plugin_order_ordertypes';
      $tab[11]['field'] = 'name';
      $tab[11]['name'] = $LANG['common'][17];
      /* comments */
      $tab[16]['table'] = $this->getTable();
      $tab[16]['field'] = 'comment';
      $tab[16]['name'] = $LANG['plugin_order'][2];
      $tab[16]['datatype'] = 'text';
      /* port price */
      $tab[17]['table'] = $this->getTable();
      $tab[17]['field'] = 'port_price';
      $tab[17]['name'] = $LANG['plugin_order'][26];
      /* ID */
      $tab[30]['table'] = $this->getTable();
      $tab[30]['field'] = 'id';
      $tab[30]['name'] = $LANG['common'][2];
      /* entity */
      $tab[80]['table'] = 'glpi_entities';
      $tab[80]['field'] = 'completename';
      $tab[80]['name'] = $LANG['entity'][0];
      
      return $tab;
   }
   
   /*define header form */
   function defineTabs($options=array()) {
      global $LANG;
      
      /* principal */
      $ong[1] = $LANG['title'][26];
      if ($this->fields['id'] > 0) {
         /* detail */
         $ong[2] = $LANG['plugin_order'][5];
         /* fournisseur */
         $ong[3] = $LANG['plugin_order'][4];
         /* generation*/
         $ong[4] = $LANG['plugin_order']['generation'][2];
         /* delivery */
         $ong[5] = $LANG['plugin_order']['delivery'][1];
         /* item */
         $ong[6] = $LANG['plugin_order']['item'][0];
         /* quality */
         $ong[7] = $LANG['plugin_order'][10];
         /* documents */
         if (haveRight("document", "r"))
            $ong[9] = $LANG['Menu'][27];
         
         if (haveRight("notes", "r"))
            $ong[10] = $LANG['title'][37];
         /* all */
         $ong[12] = $LANG['title'][38];
      }
      return $ong;
   }

   function prepareInputForAdd($input) {
      global $LANG;
      
      if (!isset ($input["num_order"]) || $input["num_order"] == '') {
         addMessageAfterRedirect($LANG['plugin_order'][44], false, ERROR);
         return array ();
      } elseif (!isset ($input["name"]) || $input["name"] == '') {
         $input["name"] = $input["num_order"];
      }

      return $input;
   }

   function showForm ($ID, $options=array()) {
      global $CFG_GLPI, $LANG;

      if (!$this->canView()) return false;

      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w');
         $this->getEmpty();
      }

      $canedit = ($this->canUpdateOrder($ID) 
                  && $this->can($ID, 'w') 
                     && $this->fields["states_id"] != self::ORDER_STATUS_CANCELED);

      $this->showTabs($options);
      $this->showFormHeader($options);

      //Display without inside table
      /* title */
      echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_order'][39] . ": </td>";
      echo "<td>";
      if ($canedit) {
         autocompletionTextField($this,"name");
      } else {
         echo $this->fields["name"];
      }
      echo "</td>";
      /* date of order */
      echo "<td>" . $LANG['plugin_order'][1] . "*:</td><td>";
      if ($canedit)  {
         if ($this->fields["order_date"] == NULL) {
            showDateFormItem("order_date", date("Y-m-d"), true, true);
         } else {
            showDateFormItem("order_date", $this->fields["order_date"], true, true);
         }
      } else {
         echo convDate($this->fields["order_date"]);
      }
      echo "</td></tr>";

      /* num order */
      echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_order'][0] . "*: </td>";
      echo "<td>";
      if ($canedit) {
         autocompletionTextField($this,"num_order");
      } else {
         echo $this->fields["num_order"];
      }
      echo "</td>";
      /* type order */
      echo "<td>" . $LANG['common'][17] . ": </td><td>";
      if ($canedit){
         Dropdown::show('PluginOrderOrderType', array('name' => "plugin_order_ordertypes_id",
                                                      'value' => $this->fields["plugin_order_ordertypes_id"]));
      } else {
         echo Dropdown::getDropdownName("glpi_plugin_order_ordertypes", 
                                        $this->fields["plugin_order_ordertypes_id"]);
      }
      echo "</td></tr>";

      /* location */
      echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_order'][40] . ": </td>";
      echo "<td>";
      if ($canedit) {
         Dropdown::show('Location', 
                        array('name'   => "locations_id",
                              'value'  => $this->fields["locations_id"], 
                              'entity' => $this->fields["entities_id"]));
      } else {
         echo Dropdown::getDropdownName("glpi_locations", $this->fields["locations_id"]);
      }
      echo "</td>";
      
      /* budget */
      echo "<td>" . $LANG['plugin_order'][3] . ": </td><td>";
      if ($canedit) {
         Dropdown::show('Budget', array('name'     => "budgets_id", 
                                        'value'    => $this->fields["budgets_id"], 
                                        'entity'   => $this->fields["entities_id"],
                                        'comments' => true));
      } else {
         echo Dropdown::getDropdownName("glpi_budgets",$this->fields["budgets_id"]);
      }
      echo "</td></tr>";
      
      /* supplier of order */
      echo "<tr class='tab_bg_1'><td>" . $LANG['financial'][26] . ": </td>";
      echo "<td>";
      if ($canedit && !$this->checkIfDetailExists($ID)) {
         $this->dropdownSuppliers("suppliers_id", $this->fields["suppliers_id"], 
                                  $this->fields["entities_id"]);
      } else {
         echo Dropdown::getDropdownName("glpi_suppliers", $this->fields["suppliers_id"]);
      }
      echo "</td>";
      /* payment */
      echo "<td>" . $LANG['plugin_order'][32] . ": </td><td>";
      if ($canedit) {
         Dropdown::show('PluginOrderOrderPayment', 
                        array('name'  => "plugin_order_orderpayments_id",
                              'value' => $this->fields["plugin_order_orderpayments_id"]));
      } else {
         echo Dropdown::getDropdownName("glpi_plugin_order_orderpayments", 
                                        $this->fields["plugin_order_orderpayments_id"]);
      }
      echo "</td>";
      echo "</tr>";
      
      /* linked contact of the supplier of order */
      echo "<tr class='tab_bg_1'><td>".$LANG['common'][92].": </td>";
      echo "<td><span id='show_contacts_id'>";
      if ($canedit && $ID > 0) {
         $this->dropdownContacts($this->fields["suppliers_id"],
                                 $this->fields["contacts_id"], $this->fields["entities_id"]);
      } else {
         echo Dropdown::getDropdownName("glpi_contacts", $this->fields["contacts_id"]);
      }
      echo "</span></td>";

      /* port price */
      echo "<td>".$LANG['plugin_order'][26].": </td>";
      echo "<td>";
      if ($canedit) {
         echo "<input type='text' name='port_price' value=\"".
            formatNumber($this->fields["port_price"],true)."\" size='5'>";
      } else {
         echo formatNumber($this->fields["port_price"]);
      }
      echo "</td>";
      echo "</tr>";
      
      /* tva port price */
      echo "<tr class='tab_bg_1'><td colspan=\"2\"></td>";
      echo "<td>" . $LANG['plugin_order'][25] . " " . $LANG['plugin_order'][26] . ": </td><td>";
      $PluginOrderConfig = new PluginOrderConfig();
      $default_taxes = $PluginOrderConfig->getDefaultTaxes();

      if (empty ($ID) || $ID < 0) {
         $taxes = $default_taxes;
      } else {
         $taxes = $this->fields["plugin_order_ordertaxes_id"];
      }
      if ($canedit) {
         Dropdown::show('PluginOrderOrderTaxe', 
                        array('name' => "plugin_order_ordertaxes_id", 'value' => $taxes));
      } else {
         echo Dropdown::getDropdownName("glpi_plugin_order_ordertaxes", $taxes);
      }
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'><td>";
      //comments of order
      echo $LANG['plugin_order'][2] . ":  </td>";
      echo "<td>";
      if ($canedit) {
         echo "<textarea cols='40' rows='4' name='comment'>" . $this->fields["comment"] . 
            "</textarea>";
      } else {
         echo $this->fields["comment"];
      }
      echo "</td>";

      /* total price (without taxes) */
      
      /* status of bill */
      echo "<td class='center b'>" . $LANG['plugin_order']['status'][0] . "<br />";
      echo "<input type='hidden' name='states_id' value=" . PluginOrderOrder::ORDER_STATUS_DRAFT . ">";
      echo self::getState($this->fields["states_id"]);

      echo "</td><td>";
      if ($ID > 0) {
         $PluginOrderOrder_Item = new PluginOrderOrder_Item();
         $prices = $PluginOrderOrder_Item->getAllPrices($ID);

         echo $LANG['plugin_order'][13] . " : ";
         echo formatNumber($prices["priceHT"]) . "<br />";
     
         // total price (with postage)
         $postagewithTVA = 
            $PluginOrderOrder_Item->getPricesATI($this->fields["port_price"], 
                                                 Dropdown::getDropdownName("glpi_plugin_order_ordertaxes", 
                                                                           $this->fields["plugin_order_ordertaxes_id"]));

         echo $LANG['plugin_order'][15] . " : ";
         $priceHTwithpostage=$prices["priceHT"]+$this->fields["port_price"];
         echo formatNumber($priceHTwithpostage) . "<br />";
         
         // total price (with taxes)
         echo $LANG['plugin_order'][14] . " : ";
         $total = $prices["priceTTC"] + $postagewithTVA;
         echo formatNumber($total) . "<br />";
         
         // total TVA
         echo "(" . $LANG['plugin_order'][25] . " : ";
         $total_tva = $prices["priceTVA"] + ($postagewithTVA- $this->fields["port_price"]);
         echo formatNumber($total_tva) . ")</td>";
      } else
         echo "</td>";

      echo "</tr>";
      
      if ($canedit) {
         $this->showFormButtons($options);
      } else {
         echo "</table></div></form>";
      }
      $this->addDivForTabs();
      
      return true;
   }

   function dropdownSuppliers($myname,$value=0,$entity_restrict='') {
      global $DB,$CFG_GLPI;

      $rand=mt_rand();

      $where=" WHERE `glpi_suppliers`.`is_deleted` = '0' ";
      $where.=getEntitiesRestrictRequest("AND","glpi_suppliers",'',$entity_restrict,true);

      $query="SELECT `glpi_suppliers`.* FROM `glpi_suppliers`
              LEFT JOIN `glpi_contacts_suppliers` 
                 ON (`glpi_contacts_suppliers`.`suppliers_id` = `glpi_suppliers`.`id`)
              $where
              GROUP BY `glpi_suppliers`.`id`
              ORDER BY `entities_id`, `name`";

      $result=$DB->query($query);

      echo "<select name='suppliers_id' id='suppliers_id'>\n";
      echo "<option value='0'>".DROPDOWN_EMPTY_VALUE."</option>\n";

      $prev=-1;
     while ($data=$DB->fetch_array($result)) {
         if ($data["entities_id"]!=$prev) {
            if ($prev>=0) {
               echo "</optgroup>";
            }
            $prev=$data["entities_id"];
            echo "<optgroup label=\"". Dropdown::getDropdownName("glpi_entities", $prev) ."\">";
         }
         $output = $data["name"];
         if($_SESSION["glpiis_ids_visible"]||empty($output)){
            $output.=" (".$data["id"].")";
         }
         echo "<option value='".$data["id"]."' ".($value==$data["id"]?" selected ":"").
            " title=\"".cleanInputText($output)."\">".
               substr($output, 0, $CFG_GLPI["dropdown_chars_limit"])."</option>";
      }
      if ($prev>=0) {
         echo "</optgroup>";
      }
      echo "</select>\n";

      $params=array('suppliers_id'=>'__VALUE__', 'entity_restrict'=>$entity_restrict,
                    'rand'=>$rand, 'myname'=>$myname);

      ajaxUpdateItemOnSelectEvent("suppliers_id", "show_contacts_id", 
                                  $CFG_GLPI["root_doc"]."/plugins/order/ajax/dropdownSupplier.php", 
                                  $params);

      return $rand;
   }
   
   function dropdownContacts($suppliers_id,$value=0,$entity_restrict='') {
      global $DB,$CFG_GLPI;

      $rand=mt_rand();

      $where=" WHERE `glpi_contacts_suppliers`.`contacts_id` = `glpi_contacts`.`id` 
                 AND (`glpi_contacts_suppliers`.`suppliers_id` = '".$suppliers_id."' 
                    AND `glpi_contacts`.`is_deleted` = '0' ) ";
      $where.=getEntitiesRestrictRequest("AND","glpi_contacts",'',$entity_restrict,true);

      $query = "SELECT `glpi_contacts`.*
               FROM `glpi_contacts`,`glpi_contacts_suppliers`
               $where
               ORDER BY `entities_id`, `name`";
               
      $result=$DB->query($query);

      echo "<select name=\"contacts_id\">";

      echo "<option value=\"0\">".DROPDOWN_EMPTY_VALUE."</option>";

      if ($DB->numrows($result)) {
         $prev=-1;
         while ($data=$DB->fetch_array($result)) {
            if ($data["entities_id"]!=$prev) {
               if ($prev>=0) {
                  echo "</optgroup>";
               }
               $prev=$data["entities_id"];
               echo "<optgroup label=\"". Dropdown::getDropdownName("glpi_entities", $prev) ."\">";
            }
            $output=formatUserName($data["id"],"",$data["name"],$data["firstname"]);
            if($_SESSION["glpiis_ids_visible"]||empty($output)){
               $output.=" (".$data["id"].")";
            }
            echo "<option value='".$data["id"]."' ".($value==$data["id"]?" selected ":"").
               " title=\"".cleanInputText($output)."\">".
                  substr($output, 0, $CFG_GLPI["dropdown_chars_limit"])."</option>";
         }
         if ($prev>=0) {
            echo "</optgroup>";
         }
      }
      echo "</select>";
   }
   
   /**
    * get the order status list
    *
    * @param $withmetaforsearch boolean
    * @return an array
    */
   static function getAllStateArray() {
      global $LANG;

      $tab = array(self::ORDER_STATUS_DRAFT               => $LANG['plugin_order']['status'][9],
                   self::ORDER_STATUS_APPROVED            => $LANG['plugin_order']['status'][12],
                   self::ORDER_STATUS_WAITING_APPROVAL    => $LANG['plugin_order']['status'][7],
                   self::ORDER_STATUS_PARTIALLY_DELIVRED  => $LANG['plugin_order']['status'][1],
                   self::ORDER_STATUS_COMPLETLY_DELIVERED => $LANG['plugin_order']['status'][2],
                   self::ORDER_STATUS_CANCELED            => $LANG['plugin_order']['status'][10]);

      return $tab;
   }
   
   /**
   * Dropdown of order state
   *
   * @param $name select name
   * @param $value default value
   *
   * @return string id of the select
   */
   static function dropdownState($name, $value=0) {
      global $LANG;

      $id = "select_$name".mt_rand();
      echo "<select id='$id' name='$name'>";
      echo "<option value='0'>".DROPDOWN_EMPTY_VALUE."</option>";
      $tab = self::getAllStateArray();
      foreach($tab as $key=>$val) {
         echo "<option value='".$key."' ".($value==$key?" selected ":"").">".$val."</option>";
      }
      echo "</select>";

      return $id;
   }
   
   static function getState($value) {
      global $LANG;

      $tab = self::getAllStateArray();
      if ($value > -1) {
         return (isset($tab[$value]) ? $tab[$value] : '');
      } else {
         return " ";
      }
   }
   
   function addStatusLog($orders_id, $status, $comments = '') {
      global $LANG;

      switch ($status) {
         case self::ORDER_STATUS_DRAFT :
            $changes = $LANG['plugin_order']['validation'][15];
            break;
         case self::ORDER_STATUS_WAITING_APPROVAL :
            $changes = $LANG['plugin_order']['validation'][1];
            break;
         case self::ORDER_STATUS_APPROVED :
            $changes = $LANG['plugin_order']['validation'][2];
            break;
         case self::ORDER_STATUS_PARTIALLY_DELIVRED :
            $changes = $LANG['plugin_order']['validation'][3];
            break;
         case self::ORDER_STATUS_COMPLETLY_DELIVERED :
            $changes = $LANG['plugin_order']['validation'][4];
            break;
         case self::ORDER_STATUS_CANCELED :
            $changes = $LANG['plugin_order']['validation'][5];
            break;
      }

      if ($comments != '') {
         $changes .= " : ".$comments;
      }

      $this->addHistory($this->getType(), '', $changes, $orders_id);

   }
   
   function updateOrderStatus($orders_id, $status, $comments = '') {
      global $CFG_GLPI;
      
      $input["states_id"] = $status;
      $input["id"]        = $orders_id;
      $this->dohistory    = false;
      $this->update($input);
      $this->addStatusLog($orders_id, $status, $comments);
      
      if ($CFG_GLPI["use_mailing"] 
         && ($status == self::ORDER_STATUS_APPROVED
            || $status == self::ORDER_STATUS_WAITING_APPROVAL
               || $status == self::ORDER_STATUS_CANCELED
                  || $status == self::ORDER_STATUS_DRAFT)) {
         
         if ($status == self::ORDER_STATUS_APPROVED)
            $notif = "validation";
         else if ($status == self::ORDER_STATUS_WAITING_APPROVAL)
            $notif = "ask";
         else if ($status == self::ORDER_STATUS_CANCELED)
            $notif = "cancel";
         else if ($status == self::ORDER_STATUS_DRAFT)
            $notif = "undovalidation";
         
         $options             = array();
         $options['comments'] = $comments;
         NotificationEvent::raiseEvent($notif,$this,$options);
      }
      
      return true;
   }
   
   function addHistory($type, $old_value='',$new_value='',$ID){
      $changes[0] = 0;
      $changes[1] = $old_value;
      $changes[2] = $new_value;
      Log::history($ID, $type, $changes, 0, HISTORY_LOG_SIMPLE_MESSAGE);
   }

   function needValidation($ID) {
      
      $ORDER_VALIDATION_STATUS = array (PluginOrderOrder::ORDER_STATUS_DRAFT,
                                        PluginOrderOrder::ORDER_STATUS_WAITING_APPROVAL);
      
      if ($ID > 0 && $this->getFromDB($ID))
         return (in_array($this->fields["states_id"], $ORDER_VALIDATION_STATUS));
      else
         return false;
   }
   
   function deleteAllLinkWithItem($orders_id) {

      $detail = new PluginOrderOrder_Item;
      $devices = getAllDatasFromTable("glpi_plugin_order_orders_items", 
                                      "plugin_order_orders_id=$orders_id");
      foreach ($devices as $deviceID => $device)
         $detail->delete(array ("id" => $deviceID));
   }
   
   function checkIfDetailExists($orders_id) {
      
      if ($orders_id) {
         $detail = new PluginOrderOrder_Item;
         $devices = getAllDatasFromTable("glpi_plugin_order_orders_items", 
                                         "plugin_order_orders_id=$orders_id");
         if (!empty($devices)) {
            return true;
         } else {
            return false;
         }
      }
   }
   
   function showValidationForm($target, $orders_id) {
      global $LANG;
      
      $this->getFromDB($orders_id);

      if ($this->can($orders_id,'w') && $this->canDisplayValidationForm($orders_id)) {
         echo "<form method='post' name='form' action=\"$target\">";
         
         echo "<div align='center'><table class='tab_cadre_fixe'>";
         
         if ($this->checkIfDetailExists($orders_id)) {
         
            echo "<tr class='tab_bg_2'><th colspan='3'>" . 
               $LANG['plugin_order']['validation'][6] . "</th></tr>";

            echo "<tr class='tab_bg_1'>";
            echo "<td valign='top' align='right'>";
            echo $LANG['common'][25] . ":&nbsp;";
            echo "</td>";
            echo "<td valign='top' align='left'>";
            echo "<textarea cols='40' rows='4' name='comment'></textarea>";
            echo "</td>";

            echo "<td align='center'>";
            echo "<input type='hidden' name='id' value=\"$orders_id\">\n";
            
            if ($this->canCancelOrder()) {
               echo "<input type='submit' onclick=\"return confirm('" . 
                  $LANG['plugin_order']['detail'][38] . "')\" name='cancel_order' value=\"" . 
                     $LANG['plugin_order']['validation'][12] . "\" class='submit'>";
               $link = "<br><br>";
            }
            $PluginOrderConfig = new PluginOrderConfig;
            $config = $PluginOrderConfig->getConfig();

            if ($this->canValidateOrrder()) {
               echo $link . "<input type='submit' name='validate' value=\"" . 
                  $LANG['plugin_order']['validation'][9] . "\" class='submit'>";
               $link = "<br><br>";
            }

            if ($this->canCancelValidationRequest()) {
               echo $link . "<input type='submit' onclick=\"return confirm('" . 
                  $LANG['plugin_order']['detail'][39] . "')\" name='cancel_waiting_for_approval' value=\"" .
                     $LANG['plugin_order']['validation'][13] . "\" class='submit'>";
               $link = "<br><br>";
            }

            if ($this->canDoValidationRequest()) {
               echo $link . "<input type='submit' name='waiting_for_approval' value=\"" . 
                  $LANG['plugin_order']['validation'][11] . "\" class='submit'>";
               $link = "<br><br>";
            }

            if ($this->canUndoValidation()) {
               echo $link . "<input type='submit' onclick=\"return confirm('" . 
                  $LANG['plugin_order']['detail'][40] . "')\" name='undovalidation' value=\"" . 
                     $LANG['plugin_order']['validation'][17] . "\" class='submit'>";
               $link = "<br><br>";
            }

            echo "</td>";
            echo "</tr>";
         } else {
            echo "<tr class='tab_bg_2 center'><td>" . $LANG['plugin_order']['validation'][0] . "</td></tr>";
         }
         echo "</table></div></form>";
      }
   }
   
   function showGenerationForm($ID) {
      global $LANG,$CFG_GLPI;
      
      $pref = new PluginOrderPreference;
      $template=$pref->checkPreferenceTemplateValue(getLoginUserID());
      if ($template) {
         if ($this->canUpdateOrder($ID)) {
            echo "<form action='".$CFG_GLPI["root_doc"]."/plugins/order/front/export.php?id=".$ID.
               "' method=\"post\">";
            echo "<div align=\"center\"><table cellspacing=\"2\" cellpadding=\"2\">";

            echo "<tr>";
            echo "<td class='center'>";
            echo "<input type='submit' value=\"".$LANG['plugin_order']['generation'][1].
               "\" class='submit' ></div></td></tr>";
            echo "</td>";
            echo "</tr>";

            echo "</table></div></form>";
         }
      } else {
         echo "<div align='center'>".$LANG['plugin_order']['parser'][4]."</div>";
      }
   }
   
   function generateOrder($ID) {
      global $LANG,$DB;
      
      $pref = new PluginOrderPreference();
      $template=$pref->checkPreferenceTemplateValue(getLoginUserID());
      if ($template) {

         $config = array('PATH_TO_TMP' => GLPI_DOC_DIR . '/_tmp');

         $odf = new odf("../templates/$template", $config);
      
         $this->getFromDB($ID);
         
         $PluginOrderOrder_Item         = new PluginOrderOrder_Item();
         $PluginOrderReference_Supplier = new PluginOrderReference_Supplier();
         
         $odf->setImage('logo', '../logo/logo.jpg');
         
         $odf->setVars('title_order', $LANG['plugin_order']['generation'][12], true, 'UTF-8');
         $odf->setVars('num_order', $this->fields["num_order"], true, 'UTF-8');
         
         $odf->setVars('title_invoice_address', $LANG['plugin_order']['generation'][3], true, 
                       'UTF-8');
         
         $entity = new Entity();
         $entity->getFromDB($this->fields["entities_id"]);
         $entdata=new EntityData();
         $town = '';
            
         if ($this->fields["entities_id"]!=0)
            $name_entity = $entity->fields["name"];
         else
            $name_entity = $LANG['entity'][2];
            
         $odf->setVars('entity_name', $name_entity, true, 'UTF-8');
         if ($entdata->getFromDB($this->fields["entities_id"])) {
            $odf->setVars('entity_address', $entdata->fields["address"], true, 'UTF-8');
            $odf->setVars('entity_postcode', $entdata->fields["postcode"],true, 'UTF-8');
            $town = $entdata->fields["town"];
            $odf->setVars('entity_town', $town,true,'UTF-8');
            $odf->setVars('entity_country', $entdata->fields["country"], true, 'UTF-8');
         }
         
         $supplier = new Supplier();
         if ($supplier->getFromDB($this->fields["suppliers_id"])) {
            $odf->setVars('supplier_name', $supplier->fields["name"],true,'UTF-8');
            $odf->setVars('supplier_address', $supplier->fields["address"],true,'UTF-8');
            $odf->setVars('supplier_postcode', $supplier->fields["postcode"],true,'UTF-8');
            $odf->setVars('supplier_town', $supplier->fields["town"],true,'UTF-8');
            $odf->setVars('supplier_country', $supplier->fields["country"],true,'UTF-8');
         }
         
         $odf->setVars('title_delivery_address',$LANG['plugin_order']['generation'][4],true,'UTF-8');

         $tmpname=Dropdown::getDropdownName("glpi_locations",$this->fields["locations_id"],1);
         $comment=$tmpname["comment"];
         $odf->setVars('comment_delivery_address',html_clean($comment),true,'UTF-8');
         
         if ($town) {
            $town = $town. ", ";
         }
         $odf->setVars('title_date_order', $town.$LANG['plugin_order']['generation'][5]." ",true,'UTF-8');
         $odf->setVars('date_order', convDate($this->fields["order_date"]),true,'UTF-8');
         
         $odf->setVars('title_sender', $LANG['plugin_order']['generation'][10],true,'UTF-8');
         $odf->setVars('sender', html_clean(getUserName(getLoginUserID())),true,'UTF-8');
         
         $output='';
         $contact = new Contact();
         if ($contact->getFromDB($this->fields["contacts_id"])) {
            $output=formatUserName($contact->fields["id"], "", $contact->fields["name"], 
                                   $contact->fields["firstname"]);
         }
         $odf->setVars('title_recipient',$LANG['plugin_order']['generation'][11],true,'UTF-8');
         $odf->setVars('recipient',html_clean($output),true,'UTF-8');
         
         $odf->setVars('nb',$LANG['plugin_order']['generation'][6],true,'UTF-8');
         $odf->setVars('title_item',$LANG['plugin_order']['generation'][7],true,'UTF-8');
         $odf->setVars('title_ref',$LANG['plugin_order']['detail'][2],true,'UTF-8');
         $odf->setVars('HTPrice_item',$LANG['plugin_order']['generation'][8],true,'UTF-8');
         $odf->setVars('TVA_item',$LANG['plugin_order'][25],true,'UTF-8');
         $odf->setVars('title_discount',$LANG['plugin_order']['generation'][13],true,'UTF-8');
         $odf->setVars('HTPriceTotal_item',$LANG['plugin_order']['generation'][9],true,'UTF-8');
         $odf->setVars('ATIPriceTotal_item',$LANG['plugin_order'][14],true,'UTF-8');
         
         $listeArticles = array();
         
         $result=$PluginOrderOrder_Item->queryDetail($ID);
         $num=$DB->numrows($result);
         
         while ($data=$DB->fetch_array($result)){

            $quantity = $PluginOrderOrder_Item->getTotalQuantityByRefAndDiscount($ID, $data["id"], 
                                                                                $data["price_taxfree"],
                                                                                $data["discount"]);

            $listeArticles[]=array('quantity'         => $quantity,
                                   'ref'              => utf8_decode($data["name"]),
                                   'taxe'             => Dropdown::getDropdownName(getTableForItemType("PluginOrderOrderTaxe"), 
                                                                                      $data["plugin_order_ordertaxes_id"]), 
                                   'refnumber'        => $PluginOrderReference_Supplier->getReferenceCodeByReferenceAndSupplier($data["id"],
                                                                                                                                $this->fields["suppliers_id"]),
                                   'price_taxfree'    => $data["price_taxfree"],
                                   'discount'         => $data["discount"], false, 0,
                                   'price_discounted' => $data["price_discounted"]*$quantity,
                                   'price_ati'        => $data["price_ati"]);
         }
         
         $article = $odf->setSegment('articles');
         foreach($listeArticles AS $element) {
            $article->nbA($element['quantity']);
            $article->titleArticle($element['ref']);
            $article->refArticle($element['refnumber']);
            $article->TVAArticle($element['taxe']);
            $article->HTPriceArticle(html_clean(formatNumber($element['price_taxfree'])));
            if ($element['discount'] != 0) {
               $article->discount(html_clean(formatNumber($element['discount']))." %");
            } else {
               $article->discount("");
            }
            $article->HTPriceTotalArticle(html_clean(formatNumber($element['price_discounted'])));

            $total_TTC_Article = $element['price_discounted']*(1+($element['taxe']/100));
            $article->ATIPriceTotalArticle(html_clean(formatNumber($total_TTC_Article)));
            $article->merge();
         }

         $odf->mergeSegment($article);
         
         $prices = $PluginOrderOrder_Item->getAllPrices($ID);

         // total price (with postage)
         $postagewithTVA = 
            $PluginOrderOrder_Item->getPricesATI($this->fields["port_price"], 
                                                 Dropdown::getDropdownName("glpi_plugin_order_ordertaxes", 
                                                                           $this->fields["plugin_order_ordertaxes_id"]));

         $total_HT   = $prices["priceHT"]    + $this->fields["port_price"];
         $total_TVA  = $prices["priceTVA"]   + $postagewithTVA - $this->fields["port_price"];
         $total_TTC  = $prices["priceTTC"]   + $postagewithTVA;

         $odf->setVars('title_totalht',$LANG['plugin_order'][13],true,'UTF-8');
         $odf->setVars('totalht',html_clean(formatNumber($prices['priceHT'])),true,'UTF-8');
         
         $odf->setVars('title_port',$LANG['plugin_order'][15],true,'UTF-8');
         $odf->setVars('totalht_port_price',html_clean(formatNumber($total_HT)),true,'UTF-8');

         $odf->setVars('title_price_port',$LANG['plugin_order'][26],true,'UTF-8');
         $odf->setVars('price_port_tva'," (".Dropdown::getDropdownName("glpi_plugin_order_ordertaxes", 
                                    $this->fields["plugin_order_ordertaxes_id"])."%)",true,'UTF-8');
         $odf->setVars('port_price',html_clean(formatNumber($postagewithTVA)),true,'UTF-8');

         $odf->setVars('title_tva',$LANG['plugin_order'][25],true,'UTF-8');
         $odf->setVars('totaltva',html_clean(formatNumber($total_TVA)),true,'UTF-8');

         $odf->setVars('title_totalttc',$LANG['plugin_order'][14],true,'UTF-8');
         $odf->setVars('totalttc',html_clean(formatNumber($total_TTC)),true,'UTF-8');

         $odf->setVars('title_money',$LANG['plugin_order']['generation'][17],true,'UTF-8');
         $odf->setVars('title_sign',$LANG['plugin_order']['generation'][16],true,'UTF-8');
         
         $sign=$pref->checkPreferenceSignatureValue(getLoginUserID());
         if ($sign) {
            $odf->setImage('sign', '../signatures/'.$sign);
         } else {
            $odf->setImage('sign', '../pics/nothing.gif');
         }
         
         $odf->setVars('title_conditions',$LANG['plugin_order'][32],true,'UTF-8');
         $odf->setVars('payment_conditions',
                       Dropdown::getDropdownName("glpi_plugin_order_orderpayments", 
                                                 $this->fields["plugin_order_orderpayments_id"]),
                                                 true,'UTF-8');
         // We export the file
         $odf->exportAsAttachedFile();
      }
   }
   
   function transfer($ID,$entity) {
      global $DB;
      
      $PluginOrderOrder_Supplier = new PluginOrderOrder_Supplier;
      $PluginOrderReference      = new PluginOrderReference();
      $PluginOrderOrder_Item     = new PluginOrderOrder_Item();
      
      $this->getFromDB($ID);
      $input["id"]          = $ID;
      $input["entities_id"] = $entity;
      $this->update($input);
      
      if($PluginOrderOrder_Supplier->getFromDBByOrder($ID)) {
         $input["id"] = $PluginOrderOrder_Supplier->fields["id"];
         $input["entities_id"] = $entity;
         $PluginOrderOrder_Supplier->update($input);
      }
      $query="SELECT `plugin_order_references_id` FROM `glpi_plugin_order_orders_items`
               WHERE `plugin_order_orders_id` = '$ID' 
               GROUP BY plugin_order_references_id";
      
      $result = $DB->query($query);
      $num    = $DB->numrows($result);
      if ($num) {
         while ($detail=$DB->fetch_array($result)) {

            $ref = $PluginOrderReference->transfer($detail["plugin_order_references_id"],
                                                   $entity);
         }
      }
   }
   
   function getAllOrdersByBudget($budgets_id) {
      global $DB,$LANG,$CFG_GLPI;
      
      $query = "SELECT * 
               FROM `".$this->getTable()."` 
               WHERE `budgets_id` = '".$budgets_id."' 
               ORDER BY `entities_id`, `name` ";
      $result = $DB->query($query);

      echo "<div class='center'>";
      echo "<table class='tab_cadre_fixe'>";
      
      echo "<tr><th colspan='3'>".$LANG['plugin_order'][11]."</th></tr>";
      echo "<tr>"; 
      echo "<th>".$LANG['common'][16]."</th>";
      echo "<th>".$LANG['entity'][0]."</th>";
      echo "<th>".$LANG['plugin_order'][14]."</th>";
      echo "</tr>";
      
      $total = 0;
      while ($data = $DB->fetch_array($result)) {
         
         $PluginOrderOrder_Item = new PluginOrderOrder_Item();
         $prices = $PluginOrderOrder_Item->getAllPrices($data["id"]);
         $postagewithTVA = 
            $PluginOrderOrder_Item->getPricesATI($data["port_price"], 
                                                 Dropdown::getDropdownName("glpi_plugin_order_ordertaxes",
                                                                           $data["plugin_order_ordertaxes_id"]));
         $total +=  $prices["priceTTC"] + $postagewithTVA;
         
         echo "<tr class='tab_bg_1' align='center'>"; 
         echo "<td>";

         $link = getItemTypeFormURL($this->getType());
         if ($this->canView()) {
            echo "<a href=\"".$link."?id=".$data["id"]."\">".$data["name"]."</a>";
         } else {
            echo $data["name"];  
         }
         echo "</td>";

         echo "<td>";
         echo Dropdown::getDropdownName("glpi_entities",$data["entities_id"]);
         echo "</td>";
         
         echo "<td>";
         echo formatnumber($prices["priceTTC"] + $postagewithTVA);
         echo "</td>";
         
         echo "</tr>"; 
         
      }
      echo "</table></div>";
      
      echo "<br><div class='center'>";
      echo "<table class='tab_cadre' width='15%'>";
      echo "<tr class='tab_bg_2'><td>" . $LANG['plugin_order'][12] . ": </td>";
      echo "<td>";
      echo formatNumber($total) . "</td>";
      echo "</tr>";
      echo "</table></div>";

   }
}
?>