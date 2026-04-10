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

/*
 * ----------------------------------------------------------------------
 * Original Author of file: Nelly Lasson
 *
 * Purpose of file:
 *       Generate location report
 *       Illustrate use of simpleReport
 * ----------------------------------------------------------------------
 */

//Options for GLPI 0.71 and newer : need slave db to access the report
use Glpi\DBAL\QueryExpression;
use Glpi\DBAL\QuerySubQuery;
use GlpiPlugin\Reports\AutoReport;
use GlpiPlugin\Reports\ColumnInteger;
use GlpiPlugin\Reports\ColumnLink;
use GlpiPlugin\Reports\DateIntervalCriteria;
use GlpiPlugin\Reports\DropdownCriteria;
use GlpiPlugin\Reports\LocationCriteria;
use GlpiPlugin\Reports\SupplierCriteria;

$USEDBREPLICATE = 1;
$DBCONNECTION_REQUIRED = 0; // Really a big SQL request

$report = new AutoReport(__s("Orders delivery statistics", "order"));
new DateIntervalCriteria($report, 'order_date', __s("Date of order", "order"));
new DateIntervalCriteria($report, 'deliverydate', __s("Delivery date"));
new LocationCriteria($report, 'locations_id', __s("Delivery location", "order"));
new SupplierCriteria($report, 'suppliers_id', __s("Supplier"));
new DropdownCriteria(
    $report,
    'plugin_order_orderstates_id',
    'PluginOrderOrderState',
    __s("Status"),
);
$report->displayCriteriasForm();

if ($report->criteriasValidated()) {
    $report->setSubNameAuto();

    $report->setColumns([
        new ColumnLink(
            'suppliers_id',
            __s("Supplier"),
            'Supplier',
        ),
        new ColumnLink(
            'entities_id',
            __s("Entity"),
            'Entity',
        ),
        new ColumnInteger('total', __s("Orders total", "order")),
        new ColumnInteger('late', __s("Late orders total", "order")),
    ]);

    $criteria_total = [
        'SELECT' => [
            'COUNT' => 'glpi_plugin_order_orders.id'
        ],
        'FROM' => 'glpi_plugin_order_orders',
        'WHERE' => [
            'glpi_plugin_order_orders.is_deleted' => '0',
            'glpi_plugin_order_orders.is_template' => '0',
            'glpi_plugin_order_orders.suppliers_id' => new QueryExpression('suppliers.id')
        ],
    ];

    $criteria_total['WHERE'] += getEntitiesRestrictCriteria(
        'glpi_plugin_order_orders'
    );

    $criteria_total['WHERE'] += $report->addNewSqlCriteriasRestriction();

    $criteria_late = $criteria_total;
    $criteria_late['WHERE'] += [
        'glpi_plugin_order_orders.is_late' => 1
    ];

    $criteria = [
        'SELECT' => [
            'glpi_plugin_order_orders.suppliers_id',
            new QuerySubQuery($criteria_total, 'total'),
            new QuerySubQuery($criteria_late, 'late')

        ],
        'DISTINCT' => true,
        'FROM' => 'glpi_plugin_order_orders',
        'LEFT JOIN' => [
            'glpi_suppliers as suppliers' => [
                'ON' => [
                    'glpi_plugin_order_orders' => 'suppliers_id',
                    'suppliers' => 'id'
                ]
            ]
        ],
        'WHERE' => [
            'glpi_plugin_order_orders.is_deleted' => '0',
            'glpi_plugin_order_orders.is_template' => '0'
        ],
        'GROUPBY' => ['suppliers_id'],
    ];

    $criteria['WHERE'] += getEntitiesRestrictCriteria(
        'glpi_plugin_order_orders'
    );

    $criteria['WHERE'] += $report->addNewSqlCriteriasRestriction();

    $report->setSqlRequest($criteria);

    $report->execute();
}

$report->footer();
