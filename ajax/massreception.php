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
 
$AJAX_INCLUDE=1;
define('GLPI_ROOT','../../..');
include (GLPI_ROOT."/inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
header_nocache();

checkLoginUser();

useplugin('order', true);

$PluginOrderReception = new PluginOrderReception();

echo "<table width='950px' class='tab_cadre' width='50%'>";
echo "<tr class='tab_bg_2'><td>";
showDateFormItem("date",date("Y-m-d"),true,1);
echo "</td><td>";
echo $LANG['financial'][19]."&nbsp;";
autocompletionTextField("deliverynum","glpi_plugin_order_detail","deliverynum",'',20,$_SESSION["glpiactive_entity"]);
echo "</td><td>";
echo "<input type='hidden' name='referenceID' value='".$_POST['referenceID']."'>";
echo "<input type='hidden' name='orderID' value='".$_POST['orderID']."'>";
echo $LANG['plugin_order']['delivery'][6];
$nb = $PluginOrderReception->checkItemStatus($_POST['orderID'],$_POST['referenceID'], ORDER_DEVICE_NOT_DELIVRED);
dropdownInteger('number_reception','',1,$nb);
echo "</td><td><input type='submit' name='bulk_reception' class='submit' value='".$LANG['buttons'][2]."'></td></tr></table>";
			
ajaxFooter();

?>
