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
include (GLPI_ROOT."/inc/includes.php");

if(!isset($_GET["id"])) $_GET["id"] = "";
if(!isset($_GET["withtemplate"])) $_GET["withtemplate"] = "";
if(!isset($_GET["plugin_order_references_id"])) $_GET["plugin_order_references_id"] = "";

$PluginOrderReference_Supplier = new PluginOrderReference_Supplier();

if (isset($_POST["add"]))
{
   if ($PluginOrderReference_Supplier->canCreate())
   {
      if (isset($_POST["suppliers_id"]) && $_POST["suppliers_id"] > 0)
      {
         $newID=$PluginOrderReference_Supplier->add($_POST);
      }
   }
   glpi_header($_SERVER['HTTP_REFERER']);
}
else if (isset($_POST["update"]))
{
   if ($PluginOrderReference_Supplier->canCreate())
      $PluginOrderReference_Supplier->update($_POST);
   glpi_header($_SERVER['HTTP_REFERER']);
}
else if (isset($_POST["delete"]))
{
   if ($PluginOrderReference_Supplier->canCreate())
   {
      foreach ($_POST["check"] as $ID => $value)
         $PluginOrderReference_Supplier->delete(array("id"=>$ID));
   }
   glpi_header($_SERVER['HTTP_REFERER']);
}
else
{
   $PluginOrderReference_Supplier->checkGlobal("r");
   
   commonHeader($LANG['plugin_order']['reference'][5],'',"plugins","order","reference");
   
   /* load order form */
   $PluginOrderReference_Supplier->showForm($_GET["id"], 
                                            array('plugin_order_references_id' => 
                                               $_GET["plugin_order_references_id"]));

   commonFooter();
}

?>