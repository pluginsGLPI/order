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

// Class NotificationTarget
class PluginOrderNotificationTargetOrder extends NotificationTarget {

   const AUTHOR                    = 30;
   const AUTHOR_GROUP              = 31;
   const DELIVERY_USER             = 32;
   const DELIVERY_GROUP            = 33;
   const SUPERVISOR_AUTHOR_GROUP   = 34;
   const SUPERVISOR_DELIVERY_GROUP = 35;
    
   function getEvents() {
      global $LANG;
      return array ('ask'          => $LANG['plugin_order']['validation'][1],
                    'validation'     => $LANG['plugin_order']['validation'][2],
                    'cancel'         => $LANG['plugin_order']['validation'][5],
                    'undovalidation' => $LANG['plugin_order']['validation'][8],
                    'duedate'        => $LANG['plugin_order'][55],
                    'delivered'      => $LANG['plugin_order']['delivery'][17]);
   }

   function getDatasForTemplate($event,$options=array()) {
      global $LANG, $CFG_GLPI;
      
      $events = $this->getAllEvents();
      $this->datas['##order.action##'] = $events[$event];
      if ($event == 'duedate') {
         $this->datas['##order.entity##'] = Dropdown::getDropdownName('glpi_entities',
                                                                      $options['entities_id']);
         
         foreach ($options['orders'] as $id => $order) {
            $tmp = array();
            $tmp['##order.item.name##']      = $order['name'];
            $tmp['##order.item.numorder##']  = $order['num_order'];
            $tmp['##order.item.url##']       = rawurldecode($CFG_GLPI["url_base"].
                                                    "/index.php?redirect=plugin_order_order_".$id);
            $tmp['##order.item.orderdate##'] = Html::convDate($order["order_date"]);
            $tmp['##order.item.duedate##']   = Html::convDate($order["duedate"]);
            $tmp['##order.item.deliverydate##']  = Html::convDate($order["deliverydate"]);
            $tmp['##order.item.comment##']   = Html::clean($order["comment"]);
            $tmp['##order.item.state##']   = Dropdown::getDropdownName('glpi_plugin_order_orderstates',
                                                                       $order["plugin_order_orderstates_id"]);
            $this->datas['orders'][] = $tmp;
         }
   
         $this->getTags();
         foreach ($this->tag_descriptions[NotificationTarget::TAG_LANGUAGE] as $tag => $values) {
            if (!isset($this->datas[$tag])) {
               $this->datas[$tag] = $values['label'];
            }
         }
      	
      } else {
         $this->datas['##lang.ordervalidation.title##'] = $events[$event];
         
         $this->datas['##lang.ordervalidation.entity##'] = $LANG['entity'][0];
         $this->datas['##ordervalidation.entity##'] =
                              Dropdown::getDropdownName('glpi_entities',
                                                        $this->obj->getField('entities_id'));
                                                        
         $this->datas['##lang.ordervalidation.name##'] = $LANG['common'][16];
         $this->datas['##ordervalidation.name##'] = $this->obj->getField("name");
         
         $this->datas['##lang.ordervalidation.numorder##'] = $LANG['financial'][18];
         $this->datas['##ordervalidation.numorder##'] = $this->obj->getField("num_order");
         
         $this->datas['##lang.ordervalidation.orderdate##'] = $LANG['plugin_order'][1];
         $this->datas['##ordervalidation.orderdate##'] = Html::convDate($this->obj->getField("order_date"));
         
         $this->datas['##lang.ordervalidation.state##'] = $LANG['joblist'][0];
         $this->datas['##ordervalidation.state##'] =
                              Dropdown::getDropdownName("glpi_plugin_order_orderstates",
                                                         $this->obj->getField("plugin_order_orderstates_id"));
         
         $this->datas['##lang.ordervalidation.comment##'] = $LANG['plugin_order']['validation'][18];
         $comment = Toolbox::stripslashes_deep(str_replace(array('\r\n', '\n', '\r'), "<br/>", $options['comments']));
         $this->datas['##ordervalidation.comment##'] = nl2br($comment);
         
         switch ($event) {
            case "ask" :
               $this->datas['##lang.ordervalidation.users##'] = $LANG['plugin_order']['validation'][1] .
                                                            " " . $LANG['plugin_order']['mailing'][2];
               break;
            case "validation" :
               $this->datas['##lang.ordervalidation.users##'] = $LANG['plugin_order']['validation'][10] .
                                                            " " . $LANG['plugin_order']['mailing'][2];
               break;
            case "cancel" :
               $this->datas['##lang.ordervalidation.users##'] = $LANG['plugin_order']['validation'][5] .
                                                            " " . $LANG['plugin_order']['mailing'][2];
               break;
            case "undovalidation" :
               $this->datas['##lang.ordervalidation.users##'] = $LANG['plugin_order']['validation'][16] .
                                                            " " . $LANG['plugin_order']['mailing'][2];
               break;
            case "delivered" :
               $this->datas['##lang.ordervalidation.users##'] = $LANG['plugin_order']['delivery'][17];
               break;
         }
         $this->datas['##ordervalidation.users##'] =  Html::clean(getUserName(Session::getLoginUserID()));

         $this->datas['##order.author.name##']       =  Html::clean(getUserName($this->obj->getField('users_id')));
         $this->datas['##order.deliveryuser.name##'] =  Html::clean(getUserName($this->obj->getField('users_id_delivery')));
         
         $this->datas['##lang.ordervalidation.url##'] = "URL";
         $url = $CFG_GLPI["url_base"]."/index.php?redirect=plugin_order_order_".$this->obj->getField("id");
         $this->datas['##ordervalidation.url##'] = urldecode($url);

      }
   }
   
   function getTags() {
      global $LANG;

      $tags = array('ordervalidation.name'        => $LANG['common'][16],
                    'ordervalidation.numorder'    => $LANG['financial'][18],
                    'ordervalidation.orderdate'   => $LANG['plugin_order'][1],
                    'ordervalidation.state'       => $LANG['joblist'][0],
                    'ordervalidation.comment'     => $LANG['plugin_order']['validation'][18],
                    'ordervalidation.users'       => $LANG['plugin_order']['validation'][19],
                    'order.entity'                => $LANG['plugin_order'][53],
                    'order.item.name'             => $LANG['common'][16],
                    'order.item.state'            => $LANG['joblist'][0],
                    'order.item.numorder'         => $LANG['financial'][18],
                    'order.item.orderdate'        => $LANG['plugin_order'][1],
                    'order.item.duedate'          => $LANG['plugin_order'][50],
                    'order.item.deliverydate'     => $LANG['plugin_order'][53],
                    'order.item.comment'          => $LANG['common'][25],
                    'order.author.name'           => $LANG['plugin_order'][56],
                    'order.author.phone'          => $LANG['plugin_order'][56].' - '.$LANG['help'][35],
                    'order.deliveryuser.name'     => $LANG['plugin_order'][58],
                    'order.deliveryuser.phone'    => $LANG['plugin_order'][58].' - '.$LANG['help'][35]);

      foreach ($tags as $tag => $label) {
         $this->addTagToList(array('tag' => $tag, 'label' => $label, 'value' => true));
      }
      
      $this->addTagToList(array('tag' => 'order.action', 'label' => $LANG['rulesengine'][30],
                                'value' => false));
      
     $this->addTagToList(array('tag'     => 'orders',
                                'label'   => $LANG['plugin_order'][55],
                                'value'   => false,
                                'foreach' => true));

      asort($this->tag_descriptions);
   }
   
   static function install(Migration $migration) {
      global $DB;

      $migration->displayMessage("Migrate PluginOrderOrder notifications");
      
      $template     = new NotificationTemplate();
      $templates_id = false;
      $query_id     = "SELECT `id`
                       FROM `glpi_notificationtemplates`
                       WHERE `itemtype`='PluginOrderOrder'
                          AND `name` = 'Order Validation'";
      $result       = $DB->query($query_id) or die ($DB->error());
      if ($DB->numrows($result) > 0) {
         $templates_id = $DB->result($result, 0, 'id');
         
      } else {
         $tmp = array('name' => 'Order Validation', 'itemtype' => 'PluginOrderOrder',
                      'date_mod' => $_SESSION['glpi_currenttime'], 'comment' => '',  'css' => '');
         $templates_id = $template->add($tmp);
      }
      
      if ($templates_id) {
         $translation = new NotificationTemplateTranslation();
         if (!countElementsInTable($translation->getTable(), "`notificationtemplates_id`='$templates_id'")) {
            $tmp['notificationtemplates_id'] = $templates_id;
            $tmp['language'] = '';
            $tmp['subject'] = '##lang.ordervalidation.title##';
            $tmp['content_text'] = '##lang.ordervalidation.url## : ##ordervalidation.url##
            ##lang.ordervalidation.entity## : ##ordervalidation.entity##
            ##IFordervalidation.name####lang.ordervalidation.name## : ##ordervalidation.name##
            ##ENDIFordervalidation.name##
            ##IFordervalidation.numorder####lang.ordervalidation.numorder## : ##ordervalidation.numorder##
            ##ENDIFordervalidation.numorder##
            ##IFordervalidation.orderdate####lang.ordervalidation.orderdate##  : ##ordervalidation.orderdate####ENDIFordervalidation.orderdate##
            ##IFordervalidation.state####lang.ordervalidation.state## : ##ordervalidation.state####ENDIFordervalidation.state##
            ##IFordervalidation.users####lang.ordervalidation.users## : ##ordervalidation.users####ENDIFordervalidation.users##
            
            ##IFordervalidation.comment####lang.ordervalidation.comment## : ##ordervalidation.comment####ENDIFordervalidation.comment##';
            $tmp['content_html'] = '&lt;p&gt;&lt;strong&gt;##lang.ordervalidation.url##&lt;/strong&gt; : ' .
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
   
         $notifs = array('New Order Validation' => 'ask', 'Confirm Order Validation' => 'validation',
                         'Cancel Order Validation' => 'undovalidation', 'Cancel Order' => 'cancel');
         $notification = new Notification();
         foreach ($notifs as $label => $name) {
            if (!countElementsInTable("glpi_notifications", "`itemtype`='PluginOrderOrder' " .
                                         "AND `event`='$name'")) {
               $tmp = array('name' => $label, 'entities_id' => 0, 'itemtype' => 'PluginOrderOrder',
                            'event' => $name, 'mode' => 'mail', 'comment' => '',
                            'is_recursive' => 1, 'is_active' => 1,
                            'date_mod' => $_SESSION['glpi_currenttime'],
                            'notificationtemplates_id' => $templates_id);
                $notification->add($tmp);
            }
         }
      }
      
      $query_id     = "SELECT `id`
                       FROM `glpi_notificationtemplates`
                       WHERE `itemtype`='PluginOrderOrder'
                          AND `name` = 'Due date overtaken'";
      $result       = $DB->query($query_id) or die ($DB->error());
      if ($DB->numrows($result) > 0) {
         $templates_id = $DB->result($result, 0, 'id');
         
      } else {
         $tmp = array('name' => 'Due date overtaken', 'itemtype' => 'PluginOrderOrder',
                      'date_mod' => $_SESSION['glpi_currenttime'], 'comment' => '',  'css' => '');
         $templates_id = $template->add($tmp);
      }
      
      if ($templates_id) {
         $translation = new NotificationTemplateTranslation();
         if (!countElementsInTable($translation->getTable(), "`notificationtemplates_id`='$templates_id'")) {
            $tmp                 = array();
            $tmp['notificationtemplates_id'] = $templates_id;
            $tmp['language']     = '';
            $tmp['subject']      = '##order.action## ##order.entity##';
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

         $notifs       = array('Due date overtaken' => 'duedate');
         $notification = new Notification();
         foreach ($notifs as $label => $name) {
            if (!countElementsInTable("glpi_notifications", "`itemtype`='PluginOrderOrder' " .
                                         "AND `event`='$name'")) {
               $tmp = array('name' => $label, 'entities_id' => 0, 'itemtype' => 'PluginOrderOrder',
                            'event' => $name, 'mode' => 'mail', 'comment' => '',
                            'is_recursive' => 1, 'is_active' => 1,
                            'date_mod' => $_SESSION['glpi_currenttime'],
                            'notificationtemplates_id' => $templates_id);
                $notification->add($tmp);
            }
         }
      }
         
      $template     = new NotificationTemplate();
      $templates_id = false;
      $query_id     = "SELECT `id`
                       FROM `glpi_notificationtemplates`
                       WHERE `itemtype`='PluginOrderOrder'
                          AND `name` = 'Order Delivered'";
      $result       = $DB->query($query_id) or die ($DB->error());
      if ($DB->numrows($result) > 0) {
         $templates_id = $DB->result($result, 0, 'id');
         
      } else {
         $tmp = array('name' => 'Order Delivered', 'itemtype' => 'PluginOrderOrder',
                      'date_mod' => $_SESSION['glpi_currenttime'], 'comment' => '',  'css' => '');
         $templates_id = $template->add($tmp);
      }
      
      if ($templates_id) {
         $translation = new NotificationTemplateTranslation();
         if (!countElementsInTable($translation->getTable(), "`notificationtemplates_id`='$templates_id'")) {
            $tmp['notificationtemplates_id'] = $templates_id;
            $tmp['language'] = '';
            $tmp['subject'] = '##order.action## ##ordervalidation.name## ##ordervalidation.numorder##';
            $tmp['content_text'] = '##order.action##
##lang.ordervalidation.name## :
##ordervalidation.name##
##lang.ordervalidation.orderdate## :
##ordervalidation.orderdate##
##lang.ordervalidation.entity## :
##ordervalidation.entity##';
            $tmp['content_html'] = '&lt;p&gt;##order.action## &lt;br /&gt;&lt;br /&gt;&#160;
                ##lang.ordervalidation.name## : &lt;br /&gt;&#160;
                ##ordervalidation.name## &lt;br /&gt;&#160;
                ##lang.ordervalidation.orderdate## : &lt;br /&gt;&#160;
                ##ordervalidation.orderdate## &lt;br /&gt;&#160; &lt;br /&gt;&#160;
                ##lang.ordervalidation.entity## : &lt;br /&gt;&#160;##ordervalidation.entity##&lt;/p&gt;';
            $translation->add($tmp);
         }
      }
      
      $notifs = array('Order Delivered' => 'delivered');
      $notification = new Notification();
      foreach ($notifs as $label => $name) {
         if (!countElementsInTable("glpi_notifications", "`itemtype`='PluginOrderOrder' " .
                                      "AND `event`='$name'")) {
            $tmp = array('name' => $label, 'entities_id' => 0, 'itemtype' => 'PluginOrderOrder',
                         'event' => $name, 'mode' => 'mail', 'comment' => '',
                         'is_recursive' => 1, 'is_active' => 1,
                         'date_mod' => $_SESSION['glpi_currenttime'],
                         'notificationtemplates_id' => $templates_id);
             $notification->add($tmp);
         }
      }
   }
   
   static function uninstall() {
      global $DB;

      $notif = new Notification();

      foreach (array('ask', 'validation', 'cancel', 'undovalidation', 'duedate', 'delivered') as $event) {
         $options = array('itemtype' => 'PluginOrderOrder',
                          'event'    => $event,
                          'FIELDS'   => 'id');
         foreach ($DB->request('glpi_notifications', $options) as $data) {
            $notif->delete($data);
         }
      }
      
      //templates
      $template    = new NotificationTemplate();
      $translation = new NotificationTemplateTranslation();
      $options     = array('itemtype' => 'PluginOrderOrder', 'FIELDS'   => 'id');
      foreach ($DB->request('glpi_notificationtemplates', $options) as $data) {
         $options_template = array('notificationtemplates_id' => $data['id'], 'FIELDS'   => 'id');
         foreach ($DB->request('glpi_notificationtemplatetranslations',
                               $options_template) as $data_template) {
            $translation->delete($data_template);
         }
         $template->delete($data);
      }
      
   }

   /**
    * Get additionnals targets for Tickets
   **/
   function getAdditionalTargets($event='') {
      global $LANG;
      $this->addTarget(self::AUTHOR, $LANG['plugin_order'][56]);
      $this->addTarget(self::AUTHOR_GROUP, $LANG['plugin_order'][57]);
      $this->addTarget(self::DELIVERY_USER, $LANG['plugin_order'][58]);
      $this->addTarget(self::DELIVERY_GROUP, $LANG['plugin_order'][59]);
      $this->addTarget(self::SUPERVISOR_AUTHOR_GROUP,
                       $LANG['common'][64]." ".$LANG['plugin_order'][57]);
      $this->addTarget(self::SUPERVISOR_DELIVERY_GROUP,
                       $LANG['common'][64]." ".$LANG['plugin_order'][59]);
   }

   function getSpecificTargets($data, $options) {
      switch ($data['items_id']) {
         case self::AUTHOR:
            $this->getUserByField ("users_id");
            break;
         case self::DELIVERY_USER:
            $this->getUserByField ("users_id_delivery");
            break;
         case self::AUTHOR_GROUP:
            $this->getAddressesByGroup(0, $this->obj->fields['groups_id']);
            break;
         case self::DELIVERY_GROUP:
            $this->getAddressesByGroup(0, $this->obj->fields['groups_id_delivery']);
            break;
         case self::SUPERVISOR_AUTHOR_GROUP:
            $this->getAddressesByGroup(1, $this->obj->fields['groups_id']);
            break;
         case self::SUPERVISOR_DELIVERY_GROUP:
            $this->getAddressesByGroup(1, $this->obj->fields['groups_id_delivery']);
            break;
      }
   }
}

?>