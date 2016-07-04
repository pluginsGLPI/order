<?php

/*
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
  @copyright Copyright (c) 2010-2015 Order plugin team
  @license   GPLv2+
  http://www.gnu.org/licenses/gpl.txt
  @link      https://forge.indepnet.net/projects/order
  @link      http://www.glpi-project.org/
  @since     2009
  ---------------------------------------------------------------------- */

include ("../../../inc/includes.php");

$bill = new PluginOrderBill();

if (isset($_REQUEST['add'])) {
    $bill->add($_REQUEST);
    Html::back();
}

if (isset($_REQUEST['update'])) {
    $bill->update($_REQUEST);
    Html::back();
}

if (isset($_REQUEST['purge'])) {
    $bill->delete($_REQUEST);
    $bill->redirectToList();
}

if (isset($_POST['action'])) {
    // Retrieve configuration for generate assets feature
    if ($_POST['action'] == 'Ajouter') {
        PluginOrderOrder::add_order_to_bill($_POST['order_to_add'], $_POST['plugin_order_bills_id']);
    } else {
        $order_item = new PluginOrderOrder_Item();
        switch ($_POST['chooseAction']) {
            case 'bill':
            case 'state':
                if (isset($_POST["item"])) {
                    foreach ($_POST["item"] as $key => $val) {
                        if ($val == 1) {
                            $tmp = $_POST;
                            $tmp['id'] = $key;
                            $order_item->update($tmp);

                            // Update infocom
                            $ic = new Infocom();
                            $ic->getFromDBforDevice($order_item->fields['itemtype'], $order_item->fields['items_id']);

                            $config = PluginOrderConfig::getConfig();

                            if ($bill->getFromDB($_POST["plugin_order_bills_id"])) {
                                $fields['id'] = $ic->fields['id'];
                                $fields['bill'] = $bill->fields['number'];
                                $fields['warranty_date'] = $bill->fields['billdate'];
                            }

                            $ic->update($fields);
                        }
                    }
                }
                break;
        }
        PluginOrderOrder::updateBillState($order_item->fields['plugin_order_orders_id']);
    }

    Html::back();
}
$dropdown = new PluginOrderBill();

Session::checkRight("plugin_order_bill", READ);

Html::header(PluginOrderBill::getTypeName(1), $_SERVER['PHP_SELF'], "management", "PluginOrderMenu", "bill");
if (isset($_REQUEST['id'])) {
    $bill->display($_REQUEST);
} else {
    $bill->show();
}

Html::footer();
