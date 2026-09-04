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

use Glpi\Tests\DbTestCase;
use PluginOrderOrder_Item;

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
}
