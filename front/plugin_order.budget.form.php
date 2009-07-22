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

$NEEDED_ITEMS = array (
	"computer",
	"printer",
	"networking",
	"monitor",
	"software",
	"peripheral",
	"phone",
	"tracking",
	"document",
	"user",
	"enterprise",
	"contract",
	"infocom",
	"group",
	"cartridge",
	"consumable"
);
define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

useplugin('order', true);

if (!isset ($_GET["ID"]))
	$_GET["ID"] = "";
if (!isset ($_GET["withtemplate"]))
	$_GET["withtemplate"] = "";

$plugin_order = new PluginOrderBudget();

/* add order */
if (isset ($_POST["add"])) {
	if (plugin_order_HaveRight("budget", "w"))
			$newID = $plugin_order->add($_POST);
	glpi_header($_SERVER['HTTP_REFERER']);
}
/* delete order */
else
	if (isset ($_POST["delete"])) {
		if (plugin_order_HaveRight("budget", "w"))
			$plugin_order->delete($_POST);
		glpi_header($CFG_GLPI["root_doc"] . "/plugins/order/front/plugin_genericobject.budget.php");
	}
/* restore order */
elseif (isset ($_POST["restore"])) {
		if (plugin_order_HaveRight("budget", "w"))
			$plugin_order->restore($_POST);
	glpi_header($_SERVER['HTTP_REFERER']);
	}
/* purge order */
elseif (isset ($_POST["purge"])) {
		if (plugin_order_HaveRight("budget", "w"))
			$plugin_order->delete($_POST, 1);
		glpi_header($CFG_GLPI["root_doc"] . "/plugins/order/front/plugin_genericobject.budget.php");
	}
/* update order */
elseif (isset ($_POST["update"])) {
		if (plugin_order_HaveRight("budget", "w"))
			$plugin_order->update($_POST);
		glpi_header($_SERVER['HTTP_REFERER']);
	} 


	plugin_order_checkRight("budget", "r");

	if (!isset ($_SESSION['glpi_tab']))
		$_SESSION['glpi_tab'] = 1;
	if (isset ($_GET['onglet'])) 
		$_SESSION['glpi_tab'] = $_GET['onglet'];

	commonHeader($LANG['financial'][87], $_SERVER["PHP_SELF"], "plugins", "order", "budget");
	$plugin_order->title();
	echo "<br>";
	$plugin_order->showForm($_SERVER["PHP_SELF"], $_GET["ID"]);
	commonFooter();
?>