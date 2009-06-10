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
    Original Author of file: 
    Purpose of file:
    ----------------------------------------------------------------------*/

/*order dropdown selection */
function plugin_order_dropdownorder($myname,$entity_restrict='',$used=array()) {
	global $DB,$LANG,$CFG_GLPI;

	$rand=mt_rand();
	$where=" WHERE glpi_plugin_order.deleted='0' ";
	$where.=getEntitiesRestrictRequest("AND","glpi_plugin_order",'',$entity_restrict,true);
	if (count($used)) {
		$where .= " AND ID NOT IN (0";
		foreach ($used as $ID)
			$where .= ",$ID";
		$where .= ")";
	}
	$query="SELECT * 
			FROM glpi_dropdown_plugin_order_taxes 
			WHERE ID IN (
				SELECT DISTINCT taxes 
				FROM glpi_plugin_order 
				$where) 
			GROUP BY name ORDER BY name";
	$result=$DB->query($query);

	echo "<select name='_taxes' id='taxes_order'>\n";
	echo "<option value='0'>------</option>\n";
	while ($data=$DB->fetch_assoc($result)){
		echo "<option value='".$data['ID']."'>".$data['name']."</option>\n";
	}
	echo "</select>\n";

	$params=array('taxes_order'=>'__VALUE__',
			'entity_restrict'=>$entity_restrict,
			'rand'=>$rand,
			'myname'=>$myname,
			'used'=>$used
			);

	ajaxUpdateItemOnSelectEvent("taxes_order","show_$myname$rand",$CFG_GLPI["root_doc"]."/plugins/order/ajax/dropdownTypeorder.php",$params);

	echo "<span id='show_$myname$rand'>";
	$_POST["entity_restrict"]=$entity_restrict;
	$_POST["taxes_order"]=0;
	$_POST["myname"]=$myname;
	$_POST["rand"]=$rand;
	$_POST["used"]=$used;
	include (GLPI_ROOT."/plugins/order/ajax/dropdownTypeorder.php");
	echo "</span>\n";

	return $rand;
}

function plugin_order_dropdownAllItems($myname,$ajax=false,$value=0,$orderID=0,$supplier=0,$entity=0,$ajax_page='') {
    global $LANG,$CFG_GLPI;
	$types = array (COMPUTER_TYPE, MONITOR_TYPE, NETWORKING_TYPE, PHONE_TYPE, PRINTER_TYPE, PERIPHERAL_TYPE, CONSUMABLE_TYPE, CARTRIDGE_TYPE);
 	
    $ci=new CommonItem();

   	echo "<select name=\"$myname\" id='$myname'>";
	echo "<option value='0' selected>------</option>\n";
	
    foreach ($types as $tmp => $type){
		$ci->setType($type);
		echo "<option value='$type' ".($type==$value?" selected":'').">".$ci->getType()."</option>\n";
    }
	echo "</select>";
	
	if ($ajax)
	{
		$params=array('type'=>'__VALUE__',
		'FK_enterprise'=>$supplier,
		'entity_restrict'=>$entity,
		'orderID'=>$orderID,
		);

		ajaxUpdateItemOnSelectEvent($myname,"show_reference",$ajax_page,$params);
	}
}

function plugin_order_dropdownTemplate($name,$entity,$table,$value='')
{
	global $DB;
	$result = $DB->query("SELECT tplname, ID FROM ".$table." WHERE FK_entities=".$entity." AND tplname <> '' GROUP BY tplname ORDER BY tplname");
	
	$rand=mt_rand();
	echo "<select name='$name' id='dropdown_".$name.$rand."'>";

	echo "<option value='0'".($value==0?" selected ":"").">-------------</option>";

	while ($data = $DB->fetch_array($result))
		echo "<option value='".$data["ID"]."'".($value==$data["tplname"]?" selected ":"").">".$data["tplname"]."</option>";

	echo "</select>";	
	return $rand;
}

function plugin_order_dropdownReferencesByEnterprise($name,$type,$enterpriseID)
{
	$references = plugin_order_getAllReferencesByEnterpriseAndType($type,$enterpriseID);
	$references[0] = '-----';
	return dropdownArrayValues($name, $references,0);
}

function plugin_order_dropdownAllItemsByType($name,$type,$entity)
{
	$items = getAllItemsByType($type, $entity);
	$items[0] = '-----';
	return dropdownArrayValues($name, $items,0);
}

function plugin_order_dropdownReceptionActions($type)
{
	global $LANG, $CFG_GLPI;
	$rand=mt_rand();
	echo "<td width='5%'>";
	echo "<select name='receptionActions$rand' id='receptionActions$rand'>";
	echo "<option value='0' selected>-----</option>";
	echo "<option value='reception'>".$LANG['plugin_order']['delivery'][2]."</option>";
	echo "<option value='generation'>".$LANG['plugin_order']['delivery'][3]."</option>";
	echo "<option value='createLink'>".$LANG['plugin_order']['delivery'][11]."</option>";
	echo "<option value='deleteLink'>".$LANG['plugin_order']['delivery'][12]."</option>";
	echo "</select>";
	$params=array('action'=>'__VALUE__',
					'type'=>$type);
	ajaxUpdateItemOnSelectEvent("receptionActions$rand","show_receptionActions$rand",$CFG_GLPI["root_doc"]."/plugins/order/ajax/receptionactions.php",$params);
	echo "</td>";
	echo "<td valign=middle><span id='show_receptionActions$rand'>&nbsp;</span></td>";
}
?>