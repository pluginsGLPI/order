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

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

// Class for a Dropdown
class PluginOrderOrderState extends CommonDropdown {

   const DRAFT                = 1;
   const WAITING_FOR_APPROVAL = 2;
   const VALIDATED            = 3;
   const BEING_DELIVERING     = 4;
   const DELIVERED            = 5;
   const CANCELED             = 6;

   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_order']['status'][0];
   }
   
   function canCreate() {
      return plugin_order_haveRight('order', 'w');
   }

   function canView() {
      return plugin_order_haveRight('order', 'r');
   } 
   
   function pre_deleteItem() {
      global $LANG;
      if ($this->getID() <= self::CANCELED ) {
         addMessageAfterRedirect($LANG['plugin_order']['status'][15].": ".$this->fields['name'], false, ERROR);
         return false;
      } else {
         return true;
      }
   }
}

?>