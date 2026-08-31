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

use Glpi\Asset\AssetDefinitionManager;
use Glpi\Asset\Capacity;
use GlpiPlugin\Order\Tests\OrderTestCase;
use PluginOrderOrder_Item;
use PluginOrderOrderableCapacity;
use PluginOrderReference;

/**
 * Coverage for the GLPI 11/12 custom-asset integration: enabling the
 * "Orderable" capacity on a custom asset definition must make its class
 * usable as a Product reference itemtype, and disabling it must clean up
 * any plugin data that referenced it.
 */
final class OrderableCapacityTest extends OrderTestCase
{
    /**
     * GLPITestCase::tearDown() resets the AssetDefinitionManager singleton
     * after every test, which discards the capacity registration normally
     * done once by plugin_init_order(). Re-register it before each test
     * that needs to create/toggle it on a definition.
     */
    private function registerOrderableCapacity(): void
    {
        AssetDefinitionManager::getInstance()->registerCapacity(new PluginOrderOrderableCapacity());
    }

    public function testCapacityUsageDescriptionReflectsLinkedReferences(): void
    {
        $this->login();
        $this->registerOrderableCapacity();

        $capacity = new PluginOrderOrderableCapacity();

        $definition = $this->initAssetDefinition(
            capacities: [new Capacity(name: PluginOrderOrderableCapacity::class)],
        );
        $classname = $definition->getAssetClassName();

        $this->assertSame(0, (int) filter_var(
            $capacity->getCapacityUsageDescription($classname),
            FILTER_SANITIZE_NUMBER_INT,
        ));

        $reference = $this->createReference(itemtype: $classname);

        $this->assertSame(1, (int) filter_var(
            $capacity->getCapacityUsageDescription($classname),
            FILTER_SANITIZE_NUMBER_INT,
        ));

        // Sanity check: the created reference is retrievable and points to the custom asset class.
        $this->assertSame($classname, $reference->fields['itemtype']);
    }

    public function testDisablingCapacityCleansUpLinkedReferencesAndOrderItems(): void
    {
        $this->login();
        $this->registerOrderableCapacity();

        $definition = $this->initAssetDefinition(
            capacities: [new Capacity(name: PluginOrderOrderableCapacity::class)],
        );
        $classname = $definition->getAssetClassName();

        $supplier  = $this->createSupplier();
        $order     = $this->createOrder();
        $reference = $this->createReference($supplier, $classname);
        $this->addReferenceToOrder($order, $reference);

        $this->assertNotCount(0, (new PluginOrderReference())->find(['itemtype' => $classname]));
        $this->assertNotCount(0, (new PluginOrderOrder_Item())->find(['itemtype' => $classname]));

        $this->disableCapacity($definition, PluginOrderOrderableCapacity::class);

        $this->assertCount(0, (new PluginOrderReference())->find(['itemtype' => $classname]));
        $this->assertCount(0, (new PluginOrderOrder_Item())->find(['itemtype' => $classname]));
    }
}
