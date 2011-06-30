<?php
/*
 * @version $Id: HEADER 2011-03-23 15:41:26 tsmr $
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
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 --------------------------------------------------------------------------
// ----------------------------------------------------------------------
// Original Authors of file: 
// NOUH Walid & FONTAN Benjamin & CAILLAUD Xavier & François Legastelois
// Purpose of file: 
// ----------------------------------------------------------------------
// ---------------------------------------------------------------------- */

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

if (!isset ($_POST["id"])) {
   exit ();
}

if (!isset ($_POST["withtemplate"]))
   $_POST["withtemplate"] = "";

$PluginOrderOrder_Supplier = new PluginOrderOrder_Supplier();
$PluginOrderOrder_Supplier->checkGlobal("r");

if ($_POST["id"]>0 && $PluginOrderOrder_Supplier->can($_POST["id"],'r')) {

   if (!empty($_POST["withtemplate"])) {
      switch($_REQUEST['glpi_tab']) {
         default :
            break;
      }
   } else {
      switch($_REQUEST['glpi_tab']) {
         case 12 :
            Log::showForItem($PluginOrderOrder_Supplier);
            break;
         default :
            break;
      }
   }
}

ajaxFooter();

?>