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

// Class PluginOrderNotificationTargetPurchaseRequest
class PluginOrderNotificationTargetPurchaseRequest extends NotificationTarget {
   const PURCHASE_VALIDATOR        = 30;
   const PURCHASE_AUTHOR           = 31;


   public function getEvents() {
      return array (
         'ask_purchaserequest'           => __("Request for validation of the purchase request", "order"),
         'no_validation_purchaserequest' => __("Refusal of validation request", "order"),
         'validation_purchaserequest'    => __("Purchase request validation", "order"),
      );
   }

   public function getDatasForTemplate($event, $options = array()) {
      global $CFG_GLPI;

      $events = $this->getAllEvents();

      $this->datas['##purchaserequest.action##'] = $events[$event];

      $this->datas['##lang.purchaserequest.title##'] = $events[$event];

      $this->datas['##lang.purchaserequest.entity##'] = __("Entity");
      $this->datas['##purchaserequest.entity##']      = Dropdown::getDropdownName('glpi_entities',
                                                                                  $this->obj->getField('entities_id'));

      $this->datas['##lang.purchaserequest.name##'] = __("Name");
      $this->datas['##purchaserequest.name##']      = $this->obj->getField("name");

      $this->datas['##lang.purchaserequest.requester##'] = __("Requester");
      $this->datas['##purchaserequest.requester##']      = Html::clean(getUserName($this->obj->getField('users_id')));

      $this->datas['##lang.purchaserequest.group##'] = __("Requester group");
      $this->datas['##purchaserequest.group##']      = Html::clean(Dropdown::getDropdownName('glpi_groups',
                                                                                             $this->obj->getField('groups_id')));

      $this->datas['##lang.purchaserequest.duedate##'] = __("Due date", "order");
      $this->datas['##purchaserequest.duedate##']      = Html::convDate($this->obj->getField("due_date"));

      $this->datas['##lang.purchaserequest.comment##'] = __("Description");

      $comment                                    = Toolbox::stripslashes_deep(str_replace(array('\r\n', '\n', '\r'), "<br/>", $this->obj->getField('comment')));
      $this->datas['##purchaserequest.comment##'] = nl2br($comment);

      $itemtype = $this->obj->getField("itemtype");

      $this->datas['##lang.purchaserequest.itemtype##'] = __("Item type");
      if (file_exists(GLPI_ROOT."/inc/".strtolower($itemtype)."type.class.php")) {
         $this->datas['##purchaserequest.itemtype##'] = Dropdown::getDropdownName(getTableForItemType($itemtype."Type"),
                                                                                  $this->obj->getField("types_id"));
      } else if ($itemtype == "PluginOrderOther") {
         $this->datas['##purchaserequest.itemtype##'] = $this->obj->getField('othertypename');
      }

      $this->datas['##lang.purchaserequest.type##'] = __("Type");
      $item = new $itemtype();
      $this->datas['##purchaserequest.type##']      = $item->getTypeName();

      switch ($event) {
         case "ask_purchaserequest" :
            $this->datas['##lang.purchaserequest.users_validation##'] = __("Purchase request validation", "order")
                                                             . " " . __("By");
            break;
         case "validation_purchaserequest" :
            $this->datas['##lang.purchaserequest.users_validation##'] = __("Purchase request is validated", "order")
                                                             . " " . __("By");
            break;
         case "no_validation_purchaserequest" :
            $this->datas['##lang.purchaserequest.users_validation##'] = __("Purchase request canceled", "order")
                                                             . " " . __("By");
            break;

      }
      $this->datas['##purchaserequest.users_validation##'] = Html::clean(getUserName( $this->obj->getField('users_id_validation')));

      $this->datas['##lang.purchaserequest.url##'] = "URL";

      $url                                    = $CFG_GLPI["url_base"] . "/index.php?redirect=PluginOrderPurchaserequest_" . $this->obj->getField("id");
      $this->datas['##purchaserequest.url##'] = urldecode($url);

   }

   public function getTags() {
      $tags = array(
         'purchaserequest.name'             => __("Name"),
         'purchaserequest.requester'        => __("Requester"),
         'purchaserequest.group'            => __("Requester group"),
         'purchaserequest.duedate'         => __("Due date", "order"),
         'purchaserequest.comment'          => __("Description"),
         'purchaserequest.itemtype'         => __("Item type"),
         'purchaserequest.type'             => __("Type"),
         'purchaserequest.users_validation' => __("Editor of validation", "order"),

      );

      foreach ($tags as $tag => $label) {
         $this->addTagToList(array(
                                'tag'   => $tag,
                                'label' => $label,
                                'value' => true,
                             ));
      }

      asort($this->tag_descriptions);
   }

   public static function install(Migration $migration) {
      global $DB;

      $migration->displayMessage("Migrate PluginOrderOrder notifications");

      $template     = new NotificationTemplate();
      $templates_id = false;
      $query_id     = "SELECT `id`
                       FROM `glpi_notificationtemplates`
                       WHERE `itemtype`='PluginOrderPurchaseRequest'
                       AND `name` = 'Purchase Request Validation'";
      $result       = $DB->query($query_id) or die ($DB->error());

      if ($DB->numrows($result) > 0) {
         $templates_id = $DB->result($result, 0, 'id');
      } else {
         $tmp = array(
            'name'     => 'Purchase Request Validation',
            'itemtype' => 'PluginOrderPurchaseRequest',
            'date_mod' => $_SESSION['glpi_currenttime'],
            'comment'  => '',
            'css'      => '',
         );
         $templates_id = $template->add($tmp);
      }

      if ($templates_id) {
         $translation = new NotificationTemplateTranslation();
         if (!countElementsInTable($translation->getTable(), "`notificationtemplates_id`='$templates_id'")) {
            $tmp['notificationtemplates_id'] = $templates_id;
            $tmp['language']                 = '';
            $tmp['subject']                  = '##lang.purchaserequest.title##';
            $tmp['content_text']             = '##lang.purchaserequest.url## : ##purchaserequest.url##
               ##lang.purchaserequest.entity## : ##purchaserequest.entity##
               ##IFpurchaserequest.name####lang.purchaserequest.name## : ##purchaserequest.name##
               ##ENDIFpurchaserequest.name##
               ##IFpurchaserequest.requester####lang.purchaserequest.requester## : ##purchaserequest.requester##
               ##ENDIFpurchaserequest.requester##               
               ##IFpurchaserequest.group####lang.purchaserequest.group## : ##purchaserequest.group##
               ##ENDIFpurchaserequest.group##
               ##IFpurchaserequest.duedate####lang.purchaserequest.duedate##  : ##purchaserequest.duedate####ENDIFpurchaserequest.duedate##
               ##IFpurchaserequest.itemtype####lang.purchaserequest.itemtype## : ##purchaserequest.itemtype####ENDIFpurchaserequest.itemtype##
               ##IFpurchaserequest.type####lang.purchaserequest.type## : ##purchaserequest.type####ENDIFpurchaserequest.type##

               ##IFpurchaserequest.comment####lang.purchaserequest.comment## : ##purchaserequest.comment####ENDIFpurchaserequest.comment##';

            $tmp['content_html']             = '&lt;p&gt;&lt;strong&gt;##lang.purchaserequest.url##&lt;/strong&gt; : ' .
                                               '&lt;a href=\"##purchaserequest.url##\"&gt;##purchaserequest.url##&lt;/a&gt;&lt;br /&gt;' .
                                               '&lt;br /&gt;&lt;strong&gt;##lang.purchaserequest.entity##&lt;/strong&gt; : ##purchaserequest.entity##&lt;br /&gt;' .
                                               ' ##IFpurchaserequest.name##&lt;strong&gt;##lang.purchaserequest.name##&lt;/strong&gt;' .
                                               ' : ##purchaserequest.name####ENDIFpurchaserequest.name##&lt;br /&gt;' .
                                               '##IFpurchaserequest.requester##&lt;strong&gt;##lang.purchaserequest.requester##&lt;/strong&gt;' .
                                               ' : ##purchaserequest.requester####ENDIFpurchaserequest.requester##&lt;br /&gt;'.
                                               '##IFpurchaserequest.group##&lt;strong&gt;##lang.purchaserequest.group##&lt;/strong&gt;' .
                                               ' : ##purchaserequest.group####ENDIFpurchaserequest.group##&lt;br /&gt;'.
                                               '##IFpurchaserequest.duedate##&lt;strong&gt;##lang.purchaserequest.duedate##&lt;/strong&gt;' .
                                               ' : ##purchaserequest.duedate####ENDIFpurchaserequest.duedate##&lt;br /&gt;' .
                                               '##IFpurchaserequest.itemtype##&lt;strong&gt;##lang.purchaserequest.itemtype##&lt;/strong&gt;' .
                                               ' : ##purchaserequest.itemtype####ENDIFpurchaserequest.itemtype##&lt;br /&gt;' .
                                               '##IFpurchaserequest.type##&lt;strong&gt;##lang.purchaserequest.type##&lt;/strong&gt;' .
                                               ' : ##purchaserequest.type####ENDIFpurchaserequest.type##&lt;br /&gt;&lt;br /&gt;' .
                                               '##IFpurchaserequest.comment##&lt;strong&gt;##lang.purchaserequest.comment##&lt;/strong&gt; :'.
                                               '##purchaserequest.comment####ENDIFpurchaserequest.comment##&lt;/p&gt;';
            $translation->add($tmp);
         }

         $notifs = array(
            'New Purchase Request Validation'     => 'ask_purchaserequest',
            'Confirm Purchase Request Validation' => 'validation_purchaserequest',
            'Cancel Purchase Request Validation'  => 'no_validation_purchaserequest',
         );
         $notification = new Notification();
         foreach ($notifs as $label => $name) {
            if (!countElementsInTable("glpi_notifications", "`itemtype`='PluginOrderOrder' AND `event`='$name'")) {
               $tmp = array(
                  'name'                     => $label,
                  'entities_id'              => 0,
                  'itemtype'                 => 'PluginOrderPurchaseRequest',
                  'event'                    => $name,
                  'mode'                     => 'mail',
                  'comment'                  => '',
                  'is_recursive'             => 1,
                  'is_active'                => 1,
                  'date_mod'                 => $_SESSION['glpi_currenttime'],
                  'notificationtemplates_id' => $templates_id,
               );
               $notification->add($tmp);
            }
         }
      }

   }

   public static function uninstall() {
      global $DB;

      $notif = new Notification();

      foreach (array('ask', 'validation', 'no_validation') as $event) {
         $options = array(
            'itemtype' => 'PluginOrderPurchaseRequest',
            'event'    => $event,
            'FIELDS'   => 'id',
         );

         foreach ($DB->request('glpi_notifications', $options) as $data) {
            $notif->delete($data);
         }
      }

      //templates
      $template    = new NotificationTemplate();
      $translation = new NotificationTemplateTranslation();
      $options     = array('itemtype' => 'PluginOrderPurchaseRequest', 'FIELDS' => 'id');

      foreach ($DB->request('glpi_notificationtemplates', $options) as $data) {
         $options_template = array('notificationtemplates_id' => $data['id'], 'FIELDS' => 'id');

         foreach ($DB->request('glpi_notificationtemplatetranslations', $options_template) as $data_template) {
            $translation->delete($data_template);
         }
         $template->delete($data);
      }
   }

   /**
    * Get additionnals targets for Tickets
    **/
   public function getAdditionalTargets($event='') {
      $this->addTarget(self::PURCHASE_VALIDATOR, __("Validator of the purchase request", "order"));
      $this->addTarget(self::PURCHASE_AUTHOR, __("Author of the purchase request", "order"));

   }

   public function getSpecificTargets($data, $options) {
      switch ($data['items_id']) {
         case self::PURCHASE_VALIDATOR:
            $this->getUserByField ("users_id_validate");
            break;
         case self::PURCHASE_AUTHOR:
            $this->getUserByField ("users_id_creator");
            break;

      }
   }
}
