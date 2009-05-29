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

$NEEDED_ITEMS=array("document","user","enterprise");
define('GLPI_ROOT', '../../..'); 
include (GLPI_ROOT."/inc/includes.php");

useplugin('order',true);

if(!isset($_GET["ID"])) $_GET["ID"] = "";
if(!isset($_GET["withtemplate"])) $_GET["withtemplate"] = "";

$plugin_order_ref=new plugin_order_reference();

/* add order */
if (isset($_POST["add"]))
{
	if(plugin_order_HaveRight("reference","w"))
	{
		$newID=$plugin_order_ref->add($_POST);
	}
	glpi_header($_SERVER['HTTP_REFERER']);
}
/* delete order */
else if (isset($_POST["delete"]))
{
	if(plugin_order_HaveRight("reference","w"))
		$plugin_order_ref->delete($_POST);
	glpi_header($CFG_GLPI["root_doc"]."/plugins/order/front/plugin_order.reference.php");
}
/* restore order */
else if (isset($_POST["restore"]))
{
	if(plugin_order_HaveRight("reference","w"))
		$plugin_order_ref->restore($_POST);
	glpi_header($_SERVER['HTTP_REFERER']);
}
/* purge order */
else if (isset($_POST["purge"]))
{
	if(plugin_order_HaveRight("reference","w"))
		$plugin_order_ref->delete($_POST,1);
	glpi_header($_SERVER['HTTP_REFERER']);
}
/* update order */
else if (isset($_POST["update"]))
{
	if(plugin_order_HaveRight("order","w"))
		$plugin_order_ref->update($_POST);
	glpi_header($_SERVER['HTTP_REFERER']);
}
else
{
	plugin_order_checkRight("reference","r");

	if (!isset($_SESSION['glpi_tab'])) $_SESSION['glpi_tab']=1;
	if (isset($_GET['onglet'])) {
		$_SESSION['glpi_tab']=$_GET['onglet'];
	}
	
	commonHeader($LANG['plugin_order']['reference'][1],$_SERVER["PHP_SELF"],"plugins","order","reference");
	
	/* load order form */
	$plugin_order_ref->showForm($_SERVER["PHP_SELF"],$_GET["ID"]);

	commonFooter();
}

?>