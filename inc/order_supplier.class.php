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

class PluginOrderOrder_Supplier extends CommonDBChild {
   
   public $itemtype = 'PluginOrderOrder';
   public $items_id = 'plugin_order_orders_id';
   
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
   
   function prepareInputForAdd($input) {
      // Not attached to reference -> not added
      if (!isset($input['plugin_order_orders_id']) || $input['plugin_order_orders_id'] <= 0) {
         return false;
      }
      return $input;
   }
	
	function showForm($target, $ID, $plugin_order_orders_id=-1) {
		global $LANG, $CFG_GLPI;
      
      if (!plugin_order_haveRight("reference", "w"))
			return false;
			
		if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $input=array('plugin_order_orders_id'=>$plugin_order_orders_id);
         $this->check(-1,'w',$input);
      }

      $this->showTabs($ID);
      $this->showFormHeader($target,$ID,'',1);

      echo "<input type='hidden' name='plugin_order_orders_id' value='$plugin_order_orders_id'>";
      
      echo "<tr class='tab_bg_2'><td>" . $LANG['financial'][26] . ": </td>";
      echo "<td>";
      $link=getItemTypeFormURL('Supplier');
      echo "<a href=\"" . $link. "?id=" . $this->fields["suppliers_id"] . "\">" . Dropdown::getDropdownName("glpi_suppliers", $this->fields["suppliers_id"]) . "</a>";
      echo "</td></tr>";
      
      /* number of quote */
      echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_order'][30] . ": </td><td>";
      autocompletionTextField($this,"num_quote");
      echo "</td>";
		echo "</tr>";
		
      /* num order supplier */
      echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_order'][31] . ": </td><td>";
      autocompletionTextField($this,"num_order");
      echo "</td>";
      echo "</tr>";
      
      /* number of bill */
      echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_order'][28] . ": </td><td>";
      autocompletionTextField($this,"num_bill");
      echo "</td>";
		echo "</tr>";
		
		$this->showFormButtons($ID,'',1,false);

      echo "<div id='tabcontent'></div>";
      echo "<script type='text/javascript'>loadDefaultTab();</script>";

		return true;
	}
	
	function showOrderSupplierInfos($target, $ID) {
      global $LANG, $DB, $CFG_GLPI;

      $order = new PluginOrderOrder;
      $order->getFromDB($ID);

      initNavigateListItems($this->getType(),$LANG['plugin_order'][7] ." = ". $order->fields["name"]);

      $candelete = plugin_order_haveRight("order","w");
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
            echo "<table width='950px' class='tab_glpi'>";
            echo "<tr><td><img src=\"".$CFG_GLPI["root_doc"]."/pics/arrow-left.png\" alt=''></td><td class='center'><a onclick= \"if ( markCheckboxes('show_supplierinfos$rand') ) return false;\" href='".$_SERVER['PHP_SELF']."?id=$ID&amp;select=all'>".$LANG['buttons'][18]."</a></td>";

            echo "<td>/</td><td class='center'><a onclick= \"if ( unMarkCheckboxes('show_supplierinfos$rand') ) return false;\" href='".$_SERVER['PHP_SELF']."?id=$ID&amp;select=none'>".$LANG['buttons'][19]."</a>";
            echo "</td><td align='left' width='80%'>";
            echo "<input type='submit' name='delete_supplier_infos' value=\"" . $LANG['buttons'][6] . "\" class='submit' >";
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
   
   function addSupplierInfosToOrder($target,$plugin_order_orders_id){
      global $LANG,$DB;

      if (plugin_order_haveRight("order","w")){

         $order = new PluginOrderOrder;
         $order->getFromDB($plugin_order_orders_id);

         if (!$order->fields["is_deleted"] && !$this->checkIfSupplierInfosExists($plugin_order_orders_id)){

            echo "<form method='post' name='add_supplierinfo' action=\"$target\">";
            echo "<table class='tab_cadre_fixe'>";
            echo "<input type='hidden' name='plugin_order_orders_id' value='" . $plugin_order_orders_id . "'>";
            echo "<tr>";
            echo "<th colspan='4' align='center'>".$LANG['plugin_order'][4]."</th></tr>";
            echo "<tr>";
            echo "<th>" . $LANG['financial'][26] . "</th>";
            echo "<th>" . $LANG['plugin_order'][30] . "</th>";
            echo "<th>" . $LANG['plugin_order'][31]. "</th>";
            echo "<th>" . $LANG['plugin_order'][28] . "</th></tr>";
            echo "<tr>";
            echo "<td class='tab_bg_1' align='center'>";
            echo Dropdown::getDropdownName('glpi_suppliers', $order->fields["suppliers_id"]);
            echo "<input type='hidden' name='suppliers_id' value='" .$order->fields["suppliers_id"] . "'>";
            echo "</td>";
            echo "<td class='tab_bg_1' align='center'>";
            autocompletionTextField($this,"num_quote");
            echo "</td>";
            echo "<td class='tab_bg_1' align='center'>";
            autocompletionTextField($this,"num_order");
            echo "</td>";
            echo "<td class='tab_bg_1' align='center'>";
            autocompletionTextField($this,"num_bill");
            echo "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td class='tab_bg_1' align='center' colspan='4'>";
            echo "<input type='submit' name='add_supplier_infos' value=\"" . $LANG['buttons'][8] . "\" class='submit' >";
            echo "</td>";
            echo "</tr>";
            echo "</table></form>";
            echo "</div>";

         }
      }
   }
}

?>