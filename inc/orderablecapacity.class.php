<?php

/**
 * Capacity that flags a GLPI 11 custom asset definition as orderable
 * through the Order plugin. When enabled on an asset definition (Setup ->
 * Asset definitions -> {your asset} -> Capacities), the corresponding
 * generated asset class is appended to $ORDER_TYPES and becomes selectable
 * as an Item type when creating a Product reference.
 */
class PluginOrderOrderableCapacity extends \Glpi\Asset\Capacity\AbstractCapacity
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
            'order'
        );
    }

    public function getCapacityUsageDescription(string $classname): string
    {
        $count = 0;
        if (class_exists('PluginOrderReference')) {
            $count = countElementsInTable(
                \PluginOrderReference::getTable(),
                ['itemtype' => $classname]
            );
        }
        return sprintf(
            _n('Used by %d order reference', 'Used by %d order references', $count, 'order'),
            $count
        );
    }

    /**
     * Clean up plugin data linked to the asset class when the capacity is
     * disabled on its definition: remove Product references targeting the
     * class and order line items that link orders to instances of the class.
     * Free-form references are intentionally left untouched, as they are
     * standalone records that do not reference any itemtype.
     */
    public function onCapacityDisabled(string $classname, \Glpi\Asset\CapacityConfig $config): void
    {
        // Delete order line items first: PluginOrderReference::pre_deleteItem()
        // refuses deletion while references are still in use by orders_items,
        // so child records must be removed before parent references.
        if (class_exists('PluginOrderOrder_Item')) {
            (new \PluginOrderOrder_Item())->deleteByCriteria(
                ['itemtype' => $classname],
                force: true,
                history: false
            );
        }
        if (class_exists('PluginOrderReference')) {
            (new \PluginOrderReference())->deleteByCriteria(
                ['itemtype' => $classname],
                force: true,
                history: false
            );
        }
    }
}
