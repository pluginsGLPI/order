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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginOrderProfile extends CommonDBTM {

   public static $rightname = 'profile';

   public static function createFirstAccess($ID) {
      self::addDefaultProfileInfos($ID, array(
         'plugin_order_order'                             => PluginOrderOrder::ALLRIGHTS, // All rights : CREATE + READ + ...
         'plugin_order_bill'                              => 127,
         'plugin_order_reference'                         => 127,
         'plugin_order_purchaserequest'                   => 127,
         'plugin_order_purchaserequest_validate'          => 1), true
      );
   }

   /**
    * @param $profile
    * */
   static function addDefaultProfileInfos($profiles_id, $rights, $drop_existing = false) {
      global $DB;

      $profileRight = new ProfileRight();
      foreach ($rights as $right => $value) {
         if (countElementsInTable('glpi_profilerights',
                                  "`profiles_id`='$profiles_id'
                                     AND `name`='$right'") && $drop_existing) {
            $profileRight->deleteByCriteria(array('profiles_id' => $profiles_id, 'name' => $right));
         }
         if (!countElementsInTable('glpi_profilerights',
                                   "`profiles_id`='$profiles_id'
                                      AND `name`='$right'")) {
            $myright['profiles_id'] = $profiles_id;
            $myright['name']        = $right;
            $myright['rights']      = $value;
            $profileRight->add($myright);

            //Add right to the current session
            $_SESSION['glpiactiveprofile'][$right] = $value;
         }
      }
   }

   /* profiles modification */

   public function showForm($profiles_id = 0, $openform = TRUE, $closeform = TRUE) {

      echo "<div class='firstbloc'>";

      if (($canedit = Session::haveRightsOr(self::$rightname, array(CREATE, UPDATE, PURGE))) && $openform) {
         $profile = new Profile();
         echo "<form method='post' action='".$profile->getFormURL()."'>";
      }

      $profile = new Profile();
      $profile->getFromDB($profiles_id);

      //$rights = array('rights' => self::getRights($profile->getField('interface'),);
      $rights = array();
      if ($profile->getField('interface') == 'central') {
         $rights = $this->getAllRights();
      }

      $profile->displayRightsChoiceMatrix($rights, array(
         'canedit'       => $canedit,
         'default_class' => 'tab_bg_2',
         'title'         => __('Orders', 'order'),
      ));

      echo "<table class='tab_cadre_fixehov'>";
      echo "<tr class='tab_bg_1'><th colspan='4'>" . __('Helpdesk') . "</th></tr>\n";

      $effective_rights = ProfileRight::getProfileRights($profiles_id, array('plugin_order_purchaserequest_validate'));
      echo "<tr class='tab_bg_2'>";
      echo "<td width='20%'>" . __("Purchase request validation", "order") . "</td>";
      echo "<td colspan='5'>";
      Html::showCheckbox(array('name' => '_plugin_order_purchaserequest_validate',
                               'checked' => $effective_rights['plugin_order_purchaserequest_validate']));
      echo "</td></tr>\n";
      echo "</table>";

      if ($canedit && $closeform) {
         echo "<div class='center'>";
         echo Html::hidden('id', array('value' => $profiles_id));
         echo Html::submit(_sx('button', 'Save'), array('name' => 'update'));
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";
   }

   public static function install(Migration $migration) {
      global $DB;

      if (TableExists("glpi_plugin_order_profiles")
         && !FieldExists("glpi_plugin_order_profiles",
                         "plugin_order_generate_order_without_validation")) {
         $DB->query("ALTER TABLE `glpi_plugin_order_profiles`
                     ADD `plugin_order_generate_order_without_validation` char(1) default NULL;");
      }

      self::initProfile();
      self::createFirstAccess($_SESSION['glpiactiveprofile']['id']);

      $migration->dropTable('glpi_plugin_order_profiles');
   }

   public static function uninstall() {
      global $DB;
      $DB->query("DELETE FROM glpi_profilerights WHERE name LIKE 'plugin_order_%'");
      self::removeRightsFromSession();
   }

   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      $type = get_class($item);
      if ($type == 'Profile') {
         if ($item->getField('id') && $item->getField('interface') != 'helpdesk') {
            return array(1 => __("Orders", "order"));
         }
      }
      return '';
   }

   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'Profile') {
         $prof = new self();
         self::addDefaultProfileInfos($item->getID(), array(
            'plugin_order_order'                             => 0, // All rights : CREATE + READ + ...
            'plugin_order_bill'                              => 0,
            'plugin_order_reference'                         => 0,
            'plugin_order_purchaserequest'                   => 0,
            'plugin_order_purchaserequest_validate'          => 0
         ));
         $prof->showForm($item->getID());
      }
      return true;
   }

   static function getAllRights($all = false) {

      $rights = array(
         array('itemtype' => 'PluginOrderOrder',
            'label'    => __("Orders", "order"),
            'field'    => 'plugin_order_order'
         ),
         array('itemtype' => 'PluginOrderReference',
            'label'    => __("Products references", "order"),
            'field'    => 'plugin_order_reference'
         ),
         array('itemtype' => 'PluginOrderBill',
            'label'    => __("Bills", "order"),
            'field'    => 'plugin_order_bill'
         ),
         array('itemtype' => 'PluginOrderPurchaseRequest',
            'label'    => __("Purchase request", "order"),
            'field'    => 'plugin_order_purchaserequest'
         )
      );

      if ($all) {
         $rights[] = array('itemtype' => 'PluginOrderPurchaseRequest',
                           'label' => __("Purchase request validation", "order"),
                           'field' => 'plugin_order_purchaserequest_validate');
      }

      return $rights;
   }

   static function translateARight($old_right) {
      switch ($old_right) {
         case '':
            return 0;
         case 'r' :
            return READ;
         case 'w':
            return PluginOrderOrder::ALLRIGHTS;
         case '0':
         case '1':
            return $old_right;

         default :
            return 0;
      }
   }

   static function migrateOneProfile($profiles_id) {
      global $DB;
      //Cannot launch migration if there's nothing to migrate...
      if (!TableExists('glpi_plugin_order_profiles')) {
         return true;
      }

      foreach ($DB->request('glpi_plugin_order_profiles',
                            "`profiles_id`='$profiles_id'") as $profile_data) {

         $matching       = array('order'              => 'plugin_order_order',
            'bill'               => 'plugin_order_bill',
            'reference'          => 'plugin_order_reference',
            'validation'         => 'plugin_order_validation',
            'cancel'             => 'plugin_order_cancel',
            'undo_validation'    => 'plugin_order_undo_validation',
            'delivery'           => 'plugin_order_delivery',
            'generate_order_odt' => 'plugin_order_generate_order_odt',
            'open_ticket'        => 'plugin_order_open_ticket'
         );
         $current_rights = ProfileRight::getProfileRights($profiles_id, array_values($matching));
         foreach ($matching as $old => $new) {
            if (!isset($current_rights[$old])) {
               $right = self::translateARight($profile_data[$old]);
               switch ($new) {
                  case 'plugin_order_delivery' :
                  case 'plugin_order_validation' :
                  case 'plugin_order_cancel' :
                  case 'plugin_order_undo_validation' :
                  case 'plugin_order_generate_order_without_validation' :
                  case 'plugin_order_generate_order_odt' :
                  case 'plugin_order_open_ticket' :
                     $right = 0;
                     if ($profile_data[$old] == 'w') {
                        $right = 1;
                     }
                     break;

               }
               $query = "UPDATE `glpi_profilerights`
                         SET `rights`='".$right."'
                         WHERE `name`='$new' AND `profiles_id`='$profiles_id'";
               $DB->query($query);
            }
         }
      }
   }

   /**
    * Initialize profiles, and migrate it necessary
    */
   static function initProfile() {
      global $DB;
      $profile = new self();

      //Add new rights in glpi_profilerights table
      foreach ($profile->getAllRights(true) as $data) {
         if (countElementsInTable("glpi_profilerights", "`name` = '".$data['field']."'") == 0) {
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
                              AND `name` LIKE '%plugin_order%'") as $prof) {
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
