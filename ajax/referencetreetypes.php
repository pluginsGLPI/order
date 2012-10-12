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

$AJAX_INCLUDE=1;

define('GLPI_ROOT','../../..');
include (GLPI_ROOT."/inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (isset($_REQUEST['node'])) {
  /* if ($_SESSION['glpiactiveprofile']['interface']=='helpdesk') {
      $target="helpdesk.public.php";
   } else {*/
      $target="reference.php";
   //}

   $nodes=array();
   // Root node
   if ($_REQUEST['node']== -1) {
      $pos=0;
      $entity = $_SESSION['glpiactive_entity'];
      
      $where=" WHERE `glpi_plugin_order_references`.`is_deleted` = '0' ";
      $where.=getEntitiesRestrictRequest("AND","glpi_plugin_order_references");
      
      $query="SELECT DISTINCT `itemtype`
      FROM `glpi_plugin_order_references`
      $where
      GROUP BY `itemtype`
      ORDER BY `itemtype` ";
      if ($result=$DB->query($query)) {
         if ($DB->numrows($result)) {
            $pos=0;
            while ($row = $DB->fetch_array($result)) {
               $class = $row['itemtype'];
               $item = new $class();
               $path['text'] = $item->getTypeName();
               //$path['id'] = $ID;
               $path['position'] = $pos;
               $pos++;
               $path['draggable'] = false;

               if($entity==0) {
                  $link="&link[1]=AND&searchtype[1]=contains&contains[1]=NULL&field[1]=80";
               } else {
                  $link="&link[1]=AND&searchtype[1]=contains&contains[1]=".
                     Dropdown::getDropdownName("glpi_entities",$entity)."&field[1]=80";
               }

               $path['href'] = $CFG_GLPI["root_doc"].
                  "/plugins/order/front/$target?is_deleted=0&field[0]=3&searchtype[0]=equals&contains[0]=".
                        rawurlencode($class)."&$link&itemtype=PluginOrderReference&start=0";
               // Check if node is a leaf or a folder.
               $path['leaf'] = true;
               $path['cls'] = 'file';
               
               $nodes[] = $path;
            }
         }
      }
   }
   
   print json_encode($nodes);
}

?>