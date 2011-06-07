<?php
/*
 * @version $Id: HEADER 2011-03-23 15:41:26 tsmr $
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
// Purpose of file: plugin order v1.4.0 - GLPI 0.80
// ----------------------------------------------------------------------
// ---------------------------------------------------------------------- */

define('GLPI_ROOT','../../..');
include (GLPI_ROOT."/inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
header_nocache();

checkLoginUser();

$PluginOrderReception = new PluginOrderReception();

echo "<table width='950px' class='tab_cadre_fixe'>";
echo "<tr class='tab_bg_2'><td>".$LANG['plugin_order']['detail'][21]."</td><td>";
showDateFormItem("delivery_date",date("Y-m-d"),true,1);
echo "</td><td>";
echo $LANG['financial'][19]."</td><td>";
echo "<input type='text' name='delivery_number' size='20'>";
echo "</td><td>";
echo "<input type='hidden' name='plugin_order_references_id' value='".
   $_POST['plugin_order_references_id']."'>";
echo "<input type='hidden' name='plugin_order_orders_id' value='".
   $_POST['plugin_order_orders_id']."'>";
echo $LANG['plugin_order']['delivery'][6]."</td><td>";
$nb = $PluginOrderReception->checkItemStatus($_POST['plugin_order_orders_id'],
                                             $_POST['plugin_order_references_id'], 
                                             PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED);
Dropdown::showInteger('number_reception','',1,$nb);
echo "</td><td>";
echo $LANG['plugin_order']['status'][3]."&nbsp;";
Dropdown::show('PluginOrderDeliveryState', array('name' => "plugin_order_deliverystates_id"));
echo "</td>";
echo "<td><input type='submit' name='bulk_reception' class='submit' value='".
   $LANG['buttons'][2]."'></td></tr></table>";

ajaxFooter();

?>