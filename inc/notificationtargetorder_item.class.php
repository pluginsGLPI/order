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
               $order->getState($order->getField("states_id"));

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
}

?>