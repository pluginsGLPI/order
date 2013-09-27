<?php
/*
 * @version $Id: bill.tabs.php 530 2011-06-30 11:30:17Z walid $
 LICENSE

 This file is part of the order plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Order. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   order
 @author    the order plugin team
 @copyright Copyright (c) 2010-2011 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

include ("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

Session::checkCentralAccess();


if ($_POST["plugin_order_references_id"] > 0) {
   $reference_supplier = new PluginOrderReference_Supplier();
   $price = $reference_supplier->getPriceByReferenceAndSupplier($_POST["plugin_order_references_id"],
                                                                $_POST["suppliers_id"]);
   switch ($_POST["update"]) {
      case 'quantity':
         echo "<input type='text' name='quantity' size='5'>";
         break;
      case 'priceht':
         echo "<input type='text' name='price' value=\"".Html::formatNumber($price, true)."\" size='5'>";
         break;
      case 'pricediscounted':
         echo "<input type='text' name='discount' size='5' value='0'>";
         break;
      case 'taxe':
         $config = PluginOrderConfig::getConfig();
         PluginOrderOrderTax::Dropdown(array('name'                => "plugin_order_ordertaxes_id",
                                              'value'               => $config->getDefaultTaxes(),
                                              'display_emptychoice' => true,
                                              'emptylabel'          => __("No VAT", "order")));
         break;
      case 'validate':
         echo "<input type='hidden' name='itemtype' value='".
            $_POST["itemtype"]."' class='submit' >";
         echo "<input type='hidden' name='plugin_order_references_id' value='".
               $_POST["plugin_order_references_id"]."' class='submit' >";
         echo "<input type='submit' name='add_item' value=\"".
            __("Add")."\" class='submit' >";
         break;
   }
} else {
   return "";
}

?>