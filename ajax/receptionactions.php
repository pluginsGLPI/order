<?php
/*
 * @version $Id: dropdownDocument.php 4635 2007-03-25 14:21:15Z moyo $
 ------------------------------------------------------------------------- 
 GLPI - Gestionnaire Libre de Parc Informatique 
 Copyright (C) 2003-2008 by the INDEPNET Development Team.

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
 */

// ----------------------------------------------------------------------
// Original Author of file: Julien Dombre
// Purpose of file:
// ----------------------------------------------------------------------

// Direct access to file
define('GLPI_ROOT', '../../..');
include (GLPI_ROOT."/inc/includes.php");
include (GLPI_ROOT."/plugins/order/inc/plugin_order.functions_dropdown.php");
include (GLPI_ROOT."/plugins/order/inc/plugin_order.reception.function.php");
$AJAX_INCLUDE=1;

header("Content-Type: text/html; charset=UTF-8");
header_nocache();

if (!defined('GLPI_ROOT'))
	die("Can not acces directly to this file");

$type=$_POST["type"];
if (isset($_POST["action"])){
	switch($_POST["action"]){
		case "reception":
			echo "</td><td valign='bottom'>";
			showDateFormItem("date",date("Y-m-d"),true,1);
			echo "</td><td valign='bottom'><input type='submit' name='reception' class='submit' value='".$LANG['buttons'][2]."'></td>";
		break;
		case "generation":
			echo "</td><td valign='bottom'><input type='submit' name='generation' class='submit' value='".$LANG['buttons'][2]."'></td>";
		break;
		case "createLink":
			echo "</td><td valign='bottom'>";
			echo "<input type='hidden' name='FK_type' value='$type'>";
			plugin_order_dropdownAllItemsByType("device", $type, $_SESSION["glpiactive_entity"]);
			echo "</td><td valign='bottom'><input type='submit' name='createLinkWithDevice' class='submit' value='".$LANG['buttons'][2]."'></td>";
		break;
		case "deleteLink":
			echo "</td><td valign='bottom'><input type='submit' name='deleteLinkWithDevice' class='submit' value='".$LANG['buttons'][2]."'></td>";
		break;
	}
}
?>