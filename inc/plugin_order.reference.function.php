<?php

/*----------------------------------------------------------------------
   GLPI - Gestionnaire Libre de Parc Informatique
   Copyright (C) 2003-2008 by the INDEPNET Development Team.

   http://indepnet.net/   http://glpi-project.org/
   ----------------------------------------------------------------------
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
   ----------------------------------------------------------------------*/
/*----------------------------------------------------------------------
    Original Author of file: Walid Nouh
    Purpose of file:
    ----------------------------------------------------------------------*/
function plugin_order_showReferenceManufacturers($target, $ID) {
	global $LANG, $DB, $CFG_GLPI,$INFOFORM_PAGES;
	
	$ref = new PluginOrderReference;
	$ref->getFromDB($ID);
			
	initNavigateListItems(PLUGIN_ORDER_REFERENCE_MANUFACTURER_TYPE,$LANG['plugin_order']['reference'][1] ." = ". $ref->fields["name"]);
	
	$candelete = plugin_order_haveRight("reference","w");
	$query = "SELECT * FROM `glpi_plugin_order_references_manufacturers` WHERE FK_reference='$ID'";
	$result = $DB->query($query);
	$rand=mt_rand();
	echo "<div class='center'>";
	echo "<form method='post' name='show_ref_manu$rand' id='show_ref_manu$rand' action=\"$target\">";
	echo "<input type='hidden' name='FK_reference' value='" . $ID . "'>";
	echo "<table class='tab_cadrehov'>";

	echo "<tr><th></th><th>" . $LANG['financial'][26] . "</th><th>" . $LANG['plugin_order']['detail'][4] . "</th></tr>";

	if ($DB->numrows($result) > 0) {
		echo "<form method='post' name='show_ref_manu' action=\"$target\">";
		echo "<input type='hidden' name='FK_reference' value='" . $ID . "'>";

		while ($data = $DB->fetch_array($result)) {
			addToNavigateListItems(PLUGIN_ORDER_REFERENCE_MANUFACTURER_TYPE,$data['ID']);
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
			echo "<td class='tab_bg_1' align='center'><a href=\"".GLPI_ROOT."/".$INFOFORM_PAGES[PLUGIN_ORDER_REFERENCE_MANUFACTURER_TYPE]."?ID=".$data["ID"]."&referenceID=".$data["FK_enterprise"]."\">" . getDropdownName("glpi_enterprises", $data["FK_enterprise"]) . "</a></td>";
			echo "<td class='tab_bg_1' align='center'>";
			echo $data["price_taxfree"];
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

function plugin_order_addSupplierToReference($target,$referenceID)
{
	global $LANG,$DB;

		if (plugin_order_haveRight("reference","w"))
		{

			$suppliers = array();
			$reference = new PluginOrderReference;
			$reference->getFromDB($referenceID);
			
			if (!$reference->fields["deleted"]){
			$query = "SELECT FK_enterprise FROM `glpi_plugin_order_references_manufacturers` WHERE FK_reference='$referenceID'";
			$result = $DB->query($query);
			while ($data = $DB->fetch_array($result))
				$suppliers["FK_enterprise"] = $data["FK_enterprise"];
				
			echo "<form method='post' name='add_ref_manu' action=\"$target\">";
			echo "<table class='tab_cadrehov'>";
			echo "<input type='hidden' name='FK_reference' value='" . $referenceID . "'>";
			echo "<tr>";
			echo "<th colspan='2' align='center'>".$LANG['plugin_order']['reference'][2]."</th></tr>";
			echo "<tr><th>" . $LANG['financial'][26] . "</th><th>" . $LANG['plugin_order']['detail'][4] . "</th></tr>";
			echo "<tr>";
			echo "<td class='tab_bg_1' align='center'>"; 
			dropdownValue("glpi_enterprises","FK_enterprise","",1,$_SESSION["glpiactive_entity"],'',$suppliers); 
			echo "</td>";
			echo "<td class='tab_bg_1' align='center'>";
			autocompletionTextField("price_taxfree", "glpi_plugin_order_references_manufacturers", "price_taxfree", 0, 7);
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

function plugin_order_showReferencesBySupplierID($ID)
{
	global $LANG, $DB, $CFG_GLPI,$INFOFORM_PAGES;
	$query = "SELECT gr.ID, gr.FK_glpi_enterprise, gr.FK_entities, gr.type, gr.name, grm.price_taxfree " .
			"FROM `glpi_plugin_order_references_manufacturers` as grm, `glpi_plugin_order_references` as gr " .
			"WHERE grm.FK_enterprise='$ID' AND grm.FK_reference=gr.ID";
	$result = $DB->query($query);


	echo "<div class='center'>";
	echo "<table class='tab_cadre_fixe'>";
	echo "<tr><th colspan='5'>".$LANG['plugin_order']['reference'][3]."</th></tr>";
	echo "<tr>"; 
	echo "<th>".$LANG['entity'][0]."</th>";
	echo "<th>".$LANG['common'][5]."</th>";
	echo "<th>".$LANG['plugin_order']['reference'][1]."</th>";
	echo "<th>". $LANG['common'][17]."</th><th>".$LANG['plugin_order']['detail'][4]."</th></tr>";
	
	if ($DB->numrows($result) > 0)
	{
		$commonitem = new CommonItem;
		while ($data = $DB->fetch_array($result))
		{
			echo "<tr class='tab_bg_1' align='center'>";
			echo "<td>";
			echo getDropdownName("glpi_entities",$data["FK_entities"]);
			echo "</td>";

			echo "<td>";
			echo getDropdownName("glpi_dropdown_manufacturer",$data["FK_glpi_enterprise"]);
			echo "</td>";

			echo "<td>";
			echo getReceptionReferenceLink($data["ID"], $data["name"]);
			echo "</td>";
			echo "<td>"; 
			$commonitem->setType($data["type"]);
			echo $commonitem->getType();
			echo "</td>";
			echo "<td>";
			echo $data["price_taxfree"];
			echo "</td>";
			echo "</tr>";	
		}
	}
	echo "</table>";	
	echo "</div>";
	
}

function plugin_order_getAllReferencesByEnterpriseAndType($type,$enterpriseID)
{
	global $DB;
	$query = "SELECT gr.name, gr.ID FROM `glpi_plugin_order_references` as gr, `glpi_plugin_order_references_manufacturers` as grm" .
			" WHERE gr.type=$type AND grm.FK_enterprise=$enterpriseID AND grm.FK_reference=gr.ID";

	$result = $DB->query($query);
	$references = array();
	while ($data = $DB->fetch_array($result))
		$references[$data["ID"]] = $data["name"];

	return $references;		
}

function plugin_order_getPriceByReferenceAndSupplier($referenceID,$supplierID)
{
	global $DB;
	$query = "SELECT price_taxfree FROM `glpi_plugin_order_references_manufacturers` " .
			"WHERE FK_reference=$referenceID AND FK_enterprise=$supplierID";
	$result = $DB->query($query);
	if ($DB->numrows($result) > 0)
		return $DB->result($result,0,"price_taxfree");
	else
		return 0;	
}

function plugin_order_getModelTable($device_type)
{
	global $ORDER_MODEL_TABLES;
	if(isset($ORDER_MODEL_TABLES[$device_type]))
		return $ORDER_MODEL_TABLES[$device_type];
	else
		return false;	
}

function plugin_order_getTypeTable($device_type)
{
	global $ORDER_TYPE_TABLES;
	
	if(isset($ORDER_TYPE_TABLES[$device_type]))
		return $ORDER_TYPE_TABLES[$device_type];
	else
		return false;	
}

function plugin_order_isSupplierInReferenceInUse($referenceID,$supplierID)
{
	global $DB;
	$query = "SELECT COUNT(*) as cpt FROM `glpi_plugin_order_detail` as detail," .
			" `glpi_plugin_order_references` as ref, ".	
			" `glpi_plugin_order` as gorder".
			" WHERE gorder.FK_enterprise=$supplierID " .
			" AND gorder.ID=detail.FK_order" .
			" AND ref.ID=detail.FK_reference" .
			" AND ref.ID=$referenceID";
	$result = $DB->query($query);
	if ($DB->result($result,0,"cpt") > 0)
		return true;
	else
		return false;			
}

function getReceptionReferenceLink($ID, $name) {
	global $CFG_GLPI, $INFOFORM_PAGES;
	if (plugin_order_haveRight("reference","r"))
		return "<a href=" . $CFG_GLPI["root_doc"] . "/" . $INFOFORM_PAGES[PLUGIN_ORDER_REFERENCE_TYPE] . "?ID=" . $ID . "'>" . $name . "</a>";
	else
		return $name;
}
?>