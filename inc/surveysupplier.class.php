<?php
/*
 * @version $Id: HEADER 2011-03-23 15:41:26 tsmr $
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
      
      $query  = "SELECT (`answer1` + `answer2` + `answer3` + `answer4` + `answer5`) AS total
                 FROM `".$this->getTable()."` " .
                "WHERE `plugin_order_orders_id` = '".$plugin_order_orders_id."' ";
      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         return $DB->result($result, 0, "total") /5;
      } else {
         return 0;
      }
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
      if ($nb) {
         return $DB->result($result,0,"total")/$DB->result($result,0,"nb");
      } else {
         return 0;
      }
   }
   
   static function showGlobalNotation($suppliers_id) {
      global $LANG,$DB;
      
      $config = PluginOrderConfig::getConfig();
      if (!$config->canUseSupplierSatisfaction()) {
         return;
      }
      
      $survey = new self();
      $query  = "SELECT `glpi_plugin_order_orders`.`id`,
                `glpi_plugin_order_orders`.`entities_id`, `glpi_plugin_order_orders`.`name`,
                `".$survey->getTable()."`.`comment`
                 FROM `glpi_plugin_order_orders`,`".$survey->getTable()."`
                 WHERE `".$survey->getTable()."`.`suppliers_id` = `glpi_plugin_order_orders`.`suppliers_id`
                    AND `".$survey->getTable()."`.`plugin_order_orders_id` = `glpi_plugin_order_orders`.`id`
                     AND `glpi_plugin_order_orders`.`suppliers_id` = '".$suppliers_id."'"
                        .getEntitiesRestrictRequest(" AND ","glpi_plugin_order_orders",
                                                    "entities_id",'',true);
      $query   .= " GROUP BY `".$survey->getTable()."`.`id`";
      $result   = $DB->query($query);
      $nb       = $DB->numrows($result);
      $total    = 0;
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
            $name        = $DB->result($result,$i,"name");
            $ID          = $DB->result($result,$i,"id");
            $comment     = $DB->result($result,$i,"comment");
            $entities_id = $DB->result($result,$i,"entities_id");
            $note        = $survey->getTotalNotation($ID);
            echo "<tr class='tab_bg_1'>";
            echo "<td>";
            echo Dropdown::getDropdownName("glpi_entities",$entities_id);
            echo "</td>";
            $link = Toolbox::getItemTypeFormURL('PluginOrderOrder');
            echo "<td><a href=\"" . $link . "?id=" . $ID . "\">" . $name . "</a></td>";
            echo "<td>" . $note." / 10"."</td>";
            echo "<td>" . nl2br($comment)."</td>";
            echo "</tr>";
            $total+= $survey->getTotalNotation($ID);
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
               Html::formatNumber($survey->getNotation($suppliers_id, "answer$i")).
                  "&nbsp;/ 10</div></td>";
            echo "</tr>";
         }
         
         echo "<tr>";
         echo "<th colspan='4'>&nbsp;</th>";
         echo "</tr>";
         
         echo "<tr class='tab_bg_1 b'>";
         echo "<td colspan='2'></td>";
         echo "<td><div align='left'>" . $LANG['plugin_order']['survey'][9]. "</div></td>";
         echo "<td><div align='left'>" . Html::formatNumber($total/$nb_order)."&nbsp;/ 10</div></td>";
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
      
      $order = new PluginOrderOrder();
      $order->getFromDB($plugin_order_orders_id);
      echo "<input type='hidden' name='plugin_order_orders_id' value='$plugin_order_orders_id'>";
      echo "<input type='hidden' name='entities_id' value='".$order->getEntityID()."'>";
      echo "<input type='hidden' name='is_recursive' value='".$order->isRecursive()."'>";
      
      echo "<tr class='tab_bg_1'><td>" . $LANG['financial'][26] . ": </td><td>";
      $suppliers_id = $order->fields["suppliers_id"];
      if ($ID > 0) {
         $suppliers_id = $this->fields["suppliers_id"];
      }
      $link=Toolbox::getItemTypeFormURL('Supplier');
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
         echo Html::formatNumber($total)." / 10";
         echo "</div></th>";
         echo "</tr>";
      }
      
      $this->showFormButtons($options);
      
      if (strpos($_SERVER['PHP_SELF'], "surveysupplier")) {
         $this->addDivForTabs();
      }
      return true;
   }
   
   static function showOrderSupplierSurvey($ID) {
      global $LANG, $DB, $CFG_GLPI;

      $order = new PluginOrderOrder;
      $order->getFromDB($ID);

      $survey = new self();
      
      $table = getTableForItemType(__CLASS__);
      Session::initNavigateListItems(__CLASS__,
                                     $LANG['plugin_order'][7] ." = ". $order->fields["name"]);

      $candelete = $order->can($ID,'w');
      $query     = "SELECT * FROM `$table` WHERE `plugin_order_orders_id` = '$ID' ";
      $result    = $DB->query($query);
      $rand      = mt_rand();
      echo "<div class='center'>";
      echo "<form method='post' name='show_suppliersurvey$rand' id='show_suppliersurvey$rand' " .
            " action=\"".Toolbox::getItemTypeFormURL(__CLASS__)."\">";
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
            Session::addToNavigateListItems(__CLASS__,$data['id']);
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
            $link=Toolbox::getItemTypeFormURL(__CLASS__);
            echo "<td><a href='".$link."?id=".$data["id"]."&plugin_order_orders_id=".$ID."'>" .
               Dropdown::getDropdownName("glpi_suppliers", $data["suppliers_id"]) . "</a></td>";
            echo "<td>";
            $total = $survey->getTotalNotation($ID);
            echo $total." / 10";
            echo "</td>";
            echo "<td>";
            echo $data["comment"];
            echo "</td>";
            echo "</tr>";
         }
         echo "</table>";

         if ($candelete) {
            echo "<div class='center'>";
            Html::openArrowMassives("show_suppliersurvey$rand");
            Html::closeArrowMassives(array("delete" => $LANG['buttons'][6]));
            echo "</div>";
         }
      } else {
         echo "</table>";
      }

      Html::closeForm();
      echo "</div>";
   }
   
   function checkIfSupplierSurveyExists($orders_id) {
      if ($orders_id) {
         return (countElementsInTable(getTableForItemType(__CLASS__),
                                         "`plugin_order_orders_id` = '$orders_id' "));
      } else {
         return false;
      }
   }
   
  static function install(Migration $migration) {
      global $DB;
      //Only avaiable since 1.3.0
      
      $table = getTableForItemType(__CLASS__);
      if (!TableExists("glpi_plugin_order_surveysuppliers")) {
         $migration->displayMessage("Installing $table");

         //Installation
         $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_order_surveysuppliers` (
                  `id` int(11) NOT NULL auto_increment,
                  `entities_id` int(11) NOT NULL default '0',
                  `is_recursive` tinyint(1) NOT NULL default '0',
                  `plugin_order_orders_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orders (id)',
                  `suppliers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)',
                  `answer1` int(11) NOT NULL default 0,
                  `answer2` int(11) NOT NULL default 0,
                  `answer3` int(11) NOT NULL default 0,
                  `answer4` int(11) NOT NULL default 0,
                  `answer5` int(11) NOT NULL default 0,
                  `comment` text collate utf8_unicode_ci,
                  PRIMARY KEY  (`id`),
                  KEY `plugin_order_orders_id` (`plugin_order_orders_id`),
                  KEY `entities_id` (`entities_id`),
                  KEY `suppliers_id` (`suppliers_id`)
               ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die($DB->error());
      } else {
         //upgrade
         $migration->displayMessage("Upgrading $table");
         
         //1.2.0
         $migration->changeField($table, "ID", "id", "int(11) NOT NULL auto_increment");
         $migration->changeField($table, "FK_order", "plugin_order_orders_id",
                                 "int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_order_orders (id)'");
         $migration->changeField($table, "FK_enterprise", "suppliers_id",
                                 "int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)'");
         $migration->changeField($table, "comment", "comment",
                                 "text collate utf8_unicode_ci");
         $migration->addField($table, "entities_id", "int(11) NOT NULL default '0'");
         $migration->addField($table, "is_recursive", "tinyint(1) NOT NULL default '0'");
         $migration->addKey($table, "plugin_order_orders_id");
         $migration->addKey($table, "suppliers_id");
         $migration->migrationOneTable($table);
         
         $query = "SELECT `suppliers_id`, `entities_id`,`is_recursive`,`id`
                   FROM `glpi_plugin_order_orders` ";
         foreach ($DB->request($query) as $data) {
            $query = "UPDATE `glpi_plugin_order_surveysuppliers`
                      SET `entities_id` = '".$data["entities_id"]."',
                          `is_recursive` = '".$data["is_recursive"]."'
                      WHERE `plugin_order_orders_id` = '".$data["id"]."' ";
            $DB->query($query) or die($DB->error());
         }
      }
   }
   
   static function uninstall() {
      global $DB;
      
      //Current table name
      $DB->query("DROP TABLE IF EXISTS  `".getTableForItemType(__CLASS__)."`") or die ($DB->error());
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;
      
      if (get_class($item) == 'PluginOrderOrder') {
         $config = PluginOrderConfig::getConfig();
         if ($config->canUseSupplierSatisfaction()
            && $item->getState() == PluginOrderOrderState::DELIVERED) {
            return array(1 => $LANG['plugin_order'][10]);
         }
      }
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      if ($item->getType() == 'PluginOrderOrder') {
         $survey = new self();
         self::showOrderSupplierSurvey($item->getID());
         if (!$survey->checkIfSupplierSurveyExists($item->getID())
             && $item->can($item->getID(), 'w')) {
            $survey->showForm("",  array('plugin_order_orders_id' => $item->getID()));
         }
      }
      
      return true;
   }
   
}

?>