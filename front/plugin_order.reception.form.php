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
	"ocsng",	"computer",	"printer",	"networking",	"monitor",
	"software",	"peripheral",	"phone",	"tracking",	"document",
	"user",	"enterprise",	"contract",	"infocom",	"group",
	"cartridge",	"consumable");
   
define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");

usePlugin('order', true);
$plugin = new Plugin;
if ($plugin->isActivated("genericobject"))
	usePlugin('genericobject');

/* reception d'une ligne d�tail */
if (isset ($_POST["reception"])) {
	plugin_order_updateReceptionStatus($_POST);
	glpi_header($_SERVER["HTTP_REFERER"]);
}

if (isset ($_POST["bulk_reception"])) {
	plugin_order_updateBulkReceptionStatus($_POST);
	glpi_header($_SERVER["HTTP_REFERER"]);
}


/*
 *  affiche le tableau permettant la g�n�ration de mat�riel */
if (isset ($_POST["generation"])) {
	if (isset ($_POST["item"])) {
		$detail = new PluginOrderDetail;
		
		foreach ($_POST["item"] as $key => $val) {
			if ($val == 1) {
				$detail->getFromDB($_POST["ID"][$key]);
				if ($detail->fields["status"] == ORDER_DEVICE_NOT_DELIVRED) {
					addMessageAfterRedirect($LANG['plugin_order'][45], true, ERROR);
					glpi_header($_SERVER["HTTP_REFERER"]);
				}
			}
		}
	}

	if (isset ($_POST["item"]))
		plugin_order_plugin_order_showItemGenerationForm($_SERVER["PHP_SELF"], $_POST);
	else {
		addMessageAfterRedirect($LANG['plugin_order']['detail'][29], false, ERROR);
		glpi_header($_SERVER["HTTP_REFERER"]);
	}
}
/* g�n�re le mat�riel */
if (isset ($_POST["generate"])) {
	plugin_order_generateNewDevice($_POST);
	glpi_header($CFG_GLPI["root_doc"] . "/plugins/order/front/plugin_order.form.php?ID=" . $_POST["orderID"] . "");
}
/* supprime un lien d'une ligne d�tail vers un mat�riel */
if (isset ($_POST["deleteLinkWithDevice"])) {
	foreach ($_POST["item"] as $key => $val) {
		if ($val == 1)
			plugin_order_deleteLinkWithDevice($key, $_POST["type"][$key]);
	}
	glpi_header($CFG_GLPI["root_doc"] . "/plugins/order/front/plugin_order.form.php?ID=" . $_POST["orderID"] . "");
}
/* cr�e un lien d'une ligne d�tail vers un mat�riel */
if (isset ($_POST["createLinkWithDevice"])) {
	$i = 0;
	if (count($_POST["item"]) <= 1 || in_array($_POST["FK_type"],$ORDER_RESTRICTED_TYPES)) {
		$detail = new PluginOrderDetail;

		foreach ($_POST["item"] as $key => $val)
		{
			if ($val == 1)
			{
				$detail->getFromDB($_POST["ID"][$key]);
				if ($detail->fields["status"] == ORDER_DEVICE_NOT_DELIVRED) {
					addMessageAfterRedirect($LANG['plugin_order'][46], true, ERROR);
					glpi_header($_SERVER["HTTP_REFERER"]);
				} else
				{
					plugin_order_createLinkWithDevice($key, $_POST["device"], $_POST["type"][$key], $_POST["orderID"]);
					
				}
			}
		}
	} else
		addMessageAfterRedirect($LANG['plugin_order'][42], true, ERROR);
	glpi_header("" . $CFG_GLPI["root_doc"] . "/plugins/order/front/plugin_order.form.php?ID=" . $_POST["orderID"] . "");
}
?>