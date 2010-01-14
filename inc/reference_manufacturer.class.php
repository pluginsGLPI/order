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

class PluginOrderReference_Manufacturer extends CommonDBTM {

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
	
   /*
   if (plugin_order_haveRight("reference", "r")) {

		$sopt[PLUGIN_ORDER_REFERENCE_MANUFACTURER_TYPE][1]['table'] = 'glpi_plugin_order_references_manufacturers';
		$sopt[PLUGIN_ORDER_REFERENCE_MANUFACTURER_TYPE][1]['field'] = 'price_taxfree';
		$sopt[PLUGIN_ORDER_REFERENCE_MANUFACTURER_TYPE][1]['linkfield'] = 'price_taxfree';
		$sopt[PLUGIN_ORDER_REFERENCE_MANUFACTURER_TYPE][1]['name'] = $LANG['plugin_order']['detail'][4];

	}
	*/
	
	function defineTabs($ID, $withtemplate) {
		global $LANG;
		/* principal */
		$ong[1] = $LANG['title'][26];
		if (haveRight("document", "r"))
			$ong[4] = $LANG['Menu'][27];

		return $ong;
	}

	function showForm($target, $ID, $plugin_order_references_id) {
		global $LANG;
      
      $PluginOrderReference=new PluginOrderReference();
      $PluginOrderReference->getFromDB($plugin_order_references_id);
      
      $this->fields["entities_id"] = $PluginOrderReference->fields["entities_id"];
      
      if (!plugin_order_haveRight("reference","r")) return false;
		
		if ($ID > 0) {
         $PluginOrderReference->check($plugin_order_references_id,'r');
      } else {
         // Create item
         $PluginOrderReference->check(-1,'w');
         $this->getEmpty();
      }

      $canedit=$PluginOrderReference->can($plugin_order_references_id,'w');
		
      $this->showTabs($ID, "");
      $this->showFormHeader($target,$ID,"",1);
      
      echo "<tr class='tab_bg_2'><td>" . $LANG['financial'][26] . ": </td>";
      echo "<td>";
      $link=getItemTypeFormURL('Supplier');
      echo "<a href=\"" . $link. "?id=" . $this->fields["suppliers_id"] . "\">" . Dropdown::getDropdownName("glpi_suppliers", $this->fields["suppliers_id"]) . "</a>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'><td>" . $LANG['plugin_order']['reference'][10] . ": </td>";
      echo "<td>";
      if ($canedit)
         autocompletionTextField($this,"reference_code");
      else
         echo $this->fields["reference_code"];
      echo "</td></tr>";

      echo "<input type='hidden' name='plugin_order_references_id' value='" . $this->fields["plugin_order_references_id"] . "'>";
      
      echo "<tr class='tab_bg_2'><td>" . $LANG['plugin_order']['detail'][4] . ": </td>";
      echo "<td>";
      if ($canedit)
         echo "<input type='text' name='price_taxfree' value=\"".formatNumber($this->fields["price_taxfree"],true)."\" size='7'>";
      else
         echo formatNumber($this->fields["price_taxfree"]);
      echo "</td></tr>";
      
      echo "</td></tr>";
      $this->showFormButtons($ID,"",1,false);
      echo "<div id='tabcontent'></div>";
      echo "<script type='text/javascript'>loadDefaultTab();</script>";

		return true;
	}

	function showReferenceManufacturers($target, $ID) {
      global $LANG, $DB, $CFG_GLPI,$INFOFORM_PAGES;

      $ref = new PluginOrderReference;
      $ref->getFromDB($ID);

      initNavigateListItems($this->getType(),$LANG['plugin_order']['reference'][1] ." = ". $ref->fields["name"]);

      $candelete = plugin_order_haveRight("reference","w");
      $query = "SELECT * FROM `".$this->getTable()."` WHERE `plugin_order_references_id` = '$ID' ";
      $result = $DB->query($query);
      $rand=mt_rand();
      echo "<div class='center'>";
      echo "<form method='post' name='show_ref_manu$rand' id='show_ref_manu$rand' action=\"$target\">";
      echo "<input type='hidden' name='plugin_order_references_id' value='" . $ID . "'>";
      echo "<table class='tab_cadrehov'>";

      echo "<tr><th></th>";
      echo "<th>" . $LANG['financial'][26] . "</th>";
      echo "<th>" . $LANG['plugin_order']['reference'][10] . "</th>";
      echo "<th>" . $LANG['plugin_order']['detail'][4] . "</th>";
      echo "</tr>";

      if ($DB->numrows($result) > 0) {
         echo "<form method='post' name='show_ref_manu' action=\"$target\">";
         echo "<input type='hidden' name='plugin_order_references_id' value='" . $ID . "'>";

         while ($data = $DB->fetch_array($result)) {
            addToNavigateListItems($this->getType(),$data['id']);
            echo "<input type='hidden' name='item[" . $data["id"] . "]' value='" . $ID . "'>";
            echo "<tr>";
            echo "<td class='tab_bg_1'>";
            if ($candelete) {
               echo "<input type='checkbox' name='check[" . $data["id"] . "]'";
               if (isset($_POST['check']) && $_POST['check'] == 'all')
                  echo " checked ";
               echo ">";
            }
            echo "</td>";
            $link=getItemTypeFormURL($this->getType());
            echo "<td class='tab_bg_1' align='center'><a href='".$link."?id=".$data["id"]."&plugin_order_references_id=".$ID."'>" .Dropdown::getDropdownName("glpi_suppliers", $data["suppliers_id"]) . "</a></td>";
            echo "<td class='tab_bg_1' align='center'>";
            echo $data["reference_code"];
            echo "</td>";
            echo "<td class='tab_bg_1' align='center'>";
            echo formatNumber($data["price_taxfree"]);
            echo "</td>";
            echo "</tr>";
         }
         echo "</table>";

         if ($candelete)
         {
            echo "<table width='80%' class='tab_glpi'>";
            echo "<tr>";
            echo "<td><img src=\"".$CFG_GLPI["root_doc"]."/pics/arrow-left.png\" alt=''></td><td align='center'><a onclick= \"if ( markCheckboxes('show_ref_manu$rand') ) return false;\" href='".$_SERVER['HTTP_REFERER']."?id=$ID&amp;check=all'>".$LANG["buttons"][18]."</a></td>";
            echo "<td>/</td><td align='center'><a onclick= \"if ( unMarkCheckboxes('show_ref_manu$rand') ) return false;\" href='".$_SERVER['HTTP_REFERER']."?id=$ID&amp;check=none'>".$LANG["buttons"][19]."</a>";
            echo "</td><td align='left' width='90%'>";
            echo "<input type='submit' name='delete_reference_manufacturer' value=\"" . $LANG['buttons'][6] . "\" class='submit' >";
            echo "</td>";
            echo "</tr>";
            echo "</table>";
         }
      }
      else
         echo "</table>";

      echo "</form>";
      echo "</div>";
   }

   function addSupplierToReference($target,$plugin_order_references_id){
      global $LANG,$DB;

      if (plugin_order_haveRight("reference","w")){

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
            echo "<table class='tab_cadrehov'>";
            echo "<input type='hidden' name='plugin_order_references_id' value='" . $plugin_order_references_id . "'>";
            echo "<tr>";
            echo "<th colspan='3' align='center'>".$LANG['plugin_order']['reference'][2]."</th></tr>";
            echo "<tr>";
            echo "<th>" . $LANG['financial'][26] . "</th>";
            echo "<th>" . $LANG['plugin_order']['reference'][10]. "</th>";
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
            echo "<input type='submit' name='add_reference_manufacturer' value=\"" . $LANG['buttons'][8] . "\" class='submit' >";
            echo "</td>";
            echo "</tr>";
            echo "</table></form>";
            echo "</div>";

         }
      }
   }

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
}

?>