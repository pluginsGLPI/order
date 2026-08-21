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

/**
 * Administrator-defined extra fields shown on the item generation form.
 *
 * Each row maps one itemtype to one field of the generated asset: a plain
 * column of the asset table (e.g. a site-added `imei` on phones) or, for
 * GLPI 11 custom assets, one of the definition's custom fields (stored as
 * `custom_<system_name>`, the input key the core asset form itself uses).
 */
class PluginOrderGenerationField extends CommonDBTM
{
    public static $rightname = 'config';

    /**
     * Columns that never make sense as manual generation inputs: identifiers,
     * flags, foreign keys and the fields the form already carries.
     */
    private const EXCLUDED_COLUMNS = [
        'id', 'entities_id', 'name', 'serial', 'otherserial', 'comment',
        'date_mod', 'date_creation', 'template_name', 'custom_fields',
        'ticket_tco', 'uuid', 'autoupdatesystems_id',
    ];

    public static function getTypeName($nb = 0)
    {
        return __s("Generation extra fields", "order");
    }


    /**
     * Fields configured for one itemtype, in configuration order.
     *
     * @return array<string, string> field => label
     */
    public static function getForItemtype(string $itemtype): array
    {
        /** @var DBmysql $DB */
        global $DB;

        static $cache = [];
        if (isset($cache[$itemtype])) {
            return $cache[$itemtype];
        }

        $fields = [];
        if ($DB->tableExists(self::getTable())) {
            foreach (
                $DB->request([
                    'FROM'  => self::getTable(),
                    'WHERE' => ['itemtype' => $itemtype],
                    'ORDER' => 'id ASC',
                ]) as $row
            ) {
                $fields[$row['field']] = $row['label'] !== '' ? $row['label'] : $row['field'];
            }
        }

        return $cache[$itemtype] = $fields;
    }


    /**
     * Every configured mapping, for the settings page and the OT source picker.
     *
     * @return array<int, array{id: int, itemtype: string, field: string, label: string}>
     */
    public static function getAllMappings(): array
    {
        /** @var DBmysql $DB */
        global $DB;

        if (!$DB->tableExists(self::getTable())) {
            return [];
        }

        $rows = [];
        foreach ($DB->request(['FROM' => self::getTable(), 'ORDER' => ['itemtype ASC', 'id ASC']]) as $row) {
            $rows[] = [
                'id'       => (int) $row['id'],
                'itemtype' => $row['itemtype'],
                'field'    => $row['field'],
                'label'    => $row['label'] !== '' ? $row['label'] : $row['field'],
            ];
        }

        return $rows;
    }


    /**
     * Fields an administrator may map for an itemtype.
     *
     * Plain scalar columns of the asset table come first (labelled through the
     * itemtype's search options when one matches), then - for GLPI 11 custom
     * assets - the simple-typed custom fields of the definition.
     *
     * @return array<string, string> field => label
     */
    public static function getAvailableFields(string $itemtype): array
    {
        /** @var DBmysql $DB */
        global $DB;

        if (!is_a($itemtype, CommonDBTM::class, true)) {
            return [];
        }

        $table = getTableForItemType($itemtype);
        if (!$DB->tableExists($table)) {
            return [];
        }

        $labels = [];
        foreach (Search::getOptions($itemtype) as $opt) {
            if (is_array($opt) && ($opt['table'] ?? '') === $table && isset($opt['field'], $opt['name'])) {
                $labels[$opt['field']] ??= $opt['name'];
            }
        }

        $available = [];
        foreach ($DB->listFields($table) as $column => $spec) {
            $type = strtolower((string) $spec['Type']);
            if (
                in_array($column, self::EXCLUDED_COLUMNS, true)
                || str_ends_with($column, '_id')
                || str_starts_with($column, 'is_')
                || str_starts_with($column, 'date_')
                || preg_match('/^(tinyint|longtext|json|blob)/', $type) === 1
            ) {
                continue;
            }
            $available[$column] = $labels[$column] ?? $column;
        }

        if (PluginOrderReference::isCustomAsset($itemtype)) {
            foreach (self::getCustomFieldDefinitionsFor($itemtype) as $custom) {
                $type_base = substr((string) $custom['type'], (int) strrpos((string) $custom['type'], '\\') + 1);
                if (!in_array($type_base, ['StringType', 'TextType', 'NumberType', 'DateType', 'DateTimeType', 'URLType'], true)) {
                    continue;
                }
                $available['custom_' . $custom['system_name']] = $custom['label'] !== ''
                    ? $custom['label']
                    : $custom['system_name'];
            }
        }

        foreach (self::getFieldsPluginFields($itemtype) as $key => $label) {
            $available[$key] = $label;
        }

        ksort($available);

        return $available;
    }


    /**
     * Simple-typed fields the "fields" plugin defines for an itemtype.
     *
     * That plugin is where sites usually keep extra data on NATIVE assets (an
     * IMEI on phones, say): containers declare which itemtypes they cover and
     * each holds a list of fields, with values living in one generated table
     * per container. Mapped keys encode the container: fields_<cid>_<name>.
     *
     * @return array<string, string> mapping key => label
     */
    private static function getFieldsPluginFields(string $itemtype): array
    {
        /** @var DBmysql $DB */
        global $DB;

        if (
            !Plugin::isPluginActive('fields')
            || !$DB->tableExists('glpi_plugin_fields_containers')
            || !$DB->tableExists('glpi_plugin_fields_fields')
        ) {
            return [];
        }

        $result = [];
        foreach (
            $DB->request([
                'FROM'  => 'glpi_plugin_fields_containers',
                'WHERE' => ['is_active' => 1],
            ]) as $container
        ) {
            $covered = json_decode((string) $container['itemtypes'], true);
            if (!is_array($covered) || !in_array($itemtype, $covered, true)) {
                continue;
            }

            foreach (
                $DB->request([
                    'FROM'  => 'glpi_plugin_fields_fields',
                    'WHERE' => [
                        'plugin_fields_containers_id' => $container['id'],
                        'is_active'                   => 1,
                        'multiple'                    => 0,
                        'type'                        => ['text', 'number', 'url', 'date', 'datetime'],
                    ],
                ]) as $field
            ) {
                $key   = sprintf('fields_%d_%s', $container['id'], $field['name']);
                $label = $field['label'] !== '' ? $field['label'] : $field['name'];
                $result[$key] = sprintf('%s [%s]', $label, $container['label']);
            }
        }

        return $result;
    }


    /**
     * Custom field definitions of a custom asset itemtype, as raw rows.
     *
     * @return array<int, array>
     */
    private static function getCustomFieldDefinitionsFor(string $itemtype): array
    {
        /** @var DBmysql $DB */
        global $DB;

        $definition_id = PluginOrderReference::getAssetDefinitionId($itemtype);
        if ($definition_id === null || !$DB->tableExists('glpi_assets_customfielddefinitions')) {
            return [];
        }

        $rows = [];
        foreach (
            $DB->request([
                'FROM'  => 'glpi_assets_customfielddefinitions',
                'WHERE' => ['assets_assetdefinitions_id' => $definition_id],
            ]) as $row
        ) {
            $rows[] = $row;
        }

        return $rows;
    }


    /**
     * Merge the extra values a generation row posted into the asset input.
     *
     * Only configured fields pass: the mapping is the trust boundary, so a
     * hand-crafted POST cannot set arbitrary columns. Custom-asset fields keep
     * their `custom_<name>` key - the core asset class encodes them itself.
     *
     * @param array<string, string> $posted Raw extra values from the form row
     */
    public static function applyExtras(array $input, string $itemtype, array $posted): array
    {
        foreach (self::getForItemtype($itemtype) as $field => $label) {
            // fields-plugin values live in container tables, not on the asset
            // row: they are written separately, after the asset exists.
            if (str_starts_with($field, 'fields_')) {
                continue;
            }
            $value = trim((string) ($posted[$field] ?? ''));
            if ($value !== '') {
                $input[$field] = $value;
            }
        }

        return $input;
    }


    /**
     * Write mapped fields-plugin values for a freshly created asset.
     *
     * Values go through the generated container class (one row per item in the
     * container's own table), grouped per container. The deliberate bypass of
     * the plugin's form validation mirrors what an import does: the generation
     * form only offers administrator-mapped fields.
     */
    public static function writeFieldsPluginValues(string $itemtype, int $items_id, array $posted): void
    {
        /** @var DBmysql $DB */
        global $DB;

        if ($items_id <= 0 || !Plugin::isPluginActive('fields') || !class_exists('PluginFieldsContainer')) {
            return;
        }

        $per_container = [];
        foreach (self::getForItemtype($itemtype) as $field => $label) {
            if (!str_starts_with($field, 'fields_')) {
                continue;
            }
            $value = trim((string) ($posted[$field] ?? ''));
            if ($value === '') {
                continue;
            }
            if (preg_match('/^fields_(\d+)_(.+)$/', $field, $m) !== 1) {
                continue;
            }
            $per_container[(int) $m[1]][$m[2]] = $value;
        }

        foreach ($per_container as $containers_id => $values) {
            $container = new PluginFieldsContainer();
            if (!$container->getFromDB($containers_id) || !(bool) $container->fields['is_active']) {
                continue;
            }

            $classname = PluginFieldsContainer::getClassname($itemtype, $container->fields['name']);
            if (!class_exists($classname)) {
                continue;
            }

            $row = [
                'plugin_fields_containers_id' => $containers_id,
                'itemtype'                    => $itemtype,
                'items_id'                    => $items_id,
            ] + $values;

            $obj = new $classname();
            if ($obj->getFromDBByCrit(['items_id' => $items_id])) {
                $obj->update(['id' => $obj->fields['id']] + $row);
            } else {
                $obj->add($row);
            }
        }
    }


    /**
     * Read one mapped field's value from a created/loaded asset.
     *
     * Used by the OT document when the administrator picks a mapped field as
     * the serial-number source. Custom-asset values live in the JSON blob
     * keyed by definition id, so they are resolved through the definition.
     */
    public static function resolveValueForAsset(CommonDBTM $asset, string $field): string
    {
        if (preg_match('/^fields_(\d+)_(.+)$/', $field, $m) === 1) {
            if (!Plugin::isPluginActive('fields') || !class_exists('PluginFieldsContainer')) {
                return '';
            }
            $container = new PluginFieldsContainer();
            if (!$container->getFromDB((int) $m[1])) {
                return '';
            }
            $classname = PluginFieldsContainer::getClassname($asset::class, $container->fields['name']);
            if (!class_exists($classname)) {
                return '';
            }
            $obj = new $classname();
            if (!$obj->getFromDBByCrit(['items_id' => $asset->getID()])) {
                return '';
            }
            $value = $obj->fields[$m[2]] ?? '';

            return is_scalar($value) ? (string) $value : '';
        }

        if (!str_starts_with($field, 'custom_')) {
            $value = $asset->fields[$field] ?? '';

            return is_scalar($value) ? (string) $value : '';
        }

        if (!(PluginOrderReference::isCustomAsset($asset::class))) {
            return '';
        }

        $system_name = substr($field, strlen('custom_'));
        $decoded = json_decode((string) ($asset->fields['custom_fields'] ?? ''), true) ?: [];
        foreach (self::getCustomFieldDefinitionsFor($asset::class) as $custom) {
            if ($custom['system_name'] === $system_name) {
                $value = $decoded[$custom['id']] ?? '';

                return is_scalar($value) ? (string) $value : '';
            }
        }

        return '';
    }


    public function prepareInputForAdd($input)
    {
        $itemtype = (string) ($input['itemtype'] ?? '');
        $field    = (string) ($input['field'] ?? '');

        $available = self::getAvailableFields($itemtype);
        if ($itemtype === '' || !isset($available[$field])) {
            Session::addMessageAfterRedirect(__s("This field cannot be mapped for this item type", "order"), true, ERROR);
            return [];
        }

        if (countElementsInTable(self::getTable(), ['itemtype' => $itemtype, 'field' => $field]) > 0) {
            Session::addMessageAfterRedirect(__s("This field is already mapped for this item type", "order"), true, ERROR);
            return [];
        }

        $input['label'] = $available[$field];

        return $input;
    }


    public static function install(Migration $migration)
    {
        /** @var DBmysql $DB */
        global $DB;

        $default_charset   = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();
        $default_key_sign  = DBConnection::getDefaultPrimaryKeySignOption();

        $table = self::getTable();
        if (!$DB->tableExists($table)) {
            $migration->displayMessage('Installing ' . $table);
            $DB->doQuery(
                "CREATE TABLE IF NOT EXISTS `{$table}` (
                   `id` int {$default_key_sign} NOT NULL auto_increment,
                   `itemtype` varchar(255) NOT NULL default '',
                   `field` varchar(255) NOT NULL default '',
                   `label` varchar(255) NOT NULL default '',
                   PRIMARY KEY (`id`),
                   UNIQUE KEY `unicity` (`itemtype`,`field`)
                 ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;",
            );
        }
    }


    public static function uninstall()
    {
        /** @var DBmysql $DB */
        global $DB;

        $DB->doQuery("DROP TABLE IF EXISTS `" . self::getTable() . "`");
    }
}
