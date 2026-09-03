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

namespace GlpiPlugin\Order\Tests\Units;

use Computer;
use GlpiPlugin\Order\Tests\OrderTestCase;
use PluginOrderOrder_Item;

final class OrderItemTest extends OrderTestCase
{
    public function testGetClassesIncludesNativeGlpiAssets(): void
    {
        $this->login();

        $classes = PluginOrderOrder_Item::getClasses(true);

        // Order lines can natively point to these core GLPI asset types.
        $this->assertContains(Computer::class, $classes);
        $this->assertContains('Monitor', $classes);
        $this->assertContains('NetworkEquipment', $classes);
        $this->assertContains('Printer', $classes);
        $this->assertContains('SoftwareLicense', $classes);
    }

    public function testAddDetailsCreatesOneLinePerQuantity(): void
    {
        $this->login();

        $supplier  = $this->createSupplier();
        $order     = $this->createOrder();
        $reference = $this->createReference($supplier, Computer::class, 250);

        $lines = $this->addReferenceToOrder($order, $reference, 3, 250, 10);

        $this->assertCount(3, $lines);
        foreach ($lines as $line) {
            $this->assertSame($order->getID(), (int) $line['plugin_order_orders_id']);
            $this->assertSame($reference->getID(), (int) $line['plugin_order_references_id']);
            $this->assertSame(Computer::class, $line['itemtype']);
            // 250 - 10% discount = 225
            $this->assertEqualsWithDelta(225.0, (float) $line['price_discounted'], 0.001);
        }
    }

    public function testGetAllPricesSumsOrderLines(): void
    {
        $this->login();

        $supplier  = $this->createSupplier();
        $order     = $this->createOrder();
        $reference = $this->createReference($supplier, Computer::class, 100);

        $this->addReferenceToOrder($order, $reference, 2, 100, 0);

        $order_item = new PluginOrderOrder_Item();
        $prices     = $order_item->getAllPrices($order->getID());

        $this->assertEqualsWithDelta(200.0, (float) $prices['priceHT'], 0.001);
    }

    public function testDeletingOrderItemDoesNotDeleteTheOrder(): void
    {
        $this->login();

        $supplier  = $this->createSupplier();
        $order     = $this->createOrder();
        $reference = $this->createReference($supplier, Computer::class, 100);
        $lines     = $this->addReferenceToOrder($order, $reference, 1, 100, 0);

        $order_item = new PluginOrderOrder_Item();
        $this->assertTrue($order_item->delete(['id' => $lines[0]['id']], true));

        $this->assertCount(
            0,
            $order_item->find(['id' => $lines[0]['id']]),
        );
        $this->assertTrue($order->getFromDB($order->getID()));
    }
}
