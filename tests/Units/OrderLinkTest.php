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

use ConsumableItem;
use Glpi\Tests\DbTestCase;
use Infocom;
use Entity;
use Phone;
use PluginOrderLink;
use PluginOrderOrder;
use PluginOrderOrder_Item;
use PluginOrderReference;

final class OrderLinkTest extends DbTestCase
{
    public function testGenerateInfocomRelatedToOrderDoesNotCrashWhenTemplateHasOlderInfocom(): void
    {
        $this->login();

        global $CFG_GLPI;

        $entities_id = getItemByTypeName(Entity::class, '_test_root_entity', true);

        // Enable auto-creation of infocoms for testing
        $CFG_GLPI['auto_create_infocoms'] = 1;

        // Roll back the session clock so that the model's infocom shows a `date_creation` value
        // different from that of the new phone's infocom, allowing the logs to be updated
        $_SESSION['glpi_currenttime'] = '2000-01-01 00:00:00';
        $phone_template = $this->createItem(Phone::class, [
            'name'          => 'Order test template',
            'entities_id'   => $entities_id,
            'is_template'   => 1,
            'template_name' => 'Order test template',
        ]);
        $_SESSION['glpi_currenttime'] = date('Y-m-d H:i:s');

        $template_infocom = new Infocom();
        $this->assertTrue($template_infocom->getFromDBforDevice(Phone::class, $phone_template->getID()));

        $phone = $this->createItem(Phone::class, [
            'name'        => 'Order test phone',
            'entities_id' => $entities_id,
        ]);

        $order = $this->createItem(PluginOrderOrder::class, [
            'name'        => 'Order test order',
            'entities_id' => $entities_id,
            'num_order'   => mt_rand(),
            'order_date'  => date('Y-m-d'),
        ]);

        $reference = $this->createItem(PluginOrderReference::class, [
            'name'         => 'Order test reference',
            'entities_id'  => $entities_id,
            'itemtype'     => Phone::class,
            'templates_id' => $phone_template->getID(),
        ]);

        $order_item = $this->createItem(PluginOrderOrder_Item::class, [
            'plugin_order_orders_id'      => $order->getID(),
            'plugin_order_references_id'  => $reference->getID(),
            'itemtype'                    => Phone::class,
            'items_id'                    => $phone->getID(),
        ]);

        // Generate the infocom for the order item
        $link = new PluginOrderLink();
        $link->generateInfoComRelatedToOrder(
            $entities_id,
            $order_item->getID(),
            Phone::class,
            $phone->getID(),
            $phone_template->getID(),
        );

        $infocom = new Infocom();
        $this->assertTrue($infocom->getFromDBforDevice(Phone::class, $phone->getID()));
    }

    public function testGenerateNewItemDoesNotCreateConsumableItem(): void
    {
        $this->login();

        $entities_id = getItemByTypeName(Entity::class, '_test_root_entity', true);

        $order = $this->createItem(PluginOrderOrder::class, [
            'name'        => 'Order consumable order',
            'entities_id' => $entities_id,
            'num_order'   => mt_rand(),
            'order_date'  => date('Y-m-d'),
        ]);

        $reference = $this->createItem(PluginOrderReference::class, [
            'name'        => 'Order consumable reference',
            'entities_id' => $entities_id,
            'itemtype'    => ConsumableItem::class,
        ]);

        $order_item = $this->createItem(PluginOrderOrder_Item::class, [
            'plugin_order_orders_id'     => $order->getID(),
            'plugin_order_references_id' => $reference->getID(),
            'itemtype'                   => ConsumableItem::class,
            'states_id'                  => PluginOrderOrder::ORDER_DEVICE_DELIVRED,
        ]);

        $nb_before = countElementsInTable(ConsumableItem::getTable());

        // Same input as the automatic generation on delivery
        $link   = new PluginOrderLink();
        $newIDs = $link->generateNewItem([
            'plugin_order_orders_id'     => $order->getID(),
            'plugin_order_references_id' => $reference->getID(),
            'itemtype'                   => ConsumableItem::class,
            'id'                         => [[
                'id'          => $order_item->getID(),
                'itemtype'    => ConsumableItem::class,
                'name'        => 'A_COMPLETER',
                'serial'      => '',
                'otherserial' => '',
                'entities_id' => $entities_id,
                'locations_id' => 0,
                'groups_id'   => 0,
                'states_id'   => 0,
            ]],
        ]);

        $this->assertSame([], $newIDs);
        $this->assertSame($nb_before, countElementsInTable(ConsumableItem::getTable()));

        // Detail line stays unlinked, so it can be linked to an existing consumable model
        $this->assertTrue($order_item->getFromDB($order_item->getID()));
        $this->assertEquals(0, $order_item->fields['items_id']);
    }
}
