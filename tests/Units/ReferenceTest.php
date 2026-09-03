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
use PluginOrderReference;
use PluginOrderReference_Supplier;

final class ReferenceTest extends OrderTestCase
{
    public function testCreateReference(): void
    {
        $this->login();

        $reference = $this->createReference();

        $this->assertSame(Computer::class, $reference->fields['itemtype']);
        $this->assertSame(1, (int) $reference->fields['is_active']);
    }

    public function testCreateReferenceWithoutNameFails(): void
    {
        $this->login();

        $reference = new PluginOrderReference();
        $id = $reference->add([
            'entities_id' => $this->getTestRootEntity(true),
            'itemtype'    => Computer::class,
        ]);

        $this->assertFalse($id);
        $this->hasSessionMessages(ERROR, ['Cannot create reference without a name']);
    }

    public function testCreateReferenceWithoutItemtypeFails(): void
    {
        $this->login();

        $reference = new PluginOrderReference();
        $id = $reference->add([
            'name'        => 'No itemtype',
            'entities_id' => $this->getTestRootEntity(true),
            'itemtype'    => '',
        ]);

        $this->assertFalse($id);
        $this->hasSessionMessages(ERROR, ['Cannot create reference without a type']);
    }

    public function testDuplicateNameInSameEntityIsRejected(): void
    {
        $this->login();

        $name = 'Duplicate Reference_' . $this->getUniqueString();
        $entities_id = $this->getTestRootEntity(true);

        $reference1 = new PluginOrderReference();
        $id1 = $reference1->add([
            'name'        => $name,
            'entities_id' => $entities_id,
            'itemtype'    => Computer::class,
        ]);
        $this->assertGreaterThan(0, $id1);

        $reference2 = new PluginOrderReference();
        $id2 = $reference2->add([
            'name'        => $name,
            'entities_id' => $entities_id,
            'itemtype'    => Computer::class,
        ]);
        $this->assertFalse($id2);
        $this->hasSessionMessages(ERROR, ['A reference with the same name still exists']);
    }

    public function testLinkReferenceToSupplier(): void
    {
        $this->login();

        $supplier  = $this->createSupplier();
        $reference = $this->createReference($supplier, Computer::class, 150.5);

        $links = (new PluginOrderReference_Supplier())->find([
            'plugin_order_references_id' => $reference->getID(),
            'suppliers_id'                => $supplier->getID(),
        ]);

        $this->assertCount(1, $links);
        $link = reset($links);
        $this->assertEqualsWithDelta(150.5, (float) $link['price_taxfree'], 0.001);
    }

    public function testUpdateReference(): void
    {
        $this->login();

        $reference = $this->createReference();

        $this->updateItem(PluginOrderReference::class, $reference->getID(), [
            'comment' => 'Updated reference comment',
        ]);

        $reference->getFromDB($reference->getID());
        $this->assertSame('Updated reference comment', $reference->fields['comment']);
    }

    public function testDeleteUnusedReference(): void
    {
        $this->login();

        $reference = $this->createReference();

        $this->assertTrue($reference->delete(['id' => $reference->getID()], true));
        $this->assertFalse((new PluginOrderReference())->getFromDB($reference->getID()));
    }

    public function testDeletingReferenceInUseIsBlocked(): void
    {
        $this->login();

        $supplier  = $this->createSupplier();
        $order     = $this->createOrder();
        $reference = $this->createReference($supplier);
        $this->addReferenceToOrder($order, $reference);

        $this->assertFalse($reference->delete(['id' => $reference->getID()], true));
        $this->assertTrue((new PluginOrderReference())->getFromDB($reference->getID()));
        $this->hasSessionMessages(ERROR, ['Reference(s) in use']);
    }
}
