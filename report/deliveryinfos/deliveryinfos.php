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
$USEDBREPLICATE = 1;
$DBCONNECTION_REQUIRED = 0; // Really a big SQL request

include ("../../../../inc/includes.php");

$report = new PluginReportsAutoReport(__("deliveryinfos_report_title", "order"));
new PluginReportsDateIntervalCriteria($report, 'order_date', __("Date of order", "order"));
new PluginReportsDateIntervalCriteria($report, 'deliverydate', __("Delivery date"));
new PluginReportsLocationCriteria($report, 'locations_id', __("Delivery location", "order"));
new PluginReportsSupplierCriteria($report, 'suppliers_id', __("Supplier"));
new PluginReportsDropdownCriteria($report, 'plugin_order_orderstates_id', 'PluginOrderOrderState',
                                  __("Status"));
$report->displayCriteriasForm();

if ($report->criteriasValidated()) {
   $report->setSubNameAuto();

   $report->setColumns([
      new PluginReportsColumnLink('suppliers_id',
                                  __("Supplier"), 'Supplier'),
      new PluginReportsColumnLink('entities_id',
                                  __("Entity"), 'Entity'),
      new PluginReportsColumnInteger('total', __("Orders total", "order")),
      new PluginReportsColumnInteger('late', __("Late orders total", "order")),
   ]);
   //TODO : ne pas chercher dans la poublelles

   $query_total = "SELECT count(*) FROM `glpi_plugin_order_orders`";
   $query_total .= getEntitiesRestrictRequest(" WHERE", "glpi_plugin_order_orders");
   $query_total .= $report->addSqlCriteriasRestriction();
   $query_total .= "AND `glpi_plugin_order_orders`.`suppliers_id`=`suppliers`.`id`";
   $query_late = $query_total." AND `is_late`='1' AND `is_deleted`='0' AND `is_template`='0'";

   $supplier = "JOIN `glpi_suppliers`as suppliers
                                 ON (`glpi_plugin_order_orders`.`suppliers_id` = suppliers.`id`)";


   $query = "SELECT DISTINCT `suppliers_id`, ($query_total) AS `total`, ($query_late) AS `late`
            FROM `glpi_plugin_order_orders` $supplier";
   $query .= getEntitiesRestrictRequest(" WHERE", "glpi_plugin_order_orders");
   $query .= $report->addSqlCriteriasRestriction();
   $report->setGroupBy("suppliers_id");
   $report->setSqlRequest($query);
   $report->execute();
}
