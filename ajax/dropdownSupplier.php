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

if (strpos($_SERVER['PHP_SELF'],"dropdownSupplier.php")) {
   define('GLPI_ROOT','../../..');
   include (GLPI_ROOT."/inc/includes.php");
   header("Content-Type: text/html; charset=UTF-8");
   header_nocache();
}

checkCentralAccess();

// Make a select box

if (isset($_POST["suppliers_id"])) {

   $rand=$_POST['rand'];

   $use_ajax=false;
   if ($CFG_GLPI["use_ajax"] && 
      countElementsInTable('glpi_suppliers',
                           "`glpi_suppliers`.`id` = '".$_POST["suppliers_id"]."' ".
                              getEntitiesRestrictRequest("AND", "glpi_suppliers","",
                                                         $_POST["entity_restrict"],true)) > 
                                                            $CFG_GLPI["ajax_limit_count"]
   ){
      $use_ajax=true;
   }

   $paramssuppliers_id=array('searchText'=>'__VALUE__',
         'suppliers_id'=>$_POST["suppliers_id"],
         'entity_restrict'=>$_POST["entity_restrict"],
         'rand'=>$_POST['rand'],
         'myname'=>$_POST['myname']
         );
   
   $default="<select name='".$_POST["myname"]."'><option value='0'>".DROPDOWN_EMPTY_VALUE.
               "</option></select>";
   ajaxDropdown($use_ajax,"/plugins/order/ajax/dropdownContact.php",$paramssuppliers_id,$default,
                $rand);

}

?>