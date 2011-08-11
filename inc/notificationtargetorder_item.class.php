<?php
/*
 * @version $Id: HEADER 2011-03-23 15:41:26 tsmr $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

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
 Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 --------------------------------------------------------------------------
// ----------------------------------------------------------------------
// Original Authors of file: 
// NOUH Walid & FONTAN Benjamin & CAILLAUD Xavier & François Legastelois
// Purpose of file: 
// ----------------------------------------------------------------------
// ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

// Class NotificationTarget
class PluginOrderNotificationTargetOrder_Item extends NotificationTarget {

   function getEvents() {
      global $LANG;
      return array ('delivered' => $LANG['plugin_order']['delivery'][5]);
   }

   function getDatasForTemplate($event,$options=array()) {
      global $LANG, $CFG_GLPI;

      $events = $this->getAllEvents();
      
      //--------------------------------------------------//
      //----------------- Notification -------------------//
      //--------------------------------------------------//
      // TITLE
      $this->datas['##lang.reception.title##'] = $events[$event];
      
      //--------------------------------------------------//
      //--------------------- Order ----------------------//
      //--------------------------------------------------//
      $order = new PluginOrderOrder;
      $order->getFromDB($this->obj->getField("plugin_order_orders_id"));

      // ordername
      $this->datas['##lang.reception.ordername##'] = $LANG['plugin_order'][39];
      $this->datas['##reception.ordername##'] = $order->getField('name');

      // orderentity
      $this->datas['##lang.reception.orderentity##'] = $LANG['entity'][0];
      $this->datas['##reception.orderentity##'] =
               Dropdown::getDropdownName('glpi_entities',
                                          $order->getField('entities_id'));
      
      // ordernumber
      $this->datas['##lang.reception.ordernumber##'] = $LANG['financial'][18];
      $this->datas['##reception.ordernumber##'] = $order->getField('num_order');

      // orderdate
      $this->datas['##lang.reception.orderdate##'] = $LANG['plugin_order'][1];
      $this->datas['##reception.orderdate##'] = convDate($order->getField("order_date"));
      
      // orderlocation
      $this->datas['##lang.reception.orderlocation##'] = $LANG['plugin_order'][40];
      $this->datas['##reception.orderlocation##'] =
               Dropdown::getDropdownName('glpi_locations',
                                          $order->getField('locations_id'));

      // orderbudget
      $this->datas['##lang.reception.orderbudget##'] = $LANG['financial'][87];
      $this->datas['##reception.orderbudget##'] =
               Dropdown::getDropdownName('glpi_budgets',
                                          $order->getField('budgets_id'));

      // ordertaxe
      $this->datas['##lang.reception.ordertaxe##'] = $LANG['plugin_order'][25];
      $this->datas['##reception.ordertaxe##'] =
               Dropdown::getDropdownName('glpi_plugin_order_ordertaxes',
                                          $order->getField('plugin_order_ordertaxes_id'));

      // orderpayment
      $this->datas['##lang.reception.orderpayment##'] = $LANG['plugin_order'][32];
      $this->datas['##reception.orderpayment##'] =
               Dropdown::getDropdownName('glpi_plugin_order_orderpayments',
                                          $order->getField('plugin_order_orderpayments_id'));

      // ordersupplier
      $this->datas['##lang.reception.ordersupplier##'] = $LANG['financial'][26];
      $this->datas['##reception.ordersupplier##'] =
               Dropdown::getDropdownName('glpi_suppliers',
                                          $order->getField('suppliers_id'));

      // ordercontact
      $this->datas['##lang.reception.ordercontact##'] = $LANG['common'][92];
      $this->datas['##reception.ordercontact##'] =
               Dropdown::getDropdownName('glpi_contacts',
                                          $order->getField('contacts_id'));

      // orderstate
      $this->datas['##lang.reception.orderstate##'] = $LANG['joblist'][0];
      $this->datas['##reception.orderstate##'] =  
               Dropdown::getDropdownName("glpi_plugin_order_orderstates", 
                                          $order->getField('plugin_order_orderstates_id'));

      // orderport
      $this->datas['##lang.reception.orderport##'] = $LANG['plugin_order'][26];
      $this->datas['##reception.orderport##'] = formatNumber($order->getField('port_price'));

      // ordercomment
      $this->datas['##lang.reception.ordercomment##'] = $LANG['common'][25];
      $comment = stripslashes(str_replace(array('\r\n', '\n', '\r'), 
                                 "<br/>", $order->getField('comment')));
      $this->datas['##reception.ordercomment##'] = nl2br($comment);

      // ordernote
      $this->datas['##lang.reception.ordernote##'] = $LANG['title'][37];
      $notepad = stripslashes(str_replace(array('\r\n', '\n', '\r'), 
                                 "<br/>", $order->getField('notepad')));
      $this->datas['##reception.ordernote##'] = nl2br($notepad);

      // orderurl
      $this->datas['##lang.reception.orderurl##'] = "URL " . 
               $LANG['plugin_order']['detail'][2];
      $url = $CFG_GLPI["url_base"]."/index.php?redirect=plugin_order_order_" . 
               $order->getField("id")."_5";
      $this->datas['##reception.orderurl##'] = urldecode($url);
      
      //--------------------------------------------------//
      //-------------------- Delivery --------------------//
      //--------------------------------------------------//
      $delivery_date    = $this->obj->getField("delivery_date");
      $delivery_number  = $this->obj->getField("delivery_number");
      $delivery_state   = $this->obj->getField("plugin_order_deliverystates_id");

      // deliverydate
      $this->datas['##lang.reception.deliverydate##'] = $LANG['plugin_order']['detail'][21];
      $this->datas['##reception.deliverydate##'] = (!empty($delivery_date)) 
                                                      ? convDate($delivery_date) 
                                                      : 'N/A';

      // deliverynumber
      $this->datas['##lang.reception.deliverynumber##'] = $LANG['financial'][19];
      $this->datas['##reception.deliverynumber##'] = (!empty($delivery_number)) 
                                                         ? $delivery_number 
                                                         : 'N/A';

      // deliverystate
      $this->datas['##lang.reception.deliverystate##'] = $LANG['plugin_order']['status'][3];
      $this->datas['##reception.deliverystate##'] = 
            (!empty($delivery_state)) 
               ? Dropdown::getDropdownName('glpi_plugin_order_deliverystates',$delivery_state)
               : 'N/A';

      // deliveryurl
      $this->datas['##lang.reception.deliveryurl##'] = "URL ".$LANG['plugin_order'][6];
      $url = $CFG_GLPI["url_base"]."/index.php?redirect=plugin_order_reception_" .
               $this->obj->getField("id");
      $this->datas['##reception.deliveryurl##'] = urldecode($url);

      //--------------------------------------------------//
      //----------- DeliveryReference --------------------//
      //--------------------------------------------------//
      $reference = new PluginOrderReference;
      $reference->getFromDB($this->obj->getField("plugin_order_references_id"));

      // deliveryreference_name
      $this->datas['##lang.reception.deliveryreference_name##'] = 
               $LANG['plugin_order']['reference'][1];
      $this->datas['##reception.deliveryreference_name##'] = $reference->getField('name');

      // deliveryreference_itemtype
      $itemtype   = $reference->getField("itemtype"); 
      $item       = new $itemtype();
      $this->datas['##lang.reception.deliveryreference_itemtype##'] = $LANG['state'][6];
      $this->datas['##reception.deliveryreference_itemtype##'] =  $item->getTypeName();

      // deliveryreference_type
      $this->datas['##lang.reception.deliveryreference_type##'] = $LANG['common'][17];
      $this->datas['##reception.deliveryreference_type##'] = 
               Dropdown::getDropdownName(getTableForItemType(
                                          $reference->getField("itemtype")."Type"),
                                          $reference->getField("types_id"));
      
      // deliveryreference_model
      $this->datas['##lang.reception.deliveryreference_model##'] = $LANG['common'][22];
      $this->datas['##reception.deliveryreference_model##'] =
               Dropdown::getDropdownName(getTableForItemType(
                                          $reference->getField("itemtype")."Model"),
                                          $reference->getField("models_id"));

      // deliveryreference_manufacturer
      $this->datas['##lang.reception.deliveryreference_manufacturer##'] = $LANG['common'][5];
      $this->datas['##reception.deliveryreference_manufacturer##'] =
               Dropdown::getDropdownName("glpi_manufacturers", 
                                          $reference->getField("manufacturers_id"));
      
      // deliveryreference_comment
      $this->datas['##lang.reception.deliveryreference_comment##'] = $LANG['common'][25];
      $comment = stripslashes(str_replace(array('\r\n', '\n', '\r'), 
                                 "<br/>", $reference->getField('comment')));
      $this->datas['##reception.deliveryreference_comment##'] = nl2br($comment);
      
      // deliveryreference_note
      $this->datas['##lang.reception.deliveryreference_note##'] = $LANG['title'][37];
      $notepad = stripslashes(str_replace(array('\r\n', '\n', '\r'), 
                                 "<br/>", $reference->getField('notepad')));
      $this->datas['##reception.deliveryreference_note##'] = nl2br($notepad);

      // deliveryreference_url
      $this->datas['##lang.reception.deliveryreference_url##'] = "URL " . 
               $LANG['plugin_order']['detail'][2];
      $url = $CFG_GLPI["url_base"]."/index.php?redirect=plugin_order_reference_" . 
               $reference->getField("id");
      $this->datas['##reception.deliveryreference_url##'] = urldecode($url);
      
      //--------------------------------------------------//
      //---------------- AssociatedItems -----------------//
      //--------------------------------------------------//
      $items_id = $this->obj->getField('items_id');
      if(!empty($items_id)) {
         $itemtype = $this->obj->getField("itemtype");
         $item = new $itemtype();
         $item->getFromDB($items_id);
         
         // associateditems_name
         $this->datas['##lang.reception.associateditems_name##'] = $LANG['common'][16];
         $this->datas['##reception.associateditems_name##'] = $item->getField('name');
         
         // associateditems_serial
         $this->datas['##lang.reception.associateditems_serial##'] = $LANG['common'][19];
         $this->datas['##reception.associateditems_serial##'] = $item->getField('serial');
         
         // associateditems_otherserial
         $this->datas['##lang.reception.associateditems_otherserial##'] = $LANG['common'][20];
         $this->datas['##reception.associateditems_otherserial##'] = 
               $item->getField('otherserial');
         
         // associateditems_state
         $this->datas['##lang.reception.associateditems_state##'] = $LANG['state'][0];
         $this->datas['##reception.associateditems_state##'] = 
               Dropdown::getDropdownName("glpi_manufacturers", 
                                          $item->getField("states_id"));

         // associateditems_url
         $this->datas['##lang.reception.associateditems_url##'] = 
                  "URL ".$LANG['plugin_order']['item'][0];
         $url = $CFG_GLPI["url_base"]."/index.php?redirect=".strtolower($itemtype)."_".$items_id;
         $this->datas['##reception.associateditems_url##'] = urldecode($url);
      }
      
   }
   
   function getTags() {
      global $LANG;

      $tags = array( 
                     // Order
                     'reception.ordername'      => $LANG['plugin_order'][39],
                     'reception.orderentity'    => $LANG['entity'][0],
                     'reception.ordernumber'    => $LANG['financial'][18],
                     'reception.orderdate'      => $LANG['plugin_order'][1],
                     'reception.orderlocation'  => $LANG['plugin_order'][40],
                     'reception.orderbudget'    => $LANG['financial'][87],
                     'reception.ordertaxe'      => $LANG['plugin_order'][25],
                     'reception.orderpayment'   => $LANG['plugin_order'][32],
                     'reception.ordersupplier'  => $LANG['financial'][26],
                     'reception.ordercontact'   => $LANG['common'][92],
                     'reception.orderstate'     => $LANG['joblist'][0],
                     'reception.orderport'      => $LANG['plugin_order'][26],
                     'reception.ordercomment'   => $LANG['common'][25],
                     'reception.ordernote'      => $LANG['title'][37],                     
                  
                     // Delivery
                     'reception.deliverydate'                     
                        => $LANG['plugin_order']['detail'][21],
                     'reception.deliverynumber'                   => $LANG['financial'][19],
                     'reception.deliverystate'                    
                        => $LANG['plugin_order']['status'][3],
                     'reception.deliveryurl'                      
                        => "URL ".$LANG['plugin_order'][6],
                        
                     // DeliveryReference
                     'reception.deliveryreference_name'           
                        => $LANG['plugin_order']['reference'][1],
                     'reception.deliveryreference_itemtype'       => $LANG['state'][6],
                     'reception.deliveryreference_type'          => $LANG['common'][17],
                     'reception.deliveryreference_model'         => $LANG['common'][22],
                     'reception.deliveryreference_manufacturer'  => $LANG['common'][5],
                     'reception.deliveryreference_comment'        => $LANG['common'][25],
                     'reception.deliveryreference_note'        => $LANG['title'][37],
                     'reception.deliveryreference_url'            
                        => "URL ".$LANG['plugin_order']['detail'][2],

                     // AssociatedItems
                     'reception.associateditems_name'          
                        => $LANG['plugin_order']['item'][0]." - ".$LANG['common'][16],
                     'reception.associateditems_serial'
                        => $LANG['plugin_order']['item'][0]." - ".$LANG['common'][19],
                     'reception.associateditems_otherserial'
                        => $LANG['plugin_order']['item'][0]." - ".$LANG['common'][20],
                     'reception.associateditems_state'
                        => $LANG['plugin_order']['item'][0]." - ".$LANG['state'][0],
                     'reception.associateditems_url'           
                        => "URL ".$LANG['plugin_order']['item'][0]
      );

      foreach ($tags as $tag => $label) {
         $this->addTagToList(array('tag'=>$tag, 'label'=>$label, 'value'=>true));
      }

      asort($this->tag_descriptions);
   }
   
   static function install(Migration $migration) {
      global $DB;

      $migration->displayMessage("Migrate PluginOrderOrder_Item notifications");

      $migration->displayMessage("Add order reception notification template");
      $notifications_id = false;
      
      $query_id = "SELECT `id` 
                   FROM `glpi_notificationtemplates` 
                   WHERE `itemtype`='PluginOrderOrder_Item' 
                      AND `name` = 'Order Reception'";
      $result           = $DB->query($query_id) or die ($DB->error());
      
      if (!$DB->numrows($result)) {
         $query = "INSERT INTO `glpi_notificationtemplates` 
                   VALUES (NULL, 'Order Reception', 'PluginOrderOrder_Item', '2011-01-25 15:00:00','',NULL);";
         $DB->query($query) or die ($DB->error());
         $notifications_id = $DB->insert_id();
      } else {
         $notifications_id = $DB->result($result, 0, 'id');
      }
      
      if ($notifications_id) {
         if (!countElementsInTable("glpi_notificationtemplatetranslations", 
                                   "`notificationtemplates_id`='$notifications_id'")) {
            $query="INSERT INTO `glpi_notificationtemplatetranslations`
                     VALUES(NULL, ".$notifications_id.",'','##lang.reception.title##','##lang.reception.title## 
      
      ##reception.orderurl## 
      
      ##lang.reception.ordername## : 
      ##reception.ordername## 
      ##lang.reception.orderdate## : 
      ##reception.orderdate## 
      
      ##lang.reception.orderentity## : 
      ##reception.orderentity## 
      ##lang.reception.orderstate## : 
      ##reception.orderstate## 
      
      ##lang.reception.ordernumber## : 
      ##reception.ordernumber## 
      ##lang.reception.orderbudget## : 
      ##reception.orderbudget## 
      
      ##lang.reception.orderlocation## : 
      ##reception.orderlocation## 
      ##lang.reception.ordertaxe## : 
      ##reception.ordertaxe## 
      
      ##lang.reception.ordersupplier## : 
      ##reception.ordersupplier## 
      ##lang.reception.orderpayment## : 
      ##reception.orderpayment## 
      
      ##lang.reception.ordercontact## : 
      ##reception.ordercontact## 
      ##lang.reception.orderport## : 
      ##reception.orderport## 
      
      ##lang.reception.ordercomment## : 
      ##reception.ordercomment## 
      ##lang.reception.ordernote## : 
      ##reception.ordernote## 
      
      Informations sur la r&#233;f&#233;rence r&#233;ceptionn&#233;e 
      
      ##reception.deliveryreference_url## 
      
      ##lang.reception.deliveryreference_name## : 
      ##reception.deliveryreference_name## 
      ##lang.reception.deliveryreference_itemtype## : 
      ##reception.deliveryreference_itemtype## 
      
      ##lang.reception.deliveryreference_type## : 
      ##reception.deliveryreference_type## 
      ##lang.reception.deliveryreference_model## : 
      ##reception.deliveryreference_model## 
      
      ##lang.reception.deliveryreference_manufacturer## : 
      ##reception.deliveryreference_manufacturer## 
      
      ##lang.reception.deliveryreference_comment## : 
      ##reception.deliveryreference_comment## 
      ##lang.reception.deliveryreference_note## : 
      ##reception.deliveryreference_note## 
      
      Informations sur la r&#233;ception 
      
      ##reception.deliveryurl## 
      
      ##lang.reception.deliverydate## : ##reception.deliverydate## 
      ##lang.reception.deliverystate## : ##reception.deliverystate## 
      ##lang.reception.deliverynumber## : ##reception.deliverynumber## 
      
      ##IFreception.associateditems_url##
      Informations sur le mat&#233;riel associ&#233; 
      
      ##reception.associateditems_url## 
      
      ##lang.reception.associateditems_name## : ##reception.associateditems_name## 
      ##lang.reception.associateditems_serial## : ##reception.associateditems_serial## 
      ##lang.reception.associateditems_otherserial## : ##reception.associateditems_otherserial## 
      ##lang.reception.associateditems_state## : ##reception.associateditems_state##
      ##ENDIFreception.associateditems_url##','&lt;table style=\"border: 1px solid black; border-collapse: collapse;\"&gt;
      &lt;tbody&gt;
      &lt;tr&gt;
      &lt;th style=\"background-color: #f2f2f2;\" colspan=\"4\"&gt;Informations sur la commande&lt;/th&gt;
      &lt;/tr&gt;
      &lt;tr&gt;
      &lt;td style=\"border: 1px solid black; background-color: #f2f2f2;\" colspan=\"4\"&gt;##reception.orderurl##&lt;/td&gt;
      &lt;/tr&gt;
      &lt;tr&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;&lt;strong&gt;##lang.reception.ordername## :&lt;/strong&gt;&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;##reception.ordername##&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;&lt;strong&gt;##lang.reception.orderdate## :&lt;/strong&gt;&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;##reception.orderdate##&lt;/td&gt;
      &lt;/tr&gt;
      &lt;tr&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;&lt;strong&gt;##lang.reception.orderentity## :&lt;/strong&gt;&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;##reception.orderentity##&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;&lt;strong&gt;##lang.reception.orderstate## :&lt;/strong&gt;&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;##reception.orderstate##&lt;/td&gt;
      &lt;/tr&gt;
      &lt;tr&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;&lt;strong&gt;##lang.reception.ordernumber## :&lt;/strong&gt;&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;##reception.ordernumber##&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;&lt;strong&gt;##lang.reception.orderbudget## :&lt;/strong&gt;&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;##reception.orderbudget##&lt;/td&gt;
      &lt;/tr&gt;
      &lt;tr&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;&lt;strong&gt;##lang.reception.orderlocation## :&lt;/strong&gt;&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;##reception.orderlocation##&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;&lt;strong&gt;##lang.reception.ordertaxe## :&lt;/strong&gt;&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;##reception.ordertaxe##&lt;/td&gt;
      &lt;/tr&gt;
      &lt;tr&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;&lt;strong&gt;##lang.reception.ordersupplier## :&lt;/strong&gt;&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;##reception.ordersupplier##&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;&lt;strong&gt;##lang.reception.orderpayment## :&lt;/strong&gt;&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;##reception.orderpayment##&lt;/td&gt;
      &lt;/tr&gt;
      &lt;tr&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;&lt;strong&gt;##lang.reception.ordercontact## :&lt;/strong&gt;&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;##reception.ordercontact##&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;&lt;strong&gt;##lang.reception.orderport## :&lt;/strong&gt;&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;##reception.orderport##&lt;/td&gt;
      &lt;/tr&gt;
      &lt;tr&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;&lt;strong&gt;##lang.reception.ordercomment## :&lt;/strong&gt;&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;##reception.ordercomment##&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;&lt;strong&gt;##lang.reception.ordernote## :&lt;/strong&gt;&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;##reception.ordernote##&lt;/td&gt;
      &lt;/tr&gt;
      &lt;/tbody&gt;
      &lt;/table&gt;
      &lt;hr /&gt;
      &lt;table style=\"border: 1px solid black; border-collapse: collapse;\"&gt;
      &lt;tbody&gt;
      &lt;tr&gt;
      &lt;th style=\"background-color: #f2f2f2;\" colspan=\"4\"&gt;Informations sur la r&#233;f&#233;rence r&#233;ceptionn&#233;e&lt;/th&gt;
      &lt;/tr&gt;
      &lt;tr&gt;
      &lt;td style=\"border: 1px solid black; background-color: #f2f2f2;\" colspan=\"4\"&gt;##reception.deliveryreference_url##&lt;/td&gt;
      &lt;/tr&gt;
      &lt;tr&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;&lt;strong&gt;##lang.reception.deliveryreference_name## :&lt;/strong&gt;&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;##reception.deliveryreference_name##&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;&lt;strong&gt;##lang.reception.deliveryreference_itemtype## :&lt;/strong&gt;&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;##reception.deliveryreference_itemtype##&lt;/td&gt;
      &lt;/tr&gt;
      &lt;tr&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;&lt;strong&gt;##lang.reception.deliveryreference_type## :&lt;/strong&gt;&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;##reception.deliveryreference_type##&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;&lt;strong&gt;##lang.reception.deliveryreference_model## :&lt;/strong&gt;&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;##reception.deliveryreference_model##&lt;/td&gt;
      &lt;/tr&gt;
      &lt;tr&gt;
      &lt;td style=\"border: 1px solid black;\" colspan=\"2\"&gt;&lt;strong&gt;##lang.reception.deliveryreference_manufacturer## :&lt;/strong&gt;&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\" colspan=\"2\"&gt;##reception.deliveryreference_manufacturer##&lt;/td&gt;
      &lt;/tr&gt;
      &lt;tr&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;&lt;strong&gt;##lang.reception.deliveryreference_comment## :&lt;/strong&gt;&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;##reception.deliveryreference_comment##&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;&lt;strong&gt;##lang.reception.deliveryreference_note## :&lt;/strong&gt;&lt;/td&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;##reception.deliveryreference_note##&lt;/td&gt;
      &lt;/tr&gt;
      &lt;/tbody&gt;
      &lt;/table&gt;
      &lt;hr /&gt;
      &lt;table style=\"border: 1px solid black; border-collapse: collapse;\"&gt;
      &lt;tbody&gt;
      &lt;tr&gt;
      &lt;th style=\"background-color: #f2f2f2;\"&gt;Informations sur la r&#233;ception&lt;/th&gt;
      &lt;/tr&gt;
      &lt;tr&gt;
      &lt;td style=\"border: 1px solid black; background-color: #f2f2f2;\"&gt;##reception.deliveryurl##&lt;/td&gt;
      &lt;/tr&gt;
      &lt;tr&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;&lt;strong&gt;##lang.reception.deliverydate## : &lt;/strong&gt;##reception.deliverydate##             &lt;br /&gt;&lt;strong&gt;##lang.reception.deliverystate## : &lt;/strong&gt;##reception.deliverystate##             &lt;br /&gt;&lt;strong&gt;##lang.reception.deliverynumber## : &lt;/strong&gt;##reception.deliverynumber##&lt;/td&gt;
      &lt;/tr&gt;
      &lt;/tbody&gt;
      &lt;/table&gt;
      ##IFreception.associateditems_url##
      &lt;hr /&gt;
      &lt;table style=\"border: 1px solid black; border-collapse: collapse;\"&gt;
      &lt;tbody&gt;
      &lt;tr&gt;
      &lt;th style=\"background-color: #f2f2f2;\"&gt;Informations sur le mat&#233;riel associ&#233;&lt;/th&gt;
      &lt;/tr&gt;
      &lt;tr&gt;
      &lt;td style=\"border: 1px solid black; background-color: #f2f2f2;\"&gt;##reception.associateditems_url##&lt;/td&gt;
      &lt;/tr&gt;
      &lt;tr&gt;
      &lt;td style=\"border: 1px solid black;\"&gt;&lt;strong&gt;##lang.reception.associateditems_name## : &lt;/strong&gt;##reception.associateditems_name##             &lt;br /&gt;&lt;strong&gt;##lang.reception.associateditems_serial## : &lt;/strong&gt;##reception.associateditems_serial##             &lt;br /&gt;&lt;strong&gt;##lang.reception.associateditems_otherserial## : &lt;/strong&gt;##reception.associateditems_otherserial##             &lt;br /&gt;&lt;strong&gt;##lang.reception.associateditems_state## : &lt;/strong&gt;##reception.associateditems_state##&lt;/td&gt;
      &lt;/tr&gt;
      &lt;/tbody&gt;
      &lt;/table&gt;
      ##ENDIFreception.associateditems_url##')";
                  
            $result=$DB->query($query) or die($DB->error());
            $migration->displayMessage("Add notification: Taken delivery");
            if (!countElementsInTable("glpi_notifications","`name`='Taken delivery'")) {
               $query = "INSERT INTO `glpi_notifications`
                          VALUES (NULL, 'Taken delivery', 0, 'PluginOrderOrder_Item', 'delivered',
                                 'mail',$notifications_id, '', 1, 1, NOW());";
               $result=$DB->query($query) or die($DB->error());
            }
         }
      }

   }
   
   static function uninstall() {
      global $DB;
      
      $notif = new Notification();
      
      $options = array('itemtype' => 'PluginOrderOrder_Item',
                    'event'    => 'delivered',
                    'FIELDS'   => 'id');
      foreach ($DB->request('glpi_notifications', $options) as $data) {
         $notif->delete($data);
      }

      $template    = new NotificationTemplate();
      $translation = new NotificationTemplateTranslation();

      //templates
      $options = array('itemtype' => 'PluginOrderOrder_Item', 'FIELDS'   => 'id');
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