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

class PluginOrderReferenceManufacturer extends CommonDBTM {

	function __construct() {
		$this->table = "glpi_plugin_order_references_manufacturers";
		$this->type = PLUGIN_ORDER_REFERENCE_MANUFACTURER_TYPE;
		$this->entity_assign=true;
		$this->may_be_recursive=false;
		$this->dohistory=true;
	}
	
	function getFromDBByReference($FK_reference) {
		global $DB;
		
		$query = "SELECT * FROM `".$this->table."`
					WHERE `FK_reference` = '" . $FK_reference . "' ";
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

	function defineTabs($ID, $withtemplate) {
		global $LANG;
		/* principal */
		$ong[1] = $LANG['title'][26];
		if (haveRight("document", "r"))
			$ong[4] = $LANG['Menu'][27];

		return $ong;
	}

	function showForm($target, $ID) {
		global $LANG, $DB, $CFG_GLPI, $INFOFORM_PAGES;

      $canedit = plugin_order_haveRight("reference","w");
      $this->getFromDB($ID);
      $this->showTabs($ID, false, 3);
      echo "<form method='post' name='show_ref_manu' id='show_ref_manu' action=\"$target\">";
      echo "<input type='hidden' name='FK_entities' value='".$this->fields["FK_entities"]."'>";
      echo "<input type='hidden' name='ID' value='" . $ID . "'>";
      echo "<input type='hidden' name='FK_reference' value='" . $this->fields["FK_reference"] . "'>";
      echo "<div class='center' id='tabsbody'>";
      echo "<table class='tab_cadre_fixe'>";
      $this->showFormHeader($ID,'',2);

      echo "<tr>";
      echo "<th>" . $LANG['financial'][26] . "</th>";
      echo "<th>" . $LANG['plugin_order']['reference'][10]. "</th>";
      echo "<th>" . $LANG['plugin_order']['detail'][4] . "</th></tr>";
      echo "<td class='tab_bg_1' align='center'><a href=\"" . $CFG_GLPI["root_doc"] . "/" . $INFOFORM_PAGES[ENTERPRISE_TYPE] . "?ID=" . $this->fields["FK_enterprise"] . "\">" . getDropdownName("glpi_enterprises", $this->fields["FK_enterprise"]) . "</a></td>";
      echo "<td class='tab_bg_1' align='center'>";
      if ($canedit)
         autocompletionTextField("reference_code", "glpi_plugin_order_references_manufacturers", "reference_code", $this->fields["reference_code"], 20);
      else
         echo $this->fields["reference_code"];
      echo "</td>";

      echo "<td class='tab_bg_1' align='center'>";
      if ($canedit)
		echo "<input type='text' name='price_taxfree' value=\"".formatNumber($this->fields["price_taxfree"],true)."\" size='7'>";
      else
         echo formatNumber($this->fields["price_taxfree"]);

      echo "</td></tr>";
      if ($canedit)
      {
         echo "<tr>";
         echo "<td class='tab_bg_1'align='center' colspan='3'>";
         echo "<input type='submit' name='update' value=\"" . $LANG['buttons'][7] . "\" class='submit' >";
         echo "</td>";
         echo "</tr>";
      }
      echo "</table></div></form>";
      echo "<div id='tabcontent'></div>";
      echo "<script type='text/javascript'>loadDefaultTab();</script>";

		return true;
	}

	function showReferenceManufacturers($target, $ID) {
      global $LANG, $DB, $CFG_GLPI,$INFOFORM_PAGES;

      $ref = new PluginOrderReference;
      $ref->getFromDB($ID);

      initNavigateListItems($this->type,$LANG['plugin_order']['reference'][1] ." = ". $ref->fields["name"]);

      $candelete = plugin_order_haveRight("reference","w");
      $query = "SELECT * FROM `".$this->table."` WHERE `FK_reference` = '$ID' ";
      $result = $DB->query($query);
      $rand=mt_rand();
      echo "<div class='center'>";
      echo "<form method='post' name='show_ref_manu$rand' id='show_ref_manu$rand' action=\"$target\">";
      echo "<input type='hidden' name='FK_reference' value='" . $ID . "'>";
      echo "<table class='tab_cadrehov'>";

      echo "<tr><th></th>";
      echo "<th>" . $LANG['financial'][26] . "</th>";
      echo "<th>" . $LANG['plugin_order']['reference'][10] . "</th>";
      echo "<th>" . $LANG['plugin_order']['detail'][4] . "</th>";
      echo "</tr>";

      if ($DB->numrows($result) > 0) {
         echo "<form method='post' name='show_ref_manu' action=\"$target\">";
         echo "<input type='hidden' name='FK_reference' value='" . $ID . "'>";

         while ($data = $DB->fetch_array($result)) {
            addToNavigateListItems($this->type,$data['ID']);
            echo "<input type='hidden' name='item[" . $data["ID"] . "]' value='" . $ID . "'>";
            echo "<tr>";
            echo "<td class='tab_bg_1'>";
            if ($candelete) {
               echo "<input type='checkbox' name='check[" . $data["ID"] . "]'";
               if (isset($_POST['check']) && $_POST['check'] == 'all')
                  echo " checked ";
               echo ">";
            }
            echo "</td>";
            echo "<td class='tab_bg_1' align='center'><a href=\"".GLPI_ROOT."/".$INFOFORM_PAGES[$this->type]."?ID=".$data["ID"]."&referenceID=".$data["FK_enterprise"]."\">" . getDropdownName("glpi_enterprises", $data["FK_enterprise"]) . "</a></td>";
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
            echo "<td><img src=\"".$CFG_GLPI["root_doc"]."/pics/arrow-left.png\" alt=''></td><td align='center'><a onclick= \"if ( markCheckboxes('show_ref_manu$rand') ) return false;\" href='".$_SERVER['HTTP_REFERER']."?ID=$ID&amp;check=all'>".$LANG["buttons"][18]."</a></td>";
            echo "<td>/</td><td align='center'><a onclick= \"if ( unMarkCheckboxes('show_ref_manu$rand') ) return false;\" href='".$_SERVER['HTTP_REFERER']."?ID=$ID&amp;check=none'>".$LANG["buttons"][19]."</a>";
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

   function addSupplierToReference($target,$referenceID){
      global $LANG,$DB;

      if (plugin_order_haveRight("reference","w")){

         $suppliers = array();
         $reference = new PluginOrderReference;
         $reference->getFromDB($referenceID);

         if (!$reference->fields["deleted"]){
            $query = "SELECT `FK_enterprise`
                     FROM `".$this->table."`
                     WHERE `FK_reference` = '$referenceID'";
            $result = $DB->query($query);
            while ($data = $DB->fetch_array($result))
               $suppliers["FK_enterprise"] = $data["FK_enterprise"];

            echo "<form method='post' name='add_ref_manu' action=\"$target\">";
            echo "<table class='tab_cadrehov'>";
            echo "<input type='hidden' name='FK_reference' value='" . $referenceID . "'>";
            echo "<tr>";
            echo "<th colspan='3' align='center'>".$LANG['plugin_order']['reference'][2]."</th></tr>";
            echo "<tr>";
            echo "<th>" . $LANG['financial'][26] . "</th>";
            echo "<th>" . $LANG['plugin_order']['reference'][10]. "</th>";
            echo "<th>" . $LANG['plugin_order']['detail'][4] . "</th></tr>";
            echo "<tr>";
            echo "<td class='tab_bg_1' align='center'>";
            dropdownValue("glpi_enterprises","FK_enterprise","",1,$_SESSION["glpiactive_entity"],'',$suppliers);
            echo "</td>";
            echo "<td class='tab_bg_1' align='center'>";
            autocompletionTextField("reference_code", $this->table, "reference_code", '', 20);
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

   function getPriceByReferenceAndSupplier($referenceID,$supplierID){
      global $DB;

      $query = "SELECT `price_taxfree`
               FROM `".$this->table."` " .
            "WHERE `FK_reference` = '$referenceID'
            AND `FK_enterprise` = '$supplierID' ";
      $result = $DB->query($query);
      if ($DB->numrows($result) > 0)
         return $DB->result($result,0,"price_taxfree");
      else
         return 0;
   }
   
   function getReferenceCodeByReferenceAndSupplier($referenceID,$supplierID){
      global $DB;

      $query = "SELECT `reference_code`
               FROM `".$this->table."` " .
            "WHERE `FK_reference` = '$referenceID'
            AND `FK_enterprise` = '$supplierID' ";
      $result = $DB->query($query);
      if ($DB->numrows($result) > 0)
         return $DB->result($result,0,"reference_code");
      else
         return 0;
   }
}

?>