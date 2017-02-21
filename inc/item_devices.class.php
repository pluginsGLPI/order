<?php

/*
 * @version $Id: HEADER 2011-03-23 15:41:26 tsmr $
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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginOrderItem_Devices extends Item_Devices {
   public static $rightname = 'plugin_order_order';
    
   
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      self::showForItem($item, $withtemplate);
      return true;
   }

   static function showForItem(CommonGLPI $item, $withtemplate = 0) {
      parent::showForItem($item);
   }

}
