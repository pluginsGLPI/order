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
header("Content-Type: text/html; charset=UTF-8");
header_nocache();

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

$reference = new PluginOrderReference;

if (isset($_POST["action"])) {
   switch($_POST["action"]) {
      case "generation":
         echo "&nbsp;<input type='hidden' name='plugin_order_references_id' " .
               "  value='".$_POST["plugin_order_references_id"]."'>"; 
         echo"<input type='submit' name='generation' class='submit' " .
             "   value='".$LANG['buttons'][2]."'>"; 
         break;
      case "createLink":
         echo "&nbsp;<input type='hidden' name='itemtype' value='".$_POST["itemtype"]."'>";
         $reference->getFromDB($_POST["plugin_order_references_id"]);
         $reference->dropdownAllItemsByType("items_id", $_POST["itemtype"], 
                                            $_SESSION["glpiactive_entity"],
                                            $reference->fields["types_id"],
                                            $reference->fields["models_id"]);
         echo "&nbsp;<input type='submit' name='createLinkWithItem' " .
               "  class='submit' value='".$LANG['buttons'][2]."'>";
         break;
      case "deleteLink":
         echo "&nbsp;<input type='submit' name='deleteLinkWithItem' " .
               "  class='submit' value='".$LANG['buttons'][2]."'>";
         break;
   }
}

?>