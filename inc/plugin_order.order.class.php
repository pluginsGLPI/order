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

class PluginOrder extends CommonDBTM {

	function __construct() {
		$this->table = "glpi_plugin_order";
		$this->type = PLUGIN_ORDER_TYPE;
		$this->entity_assign = true;
		$this->may_be_recursive = true;
		$this->dohistory = true;
	}

	/*clean if order are deleted */
	function cleanDBonPurge($ID) {
		global $DB;

		$query = "DELETE FROM `glpi_doc_device` 
				  WHERE `FK_device` = '$ID' 
				  AND `device_type` = '" .$this->type . "' ";
		$DB->query($query);
		$query = "DELETE 
               FROM `glpi_plugin_order_detail`
               WHERE `FK_order` = '$ID'";
		$DB->query($query);
	}
   
   function canUpdateOrder($orderID) {
      global $ORDER_VALIDATION_STATUS;
      
      if ($orderID > 0) {
         $this->getFromDB($orderID);
         return (in_array($this->fields["status"], $ORDER_VALIDATION_STATUS));
      } else
         return true;
   }
   
   /**
	 * Print a good title for user pages
	 *
	 *@return nothing (display)
	 **/
	function title() {
		global $LANG, $CFG_GLPI;
		
		displayTitle($CFG_GLPI["root_doc"] . "/plugins/order/pics/order-icon.png", $LANG['plugin_order']['title'][1], $LANG['plugin_order']['title'][1]);
	}
	
	/*define header form */
	function defineTabs($ID, $withtemplate) {
		global $LANG;
		
		/* principal */
		$ong[1] = $LANG['title'][26];
		if ($ID > 0) {
			$plugin = new Plugin();
			/* detail */
			$ong[2] = $LANG['plugin_order']['detail'][0];
			/* fournisseur */
			$ong[3] = $LANG['plugin_order'][4];
			/* generation 
			$ong[4] = $LANG['plugin_order']['generation'][2];*/
			/* delivery */
			$ong[5] = $LANG['plugin_order']['delivery'][1];
			/* item */
			$ong[6] = $LANG['plugin_order']['item'][0];
			/* documents */
			if (haveRight("document", "r"))
				$ong[9] = $LANG['Menu'][27];
			
			if (haveRight("notes", "r"))
				$ong[10] = $LANG['title'][37];
			/* all */
			$ong[12] = $LANG['title'][38];
		}
		return $ong;
	}

	function prepareInputForAdd($input) {
		global $LANG;
		
		if (!isset ($input["numorder"]) || $input["numorder"] == '') {
			addMessageAfterRedirect($LANG['plugin_order'][44], false, ERROR);
			return array ();
		}
		elseif (!isset ($input["name"]) || $input["name"] == '') $input["name"] = $input["numorder"];

		return $input;
	}

	function showForm($target, $ID, $withtemplate = '') {
		global $CFG_GLPI, $LANG, $DB;

		if (!plugin_order_haveRight("order", "r"))
			return false;
		$spotted = false;
		if ($ID > 0) {
			if ($this->can($ID, 'r')) {
				$spotted = true;
			}
		} else {
			if ($this->can(-1, 'w')) {
				$spotted = true;
				$this->getEmpty();
			}
		}

		if ($spotted) {
			$this->showTabs($ID, $withtemplate, $_SESSION['glpi_tab']);
			$canedit = ($this->canUpdateOrder($ID) && $this->can($ID, 'w') && $this->fields["status"] != ORDER_STATUS_CANCELED);
			$canrecu = $this->can($ID, 'recursive');
			echo "<form method='post' name='form' action=\"$target\">";
			if (empty ($ID) || $ID < 0) {
				echo "<input type='hidden' name='FK_entities' value='" . $_SESSION["glpiactive_entity"] . "'>";
			}

			echo "<div class='center' id='tabsbody'>";
			echo "<table class='tab_cadre_fixe'>";
			$this->showFormHeader($ID,$withtemplate, 2);

			//Display without inside table
			/* title */
			echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_order'][39] . ": </td>";
			echo "<td>";
			if ($canedit)
				autocompletionTextField("name", "glpi_plugin_order", "name", $this->fields["name"], 30, $this->fields["FK_entities"]);
			else
				echo $this->fields["name"];
			echo "</td>";
			/* date of order */
			$editcalendar = ($withtemplate != 2);
			echo "<td>" . $LANG['plugin_order'][1] . "*:</td><td>";
			if ($canedit)
				if ($this->fields["date"] == NULL)
					showDateFormItem("date", date("Y-m-d"), true, $editcalendar);
				else
					showDateFormItem("date", $this->fields["date"], true, $editcalendar);
			else
				echo convDate($this->fields["date"]);
			echo "</td></tr>";

			/* num order */
			echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_order'][0] . "*: </td>";
			echo "<td>";
			if ($canedit)
				autocompletionTextField("numorder", "glpi_plugin_order", "numorder", $this->fields["numorder"], 30, $this->fields["FK_entities"]);
			else
				echo $this->fields["numorder"];
			echo "</td>";
			echo "<td>" . $LANG['plugin_order'][3] . ": </td><td>";
			if ($canedit)
				dropdownValue("glpi_dropdown_budget", "budget", $this->fields["budget"], 1, $this->fields["FK_entities"]);
			else
				echo getDropdownName("glpi_dropdown_budget", $this->fields["budget"]);
			echo "</td></tr>";

			/* location */
			echo "<tr class='tab_bg_1'><td>" . $LANG['plugin_order'][40] . ": </td>";
			echo "<td>";
			if ($canedit)
				dropdownValue("glpi_dropdown_locations", "location", $this->fields["location"], 1, $this->fields["FK_entities"]);
			else
				echo getDropdownName("glpi_dropdown_locations", $this->fields["location"]);
			echo "</td>";
			
			/* payment */
			echo "<td>" . $LANG['plugin_order'][32] . ": </td><td>";
			if ($canedit)
				dropdownValue("glpi_dropdown_plugin_order_payment", "payment", $this->fields["payment"], 1, $this->fields["FK_entities"]);
			else
				echo getDropdownname("glpi_dropdown_plugin_order_payment", $this->fields["payment"]);
			echo "</td></tr>";
			
			/* supplier of order */
			echo "<tr class='tab_bg_1'><td>" . $LANG['financial'][26] . ": </td>";
			echo "<td>";
			if ($canedit)
            if (empty ($ID) || $ID < 0)
               $this->dropdownSuppliers("FK_enterprise", $this->fields["FK_entities"]);
            else
               dropdownValue("glpi_enterprises", "FK_enterprise", $this->fields["FK_enterprise"], 1, $this->fields["FK_entities"]);
			else
				echo getDropdownName("glpi_enterprises", $this->fields["FK_enterprise"]);
			echo "</td>";
			
			/* port price */
			echo "<td>".$LANG['plugin_order'][26].": </td>";
			echo "<td>";
			if ($canedit)
				echo "<input type='text' name='port_price' value=\"".formatNumber($this->fields["port_price"],true)."\" size='5'>";
			else
				echo formatNumber($this->fields["port_price"]);
			echo "</td></tr>";
			
			/* linked contact of the supplier of order */
			echo "<tr class='tab_bg_1'><td>".$LANG['common'][18].": </td>";
			echo "<td><span id='show_contact'>";
			if ($canedit && $ID > 0)
            dropdownValue("glpi_contacts", "FK_contact", $this->fields["FK_contact"], 1, $this->fields["FK_entities"]);
			else
				echo getDropdownName("glpi_contacts", $this->fields["FK_contact"]);
			echo "</span></td>";
			
			/* status of bill */
			echo "<td>" . $LANG['plugin_order']['status'][0] . ": </td>";
			echo "<td>";
			echo "<input type='hidden' name='status' value=" . ORDER_STATUS_DRAFT . ">";
			echo $this->getDropdownStatus($this->fields["status"]);
			echo "</td></tr>";
			//End

			echo "<tr class='tab_bg_1'><td>";
			//comments of order
			echo $LANG['plugin_order'][2] . ":	</td>";
			echo "<td>";
			if ($canedit)
				echo "<textarea cols='40' rows='4' name='comment'>" . $this->fields["comment"] . "</textarea>";
			else
				echo $this->fields["comment"];
			echo "</td>";

			/* total price (without taxes) */

			if ($ID > 0) {
            $PluginOrderDetail = new PluginOrderDetail();
				$prices = $PluginOrderDetail->getAllPrices($ID);

				echo "<td colspan='2'>" . $LANG['plugin_order'][13] . " : ";
				echo formatNumber($prices["priceHT"]) . "<br>";
        
        /* total price (with postage) */
				echo $LANG['plugin_order'][15] . " : ";
				$priceHTwithpostage=$prices["priceHT"]+$this->fields["port_price"];
				echo formatNumber($priceHTwithpostage) . "<br>";
				
				/* total price (with taxes) */
				echo $LANG['plugin_order'][14] . " : ";
				echo formatNumber($prices["priceTTC"]) . "</td></tr>";
			} else
				echo "<td colspan='2'></td>";

			echo "</tr>";

			if ($canedit) {
				echo "<tr>";
				echo "<td class='tab_bg_2' colspan='4' align='center'>";

				if (empty ($ID) || $ID < 0) {
					echo "<input type='submit' name='add' value=\"" . $LANG['buttons'][8] . "\" class='submit'>";
				} else {
					echo "<input type='hidden' name='ID' value=\"$ID\">\n";
					echo "<input type='submit' name='update' value=\"" . $LANG['buttons'][7] . "\" class='submit'>";

					if (!$this->fields["deleted"]) {
						echo "&nbsp<input type='submit' name='delete' value=\"" . $LANG['buttons'][6] . "\" class='submit'>";
					} else {
						echo "&nbsp<input type='submit' name='restore' value=\"" . $LANG['buttons'][21] . "\" class='submit'>";
						echo "&nbsp<input type='submit' name='purge' value=\"" . $LANG['buttons'][22] . "\" class='submit'>";
					}
				}
				echo "</td>";
				echo "</tr>";
			}

			echo "</table></div></form>";
			echo "<div id='tabcontent'></div>";
			echo "<script type='text/javascript'>loadDefaultTab();</script>";
		} else {
			echo "<div align='center'><b>" . $LANG['plugin_order'][11] . "</b></div>";
			return false;
		}
		return true;
	}

   function dropdownSuppliers($myname,$entity_restrict='') {
      global $DB,$CFG_GLPI;

      $rand=mt_rand();

      $where=" WHERE `glpi_enterprises`.`deleted` = '0' ";
      $where.=getEntitiesRestrictRequest("AND","glpi_enterprises",'',$entity_restrict,true);

      $query="SELECT `glpi_enterprises`.* FROM `glpi_enterprises`
         LEFT JOIN `glpi_contact_enterprise` ON (`glpi_contact_enterprise`.`FK_enterprise` = `glpi_enterprises`.`ID`)
         $where
         GROUP BY `glpi_enterprises`.`ID`
         ORDER BY `FK_entities`, `name`";
      //error_log($query);
      $result=$DB->query($query);

      echo "<select name='FK_enterprise' id='FK_enterprise'>\n";
      echo "<option value='0'>------</option>\n";

      $prev=-1;
      while ($data=$DB->fetch_array($result)) {
         if ($data["FK_entities"]!=$prev) {
            if ($prev>=0) {
               echo "</optgroup>";
            }
            $prev=$data["FK_entities"];
            echo "<optgroup label=\"". getDropdownName("glpi_entities", $prev) ."\">";
         }
         $output = $data["name"];
         if($_SESSION["glpiview_ID"]||empty($output)){
            $output.=" (".$data["ID"].")";
         }
         echo "<option value=\"".$data["ID"]."\" title=\"".cleanInputText($output)."\">".substr($output,0,$_SESSION["glpidropdown_limit"])."</option>";
      }
      if ($prev>=0) {
         echo "</optgroup>";
      }
      echo "</select>\n";

      $params=array('FK_enterprise'=>'__VALUE__',
            'entity_restrict'=>$entity_restrict,
            'rand'=>$rand,
            'myname'=>$myname
            );

      ajaxUpdateItemOnSelectEvent("FK_enterprise","show_contact",$CFG_GLPI["root_doc"]."/plugins/order/ajax/dropdownSupplier.php",$params);

      return $rand;
   }
   
   function getDropdownStatus($value) {
      global $LANG;
      
      switch ($value) {
         case ORDER_STATUS_DRAFT :
            return $LANG['plugin_order']['status'][9];
         case ORDER_STATUS_APPROVED :
            return $LANG['plugin_order']['status'][12];
         case ORDER_STATUS_WAITING_APPROVAL :
            return $LANG['plugin_order']['status'][7];
         case ORDER_STATUS_PARTIALLY_DELIVRED :
            return $LANG['plugin_order']['status'][1];
         case ORDER_STATUS_COMPLETLY_DELIVERED :
            return $LANG['plugin_order']['status'][2];
         case ORDER_STATUS_CANCELED :
            return $LANG['plugin_order']['status'][10];
         default :
            return "";
      }
   }

   function addStatusLog($orderID, $status, $comments = '') {
      global $LANG;

      switch ($status) {
         case ORDER_STATUS_DRAFT :
            $changes = $LANG['plugin_order']['validation'][15];
            break;
         case ORDER_STATUS_WAITING_APPROVAL :
            $changes = $LANG['plugin_order']['validation'][1];
            break;
         case ORDER_STATUS_APPROVED :
            $changes = $LANG['plugin_order']['validation'][2];
            break;
         case ORDER_STATUS_PARTIALLY_DELIVRED :
            $changes = $LANG['plugin_order']['validation'][3];
            break;
         case ORDER_STATUS_COMPLETLY_DELIVERED :
            $changes = $LANG['plugin_order']['validation'][4];
            break;
         case ORDER_STATUS_CANCELED :
            $changes = $LANG['plugin_order']['validation'][5];
            break;
      }

      if ($comments != '')
         $changes .= " : ".$comments;

      $this->addHistory($this->type, '',$changes,$orderID);
   //	historyLog($orderID, PLUGIN_ORDER_TYPE, $changes, 0, HISTORY_LOG_SIMPLE_MESSAGE);
   }
   
   function updateOrderStatus($orderID, $status, $comments = '') {

      $input["status"] = $status;
      $input["ID"] = $orderID;
      $this->dohistory = false;
      $this->update($input);
      $this->addStatusLog($orderID, $status, $comments);
      return true;
   }
   
   function addHistory($type, $old_value='',$new_value='',$ID){
      $changes[0] = 0;
      $changes[1] = $old_value;
      $changes[2] = $new_value;
      historyLog($ID, $type, $changes, 0, HISTORY_LOG_SIMPLE_MESSAGE);
   }

	function needValidation($ID) {
		global $ORDER_VALIDATION_STATUS;
		
		if ($ID > 0 && $this->getFromDB($ID))
			return (in_array($this->fields["status"], $ORDER_VALIDATION_STATUS));
		else
			return false;
	}
   
   function canDisplayValidationForm($orderID) {

      $this->getFromDB($orderID);

      //If it's an order creation -> do not display form
      if (!$orderID)
         return false;
      else
         return ($this->canValidate() || $this->canUndoValidation() || $this->canCancelOrder());
   }

	function canValidate() {
		global $ORDER_VALIDATION_STATUS;
		
		$PluginOrderConfig = new PluginOrderConfig;
		$config = $PluginOrderConfig->getConfig();

		//If no validation process -> can validate if order is in draft state
		if (!$config["use_validation"])
			return ($this->fields["status"] == ORDER_STATUS_DRAFT);
		else {
			//Validation process is used

			//If order is canceled, cannot validate !
			if ($this->fields["status"] == ORDER_STATUS_CANCELED)
				return false;

			//If no right to validate
			if (!plugin_order_haveRight("validation", "w"))
				return false;
			else
				return (in_array($this->fields["status"], $ORDER_VALIDATION_STATUS));
		}
	}

	function canCancelOrder() {
		//If order is canceled, cannot validate !
		if ($this->fields["status"] == ORDER_STATUS_CANCELED)
			return false;

		//If no right to cancel
		if (!plugin_order_haveRight("cancel", "w"))
			return false;

		return true;
	}
	
	function deleteAllLinkWithDevice($orderID) {

      $detail = new PluginOrderDetail;
      $devices = getAllDatasFromTable("glpi_plugin_order_detail", "FK_order=$orderID");
      foreach ($devices as $deviceID => $device)
         $detail->delete(array (
            "ID" => $deviceID
         ));
   }

	function canDoValidationRequest() {
		
		$PluginOrderConfig = new PluginOrderConfig;
		$config = $PluginOrderConfig->getConfig();
		
		if (!$config["use_validation"])
			return false;
		else
			return ($this->fields["status"] == ORDER_STATUS_DRAFT);
	}

	function canCancelValidationRequest() {
	
		return ($this->fields["status"] == ORDER_STATUS_WAITING_APPROVAL);
	}

	function canUndoValidation() {
		global $ORDER_VALIDATION_STATUS;
		
		//If order is canceled, cannot validate !
		if ($this->fields["status"] == ORDER_STATUS_CANCELED)
			return false;

		//If order is not validate, cannot undo validation !
		if (in_array($this->fields["status"], $ORDER_VALIDATION_STATUS))
			return false;

		//If no right to cancel
		return (plugin_order_haveRight("undo_validation", "w"));
	}
	
	function showValidationForm($target, $orderID) {
      global $LANG;
      
      $this->getFromDB($orderID);

      if ($this->canDisplayValidationForm($orderID)) {
         echo "<form method='post' name='form' action=\"$target\">";
         echo "<table class='tab_cadre_fixe'>";

         echo "<tr class='tab_bg_2'><th colspan='3'>" . $LANG['plugin_order']['validation'][6] . "</th></tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td valign='top' align='right'>";
         echo $LANG['common'][25] . ":&nbsp;";
         echo "</td>";
         echo "<td valign='top' align='left'>";
         echo "<textarea cols='40' rows='4' name='comments'></textarea>";
         echo "</td>";

         echo "<td align='center'>";
         echo "<input type='hidden' name='ID' value=\"$orderID\">\n";

         if ($this->canCancelOrder()) {
            echo "<input type='submit' onclick=\"return confirm('" . $LANG['plugin_order']['detail'][38] . "')\" name='cancel_order' value=\"" . $LANG['plugin_order']['validation'][12] . "\" class='submit'>";
            $link = "<br><br>";
         }
         $PluginOrderConfig = new PluginOrderConfig;
         $config = $PluginOrderConfig->getConfig();

         if ($this->canValidate()) {
            echo $link . "<input type='submit' name='validate' value=\"" . $LANG['plugin_order']['validation'][9] . "\" class='submit'>";
            $link = "<br><br>";
         }

         if ($this->canCancelValidationRequest()) {
            echo $link . "<input type='submit' onclick=\"return confirm('" . $LANG['plugin_order']['detail'][39] . "')\" name='cancel_waiting_for_approval' value=\"" . $LANG['plugin_order']['validation'][13] . "\" class='submit'>";
            $link = "<br><br>";
         }

         if ($this->canDoValidationRequest()) {
            echo $link . "<input type='submit' name='waiting_for_approval' value=\"" . $LANG['plugin_order']['validation'][11] . "\" class='submit'>";
            $link = "<br><br>";
         }

         if ($this->canUndoValidation()) {
            echo $link . "<input type='submit' onclick=\"return confirm('" . $LANG['plugin_order']['detail'][40] . "')\" name='undovalidation' value=\"" . $LANG['plugin_order']['validation'][17] . "\" class='submit'>";
            $link = "<br><br>";
         }

         echo "</td>";
         echo "</tr>";

         echo "</table></form>";
      }
   }
   
   function showGenerationForm($ID) {
      global $LANG,$CFG_GLPI;

      echo "<form action='".$CFG_GLPI["root_doc"]."/plugins/order/front/plugin_order.generate.php?ID=".$ID."' method=\"post\">";
      echo "<div align=\"center\"><table cellspacing=\"2\" cellpadding=\"2\">";

      echo "<tr>";
      echo "<td class='center'>";
      echo "<input type='submit' value=\"".$LANG['plugin_order']['generation'][1]."\" class='submit' ></div></td></tr>";
      echo "</td>";
      echo "</tr>";

      echo "</table></div></form>";
   }
   
   function generateOrder($ID) {
		global $LANG,$DB;
		
		$odf = new odf("template.odt");
      
      $this->getFromDB($ID);
      $titre = $LANG['plugin_order']['generation'][2]." ".$this->fields["numorder"];
		$odf->setVars('title',$titre,true,'UTF-8');

      $message = $LANG['plugin_order']['generation'][3];

      $odf->setVars('message', $message,true,'UTF-8');
      
      $odf->setVars('titletype',$LANG['plugin_order']['detail'][1],true,'UTF-8');
      
      $odf->setVars('titlename',$LANG['plugin_order']['detail'][2],true,'UTF-8');
      
      $listeArticles = array();
      
      $query="SELECT `glpi_plugin_order_detail`.`ID` AS IDD, `glpi_plugin_order_references`.`ID`, 
					`glpi_plugin_order_references`.`type`,`glpi_plugin_order_references`.`FK_type`,`glpi_plugin_order_references`.`FK_model`, `glpi_plugin_order_references`.`FK_glpi_enterprise`, `glpi_plugin_order_references`.`name`, 
					`glpi_plugin_order_detail`.`price_taxfree`, `glpi_plugin_order_detail`.`price_ati`, `glpi_plugin_order_detail`.`price_discounted`, 
               `glpi_plugin_order_detail`.`discount`,
					`glpi_plugin_order_detail`.`price_discounted` AS totalpriceHT, 
					`glpi_plugin_order_detail`.`price_ati` AS totalpriceTTC 
					FROM `glpi_plugin_order_detail`, `glpi_plugin_order_references`
					WHERE `glpi_plugin_order_detail`.`FK_reference` = `glpi_plugin_order_references`.`ID`
					AND `glpi_plugin_order_detail`.`FK_order` = '$ID'
					ORDER BY `glpi_plugin_order_references`.`name` ";
					
      $result=$DB->query($query);
      $num=$DB->numrows($result);
      
      while ($data=$DB->fetch_array($result)){
         $ci=new CommonItem();
         $ci->setType($data["type"]);
         
         $listeArticles[]=array('type' => utf8_decode($ci->getType()),
               'ref' => utf8_decode($data["name"]));
         
      }
      
      $article = $odf->setSegment('articles');
      foreach($listeArticles AS $element) {
         $article->titleArticle($element['type']);
         $article->texteArticle($element['ref']);
         $article->merge();
      }

      $odf->mergeSegment($article);
      
      // We export the file
      $odf->exportAsAttachedFile();
	}
}

?>