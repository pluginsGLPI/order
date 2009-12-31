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

if(strpos($_SERVER['PHP_SELF'],"dropdownContact.php")){
	define('GLPI_ROOT','../../..');
	$AJAX_INCLUDE=1;
	include (GLPI_ROOT."/inc/includes.php");
	header("Content-Type: text/html; charset=UTF-8");
	header_nocache();
}
if (!defined('GLPI_ROOT')){
   die("Can not acces directly to this file");
}

checkCentralAccess();
// Make a select box with all glpi users
$where=" WHERE `glpi_contact_enterprise`.`FK_contact` = `glpi_contacts`.`ID` AND (`glpi_contact_enterprise`.`FK_enterprise` = '".$_POST['FK_enterprise']."' AND `glpi_contacts`.`deleted` = '0' ) ";


if (isset($_POST["entity_restrict"])){
   if (!is_numeric($_POST["entity_restrict"]) && !is_array($_POST["entity_restrict"])) {
      $_POST["entity_restrict"] = unserialize(stripslashes($_POST["entity_restrict"]));
   }
	$where.=getEntitiesRestrictRequest("AND","glpi_contacts",'',$_POST["entity_restrict"],true);
} else {
	$where.=getEntitiesRestrictRequest("AND","glpi_contacts",'','',true);
}

if ($_POST['searchText']!=$CFG_GLPI["ajax_wildcard"])
	$where.=" AND `glpi_contacts`.`name` ".makeTextSearch($_POST['searchText']);

$NBMAX=$CFG_GLPI["dropdown_max"];
$LIMIT="LIMIT 0,$NBMAX";
if ($_POST['searchText']==$CFG_GLPI["ajax_wildcard"]) $LIMIT="";


$query = "SELECT `glpi_contacts`.*
	FROM `glpi_contacts`,`glpi_contact_enterprise`
	$where
	ORDER BY `FK_entities`, `name` $LIMIT";
//error_log($query);
$result = $DB->query($query);

echo "<select name=\"FK_contact\">";

echo "<option value=\"0\">-----</option>";

if ($DB->numrows($result)) {
	$prev=-1;
	while ($data=$DB->fetch_array($result)) {
		if ($data["FK_entities"]!=$prev) {
			if ($prev>=0) {
				echo "</optgroup>";
			}
			$prev=$data["FK_entities"];
			echo "<optgroup label=\"". getDropdownName("glpi_entities", $prev) ."\">";
		}
		$output=formatUserName($data["ID"],"",$data["name"],$data["firstname"]);
		if($_SESSION["glpiview_ID"]||empty($output)){
			$output.=" (".$data["ID"].")";
		}
		echo "<option value=\"".$data["ID"]."\" title=\"".cleanInputText($output)."\">".substr($output,0,$_SESSION["glpidropdown_limit"])."</option>";
	}
	if ($prev>=0) {
		echo "</optgroup>";
	}
}
echo "</select>";

?>