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
	glpi_header($_SERVER['HTTP_REFERER']);
/* delete orders created with infocoms */
} elseif($action=="deleteorder") {
	plugin_order_config_infocoms_delete();
	glpi_header($_SERVER['HTTP_REFERER']);
/* update config */
} elseif(isset($_POST["update"])) {
	$config = new plugin_order_config;
	$config->update($_POST);
	glpi_header($_SERVER['HTTP_REFERER']);
}

commonHeader($LANG['plugin_order'][4],$_SERVER["PHP_SELF"],"config","plugins");
$config= new plugin_order_config();
$config->showForm($_SERVER["PHP_SELF"]);
$config->showOrderGenerationForm($_SERVER["PHP_SELF"]);

commonFooter();
?>