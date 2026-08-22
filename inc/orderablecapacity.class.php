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

use Glpi\Asset\Capacity\AbstractCapacity;
use Glpi\Asset\CapacityConfig;

/**
 * Capacity that flags a GLPI 11 custom asset definition as orderable
 * through the Order plugin. When enabled on an asset definition (Setup ->
 * Asset definitions -> {your asset} -> Capacities), the corresponding
 * generated asset class is appended to $ORDER_TYPES and becomes selectable
 * as an Item type when creating a Product reference.
 */
class PluginOrderOrderableCapacity extends AbstractCapacity
{
    public function getLabel(): string
    {
        return __('Orderable', 'order');
    }

    public function getIcon(): string
    {
        return 'ti ti-shopping-cart';
    }

    public function getDescription(): string
    {
        return __(
            'Allow this asset to be referenced as a Product reference and '
            . 'generated from the Generate item massive action.',
            'order',
        );
    }

    public function getCapacityUsageDescription(string $classname): string
    {
        $count = 0;
        if (class_exists('PluginOrderReference')) {
            $count = countElementsInTable(
                PluginOrderReference::getTable(),
                ['itemtype' => $classname],
            );
        }

        return sprintf(
            _n('Used by %d order reference', 'Used by %d order references', $count, 'order'),
            $count,
        );
    }

    /**
     * Clean up plugin data linked to the asset class when the capacity is
     * disabled on its definition: remove order line items first (parent
     * Product references refuse deletion via pre_deleteItem() while still
     * referenced by orders_items), then remove the references themselves.
     * Free-form references are intentionally left untouched, as they are
     * standalone records that do not reference any itemtype.
     */
    public function onCapacityDisabled(string $classname, CapacityConfig $config): void
    {
        if (class_exists('PluginOrderOrder_Item')) {
            (new PluginOrderOrder_Item())->deleteByCriteria(
                ['itemtype' => $classname],
                force: true,
                history: false,
            );
        }

        if (class_exists('PluginOrderReference')) {
            (new PluginOrderReference())->deleteByCriteria(
                ['itemtype' => $classname],
                force: true,
                history: false,
            );
        }
    }
}
