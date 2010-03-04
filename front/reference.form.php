<?php
/*
 * @version $Id: HEADER 1 2010-03-03 21:49 Tsmr $
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
// Original Author of file: NOUH Walid & FONTAN Benjamin & CAILLAUD Xavier
// Purpose of file: plugin order v1.2.0 - GLPI 0.78
// ----------------------------------------------------------------------
 */

define('GLPI_ROOT', '../../..'); 
include (GLPI_ROOT."/inc/includes.php");

if(!isset($_GET["id"])) $_GET["id"] = "";
if(!isset($_GET["withtemplate"])) $_GET["withtemplate"] = "";

$PluginOrderReference=new PluginOrderReference();
$PluginOrderReference_Supplier = new PluginOrderReference_Supplier;

/* add order */
if (isset($_POST["add"]))
{
	if(plugin_order_HaveRight("reference","w"))
	{
		$newID=$PluginOrderReference->add($_POST);
	}
	glpi_header($_SERVER['HTTP_REFERER']);
}
/* delete order */
else if (isset($_POST["delete"]))
{
	if(plugin_order_HaveRight("reference","w"))
		$PluginOrderReference->delete($_POST);
	glpi_header(getItemTypeSearchURL('PluginOrderReference'));
}
/* restore order */
else if (isset($_POST["restore"]))
{
	if(plugin_order_HaveRight("reference","w"))
		$PluginOrderReference->restore($_POST);
	glpi_header(getItemTypeSearchURL('PluginOrderReference'));
}
/* purge order */
else if (isset($_POST["purge"]))
{
	if(plugin_order_HaveRight("reference","w"))
		$PluginOrderReference->delete($_POST,1);
	glpi_header(getItemTypeSearchURL('PluginOrderReference'));
}
/* update order */
else if (isset($_POST["update"]))
{
	if(plugin_order_HaveRight("reference","w"))
		$PluginOrderReference->update($_POST);
	glpi_header($_SERVER['HTTP_REFERER']);
}
else
{
	PluginOrderProfile::checkRight("reference","r");

	if (!isset($_SESSION['glpi_tab'])) $_SESSION['glpi_tab']=1;
	if (isset($_GET['onglet'])) {
		$_SESSION['glpi_tab']=$_GET['onglet'];
	}
	
	commonHeader($LANG['plugin_order']['reference'][1],'',"plugins","order","reference");
	
	$PluginOrderReference->showForm($_GET["id"]);

	commonFooter();
}

?>