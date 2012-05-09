<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 --------------------------------------------------------------------------
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
$USEDBREPLICATE=1;
$DBCONNECTION_REQUIRED=0; // Really a big SQL request

define('GLPI_ROOT', '../../../..');
include (GLPI_ROOT . "/inc/includes.php");

$report = new PluginReportsAutoReport();
new PluginReportsDateIntervalCriteria($report, 'order_date', $LANG['plugin_order'][1]);
new PluginReportsDateIntervalCriteria($report, 'deliverydate', $LANG['plugin_order'][53]);
new PluginReportsLocationCriteria($report, 'locations_id', $LANG['plugin_order'][40]);
new PluginReportsSupplierCriteria($report, 'suppliers_id', $LANG['financial'][26]);
new PluginReportsDropdownCriteria($report, 'plugin_order_orderstates_id', 'PluginOrderOrderState',
                                  $LANG['joblist'][0]);
$report->displayCriteriasForm();

if ($report->criteriasValidated()) {
   $report->setSubNameAuto();

   $report->setColumns(array(new PluginReportsColumnLink('entities_id',
                                                         $LANG['entity'][0], 'Entity'),
                             new PluginReportsColumnLink('id', $LANG['common'][16],
                                                         'PluginOrderOrder',
                                                         array('with_comment' => true,
                                                               'with_navigate' => true)),
                             new PluginReportsColumn('num_order', $LANG['plugin_order'][0]),
                             new PluginReportsColumnLink('suppliers_id',
                                                         $LANG['financial'][26], 'Supplier'),
                             new PluginReportsColumnLink('plugin_order_orderstates_id',
                                                         $LANG['joblist'][0],
                                                         'PluginOrderOrderState',
                                                         array('with_comment' => true)),
                             new PluginReportsColumnDateTime('order_date', $LANG['plugin_order'][1]),
                             new PluginReportsColumnDateTime('duedate', $LANG['plugin_order'][50]),
                             new PluginReportsColumnDateTime('deliverydate', $LANG['plugin_order'][53]),
                             new PluginReportsColumnLink('locations_id',
                                                         $LANG['plugin_order'][40], 'Location',
                                                         array('with_comment' => true))
                       ));
   
    //TODO : ne pas chercher dans la poublelles
   $query = "SELECT * FROM `glpi_plugin_order_orders`";
   $query.= getEntitiesRestrictRequest(" WHERE", "glpi_plugin_order_orders");
   $query.= $report->addSqlCriteriasRestriction();
   $query.= " AND `is_deleted`='0' AND `is_template`='0' ";
   $query.="GROUP BY `entities_id`, `plugin_order_orderstates_id`, `num_order`, `order_date`";
   $report->setGroupBy("entities_id", "plugin_order_orderstates_id", "num_order", "order_date");
   $report->setSqlRequest($query);
   $report->execute();
}
?>