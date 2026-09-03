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

use GlpiPlugin\Order\Tests\OrderTestCase;
use PluginOrderBill;

final class BillTest extends OrderTestCase
{
    public function testCreateBillRequiresNumber(): void
    {
        $this->login();

        $bill = new PluginOrderBill();
        $id = $bill->add([
            'name'        => 'Bill without number',
            'entities_id' => $this->getTestRootEntity(true),
        ]);

        $this->assertFalse($id);
        $this->hasSessionMessages(ERROR, ['A bill number is mandatory']);
    }

    public function testCreateBillLinkedToOrderAndSupplier(): void
    {
        $this->login();

        $supplier = $this->createSupplier();
        $order    = $this->createOrder();

        $bill = $this->createItem(PluginOrderBill::class, [
            'name'                    => 'Test bill',
            'number'                  => 'INV-' . $this->getUniqueString(),
            'entities_id'             => $this->getTestRootEntity(true),
            'suppliers_id'            => $supplier->getID(),
            'plugin_order_orders_id'  => $order->getID(),
            'value'                   => 199.99,
        ]);

        $this->assertSame($supplier->getID(), (int) $bill->fields['suppliers_id']);
        $this->assertSame($order->getID(), (int) $bill->fields['plugin_order_orders_id']);
        $this->assertEqualsWithDelta(199.99, (float) $bill->fields['value'], 0.001);
    }

    public function testUpdateBill(): void
    {
        $this->login();

        $bill = $this->createItem(PluginOrderBill::class, [
            'name'        => 'Test bill',
            'number'      => 'INV-' . $this->getUniqueString(),
            'entities_id' => $this->getTestRootEntity(true),
        ]);

        $this->updateItem(PluginOrderBill::class, $bill->getID(), [
            'value' => 42.5,
        ]);

        $bill->getFromDB($bill->getID());
        $this->assertEqualsWithDelta(42.5, (float) $bill->fields['value'], 0.001);
    }

    public function testDeleteBill(): void
    {
        $this->login();

        $bill = $this->createItem(PluginOrderBill::class, [
            'name'        => 'Test bill',
            'number'      => 'INV-' . $this->getUniqueString(),
            'entities_id' => $this->getTestRootEntity(true),
        ]);

        $this->assertTrue($bill->delete(['id' => $bill->getID()], true));
        $this->assertFalse((new PluginOrderBill())->getFromDB($bill->getID()));
    }
}
