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
 * @copyright Copyright (C) 2009-2023 by Order plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/order
 * -------------------------------------------------------------------------
 */

namespace GlpiPlugin\Order\Tests;

use Glpi\Tests\DbTestCase;
use PluginOrderOrder;
use PluginOrderOrder_Item;
use PluginOrderReference;
use PluginOrderReference_Supplier;
use Supplier;

abstract class OrderTestCase extends DbTestCase
{
    /**
     * Create a Supplier fixture, usable both as an order supplier and a
     * reference supplier.
     */
    public function createSupplier(string $name = 'Test Supplier'): Supplier
    {
        return $this->createItem(Supplier::class, [
            'name'        => $name . '_' . $this->getUniqueString(),
            'entities_id' => $this->getTestRootEntity(true),
        ]);
    }

    /**
     * Create a catalog Product reference (PluginOrderReference), optionally
     * linked to a Supplier via PluginOrderReference_Supplier.
     */
    public function createReference(
        ?Supplier $supplier = null,
        string $itemtype = 'Computer',
        float $price_taxfree = 100,
    ): PluginOrderReference {
        $reference = $this->createItem(PluginOrderReference::class, [
            'name'        => 'Test Reference_' . $this->getUniqueString(),
            'entities_id' => $this->getTestRootEntity(true),
            'itemtype'    => $itemtype,
        ]);

        if ($supplier !== null) {
            $this->createItem(PluginOrderReference_Supplier::class, [
                'plugin_order_references_id' => $reference->getID(),
                'suppliers_id'                => $supplier->getID(),
                'price_taxfree'               => $price_taxfree,
            ]);
        }

        return $reference;
    }

    /**
     * Create a draft PluginOrderOrder fixture.
     */
    public function createOrder(array $input = []): PluginOrderOrder
    {
        return $this->createItem(PluginOrderOrder::class, array_merge([
            'name'        => 'Test Order_' . $this->getUniqueString(),
            'num_order'   => 'CMD_' . $this->getUniqueString(),
            'entities_id' => $this->getTestRootEntity(true),
        ], $input));
    }

    /**
     * Link a Product reference to an order, as PluginOrderOrder_Item does
     * through PluginOrderOrder::showAddForm() / addDetails().
     */
    public function addReferenceToOrder(
        PluginOrderOrder $order,
        PluginOrderReference $reference,
        int $quantity = 1,
        float $price = 100,
        float $discount = 0,
    ): array {
        $order_item = new PluginOrderOrder_Item();
        $order_item->addDetails(
            $reference->getID(),
            $reference->fields['itemtype'],
            $order->getID(),
            $quantity,
            $price,
            $discount,
            0,
            0,
        );

        return array_values($order_item->find([
            'plugin_order_orders_id'     => $order->getID(),
            'plugin_order_references_id' => $reference->getID(),
        ]));
    }
}
