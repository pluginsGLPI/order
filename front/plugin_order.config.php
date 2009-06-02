<?php
/*----------------------------------------------------------------------
   GLPI - Gestionnaire Libre de Parc Informatique
   Copyright (C) 2003-2008 by the INDEPNET Development Team.

   http://indepnet.net/   http://glpi-project.org/
   ----------------------------------------------------------------------
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
   ----------------------------------------------------------------------*/
/*----------------------------------------------------------------------
    Original Author of file: 
    Purpose of file:
    ----------------------------------------------------------------------*/
    
if (!defined('GLPI_ROOT'))
	define('GLPI_ROOT', '../../..'); 
include (GLPI_ROOT."/inc/includes.php");
useplugin('order',true);

if(!isset($_GET["action"])) $_GET["action"] = "";
$action=$_GET["action"];
/* create orders with infocoms */
if($action=="createorder") {
	plugin_order_config_infocoms_create();
/* delete orders created with infocoms */
} elseif($action=="deleteorder") {
	plugin_order_config_infocoms_delete();
/* update default status config */
} elseif(isset($_POST["update_status"])) {
	plugin_order_config_default_status();
}

commonHeader($LANG['plugin_order'][4],$_SERVER['PHP_SELF'],"plugins","order");
echo "<div class='center'>";
echo "<table class='tab_cadre'>";
echo "<tr><th>".$LANG['plugin_order'][4]."</th></tr>";
echo "<tr><td><table class='tab_cadre'>";
echo "<tr><th>".$LANG['plugin_order'][37]."</th></tr>";
echo "<tr class='tab_bg_1' align='center'><td><a href=".$_SERVER["PHP_SELF"]."?action=createorder>".$LANG['plugin_order'][34]."</a></td></tr>";
echo "<tr class='tab_bg_1' align='center'><td><a href=".$_SERVER["PHP_SELF"]."?action=deleteorder>".$LANG['plugin_order'][35]."</a></td></tr>";
echo "</table></td></tr>";
echo "<tr><td><table class='tab_cadre'>";
echo "<tr><th colspan='3'>".$LANG['plugin_order'][38]."</th></tr>";
echo "<form method='post' name=form action=''>";
$config= new plugin_order_config();
$config->getFromDB(1);
/* default status order creation */
echo "<tr><td><b>".$LANG['plugin_order']['status'][4]."</b></td><td>";
dropdownValue("glpi_dropdown_plugin_order_status", "status_creation", $config->fields["status_creation"],1);
echo "</td>";
/* default status delivered order */
echo "<tr><td><b>".$LANG['plugin_order']['status'][5]."</b></td><td>";
dropdownValue("glpi_dropdown_plugin_order_status", "status_delivered", $config->fields["status_delivered"],1);
echo "</td>";
/* default status no delivered order */
echo "<tr><td><b>".$LANG['plugin_order']['status'][6]."</b></td><td>";
dropdownValue("glpi_dropdown_plugin_order_status", "status_nodelivered", $config->fields["status_nodelivered"],1);
echo "</td><td><input type='submit' name='update_status' value=\"".$LANG['buttons'][7]."\" class='submit' ></td>";
echo "</table></td></tr>";
echo "</table>";
echo "</div>";
commonFooter();
?>