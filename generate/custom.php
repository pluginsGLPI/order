<?php
/*
 * @version $Id: bill.tabs.php 530 2011-06-30 11:30:17Z walid $
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
 along with GLPI; along with Behaviors. If not, see <http://www.gnu.org/licenses/>.
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

function plugin_order_getCustomFieldsForODT($ID, $odttemplates_id, $odf, $signature) {
   /** @var \DBmysql $DB */
   global $DB;

   $order = new PluginOrderOrder();
   $order->getFromDB($ID);
   $PluginOrderOrder_Item         = new PluginOrderOrder_Item();
   $PluginOrderReference_Supplier = new PluginOrderReference_Supplier();

   $odf->setImage('logo', PLUGIN_ORDER_TEMPLATE_LOGO_DIR.'/logo.jpg');

   $odf->setVars('title_order', __("Order number", "order"), true, 'UTF-8');
   $odf->setVars('num_order', $order->fields["num_order"], true, 'UTF-8');
   //$odf->setVars('comment', $order->fields["comment"], true, 'UTF-8');

   $odf->setVars('title_invoice_address', __("Invoice address", "order"), true, 'UTF-8');

   $entity = new Entity();
   $entity->getFromDB($order->fields["entities_id"]);
   $town    = '';

   if ($order->fields["entities_id"]!=0) {
      $name_entity = $entity->fields["name"];
   } else {
      $name_entity = __("Root entity");
   }

   $odf->setVars('entity_name', $name_entity, true, 'UTF-8');
   if ($entity->getFromDB($order->fields["entities_id"])) {
      $odf->setVars('entity_address', $entity->fields["address"], true, 'UTF-8');
      $odf->setVars('entity_postcode', $entity->fields["postcode"],true, 'UTF-8');
      $town = $entity->fields["town"];
      $odf->setVars('entity_town', $town,true,'UTF-8');
      $odf->setVars('entity_country', $entity->fields["country"], true, 'UTF-8');
      $odf->setVars('entity_ldapdn', $entity->fields["ldap_dn"], true, 'UTF-8');
   }

   $supplier = new Supplier();
   if ($supplier->getFromDB($order->fields["suppliers_id"])) {
      $odf->setVars('supplier_name', $supplier->fields["name"],true,'UTF-8');
      $odf->setVars('supplier_address', $supplier->fields["address"],true,'UTF-8');
      $odf->setVars('supplier_postcode', $supplier->fields["postcode"],true,'UTF-8');
      $odf->setVars('supplier_town', $supplier->fields["town"],true,'UTF-8');
      $odf->setVars('supplier_country', $supplier->fields["country"],true,'UTF-8');
   }

   $odf->setVars('title_delivery_address',__("Delivery address", "order"),true,'UTF-8');
   $tmpname=Dropdown::getDropdownName("glpi_locations",$order->fields["locations_id"],1);
   $comment=$tmpname["comment"];
   $odf->setVars('comment_delivery_address',$comment,true,'UTF-8');

   if ($town) {
      $town = $town. ", ";
   }
   $odf->setVars('title_date_order', $town.__("The", "order")." ",true,'UTF-8');
   $odf->setVars('date_order', Html::convDate($order->fields["order_date"]),true,'UTF-8');

   $odf->setVars('title_sender', __("Issuer order", "order"),true,'UTF-8');
   $odf->setVars('sender', getUserName(Session::getLoginUserID()),true,'UTF-8');

   $output='';
   $contact = new Contact();
   if ($contact->getFromDB($order->fields["contacts_id"])) {
      $output=formatUserName($contact->fields["id"], "", $contact->fields["name"],
                             $contact->fields["firstname"], 0);
   }
   $odf->setVars('title_recipient',__("Recipient", "order"),true,'UTF-8');
   $odf->setVars('recipient',$output,true,'UTF-8');
   $odf->setVars('nb',__("Quantity", "order"),true,'UTF-8');
   $odf->setVars('title_item',__("Designation", "order"),true,'UTF-8');
   $odf->setVars('title_ref',__("Reference"),true,'UTF-8');
   $odf->setVars('HTPrice_item',__("Unit price", "order"),true,'UTF-8');
   $odf->setVars('TVA_item',__("VAT", "order"),true,'UTF-8');
   $odf->setVars('title_discount',__("Discount rate", "order"),true,'UTF-8');
   $odf->setVars('title_ecotax',__("Ecotax", "order"),true,'UTF-8');
   $odf->setVars('HTPriceTotal_item',__("Sum tax free", "order"),true,'UTF-8');
   $odf->setVars('ATIPriceTotal_item',__("Price ATI", "order"),true,'UTF-8');

   $listeArticles = [];
   $result = $PluginOrderOrder_Item->queryDetail($ID, 'glpi_plugin_order_references');
   $num    = $DB->numrows($result);

   // Get ecotax total from references
   $ecotaxHT = $PluginOrderOrder_Item->getEcotaxTotal($ID);

   // If no calculated ecotax from references, use the global ecotax value
   if ($ecotaxHT == 0) {
      $ecotaxHT = $order->fields["ecotax_price"];
   }

   while ($data = $DB->fetchArray($result)) {
      $quantity = $PluginOrderOrder_Item->getTotalQuantityByRefAndDiscount($ID, $data["id"],
                                                                           $data["price_taxfree"],
                                                                           $data["discount"]);

      // Get ecotax price for display in line items
      $ecotax_line = 0;
      $ref = new PluginOrderReference();
      if ($ref->getFromDB($data["id"])) {
         $ecotax_line = $ref->getEcotaxPrice();
      }

      $listeArticles[] = [
         'quantity'         => $quantity,
         'ref'              => $data["name"],
         'taxe'             => Dropdown::getDropdownName(PluginOrderOrderTax::getTable(),
                                                         $data["plugin_order_ordertaxes_id"]),
         'refnumber'        => $PluginOrderReference_Supplier->getReferenceCodeByReferenceAndSupplier($data["id"],
                                                                                                      $order->fields["suppliers_id"]),
         'price_taxfree'    => $data["price_taxfree"],
         'ecotax'           => $ecotax_line, // Add ecotax price per line
         'discount'         => $data["discount"],
         'price_discounted' => $data["price_discounted"]*$quantity,
         'price_ati'        => $data["price_ati"]
      ];
   }

   // Add free reference items
   $result_free = $PluginOrderOrder_Item->queryDetail($ID, 'glpi_plugin_order_referencefrees');
   while ($data_free = $DB->fetchArray($result_free)) {
      $quantity = $PluginOrderOrder_Item->getTotalQuantityByRefAndDiscount($ID, $data_free["id"],
                                                                           $data_free["price_taxfree"],
                                                                           $data_free["discount"]);

      // Get ecotax price for free references
      $ecotax_line = 0;
      $ref_free = new PluginOrderReferenceFree();
      if ($ref_free->getFromDB($data_free["id"])) {
         $ecotax_line = $ref_free->getEcotaxPrice();
      }

      $listeArticles[] = [
         'quantity'         => $quantity,
         'ref'              => $data_free["name"],
         'taxe'             => Dropdown::getDropdownName(PluginOrderOrderTax::getTable(),
                                                         $data_free["plugin_order_ordertaxes_id"]),
         'refnumber'        => '',
         'price_taxfree'    => $data_free["price_taxfree"],
         'ecotax'           => $ecotax_line,
         'discount'         => $data_free["discount"],
         'price_discounted' => $data_free["price_discounted"]*$quantity,
         'price_ati'        => $data_free["price_ati"]
      ];
   }

   $article = $odf->setSegment('articles');
   foreach($listeArticles AS $element) {
      $articleValues = [];
      $articleValues['nbA'] = $element['quantity'];
      $articleValues['titleArticle'] = $element['ref'];
      $articleValues['refArticle'] = $element['refnumber'];
      $articleValues['TVAArticle'] = $element['taxe'];
      $articleValues['HTPriceArticle'] = Html::formatNumber($element['price_taxfree']);
      if ($element['discount'] != 0) {
         $articleValues['discount'] = Html::formatNumber($element['discount'])." %";
      } else {
         $articleValues['discount'] = "";
      }
      // Add ecotax to the template
      if ($element['ecotax'] > 0) {
         $articleValues['ecotax'] = Html::formatNumber($element['ecotax']);
      } else {
         $articleValues['ecotax'] = "";
      }
      $articleValues['HTPriceTotalArticle'] = Html::formatNumber($element['price_discounted']);

      $total_TTC_Article = $element['price_discounted'] * (1 + ($element['taxe'] / 100));
      $articleValues['ATIPriceTotalArticle'] = Html::formatNumber($total_TTC_Article);

      // Set variables in odt segment
      foreach ($articleValues as $field => $val) {
         try {
            $article->setVars($field, $val, true, 'UTF-8');
         } catch (\Odtphp\Exceptions\OdfException $e) {
            $is_cs_happy = true;
         }
      }
      $article->merge();
   }

   $odf->mergeSegment($article);
   $prices = $PluginOrderOrder_Item->getAllPrices($ID);

   // total price (with postage)
   $tax = new PluginOrderOrderTax();
   $tax->getFromDB($order->fields["plugin_order_ordertaxes_id"]);

   $postagewithTVA = $PluginOrderOrder_Item->getPricesATI(
      $order->fields["port_price"],
      $tax->getRate()
   );

   // Get ecotax tax rate and calculate VAT on ecotax
   $ecotaxRate = 0;
   if ($order->fields["plugin_order_ordertaxes_ecotax_id"] > 0) {
      $tax->getFromDB($order->fields["plugin_order_ordertaxes_ecotax_id"]);
      $ecotaxRate = $tax->getRate();
   }

   $ecotaxTVA = $ecotaxHT * ($ecotaxRate / 100);
   $ecotaxTTC = $ecotaxHT + $ecotaxTVA;

   $total_HT  = $prices["priceHT"] + $order->fields["port_price"] + $ecotaxHT;
   $total_TVA = $prices["priceTVA"] + ($postagewithTVA - $order->fields["port_price"]) + $ecotaxTVA;
   $total_TTC = $prices["priceTTC"] + $postagewithTVA + $ecotaxTTC;

   // Section Articles
   $odf->setVars('title_articles', __("Articles", "order"), true, 'UTF-8');
   $odf->setVars('title_totalht', __("Price tax free", "order"), true, 'UTF-8');
   $odf->setVars('totalht', Html::formatNumber($prices['priceHT']), true, 'UTF-8');

   // Section Ecotax
   $odf->setVars('title_ecotax_section', __("Ecotax", "order"), true, 'UTF-8');
   $odf->setVars('title_ecotax_ht', __("Ecotax tax free", "order"), true, 'UTF-8');
   $odf->setVars('ecotax_ht', Html::formatNumber($ecotaxHT), true, 'UTF-8');

   $odf->setVars('title_ecotax_tva', __("VAT on Ecotax", "order"), true, 'UTF-8');
   $odf->setVars('ecotax_rate', $ecotaxRate . " %", true, 'UTF-8');
   $odf->setVars('ecotax_tva', Html::formatNumber($ecotaxTVA), true, 'UTF-8');

   $odf->setVars('title_ecotax_ttc', __("Ecotax (ATI)", "order"), true, 'UTF-8');
   $odf->setVars('ecotax_ttc', Html::formatNumber($ecotaxTTC), true, 'UTF-8');

   // Section Shipping
   $odf->setVars('title_shipping', __("Shipping", "order"), true, 'UTF-8');
   $odf->setVars('title_price_port', __("Postage", "order"), true, 'UTF-8');
   $odf->setVars('port_price', Html::formatNumber($order->fields["port_price"]), true, 'UTF-8');

   $odf->setVars('title_tva_port', __("VAT on postage", "order"), true, 'UTF-8');
   $odf->setVars('price_port_tva', Html::formatNumber($postagewithTVA - $order->fields["port_price"]), true, 'UTF-8');

   $odf->setVars('title_port_ttc', __("Postage (ATI)", "order"), true, 'UTF-8');
   $odf->setVars('port_price_ttc', Html::formatNumber($postagewithTVA), true, 'UTF-8');

   // Section Totals
   $odf->setVars('title_totals', __("Totals", "order"), true, 'UTF-8');
   $odf->setVars('title_total_ht', __("Total tax free", "order"), true, 'UTF-8');
   $odf->setVars('totalht_port_price', Html::formatNumber($total_HT), true, 'UTF-8');

   $odf->setVars('title_tva', __("Total VAT", "order"), true, 'UTF-8');
   $odf->setVars('totaltva', Html::formatNumber($total_TVA), true, 'UTF-8');

   $odf->setVars('title_totalttc', __("Total price (ATI)", "order"), true, 'UTF-8');
   $odf->setVars('totalttc', Html::formatNumber($total_TTC), true, 'UTF-8');

   $odf->setVars('title_money', __("€", "order"), true, 'UTF-8');
   $odf->setVars('title_sign', __("Signature of issuing order", "order"), true, 'UTF-8');

   if ($signature) {
      $odf->setImage('sign', PLUGIN_ORDER_SIGNATURE_DIR . $signature);
   } else {
      $odf->setImage('sign', '../pics/nothing.gif');
   }
   //$odf->setVars('title_conditions',__("Payment conditions", "order"),true,'UTF-8');
   $odf->setVars('payment_conditions',
                 Dropdown::getDropdownName("glpi_plugin_order_orderpayments",
                                           $order->fields["plugin_order_orderpayments_id"]),
                                           true,'UTF-8');
}


