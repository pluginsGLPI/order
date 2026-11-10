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
use Migration;
use PHPUnit\Framework\Attributes\DataProvider;
use Plugin;
use PluginOrderConfig;
use PluginOrderOrder_Item;

/**
 * Upgrade of an up-to-date schema (#616): every CHANGE rebuilds a whole table and every post
 * query of the data migrations runs once per row, so replaying them on each upgrade made the
 * upgrade of large databases time out.
 */
final class InstallTest extends OrderTestCase
{
    public function testUpgradeOfAnUpToDateSchemaQueuesNothing(): void
    {
        // As from the plugins page: PluginOrderProfile::install() reads the active profile
        $this->login();
        require_once Plugin::getPhpDir('order') . '/hook.php';

        // Records what install() would queue, without touching the schema of the test database
        $migration = new class (PLUGIN_ORDER_VERSION) extends Migration {
            /** @var list<string> columns whose type (or name) would be changed */
            public array $changed_fields = [];

            /** @var list<string> */
            public array $post_queries = [];

            public function changeField($table, $oldfield, $newfield, $type, $options = [])
            {
                /** @var \DBmysql $DB */
                global $DB;

                // A legacy column renamed long ago is missing: Migration skips it. An existing
                // column would be altered.
                if ($DB->fieldExists($table, $oldfield, false)) {
                    $this->changed_fields[] = sprintf('%s.%s => %s %s', $table, $oldfield, $newfield, $type);
                }

                return false;
            }

            public function addPostQuery($query, $message = null)
            {
                $this->post_queries[] = $query;
            }

            public function migrationOneTable($table)
            {
                // Queued changes are only recorded: nothing reaches the schema of the test
                // database, whose transaction a DDL statement would commit
            }

            public function displayMessage($msg) {}
        };

        foreach (plugin_order_get_install_classes() as $class) {
            $class::install($migration);
        }

        $this->assertSame([], $migration->changed_fields);
        $this->assertSame([], $migration->post_queries);
        $this->assertFalse(PluginOrderOrder_Item::$upgraded_from_before_1_5_0);
    }

    public static function fieldHasTypeProvider(): iterable
    {
        yield 'decimal, same precision' => ['glpi_plugin_order_orders_items', 'price_taxfree', 'decimal(20,6)', true];
        yield 'decimal, other precision' => ['glpi_plugin_order_orders_items', 'price_taxfree', 'decimal(10,2)', false];
        // MySQL 5.7 and MariaDB report "int(10) unsigned", MySQL 8 "int unsigned": both match
        yield 'int unsigned, display width ignored' => ['glpi_plugin_order_orders_items', 'id', 'int unsigned', true];
        yield 'int unsigned, other type' => ['glpi_plugin_order_orders_items', 'id', 'bigint unsigned', false];
        yield 'varchar' => ['glpi_plugin_order_references_suppliers', 'reference_code', 'varchar(255)', true];
        yield 'type compared case insensitively' => ['glpi_plugin_order_references_suppliers', 'reference_code', 'VARCHAR(255)', true];
        // Nothing to change on a column that does not exist (renamed or dropped long ago)
        yield 'missing column' => ['glpi_plugin_order_orders_items', 'price_taxfree2', 'decimal(20,6)', true];
    }

    #[DataProvider('fieldHasTypeProvider')]
    public function testFieldHasType(string $table, string $field, string $type, bool $expected): void
    {
        $this->assertSame($expected, PluginOrderConfig::fieldHasType($table, $field, $type));
    }
}
