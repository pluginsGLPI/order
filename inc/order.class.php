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
 along with GLPI; along with Behaviors. If not, see <http://www.gnu.org/licenses/>.
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

class PluginOrderOrder extends CommonDBTM {
   
   static $types = array(
         'Computer', 'Monitor', 'NetworkEquipment', 'Peripheral', 'Printer',
                           'Phone', 'ConsumableItem', 'CartridgeItem', 'Contract',
                           'PluginOrderOther', 'SoftwareLicense');
         
   public $dohistory = true;
   public $forward_entity_to = array("PluginOrderOrder_Item");
   
   const ORDER_DEVICE_NOT_DELIVRED        = 0;
   const ORDER_DEVICE_DELIVRED            = 1;
   
   // Const Budget
   const ORDER_IS_OVER_BUDGET  = 1;
   const ORDER_IS_EQUAL_BUDGET = 2;
   const ORDER_IS_UNDER_BUDGET = 3;

   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_order']['title'][1];
   }
   
   function getState() {
      return $this->fields["plugin_order_orderstates_id"];
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
   
   function isDraft() {
      $config = PluginOrderConfig::getConfig();
      return ($this->getState() == $config->getDraftState());
   }

   function isWaitingForApproval() {
      $config = PluginOrderConfig::getConfig();
      return ($this->getState() == $config->getWaitingForApprovalState());
   }

   function isApproved() {
      $config = PluginOrderConfig::getConfig();
      return ($this->getState() == $config->getApprovedState());
   }

   function isPartiallyDelivered() {
      $config = PluginOrderConfig::getConfig();
      return ($this->getState() == $config->getPartiallyDeliveredState());
   }

   function isDelivered() {
      $config = PluginOrderConfig::getConfig();
      return ($this->getState() == $config->getDeliveredState());
   }

   function isCanceled() {
      $config = PluginOrderConfig::getConfig();
      return ($this->getState() == $config->getCanceledState());
   }

   function isPaid() {
      $config = PluginOrderConfig::getConfig();
      return ($this->getState() == $config->getPaidState());
   }
   
   function cleanDBonPurge() {

      $temp = new PluginOrderOrder_Item();
      $temp->deleteByCriteria(array('plugin_order_orders_id' => $this->fields['id']));

   }
   
   function canUpdateOrder() {
      if (!$this->getID()) {
         return true;
      } else {
         return ($this->isDraft() || $this->isWaitingForApproval());
      }
   }
   
   function canDisplayValidationForm($orders_id) {

      $this->getFromDB($orders_id);

      //If it's an order creation -> do not display form
      if (!$orders_id) {
         return false;
      } else {
         return ($this->canValidateOrder() 
                  || $this->canUndoValidation() 
                     || $this->canCancelOrder());
      }
   }
   
   function canValidateOrder() {
      $config = PluginOrderConfig::getConfig();
      
      //If no validation process -> can validate if order is in draft state
      if (!$config->useValidation()) {
         return $this->isDraft();
      } else {
         //Validation process is used

         //If order is canceled, cannot validate !
         if ($this->isCanceled()) {
            return false;
         }

         //If no right to validate
         if (!$this->canValidate()) {
            return false;
         } else {
            return ($this->isDraft() || $this->isWaitingForApproval());
         }
      }
   }

   function canCancelOrder() {
      //If order is canceled or if no right to cancel!
      if ($this->isCanceled() || !$this->canCancel()) {
         return false;
      }
      return true;
   }

   function canDoValidationRequest() {
      $config = PluginOrderConfig::getConfig();
      
      if (!$config->useValidation()) {
         return false;
      } else {
         return $this->isDraft();
      }
   }

   function canCancelValidationRequest() {
      return $this->isWaitingForApproval();
   }

   function canUndoValidation() {
      //If order is canceled, cannot validate !
      if ($this->isCanceled()) {
         return false;
         
      }

      //If order is not validate, cannot undo validation !
      if ($this->isDraft() || $this->isWaitingForApproval()) {
         return false;
         
      }

      //If no right to cancel
      return ($this->canUndo());
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if ($item->getType()=='Budget') {
            return $LANG['plugin_order']['title'][1];
      } else if ($item->getType()==__CLASS__) {
         
         $config = PluginOrderConfig::getConfig();
         
         $ong[1] = $LANG['plugin_order'][5];
         
         if ($config->canGenerateOrderPDF() && $item->getState() > PluginOrderOrderState::DRAFT) {
         // generation
            $ong[2] = $LANG['plugin_order']['generation'][2];
         
         }

         return $ong;
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      
      $order = new self();
      
      if ($item->getType()=='Budget') {
      
         $order->getAllOrdersByBudget($item->getField('id'));
         
      } else if ($item->getType() == __CLASS__) {
         switch ($tabnum) {
            case 1 :
               $item->showValidationForm($item->getID());
               break;

            case 2 :
               $item->showGenerationForm($item->getID());
               break;

         }
      }
      return true;
   }
   
   /*define header form */
   function defineTabs($options=array()) {
      global $LANG;

      $ong = array();
      $config = PluginOrderConfig::getConfig();
      
      $this->addStandardTab('PluginOrderOrder_Item', $ong,$options);

      $this->addStandardTab('PluginOrderOrder', $ong,$options);
      if ($config->canUseSupplierInformations() && $this->fields['suppliers_id']) {
         $this->addStandardTab('PluginOrderOrder_Supplier', $ong, $options);
      }
      if ($this->getState() != PluginOrderOrderState::DRAFT) {
         if ($this->checkIfDetailExists($this->getID(), true)) {
            $this->addStandardTab('PluginOrderReception', $ong, $options);
         }
         $this->addStandardTab('PluginOrderLink', $ong, $options);
         $this->addStandardTab('PluginOrderBill', $ong, $options);
      }
      if ($config->canUseSupplierSatisfaction() 
            && $this->getState() == PluginOrderOrderState::DELIVERED) {
            $this->addStandardTab('PluginOrderSurveySupplier', $ong, $options);
            
      }
      $this->addStandardTab('Document',$ong,$options);
      $this->addStandardTab('Note',$ong,$options);
      $this->addStandardTab('Log',$ong,$options);
      
      return $ong;
   }
   
   function getSearchOptions() {
      global $LANG;

      $tab = array();
    
      $tab['common'] = $LANG['plugin_order']['title'][1];

      /* order_number */
      $tab[1]['table']       = $this->getTable();
      $tab[1]['field']       = 'num_order';
      $tab[1]['name']        = $LANG['plugin_order'][0];
      $tab[1]['datatype']    = 'itemlink';
      $tab[1]['checktype']   = 'text';
      $tab[1]['displaytype'] = 'text';
      $tab[1]['injectable']  = true;

      /* order_date */
      $tab[2]['table']       = $this->getTable();
      $tab[2]['field']       = 'order_date';
      $tab[2]['name']        = $LANG['plugin_order'][1];
      $tab[2]['datatype']    = 'date';
      $tab[2]['checktype']   = 'date';
      $tab[2]['displaytype'] = 'date';
      $tab[2]['injectable']  = true;

      /* taxes*/
      $tab[3]['table']       = 'glpi_plugin_order_ordertaxes';
      $tab[3]['field']       = 'name';
      $tab[3]['name']        = $LANG['plugin_order'][25] . " " . $LANG['plugin_order'][26];
      $tab[3]['checktype']   = 'text';
      $tab[3]['displaytype'] = 'dropdown';
      $tab[3]['injectable']  = true;

      /* location */
      $tab[4]['table']       = 'glpi_locations';
      $tab[4]['field']       = 'completename';
      $tab[4]['name']        = $LANG['plugin_order'][40];
      $tab[4]['checktype']   = 'text';
      $tab[4]['displaytype'] = 'dropdown';
      $tab[4]['injectable']  = true;

      /* status */
      $tab[5]['table']       = 'glpi_plugin_order_orderstates';
      $tab[5]['field']       = 'name';
      $tab[5]['name']        = $LANG['plugin_order']['status'][0];
      $tab[5]['checktype']   = 'text';
      $tab[5]['displaytype'] = 'dropdown';
      $tab[5]['injectable']  = true;

      /* supplier */
      $tab[6]['table']         = 'glpi_suppliers';
      $tab[6]['field']         = 'name';
      $tab[6]['name']          = $LANG['financial'][26];
      $tab[6]['datatype']      = 'itemlink';
      $tab[6]['itemlink_type'] = 'Supplier';
      $tab[6]['forcegroupby']  = true;
      $tab[6]['checktype']     = 'text';
      $tab[6]['displaytype']   = 'dropdown';
      $tab[6]['injectable']    = true;

      /* payment */
      $tab[7]['table']         = 'glpi_plugin_order_orderpayments';
      $tab[7]['field']         = 'name';
      $tab[7]['name']          = $LANG['plugin_order'][32];
      $tab[7]['checktype']     = 'text';
      $tab[7]['displaytype']   = 'dropdown';
      $tab[7]['injectable']    = true;

      /* contact */
      $tab[8]['table']         = 'glpi_contacts';
      $tab[8]['field']         = 'completename';
      $tab[8]['name']          = $LANG['common'][18];
      $tab[8]['datatype']      = 'itemlink';
      $tab[8]['itemlink_type'] = 'Contact';
      $tab[8]['forcegroupby']  = true;
      $tab[8]['checktype']     = 'text';
      $tab[8]['displaytype']   = 'dropdown';
      $tab[8]['injectable']    = true;

      /* budget */
      $tab[9]['table']         = 'glpi_budgets';
      $tab[9]['field']         = 'name';
      $tab[9]['name']          = $LANG['financial'][87];
      $tab[9]['datatype']      = 'itemlink';
      $tab[9]['itemlink_type'] = 'Budget';
      $tab[9]['forcegroupby']  = true;
      $tab[9]['checktype']     = 'text';
      $tab[9]['displaytype']   = 'dropdown';
      $tab[9]['injectable']    = true;

      /* title */
      $tab[10]['table']        = $this->getTable();
      $tab[10]['field']        = 'name';
      $tab[10]['name']         = $LANG['plugin_order'][39];
      $tab[10]['checktype']    = 'text';
      $tab[10]['displaytype']  = 'text';
      $tab[10]['injectable']   = true;

      /* type */
      $tab[11]['table']        = 'glpi_plugin_order_ordertypes';
      $tab[11]['field']        = 'name';
      $tab[11]['name']         = $LANG['common'][17];
      $tab[11]['checktype']    = 'text';
      $tab[11]['displaytype']  = 'dropdown';
      $tab[11]['injectable']   = true;

      /* order_date */
      $tab[12]['table']        = $this->getTable();
      $tab[12]['field']        = 'duedate';
      $tab[12]['name']         = $LANG['plugin_order'][50];
      $tab[12]['datatype']     = 'date';
      $tab[12]['checktype']    = 'date';
      $tab[12]['displaytype']  = 'date';
      $tab[12]['injectable']   = true;

      /* order_date */
      $tab[13]['table']        = $this->getTable();
      $tab[13]['field']        = 'deliverydate';
      $tab[13]['name']         = $LANG['plugin_order'][53];
      $tab[13]['datatype']     = 'date';
      $tab[13]['checktype']    = 'date';
      $tab[13]['displaytype']  = 'date';
      $tab[13]['injectable']   = true;

      /* order_date */
      $tab[14]['table']        = $this->getTable();
      $tab[14]['field']        = 'is_late';
      $tab[14]['name']         = $LANG['plugin_order']['status'][20];
      $tab[14]['datatype']     = 'bool';
      $tab[14]['checktype']    = 'bool';
      $tab[14]['displaytype']  = 'bool';
      $tab[14]['injectable']   = true;

      /* comments */
      $tab[16]['table']        = $this->getTable();
      $tab[16]['field']        = 'comment';
      $tab[16]['name']         = $LANG['plugin_order'][2];
      $tab[16]['datatype']     = 'text';
      $tab[16]['checktype']    = 'text';
      $tab[16]['displaytype']  = 'multiline_text';
      $tab[16]['injectable']   = true;

      /* port price */
      $tab[17]['table']        = $this->getTable();
      $tab[17]['field']        = 'port_price';
      $tab[17]['name']         = $LANG['plugin_order'][26];
      $tab[17]['datatype']     = 'decimal';
      $tab[17]['checktype']    = 'text';
      $tab[17]['displaytype']  = 'text';
      $tab[17]['injectable']   = true;

      /* id */
      $tab[30]['table']       = $this->getTable();
      $tab[30]['field']       = 'id';
      $tab[30]['name']        = $LANG['common'][2];
      $tab[30]['injectable']  = false;

      /* entity */
      $tab[80]['table']       = 'glpi_entities';
      $tab[80]['field']       = 'completename';
      $tab[80]['name']        = $LANG['entity'][0];
      $tab[80]['injectable']  = false;

      $tab[86]['table']         = $this->getTable();
      $tab[86]['field']         = 'is_recursive';
      $tab[86]['name']          = $LANG['entity'][9];
      $tab[86]['datatype']      = 'bool';
      $tab[86]['massiveaction'] = false;
      $tab[86]['checktype']     = 'bool';
      $tab[86]['displaytype']   = 'bool';
      $tab[86]['injectable']    = true;

      return $tab;
   }

   function prepareInputForAdd($input) {
      global $LANG;
      
      if (!isset ($input["num_order"]) || $input["num_order"] == '') {
         Session::addMessageAfterRedirect($LANG['plugin_order'][44], false, ERROR);
         return array ();
      } elseif (!isset ($input["name"]) || $input["name"] == '') {
         $input["name"] = $input["num_order"];
      }

      if( isset($input['budgets_id']) && $input['budgets_id'] > 0) {
         if( !self::canStillUseBudget($input) ) {
            Session::addMessageAfterRedirect($LANG['plugin_order'][49], false, ERROR);
         }
      }

      return $input;
   }
   
   function prepareInputForUpdate($input) {
      global $LANG;
      
      if( (isset($input['budgets_id']) 
         && $input['budgets_id'] > 0)
            || (isset($input['budgets_id']) 
               && $this->fields['budgets_id'] != $input['budgets_id']) ) {
         if( !self::canStillUseBudget($input) && !isset($input['_unlink_budget'])) {
            Session::addMessageAfterRedirect($LANG['plugin_order'][49], false, ERROR);
         }
      }
      
      //Check is order is late or not
      if (!isset($input['is_late']) && $this->shouldBeAlreadyDelivered()) {
         $this->setIsLate();
      }
      return $input;
   }

   function setIsLate() {
      $this->update(array('id' => $this->getID(), 'is_late' => 1));
   }
   
   /**
    * 
    */
   function shouldBeAlreadyDelivered($check_all_status = false) {
      if ($check_all_status || $this->isApproved() || $this->isPartiallyDelivered()) {
         if (!is_null($this->fields['duedate']) && $this->fields['duedate'] != '' 
            && (new DateTime($this->fields['duedate']) < new DateTime())) {
            return true;
            
         } else {
            return false;
         }
         
      } else {
         return false;
      }
   }
   
   function showForm ($ID, $options=array()) {
      global $CFG_GLPI, $LANG;
      $config = PluginOrderConfig::getConfig();

      if (!$this->canView()) {
         return false;
      }

      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w');
         $this->getEmpty();
      }

      $canedit = ($this->canUpdateOrder() && $this->can($ID, 'w') && !$this->isCanceled());
      $options['canedit'] = $canedit;
      
      // Displaying OVER BUDGET ALERT
      if( $this->fields['budgets_id'] > 0 ) {
            self::displayAlertOverBudget(self::isOverBudget($ID));
      }

      $this->showTabs($options);
      $this->showFormHeader($options);

      //Display without inside table
      /* title */
      echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_order'][39] . ": </td>";
      echo "<td>";
      if ($canedit) {
         Html::autocompletionTextField($this, "name");
         
      } else {
         echo $this->fields["name"];
      }
      echo "</td>";
      /* date of order */
      echo "<td>" . $LANG['plugin_order'][1] . "*:</td><td>";
      if ($canedit)  {
         if ($this->fields["order_date"] == NULL) {
            Html::showDateFormItem("order_date", date("Y-m-d"), true, true);
            
         } else {
            Html::showDateFormItem("order_date", $this->fields["order_date"], true, true);
         }
         
      } else {
         echo Html::convDate($this->fields["order_date"]);
      }
      echo "</td></tr>";

      /* num order */
      echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_order'][0] . "*: </td>";
      echo "<td>";
      if ($canedit) {
         Html::autocompletionTextField($this, "num_order");
         
      } else {
         echo $this->fields["num_order"];
      }
      echo "</td>";
      /* type order */
      echo "<td>" . $LANG['common'][17] . ": </td><td>";
      if ($canedit){
         Dropdown::show('PluginOrderOrderType', 
                        array('name'  => "plugin_order_ordertypes_id",
                              'value' => $this->fields["plugin_order_ordertypes_id"]));
      } else {
         echo Dropdown::getDropdownName("glpi_plugin_order_ordertypes", 
                                        $this->fields["plugin_order_ordertypes_id"]);
      }
      echo "</td></tr>";

      /* state */
      echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_order']['status'][0] . ": </td>";
      echo "<td>";
      if (!$this->getID()) {
         $state = $config->getDraftState();
         
      } else {
         $state = $this->fields["plugin_order_orderstates_id"];
      }
      if ($canedit) {
         Dropdown::show('PluginOrderOrderState', 
                        array('name'   => "plugin_order_orderstates_id",
                              'value'  => $state));
                              
      } else {
         echo Dropdown::getDropdownName("glpi_plugin_order_orderstates", 
                                        $this->getState());
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
         $budget = new Budget();
         if ($this->fields["budgets_id"] > 0 
            && $budget->can($this->fields["budgets_id"], 'r')) {
            echo "<a href='".$budget->getLinkURL()."'>".$budget->getName(1)."</a>";
         } else {
            echo Dropdown::getDropdownName("glpi_budgets", $this->fields["budgets_id"]);
         }
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

      /* port price */
      echo "<td>".$LANG['plugin_order'][26].": </td>";
      echo "<td>";
      if ($canedit) {
         echo "<input type='text' name='port_price' value=\"".
            Html::formatNumber($this->fields["port_price"], true)."\" size='5'>";
            
      } else {
         echo Html::formatNumber($this->fields["port_price"]);
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
      
      /* tva port price */
      echo "<td>" . $LANG['plugin_order'][25] . " " . $LANG['plugin_order'][26] . ": </td><td>";
      $PluginOrderConfig = new PluginOrderConfig();
      $default_taxes     = $PluginOrderConfig->getDefaultTaxes();

      if (empty ($ID) || $ID < 0) {
         $taxes = $default_taxes;
         
      } else {
         $taxes = $this->fields["plugin_order_ordertaxes_id"];
      }
      if ($canedit) {
         Dropdown::show('PluginOrderOrderTaxe', 
                        array('name'                => "plugin_order_ordertaxes_id", 
                              'value'               => $taxes,
                              'display_emptychoice' => true,
                              'emptylabel'          => $LANG['plugin_order']['config'][20]));
                              
      } else {
         echo Dropdown::getDropdownName("glpi_plugin_order_ordertaxes", $taxes);
      }
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'><td colspan='2'></td><td>";
      echo $LANG['plugin_order'][50].":";
      if ($this->isDelivered() && $this->fields['deliverydate']) {
         echo "<br/>".$LANG['plugin_order'][53].":";
      }
      echo " </td><td>";
      if ($canedit)  {
         if ($this->fields["duedate"] == NULL) {
            Html::showDateFormItem("duedate", '', true, true);
            
         } else {
            Html::showDateFormItem("duedate", $this->fields["duedate"], true, true);
         }
         
      } else {
         echo Html::convDate($this->fields["duedate"]);
      }
      if ($this->shouldBeAlreadyDelivered()) {
         echo "<br/><span class='red'>".$LANG['plugin_order'][51]."</span>";
         
      }
      if ($this->isDelivered() && $this->fields['deliverydate']) {
         echo "<br/>".Html::convDate($this->fields['deliverydate']);
      } 
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'><td>";
      //comments of order
      echo $LANG['plugin_order'][2] . ":  </td>";
      echo "<td>";
      if ($canedit) {
         echo "<textarea cols='50' rows='4' name='comment'>" . $this->fields["comment"] . 
            "</textarea>";
            
      } else {
         echo $this->fields["comment"];
      }
      echo "</td>";

      /* total price (without taxes) */
      
      /* status of bill */
      echo "<td colspan=\"2\" style=\"width:40%;\">";
      if ($ID > 0) {
         $PluginOrderOrder_Item = new PluginOrderOrder_Item();
         $prices = $PluginOrderOrder_Item->getAllPrices($ID);

         echo $LANG['plugin_order'][13] . " : ";
         echo Html::formatNumber($prices["priceHT"]) . "<br />";
     
         // total price (with postage)
         $postagewithTVA = 
            $PluginOrderOrder_Item->getPricesATI($this->fields["port_price"], 
                                                 Dropdown::getDropdownName("glpi_plugin_order_ordertaxes", 
                                                                           $this->fields["plugin_order_ordertaxes_id"]));

         echo $LANG['plugin_order'][15] . " : ";
         $priceHTwithpostage = $prices["priceHT"] + $this->fields["port_price"];
         echo Html::formatNumber($priceHTwithpostage) . "<br />";
         
         // total price (with taxes)
         echo $LANG['plugin_order'][14] . " : ";
         $total = $prices["priceTTC"] + $postagewithTVA;
         echo Html::formatNumber($total) . "<br />";
         
         // total TVA
         echo "(" . $LANG['plugin_order'][25] . " : ";
         $total_tva = $prices["priceTVA"] + ($postagewithTVA- $this->fields["port_price"]);
         echo Html::formatNumber($total_tva) . ")</td>";
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
      $where.=getEntitiesRestrictRequest("AND", "glpi_suppliers",'',$entity_restrict,true);

      $query="SELECT `glpi_suppliers`.* FROM `glpi_suppliers`
              LEFT JOIN `glpi_contacts_suppliers` 
                 ON (`glpi_contacts_suppliers`.`suppliers_id` = `glpi_suppliers`.`id`)
              $where
              GROUP BY `glpi_suppliers`.`id`
              ORDER BY `entities_id`, `name`";

      $result=$DB->query($query);

      echo "<select name='suppliers_id' id='suppliers_id'>\n";
      echo "<option value='0'>".Dropdown::EMPTY_VALUE."</option>\n";

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
            " title=\"".Html::cleanInputText($output)."\">".
               substr($output, 0, $CFG_GLPI["dropdown_chars_limit"])."</option>";
      }
      if ($prev>=0) {
         echo "</optgroup>";
      }
      echo "</select>\n";

      $params=array('suppliers_id' => '__VALUE__', 'entity_restrict' => $entity_restrict,
                    'rand' => $rand, 'myname' => $myname);

      Ajax::updateItemOnSelectEvent("suppliers_id", "show_contacts_id", 
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
      $where.=getEntitiesRestrictRequest("AND", "glpi_contacts", '', $entity_restrict, true);

      $query = "SELECT `glpi_contacts`.*
               FROM `glpi_contacts`,`glpi_contacts_suppliers`
               $where
               ORDER BY `entities_id`, `name`";
               
      $result = $DB->query($query);

      echo "<select name=\"contacts_id\">";

      echo "<option value=\"0\">".Dropdown::EMPTY_VALUE."</option>";

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
               " title=\"".Html::cleanInputText($output)."\">".
                  substr($output, 0, $CFG_GLPI["dropdown_chars_limit"])."</option>";
         }
         if ($prev>=0) {
            echo "</optgroup>";
         }
      }
      echo "</select>";
   }
   
   function addStatusLog($orders_id, $status, $comments = '') {
      global $LANG;

      $changes = Dropdown::getDropdownName("glpi_plugin_order_orderstates", $status);

      if ($comments != '') {
         $changes .= " : ".$comments;
      }

      $this->addHistory($this->getType(), '', $changes, $orders_id);

   }
   
   function updateOrderStatus($orders_id, $status, $comments = '') {
      global $CFG_GLPI;

      $config = PluginOrderConfig::getConfig();
      
      $input["plugin_order_orderstates_id"] = $status;
      $input["id"]                          = $orders_id;
      $this->dohistory                      = false;
      if ($status == $config->getDeliveredState()) {
         $input['deliverydate'] = $_SESSION['glpi_currenttime'];
      }
      $this->update($input);
      $this->addStatusLog($orders_id, $status, $comments);

      $this->dohistory = true;
      $notify          = true;
      $event           = "";

      if ($CFG_GLPI["use_mailing"]) {
         
         switch ($status) {
            case $config->getApprovedState():
               $event = "validation";
               break;
            case $config->getWaitingForApprovalState():
               $event = "ask";
               break;
            case $config->getCanceledState();
               $event = "cancel";
               break;
            case $config->getDraftState():
               $event = "undovalidation";
               break;
            default:
               $notify = false;
               break;
         }
         if ($notify) {
            NotificationEvent::raiseEvent($event, $this, array('comments' => $comments));

         }

      }

      
      return true;
   }
   
   function addHistory($type, $old_value='',$new_value='',$ID){
      $changes[0] = 0;
      $changes[1] = $old_value;
      $changes[2] = $new_value;
      Log::history($ID, $type, $changes, 0, Log::HISTORY_LOG_SIMPLE_MESSAGE);
   }

   function needValidation($ID) {
      if ($ID > 0 && $this->getFromDB($ID)) {
         return ($this->isDraft() || $this->isWaitingForApproval());
      } else {
         return false;
      }
   }
   
   function deleteAllLinkWithItem($orders_id) {

      $detail = new PluginOrderOrder_Item;
      $devices = getAllDatasFromTable("glpi_plugin_order_orders_items", 
                                      "`plugin_order_orders_id`='$orders_id'");
      foreach ($devices as $deviceID => $device) {
         $detail->delete(array ("id" => $deviceID));
      }
   }
   
   function checkIfDetailExists($orders_id, $only_delivered = false) {
      
      if ($orders_id) {
         $detail  = new PluginOrderOrder_Item();
         $where = "`plugin_order_orders_id`='$orders_id'";
         if ($only_delivered) {
            $where.= " AND `plugin_order_deliverystates_id` > 0";
         }
         $devices = getAllDatasFromTable("glpi_plugin_order_orders_items", $where);
         return (!empty($devices));
         
      } else {
         return false;
      }
   }
   
   function showValidationForm($orders_id) {
      global $LANG;
      
      $this->getFromDB($orders_id);

      echo "<form method='post' name='form' action=\"".Toolbox::getItemTypeFormURL('PluginOrderOrder')."\">";
      echo "<div align='center'><table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_2'><th colspan='3'>" . 
         $LANG['plugin_order']['validation'][6] . "</th></tr>";

      if ($this->can($orders_id, 'w') && $this->canDisplayValidationForm($orders_id)) {
         
         if ($this->checkIfDetailExists($orders_id)) {

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

            if ($this->canValidateOrder()) {
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
            echo "<tr class='tab_bg_2 center'><td>" 
               . $LANG['plugin_order']['validation'][0] . "</td></tr>";
         }
      }
      echo "</table></div></form>";
   }
   
   function showGenerationForm($ID) {
      global $LANG,$CFG_GLPI;

      echo "<form action='".$CFG_GLPI["root_doc"]."/plugins/order/front/export.php?id=".$ID.
          "&display_type=".PDF_OUTPUT_LANDSCAPE."' method=\"post\">";
      echo "<div align=\"center\"><table class='tab_cadre_fixe'>";
      echo "<tr><th>".$LANG['plugin_order']['generation'][1]."</th></tr>";
      
      $pref     = new PluginOrderPreference();
      $template = $pref->checkPreferenceTemplateValue(Session::getLoginUserID());
      if ($template) {
         if ($this->getState() > PluginOrderOrderState::DRAFT) {
            echo "<tr class='tab_bg_1'>";
            echo "<td class='center'>";
            echo "<input type='submit' value=\"".$LANG['plugin_order']['generation'][1].
               "\" class='submit' ></div></td></tr>";
            echo "</td>";
            echo "</tr>";

         }
      } else {
         echo "<tr class='tab_bg_1'>";
         echo "<td class='center'>"; 
         echo "<a href='".$CFG_GLPI['root_doc']."/front/preference.php?forcetab=order_1'>";
         echo $LANG['plugin_order']['parser'][4]."</a></td></tr>";
      }
 
      echo "</table></div></form>";
   }
   
   function generateOrder($ID) {
      global $LANG,$DB;
      
      $pref = new PluginOrderPreference();
      $template=$pref->checkPreferenceTemplateValue(Session::getLoginUserID());
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
         $odf->setVars('comment_delivery_address',Html::clean($comment),true,'UTF-8');
         
         if ($town) {
            $town = $town. ", ";
         }
         $odf->setVars('title_date_order', $town.$LANG['plugin_order']['generation'][5]." ",true,'UTF-8');
         $odf->setVars('date_order', Html::convDate($this->fields["order_date"]),true,'UTF-8');
         
         $odf->setVars('title_sender', $LANG['plugin_order']['generation'][10],true,'UTF-8');
         $odf->setVars('sender', Html::clean(getUserName(Session::getLoginUserID())),true,'UTF-8');
         
         $output='';
         $contact = new Contact();
         if ($contact->getFromDB($this->fields["contacts_id"])) {
            $output=formatUserName($contact->fields["id"], "", $contact->fields["name"], 
                                   $contact->fields["firstname"]);
         }
         $odf->setVars('title_recipient',$LANG['plugin_order']['generation'][11],true,'UTF-8');
         $odf->setVars('recipient',Html::clean($output),true,'UTF-8');
         
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
            $article->HTPriceArticle(Html::clean(Html::formatNumber($element['price_taxfree'])));
            if ($element['discount'] != 0) {
               $article->discount(Html::clean(Html::formatNumber($element['discount']))." %");
            } else {
               $article->discount("");
            }
            $article->HTPriceTotalArticle(Html::clean(Html::formatNumber($element['price_discounted'])));

            $total_TTC_Article = $element['price_discounted']*(1+($element['taxe']/100));
            $article->ATIPriceTotalArticle(Html::clean(Html::formatNumber($total_TTC_Article)));
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
         $odf->setVars('totalht',Html::clean(Html::formatNumber($prices['priceHT'])),true,'UTF-8');
         
         $odf->setVars('title_port',$LANG['plugin_order'][15],true,'UTF-8');
         $odf->setVars('totalht_port_price',Html::clean(Html::formatNumber($total_HT)),true,'UTF-8');

         $odf->setVars('title_price_port',$LANG['plugin_order'][26],true,'UTF-8');
         $odf->setVars('price_port_tva'," (".Dropdown::getDropdownName("glpi_plugin_order_ordertaxes", 
                                    $this->fields["plugin_order_ordertaxes_id"])."%)",true,'UTF-8');
         $odf->setVars('port_price',Html::clean(Html::formatNumber($postagewithTVA)),true,'UTF-8');

         $odf->setVars('title_tva',$LANG['plugin_order'][25],true,'UTF-8');
         $odf->setVars('totaltva',Html::clean(Html::formatNumber($total_TVA)),true,'UTF-8');

         $odf->setVars('title_totalttc',$LANG['plugin_order'][14],true,'UTF-8');
         $odf->setVars('totalttc',Html::clean(Html::formatNumber($total_TTC)),true,'UTF-8');

         $odf->setVars('title_money',$LANG['plugin_order']['generation'][17],true,'UTF-8');
         $odf->setVars('title_sign',$LANG['plugin_order']['generation'][16],true,'UTF-8');
         
         $sign=$pref->checkPreferenceSignatureValue(Session::getLoginUserID());
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
   
   function transfer($ID, $entity) {
      global $DB;
      
      $supplier   = new PluginOrderOrder_Supplier();
      $reference  = new PluginOrderReference();
      
      $this->getFromDB($ID);
      $input["id"]          = $ID;
      $input["entities_id"] = $entity;
      $this->update($input);
      
      if($supplier->getFromDBByOrder($ID)) {
         $input["id"]          = $supplier->fields["id"];
         $input["entities_id"] = $entity;
         $supplier->update($input);
      }
      
      $query = "SELECT `plugin_order_references_id` FROM `glpi_plugin_order_orders_items`
                WHERE `plugin_order_orders_id` = '$ID' 
                GROUP BY plugin_order_references_id";
      
      $result = $DB->query($query);
      $num    = $DB->numrows($result);
      if ($num) {
         while ($detail=$DB->fetch_array($result)) {
            $ref = $reference->transfer($detail["plugin_order_references_id"], $entity);
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
      
      echo "<tr><th colspan='4'>".$LANG['plugin_order'][11]."</th></tr>";
      echo "<tr>"; 
      echo "<th style='width:15%;'>".$LANG['rulesengine'][7]."</th>";
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
         
         $link = Toolbox::getItemTypeFormURL($this->getType());
         
         echo "<tr class='tab_bg_1' align='center'>"; 
         echo "<td>";
            echo "<a href=\"".$link."?unlink_order=unlink_order&id=".$data["id"]."\">".$LANG['plugin_order'][52]."</a>";
         echo "</td>";
         echo "<td>";

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
         echo Html::formatnumber($prices["priceTTC"] + $postagewithTVA);
         echo "</td>";
         
         echo "</tr>"; 
         
      }
      echo "</table></div>";
      
      echo "<br><div class='center'>";
      echo "<table class='tab_cadre' width='15%'>";
      echo "<tr class='tab_bg_2'><td>" . $LANG['plugin_order'][12] . ": </td>";
      echo "<td>";
      echo Html::formatNumber($total) . "</td>";
      echo "</tr>";
      echo "</table></div>";

   }
   
   function canStillUseBudget($input){
         $budget = new Budget();
         $budget->getFromDB($input['budgets_id']);

         if( !($input['order_date'] > $budget->getField('begin_date') 
                  && $input['order_date'] < $budget->getField('end_date'))) {
            return false;
         }

      return true;
   }
   
   static function updateBillState($ID) {
      $all_paid   = true;
      $order_items = getAllDatasFromTable(getTableForItemType('PluginOrderOrder_Item'), 
                                          "`plugin_order_orders_id`='$ID'");
      foreach ($order_items as $item) {
         if ($item['plugin_order_billstates_id'] == PluginOrderBillState::NOTPAID) {
            $all_paid = false;
         }
      }
      
      $order = new self();
      $order->getFromDB($ID);
      if($all_paid) {
         $state = PluginOrderBillState::PAID;
      } else {
         $state = PluginOrderBillState::NOTPAID;
      }
      $order->update(array('id' => $ID, 'plugin_order_billstates_id' => $state));
   }
   
   function isOverBudget($ID){
      global $DB;

      // Compute all prices for BUDGET
      $query = "SELECT * 
               FROM `".$this->getTable()."` 
               WHERE `budgets_id` = '".$this->fields['budgets_id']."'";

      $total_HT = 0;
      foreach($DB->request($query) as $data) {
         $item      = new PluginOrderOrder_Item();
         $prices    = $item->getAllPrices($data['id']);
         $total_HT += $prices["priceHT"] + $data['port_price'];
      }

      // Get BUDGET
      $budget = new Budget();
      $budget->getFromDB($this->fields['budgets_id']);

      // Compare BUDGET value to TOTAL_HT value
      if( $total_HT > $budget->getField('value') ) {
         return PluginOrderOrder::ORDER_IS_OVER_BUDGET;

      }elseif( $total_HT == $budget->getField('value') ) {
         return PluginOrderOrder::ORDER_IS_EQUAL_BUDGET;

      }else{
         return PluginOrderOrder::ORDER_IS_UNDER_BUDGET;
      }
   }
   
   
   function displayAlertOverBudget($type) {
      global $LANG;

      switch($type) {
         case PluginOrderOrder::ORDER_IS_OVER_BUDGET :
            $message = "<h3><span class='red'>" . 
                              $LANG['plugin_order']['budget_over'][0] . 
                        "</span></h3>";
            break;
         case PluginOrderOrder::ORDER_IS_EQUAL_BUDGET :
            $message = "<h3><span class='red'>" . 
                              $LANG['plugin_order']['budget_over'][1] . 
                        "</span></h3>";
            break;
      }

      if( $type != PluginOrderOrder::ORDER_IS_UNDER_BUDGET ){
         echo "<div class='box' style='margin-bottom:20px;'>";
         echo "<div class='box-tleft'><div class='box-tright'><div class='box-tcenter'>";
         echo "</div></div></div>";
         echo "<div class='box-mleft'><div class='box-mright'><div class='box-mcenter'>";
         echo $message;
         echo "</div></div></div>";
         echo "<div class='box-bleft'><div class='box-bright'><div class='box-bcenter'>";
         echo "</div></div></div>";
         echo "</div>";  
      }
   }
   
   function unlinkBudget($ID) {
      $order = new self();
      $order->getFromDB($ID);
      $order->update(array('id' => $ID, 'budgets_id' => 0, '_unlink_budget' => 1));
      
   }
   
   /**
    * For other plugins, add a type to the linkable types
    *
    * @since version 1.6.0
    *
    * @param $type string class name
   **/
   static function registerType($type) {
      if (!in_array($type, self::$types)) {
         self::$types[] = $type;
      }
   }


   /**
    * Type than could be linked to a Rack
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
   **/
   static function getTypes($all=false) {

      if ($all) {
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;

      foreach ($types as $key => $type) {
         if (!class_exists($type)) {
            continue;
         }

         $item = new $type();
         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }

   static function cronComputeLateOrders($task) {
      global $CFG_GLPI, $DB;
      $nblate = 0;
      
      $table = getTableForItemType(__CLASS__);
      foreach (getAllDatasFromTable($table) as $values) {
         $order = new self();
         $order->fields = $values;
         if (!$order->fields['is_late'] && $order->shouldBeAlreadyDelivered(true)) {
            $order->setIsLate();
            $nblate++;
         }
      }
      $task->addVolume($nblate);

      $cron_status = 1;
      if ($CFG_GLPI["use_mailing"]) {
         $message = array();
         $alert   = new Alert();
         
         $entities[] = 0;
         foreach ($DB->request("SELECT `id` FROM `glpi_entities` ORDER BY `id` ASC") as $entity) {
            $entities[] = $entity['id'];
         }
         foreach ($entities as $entity) {
            $query_alert = "SELECT `$table`.`id` AS id,
                                   `$table`.`name` AS name,
                                   `$table`.`num_order` AS num_order,
                                   `$table`.`order_date` AS order_date,
                                   `$table`.`duedate` AS duedate,
                                   `$table`.`deliverydate` AS deliverydate,
                                   `$table`.`comment` AS comment,
                                   `$table`.`plugin_order_orderstates_id` AS plugin_order_orderstates_id,
                                   `glpi_alerts`.`id` AS alertID,
                                   `glpi_alerts`.`date`
                            FROM `$table`
                            LEFT JOIN `glpi_alerts`
                                  ON (`$table`.`id` = `glpi_alerts`.`items_id`
                                      AND `glpi_alerts`.`itemtype` = '".__CLASS__."')
                            WHERE `$table`.`entities_id` = '".$entity."'
                                   AND (`glpi_alerts`.`date` IS NULL) AND `$table`.`is_late`='1';";
         $orders = array();
         foreach ($DB->request($query_alert) as $order) {
            $orders[$order['id']] = $order;
         }

         if (!empty($orders)) {
            $options['entities_id'] = $entity;
            $options['orders']      = $orders;
            if (NotificationEvent::raiseEvent('duedate', new PluginOrderOrder(), $options)) {
               if ($task) {
                  $task->log(Dropdown::getDropdownName("glpi_entities", $entity)
                            ."&nbsp;:  $message\n");
                  $task->addVolume(1);
               } else {
                  Session::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities", $entity)
                                                  ."&nbsp;:  $message");
               }
               $input["type"]     = Alert::THRESHOLD;
               $input["itemtype"] = 'PluginOrderOrder';
               
               // add alerts
               foreach ($orders as $ID=>$tmp) {
                  $input["items_id"] = $ID;
                  $alert->add($input);
                  unset($alert->fields['id']);
                }

             } else {
               if ($task) {
                  $task->log(Dropdown::getDropdownName("glpi_entities", $entity)
                            ."&nbsp;: Send order alert failed\n");
               } else {
                  Session::addMessageAfterRedirect(Dropdown::getDropdownName("glpi_entities", $entity)
                                                 ."&nbsp;: Send order alert failed", false, ERROR);
                  }
               }
            }
         }
      }
      return true;
   }

   //------------------------------------------------------------
   //--------------------Install / uninstall --------------------
   //------------------------------------------------------------
   
   static function install(Migration $migration) {
      global $DB, $LANG;

      $table = getTableForItemType(__CLASS__);
      //Installation
      if (!TableExists($table) && !TableExists("glpi_plugin_order")) {
         $migration->displayMessage("Installing $table");

 
         $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_order_orders` (
               `id` int(11) NOT NULL auto_increment,
               `entities_id` int(11) NOT NULL default '0',
               `is_recursive` tinyint(1) NOT NULL default '0',
               `name` varchar(255) collate utf8_unicode_ci default NULL,
               `num_order` varchar(255) collate utf8_unicode_ci default NULL,
               `budgets_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_budgets (id)',
               `plugin_order_ordertaxes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_ordertaxes (id)',
               `plugin_order_orderpayments_id` int (11)  NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orderpayments (id)',
               `order_date` date default NULL,
               `duedate` date default NULL,
               `deliverydate` date default NULL,
               `is_late` tinyint(1) NOT NULL default '0',
               `suppliers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)',
               `contacts_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_contacts (id)',
               `locations_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_locations (id)',
               `plugin_order_orderstates_id` int(11) NOT NULL default 1,
               `plugin_order_billstates_id` int(11) NOT NULL default 1,
               `port_price` float NOT NULL default 0,
               `comment` text collate utf8_unicode_ci,
               `notepad` longtext collate utf8_unicode_ci,
               `is_deleted` tinyint(1) NOT NULL default '0',
               `plugin_order_ordertypes_id` int (11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_ordertypes (id)',
               PRIMARY KEY  (`id`),
               KEY `name` (`name`),
               KEY `entities_id` (`entities_id`),
               KEY `plugin_order_ordertaxes_id` (`plugin_order_ordertaxes_id`),
               KEY `plugin_order_orderpayments_id` (`plugin_order_orderpayments_id`),
               KEY `states_id` (`plugin_order_orderstates_id`),
               KEY `suppliers_id` (`suppliers_id`),
               KEY `contacts_id` (`contacts_id`),
               KEY `locations_id` (`locations_id`),
               KEY `is_late` (`is_late`),
               KEY `is_deleted` (`is_deleted`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
            $DB->query($query) or die ($DB->error());
      } else {
         //Upgrade
         $migration->displayMessage("Upgrading $table");

         if (TableExists('glpi_plugin_order')) {
            //Update to 1.1.0
            $migration->addField('glpi_plugin_order', "port_price", "FLOAT NOT NULL default '0'");
            $migration->addField('glpi_plugin_order', "taxes", "FLOAT NOT NULL default '0'");
            if (FieldExists("glpi_plugin_order", "numordersupplier")) {
               foreach ($DB->request("glpi_plugin_order") as $data) {
                  $query = "INSERT INTO  `glpi_plugin_order_suppliers`
                             (`ID`, `FK_order`, `numorder`, `numbill`) VALUES
                            (NULL, '".$data["ID"]."', '".$data["numordersupplier"]."', '".$data["numbill"]."') ";
                  $DB->query($query) or die($DB->error());
               }

            }
            $migration->dropField('glpi_plugin_order', 'numordersupplier');
            $migration->dropField('glpi_plugin_order', 'numbill');
            $migration->migrationOneTable('glpi_plugin_order');

         }
      
         //1.2.0
         $domigration_itemtypes = false;
         if ($migration->renameTable("glpi_plugin_order", $table)) {
            $domigration_itemtypes = true;
         }
            
         $migration->changeField($table, "ID", "id", "int(11) NOT NULL AUTO_INCREMENT");
         $migration->changeField($table, "FK_entities", "entities_id", "integer");
         $migration->changeField($table, "recursive", "is_recursive", "bool");
         $migration->changeField($table, "name", "name", 
                                 "varchar(255) collate utf8_unicode_ci default NULL");
         $migration->changeField($table, "budget", "budgets_id", 
                                 "int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_budgets (id)'");
         $migration->changeField($table, "numorder", "num_order", 
                                 "varchar(255) collate utf8_unicode_ci default NULL");
         $migration->changeField($table, "taxes", "plugin_order_ordertaxes_id", 
                                 "int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_ordertaxes (id)'");
         $migration->changeField($table, "payment", "plugin_order_orderpayments_id", 
                                 "int (11)  NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orderpayments (id)'");
         $migration->changeField($table, "date", "order_date",
                                 "date default NULL");
         $migration->changeField($table, "FK_enterprise", "suppliers_id",
                                 "int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)'");
         $migration->changeField($table, "FK_contact", "contacts_id",
                                  "int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_contacts (id)'");
         $migration->changeField($table, "location", "locations_id",
                                 "int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_locations (id)'");
         $migration->changeField($table, "status", "states_id",
                                 "int(11) NOT NULL default '0'");
         $migration->changeField($table, "comment", "comment",
                                 "text collate utf8_unicode_ci");
         $migration->changeField($table, "notes", "notepad",
                                 "longtext collate utf8_unicode_ci");
         $migration->changeField($table, "deleted", "is_deleted",
                                 "tinyint(1) NOT NULL default '0'");
         $migration->addKey($table, "name");
         $migration->addKey($table, "entities_id");
         $migration->addKey($table, "plugin_order_ordertaxes_id");
         $migration->addKey($table, "plugin_order_orderpayments_id");
         $migration->addKey($table, "states_id");
         $migration->addKey($table, "suppliers_id");
         $migration->addKey($table, "contacts_id");
         $migration->addKey($table, "locations_id");
         $migration->addKey($table, "is_deleted");
         $migration->migrationOneTable($table);

         //Only migrate itemtypes when it's only necessary, otherwise it breaks upgrade procedure !
         if ($domigration_itemtypes) {
            Plugin::migrateItemType(array(3150 => 'PluginOrderOrder'),
                                    array("glpi_bookmarks", "glpi_bookmarks_users", 
                                          "glpi_displaypreferences", "glpi_documents_items", 
                                          "glpi_infocoms", "glpi_logs", "glpi_tickets"),
                                    array());
         }

         if (TableExists("glpi_plugin_order_budgets")) {
            //Manage budgets (here because class has been remove since 1.4.0)
            $migration->changeField("glpi_plugin_order_budgets", "ID", "id", " int(11) NOT NULL auto_increment");
            $migration->changeField("glpi_plugin_order_budgets", "FK_entities", "entities_id", 
                                    "int(11) NOT NULL default '0'");
            $migration->changeField("glpi_plugin_order_budgets", "FK_budget", "budgets_id", 
                                    "int(11) NOT NULL default '0'");
            $migration->changeField("glpi_plugin_order_budgets", "comments", "comment",
                                    "text collate utf8_unicode_ci");
            $migration->changeField("glpi_plugin_order_budgets", "deleted", "is_deleted", 
                                    "tinyint(1) NOT NULL default '0'");
            $migration->changeField("glpi_plugin_order_budgets", "startdate", "start_date", 
                                    "date default NULL");
            $migration->changeField("glpi_plugin_order_budgets", "enddate", "end_date", 
                                    "date default NULL");
            $migration->changeField("glpi_plugin_order_budgets", "value", "value", 
                                    "float NOT NULL DEFAULT '0'");
            $migration->addKey("glpi_plugin_order_budgets", "entities_id");
            $migration->addKey("glpi_plugin_order_budgets", "is_deleted");
            $migration->migrationOneTable("glpi_plugin_order_budgets");
               
            Plugin::migrateItemType(array(3153 => 'PluginOrderBudget'),
                                    array("glpi_bookmarks", "glpi_bookmarks_users", 
                                          "glpi_displaypreferences", "glpi_documents_items", 
                                          "glpi_infocoms", "glpi_logs", "glpi_tickets"),
                                    array());

            //Manage budgets migration before dropping the table
            $budget = new Budget();
            $matchings = array('budgets_id' => 'id', 'name' => 'name', 'start_date' => 'begin_date', 
                               'end_date' => 'end_date', 'value' => 'value', 'comment' => 'comment', 
                               'entities_id' => 'entities_id', 'is_deleted' => 'is_deleted');
            foreach (getAllDatasFromTable("glpi_plugin_order_budgets") as $data) {
               $tmp    = array();
               $id     = false;
               foreach ($matchings as $old => $new) {
                  if (!is_null($data[$old])) {
                     $tmp[$new] = $data[$old];
                  }
               }
         
               $tmp['comment'] = addslashes($tmp['comment']);
                  
               //Budget already exists in the core: update it
               if ($budget->getFromDB($data['budgets_id'])) {
                  $budget->update($tmp);
                  $id = $tmp['id'];
               } else {
                  //Budget doesn't exists in the core: create it
                  unset($tmp['id']);
                  $id = $budget->add($tmp);
               }

            }
               
            $DB->query("DROP TABLE `glpi_plugin_order_budgets`");
               
            foreach (array('glpi_displaypreferences', 'glpi_documents_items', 'glpi_bookmarks', 
                           'glpi_logs') as $t) {
               $DB->query("DELETE FROM `$t` WHERE `itemtype` = 'PluginOrderBudget'");
            }

         }
            
         //1.3.0
         $migration->addField($table, "plugin_order_ordertypes_id",
                              "int (11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_ordertypes (id)'");
         $migration->migrationOneTable($table);

         //1.4.0
         if ($migration->changeField("glpi_plugin_order_orders", "states_id", 
                                     "plugin_order_orderstates_id", "int(11) NOT NULL default 1")) {
            $migration->migrationOneTable($table);
            $query = "UPDATE `glpi_plugin_order_orders` SET `plugin_order_orderstates_id`=`plugin_order_orderstates_id`+1";
            $DB->query($query) or die ($DB->error());
         }
         
         $migration->addField($table, "duedate", "DATETIME NULL");
         $migration->migrationOneTable($table);

         //1.5.0
         if (TableExists("glpi_dropdown_plugin_order_status")) {
            $DB->query("DROP TABLE `glpi_dropdown_plugin_order_status`") or die($DB->error());
         }
            
            
         if (TableExists("glpi_plugin_order_mailing")) {
            $DB->query("DROP TABLE IF EXISTS `glpi_plugin_order_mailing`;") or die($DB->error());
         }

         $migration->addField($table, 'plugin_order_billstates_id', "int(11) NOT NULL default 0");
         
         //1.5.2
         $migration->addField($table, 'deliverydate', "DATETIME NULL");
         $migration->addField($table, "is_late", "TINYINT(1) NOT NULL DEFAULT '0'");
         $migration->addKey($table, "is_late");
         if (!countElementsInTable('glpi_crontasks', "`name`='computeLateOrders'")) {
            Crontask::Register(__CLASS__, 'computeLateOrders', HOUR_TIMESTAMP, 
                               array('param' => 24, 'mode' => CronTask::MODE_EXTERNAL));
         }

         $migration->migrationOneTable($table);

         //Displayprefs
         $prefs = array(1 => 1, 2 => 2, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 10 => 10);
         foreach ($prefs as $num => $rank) {
            if (!countElementsInTable("glpi_displaypreferences", 
                                       "`itemtype`='PluginOrderOrder' AND `num`='$num' 
                                          AND `rank`='$rank' AND `users_id`='0'")) {
               $DB->query("INSERT INTO glpi_displaypreferences 
                           VALUES (NULL,'PluginOrderOrder','$num','$rank','0');");
            }
         }
      }
   }
   
   static function uninstall() {
      global $DB;

      $tables = array ("glpi_displaypreferences", "glpi_documents_items", "glpi_bookmarks",
                       "glpi_logs");
      foreach ($tables as $table) {
         $query = "DELETE FROM `$table` WHERE `itemtype`='".__CLASS__."'";
         $DB->query($query);
      }
      
      //Old table name
      $DB->query("DROP TABLE IF EXISTS `glpi_plugin_order`") or die ($DB->error());
      //Current table name
      $DB->query("DROP TABLE IF EXISTS  `".getTableForItemType(__CLASS__)."`") or die ($DB->error());
   }
}
?>
