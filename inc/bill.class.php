<?php
/*
 * @version $Id$
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

class PluginOrderBill extends CommonDropdown {

   public $dohistory         = true;
   public $first_level_menu  = "plugins";
   public $second_level_menu = "order";

   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_order']['bill'][0];
   }
   
   function canCreate() {
      return plugin_order_haveRight('bill', 'w');
   }

   function canView() {
      return plugin_order_haveRight('bill', 'r');
   }
   
   function post_getEmpty() {
      $this->fields['value'] = 0;
   }

   function prepareInputForAdd($input) {
      global $LANG;
      
      if (!isset ($input["number"]) || $input["number"] == '') {
         addMessageAfterRedirect($LANG['plugin_order']['bill'][3], false, ERROR);
         return array ();
      }

      return $input;
   }   

   function getAdditionalFields() {
      global $LANG;

      return array(array('name'  =>'suppliers_id',
                         'label' => $LANG['financial'][26],
                         'type'  => 'dropdownValue'),
                   array('name'  => 'value',
                         'label' => $LANG['financial'][21],
                         'type'  => 'text'),
                   array('name'  => 'number',
                         'label' => $LANG['financial'][4],
                         'type'  => 'text'),
                   array('name'  => 'billdate',
                         'label' => $LANG['common'][27],
                         'type'  => 'datetime'),
                   array('name'  => 'plugin_order_billtypes_id',
                         'label' => $LANG['common'][17],
                         'type'  => 'dropdownValue'),
                   array('name'  => 'plugin_order_billstates_id',
                         'label' => $LANG['joblist'][0],
                         'type'  => 'dropdownValue'),
                   array('name'  => 'users_id_validation',
                         'label' => $LANG['validation'][21],
                         'type'  => 'UserDropdown'),
                   array('name'  => 'validationdate',
                         'label' => $LANG['validation'][4],
                         'type'  => 'datetime'));
   }
   
   /**
    * Display more tabs
    *
    * @param $tab
   **/
   function displayMoreTabs($tab) {

      switch ($tab) {

         case 5 :
            Document::showAssociated($this);
            break;

         case 6 :
            self::showItems($this);
            break;

         case 10 :
            showNotesForm($_POST['target'], get_class($this), $_POST["id"]);
            break;
        
         case 12 :
            Log::showForItem($this);
            break;

         case -1 :
            Document::showAssociated($this);
            self::showItems($this);
            showNotesForm($_POST['target'], get_class($this), $_POST["id"]);
            Log::showForItem($this);
            break;
      }
   }
   
   function title() {
   }
   
   function defineMoreTabs($options=array()) {
      global $LANG;
      if ($this->fields['id'] > 0) {
         return array(5 => $LANG['Menu'][27], 6 => $LANG['plugin_order']['item'][0],
                      10 => $LANG['title'][37], 12 => $LANG['title'][38]);
      } else {
         return array();
      }
   }
   function getSearchOptions() {
      global $LANG;

      $tab = array();
    
      $tab['common'] = $LANG['plugin_order']['bill'][0];

      /* order_number */
      $tab[1]['table'] = $this->getTable();
      $tab[1]['field'] = 'number';
      $tab[1]['name'] = $LANG['financial'][4];
      $tab[1]['datatype'] = 'itemlink';

      $tab[2]['table'] = $this->getTable();
      $tab[2]['field'] = 'billdate';
      $tab[2]['name'] = $LANG['common'][27];
      $tab[2]['datatype'] = 'datetime';

      $tab[3]['table']    = $this->getTable();
      $tab[3]['field']    = 'validationdate';
      $tab[3]['name']     = $LANG['validation'][4];
      $tab[3]['datatype'] = 'datetime';

      $tab[4]['table']     = getTableForItemType('User');
      $tab[4]['field']     = 'name';
      $tab[4]['linkfield'] = 'users_id_validation';
      $tab[4]['name']      = $LANG['validation'][21];

      $tab[5]['table'] = getTableForItemType('PluginOrderBillType');
      $tab[5]['field'] = 'name';
      $tab[5]['name']  = $LANG['common'][17];
  
      $tab[6]['table'] = getTableForItemType('PluginOrderBillState');
      $tab[6]['field'] = 'name';
      $tab[6]['name']  = $LANG['joblist'][0];

      $tab[7]['table']         = getTableForItemType('Supplier');
      $tab[7]['field']         = 'name';
      $tab[7]['name']          = $LANG['financial'][26];
      $tab[7]['datatype']      = 'itemlink';
      $tab[7]['itemlink_type'] = 'Supplier';
      $tab[7]['forcegroupby']  = true;
  
      /* comments */
      $tab[16]['table']    = $this->getTable();
      $tab[16]['field']    = 'comment';
      $tab[16]['name']     = $LANG['plugin_order'][2];
      $tab[16]['datatype'] = 'text';

      /* ID */
      $tab[30]['table'] = $this->getTable();
      $tab[30]['field'] = 'id';
      $tab[30]['name']  = $LANG['common'][2];

      /* entity */
      $tab[80]['table'] = 'glpi_entities';
      $tab[80]['field'] = 'completename';
      $tab[80]['name'] = $LANG['entity'][0];

      $tab[86]['table']    = $this->getTable();
      $tab[86]['field']    = 'is_recursive';
      $tab[86]['name']     = $LANG['entity'][9];
      $tab[86]['datatype'] = 'bool';
      $tab[86]['massiveaction'] = false;

      return $tab;
   }

   static function showItems(PluginOrderBill $bill) {
      global $DB, $LANG;
      
      echo "<div class='spaced'><table class='tab_cadre_fixehov'>";
      echo "<tr><th>";
      printPagerForm();
      echo "</th><th colspan='5'>";
      echo $LANG['document'][19];
      echo "</th></tr>";
      
      $bills_id = $bill->getID();
      $query = "SELECT * FROM `".getTableForItemType("PluginOrderOrder_Item");
      $query.= "` WHERE `plugin_order_bills_id` = '$bills_id'";
      $query.= getEntitiesRestrictRequest(" AND", getTableForItemType("PluginOrderOrder_Item"), 
                                          "entities_id", $bill->getEntityID(), true);
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      
      if (!$number) {
         echo "</th><td>";
         echo $LANG['document'][19];
         echo "</td></tr>";
      } else {

         echo "<tr><th>".$LANG['common'][17]."</th>";
         echo "<th>".$LANG['entity'][0]."</th>";
         echo "<th>".$LANG['plugin_order'][7]."</th>";
         echo "<th>".$LANG['plugin_order']['detail'][2]."</th>";
         echo "<th>".$LANG['state'][0]."</th>";
         echo "<th>".$LANG['plugin_order']['generation'][9]."</th>";
         echo "</tr>";


         $num = 0;
         while ($data = $DB->fetch_array($result)) {
   
            if (!class_exists($data['itemtype'])) {
               continue;
            }
            $item = new $data['itemtype']();
            if ($item->canView()) {
               if ($number > $_SESSION['glpilist_limit']) {
                  echo "<tr class='tab_bg_1'>";
                  echo "<td class='center'>".$item->getTypeName()."&nbsp;:&nbsp;</td>";
                  echo "<td class='center' colspan='2'>";
                  echo "<a href='". $item->getSearchURL() . "?" .
                        rawurlencode("contains[0]") . "=" . rawurlencode('$$$$'.$bills_id) . "&" .
                        rawurlencode("field[0]") . "=50&sort=80&order=ASC&is_deleted=0&start=0". "'>" .
                        $LANG['reports'][57]."</a></td>";
                  echo "<td class='center'>-</td><td class='center'>-</td><td class='center'>-</td></tr>";
               } else {
                  $ID = "";
                  if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
                       $ID = " (".$data["id"].")";
                  }
                  $name = NOT_AVAILABLE;
                  if ($item->getFromDB($data["id"])) {
                       $name = $item->getLink();
                  }
                  echo "<tr class='tab_bg_1'>";
                  echo "<td class='center top'>".$item->getTypeName()."</td>";
                  $order = new PluginOrderOrder();
                  $order->getFromDB($data["plugin_order_orders_id"]);
                  echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities",
                                                                       $data["entities_id"]);
                  if ($order->canView()) {
                     echo "<td class='center'><a href='".$order->getLinkURL()."'>";
                     echo $order->getName()."</a></td>";
                  } else {
                     echo "<td class='center'>".$order->getName(true)."</td>";
                  }
                  $reference = new PluginOrderReference();
                  $reference->getFromDB($data["plugin_order_references_id"]);
                  if ($reference->canView()) {
                     echo "<td class='center'><a href='".$reference->getLinkURL()."'>";
                     echo $reference->getName()."</a></td>";
                  } else {
                     echo "<td class='center'>".$reference->getName(true)."</td>";
                  }
                  echo "</td></tr>";
                  echo "<td class='center'>".Dropdown::getDropdownName("glpi_plugin_order_deliverystates",
                                                                       $data["plugin_order_deliverystates_id"]);
               }
            }
         }
      }
      echo "</table></div>";
   }
   
   static function install(Migration $migration) {
      global $DB;
      
      $query ="CREATE TABLE IF NOT EXISTS `glpi_plugin_order_bills` (
           `id` int(11) NOT NULL AUTO_INCREMENT,
           `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
           `number` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
           `billdate` datetime DEFAULT NULL,
           `validationdate` datetime DEFAULT NULL,
           `comment` text COLLATE utf8_unicode_ci,
           `plugin_order_billstates_id` int(11) NOT NULL DEFAULT '0',
           `value` float NOT NULL DEFAULT '0',
           `plugin_order_billtypes_id` int(11) NOT NULL DEFAULT '0',
           `suppliers_id` int(11) NOT NULL DEFAULT '0',
           `plugin_order_orders_id` int(11) NOT NULL DEFAULT '0',
           `users_id_validation` int(11) NOT NULL DEFAULT '0',
           `entities_id` int(11) NOT NULL DEFAULT '0',
           `is_recursive` int(11) NOT NULL DEFAULT '0',
           `notepad` text COLLATE utf8_unicode_ci,
           PRIMARY KEY (`id`)
         ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;";
   $DB->query($query) or die ($DB->error());


   }
   
   static function uninstall() {
      global $DB;

      $table = getTableForItemType(__CLASS__);
      foreach (array ("glpi_displaypreferences", "glpi_documents_items", "glpi_bookmarks",
                       "glpi_logs") as $t) {
         $query = "DELETE FROM `$t` WHERE `itemtype`='".__CLASS__."'";
         $DB->query($query);
      }
      

      $DB->query("DROP TABLE IF EXISTS`".$table."`") or die ($DB->error());
   }
}
?>