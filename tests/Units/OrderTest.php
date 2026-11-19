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

namespace GlpiPlugin\Order\Tests\Units;

<<<<<<< HEAD
use GlpiPlugin\Order\Tests\OrderTestCase;
use PluginOrderOrder;
use PluginOrderOrder_Item;
use PluginOrderOrder_Supplier;
use PluginOrderOrderState;
=======
use Budget;
use Computer;
use DBmysql;
use Entity;
use Glpi\Tests\DbTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PluginOrderOrder;
use PluginOrderOrder_Item;
use PluginOrderReference;
use RuntimeException;
>>>>>>> 880912c (Fix(Perf): index order lines, check budget in one query and stop re-r… (#631))

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

    public static function budgetValueProvider(): iterable
    {
        yield 'under budget' => [281, PluginOrderOrder::ORDER_IS_UNDER_BUDGET];
        yield 'equal to budget' => [280, PluginOrderOrder::ORDER_IS_EQUAL_BUDGET];
        yield 'over budget' => [279, PluginOrderOrder::ORDER_IS_OVER_BUDGET];
    }

    #[DataProvider('budgetValueProvider')]
    public function testIsOverBudgetSumsAllOrdersOfTheBudget(int $value, int $expected): void
    {
        $this->login();

        $entities_id = getItemByTypeName(Entity::class, '_test_root_entity', true);
        $budget = $this->createItem(Budget::class, [
            'name'        => 'Order test budget',
            'entities_id' => $entities_id,
            'value'       => $value,
        ]);
        $other_budget = $this->createItem(Budget::class, [
            'name'        => 'Order test other budget',
            'entities_id' => $entities_id,
            'value'       => $value,
        ]);
        $reference = $this->createItem(PluginOrderReference::class, [
            'name'        => 'Order test budget reference',
            'entities_id' => $entities_id,
            'itemtype'    => Computer::class,
        ]);

        // Lines 150 + 100, postage 10 + 20, an order without line: 280 on the budget
        $orders = [];
        foreach ([[$budget, 10, [100, 50]], [$budget, 20, [100]], [$budget, 0, []], [$other_budget, 0, [1000]]] as [$order_budget, $port_price, $prices]) {
            $order = $this->createItem(PluginOrderOrder::class, [
                'name'        => 'Order test budget order',
                'entities_id' => $entities_id,
                'num_order'   => mt_rand(),
                'order_date'  => date('Y-m-d'),
                'budgets_id'  => $order_budget->getID(),
                'port_price'  => $port_price,
            ]);
            foreach ($prices as $price) {
                $this->createItem(PluginOrderOrder_Item::class, [
                    'plugin_order_orders_id'     => $order->getID(),
                    'plugin_order_references_id' => $reference->getID(),
                    'itemtype'                   => Computer::class,
                    'items_id'                   => 0,
                    'price_taxfree'              => $price,
                    'price_discounted'           => $price,
                    'discount'                   => 0,
                ]);
            }
            $orders[] = $order;
        }

        $this->assertSame($expected, $orders[0]->isOverBudget($orders[0]->getID()));
    }

    public static function indexProvider(): iterable
    {
        yield 'order lines by order' => ['glpi_plugin_order_orders_items', 'plugin_order_orders_id'];
        yield 'orders by budget' => ['glpi_plugin_order_orders', 'budgets_id'];
    }

    #[DataProvider('indexProvider')]
    public function testOrderTablesAreIndexedForBudgetAndLineLookups(string $table, string $index): void
    {
        /** @var DBmysql $DB */
        global $DB;

        $count = $DB->request([
            'COUNT' => 'cpt',
            'FROM'  => 'information_schema.statistics',
            'WHERE' => [
                'table_schema' => $DB->dbdefault,
                'table_name'   => $table,
                'index_name'   => $index,
            ],
        ])->current()['cpt'];

        $this->assertGreaterThan(0, $count, "Index $index is missing on $table");
    }
}
