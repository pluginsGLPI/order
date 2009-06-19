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
include (GLPI_ROOT . "/inc/includes.php");

useplugin('order',true);
		
if(!isset($_POST["ID"])) {
	exit();
}
if(!isset($_POST["sort"])) $_POST["sort"] = "";
if(!isset($_POST["order"])) $_POST["order"] = "";
if(!isset($_POST["withtemplate"])) $_POST["withtemplate"] = "";

	plugin_order_checkRight("reference","r");

	if (empty($_POST["ID"])){
		switch($_POST['glpi_tab']){
			default :
				break;
		}
	}else{
		switch($_POST['glpi_tab']){
			case 1 :
				plugin_order_showReferenceManufacturers($_SERVER['HTTP_REFERER'],$_POST["ID"]);
				plugin_order_addSupplierToReference($_SERVER['HTTP_REFERER'],$_POST["ID"]);
				break;
			case 3 :
				showNotesForm($_SERVER['HTTP_REFERER'],PLUGIN_ORDER_REFERENCE_TYPE,$_POST["ID"]);
				break;
			case 4 :
				/* show documents linking form */
				showDocumentAssociated(PLUGIN_ORDER_REFERENCE_TYPE,$_POST["ID"],$_POST["withtemplate"]);
				break;

			case 12 :
				/* show history form */
				showHistory(PLUGIN_ORDER_REFERENCE_TYPE,$_POST["ID"]);
				break;
			case -1:
				plugin_order_showReferenceManufacturers($_SERVER['HTTP_REFERER'],$_POST["ID"]);
				plugin_order_addSupplierToReference($_SERVER['HTTP_REFERER'],$_POST["ID"]);
				showNotesForm($_SERVER['HTTP_REFERER'],PLUGIN_ORDER_REFERENCE_TYPE,$_POST["ID"]);
				showDocumentAssociated(PLUGIN_ORDER_REFERENCE_TYPE,$_POST["ID"],$_POST["withtemplate"]);
				showHistory(PLUGIN_ORDER_REFERENCE_TYPE,$_POST["ID"]);
			default :
				break;
		}
	}
 ajaxFooter();

?>