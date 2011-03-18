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
// Original Authors of file: 
// NOUH Walid & FONTAN Benjamin & CAILLAUD Xavier & François Legastelois
// Purpose of file: plugin order v1.3.0 - GLPI 0.78.3
// ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginOrderReference_Supplier extends CommonDBChild {
   
   public $itemtype = 'PluginOrderReference';
   public $items_id = 'plugin_order_references_id';
   public $dohistory=true;
   
   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_order']['reference'][5];
   }
   
   function canCreate() {
      return plugin_order_haveRight('reference', 'w');
   }

   function canView() {
      return plugin_order_haveRight('reference', 'r');
   }
   
   function getFromDBByReference($plugin_order_references_id) {
      global $DB;
      
      $query = "SELECT * FROM `".$this->getTable()."`
               WHERE `plugin_order_references_id` = '" . $plugin_order_references_id . "' ";
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
   
   function getSearchOptions() {
      global $LANG;

      $tab = array();
    
      $tab['common'] = $LANG['plugin_order']['reference'][5];

      $tab[1]['table'] = $this->getTable();
      $tab[1]['field'] = 'reference_code';
      $tab[1]['linkfield'] = 'reference_code';
      $tab[1]['name'] = $LANG['plugin_order']['reference'][10];
      $tab[1]['datatype'] = 'text';

      $tab[2]['table'] = $this->getTable();
      $tab[2]['field'] = 'price_taxfree';
      $tab[2]['linkfield'] = 'price_taxfree';
      $tab[2]['name'] = $LANG['plugin_order']['detail'][4];
      $tab[2]['datatype'] = 'number';

      $tab[3]['table'] = 'glpi_suppliers';
      $tab[3]['field'] = 'name';
      $tab[3]['linkfield'] = 'suppliers_id';
      $tab[3]['name'] = $LANG['financial'][26];
      $tab[3]['datatype']='itemlink';
      $tab[3]['itemlink_type']='Supplier';
      $tab[3]['forcegroupby']=true;
      
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
   
   function prepareInputForAdd($input) {
      // Not attached to reference -> not added
      if (!isset($input['plugin_order_references_id']) || $input['plugin_order_references_id'] <= 0) {
         return false;
      }
      return $input;
   }
   
   function defineTabs($options=array()) {
      global $LANG;
      /* principal */
      $ong[1] = $LANG['title'][26];
      if ($this->fields['id'] > 0) {
         if (haveRight("document", "r"))
            $ong[4] = $LANG['Menu'][27];
         $ong[12] = $LANG['title'][38];
      }
      return $ong;
   }

   function showForm ($ID, $options=array()) {
      global $LANG, $DB;
      
      if (!$this->canView())
         return false;
      
      $plugin_order_references_id = -1;
      if (isset($options['plugin_order_references_id'])) {
         $plugin_order_references_id = $options['plugin_order_references_id'];
      }

      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $input=array('plugin_order_references_id' => $options['plugin_order_references_id']);
         $this->check(-1,'w',$input);
      }
      
      if (strpos($_SERVER['PHP_SELF'],"reference_supplier"))
         $this->showTabs($options);
      $this->showFormHeader($options);
      
      $PluginOrderReference = new PluginOrderReference();
      $PluginOrderReference->getFromDB($plugin_order_references_id);
      echo "<input type='hidden' name='plugin_order_references_id' value='$plugin_order_references_id'>";
      echo "<input type='hidden' name='entities_id' value='".$PluginOrderReference->getEntityID()."'>";
      echo "<input type='hidden' name='is_recursive' value='".$PluginOrderReference->isRecursive()."'>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>" . $LANG['financial'][26] . ": </td>";
      echo "<td>";

      if ($ID > 0) {
         $link=getItemTypeFormURL('Supplier');
         echo "<a href=\"" . $link. "?id=" . $this->fields["suppliers_id"] . "\">" . Dropdown::getDropdownName("glpi_suppliers", $this->fields["suppliers_id"]) . "</a>";
         echo "<input type='hidden' name='suppliers_id' value='".$this->fields["suppliers_id"]."'>";
      } else {
         $suppliers = array();
         $query = "SELECT `suppliers_id`
                     FROM `".$this->getTable()."`
                     WHERE `plugin_order_references_id` = '$plugin_order_references_id'";
         $result = $DB->query($query);
         while ($data = $DB->fetch_array($result))
            $suppliers[] = $data["suppliers_id"];

         Dropdown::show('Supplier',
                  array('name'   => 'suppliers_id',
                        'used' => $suppliers,
                        'entity' => $PluginOrderReference->getEntityID()));
      }
      echo "</td>";

      echo "<td>" . $LANG['plugin_order']['reference'][10] . ": </td>";
      echo "<td>";
      autocompletionTextField($this,"reference_code");
      echo "</td></tr>";
      
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'>";
      
      echo "<td>" . $LANG['plugin_order']['detail'][4] . ": </td>";
      echo "<td>";
      echo "<input type='text' name='price_taxfree' value=\"".formatNumber($this->fields["price_taxfree"],true)."\" size='7'>";
      echo "</td>";
      
      echo "<td></td>";
      echo "<td></td>";
      
      echo "</tr>";
        
      $options['candel'] = false;
      $this->showFormButtons($options);
      
      if (strpos($_SERVER['PHP_SELF'],"reference_supplier")) {
         $this->addDivForTabs();
      }
      return true;
   }

   function showReferenceManufacturers($target, $ID) {
      global $LANG, $DB, $CFG_GLPI;

      $ref = new PluginOrderReference;
      $ref->getFromDB($ID);
      
      initNavigateListItems($this->getType(),$LANG['plugin_order']['reference'][1] ." = ". $ref->fields["name"]);

      $candelete =$ref->can($ID,'w');
      $query = "SELECT * FROM `".$this->getTable()."` WHERE `plugin_order_references_id` = '$ID' ";
      $result = $DB->query($query);
      $rand=mt_rand();
      echo "<div class='center'>";
      echo "<form method='post' name='show_supplierref$rand' id='show_supplierref$rand' action=\"$target\">";
      echo "<input type='hidden' name='plugin_order_references_id' value='" . $ID . "'>";
      echo "<table class='tab_cadre_fixe'>";
      
      echo "<tr><th colspan='5'>".$LANG['plugin_order'][4]."</th></tr>";
      echo "<tr><th>&nbsp;</th>";
      echo "<th>" . $LANG['financial'][26] . "</th>";
      echo "<th>" . $LANG['plugin_order']['reference'][1] . "</th>";
      echo "<th>" . $LANG['plugin_order']['detail'][4] . "</th>";
      echo "</tr>";

      if ($DB->numrows($result) > 0) {
         echo "<form method='post' name='show_ref_manu' action=\"$target\">";
         echo "<input type='hidden' name='plugin_order_references_id' value='" . $ID . "'>";

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
            echo "<td><a href='".$link."?id=".$data["id"]."&plugin_order_references_id=".$ID."'>" .Dropdown::getDropdownName("glpi_suppliers", $data["suppliers_id"]) . "</a></td>";
            echo "<td>";
            echo $data["reference_code"];
            echo "</td>";
            echo "<td>";
            echo formatNumber($data["price_taxfree"]);
            echo "</td>";
            echo "</tr>";
         }
         echo "</table>";

         if ($candelete)
         {     
            echo "<div class='center'>";
            echo "<table width='900px' class='tab_glpi'>";
            echo "<tr><td><img src=\"".$CFG_GLPI["root_doc"]."/pics/arrow-left.png\" alt=''></td><td class='center'><a onclick= \"if ( markCheckboxes('show_supplierref$rand') ) return false;\" href='#'>".$LANG['buttons'][18]."</a></td>";

            echo "<td>/</td><td class='center'><a onclick= \"if ( unMarkCheckboxes('show_supplierref$rand') ) return false;\" href='#'>".$LANG['buttons'][19]."</a>";
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

   /*function addSupplierToReference($target,$plugin_order_references_id){
      global $LANG,$DB;

      if ($this->canView()){

         $suppliers = array();
         $reference = new PluginOrderReference;
         $reference->getFromDB($plugin_order_references_id);

         if (!$reference->fields["is_deleted"]){
            $query = "SELECT `suppliers_id`
                     FROM `".$this->getTable()."`
                     WHERE `plugin_order_references_id` = '$plugin_order_references_id'";
            $result = $DB->query($query);
            while ($data = $DB->fetch_array($result))
               $suppliers["suppliers_id"] = $data["suppliers_id"];

            echo "<form method='post' name='add_ref_manu' action=\"$target\">";
            echo "<table class='tab_cadre_fixe'>";
            echo "<input type='hidden' name='plugin_order_references_id' value='" . $plugin_order_references_id . "'>";
            echo "<input type='hidden' name='entities_id' value='".$reference->fields["entities_id"]."'>";
            echo "<input type='hidden' name='is_recursive' value='".$reference->fields["is_recursive"]."'>";
            echo "<tr>";
            echo "<th colspan='3' align='center'>".$LANG['plugin_order']['reference'][2]."</th></tr>";
            echo "<tr>";
            echo "<th>" . $LANG['financial'][26] . "</th>";
            echo "<th>" . $LANG['plugin_order']['reference'][1]. "</th>";
            echo "<th>" . $LANG['plugin_order']['detail'][4] . "</th></tr>";
            echo "<tr>";
            echo "<td class='tab_bg_1' align='center'>";
            Dropdown::show('Supplier', array('name' => "suppliers_id",'used' => $suppliers,'entity' => $_SESSION["glpiactive_entity"]));
            echo "</td>";
            echo "<td class='tab_bg_1' align='center'>";
            autocompletionTextField($this,"reference_code");
            echo "</td>";
            echo "<td class='tab_bg_1' align='center'>";
            echo "<input type='text' name='price_taxfree' value=\"".formatNumber("price_taxfree",true)."\" size='7'>";
            echo "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td class='tab_bg_1' align='center' colspan='3'>";
            echo "<input type='submit' name='add_reference_supplier' value=\"" . $LANG['buttons'][8] . "\" class='submit' >";
            echo "</td>";
            echo "</tr>";
            echo "</table></form>";
            echo "</div>";

         }
      }
   }*/

   function getPriceByReferenceAndSupplier($plugin_order_references_id,$suppliers_id){
      global $DB;

      $query = "SELECT `price_taxfree`
               FROM `".$this->getTable()."` " .
            "WHERE `plugin_order_references_id` = '$plugin_order_references_id'
            AND `suppliers_id` = '$suppliers_id' ";
      $result = $DB->query($query);
      if ($DB->numrows($result) > 0)
         return $DB->result($result,0,"price_taxfree");
      else
         return 0;
   }
   
   function getReferenceCodeByReferenceAndSupplier($plugin_order_references_id,$suppliers_id){
      global $DB;

      $query = "SELECT `reference_code`
               FROM `".$this->getTable()."` " .
            "WHERE `plugin_order_references_id` = '$plugin_order_references_id'
            AND `suppliers_id` = '$suppliers_id' ";
      $result = $DB->query($query);
      if ($DB->numrows($result) > 0)
         return $DB->result($result,0,"reference_code");
      else
         return 0;
   }
}

?>