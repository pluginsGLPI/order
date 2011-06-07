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

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginOrderSurveySupplier extends CommonDBChild {
   
   public $itemtype = 'PluginOrderOrder';
   public $items_id = 'plugin_order_orders_id';
   
   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_order']['survey'][0];
   }
   
   function canCreate() {
      return plugin_order_haveRight('order', 'w');
   }

   function canView() {
      return plugin_order_haveRight('order', 'r');
   }
   
   //function getSearchOptions()  ?
   
   function defineTabs($options=array()) {
      global $LANG;
      /* principal */
      $ong[1] = $LANG['title'][26];

      return $ong;
   }
   
   function prepareInputForAdd($input) {
      // Not attached to reference -> not added
      if (!isset($input['plugin_order_orders_id']) || $input['plugin_order_orders_id'] <= 0) {
         return false;
      }
      return $input;
   }
   
   function getFromDBByOrder($plugin_order_orders_id) {
      global $DB;
      
      $query = "SELECT * FROM `".$this->getTable()."`
               WHERE `plugin_order_orders_id` = '" . $plugin_order_orders_id . "' ";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         } else {
            return false;
         }
      }
      return false;
   }
   
   function addNotation($field,$value) {
      global $LANG;
      
      echo "<font size='1'>".$LANG['plugin_order']['survey'][7]."</font>&nbsp;";
      
      for ($i=10 ; $i >= 1 ; $i--) {
         echo "&nbsp;".$i."&nbsp;<input type='radio' name='".$field."' value='".$i."' ";
         if ($i == $value)
         echo " checked ";
         echo ">";
      }
      
      echo "&nbsp;<font size='1'>".$LANG['plugin_order']['survey'][6]."</font>";
   }
   
   function getTotalNotation($plugin_order_orders_id) {
      global $DB;
      
      $query = "SELECT (`answer1` + `answer2` + `answer3` + `answer4` + `answer5`) AS total FROM `".
                  $this->getTable()."` " .
            "WHERE `plugin_order_orders_id` = '".$plugin_order_orders_id."' ";
      $result = $DB->query($query);
      $nb = $DB->numrows($result);
      if ($nb)
         return $DB->result($result,0,"total")/5;
      else
         return 0;
   }
   
   function getNotation($suppliers_id,$field) {
      global $DB;
      
      $query = "SELECT SUM(`".$this->getTable()."`.`".$field."`) AS total, COUNT(`".$this->getTable()."`.`id`) AS nb 
               FROM `glpi_plugin_order_orders`,`".$this->getTable()."`
               WHERE `".$this->getTable()."`.`suppliers_id` = `glpi_plugin_order_orders`.`suppliers_id` 
               AND `".$this->getTable()."`.`plugin_order_orders_id` = `glpi_plugin_order_orders`.`id` 
               AND `glpi_plugin_order_orders`.`suppliers_id` = '".$suppliers_id."'"
               .getEntitiesRestrictRequest(" AND ","glpi_plugin_order_orders","entities_id",'',true);
      $result = $DB->query($query);
      $nb = $DB->numrows($result);
      if ($nb)
         return $DB->result($result,0,"total")/$DB->result($result,0,"nb");
      else
         return 0;
   }
   
   function showGlobalNotation($suppliers_id) {
      global $LANG,$DB;
      
      $query = "SELECT `glpi_plugin_order_orders`.`id`, `glpi_plugin_order_orders`.`entities_id`, `glpi_plugin_order_orders`.`name`,`".$this->getTable()."`.`comment` 
                  FROM `glpi_plugin_order_orders`,`".$this->getTable()."`
                  WHERE `".$this->getTable()."`.`suppliers_id` = `glpi_plugin_order_orders`.`suppliers_id` 
                  AND `".$this->getTable()."`.`plugin_order_orders_id` = `glpi_plugin_order_orders`.`id` 
                  AND `glpi_plugin_order_orders`.`suppliers_id` = '".$suppliers_id."'"
                  .getEntitiesRestrictRequest(" AND ","glpi_plugin_order_orders","entities_id",'',true);
      $query.= " GROUP BY `".$this->table."`.`id`";
      $result = $DB->query($query);
      $nb = $DB->numrows($result);
      $total = 0;
      $nb_order = 0;
      
      echo "<br><div class='center'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      echo "<th colspan='4'>" . $LANG['plugin_order']['survey'][0]. "</th>";
      echo "</tr>";
      echo "<tr>";
      echo "<th>".$LANG['entity'][0]."</th>";
      echo "<th>" . $LANG['plugin_order'][39]. "</th>";
      echo "<th>" . $LANG['plugin_order']['survey'][10]."</th>";
      echo "<th>" . $LANG['plugin_order']['survey'][11]."</th>";
      echo "</tr>";
      
      if ($nb) {
         for ($i=0 ; $i <$nb ; $i++) {
            $name = $DB->result($result,$i,"name");
            $ID = $DB->result($result,$i,"id");
            $comment = $DB->result($result,$i,"comment");
            $entities_id = $DB->result($result,$i,"entities_id");
            $note = $this->getTotalNotation($ID);
            echo "<tr class='tab_bg_1'>";
            echo "<td>";
            echo Dropdown::getDropdownName("glpi_entities",$entities_id);
            echo "</td>";
            $link=getItemTypeFormURL('PluginOrderOrder');
            echo "<td><a href=\"" . $link . "?id=" . $ID . "\">" . $name . "</a></td>";
            echo "<td>" . $note." / 10"."</td>";
            echo "<td>" . nl2br($comment)."</td>";
            echo "</tr>";
            $total+= $this->getTotalNotation($ID);
            $nb_order++;
         }
         echo "<tr>";
         echo "<th colspan='4'>&nbsp;</th>";
         echo "</tr>";
         
         for ($i=1 ; $i <= 5 ; $i++) {
            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='2'></td>";
            echo "<td><div align='left'>" . $LANG['plugin_order']['survey'][$i]. "</div></td>";
            echo "<td><div align='left'>" . 
               formatNumber($this->getNotation($suppliers_id,"answer$i"))."&nbsp;/ 10</div></td>";
            echo "</tr>";
         }
         
         echo "<tr>";
         echo "<th colspan='4'>&nbsp;</th>";
         echo "</tr>";
         
         echo "<tr class='tab_bg_1 b'>";
         echo "<td colspan='2'></td>";
         echo "<td><div align='left'>" . $LANG['plugin_order']['survey'][9]. "</div></td>";
         echo "<td><div align='left'>" . formatNumber($total/$nb_order)."&nbsp;/ 10</div></td>";
         echo "</tr>";
      }
      echo "</table>";
      echo "</div>";
   }
   
   function showForm ($ID, $options=array()) {
      global $LANG;
      
      if (!$this->canView())
         return false;
      
      $plugin_order_orders_id = -1;
      if (isset($options['plugin_order_orders_id'])) {
         $plugin_order_orders_id = $options['plugin_order_orders_id'];
      }
      
      if ($ID > 0) {
         $this->check($ID,'r');
      } else {
         // Create item
         $input=array('plugin_order_orders_id' => $options['plugin_order_orders_id']);
         $this->check(-1,'w',$input);
      }
      
      if (strpos($_SERVER['PHP_SELF'],"surveysupplier")) {
         $this->showTabs($options);
      }
      $options['colspan'] = 1;
      $this->showFormHeader($options);
      
      $PluginOrderOrder = new PluginOrderOrder();
      $PluginOrderOrder->getFromDB($plugin_order_orders_id);
      echo "<input type='hidden' name='plugin_order_orders_id' value='$plugin_order_orders_id'>";
      echo "<input type='hidden' name='entities_id' value='".$PluginOrderOrder->getEntityID()."'>";
      echo "<input type='hidden' name='is_recursive' value='".$PluginOrderOrder->isRecursive()."'>";
      
      echo "<tr class='tab_bg_1'><td>" . $LANG['financial'][26] . ": </td><td>";
      $suppliers_id = $PluginOrderOrder->fields["suppliers_id"];
      if ($ID > 0)
         $suppliers_id = $this->fields["suppliers_id"];
      $link=getItemTypeFormURL('Supplier');
      echo "<a href=\"" . $link . "?id=" . $suppliers_id . "\">" . 
         Dropdown::getDropdownName("glpi_suppliers", $suppliers_id) . "</a>";
      echo "<input type='hidden' name='suppliers_id' value='".$suppliers_id."'>";
      echo "</td>";
      echo "</tr>";
      
      for ($i=1 ; $i <= 5 ; $i++) {
         echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_order']['survey'][$i] . ": </td><td>";
         $this->addNotation("answer$i",$this->fields["answer$i"]);
         echo "</td>";
         echo "</tr>";
      }
      
      echo "<tr class='tab_bg_1'><td>";
      //comments of order
      echo $LANG['common'][25] . ": </td>";
      echo "<td>";
      echo "<textarea cols='80' rows='4' name='comment'>" . $this->fields["comment"] . "</textarea>";
      echo "</td>";
      echo "</tr>";
      
      if ($ID>0) {
         echo "<tr><th><div align='left'>" . $LANG['plugin_order']['survey'][8] . 
               ": </div></th><th><div align='left'>";
         $total = $this->getTotalNotation($this->fields["plugin_order_orders_id"]);
         echo formatNumber($total)." / 10";
         echo "</div></th>";
         echo "</tr>";
      }
      
      $this->showFormButtons($options);
      
      if (strpos($_SERVER['PHP_SELF'],"surveysupplier")) {
         $this->addDivForTabs();
      }  
      return true;
   }
   
   function showOrderSupplierSurvey($target, $ID) {
      global $LANG, $DB, $CFG_GLPI;

      $order = new PluginOrderOrder;
      $order->getFromDB($ID);

      initNavigateListItems($this->getType(),$LANG['plugin_order'][7] ." = ". $order->fields["name"]);

      $candelete =$order->can($ID,'w');
      $query = "SELECT * FROM `".$this->getTable()."` WHERE `plugin_order_orders_id` = '$ID' ";
      $result = $DB->query($query);
      $rand=mt_rand();
      echo "<div class='center'>";
      echo "<form method='post' name='show_suppliersurvey$rand' id='show_suppliersurvey$rand' " .
            " action=\"$target\">";
      echo "<input type='hidden' name='plugin_order_orders_id' value='" . $ID . "'>";
      echo "<table class='tab_cadre_fixe'>";
      
      echo "<tr><th colspan='5'>".$LANG['plugin_order']['survey'][0]."</th></tr>";
      echo "<tr><th>&nbsp;</th>";
      echo "<th>" . $LANG['financial'][26] . "</th>";
      echo "<th>" . $LANG['plugin_order']['survey'][10] . "</th>";
      echo "<th>" . $LANG['plugin_order']['survey'][11] . "</th>";
      echo "</tr>";

      if ($DB->numrows($result) > 0) {

         while ($data = $DB->fetch_array($result)) {
            addToNavigateListItems($this->getType(),$data['id']);
            echo "<input type='hidden' name='item[" . $data["id"] . "]' value='" . $ID . "'>";
            echo "<tr class='tab_bg_1 center'>";
            echo "<td>";
            if ($candelete) {
               echo "<input type='checkbox' name='check[" . $data["id"] . "]'";
               if (isset($_POST['check']) && $_POST['check'] == 'all')
                  echo " checked ";
               echo ">";
            }
            echo "</td>";
            $link=getItemTypeFormURL($this->getType());
            echo "<td><a href='".$link."?id=".$data["id"]."&plugin_order_orders_id=".$ID."'>" .
               Dropdown::getDropdownName("glpi_suppliers", $data["suppliers_id"]) . "</a></td>";
            echo "<td>";
            $total = $this->getTotalNotation($ID);
            echo $total." / 10";
            echo "</td>";
            echo "<td>";
            echo $data["comment"];
            echo "</td>";
            echo "</tr>";
         }
         echo "</table>";

         if ($candelete)
         {
            echo "<div class='center'>";
            echo "<table width='900px' class='tab_glpi'>";
            echo "<tr><td><img src=\"".$CFG_GLPI["root_doc"]."/pics/arrow-left.png\" alt=''></td><td class='center'><a onclick= \"if ( markCheckboxes('show_suppliersurvey$rand') ) return false;\" href='#'>".$LANG['buttons'][18]."</a></td>";

            echo "<td>/</td><td class='center'><a onclick= \"if ( unMarkCheckboxes('show_suppliersurvey$rand') ) return false;\" href='#'>".$LANG['buttons'][19]."</a>";
            echo "</td><td align='left' width='80%'>";
            echo "<input type='submit' name='delete' value=\"" . $LANG['buttons'][6] . "\" class='submit' >";
            echo "</td>";
            echo "</table>";
            echo "</div>";
         }
      }
      else
         echo "</table>";

      echo "</form>";
      echo "</div>";
   }
   
   function checkIfSupplierSurveyExists($plugin_order_orders_id) {
      
      if ($plugin_order_orders_id) {
         $devices = getAllDatasFromTable($this->getTable(), 
                                         "`plugin_order_orders_id` = '$plugin_order_orders_id' ");
         if (!empty($devices))
            return true;
         else
            return false;
      }
   }
}

?>