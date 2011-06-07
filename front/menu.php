<?php
/*
 * @version $Id: HEADER 2011-03-23 15:41:26 tsmr $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2010 by the INDEPNET Development Team.

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
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
// ----------------------------------------------------------------------
// Original Authors of file: 
// NOUH Walid & FONTAN Benjamin & CAILLAUD Xavier & François Legastelois
// Purpose of file: plugin order v1.4.0 - GLPI 0.80
// ----------------------------------------------------------------------
// ---------------------------------------------------------------------- */

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

commonHeader($LANG['plugin_order']['title'][1], '', "plugins", "order", "menu");

$PluginOrderOrder = new PluginOrderOrder();
$PluginOrderReference = new PluginOrderReference();

if ($PluginOrderOrder->canView() 
      || $PluginOrderReference->canView()) {
   echo "<div class='center'>";
   echo "<table class='tab_cadre'>";
   echo "<tr><th colspan='2'>" . $LANG['plugin_order']['title'][1] . "</th></tr>";

   if ($PluginOrderOrder->canView()) {
      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td><img src='../pics/order-icon.png'></td>";
      echo "<td><a href='order.php'>" . $LANG['plugin_order']['menu'][1] . "</a></td></tr>";
   }

   if ($PluginOrderReference->canView()) {
      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td><img src='../pics/reference-icon.png'></td>";
      echo "<td><a href='reference.php'>" . $LANG['plugin_order']['menu'][2] . "</a></td></tr>";
   }
   echo "</table></div>";
} else {
   echo "<div align='center'><br><br><img src=\"" . $CFG_GLPI["root_doc"] . 
         "/pics/warning.png\" alt=\"warning\"><br><br>";
   echo "<b>" . $LANG['login'][5] . "</b></div>";
}

commonFooter();

?>