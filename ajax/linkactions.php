<?php
/*
 * @version $Id: bill.tabs.php 530 2011-06-30 11:30:17Z walid $
 LICENSE

 This file is part of the order plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Order. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   order
 @author    the order plugin team
 @copyright Copyright (c) 2010-2011 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT."/inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

$reference = new PluginOrderReference();

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