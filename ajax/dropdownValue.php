<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Resources plugin for GLPI
 Copyright (C) 2003-2011 by the Resources Development Team.

 https://forge.indepnet.net/projects/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Resources.

 Resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (strpos($_SERVER['PHP_SELF'],"dropdownValue.php")) {
   define('GLPI_ROOT','../../..');
   include (GLPI_ROOT."/inc/includes.php");
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

Session::checkLoginUser();

$table = $_POST['table'];

$displaywith = false;

if (isset($_POST['displaywith'])
    && is_array($_POST['displaywith'])
    && count($_POST['displaywith'])) {

   $displaywith = true;
}

// No define value
if (!isset($_POST['value'])) {
   $_POST['value'] = '';
}

// No define rand
if (!isset($_POST['rand'])) {
   $_POST['rand'] = mt_rand();
}

if (isset($_POST['condition']) && !empty($_POST['condition'])) {
   $_POST['condition'] = rawurldecode(stripslashes($_POST['condition']));
}

if (!isset($_POST['emptylabel']) || $_POST['emptylabel'] == '') {
   $_POST['emptylabel'] = Dropdown::EMPTY_VALUE;
}

if (!isset($_POST['display_rootentity'])) {
   $_POST['display_rootentity'] = false;
}

if (isset($_POST["entity_restrict"])
    && !is_numeric($_POST["entity_restrict"])
    && !is_array($_POST["entity_restrict"])) {

   $_POST["entity_restrict"] = unserialize(stripslashes($_POST["entity_restrict"]));
}

// Make a select box with preselected values
if (!isset($_POST["limit"])) {
   $_POST["limit"] = $_SESSION["glpidropdown_chars_limit"];
}

$NBMAX = $CFG_GLPI["dropdown_max"];
$LIMIT = "LIMIT 0,$NBMAX";


if ($_POST['searchText']==$CFG_GLPI["ajax_wildcard"]) {
   $LIMIT = "";
}

/*  
$where = "WHERE 1 ";

if ($item->maybeDeleted()) {
   $where .= " AND `is_deleted` = '0' ";
}
if ($item->maybeTemplate()) {
   $where .= " AND `is_template` = '0' ";
}

$where .=" AND `$table`.`id` NOT IN ('".$_POST['value']."'";

if (isset($_POST['used'])) {

   if (is_array($_POST['used'])) {
      $used = $_POST['used'];
   } else {
      $used = unserialize(stripslashes($_POST['used']));
   }

   if (count($used)) {
      $where .= ",'".implode("','",$used)."'";
   }
}

if (isset($_POST['toadd'])) {
   if (is_array($_POST['toadd'])) {
      $toadd = $_POST['toadd'];
   } else {
      $toadd = unserialize(stripslashes($_POST['toadd']));
   }
} else {
   $toadd = array();
}

$where .= ") ";

if (isset($_POST['condition']) && $_POST['condition'] != '') {
   $where .= " AND ".$_POST['condition']." ";
}
$multi = false;

if ($item->isEntityAssign()) {
   $multi = $item->maybeRecursive();

   if (isset($_POST["entity_restrict"]) && !($_POST["entity_restrict"]<0)) {
      $where .= getEntitiesRestrictRequest("AND", $table, "entities_id",
                                           $_POST["entity_restrict"], $multi);

      if (is_array($_POST["entity_restrict"]) && count($_POST["entity_restrict"])>1) {
         $multi = true;
      }

   } else {
      $where .= getEntitiesRestrictRequest("AND", $table, '', '', $multi);

      if (count($_SESSION['glpiactiveentities'])>1) {
         $multi = true;
      }
   }
}

$field = "name";

if ($_POST['searchText']!=$CFG_GLPI["ajax_wildcard"]) {
   $search = Search::makeTextSearch($_POST['searchText']);
   $where .=" AND  (`$table`.`$field` ".$search;
   $where .= ')';
}

switch ($_POST['itemtype']) {

   default :
      $query = "SELECT *
                FROM `$table`
                $where";
}

if ($multi) {
   $query .= " ORDER BY `entities_id`, $field
              $LIMIT";
} else {
   $query .= " ORDER BY $field
              $LIMIT";
}*/


echo "<select id='dropdown_".$_POST["myname"].$_POST["rand"]."' name='".$_POST['myname']."'
          size='1'";

if (isset($_POST["on_change"]) && !empty($_POST["on_change"])) {
   echo " onChange='".$_POST["on_change"]."'";
}

echo ">";



$query = "SELECT `gr`.`name`, `gr`.`id`, `grm`.`reference_code`
                FROM `".$table."` AS gr, `glpi_plugin_order_references_suppliers` AS grm
                WHERE `gr`.`itemtype` = '".$_POST["itemtype"]."'
                   AND `grm`.`suppliers_id` = '".$_POST["suppliers_id"]."'
                     AND `grm`.`plugin_order_references_id` = `gr`.`id` AND `gr`.`is_active`='1'";

if ($_POST['searchText']!=$CFG_GLPI["ajax_wildcard"]) {
   $search = Search::makeTextSearch($_POST['searchText']);
   $query .=" AND  `gr`.`name` ".$search;
}

$query .= "ORDER BY `gr`.`name` ASC";

if ($result = $DB->query($query)) {

   if ($_POST['searchText']!=$CFG_GLPI["ajax_wildcard"] && $DB->numrows($result)==$NBMAX) {
      echo "<option value='0'>--".$LANG['common'][11]."--</option>";

   } else if (!isset($_POST['display_emptychoice']) || $_POST['display_emptychoice']) {
      echo "<option value='0'>".$_POST["emptylabel"]."</option>";
   }

   foreach ($DB->request($query) as $data) {
      
      echo "<option value='".$data["id"]."'>".$data['name'];
      if ($data['reference_code']) {
         echo " (".$data['reference_code'].")";
      }
      echo "</option>\n";
   }
}
echo "</select>";

/*
if ($result = $DB->query($query)) {

   if (count($toadd)) {
      foreach ($toadd as $key => $val) {
         echo "<option title=\"".Html::cleanInputText($val)."\" value='$key' ".
               ($_POST['value']==$key?'selected':'').">".
               Toolbox::substr($val, 0, $_POST["limit"])."</option>";
      }
   }

   $output = Dropdown::getDropdownName($table,$_POST['value']);

   if (strlen($output)!=0 && $output!="&nbsp;") {
      if ($_SESSION["glpiis_ids_visible"]) {
         $output .= " (".$_POST['value'].")";
      }
      echo "<option selected value='".$_POST['value']."'>".$output."</option>";
   }

   if ($DB->numrows($result)) {
      $prev = -1;

      while ($data =$DB->fetch_array($result)) {
         $output = $data[$field];

         if ($displaywith) {
            foreach ($_POST['displaywith'] as $key) {
               if (isset($data[$key]) && strlen($data[$key])!=0) {
                  $output .= " - ".$data[$key];
               }
            }
         }
         $ID = $data['id'];
         $addcomment = "";

         if (isset($data["comment"])) {
            $addcomment = " - ".$data["comment"];
         }
         if ($_SESSION["glpiis_ids_visible"] || strlen($output)==0) {
            $output .= " ($ID)";
         }

         if ($multi && $data["entities_id"]!=$prev) {
            if ($prev>=0) {
               echo "</optgroup>";
            }
            $prev = $data["entities_id"];
            echo "<optgroup label=\"". Dropdown::getDropdownName("glpi_entities", $prev) ."\">";
         }

         echo "<option value='$ID' title=\"".Html::cleanInputText($output.$addcomment)."\">".
               Toolbox::substr($output, 0, $_POST["limit"])."</option>";
      }

      if ($multi) {
         echo "</optgroup>";
      }
   }
   echo "</select>";
}
*/
if (isset($_POST["comment"]) && $_POST["comment"]) {
   $paramscomment = array('value' => '__VALUE__',
                          'table' => $table);

   Ajax::updateItemOnSelectEvent("dropdown_".$_POST["myname"].$_POST["rand"],
                                 "comment_".$_POST["myname"].$_POST["rand"],
                                $CFG_GLPI["root_doc"]."/ajax/comments.php", $paramscomment);
}

if (isset($_POST["action"]) && $_POST["action"]) {
 
   $params=array($_POST['myname']         => '__VALUE__',
                       'entity_restrict'  => $_POST['entity_restrict'],
                       'suppliers_id'     => $_POST["suppliers_id"],
                       'rand'             => $_POST['rand'],
                       'itemtype'         => $_POST['itemtype']);
                   
   Ajax::updateItemOnSelectEvent("dropdown_".$_POST["myname"].$_POST["rand"], $_POST['span'],
                                     $_POST['action'],
                                     $params);
}

Ajax::commonDropdownUpdateItem($_POST);

?>