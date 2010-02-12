<?php
/*
 * @version $Id: HEADER 1 2009-09-21 14:58 Tsmr $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2009 by the INDEPNET Development Team.

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
// Original Author of file: NOUH Walid & Benjamin Fontan
// Purpose of file: plugin order v1.1.0 - GLPI 0.72
// ----------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginOrderOrder extends CommonDBTM {

	public $dohistory=true;
   
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
   
	function cleanDBonPurge() {
		global $DB;

		$temp = new PluginOrderOrder_Item();
      $temp->clean(array('plugin_order_orders_id' => $this->fields['id']));

	}
   
   function canUpdateOrder($orders_id) {
      global $ORDER_VALIDATION_STATUS;
      
      if ($orders_id > 0) {
         $this->getFromDB($orders_id);
         return (in_array($this->fields["states_id"], $ORDER_VALIDATION_STATUS));
      } else
         return true;
   }
	
	function getSearchOptions() {
      global $LANG;

      $tab = array();
    
      $tab['common'] = $LANG['plugin_order']['title'][1];
		/* order_number */
		$tab[1]['table'] = $this->getTable();
		$tab[1]['field'] = 'num_order';
		$tab[1]['linkfield'] = 'num_order';
		$tab[1]['name'] = $LANG['plugin_order'][0];
		$tab[1]['datatype'] = 'itemlink';
		/* order_date */
		$tab[2]['table'] = $this->getTable();
		$tab[2]['field'] = 'order_date';
		$tab[2]['linkfield'] = 'order_date';
		$tab[2]['name'] = $LANG['plugin_order'][1];
		$tab[2]['datatype']='date';
		/* taxes*/
		$tab[3]['table'] = 'glpi_plugin_order_ordertaxes';
		$tab[3]['field'] = 'name';
		$tab[3]['linkfield'] = 'plugin_order_ordertaxes_id';
		$tab[3]['name'] = $LANG['plugin_order'][25];
		/* location */
		$tab[4]['table'] = 'glpi_locations';
		$tab[4]['field'] = 'completename';
		$tab[4]['linkfield'] = 'locations_id';
		$tab[4]['name'] = $LANG['plugin_order'][40];
		/* status */
		$tab[5]['table'] = $this->getTable();
		$tab[5]['field'] = 'states_id';
		$tab[5]['linkfield'] = '';
		$tab[5]['name'] = $LANG['plugin_order']['status'][0];
		/* supplier */
		$tab[6]['table'] = 'glpi_suppliers';
		$tab[6]['field'] = 'name';
		$tab[6]['linkfield'] = 'suppliers_id';
		$tab[6]['name'] = $LANG['financial'][26];
		$tab[6]['datatype']='itemlink';
		$tab[6]['itemlink_type']='Supplier';
		$tab[6]['forcegroupby']=true;
		/* payment */
		$tab[7]['table'] = 'glpi_plugin_order_orderpayments';
		$tab[7]['field'] = 'name';
		$tab[7]['linkfield'] = 'plugin_order_orderpayments_id';
		$tab[7]['name'] = $LANG['plugin_order'][32];
      /* contact */
		$tab[8]['table'] = 'glpi_contacts';
		$tab[8]['field'] = 'completename';
		$tab[8]['linkfield'] = 'contacts_id';
		$tab[8]['name'] = $LANG['common'][18];
		$tab[8]['datatype']='itemlink';
		$tab[8]['itemlink_type']='Contact';
		$tab[8]['forcegroupby']=true;
		/* budget */
		$tab[9]['table'] = 'glpi_budgets';
		$tab[9]['field'] = 'name';
		$tab[9]['linkfield'] = 'budgets_id';
		$tab[9]['name'] = $LANG['financial'][87];
		$tab[9]['datatype']='itemlink';
		$tab[9]['itemlink_type']='Budget';
		$tab[9]['forcegroupby']=true;
		/* title */
		$tab[10]['table'] = $this->getTable();
		$tab[10]['field'] = 'name';
		$tab[10]['linkfield'] = 'name';
		$tab[10]['name'] = $LANG['plugin_order'][39];
		/* comments */
		$tab[16]['table'] = $this->getTable();
		$tab[16]['field'] = 'comment';
		$tab[16]['linkfield'] = 'comment';
		$tab[16]['name'] = $LANG['plugin_order'][2];
		$tab[16]['datatype'] = 'text';
		/* ID */
		$tab[30]['table'] = $this->getTable();
		$tab[30]['field'] = 'id';
		$tab[30]['linkfield'] = '';
		$tab[30]['name'] = $LANG['common'][2];
		/* entity */
		$tab[80]['table'] = 'glpi_entities';
		$tab[80]['field'] = 'completename';
		$tab[80]['linkfield'] = 'entities_id';
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
			/* generation */
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
		}
		elseif (!isset ($input["name"]) || $input["name"] == '') $input["name"] = $input["num_order"];

		return $input;
	}

	function showForm ($ID, $options=array()) {
		global $CFG_GLPI, $LANG;

		if (!plugin_order_haveRight("order","r")) return false;

		if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $this->check(-1,'w');
         $this->getEmpty();
      }

      $canedit = ($this->canUpdateOrder($ID) && $this->can($ID, 'w') && $this->fields["states_id"] != ORDER_STATUS_CANCELED);

      $this->showTabs($options);
      $this->showFormHeader($options);

      //Display without inside table
      /* title */
      echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_order'][39] . ": </td>";
      echo "<td>";
      if ($canedit)
         autocompletionTextField($this,"name");
      else
         echo $this->fields["name"];
      echo "</td>";
      /* date of order */
      echo "<td>" . $LANG['plugin_order'][1] . "*:</td><td>";
      if ($canedit)
         if ($this->fields["order_date"] == NULL)
            showDateFormItem("order_date", date("Y-m-d"), true, true);
         else
            showDateFormItem("order_date", $this->fields["order_date"], true, true);
      else
         echo convDate($this->fields["order_date"]);
      echo "</td></tr>";

      /* num order */
      echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_order'][0] . "*: </td>";
      echo "<td>";
      if ($canedit)
         autocompletionTextField($this,"num_order");
      else
         echo $this->fields["num_order"];
      echo "</td>";
      echo "<td>" . $LANG['plugin_order'][3] . ": </td><td>";
      if ($canedit)
         Dropdown::show('Budget', array('name' => "budgets_id",'value' => $this->fields["budgets_id"], 'entity' => $this->fields["entities_id"]));
      else
         echo Dropdown::getDropdownName("glpi_budgets",$this->fields["budgets_id"]);
      echo "</td></tr>";

      /* location */
      echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_order'][40] . ": </td>";
      echo "<td>";
      if ($canedit)
         Dropdown::show('Location', array('name' => "locations_id",'value' => $this->fields["locations_id"], 'entity' => $this->fields["entities_id"]));
      else
         echo Dropdown::getDropdownName("glpi_locations",$this->fields["locations_id"]);
      echo "</td>";
      
      /* tva */
      echo "<td>" . $LANG['plugin_order'][25] . ": </td><td>";
      $PluginOrderConfig = new PluginOrderConfig();
      $default_taxes = $PluginOrderConfig->getDefaultTaxes();
      
      if (empty ($ID) || $ID < 0) {
         $taxes = $default_taxes;
      } else {
         $taxes = $this->fields["plugin_order_ordertaxes_id"];
      }
      if ($canedit)
         Dropdown::show('PluginOrderOrderTaxe', array('name' => "plugin_order_ordertaxes_id",'value' => $this->fields["plugin_order_ordertaxes_id"]));
      else
         echo Dropdown::getDropdownName("glpi_plugin_order_ordertaxes",$this->fields["plugin_order_ordertaxes_id"]);
      echo "</td>";
      echo "</tr>";
      
      /* supplier of order */
      echo "<tr class='tab_bg_1'><td>" . $LANG['financial'][26] . ": </td>";
      echo "<td>";
      if ($canedit && !$this->checkIfDetailExists($ID))
         $this->dropdownSuppliers("suppliers_id", $this->fields["suppliers_id"], $this->fields["entities_id"]);
      else
         echo Dropdown::getDropdownName("glpi_suppliers",$this->fields["suppliers_id"]);
      echo "</td>";
      
      /* payment */
      echo "<td>" . $LANG['plugin_order'][32] . ": </td><td>";
      if ($canedit)
         Dropdown::show('PluginOrderOrderPayment', array('name' => "plugin_order_orderpayments_id",'value' => $this->fields["plugin_order_orderpayments_id"]));
      else
         echo Dropdown::getDropdownName("glpi_plugin_order_orderpayments",$this->fields["plugin_order_orderpayments_id"]);
      echo "</td></tr>";
      
      /* linked contact of the supplier of order */
      echo "<tr class='tab_bg_1'><td>".$LANG['common'][18].": </td>";
      echo "<td><span id='show_contacts_id'>";
      if ($canedit && $ID > 0)
         $this->dropdownContacts($this->fields["suppliers_id"],$this->fields["contacts_id"],$this->fields["entities_id"]);
      else
         echo Dropdown::getDropdownName("glpi_contacts",$this->fields["contacts_id"]);
      echo "</span></td>";
      
      /* port price */
      echo "<td>".$LANG['plugin_order'][26].": </td>";
      echo "<td>";
      if ($canedit)
         echo "<input type='text' name='port_price' value=\"".formatNumber($this->fields["port_price"],true)."\" size='5'>";
      else
         echo formatNumber($this->fields["port_price"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>";
      //comments of order
      echo $LANG['plugin_order'][2] . ":	</td>";
      echo "<td>";
      if ($canedit)
         echo "<textarea cols='40' rows='4' name='comment'>" . $this->fields["comment"] . "</textarea>";
      else
         echo $this->fields["comment"];
      echo "</td>";

      /* total price (without taxes) */
      
      /* status of bill */
      echo "<td class='center b'>" . $LANG['plugin_order']['status'][0] . "<br>";
      echo "<input type='hidden' name='states_id' value=" . ORDER_STATUS_DRAFT . ">";
      echo $this->getDropdownStatus($this->fields["states_id"]);

      echo "</td><td>";
      if ($ID > 0) {
         $PluginOrderOrder_Item = new PluginOrderOrder_Item();
         $prices = $PluginOrderOrder_Item->getAllPrices($ID);

         echo $LANG['plugin_order'][13] . " : ";
         echo formatNumber($prices["priceHT"]) . "<br>";
     
         // total price (with postage)
         echo $LANG['plugin_order'][15] . " : ";
         $priceHTwithpostage=$prices["priceHT"]+$this->fields["port_price"];
         echo formatNumber($priceHTwithpostage) . "<br>";
         
         // total price (with taxes)
         echo $LANG['plugin_order'][14] . " : ";
         $postagewithTVA = $PluginOrderOrder_Item->getPricesATI($this->fields["port_price"], Dropdown::getDropdownName("glpi_plugin_order_ordertaxes", $this->fields["plugin_order_ordertaxes_id"]));
         $total = $prices["priceTTC"] + $postagewithTVA;
         echo formatNumber($total) . "</td>";
      } else
         echo "</td>";

      echo "</tr>";

      $this->showFormButtons($options);
      echo "<div id='tabcontent'></div>";
      echo "<script type='text/javascript'>loadDefaultTab();</script>";
      
      return true;
	}

   function dropdownSuppliers($myname,$value=0,$entity_restrict='') {
      global $DB,$CFG_GLPI;

      $rand=mt_rand();

      $where=" WHERE `glpi_suppliers`.`is_deleted` = '0' ";
      $where.=getEntitiesRestrictRequest("AND","glpi_suppliers",'',$entity_restrict,true);

      $query="SELECT `glpi_suppliers`.* FROM `glpi_suppliers`
         LEFT JOIN `glpi_contacts_suppliers` ON (`glpi_contacts_suppliers`.`suppliers_id` = `glpi_suppliers`.`id`)
         $where
         GROUP BY `glpi_suppliers`.`id`
         ORDER BY `entities_id`, `name`";

      $result=$DB->query($query);

      echo "<select name='suppliers_id' id='suppliers_id'>\n";
      echo "<option value='0'>------</option>\n";

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
         echo "<option value='".$data["id"]."' ".($value==$data["id"]?" selected ":"")." title=\"".cleanInputText($output)."\">".substr($output,0,$CFG_GLPI["dropdown_chars_limit"])."</option>";
      }
      if ($prev>=0) {
         echo "</optgroup>";
      }
      echo "</select>\n";

      $params=array('suppliers_id'=>'__VALUE__',
                     'entity_restrict'=>$entity_restrict,
                     'rand'=>$rand,
                     'myname'=>$myname
                     );

      ajaxUpdateItemOnSelectEvent("suppliers_id","show_contacts_id",$CFG_GLPI["root_doc"]."/plugins/order/ajax/dropdownSupplier.php",$params);

      return $rand;
   }
   
   function dropdownContacts($suppliers_id,$value=0,$entity_restrict='') {
      global $DB,$CFG_GLPI;

      $rand=mt_rand();

      $where=" WHERE `glpi_contacts_suppliers`.`contacts_id` = `glpi_contacts`.`id` AND (`glpi_contacts_suppliers`.`suppliers_id` = '".$suppliers_id."' AND `glpi_contacts`.`is_deleted` = '0' ) ";
      $where.=getEntitiesRestrictRequest("AND","glpi_contacts",'',$entity_restrict,true);

      $query = "SELECT `glpi_contacts`.*
               FROM `glpi_contacts`,`glpi_contacts_suppliers`
               $where
               ORDER BY `entities_id`, `name`";
               
      $result=$DB->query($query);

      echo "<select name=\"contacts_id\">";

      echo "<option value=\"0\">-----</option>";

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
            echo "<option value='".$data["id"]."' ".($value==$data["id"]?" selected ":"")." title=\"".cleanInputText($output)."\">".substr($output,0,$CFG_GLPI["dropdown_chars_limit"])."</option>";
         }
         if ($prev>=0) {
            echo "</optgroup>";
         }
      }
      echo "</select>";
   }
   
   function getDropdownStatus($value) {
      global $LANG;
      
      switch ($value) {
         case ORDER_STATUS_DRAFT :
            return $LANG['plugin_order']['status'][9];
         case ORDER_STATUS_APPROVED :
            return $LANG['plugin_order']['status'][12];
         case ORDER_STATUS_WAITING_APPROVAL :
            return $LANG['plugin_order']['status'][7];
         case ORDER_STATUS_PARTIALLY_DELIVRED :
            return $LANG['plugin_order']['status'][1];
         case ORDER_STATUS_COMPLETLY_DELIVERED :
            return $LANG['plugin_order']['status'][2];
         case ORDER_STATUS_CANCELED :
            return $LANG['plugin_order']['status'][10];
         default :
            return "";
      }
   }

   function addStatusLog($orders_id, $status, $comments = '') {
      global $LANG;

      switch ($status) {
         case ORDER_STATUS_DRAFT :
            $changes = $LANG['plugin_order']['validation'][15];
            break;
         case ORDER_STATUS_WAITING_APPROVAL :
            $changes = $LANG['plugin_order']['validation'][1];
            break;
         case ORDER_STATUS_APPROVED :
            $changes = $LANG['plugin_order']['validation'][2];
            break;
         case ORDER_STATUS_PARTIALLY_DELIVRED :
            $changes = $LANG['plugin_order']['validation'][3];
            break;
         case ORDER_STATUS_COMPLETLY_DELIVERED :
            $changes = $LANG['plugin_order']['validation'][4];
            break;
         case ORDER_STATUS_CANCELED :
            $changes = $LANG['plugin_order']['validation'][5];
            break;
      }

      if ($comments != '')
         $changes .= " : ".$comments;

      $this->addHistory($this->getType(), '',$changes,$orders_id);

   }
   
   function updateOrderStatus($orders_id, $status, $comments = '') {

      $input["states_id"] = $status;
      $input["id"] = $orders_id;
      $this->dohistory = false;
      $this->update($input);
      $this->addStatusLog($orders_id, $status, $comments);
      return true;
   }
   
   function addHistory($type, $old_value='',$new_value='',$ID){
      $changes[0] = 0;
      $changes[1] = $old_value;
      $changes[2] = $new_value;
      Log::history($ID, $type, $changes, 0, HISTORY_LOG_SIMPLE_MESSAGE);
   }

	function needValidation($ID) {
		global $ORDER_VALIDATION_STATUS;
		
		if ($ID > 0 && $this->getFromDB($ID))
			return (in_array($this->fields["states_id"], $ORDER_VALIDATION_STATUS));
		else
			return false;
	}
   
   function canDisplayValidationForm($orders_id) {

      $this->getFromDB($orders_id);

      //If it's an order creation -> do not display form
      if (!$orders_id)
         return false;
      else
         return ($this->canValidate() || $this->canUndoValidation() || $this->canCancelOrder());
   }

	function canValidate() {
		global $ORDER_VALIDATION_STATUS;
		
		$PluginOrderConfig = new PluginOrderConfig;
		$config = $PluginOrderConfig->getConfig();

		//If no validation process -> can validate if order is in draft state
		if (!$config["use_validation"])
			return ($this->fields["states_id"] == ORDER_STATUS_DRAFT);
		else {
			//Validation process is used

			//If order is canceled, cannot validate !
			if ($this->fields["states_id"] == ORDER_STATUS_CANCELED)
				return false;

			//If no right to validate
			if (!plugin_order_haveRight("validation", "w"))
				return false;
			else
				return (in_array($this->fields["states_id"], $ORDER_VALIDATION_STATUS));
		}
	}

	function canCancelOrder() {
		//If order is canceled, cannot validate !
		if ($this->fields["states_id"] == ORDER_STATUS_CANCELED)
			return false;

		//If no right to cancel
		if (!plugin_order_haveRight("cancel", "w"))
			return false;

		return true;
	}
	
	function deleteAllLinkWithDevice($orders_id) {

      $detail = new PluginOrderOrder_Item;
      $devices = getAllDatasFromTable("glpi_plugin_order_orders_items", "plugin_order_orders_id=$orders_id");
      foreach ($devices as $deviceID => $device)
         $detail->delete(array (
            "id" => $deviceID
         ));
   }

	function canDoValidationRequest() {
		
		$PluginOrderConfig = new PluginOrderConfig;
		$config = $PluginOrderConfig->getConfig();
		
		if (!$config["use_validation"])
			return false;
		else
			return ($this->fields["states_id"] == ORDER_STATUS_DRAFT);
	}

	function canCancelValidationRequest() {
	
		return ($this->fields["states_id"] == ORDER_STATUS_WAITING_APPROVAL);
	}

	function canUndoValidation() {
		global $ORDER_VALIDATION_STATUS;
		
		//If order is canceled, cannot validate !
		if ($this->fields["states_id"] == ORDER_STATUS_CANCELED)
			return false;

		//If order is not validate, cannot undo validation !
		if (in_array($this->fields["states_id"], $ORDER_VALIDATION_STATUS))
			return false;

		//If no right to cancel
		return (plugin_order_haveRight("undo_validation", "w"));
	}
	
	function checkIfDetailExists($orders_id) {
      
      if ($orders_id) {
         $detail = new PluginOrderOrder_Item;
         $devices = getAllDatasFromTable("glpi_plugin_order_orders_items", "plugin_order_orders_id=$orders_id");
         if (!empty($devices))
            return true;
         else
            return false;
      }
   }
	
	function showValidationForm($target, $orders_id) {
      global $LANG;
      
      $this->getFromDB($orders_id);

      if ($this->can($orders_id,'w') && $this->canDisplayValidationForm($orders_id)) {
         echo "<form method='post' name='form' action=\"$target\">";
         
         echo "<div align='center'><table class='tab_cadre_fixe'>";
         
         if ($this->checkIfDetailExists($orders_id)) {
         
            echo "<tr class='tab_bg_2'><th colspan='3'>" . $LANG['plugin_order']['validation'][6] . "</th></tr>";

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
               echo "<input type='submit' onclick=\"return confirm('" . $LANG['plugin_order']['detail'][38] . "')\" name='cancel_order' value=\"" . $LANG['plugin_order']['validation'][12] . "\" class='submit'>";
               $link = "<br><br>";
            }
            $PluginOrderConfig = new PluginOrderConfig;
            $config = $PluginOrderConfig->getConfig();

            if ($this->canValidate()) {
               echo $link . "<input type='submit' name='validate' value=\"" . $LANG['plugin_order']['validation'][9] . "\" class='submit'>";
               $link = "<br><br>";
            }

            if ($this->canCancelValidationRequest()) {
               echo $link . "<input type='submit' onclick=\"return confirm('" . $LANG['plugin_order']['detail'][39] . "')\" name='cancel_waiting_for_approval' value=\"" . $LANG['plugin_order']['validation'][13] . "\" class='submit'>";
               $link = "<br><br>";
            }

            if ($this->canDoValidationRequest()) {
               echo $link . "<input type='submit' name='waiting_for_approval' value=\"" . $LANG['plugin_order']['validation'][11] . "\" class='submit'>";
               $link = "<br><br>";
            }

            if ($this->canUndoValidation()) {
               echo $link . "<input type='submit' onclick=\"return confirm('" . $LANG['plugin_order']['detail'][40] . "')\" name='undovalidation' value=\"" . $LANG['plugin_order']['validation'][17] . "\" class='submit'>";
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

      echo "<form action='".$CFG_GLPI["root_doc"]."/plugins/order/front/export.php?id=".$ID."' method=\"post\">";
      echo "<div align=\"center\"><table cellspacing=\"2\" cellpadding=\"2\">";

      echo "<tr>";
      echo "<td class='center'>";
      echo "<input type='submit' value=\"".$LANG['plugin_order']['generation'][1]."\" class='submit' ></div></td></tr>";
      echo "</td>";
      echo "</tr>";

      echo "</table></div></form>";
   }
   
   function sendNotification($action,$orders_id,$entities_id=0,$users_id=0,$comment=''){
      $mailing = new PluginOrderMailing($orders_id,$action,$entities_id,$users_id,$comment);
      $mailing->mailing();
   }
   
   function transfer($ID,$entity) {
      global $DB;
      
      $PluginOrderOrder_Supplier = new PluginOrderOrder_Supplier;
      $PluginOrderReference = new PluginOrderReference();
      $PluginOrderOrder_Item = new PluginOrderOrder_Item();
      
      $this->getFromDB($ID);
      $input["id"] = $ID;
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
      
      $result=$DB->query($query);
      $num=$DB->numrows($result);
      if ($num) {
         while ($detail=$DB->fetch_array($result)) {

            $ref=$PluginOrderReference->transfer($detail["plugin_order_references_id"],
                                             $entity);
         }
      }
   }
}

?>