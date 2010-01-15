<?php
/*
 * @version $Id: HEADER 1 2009-09-21 14:58 Tsmr $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2009 by the INDEPNET Development Team.

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
// Original Author of file: NOUH Walid & Benjamin Fontan
// Purpose of file: plugin order v1.1.0 - GLPI 0.72
// ----------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')){
   die("Sorry. You can't access directly to this file");
}

class PluginOrderBudget extends CommonDBTM {

	function __construct() {
		$this->table = "glpi_plugin_order_budgets";
		$this->type = PLUGIN_ORDER_BUDGET_TYPE;
	}

	/*define header form */
	function defineTabs($ID, $withtemplate) {
		global $LANG;
		
		/* principal */
		$ong[1] = $LANG['title'][26];

		return $ong;
	}
	
	/**
	 * Print a good title for user pages
	 *
	 *@return nothing (display)
	 **/
	function title() {
		global $LANG, $CFG_GLPI;
		
		displayTitle($CFG_GLPI["root_doc"] . "/plugins/order/pics/budget-icon.png", $LANG['financial'][87], $LANG['financial'][87]);
	}

	function showForm($target, $ID, $withtemplate = '') {
		global $CFG_GLPI, $LANG, $DB;

		plugin_order_checkRight("budget","r");
		
		if ($ID > 0) {
			$this->getFromDB($ID);
		} else
				$this->getEmpty();
		
		$canedit = plugin_order_haveRight("budget", "w");
		
      $this->showTabs($ID, $withtemplate, $_SESSION['glpi_tab']);

      echo "<form method='post' name=form action=\"$target\">";
      if (empty ($ID) || $ID < 0) {
         echo "<input type='hidden' name='FK_entities' value='" . $_SESSION["glpiactive_entity"] . "'>";
      }
      echo "<div class='center' id='tabsbody'>";
      echo "<table class='tab_cadre_fixe'>";
      $this->showFormHeader($ID, '', 1);
      echo "<tr class='tab_bg_2'><td>" . $LANG['common'][16] . ": </td>";
      echo "<td>";
      if ($canedit)
         autocompletionTextField("name", "glpi_plugin_order_budgets", "name", $this->fields["name"], 20, $this->fields["FK_entities"]);
      else
         echo $this->fields["name"];	
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'><td>" . $LANG['financial'][87]." GLPI" . ": </td>";
      echo "<td>";
      if ($canedit)
         dropdownValue("glpi_dropdown_budget", "FK_budget", $this->fields["FK_budget"]);
      else
         echo getDropdownName("glpi_dropdown_budget",$this->fields["FK_budget"]);	
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'><td>" . $LANG['search'][8] . ": </td>";
      echo "<td>";
      if ($canedit)
         showDateFormItem("startdate",$this->fields["startdate"]);
      else
         echo convDate($this->fields["startdate"]);	
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'><td>" . $LANG['search'][9] . ": </td>";
      echo "<td>";
      if ($canedit)
         showDateFormItem("enddate",$this->fields["enddate"]);
      else
         echo convDate($this->fields["enddate"]);	
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'><td>" . $LANG['financial'][21] . ": </td>";
      echo "<td>";
      if ($canedit)
         echo "<input type='text' name='value' value=\"".formatNumber($this->fields["value"],true)."\" size='20'>";
      else
         echo $this->fields["value"];	
      echo "</td></tr>";

      if ($ID > 0) {
         $query = "SELECT SUM(`price_discounted`) as total_price FROM `glpi_plugin_order`, `glpi_plugin_order_detail` " .
               "WHERE `budget` = '".$this->fields["FK_budget"]."' AND `glpi_plugin_order_detail`.`FK_order` = `glpi_plugin_order`.`ID` " .
               "GROUP BY `glpi_plugin_order`.`budget`";
         $result = $DB->query($query);

         echo "<tr class='tab_bg_2'><td>" . $LANG['plugin_order']['budget'][2] . ": </td>";
         echo "<td>";
         if ($DB->numrows($result))
            echo formatNumber($DB->result($result,0,0),false,2);
         else
            echo "0";
         echo "</td></tr>";
      }

      echo "<tr class='tab_bg_2'><td>" . $LANG['common'][25] . ": </td>";
      
      echo "<td colspan='3'>";
      if ($canedit)
         echo "<textarea cols='50' rows='4' name='comments' >" . $this->fields["comments"] . "</textarea>";
      else
         echo $this->fields["comments"];
      echo "</td></tr>";

      if ($canedit) {
         if (empty ($ID) || $ID < 0) {
            echo "<tr>";
            echo "<td class='tab_bg_2' valign='top' colspan='2'>";
            echo "<div align='center'><input type='submit' name='add' value=\"" . $LANG['buttons'][8] . "\" class='submit'></div>";
            echo "</td>";
            echo "</tr>";
         } else {
            echo "<tr>";
            echo "<td class='tab_bg_2' valign='top' colspan='2'><div align='center'>";
            echo "<input type='hidden' name='ID' value=\"$ID\">\n";
            echo "<input type='submit' name='update' value=\"" . $LANG['buttons'][7] . "\" class='submit' >";
               if ($this->fields["deleted"] == '0') {
                  echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='delete' value=\"" . $LANG['buttons'][6] . "\" class='submit'></div>";
               } else {
                  echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='restore' value=\"" . $LANG['buttons'][21] . "\" class='submit'>";
                  echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='purge' value=\"" . $LANG['buttons'][22] . "\" class='submit'></div>";
               }
            echo "</td>";
            echo "</tr>";
         }
      }
      echo "</table></div></form>";
      echo "<div id='tabcontent'></div>";
      echo "<script type='text/javascript'>loadDefaultTab();</script>";
      
      return true;
	}
	
	function getAllOrdersByBudget($budget_id){
      global $DB,$LANG,$INFOFORM_PAGES,$CFG_GLPI;
      
      $budget = new PluginOrderBudget;
      $budget->getFromDB($budget_id);
      if ($budget->fields["FK_budget"]!=0) {
         $query = "SELECT * FROM `glpi_plugin_order` WHERE `budget` = '".$budget->fields["FK_budget"]."' ORDER BY `FK_entities`, `name` ";
         $result = $DB->query($query);

         echo "<div class='center'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><th colspan='5'>".$LANG['plugin_order']['budget'][1]."</th></tr>";
         echo "<tr>"; 
         echo "<th>".$LANG['common'][16]."</th>";
         echo "<th>".$LANG['entity'][0]."</th>";
         echo "</tr>";

         while ($data = $DB->fetch_array($result))
         {
            echo "<tr class='tab_bg_1' align='center'>"; 
            echo "<td>";
            if (plugin_order_haveRight("order","r"))
               echo "<a href='".$CFG_GLPI["root_doc"]."/".$INFOFORM_PAGES[PLUGIN_ORDER_TYPE]."?ID=".$data["ID"]."'>".$data["name"]."</a>";
            else
               echo $data["name"];	
            echo "</td>";

            echo "<td>";
            echo getDropdownName("glpi_entities",$data["FK_entities"]);
            echo "</td>";

            echo "</tr>"; 
         }
         
         echo "</table></div>";
      }
   }
}

?>