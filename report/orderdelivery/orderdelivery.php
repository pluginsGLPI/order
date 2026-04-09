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
use GlpiPlugin\Reports\AutoReport;
use GlpiPlugin\Reports\Column;
use GlpiPlugin\Reports\ColumnDateTime;
use GlpiPlugin\Reports\ColumnLink;
use GlpiPlugin\Reports\DateIntervalCriteria;
use GlpiPlugin\Reports\DropdownCriteria;
use GlpiPlugin\Reports\LocationCriteria;
use GlpiPlugin\Reports\SupplierCriteria;

$USEDBREPLICATE = 1;
$DBCONNECTION_REQUIRED = 0; // Really a big SQL request

$report = new AutoReport(__s("Orders delivery", "order"));

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
            'entities_id',
            __s("Entity"),
            'Entity',
        ),
        new ColumnLink(
            'id',
            __s("Name"),
            'PluginOrderOrder',
            [
                'with_comment'  => true,
                'with_navigate' => true,
            ],
        ),
        new Column('num_order', __s("Order number", "order")),
        new ColumnLink(
            'suppliers_id',
            __s("Supplier"),
            'Supplier',
        ),
        new ColumnLink(
            'plugin_order_orderstates_id',
            __s("Status"),
            'PluginOrderOrderState',
            ['with_comment' => true],
        ),
        new ColumnDateTime('order_date', __s("Date of order", "order")),
        new ColumnDateTime('duedate', __s("Estimated due date", "order")),
        new ColumnDateTime('deliverydate', __s("Delivery date")),
        new ColumnLink(
            'locations_id',
            __s("Delivery location", "order"),
            'Location',
            ['with_comment' => true],
        ),
    ]);

    $criteria = [
        'SELECT' => [
            '*',
        ],
        'FROM' => 'glpi_plugin_order_orders',
        'WHERE' => [
            'is_deleted' => 0,
            'is_template' => 0,
        ],
        'GROUPBY' => ['entities_id', 'plugin_order_orderstates_id', 'num_order', 'order_date'],
    ];

    $criteria['WHERE'] = $criteria['WHERE'] + getEntitiesRestrictCriteria(
            'glpi_plugin_order_orders'
        );

    $criteria['WHERE'] = $criteria['WHERE'] + $report->addNewSqlCriteriasRestriction();

    $report->setSqlRequest($criteria);

    $report->execute();
}

$report->footer();

