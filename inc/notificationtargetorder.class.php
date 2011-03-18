<?php
/*
 * @version $Id: HEADER 1 2010-03-03 21:49 Tsmr $
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
// Purpose of file: plugin order v1.3.0 - GLPI 0.78.3
// ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

// Class NotificationTarget
class PluginOrderNotificationTargetOrder extends NotificationTarget {

   function getEvents() {
      global $LANG;
      return array ('ask'              => $LANG['plugin_order']['validation'][1],
                    'validation'       => $LANG['plugin_order']['validation'][2],
                    'cancel'           => $LANG['plugin_order']['validation'][5],
                    'undovalidation'   => $LANG['plugin_order']['validation'][8]);
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
         PluginOrderOrder::getState($this->obj->getField("states_id"));
      
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

      $tags = array('ordervalidation.name'         => $LANG['common'][16],
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
}

?>