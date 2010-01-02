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

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT."/inc/includes.php");
$AJAX_INCLUDE=1;
header("Content-Type: text/html; charset=UTF-8");
header_nocache();

if (!defined('GLPI_ROOT')){
   die("Can not acces directly to this file");
}

useplugin('order', true);

$type=$_POST["type"];
if (isset($_POST["action"])){
	switch($_POST["action"]){
		case "reception":
			echo "</td><td>";
			showDateFormItem("date",date("Y-m-d"),true,1);
			echo "</td><td>";
			echo $LANG['financial'][19]."&nbsp;";
			autocompletionTextField("deliverynum","glpi_plugin_order_detail","deliverynum",'',20,$_SESSION["glpiactive_entity"]);
			echo "</td><td><input type='submit' name='reception' class='submit' value='".$LANG['buttons'][2]."'></td>";
         break;
   case "bulk_reception":
			echo "</td><td>";
			showDateFormItem("date",date("Y-m-d"),true,1);
			echo "</td><td>";
			echo $LANG['financial'][19]."&nbsp;";
			autocompletionTextField("deliverynum","glpi_plugin_order_detail","deliverynum",'',20,$_SESSION["glpiactive_entity"]);
         echo "</td><td>";
         echo "<input type='hidden' name='referenceID' value='".$_POST['referenceID']."'>";
         echo "<input type='hidden' name='orderID' value='".$_POST['orderID']."'>";
			echo $LANG['plugin_order']['delivery'][6]."&nbsp;<input type='text' name='number_reception' size='2' value='0'>";
			echo "</td><td><input type='submit' name='bulk_reception' class='submit' value='".$LANG['buttons'][2]."'></td>";
         break;
		case "generation":
			echo "<input type='hidden' name='referenceID' value='".$_POST["referenceID"]."'>"; 
			echo"<input type='submit' name='generation' class='submit' value='".$LANG['buttons'][2]."'>"; 
         break;
   	case "createLink":
			echo "<input type='hidden' name='FK_type' value='$type'>";
			$reference = new PluginOrderReference;
			$reference->getFromDB($_POST["referenceID"]);
			plugin_order_dropdownAllItemsByType("device", $type, $_SESSION["glpiactive_entity"],$reference->fields["FK_type"],$reference->fields["FK_model"]);
			echo "&nbsp;<input type='submit' name='createLinkWithDevice' class='submit' value='".$LANG['buttons'][2]."'>";
         break;
   	case "deleteLink":
			echo "&nbsp;<input type='submit' name='deleteLinkWithDevice' class='submit' value='".$LANG['buttons'][2]."'>";
         break;
	}
}
?>