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
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Generic CRUD coverage for every simple CommonDropdown exposed by the
 * plugin. These are all used as reference data on Orders / Order items /
 * Bills, so a regression here (e.g. a rightname or table change breaking
 * under a newer GLPI) would silently break every form relying on them.
 */
final class DropdownsTest extends OrderTestCase
{
    public static function dropdownClassesProvider(): array
    {
        return [
            ['PluginOrderOrderState'],
            ['PluginOrderOrdertype'],
            ['PluginOrderOrderTax'],
            ['PluginOrderOrderPayment'],
            ['PluginOrderAccountsection'],
            ['PluginOrderAnalyticnature'],
            ['PluginOrderDeliverystate'],
            ['PluginOrderBillState'],
            ['PluginOrderBillType'],
            ['PluginOrderOthertype'],
        ];
    }

    #[DataProvider('dropdownClassesProvider')]
    public function testCreateUpdateAndDeleteDropdown(string $itemtype): void
    {
        $this->login();

        $item = $this->createItem($itemtype, [
            'name' => 'Test ' . $itemtype . '_' . $this->getUniqueString(),
        ]);

        $this->updateItem($itemtype, $item->getID(), [
            'comment' => 'Updated via functional test',
        ]);
        $item->getFromDB($item->getID());
        $this->assertSame('Updated via functional test', $item->fields['comment']);

        $this->assertTrue($item->delete(['id' => $item->getID()], true));
        $this->assertFalse((new $itemtype())->getFromDB($item->getID()));
    }
}
