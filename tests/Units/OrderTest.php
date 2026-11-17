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

use Budget;
use Computer;
use Entity;
use Glpi\Tests\DbTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PluginOrderOrder;
use PluginOrderOrder_Item;
use PluginOrderReference;
use RuntimeException;

final class OrderTest extends DbTestCase
{
    public static function invalidTemplateNameProvider(): iterable
    {
        yield 'path traversal' => ['../../../etc/passwd.odt'];
        yield 'embedded path separator' => ['sub/template.odt'];
        yield 'disallowed extension' => ['template.docx'];
    }

    #[DataProvider('invalidTemplateNameProvider')]
    public function testGenerateOrderRejectsInvalidTemplateName(string $template): void
    {
        $order = new PluginOrderOrder();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid template name');

        $order->generateOrder([
            'id'       => 0,
            'template' => $template,
            'sign'     => '',
        ]);
    }

    public static function invalidSignatureNameProvider(): iterable
    {
        yield 'path traversal via slash' => ['../../../etc/passwd.png'];
        yield 'path traversal via backslash' => ['..\\..\\signature.png'];
        yield 'disallowed extension' => ['signature.php'];
    }

    #[DataProvider('invalidSignatureNameProvider')]
    public function testGenerateOrderRejectsInvalidSignatureName(string $signature): void
    {
        $order = new PluginOrderOrder();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid signature file name');

        $order->generateOrder([
            'id'       => 0,
            'template' => 'template.odt',
            'sign'     => $signature,
        ]);
    }

    public static function acceptedSignatureProvider(): iterable
    {
        yield 'no signature' => [''];
        yield 'valid png signature' => ['signature.png'];
    }

    #[DataProvider('acceptedSignatureProvider')]
    public function testGenerateOrderAcceptsValidSignatureAndReachesFileCheck(string $signature): void
    {
        $order = new PluginOrderOrder();

        // The template file does not exist in the test environment: reaching this
        // exception proves the name/signature validation passed successfully.
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Template file not found or not readable');

        $order->generateOrder([
            'id'       => 0,
            'template' => 'template.odt',
            'sign'     => $signature,
        ]);
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
}
