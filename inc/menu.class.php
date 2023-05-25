<?php

/**
 * -------------------------------------------------------------------------
 * Order plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Order.
 *
 * Order is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Order is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Order. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2009-2023 by Order plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/order
 * -------------------------------------------------------------------------
 */

class PluginOrderMenu extends CommonGLPI {


   public static function getTypeName($nb = 0) {
      return __("Orders", "order");
   }


   static function getMenuContent() {
      $menu = [
         'title' => self::getTypeName(2),
         'page'  => self::getSearchURL(false),
         'icon'  => PluginOrderOrder::getIcon(),
      ];
      if (PluginOrderConfig::canView()) {
         $menu['links']['config'] = PluginOrderConfig::getFormURL(false);
      }

      if (PluginOrderOrder::canView()) {
         $menu['options']['order'] = [
            'title' => PluginOrderOrder::getTypeName(2),
            'page'  => PluginOrderOrder::getSearchURL(false),
            'icon'  => PluginOrderOrder::getIcon(),
         ];
         if (PluginOrderOrder::canCreate()) {
            $menu['options']['order']['links'] = [
               'search' => PluginOrderOrder::getSearchURL(false),
               'add'    => "/front/setup.templates.php?itemtype=PluginOrderOrder&add=1",
            ];
         }
         $menu['options']['order']['links']['template'] = "/front/setup.templates.php?itemtype=PluginOrderOrder&add=0";
         if (PluginOrderConfig::canView()) {
            $menu['options']['order']['links']['config'] = PluginOrderConfig::getFormURL(false);
         }
         $menu['options']['order']['links']['lists']  = "";
         $menu['options']['order']['lists_itemtype']  = PluginOrderOrder::getType();
      }

      if (PluginOrderBill::canView()) {
         $menu['options']['bill'] = [
            'title' => PluginOrderBill::getTypeName(2),
            'page'  => PluginOrderBill::getSearchURL(false),
            'links' => [
               'search' => PluginOrderBill::getSearchURL(false)
            ],
            'icon'  => PluginOrderBill::getIcon(),
         ];
         if (PluginOrderBill::canCreate()) {
            $menu['options']['bill']['links']['add'] = PluginOrderBill::getFormURL(false);
         }
         if (PluginOrderConfig::canView()) {
            $menu['options']['bill']['links']['config'] = PluginOrderConfig::getFormURL(false);
         }
         $menu['options']['bill']['links']['lists']  = "";
         $menu['options']['bill']['lists_itemtype']  = PluginOrderBill::getType();
      }

      if (PluginOrderReference::canView()) {
         $menu['options']['references'] = [
            'title' => PluginOrderReference::getTypeName(2),
            'page'  => PluginOrderReference::getSearchURL(false),
            'links' => [
               'search' => PluginOrderReference::getSearchURL(false)
            ],
            'icon'  => PluginOrderReference::getIcon(),
         ];
         if (PluginOrderReference::canCreate()) {
            $menu['options']['references']['links']['add'] = PluginOrderReference::getFormURL(false);
         }
         if (PluginOrderConfig::canView()) {
            $menu['options']['references']['links']['config'] = PluginOrderConfig::getFormURL(false);
         }
         $menu['options']['references']['links']['lists']  = "";
         $menu['options']['references']['lists_itemtype']  = PluginOrderReference::getType();
      }
      return $menu;
   }


   function install() {

   }


}
