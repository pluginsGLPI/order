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

// Class NotificationTarget
class PluginOrderNotificationTargetOrder extends NotificationTarget {

   function getEvents() {
      global $LANG;
      return array ('ask'            => $LANG['plugin_order']['validation'][1],
                    'validation'     => $LANG['plugin_order']['validation'][2],
                    'cancel'         => $LANG['plugin_order']['validation'][5],
                    'undovalidation' => $LANG['plugin_order']['validation'][8]);
   }

   function getDatasForTemplate($event,$options=array()) {
      global $LANG, $CFG_GLPI;
      
      $events = $this->getAllEvents();

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
      $this->datas['##ordervalidation.orderdate##'] = convDate($this->obj->getField("order_date"));
      
      $this->datas['##lang.ordervalidation.state##'] = $LANG['joblist'][0];
      $this->datas['##ordervalidation.state##'] =  
                           Dropdown::getDropdownName("glpi_plugin_order_orderstates", 
                                                      $this->fields["plugin_order_orderstates_id"]);
      
      $this->datas['##lang.ordervalidation.comment##'] = $LANG['plugin_order']['validation'][18];
      $comment = stripslashes(str_replace(array('\r\n', '\n', '\r'), "<br/>", $options['comments']));
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
      }
      $this->datas['##ordervalidation.users##'] =  html_clean(getUserName(getLoginUserID()));
      
      $this->datas['##lang.ordervalidation.url##'] = "URL";
      $url = $CFG_GLPI["url_base"]."/index.php?redirect=plugin_order_order_".$this->obj->getField("id");
      $this->datas['##ordervalidation.url##'] = urldecode($url);

   }
   
   function getTags() {
      global $LANG;

      $tags = array('ordervalidation.name'        => $LANG['common'][16],
                    'ordervalidation.numorder'    => $LANG['financial'][18],
                    'ordervalidation.orderdate'   => $LANG['plugin_order'][1],
                    'ordervalidation.state'       => $LANG['joblist'][0],
                    'ordervalidation.comment'     => $LANG['plugin_order']['validation'][18],
                    'ordervalidation.users'       => $LANG['plugin_order']['validation'][19]);

      foreach ($tags as $tag => $label) {
         $this->addTagToList(array('tag'=>$tag, 'label'=>$label, 'value'=>true));
      }

      asort($this->tag_descriptions);
   }
   
   static function install(Migration $migration) {
      global $DB;

      $migration->displayMessage("Migrate PluginOrderOrder notifications");
      
      $template     = new NotificationTemplate();
      $templates_id = false;
      $query_id     = "SELECT `id` FROM `glpi_notificationtemplates` 
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
   }
   
   static function uninstall() {
      global $DB;

      $notif = new Notification();
      $options = array('itemtype' => 'PluginOrderOrder',
                       'event'    => 'ask',
                       'FIELDS'   => 'id');
      foreach ($DB->request('glpi_notifications', $options) as $data) {
         $notif->delete($data);
      }
      $options = array('itemtype' => 'PluginOrderOrder',
                       'event'    => 'validation',
                       'FIELDS'   => 'id');
      foreach ($DB->request('glpi_notifications', $options) as $data) {
         $notif->delete($data);
      }
      $options = array('itemtype' => 'PluginOrderOrder',
                       'event'    => 'cancel',
                       'FIELDS'   => 'id');
      foreach ($DB->request('glpi_notifications', $options) as $data) {
         $notif->delete($data);
      }
      $options = array('itemtype' => 'PluginOrderOrder',
                       'event'    => 'undovalidation',
                       'FIELDS'   => 'id');
      foreach ($DB->request('glpi_notifications', $options) as $data) {
         $notif->delete($data);
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
}

?>