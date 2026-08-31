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
use PluginOrderOrder;
use PluginOrderProfile;
use PluginOrderReference;
use ProfileRight;

final class ProfileTest extends OrderTestCase
{
    public function testGetAllRightsDeclaresTheThreeRightBuckets(): void
    {
        $rights = PluginOrderProfile::getAllRights();

        $fields = array_column($rights, 'field');
        $this->assertContains(PluginOrderOrder::$rightname, $fields);
        $this->assertContains(PluginOrderReference::$rightname, $fields);
        $this->assertContains(PluginOrderBill::$rightname, $fields);

        $itemtypes = array_column($rights, 'itemtype');
        $this->assertContains(PluginOrderOrder::class, $itemtypes);
        $this->assertContains(PluginOrderReference::class, $itemtypes);
        $this->assertContains(PluginOrderBill::class, $itemtypes);
    }

    public function testEveryDeclaredRightIsRegisteredInProfileRights(): void
    {
        // initProfile() is run on plugin activation; the columns must already exist.
        foreach (PluginOrderProfile::getAllRights(true) as $data) {
            $this->assertGreaterThan(
                0,
                countElementsInTable('glpi_profilerights', ['name' => $data['field']]),
                sprintf('Right "%s" is not registered in glpi_profilerights', $data['field']),
            );
        }
    }

    public function testToggleOrderRightBitOnProfile(): void
    {
        $profile_right = new ProfileRight();
        $profile_right->getFromDBByCrit([
            'profiles_id' => getItemByTypeName('Profile', 'Super-Admin', true),
            'name'        => PluginOrderOrder::$rightname,
        ]);

        $this->addRightToProfile('Super-Admin', PluginOrderOrder::$rightname, PluginOrderOrder::RIGHT_DELIVERY);

        $profile_right->getFromDB($profile_right->getID());
        $this->assertNotSame(0, (int) $profile_right->fields['rights'] & PluginOrderOrder::RIGHT_DELIVERY);

        $this->removeRightFromProfile('Super-Admin', PluginOrderOrder::$rightname, PluginOrderOrder::RIGHT_DELIVERY);

        $profile_right->getFromDB($profile_right->getID());
        $this->assertSame(0, (int) $profile_right->fields['rights'] & PluginOrderOrder::RIGHT_DELIVERY);
    }
}
