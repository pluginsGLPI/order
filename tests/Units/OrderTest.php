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
use PluginOrderOrder;
use PluginOrderOrder_Item;
use PluginOrderOrder_Supplier;
use PluginOrderOrderState;

final class OrderTest extends OrderTestCase
{
    public function testCreateOrderIsDraftByDefault(): void
    {
        $this->login();

        $order = $this->createOrder();

        $this->assertSame(
            PluginOrderOrderState::DRAFT,
            (int) $order->fields['plugin_order_orderstates_id'],
        );
        $this->assertTrue($order->isDraft());
        $this->assertFalse($order->isCanceled());
        $this->assertFalse($order->isDelivered());
    }

    public function testCreateOrderWithoutNumOrderFails(): void
    {
        $this->login();

        $order = new PluginOrderOrder();
        $id = $order->add([
            'name'        => 'Missing num_order',
            'entities_id' => $this->getTestRootEntity(true),
        ]);

        $this->assertFalse($id);
        $this->hasSessionMessages(ERROR, ['An order number is mandatory !']);
    }

    public function testUpdateOrder(): void
    {
        $this->login();

        $order = $this->createOrder();

        $this->updateItem(PluginOrderOrder::class, $order->getID(), [
            'comment' => 'Updated comment',
        ]);

        $order->getFromDB($order->getID());
        $this->assertSame('Updated comment', $order->fields['comment']);
    }

    public function testUpdateOrderStatusAddsHistoryLog(): void
    {
        $this->login();

        $order = $this->createOrder();
        $order->updateOrderStatus($order->getID(), PluginOrderOrderState::VALIDATED, 'validated for test');

        $order->getFromDB($order->getID());
        $this->assertSame(PluginOrderOrderState::VALIDATED, (int) $order->fields['plugin_order_orderstates_id']);
        $this->assertTrue($order->isApproved());
    }

    public function testDeliveredStateSetsDeliveryDate(): void
    {
        $this->login();

        $order = $this->createOrder();
        $this->assertEmpty($order->fields['deliverydate']);

        $order->updateOrderStatus($order->getID(), PluginOrderOrderState::DELIVERED);

        $order->getFromDB($order->getID());
        $this->assertTrue($order->isDelivered());
        $this->assertNotEmpty($order->fields['deliverydate']);
    }

    public function testCanValidateOrderWithoutValidationProcessDependsOnDraftState(): void
    {
        $this->login();

        $order = $this->createOrder();

        // No validation workflow configured by default: draft orders can be validated directly.
        $this->assertTrue($order->canValidateOrder());

        $order->updateOrderStatus($order->getID(), PluginOrderOrderState::VALIDATED);
        $order->getFromDB($order->getID());
        $this->assertFalse($order->canValidateOrder());
    }

    public function testCanCancelOrderRequiresRight(): void
    {
        $this->removeRightFromProfile('Super-Admin', PluginOrderOrder::$rightname, PluginOrderOrder::RIGHT_CANCEL);
        $this->login();

        $order = $this->createOrder();
        $this->assertFalse($order->canCancelOrder());

        $this->logOut();
        $this->addRightToProfile('Super-Admin', PluginOrderOrder::$rightname, PluginOrderOrder::RIGHT_CANCEL);
        $this->login();

        $order->getFromDB($order->getID());
        $this->assertTrue($order->canCancelOrder());
    }

    public function testCanceledOrderCannotBeCanceledAgain(): void
    {
        $this->addRightToProfile('Super-Admin', PluginOrderOrder::$rightname, PluginOrderOrder::RIGHT_CANCEL);
        $this->login();

        $order = $this->createOrder();
        $order->updateOrderStatus($order->getID(), PluginOrderOrderState::CANCELED);
        $order->getFromDB($order->getID());

        $this->assertTrue($order->isCanceled());
        $this->assertFalse($order->canCancelOrder());
    }

    public function testUndoValidationRequiresRightAndValidatedState(): void
    {
        $this->addRightToProfile('Super-Admin', PluginOrderOrder::$rightname, PluginOrderOrder::RIGHT_UNDO_VALIDATION);
        $this->login();

        $order = $this->createOrder();
        // Still draft: nothing to undo.
        $this->assertFalse($order->canUndoValidation());

        $order->updateOrderStatus($order->getID(), PluginOrderOrderState::VALIDATED);
        $order->getFromDB($order->getID());
        $this->assertTrue($order->canUndoValidation());

        $this->logOut();
        $this->removeRightFromProfile('Super-Admin', PluginOrderOrder::$rightname, PluginOrderOrder::RIGHT_UNDO_VALIDATION);
        $this->login();

        $order->getFromDB($order->getID());
        $this->assertFalse($order->canUndoValidation());
    }

    public function testPurgingOrderCascadesToRelatedItems(): void
    {
        $this->login();

        $supplier = $this->createSupplier();
        $order    = $this->createOrder();

        $order_supplier = $this->createItem(PluginOrderOrder_Supplier::class, [
            'plugin_order_orders_id' => $order->getID(),
            'suppliers_id'           => $supplier->getID(),
        ]);

        $reference = $this->createReference($supplier);
        $this->addReferenceToOrder($order, $reference);

        $this->assertNotCount(
            0,
            (new PluginOrderOrder_Item())->find(['plugin_order_orders_id' => $order->getID()]),
        );

        $this->assertTrue($order->delete(['id' => $order->getID()], true));

        $this->assertFalse((new PluginOrderOrder_Supplier())->getFromDB($order_supplier->getID()));
        $this->assertCount(
            0,
            (new PluginOrderOrder_Item())->find(['plugin_order_orders_id' => $order->getID()]),
        );
    }
}
