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
 * @copyright Copyright (C) 2009-2026 by Order plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/order
 * -------------------------------------------------------------------------
 */

declare(strict_types=1);

namespace GlpiPlugin\Order\Tests\Units;

use Computer;
use Entity;
use Glpi\Tests\DbTestCase;
use PluginOrderOrder;
use PluginOrderOrder_Item;
use PluginOrderReference;

final class OrderItemTest extends DbTestCase
{
    public function testBelongsToOrderReturnsTrueForOwningOrder(): void
    {
        $item = new PluginOrderOrder_Item();
        $item->fields['plugin_order_orders_id'] = 5;

        $this->assertTrue($item->belongsToOrder(5));
    }

    public function testBelongsToOrderReturnsFalseForForeignOrder(): void
    {
        $item = new PluginOrderOrder_Item();
        $item->fields['plugin_order_orders_id'] = 5;

        $this->assertFalse($item->belongsToOrder(42));
    }

    public function testUpdatePrice_taxfreeIgnoresItemFromAnotherOrder(): void
    {
        $this->login();

        [$order_item, $foreign_orders_id] = $this->createItemInOrderAndForeignOrder();

        $order_item->updatePrice_taxfree([
            'item_id'       => $order_item->getID(),
            'orders_id'     => $foreign_orders_id,
            'price_taxfree' => 999,
        ]);

        $this->assertTrue($order_item->getFromDB($order_item->getID()));
        $this->assertEquals(100, (float) $order_item->fields['price_taxfree']);
    }

    public function testUpdatePrice_taxfreeAppliesToItemOfCurrentOrder(): void
    {
        $this->login();

        [$order_item] = $this->createItemInOrderAndForeignOrder();

        $order_item->updatePrice_taxfree([
            'item_id'       => $order_item->getID(),
            'orders_id'     => (int) $order_item->fields['plugin_order_orders_id'],
            'price_taxfree' => 999,
        ]);

        $this->assertTrue($order_item->getFromDB($order_item->getID()));
        $this->assertEquals(999, (float) $order_item->fields['price_taxfree']);
    }

    public function testUpdateDiscountIgnoresItemFromAnotherOrder(): void
    {
        $this->login();

        [$order_item, $foreign_orders_id] = $this->createItemInOrderAndForeignOrder();

        $order_item->updateDiscount([
            'item_id'   => $order_item->getID(),
            'orders_id' => $foreign_orders_id,
            'discount'  => 50,
            'price'     => 100,
        ]);

        $this->assertTrue($order_item->getFromDB($order_item->getID()));
        $this->assertEquals(0, (float) $order_item->fields['discount']);
    }

    public function testUpdateDiscountAppliesToItemOfCurrentOrder(): void
    {
        $this->login();

        [$order_item] = $this->createItemInOrderAndForeignOrder();

        $order_item->updateDiscount([
            'item_id'   => $order_item->getID(),
            'orders_id' => (int) $order_item->fields['plugin_order_orders_id'],
            'discount'  => 50,
            'price'     => 100,
        ]);

        $this->assertTrue($order_item->getFromDB($order_item->getID()));
        $this->assertEquals(50, (float) $order_item->fields['discount']);
        $this->assertEquals(50, (float) $order_item->fields['price_discounted']);
    }

    /** @return array{0: PluginOrderOrder_Item, 1: int} */
    private function createItemInOrderAndForeignOrder(): array
    {
        $entities_id = getItemByTypeName(Entity::class, '_test_root_entity', true);

        $order = $this->createItem(PluginOrderOrder::class, [
            'name'        => 'Order test owner order',
            'entities_id' => $entities_id,
            'num_order'   => mt_rand(),
            'order_date'  => date('Y-m-d'),
        ]);

        $foreign_order = $this->createItem(PluginOrderOrder::class, [
            'name'        => 'Order test foreign order',
            'entities_id' => $entities_id,
            'num_order'   => mt_rand(),
            'order_date'  => date('Y-m-d'),
        ]);

        $reference = $this->createItem(PluginOrderReference::class, [
            'name'        => 'Order test reference',
            'entities_id' => $entities_id,
            'itemtype'    => Computer::class,
        ]);

        $order_item = $this->createItem(PluginOrderOrder_Item::class, [
            'plugin_order_orders_id'     => $order->getID(),
            'plugin_order_references_id' => $reference->getID(),
            'itemtype'                   => Computer::class,
            'items_id'                   => 0,
            'price_taxfree'              => 100,
            'discount'                   => 0,
        ]);

        return [$order_item, (int) $foreign_order->getID()];
    }
}
