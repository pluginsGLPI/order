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

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT."/inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
header_nocache();

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

checkCentralAccess();

$PluginOrderReference_Supplier = new PluginOrderReference_Supplier();
$PluginOrderOrder_Item = new PluginOrderOrder_Item();

if ($_POST["plugin_order_references_id"] > 0)
{  
   
   $price = $PluginOrderReference_Supplier->getPriceByReferenceAndSupplier($_POST["plugin_order_references_id"],
                                                                           $_POST["suppliers_id"]);
   switch ($_POST["update"])
   {
      case 'quantity':
         echo "<input type='text' name='quantity' size='5'>";
         break;
      case 'priceht':
         echo "<input type='text' name='price' value=\"".formatNumber($price,true)."\" size='5'>";
         break;
      case 'pricediscounted':
         echo "<input type='text' name='discount' size='5' value='0'>";
         break;
      case 'taxe':
         Dropdown::show('PluginOrderOrderTaxe', array('name' => "plugin_order_ordertaxes_id"));
         break;
      case 'validate':
         echo "<input type='hidden' name='itemtype' value='".
            $_POST["itemtype"]."' class='submit' >";
         echo "<input type='hidden' name='plugin_order_references_id' value='".
               $_POST["plugin_order_references_id"]."' class='submit' >";
         echo "<input type='submit' name='add_item' value=\"".
            $LANG['buttons'][8]."\" class='submit' >";
         break;
   }  
}
else
   return "";

?>