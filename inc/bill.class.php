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

      return array(array('name'  => 'value',
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

         case 10 :
            showNotesForm($_POST['target'], get_class($this), $_POST["id"]);
            break;
        
         case 12 :
            Log::showForItem($this);
            break;

         case -1 :
            Document::showAssociated($this);
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

   /**
   * Print the HTML array of Items on a budget
   *
   *@return Nothing (display)
   **/
   function showItems() {
      global $DB, $LANG;

      $budgets_id = $this->fields['id'];

      if (!$this->can($budgets_id,'r')) {
         return false;
      }

      $query = "SELECT DISTINCT `itemtype`
                FROM `glpi_infocoms`
                WHERE `budgets_id` = '$budgets_id'
                      AND itemtype NOT IN ('ConsumableItem', 'CartridgeItem', 'Software')
               ORDER BY `itemtype`";

      $result = $DB->query($query);
      $number = $DB->numrows($result);

      echo "<div class='spaced'><table class='tab_cadre_fixehov'>";
      echo "<tr><th colspan='2'>";
      printPagerForm();
      echo "</th><th colspan='4'>";
      if ($DB->numrows($result)==0) {
         echo $LANG['document'][13];
      } else {
         echo $LANG['document'][19];
      }
      echo "</th></tr>";

      echo "<tr><th>".$LANG['common'][17]."</th>";
      echo "<th>".$LANG['entity'][0]."</th>";
      echo "<th>".$LANG['common'][16]."</th>";
      echo "<th>".$LANG['common'][19]."</th>";
      echo "<th>".$LANG['common'][20]."</th>";
      echo "<th>".$LANG['financial'][21]."</th>";
      echo "</tr>";

      $num = 0;
      for ($i = 0; $i < $number ; $i++) {
         $itemtype = $DB->result($result, $i, "itemtype");

         if (!class_exists($itemtype)) {
            continue;
         }
         $item = new $itemtype();
         if ($item->canView()) {
            switch ($itemtype) {
               default :
                  $query = "SELECT `".$item->getTable()."`.*,
                                   `glpi_infocoms`.`value`
                            FROM `glpi_infocoms`
                            INNER JOIN `".$item->getTable()."`
                                 ON (`".$item->getTable()."`.`id` = `glpi_infocoms`.`items_id`)
                            WHERE `glpi_infocoms`.`itemtype` = '$itemtype'
                                  AND `glpi_infocoms`.`budgets_id` = '$budgets_id' ".
                                  getEntitiesRestrictRequest(" AND", $item->getTable())."
                            ORDER BY `entities_id`,
                                     `".$item->getTable()."`.`name`";
               break;

               case 'Cartridge':
                  $query = "SELECT `".$item->getTable()."`.*,
                                   `glpi_cartridgeitems`.`name`
                            FROM `glpi_infocoms`
                            INNER JOIN `".$item->getTable()."`
                                 ON (`".$item->getTable()."`.`id` = `glpi_infocoms`.`items_id`)
                            INNER JOIN `glpi_cartridgeitems`
                                 ON (`".$item->getTable()."`.`cartridgeitems_id` = `glpi_cartridgeitems`.`id`)
                            WHERE `glpi_infocoms`.`itemtype`='$itemtype'
                                  AND `glpi_infocoms`.`budgets_id` = '$budgets_id' ".
                                  getEntitiesRestrictRequest(" AND", $item->getTable())."
                            ORDER BY `entities_id`,
                                     `glpi_cartridgeitems`.`name`";
               break;

               case 'Consumable':
                  $query = "SELECT `".$item->getTable()."`.*,
                                   `glpi_consumableitems`.`name`
                            FROM `glpi_infocoms`
                            INNER JOIN `".$item->getTable()."`
                                 ON (`".$item->getTable()."`.`id` = `glpi_infocoms`.`items_id`)
                            INNER JOIN `glpi_consumableitems`
                                 ON (`".$item->getTable()."`.`consumableitems_id` = `glpi_consumableitems`.`id`)
                            WHERE `glpi_infocoms`.`itemtype` = '$itemtype'
                                  AND `glpi_infocoms`.`budgets_id` = '$budgets_id' ".
                                  getEntitiesRestrictRequest(" AND", $item->getTable())."
                            ORDER BY `entities_id`,
                                     `glpi_consumableitems`.`name`";
               break;
            }

            if ($result_linked=$DB->query($query)) {
               $nb = $DB->numrows($result_linked);

               if ($nb>$_SESSION['glpilist_limit']) {
                  echo "<tr class='tab_bg_1'>";
                  echo "<td class='center'>".$item->getTypeName($nb)."&nbsp;:&nbsp;$nb</td>";
                  echo "<td class='center' colspan='2'>";
                  echo "<a href='". $item->getSearchURL() . "?" .
                        rawurlencode("contains[0]") . "=" . rawurlencode('$$$$'.$budgets_id) . "&" .
                        rawurlencode("field[0]") . "=50&sort=80&order=ASC&is_deleted=0&start=0". "'>" .
                        $LANG['reports'][57]."</a></td>";
                  echo "<td class='center'>-</td><td class='center'>-</td><td class='center'>-</td></tr>";

               } else if ($nb) {
                  for ($prem=true ; $data=$DB->fetch_assoc($result_linked) ; $prem=false) {
                     $ID = "";
                     if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
                        $ID = " (".$data["id"].")";
                     }
                     $name = NOT_AVAILABLE;
                     if ($item->getFromDB($data["id"])) {
                        $name = $item->getLink();
                     }
                     echo "<tr class='tab_bg_1'>";
                     if ($prem) {
                        echo "<td class='center top' rowspan='$nb'>".$item->getTypeName($nb)
                              .($nb>1?"&nbsp;:&nbsp;$nb</td>":"</td>");
                     }
                     echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities",
                                                                          $data["entities_id"]);
                     echo "</td><td class='center";
                     echo (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                     echo ">".$name."</td>";
                     echo "<td class='center'>".(isset($data["serial"])? "".$data["serial"]."" :"-");
                     echo "</td>";
                     echo "<td class='center'>".
                            (isset($data["otherserial"])? "".$data["otherserial"]."" :"-")."</td>";
                     echo "<td class='center'>".
                            (isset($data["value"])? "".formatNumber($data["value"],true)."" :"-");

                     echo "</td></tr>";
                  }
               }
            $num += $nb;
            }
         }
      }
      if ($num>0) {
         echo "<tr class='tab_bg_2'><td class='center b'>".$LANG['common'][33]."&nbsp;:&nbsp;$num</td><td colspan='5'>&nbsp;</td></tr> ";
      }
      echo "</table></div>";
   }   
}
?>