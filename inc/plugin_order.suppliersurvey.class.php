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

class PluginOrderSupplierSurvey extends CommonDBTM {

	function __construct() {
		$this->table = "glpi_plugin_order_surveysuppliers";
		$this->type = PLUGIN_ORDER_SURVEY_TYPE;
		$this->dohistory = true;
	}
   
   function getFromDBByOrder($FK_Order) {
		global $DB;
		
		$query = "SELECT * FROM `".$this->table."`
					WHERE `FK_Order` = '" . $FK_Order . "' ";
		if ($result = $DB->query($query)) {
			if ($DB->numrows($result) != 1) {
				return false;
			}
			$this->fields = $DB->fetch_assoc($result);
			if (is_array($this->fields) && count($this->fields)) {
				return true;
			} else {
				return false;
			}
		}
		return false;
	}
	
	function addNotation($field,$value) {
      global $LANG;
      
      echo "<font size='1'>".$LANG['plugin_order']['survey'][7]."</font>&nbsp;";
      
      for ($i=10 ; $i >= 1 ; $i--) {
         echo "&nbsp;".$i."&nbsp;<input type='radio' name='".$field."' value='".$i."' ";
         if ($i == $value)
         echo " checked ";
         echo ">";
      }
      
      echo "&nbsp;<font size='1'>".$LANG['plugin_order']['survey'][6]."</font>";
	}
	
	function getTotalNotation($FK_order) {
      global $DB;
      
      $query = "SELECT (`answer1` + `answer2` + `answer3` + `answer4` + `answer5`) AS total FROM `".$this->table."` " .
				"WHERE `FK_order` = '".$FK_order."' ";
		$result = $DB->query($query);
		$nb = $DB->numrows($result);
		if ($nb)
         return $DB->result($result,0,"total")/5;
      else
         return 0;
	}
	
	function getNotation($FK_enterprise,$field) {
      global $DB;
      
      $query = "SELECT SUM(`".$this->table."`.`".$field."`) AS total, COUNT(`".$this->table."`.`ID`) AS nb 
               FROM `glpi_plugin_order`,`".$this->table."`
               WHERE `".$this->table."`.`FK_enterprise` = `glpi_plugin_order`.`FK_enterprise` 
               AND `".$this->table."`.`FK_order` = `glpi_plugin_order`.`ID` 
               AND `glpi_plugin_order`.`FK_enterprise` = '".$FK_enterprise."'"
               .getEntitiesRestrictRequest(" AND ","glpi_plugin_order","FK_entities",'',true);
		$result = $DB->query($query);
		$nb = $DB->numrows($result);
		if ($nb)
         return $DB->result($result,0,"total")/$DB->result($result,0,"nb");
      else
         return 0;
	}
	
	function showGlobalNotation($FK_enterprise) {
      global $LANG,$DB,$CFG_GLPI,$INFOFORM_PAGES;
      
      $query = "SELECT `glpi_plugin_order`.`ID`, `glpi_plugin_order`.`name`, `glpi_plugin_order`.`FK_entities`,`".$this->table."`.`comment` 
                  FROM `glpi_plugin_order`,`".$this->table."`
                  WHERE `".$this->table."`.`FK_enterprise` = `glpi_plugin_order`.`FK_enterprise`
                  AND `".$this->table."`.`FK_order` = `glpi_plugin_order`.`ID`
                  AND `glpi_plugin_order`.`FK_enterprise` = '".$FK_enterprise."' "
                  .getEntitiesRestrictRequest(" AND ","glpi_plugin_order","FK_entities",'',true);
      $query.= " GROUP BY `".$this->table."`.`ID`";
		$result = $DB->query($query);
		$nb = $DB->numrows($result);
		$total = 0;
		$nb_order = 0;
		
		echo "<br><div class='center'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      echo "<th colspan='4'>" . $LANG['plugin_order']['survey'][0]. "</th>";
      echo "</tr>";
      echo "<tr>";
      echo "<th>".$LANG['entity'][0]."</th>";
      echo "<th>" . $LANG['plugin_order'][39]. "</th>";
      echo "<th>" . $LANG['plugin_order']['survey'][10]."</th>";
      echo "<th>" . $LANG['plugin_order']['survey'][11]."</th>";
      echo "</tr>";
      
      if ($nb) {
         for ($i=0 ; $i <$nb ; $i++) {
            $name = $DB->result($result,$i,"name");
            $ID = $DB->result($result,$i,"ID");
            $comment = $DB->result($result,$i,"comment");
            $FK_entities = $DB->result($result,$i,"FK_entities");
            $note = $this->getTotalNotation($ID);
            echo "<tr class='tab_bg_1'>";
            echo "<td>";
            echo getDropdownName("glpi_entities",$FK_entities);
            echo "</td>";
            echo "<td><a href=\"" . $CFG_GLPI["root_doc"] . "/" . $INFOFORM_PAGES[PLUGIN_ORDER_TYPE] . "?ID=" . $ID . "\">" . $name . "</a></td>";
            echo "<td>" . $note." / 10"."</td>";
            echo "<td>" . nl2br($comment)."</td>";
            echo "</tr>";
            $total+= $this->getTotalNotation($ID);
            $nb_order++;
         }
         
         echo "<tr>";
         echo "<th colspan='4'>&nbsp;</th>";
         echo "</tr>";
         
         for ($i=1 ; $i <= 5 ; $i++) {
            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='2'></td>";
            echo "<td><div align='left'>" . $LANG['plugin_order']['survey'][$i]. "</div></td>";
            echo "<td><div align='left'>" . formatNumber($this->getNotation($FK_enterprise,"answer$i"))."&nbsp;/ 10</div></td>";
            echo "</tr>";
         }
         
         echo "<tr>";
         echo "<th colspan='4'>&nbsp;</th>";
         echo "</tr>";
         
         echo "<tr class='tab_bg_1 b'>";
         echo "<td colspan='2'></td>";
         echo "<td><div align='left'>" . $LANG['plugin_order']['survey'][9]. "</div></td>";
         echo "<td><div align='left'>" . formatNumber($total/$nb_order)."&nbsp;/ 10</div></td>";
         echo "</tr>";
      }
      echo "</table>";
      echo "</div>";
	}
	
	function showForm($target, $ID = -1, $surveyid = -1) {
		global $LANG, $DB, $CFG_GLPI, $INFOFORM_PAGES;
      
      $PluginOrder=new PluginOrder();
      
      if ($surveyid > 0) {
         $this->getFromDB($surveyid);
         $ID = $this->fields["FK_order"];
      } else {
         if($this->getFromDBByOrder($ID)) {
            $surveyid = $this->fields["ID"];
         }
      }

      $canedit=$PluginOrder->can($ID,'w');
      
		if ($ID > 0 & $surveyid > 0) {
			$this->check($surveyid,'r');
		} else {
			// Create item 
			$this->check(-1,'w');
			$this->getEmpty();
			$surveyid=0;
		}

      echo "<form method='post' name='show_survey' id='show_survey' action=\"$target\">";
      echo "<input type='hidden' name='ID' value='" . $surveyid . "'>";
      echo "<input type='hidden' name='FK_order' value='" . $ID . "'>";
      
      $PluginOrder->getFromDB($ID);
      $supplier = $PluginOrder->fields["FK_enterprise"];
      if ($surveyid > 0)
         $supplier = $this->fields["FK_enterprise"];
      echo "<input type='hidden' name='FK_enterprise' value='" . $supplier . "'>";
      
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      echo "<th>".$LANG['plugin_order']['survey'][0]."</th>";
      echo "<th><div align='left'>";
      if ($surveyid>0) {
         echo " ID $surveyid:";
      }		
      echo "</div></th></tr>";
      
      echo "<tr class='tab_bg_1'><td>" . $LANG['financial'][26] . ": </td><td>";
      echo "<a href=\"" . $CFG_GLPI["root_doc"] . "/" . $INFOFORM_PAGES[ENTERPRISE_TYPE] . "?ID=" . $supplier . "\">" . getDropdownName("glpi_enterprises", $supplier) . "</a>";
      echo "</td>";
		echo "</tr>";
		
		for ($i=1 ; $i <= 5 ; $i++) {
         echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_order']['survey'][$i] . ": </td><td>";
         $this->addNotation("answer$i",$this->fields["answer$i"]);
         echo "</td>";
         echo "</tr>";
      }
		
		echo "<tr class='tab_bg_1'><td>";
      //comments of order
      echo $LANG['common'][25] . ":	</td>";
      echo "<td>";
      if ($canedit)
         echo "<textarea cols='80' rows='4' name='comment'>" . $this->fields["comment"] . "</textarea>";
      else
         echo $this->fields["comment"];
      echo "</td>";
		echo "</tr>";
		
		if ($surveyid>0) {
         echo "<tr><th><div align='left'>" . $LANG['plugin_order']['survey'][8] . ": </div></th><th><div align='left'>";
         $total = $this->getTotalNotation($ID);
         echo $total." / 10";
         echo "</div></th>";
         echo "</tr>";
		}
		if ($canedit) {
			
         if (empty($surveyid)||$surveyid<0){

            echo "<tr>";
            echo "<td class='tab_bg_2' valign='top' colspan='3'>";
            echo "<div align='center'><input type='submit' name='add' value=\"".$LANG['buttons'][8]."\" class='submit'></div>";
            echo "</td>";
            echo "</tr>";
   
         } else {
   
            echo "<tr>";
            echo "<td class='tab_bg_2' valign='top' colspan='3'><div align='center'>";
            echo "<input type='submit' name='update' value=\"".$LANG['buttons'][7]."\" class='submit' >";
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='delete' value=\"".$LANG['buttons'][6]."\" class='submit'></div>";
            echo "</td>";
            echo "</tr>";
   
         }
      }
      echo "</table></form>";

		return true;
	}
}

?>