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
// Original Authors of file: 
// NOUH Walid & FONTAN Benjamin & CAILLAUD Xavier & François Legastelois
// Purpose of file: plugin order v1.3.0 - GLPI 0.78.3
// ---------------------------------------------------------------------- */

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
header_nocache();

if(!isset($_POST["id"])) {
	exit();
}

if (!isset ($_POST["withtemplate"]))
	$_POST["withtemplate"] = "";

$PluginOrderReference = new PluginOrderReference();
$PluginOrderReference->checkGlobal("r");
$PluginOrderReference_Supplier = new PluginOrderReference_Supplier();

if ($_POST["id"]>0 && $PluginOrderReference->can($_POST["id"],'r')) {
   if (!empty($_POST["withtemplate"])) {
		switch($_REQUEST['glpi_tab']) {
			default :
				break;
		}
	} else {
		switch($_REQUEST['glpi_tab']) {
         case -1:
            $PluginOrderReference_Supplier->showReferenceManufacturers($CFG_GLPI["root_doc"] .
         "/plugins/order/front/reference_supplier.form.php",$_POST["id"]);
            if ($PluginOrderReference->can($_POST["id"],'w'))
               $PluginOrderReference_Supplier->showForm("", 
               array('plugin_order_references_id' => $_POST["id"], 
               'target' => $CFG_GLPI["root_doc"] ."/plugins/order/front/reference_supplier.form.php"));

            Document::showAssociated($PluginOrderReference);
         case 2 :  
            $PluginOrderReference->getAllOrdersByReference($_POST["id"]);
            break;
         case 3 :
            showNotesForm($_POST['target'],"PluginOrderReference",$_POST["id"]);
            break;
         case 4 :
            /* show documents linking form */
            Document::showAssociated($PluginOrderReference);
            break;
         case 12 :
            /* show history form */
            Log::showForItem($PluginOrderReference);
            break;
         default :
            $PluginOrderReference_Supplier->showReferenceManufacturers($CFG_GLPI["root_doc"] .
         "/plugins/order/front/reference_supplier.form.php",$_POST["id"]);
            if ($PluginOrderReference->can($_POST["id"],'w'))
               $PluginOrderReference_Supplier->showForm("", 
               array('plugin_order_references_id' => $_POST["id"], 
               'target' => $CFG_GLPI["root_doc"] ."/plugins/order/front/reference_supplier.form.php"));
            break;
      }
   }
}

ajaxFooter();

?>