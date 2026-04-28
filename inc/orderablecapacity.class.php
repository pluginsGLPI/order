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
}
