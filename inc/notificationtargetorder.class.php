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
 @author   the order plugin team
 @copyright Copyright (c) 2010-2015 Order plugin team
 @license   GPLv2+
         http://www.gnu.org/licenses/gpl.txt
 @link     https://forge.indepnet.net/projects/order
 @link     http://www.glpi-project.org/
 @since    2009
 ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

// Class NotificationTarget
class PluginOrderNotificationTargetOrder extends NotificationTarget {
   const AUTHOR               = 30;
   const AUTHOR_GROUP           = 31;
   const DELIVERY_USER          = 32;
   const DELIVERY_GROUP         = 33;
   const SUPERVISOR_AUTHOR_GROUP   = 34;
   const SUPERVISOR_DELIVERY_GROUP = 35;


   public function getEvents() {
      return [
       'ask'         => __("Request order validation", "order"),
       'validation'    => __("Order validated", "order"),
       'cancel'       => __("Order canceled", "order"),
       'undovalidation' => __("Order currently edited", "order"),
       'duedate'      => __("Late orders", "order"),
       'delivered'     => __("No item to generate", "order")
      ];
   }


   public function addDataForTemplate($event, $options = []) {
      global $CFG_GLPI, $DB;

      $events = $this->getAllEvents();
      $this->data['##order.action##'] = $events[$event];

      $this->data['##order.comment##']=$this->obj->getField('comment');
      $this->data['##order.duedate##']=$this->obj->getField('duedate');
      $this->data['##order.budget##']=$this->getBudgetsName($this->obj->getField('budgets_id'));

      $query = "SELECT ref.id as references_id, ref.name, ref.itemtype, ref.models_id, ref.manufacturers_id,TRUNCATE(item.price_taxfree, 2) as price_taxfree, TRUNCATE(item.discount, 2) as discount, TRUNCATE(item.price_discounted, 2) as price_discounted, count(*) as quantity, TRUNCATE(SUM(price_discounted), 2) as total_item FROM glpi_plugin_order_orders_items item LEFT JOIN glpi_plugin_order_references ref ON item.plugin_order_references_id=ref.id WHERE item.plugin_order_orders_id = ".$this->obj->getField('id')." GROUP BY ref.id, price_taxfree, discount,item.price_discounted";
      $data_orderitems = $DB->query($query);
      $this->data["orderitems"] = [];
      $total_price = 0;
      foreach ($data_orderitems as $id => $orderitem) {
         $tmp = [];
         $tmp['##order.item.type##'] = $orderitem['itemtype'];
         $tmp['##order.item.price##'] = $orderitem['price_discounted'];
         $tmp['##order.item.quantity##'] = $orderitem['quantity'];
         $tmp['##order.item.manufacturer##'] = Dropdown::getDropdownName("glpi_manufacturers", $orderitem["manufacturers_id"]);
         $tmp['##order.item.model##'] = Dropdown::getDropdownName(getTableForItemType($orderitem["itemtype"]."models"), $orderitem["models_id"]);
         $tmp['##order.item.totalprice##'] = $orderitem['total_item'];
         $references_data = $this->getReferenceData($orderitem['references_id']);
         $tmp['##order.itemreference.name##'] = $references_data['name'];
         $tmp['##order.itemreference.itemtype##'] = $references_data['itemtype'];
         $tmp['##order.itemreference.manufacturer_ref##'] = $references_data['manufacturers_reference'];
         $tmp['##order.itemreference.model##'] = Dropdown::getDropdownName(getTableForItemType($orderitem["itemtype"]."models"), $references_data["models_id"]);
         $tmp['##order.itemreference.type##'] = Dropdown::getDropdownName(getTableForItemType($references_data['itemtype']."types"), $references_data["types_id"]);
         $this->data['orderitems'][] = $tmp;
         $total_price += $orderitem['total_item'];
      }
      $this->data['##order.totalprice##'] = number_format($total_price, 2);
      $this->data['##order.location##'] = Dropdown::getDropdownName("glpi_locations", $this->obj->getField('locations_id'));

      $this->data['##lang.ordervalidation.title##']    = $events[$event];
      $this->data['##lang.ordervalidation.entity##']   = __("Entity");
      $this->data['##ordervalidation.entity##']       = Dropdown::getDropdownName('glpi_entities', $this->obj->getField('entities_id'));
      $this->data['##lang.ordervalidation.name##']     = __("Name");
      $this->data['##ordervalidation.name##']         = $this->obj->getField("name");
      $this->data['##lang.ordervalidation.numorder##']  = __("Order number");
      $this->data['##ordervalidation.numorder##']      = $this->obj->getField("num_order");
      $this->data['##lang.ordervalidation.orderdate##'] = __("Date of order", "order");
      $this->data['##ordervalidation.orderdate##']     = Html::convDate($this->obj->getField("order_date"));
      $this->data['##lang.ordervalidation.state##']    = __("Status");
      $this->data['##ordervalidation.state##']        = Dropdown::getDropdownName("glpi_plugin_order_orderstates", $this->obj->getField("plugin_order_orderstates_id"));

      $this->data['##order.supplier##']               = $this->getSupplierName($this->obj->getField('id'));

      $this->data['##lang.ordervalidation.comment##']= __("Comment of validation", "order");
      switch ($event) {
         case "ask" :
            $this->data['##lang.ordervalidation.users##'] = __("Request order validation", "order").
                                                " ".__("By");
            $comment = Toolbox::stripslashes_deep(str_replace(['\r\n', '\n', '\r'], "<br/>", $options['comments']));
            $this->data['##ordervalidation.comment##']      = nl2br($comment);
          break;
         case "validation" :
            $this->data['##lang.ordervalidation.users##'] = __("Order is validated", "order").
                                                " ".__("By");
            $comment = Toolbox::stripslashes_deep(str_replace(['\r\n', '\n', '\r'], "<br/>", $options['comments']));
            $this->data['##ordervalidation.comment##']      = nl2br($comment);
          break;
         case "cancel" :
            $this->data['##lang.ordervalidation.users##'] = __("Order canceled", "order").
                                                " ".__("By");
            $comment = Toolbox::stripslashes_deep(str_replace(['\r\n', '\n', '\r'], "<br/>", $options['comments']));
            $this->data['##ordervalidation.comment##']      = nl2br($comment);
          break;
         case "undovalidation" :
            $this->data['##lang.ordervalidation.users##'] = __("Validation canceled successfully", "order").
                                                " ".__("By");
            $comment = Toolbox::stripslashes_deep(str_replace(['\r\n', '\n', '\r'], "<br/>", $options['comments']));
            $this->data['##ordervalidation.comment##']      = nl2br($comment);
          break;
         case "delivered" :
            $this->data['##lang.ordervalidation.users##'] = __("No item to generate", "order");
            $comment = Toolbox::stripslashes_deep(str_replace(['\r\n', '\n', '\r'], "<br/>", $options['comments']));
            $this->data['##ordervalidation.comment##']      = nl2br($comment);
          break;
         case "duedate" :
            $this->data['##order.entity##'] = Dropdown::getDropdownName(
            'glpi_entities',
            $options['entities_id']
            );
            foreach ($options['orders'] as $id => $order) {
               $this->data['orders'][] = [
                '##order.item.name##'       => $order['name'],
                '##order.item.numorder##'    => $order['num_order'],
                '##order.item.url##'        => rawurldecode(
                $CFG_GLPI["url_base"]
                ."/index.php?redirect=plugin_order_order_".$id
               ),
               '##order.item.orderdate##'   => Html::convDate($order["order_date"]),
               '##order.item.duedate##'     => Html::convDate($order["duedate"]),
               '##order.item.deliverydate##' => Html::convDate($order["deliverydate"]),
               '##order.item.comment##'     => Html::clean($order["comment"]),
               '##order.item.state##'      => Dropdown::getDropdownName(
                 'glpi_plugin_order_orderstates',
                 $order["plugin_order_orderstates_id"]
               ),
               ];
            }
          break;
      }
      $this->data['##ordervalidation.users##']   = Html::clean(getUserName(Session::getLoginUserID()));
      $this->data['##order.author.name##']      = Html::clean(getUserName($this->obj->getField('users_id')));
      $this->data['##order.deliveryuser.name##']  = Html::clean(getUserName($this->obj->getField('users_id_delivery')));
      $this->data['##lang.ordervalidation.url##'] = "URL";
      $url = $CFG_GLPI["url_base"]."/index.php?redirect=plugin_order_order_".$this->obj->getField("id");
      $this->data['##ordervalidation.url##']     = urldecode($url);

      $this->getTags();
      foreach ($this->tag_descriptions[NotificationTarget::TAG_LANGUAGE] as $tag => $values) {
         if (!isset($this->data[$tag])) {
            $this->data[$tag] = $values['label'];
         }
      }
   }


   public function getTags() {
      $tags = [
         'ordervalidation.name'      => __("Name"),
         'ordervalidation.numorder'   => __("Order number"),
         'ordervalidation.orderdate'   => __("Date of order", "order"),
         'ordervalidation.state'      => __("Status"),
         'ordervalidation.comment'    => __("Comment of validation", "order"),
         'ordervalidation.users'      => __("Editor of validation", "order"),
         'order.entity'            => __("Delivery date"),
         'order.item.name'          => __("Name"),
         'order.item.state'         => __("Status"),
         'order.item.numorder'       => __("Order number"),
         'order.item.orderdate'      => __("Date of order", "order"),
         'order.item.duedate'        => __("Estimated due date", "order"),
         'order.item.deliverydate'    => __("Delivery date"),
         'order.item.comment'        => __("Comments"),
         'order.author.name'         => __("Author"),
         'order.author.phone'        => __("Author").' - '.__("Phone"),
         'order.deliveryuser.name'    => __("Recipient"),
         'order.deliveryuser.phone'   => __("Recipient").' - '.__("Phone"),

         'order.comment'  =>__("Comments"),
         'order.budget' =>__("Budget"),
         'order.duedate' => __("Estimated due date", "order"),
         'order.item.type'=> __("Item type"),
         'order.item.price'=>__("Item price"),
         'order.item.quantity'      => __("quantity"),
         'order.item.manufacturer'   => __("Manufacturer"),
         'order.item.model'         => __("Model"),
         'order.item.totalprice'=> __("Total price for item"),
         'order.totalprice'   => __("Total price"),
         'order.location'      => __('Delivery location'),
         'order.supplier'            => __("Supplier"),
         'order.itemreference.name'      => __("References name"),
         'order.itemreference.itemtype'      => __("References itemtype"),
         'order.itemreference.manufacturer_ref'      => __("References manufacturer ref"),
         'order.itemreference.model'      => __("References model"),
         'order.itemreference.type'      => __("References type"),
      ];

      foreach ($tags as $tag => $label) {
         $this->addTagToList([
            'tag'   => $tag,
            'label' => $label,
            'value' => true,
         ]);
      }

      $this->addTagToList([
         'tag'   => 'order.action',
         'label' => __("Action"),
         'value' => false,
      ]);

      $this->addTagToList([
         'tag'    => 'orders',
         'label'   => __("Late orders", "order"),
         'value'   => false,
         'foreach' => true,
      ]);

      $this->addTagToList([
         'tag'    => 'orderitems',
         'label'   => __("Order items"),
         'value'   => false,
         'foreach' => true,
      ]);

      asort($this->tag_descriptions);
   }


   public static function install(Migration $migration) {
      global $DB;

      $migration->displayMessage("Migrate PluginOrderOrder notifications");

      $template    = new NotificationTemplate();
      $translation  = new NotificationTemplateTranslation();
      $notification = new Notification();
      $n_n_template = new Notification_NotificationTemplate();

      $templates_id = false;
      $query_id    = "SELECT `id`
                  FROM `glpi_notificationtemplates`
                  WHERE `itemtype`='PluginOrderOrder'
                  AND `name` = 'Order Validation'";
      $result      = $DB->query($query_id) or die ($DB->error());

      if ($DB->numrows($result) > 0) {
         $templates_id = $DB->result($result, 0, 'id');
      } else {
         $tmp = [
         'name'    => 'Order Validation',
         'itemtype' => 'PluginOrderOrder',
         'date_mod' => $_SESSION['glpi_currenttime'],
         'comment'  => '',
         'css'     => '',
         ];
         $templates_id = $template->add($tmp);
      }

      if ($templates_id) {
         if (!countElementsInTable($translation->getTable(), ['notificationtemplates_id' => $templates_id])) {
            $tmp = [];
            $tmp['notificationtemplates_id'] = $templates_id;
            $tmp['language']             = '';
            $tmp['subject']              = '##lang.ordervalidation.title##';
            $tmp['content_text']          = '##lang.ordervalidation.url## : ##ordervalidation.url##
            ##lang.ordervalidation.entity## : ##ordervalidation.entity##
            ##IFordervalidation.name####lang.ordervalidation.name## : ##ordervalidation.name##
            ##ENDIFordervalidation.name##
            ##IFordervalidation.numorder####lang.ordervalidation.numorder## : ##ordervalidation.numorder##
            ##ENDIFordervalidation.numorder##
            ##IFordervalidation.orderdate####lang.ordervalidation.orderdate##  : ##ordervalidation.orderdate####ENDIFordervalidation.orderdate##
            ##IFordervalidation.state####lang.ordervalidation.state## : ##ordervalidation.state####ENDIFordervalidation.state##
            ##IFordervalidation.users####lang.ordervalidation.users## : ##ordervalidation.users####ENDIFordervalidation.users##

            ##IFordervalidation.comment####lang.ordervalidation.comment## : ##ordervalidation.comment####ENDIFordervalidation.comment##';
            $tmp['content_html']          = '&lt;p&gt;&lt;strong&gt;##lang.ordervalidation.url##&lt;/strong&gt; : ' .
              '&lt;a href=\"##ordervalidation.url##\"&gt;##ordervalidation.url##&lt;/a&gt;&lt;br /&gt;' .
              '&lt;br /&gt;&lt;strong&gt;##lang.ordervalidation.entity##&lt;/strong&gt; : ##ordervalidation.entity##&lt;br /&gt;' .
              ' ##IFordervalidation.name##&lt;strong&gt;##lang.ordervalidation.name##&lt;/strong&gt;' .
              ' : ##ordervalidation.name####ENDIFordervalidation.name##&lt;br /&gt;' .
              '##IFordervalidation.numorder##&lt;strong&gt;##lang.ordervalidation.numorder##&lt;/strong&gt;' .
              ' : ##ordervalidation.numorder####ENDIFordervalidation.numorder##&lt;br /&gt;##IFordervalidation.orderdate##&lt;strong&gt;##lang.ordervalidation.orderdate##&lt;/strong&gt;' .
              ' : ##ordervalidation.orderdate####ENDIFordervalidation.orderdate##&lt;br /&gt;' .
              '##IFordervalidation.state##&lt;strong&gt;##lang.ordervalidation.state##&lt;/strong&gt;' .
              ' : ##ordervalidation.state####ENDIFordervalidation.state##&lt;br /&gt;' .
              '##IFordervalidation.users##&lt;strong&gt;##lang.ordervalidation.users##&lt;/strong&gt;' .
              ' : ##ordervalidation.users####ENDIFordervalidation.users##&lt;br /&gt;&lt;br /&gt;' .
              '##IFordervalidation.comment##&lt;strong&gt;##lang.ordervalidation.comment##&lt;/strong&gt; : ##ordervalidation.comment####ENDIFordervalidation.comment##&lt;/p&gt;';
            $translation->add($tmp);
         }

         $notifs = [
         'New Order Validation'    => 'ask',
         'Confirm Order Validation' => 'validation',
         'Cancel Order Validation'  => 'undovalidation',
         'Cancel Order'          => 'cancel',
         ];
         foreach ($notifs as $label => $name) {
            if (!countElementsInTable("glpi_notifications", ['itemtype' => 'PluginOrderOrder', 'event' => $name])) {
               $notification_id = $notification->add([
               'name'                => $label,
               'entities_id'           => 0,
               'itemtype'             => 'PluginOrderOrder',
               'event'               => $name,
               'comment'              => '',
               'is_recursive'          => 1,
               'is_active'            => 1,
               'date_mod'             => $_SESSION['glpi_currenttime'],
               ]);

               $n_n_template->add(
                [
                'notifications_id'       => $notification_id,
                'mode'                => Notification_NotificationTemplate::MODE_MAIL,
                'notificationtemplates_id' => $templates_id,
                ]
               );
            }
         }
      }

      $query_id    = "SELECT `id`
                  FROM `glpi_notificationtemplates`
                  WHERE `itemtype`='PluginOrderOrder'
                  AND `name` = 'Due date overtaken'";
      $result      = $DB->query($query_id) or die ($DB->error());

      if ($DB->numrows($result) > 0) {
         $templates_id = $DB->result($result, 0, 'id');
      } else {
         $templates_id = $template->add([
         'name'    => 'Due date overtaken',
         'itemtype' => 'PluginOrderOrder',
         'date_mod' => $_SESSION['glpi_currenttime'],
         'comment'  => '',
         'css'     => '',
         ]);
      }

      if ($templates_id) {
         if (!countElementsInTable($translation->getTable(), ['notificationtemplates_id' => $templates_id])) {
            $tmp = [];
            $tmp['notificationtemplates_id'] = $templates_id;
            $tmp['language']    = '';
            $tmp['subject']     = '##order.action## ##order.entity##';
            $tmp['content_text'] = '##lang.order.entity## : ##order.entity##\n' .
                           ' \n##FOREACHorders##\n' .
                           '##lang.order.item.name## : ##order.item.name##\n ' .
                           '##lang.order.item.numorder## : ##order.item.numorder##\n ' .
                           '##lang.order.item.orderdate## : ##order.item.orderdate##\n ' .
                           '##lang.order.item.duedate## : ##order.item.duedate##\n ' .
                           '##lang.order.item.deliverydate## : ##order.item.deliverydate##\n ' .
                           '##order.item.url## \n ##ENDFOREACHorders##';
            $tmp['content_html'] = "##lang.order.entity## : ##order.entity##&lt;br /&gt; " .
                           "&lt;br /&gt;##FOREACHorders##&lt;br /&gt;" .
                           "##lang.order.item.name## : ##order.item.name##&lt;br /&gt; " .
                           "##lang.order.item.numorder## : ##order.item.numorder##&lt;br /&gt; " .
                           "##lang.order.item.orderdate## : ##order.item.orderdate##&lt;br /&gt; &lt;a&gt;" .
                           "##lang.order.item.duedate## : ##order.item.duedate##&lt;br /&gt; &lt;/a&gt;&lt;a&gt;" .
                           "##lang.order.item.deliverydate## : ##order.item.deliverydate##&lt;br /&gt; &lt;/a&gt;&lt;a&gt;" .
                           "##order.item.url##&lt;/a&gt;&lt;br /&gt; ##ENDFOREACHorders##";
            $translation->add($tmp);
         }

         $notifs      = ['Due date overtaken' => 'duedate'];
         foreach ($notifs as $label => $name) {
            if (!countElementsInTable("glpi_notifications", ['itemtype' => 'PluginOrderOrder', 'event' => $name])) {
               $notification_id = $notification->add([
               'name'                => $label,
               'entities_id'           => 0,
               'itemtype'             => 'PluginOrderOrder',
               'event'               => $name,
               'comment'              => '',
               'is_recursive'          => 1,
               'is_active'            => 1,
               'date_mod'             => $_SESSION['glpi_currenttime'],
               ]);

               $n_n_template->add(
                [
                'notifications_id'       => $notification_id,
                'mode'                => Notification_NotificationTemplate::MODE_MAIL,
                'notificationtemplates_id' => $templates_id,
                ]
               );
            }
         }
      }

      $templates_id = false;
      $query_id    = "SELECT `id`
                  FROM `glpi_notificationtemplates`
                  WHERE `itemtype`='PluginOrderOrder'
                  AND `name` = 'Order Delivered'";
      $result      = $DB->query($query_id) or die ($DB->error());

      if ($DB->numrows($result) > 0) {
         $templates_id = $DB->result($result, 0, 'id');
      } else {
         $templates_id = $template->add([
         'name'    => 'Order Delivered',
         'itemtype' => 'PluginOrderOrder',
         'date_mod' => $_SESSION['glpi_currenttime'],
         'comment'  => '',
         'css'     => '',
         ]);
      }

      if ($templates_id) {
         if (!countElementsInTable($translation->getTable(), ['notificationtemplates_id' => $templates_id])) {
            $tmp = [];
            $tmp['notificationtemplates_id'] = $templates_id;
            $tmp['language']             = '';
            $tmp['subject']              = '##order.action## ##ordervalidation.name## ##ordervalidation.numorder##';
            $tmp['content_text']          = '##order.action##
##lang.ordervalidation.name## :
##ordervalidation.name##
##lang.ordervalidation.orderdate## :
##ordervalidation.orderdate##
##lang.ordervalidation.entity## :
##ordervalidation.entity##';
            $tmp['content_html']          = '&lt;p&gt;##order.action## &lt;br /&gt;&lt;br /&gt;&#160;
            ##lang.ordervalidation.name## : &lt;br /&gt;&#160;
            ##ordervalidation.name## &lt;br /&gt;&#160;
            ##lang.ordervalidation.orderdate## : &lt;br /&gt;&#160;
            ##ordervalidation.orderdate## &lt;br /&gt;&#160; &lt;br /&gt;&#160;
            ##lang.ordervalidation.entity## : &lt;br /&gt;&#160;##ordervalidation.entity##&lt;/p&gt;';
            $translation->add($tmp);
         }

         $notifs = ['Order Delivered' => 'delivered'];
         foreach ($notifs as $label => $name) {
            if (!countElementsInTable("glpi_notifications", ['itemtype' => 'PluginOrderOrder', 'event' => $name])) {
               $notification_id = $notification->add([
               'name'                => $label,
               'entities_id'           => 0,
               'itemtype'             => 'PluginOrderOrder',
               'event'               => $name,
               'comment'              => '',
               'is_recursive'          => 1,
               'is_active'            => 1,
               'date_mod'             => $_SESSION['glpi_currenttime'],
               ]);

               $n_n_template->add(
                [
                'notifications_id'       => $notification_id,
                'mode'                => Notification_NotificationTemplate::MODE_MAIL,
                'notificationtemplates_id' => $templates_id,
                ]
               );
            }
         }
      }
   }


   public static function uninstall() {
      global $DB;

      $notif = new Notification();

      foreach (['ask', 'validation', 'cancel', 'undovalidation', 'duedate', 'delivered'] as $event) {
         $options = [
         'itemtype' => 'PluginOrderOrder',
         'event'   => $event,
         'FIELDS'   => 'id',
         ];
         foreach ($DB->request('glpi_notifications', $options) as $data) {
            $notif->delete($data);
         }
      }

      //templates
      $template   = new NotificationTemplate();
      $translation = new NotificationTemplateTranslation();
      $options    = [
       'itemtype' => 'PluginOrderOrder',
       'FIELDS'   => 'id'
      ];

      foreach ($DB->request('glpi_notificationtemplates', $options) as $data) {
         $options_template = [
         'notificationtemplates_id' => $data['id'],
         'FIELDS'               => 'id'
         ];
         foreach ($DB->request('glpi_notificationtemplatetranslations', $options_template) as $data_template) {
            $translation->delete($data_template);
         }
         $template->delete($data);
      }
   }


   /**
   * Get additionnals targets for Tickets
   **/
   public function addAdditionalTargets($event = '') {
      $this->addTarget(self::AUTHOR, __("Author"));
      $this->addTarget(self::AUTHOR_GROUP, __("Author group", "order"));
      $this->addTarget(self::DELIVERY_USER, __("Recipient"));
      $this->addTarget(self::DELIVERY_GROUP, __("Recipient group", "order"));
      $this->addTarget(self::SUPERVISOR_AUTHOR_GROUP, __("Manager")." ".__("Author group", "order"));
      $this->addTarget(self::SUPERVISOR_DELIVERY_GROUP, __("Manager")." ".__("Recipient group", "order"));
   }


   public function addSpecificTargets($data, $options) {
      switch ($data['items_id']) {
         case self::AUTHOR:
            $this->addUserByField("users_id");
          break;
         case self::DELIVERY_USER:
            $this->addUserByField("users_id_delivery");
          break;
         case self::AUTHOR_GROUP:
            $this->addForGroup(0, $this->obj->fields['groups_id']);
          break;
         case self::DELIVERY_GROUP:
            $this->addForGroup(0, $this->obj->fields['groups_id_delivery']);
          break;
         case self::SUPERVISOR_AUTHOR_GROUP:
            $this->addForGroup(1, $this->obj->fields['groups_id']);
          break;
         case self::SUPERVISOR_DELIVERY_GROUP:
            $this->addForGroup(1, $this->obj->fields['groups_id_delivery']);
          break;
      }
   }

   function getBudgetsName($references_id) {
      global $DB;
      $query = "SELECT name FROM glpi_budgets WHERE id = '$references_id'";
      $result = $DB->query($query);
      $data = $DB->fetch_assoc($result);
      return $data['name'];
   }

   /**
    * Get supplier name
    *
    * @param $order_id          order id
    **/
   function getSupplierName($order_id) {
      global $DB;
      $query = "SELECT glpi_suppliers.name AS name
            FROM glpi_suppliers
            LEFT JOIN glpi_plugin_order_orders
            ON glpi_plugin_order_orders.suppliers_id = glpi_suppliers.id
            WHERE glpi_plugin_order_orders.id = $order_id";

      $result = $DB->request($query)->next();
      return $result["name"];
   }

   /**
    * Get order references data
    **/
   public function getReferenceData($reference_id) {
      global $DB;
      $id_order = $this->obj->fields['id'];
      $query = "SELECT name, itemtype, models_id, manufacturers_reference, types_id
            FROM glpi_plugin_order_references WHERE id = $reference_id;";
      $result = $DB->query($query);
      $data = $DB->fetch_assoc($result);
      return $data;
   }


}
