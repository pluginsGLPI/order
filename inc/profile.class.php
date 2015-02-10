<?php
/*
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
 @copyright Copyright (c) 2010-2015 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginOrderProfile extends CommonDBTM
{
   public static $rightname = 'profile';

   public static function getTypeName($nb = 0)
   {
      return __("Rights assignment");
   }

   //if profile deleted
   public static function purgeProfiles(Profile $prof)
   {
      $plugprof = new self();
      $plugprof->deleteByCriteria(array('profiles_id' => $prof->getField("id")));
   }

   public function getFromDBByProfile($profiles_id)
   {
      global $DB;

      $query = "SELECT * FROM `" . $this->getTable() . "`
               WHERE `profiles_id` = '" . $profiles_id . "' ";
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

   public static function createFirstAccess($ID)
   {
      // $myProf = new self();
      // if (!$myProf->getFromDBByProfile($ID)) {
      //    $myProf->add(array(
      //       'profiles_id'        => $ID,
      //       'order'              => UPDATE,
      //       'reference'          => UPDATE,
      //       'validation'         => UPDATE,
      //       'cancel'             => UPDATE,
      //       'undo_validation'    => UPDATE,
      //       'bill'               => UPDATE,
      //       'delivery'           => UPDATE,
      //       'generate_order_odt' => UPDATE,
      //    ));

      // }

      self::addDefaultProfileInfos($ID, array(
         'plugin_order'                    => 127,
         'plugin_order_order'              => 1,
         'plugin_order_bill'               => 1,
         'plugin_order_reference'          => 1,
         'plugin_order_delivery'           => 1,
         'plugin_order_generate_order_odt' => 1,
         'plugin_order_open_ticket'        => 1,
         'plugin_order_validation'         => 1,
         'plugin_order_cancel'             => 1,
         'plugin_order_undo_validation'    => 1,
      ));
   }

   public static function addRightToProfile($profiles_id, $right, $value = '')
   {
      $myProf = new self();
      if ($myProf->getFromDBByProfile($profiles_id)) {
         $tmp = $myProf->fields;
         $tmp[$right] = $value;
         $myProf->update($tmp);
      }
   }

   public function createAccess($ID)
   {
      $this->add(array('profiles_id' => $ID));
   }

   public static function changeProfile()
   {
      $prof = new self();
      if ($prof->getFromDBByProfile($_SESSION['glpiactiveprofile']['id'])) {
         $_SESSION["glpi_plugin_order_profile"] = $prof->fields;
      } else {
         unset($_SESSION["glpi_plugin_order_profile"]);
      }
   }

   /* profiles modification */
   public function showForm ($profiles_id = 0, $openform = TRUE, $closeform = TRUE)
   {
      // if (!Session::haveRight("profile", READ)) {
      //    return false;
      // }

      // $prof = new Profile();
      // if ($ID) {
      //    $this->getFromDBByProfile($ID);
      //    $prof->getFromDB($ID);
      // }

      // $this->showFormHeader($options);

      // echo "<tr class='tab_bg_2'>";

      // echo "<th colspan='4' align='center'><strong>" .
      //    __("Rights assignment") . " " . $prof->fields["name"] . "</strong></th>";

      // echo "</tr>";
      // echo "<tr class='tab_bg_2'>";

      // echo "<td>" . __("Orders", "order") . ":</td><td>";
      // if ($prof->fields['interface'] != 'helpdesk') {
      //    Profile::dropdownNoneReadWrite("order",$this->fields["order"], 1, 1, 1);
      // } else {
      //    echo __("No access");
      // }
      // echo "</td>";

      // echo "<td>" . __("Products references", "order") . ":</td><td>";
      // if ($prof->fields['interface'] != 'helpdesk') {
      //    Profile::dropdownNoneReadWrite("reference",$this->fields["reference"], 1, 1, 1);
      // } else {
      //    echo __("No access");
      // }
      // echo "</td>";

      // echo "</tr>";
      // echo "<tr class='tab_bg_2'>";

      // echo "<td>" . __("Bills", "order") . ":</td><td>";
      // if ($prof->fields['interface'] != 'helpdesk') {
      //    Profile::dropdownNoneReadWrite("bill",$this->fields["bill"], 1, 1, 1);
      // } else {
      //    echo __("No access");
      // }
      // echo "</td>";

      // echo "<td>" . __("Take item delivery", "order") . ":</td><td>";
      // if ($prof->fields['interface'] != 'helpdesk') {
      //    Profile::dropdownNoneReadWrite("delivery", $this->fields["delivery"], 1, 1, 1);
      // } else {
      //    echo __("No access");
      // }
      // echo "</td>";

      // echo "</tr>";

      // echo "<tr class='tab_bg_2'>";

      // echo "<td>" . __("Order Generation", "order") . ":</td><td>";
      // if ($prof->fields['interface'] != 'helpdesk') {
      //    Profile::dropdownNoneReadWrite("generate_order_odt",
      //                                   $this->fields["generate_order_odt"], 1, 0, 1);
      // } else {
      //    echo __("No access");
      // }
      // echo "</td>";
      // echo "<td>" . __("Link order to a ticket", "order") . "</td>";
      // echo "<td>";
      // Dropdown::showYesNo('open_ticket', $this->fields['open_ticket']);
      // echo "</td>";
      // echo "</tr>";


      // echo "<tr align='center'><th colspan='4' >" . __("Validation", "order") . "</th></tr>";

      // echo "<tr class='tab_bg_2'>";

      // echo "<td>" . __("Order validation", "order") . ":</td><td>";
      // if ($prof->fields['interface'] != 'helpdesk') {
      //    Profile::dropdownNoneReadWrite("validation", $this->fields["validation"], 1, 0, 1);
      // } else {
      //    echo __("No access");
      // }
      // echo "</td>";

      // echo "<td>" . __("Cancel order", "order") . ":</td><td>";
      // if ($prof->fields['interface'] != 'helpdesk') {
      //    Profile::dropdownNoneReadWrite("cancel", $this->fields["cancel"], 1, 0, 1);
      // } else {
      //    echo __("No access");
      // }
      // echo "</td>";

      // echo "</tr>";
      // echo "<tr class='tab_bg_2'>";

      // echo "<td>" . __("Edit a validated order", "order") . ":</td><td>";
      // if ($prof->fields['interface'] != 'helpdesk') {
      //    Profile::dropdownNoneReadWrite("undo_validation", $this->fields["undo_validation"], 1, 0, 1);
      // } else {
      //    echo __("No access");
      // }
      // echo "</td>";

      // echo "<td colspan='2'></td>";

      // echo "</tr>";

      // echo "<input type='hidden' name='id' value=" . $this->fields["id"] . ">";

      // $options['candel'] = false;
      // $this->showFormButtons($options);

      echo "<div class='firstbloc'>";
      if (($canedit = Session::haveRightsOr(self::$rightname, array(CREATE, UPDATE, PURGE)))
          && $openform) {
         $profile = new Profile();
         echo "<form method='post' action='".$profile->getFormURL()."'>";
      }

      $profile = new Profile();
      $profile->getFromDB($profiles_id);

      $rights = $this->getHelpdeskRights();
      if ($profile->getField('interface') == 'central') {
         $rights = $this->getAllRights();
      }
      $profile->displayRightsChoiceMatrix($rights, array(
         'canedit'       => $canedit,
         'default_class' => 'tab_bg_2',
         'title'         => __('Order management', 'order'),
      ));

      if ($canedit
          && $closeform) {
         echo "<div class='center'>";
         echo Html::hidden('id', array('value' => $profiles_id));
         echo Html::submit(_sx('button', 'Save'), array('name' => 'update'));
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";
   }

   public static function install(Migration $migration)
   {
      global $DB;

      $table = getTableForItemType(__CLASS__);
      if (!TableExists($table)) {
         $migration->displayMessage("Installing $table");

         $query = "CREATE TABLE `glpi_plugin_order_profiles` (
               `id` int(11) NOT NULL auto_increment,
               `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
               `order` char(1) collate utf8_unicode_ci default NULL,
               `reference` char(1) collate utf8_unicode_ci default NULL,
               `validation` char(1) collate utf8_unicode_ci default NULL,
               `cancel` char(1) collate utf8_unicode_ci default NULL,
               `undo_validation` char(1) collate utf8_unicode_ci default NULL,
               `bill` char(1) collate utf8_unicode_ci default NULL,
               `delivery` char(1) collate utf8_unicode_ci default NULL,
               `generate_order_odt` char(1) collate utf8_unicode_ci default NULL,
               `open_ticket` char(1) default NULL,
               PRIMARY KEY  (`id`),
               KEY `profiles_id` (`profiles_id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die($DB->error());
         PluginOrderProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);

      } else {
         $migration->displayMessage("Upgrading $table");

         //1.2.0
         $migration->changeField($table, "ID", "id", "int(11) NOT NULL auto_increment");
         foreach (array('order', 'reference', 'budget', 'validation', 'cancel', 'undo_validation')
            as $right) {
            $migration->changeField($table, $right, $right,
                                    "char(1) collate utf8_unicode_ci default NULL");
         }
         $migration->migrationOneTable($table);

         if ($migration->addField($table, "profiles_id", "int(11) NOT NULL default '0'")) {
            $migration->addKey($table, "profiles_id");
            $migration->migrationOneTable($table);

            //Migration profiles
            $DB->query("UPDATE `$table` SET `profiles_id`=`id`");
         }

         //1.4.0
         $migration->dropField($table, "budget");
         $migration->dropField($table, "name");
         $migration->migrationOneTable($table);

         //1.5.0
         $migration->addField($table, "bill", "char");
         $migration->migrationOneTable($table);
         self::addRightToProfile($_SESSION['glpiactiveprofile']['id'], "bill" , "w");

         //1.5.3
         //Add delivery right
         if ($migration->addField($table, "delivery", "char")) {
            $migration->migrationOneTable($table);
            //Update profiles : copy order right not to change current behavior
            $update = "UPDATE `$table` SET `delivery`=`order`";
            $DB->query($update);
            self::addRightToProfile($_SESSION['glpiactiveprofile']['id'], "delivery" , "w");
         }
         if ($migration->addField($table, "generate_order_odt", "char")) {
            $migration->migrationOneTable($table);
            //Update profiles : copy order right not to change current behavior
            $update = "UPDATE `$table` SET `generate_order_odt`=`order`";
            $DB->query($update);
            self::addRightToProfile($_SESSION['glpiactiveprofile']['id'], "generate_order_odt" , "w");
         }

      }

      //1.7.2
      $migration->addField($table, "open_ticket", "char");
      $migration->migrationOneTable($table);
      self::addRightToProfile($_SESSION['glpiactiveprofile']['id'], "open_ticket" , "w");

      self::changeProfile();
   }

   public static function uninstall()
   {
      global $DB;

      //Current table name
      $DB->query("DROP TABLE IF EXISTS  `" . getTableForItemType(__CLASS__) . "`") or die ($DB->error());
   }

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
   {
      $type = get_class($item);
      if ($type == 'Profile') {
         if ($item->getField('id') && $item->getField('interface')!='helpdesk') {
            return array(1 => __("Orders", "order"));
         }
      }
      return '';
   }


   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
   {
      // if (get_class($item) == 'Profile') {
      //    $profile = new self();
      //    if (!$profile->getFromDBByProfile($item->getField('id'))) {
      //       $profile->createAccess($item->getField('id'));

      //    }
      //    $profile->showForm($item->getField('id'));
      // }


      if ($item->getType()=='Profile') {
         $ID   = $item->getID();
         $prof = new self();

         self::addDefaultProfileInfos($ID, array(
            'plugin_order'                    => 0,
            'plugin_order_order'              => 0,
            'plugin_order_bill'               => 0,
            'plugin_order_reference'          => 0,
            'plugin_order_delivery'           => 0,
            'plugin_order_generate_order_odt' => 0,
            'plugin_order_open_ticket'        => 0,
            'plugin_order_validation'         => 0,
            'plugin_order_cancel'             => 0,
            'plugin_order_undo_validation'    => 0,
         ));
         $prof->showForm($ID);
      }
      return true;
   }

   /**
    * @param $profile
   **/
   static function addDefaultProfileInfos($profiles_id, $rights, $drop_existing = false) {
      global $DB;

      $profileRight = new ProfileRight();
      foreach ($rights as $right => $value) {
         if (countElementsInTable('glpi_profilerights',
                                   "`profiles_id`='$profiles_id' AND `name`='$right'") && $drop_existing) {
            $profileRight->deleteByCriteria(array('profiles_id' => $profiles_id, 'name' => $right));
         }
         if (!countElementsInTable('glpi_profilerights',
                                   "`profiles_id`='$profiles_id' AND `name`='$right'")) {
            $myright['profiles_id'] = $profiles_id;
            $myright['name']        = $right;
            $myright['rights']      = $value;
            $profileRight->add($myright);

            //Add right to the current session
            $_SESSION['glpiactiveprofile'][$right] = $value;
         }
      }
   }

   static function getHelpdeskRights($all = false) {


      $rights = array(
          array('rights'  => Profile::getRightsFor('PluginAccountsAccount', 'helpdesk'),
                'label'     => _n('Account', 'Accounts', 2, 'accounts'),
                'field'     => 'plugin_accounts'
          ),
      );

      if ($all) {
         $rights[] = array('itemtype' => 'PluginAccountsAccount',
                           'label'    =>  __('See accounts of my groups', 'accounts'),
                           'field'    => 'plugin_accounts_my_groups');

         $rights[] = array('itemtype' => 'PluginAccountsAccount',
                           'label'    =>  __('See all accounts', 'accounts'),
                           'field'    => 'plugin_accounts_see_all_users');

         $rights[] = array('itemtype' => 'PluginAccountsAccount',
                           'label'    =>  __('Associable items to a ticket'),
                           'field'    => 'plugin_accounts_open_ticket');
      }

      return $rights;
   }

   static function getAllRights($all = false) {


      $rights = array(
          array('rights'  => Profile::getRightsFor('PluginAccountsAccount', 'central'),
                'label'     => _n('Account', 'Accounts', 2, 'accounts'),
                'field'     => 'plugin_accounts'
          ),
      );

      if ($all) {
         $rights[] = array('itemtype' => 'PluginAccountsAccount',
                           'label'    =>  __('See accounts of my groups', 'accounts'),
                           'field'    => 'plugin_accounts_my_groups');

         $rights[] = array('itemtype' => 'PluginAccountsAccount',
                           'label'    =>  __('See all accounts', 'accounts'),
                           'field'    => 'plugin_accounts_see_all_users');

         $rights[] = array('itemtype' => 'PluginAccountsAccount',
                           'label'    =>  __('Associable items to a ticket'),
                           'field'    => 'plugin_accounts_open_ticket');
      }

      return $rights;
   }

   /**
    * Init profiles
    *
    **/

   static function translateARight($old_right) {
      switch ($old_right) {
         case '':
            return 0;
         case 'r' :
            return READ;
         case 'w':
            return ALLSTANDARDRIGHT + READNOTE + UPDATENOTE;
         case '0':
         case '1':
            return $old_right;

         default :
            return 0;
      }
   }

   /**
   * @since 0.85
   * Migration rights from old system to the new one for one profile
   * @param $profiles_id the profile ID
   */
   static function migrateOneProfile($profiles_id) {
      // global $DB;
      // //Cannot launch migration if there's nothing to migrate...
      // if (!TableExists('glpi_plugin_accounts_profiles')) {
      // return true;
      // }

      // foreach ($DB->request('glpi_plugin_accounts_profiles',
      //                       "`profiles_id`='$profiles_id'") as $profile_data) {

      //    $matching = array('accounts'    => 'plugin_accounts',
      //                      'all_users'   => 'plugin_accounts_see_all_users',
      //                      'my_groups'   => 'plugin_accounts_my_groups',
      //                      'open_ticket' => 'plugin_accounts_open_ticket');
      //    $current_rights = ProfileRight::getProfileRights($profiles_id, array_values($matching));
      //    foreach ($matching as $old => $new) {
      //       if (!isset($current_rights[$old])) {
      //          $query = "UPDATE `glpi_profilerights`
      //                    SET `rights`='".self::translateARight($profile_data[$old])."'
      //                    WHERE `name`='$new' AND `profiles_id`='$profiles_id'";
      //          $DB->query($query);
      //       }
      //    }
      // }
   }

   /**
   * Initialize profiles, and migrate it necessary
   */
   static function initProfile() {
      global $DB;
      $profile = new self();

      //Add new rights in glpi_profilerights table
      foreach ($profile->getAllRights(true) as $data) {
         if (countElementsInTable("glpi_profilerights",
                                  "`name` = '".$data['field']."'") == 0) {
            ProfileRight::addProfileRights(array($data['field']));
         }
      }

      //Migration old rights in new ones
      foreach ($DB->request("SELECT `id` FROM `glpi_profiles`") as $prof) {
         self::migrateOneProfile($prof['id']);
      }
      foreach ($DB->request("SELECT *
                           FROM `glpi_profilerights`
                           WHERE `profiles_id`='".$_SESSION['glpiactiveprofile']['id']."'
                              AND `name` LIKE '%plugin_accounts%'") as $prof) {
         $_SESSION['glpiactiveprofile'][$prof['name']] = $prof['rights'];
      }
   }


   static function removeRightsFromSession() {
      foreach (self::getAllRights(true) as $right) {
         if (isset($_SESSION['glpiactiveprofile'][$right['field']])) {
            unset($_SESSION['glpiactiveprofile'][$right['field']]);
         }
      }
   }
}
