<?php

/**
 * -------------------------------------------------------------------------
 * Order plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Order.
 *
 * Order is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Order is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Order. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2009-2022 by Order plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/order
 * -------------------------------------------------------------------------
 */

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
         echo "<input type='number' class='form-control' min='0' name='quantity' class='quantity'>";
         break;
      case 'priceht':
         echo "<input type='number' class='form-control' min='0' step='".PLUGIN_ORDER_NUMBER_STEP."'
                      name='price' value='".Html::formatNumber($price, true)."' class='decimal'>";
         break;
      case 'pricediscounted':
         echo "<input type='number' class='form-control' min='0' step='".PLUGIN_ORDER_NUMBER_STEP."'
                      name='discount' class='smalldecimal' value='0'>";
         break;
      case 'taxe':
         $config = PluginOrderConfig::getConfig();
         PluginOrderOrderTax::Dropdown([
            'name'                => "plugin_order_ordertaxes_id",
            'value'               => $config->getDefaultTaxes(),
            'display_emptychoice' => true,
            'emptylabel'          => __("No VAT", "order"),
         ]);
         break;
      case 'validate':
         echo Html::hidden('itemtype', ['value' => $_POST["itemtype"]]);
         echo Html::hidden('plugin_order_references_id',
                           ['value' => $_POST["plugin_order_references_id"]]);
         echo "<input type='submit' name='add_item' value=\"". __("Add")."\" class='submit' >";
         break;
   }
} else {
   return "";
}
