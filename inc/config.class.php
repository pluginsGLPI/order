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
// Original Author of file: NOUH Walid & FONTAN Benjamin & CAILLAUD Xavier
// Purpose of file: plugin order v1.2.0 - GLPI 0.78
// ----------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginOrderConfig extends CommonDBTM {

   
   static function getTypeName() {
      global $LANG;

      return $LANG['plugin_order']['config'][0];
   }
   
   function showConfigForm(){
      global $LANG;
      
      $this->getFromDB(1);
      echo "<div class='center'>";
      echo "<form name='form' method='post' action='".$this->getFormURL()."'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2'>".$LANG['plugin_order']['config'][0]."</th></tr>";
      
      echo "<input type='hidden' name='id' value='1'>";
      echo "<tr class='tab_bg_1' align='center'><td>".$LANG['plugin_order']['config'][1].
            "</td><td>";
      Dropdown::show('PluginOrderOrderTaxe', array('name' => "default_taxes",
                                                   'value' => $this->fields["default_taxes"]));
      echo "</td>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1' align='center'>
                  <td>".$LANG['plugin_order']['config'][2]."</td><td>";
                  Dropdown::showYesNo("use_validation",$this->fields["use_validation"]); 
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>
               <td>".$LANG['plugin_order']['config'][12]."</td><td>";
               Dropdown::show('State', 
                              array('name'   => 'default_asset_states_id',
                                    'value'  => $this->fields["default_asset_states_id"],
                                    'entity' => $_SESSION["glpiactiveentities"]));
      echo "</td></tr>";

		// Automatic actions
		echo "<tr class='tab_bg_1' align='center'>
		         <th colspan='2'>".$LANG['plugin_order']['config'][3]."</th>
		      </tr>";
		
		// ASSETS
		echo "<tr class='tab_bg_1' align='center'>
		         <th colspan='2'>".$LANG['common'][1]."</th>
		      </tr>";

      echo "<tr class='tab_bg_1' align='center'>
                  <td>".$LANG['plugin_order']['config'][4]."</td><td>";
                  Dropdown::showYesNo("generate_assets",$this->fields["generate_assets"]);
		echo "</td></tr>";
		
      echo "<tr class='tab_bg_1' align='center'>
            <td>".$LANG['plugin_order']['config'][5]."</td><td>";
		         autocompletionTextField($this, "generated_name");
		echo "</td></tr>";

		echo "<tr class='tab_bg_1' align='center'>
		         <td>".$LANG['plugin_order']['config'][6]."</td><td>";
		         autocompletionTextField($this, "generated_serial");
		echo "</td></tr>";
		
		echo "<tr class='tab_bg_1' align='center'>
               <td>".$LANG['plugin_order']['config'][7]."</td><td>";
   		      autocompletionTextField($this, "generated_otherserial");
      echo "</td></tr>";
		
		echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".$LANG['plugin_order']['config'][8]."</td><td>";
      if (isMultiEntitiesMode()) {
         Dropdown::show('Entity', array(  'name'   => "default_asset_entities_id",
                                          'value'  => $this->fields["default_asset_entities_id"],
                                          'entity' => $_SESSION["glpiactiveentities"]));
      } else {
         echo $_SESSION["glpiactive_entity"];
      }
		echo "</td></tr>";
		
      // TICKETS
		echo "<tr class='tab_bg_1' align='center'>
		         <th colspan='2'>".$LANG['job'][38]."</th>
		      </tr>";
	
      echo "<tr class='tab_bg_1' align='center'>
            <td>".$LANG['plugin_order']['config'][4]."</td><td>";
                  Dropdown::showYesNo("generate_ticket",$this->fields["generate_ticket"]);
		echo "</td></tr>";

		echo "<tr class='tab_bg_1' align='center'>
               <td>".$LANG['plugin_order']['config'][10]."</td><td>";
		         autocompletionTextField($this, "generated_title");
		echo "</td></tr>";
		
		echo "<tr class='tab_bg_1' align='center'>
		         <td>".$LANG['plugin_order']['config'][11]."</td><td>";
      echo "<textarea cols='60' rows='4' name='generated_content'>" .
                  $this->fields["generated_content"] . "</textarea>";
		echo "</td></tr>";
		
		echo "<tr class='tab_bg_1' align='center'>
               <td>".$LANG['plugin_order']['config'][9]."</td><td>";
               Dropdown::show('TicketCategory', 
                              array('name'   => 'default_ticketcategories_id',
                                    'value'  => $this->fields["default_ticketcategories_id"],
                                    'entity' => $_SESSION["glpiactiveentities"]));
      echo "</td></tr>";

      echo "<tr class='tab_bg_1' align='center'>"; 
      echo "<td colspan='2' align='center'>"; 
      echo "<input type='submit' name='update' value=\"".$LANG['buttons'][7]."\" class='submit' >"; 
      echo"</td>";
      echo "</tr>";
      
      echo "</table></form></div>";
   }
   
   function getConfig(){
   
      $this->getFromDB(1);
      return $this->fields; 
   }
   
   function getDefaultTaxes() {
   
      $config = $this->getConfig();
      return $config["default_taxes"];
   }
}

?>